<?php
// 如果这个文件被直接访问，则退出
// if (!defined('ABSPATH')) {
//     exit('不允许直接访问');
// }
session_start();
# 初始化插件
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
define('Func_Path', __FILE__);
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/plugin_func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/auth_func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/logger.php';
use Unleash\Client\UnleashBuilder;

//use Random\RandomException;

if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/install.lock')){
    header("Location: /install.php");
}
/**
 * @return void
 */
function initDatabase()
{
    global $pdo; // 使用全局变量
    try {
        $config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
        $host = $config['db']['host'];
        $dbname = $config['db']['dbname'];
        $user = $config['db']['user'];
        $pass = $config['db']['pass'];
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Could not connect to the database: " . $e->getMessage());
    }
}

$action_file_path = $_SERVER['DOCUMENT_ROOT'] . '/action/';
if (!file_exists($action_file_path)) {
    mkdir($action_file_path);
}
//[DEL]
// 定义缓存文件目录
define('CACHE_DIR', $_SERVER['DOCUMENT_ROOT'] . '/cache/');

// 确保缓存目录存在
if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0777, true);
}
// 生成加密密钥并保存到文件中
function generateEncryptionKey(): string
{
    $key = base64_encode(random_bytes(32));
    $keyFilePath = CACHE_DIR . 'secret.key';
    file_put_contents($keyFilePath, $key);
    return $key;
}

// 加载加密密钥
function loadEncryptionKey(): string
{
    $keyFilePath = CACHE_DIR . 'secret.key';
    if (!file_exists($keyFilePath)) {
        return generateEncryptionKey();
    }
    return file_get_contents($keyFilePath);
}

// 获取缓存文件路径
/**
 * @return string
 */
function getCacheFilePath(): string
{
    return CACHE_DIR . 'status.cache';
}

// 检查缓存文件是否存在且未过期
/**
 * @return bool
 * @throws DateMalformedStringException
 */
function isCacheValid(): bool
{
    $cacheFilePath = getCacheFilePath();
    if (!file_exists($cacheFilePath)) {
        return false;
    }

    $cacheData = json_decode(file_get_contents($cacheFilePath), true);
    $expirationTime = new DateTime($cacheData['expiration']);
    $currentTime = new DateTime();

    return $currentTime < $expirationTime;
}

// 读取缓存
/**
 * @return mixed|null
 * @throws RandomException
 */
function readCache(): mixed
{
    $cacheFilePath = getCacheFilePath();
    $cacheData = json_decode(file_get_contents($cacheFilePath), true);
    return $cacheData;
}

// 写入缓存
/**
 * @param $cacheData
 * @return void
 */
function writeCache($cacheData)
{

    file_put_contents(getCacheFilePath(), json_encode($cacheData));
}

// 删除缓存
/**
 * @return void
 */
function deleteCache()
{
    $cacheFilePath = getCacheFilePath();
    if (file_exists($cacheFilePath)) {
        unlink($cacheFilePath);
    }
}

/**
 * @return bool
 * @throws DateMalformedStringException
 * @throws RandomException
 */


/**
 * @param $dataArray
 * @return void
 */
function outputJson($dataArray)
{
    // 将数组转换为JSON格式
    $json = json_encode($dataArray);

    // 检查json_encode是否成功
    header('Content-Type: application/json');
    if (json_last_error() !== JSON_ERROR_NONE) {
        // 如果转换失败，输出错误信息
        echo json_encode(array('error' => 'Failed to encode data to JSON'));
        return;
    }

    // 设置HTTP头部为application/json

    // 输出JSON字符串
    echo $json;
}

// 使用示例
//$data = array(
//    'name' => 'Guest',
//    'email' => 'null',
//    'device_id' => 'null',
//    'userToken' => 'null',
//    'islogin' => false,
//    'isadmin' => false,
//    'isuser' => false,
//    'isguest' => true,
//    'isunknown' => false,
//    'isdeleted' => false,
//    'isdisabled' => false,
//    'islocked' => false,
//);
//outputJson($data);

/**
 * @return false|mixed|string
 */
function getRealIp()
{
    $ip = false;
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
        if ($ip) {
            array_unshift($ips, $ip);
            $ip = FALSE;
        }
        for ($i = 0; $i < count($ips); $i++) {
            if (!preg_match("/^(10|172\.16|192\.168)\./", $ips[$i])) {
                $ip = $ips[$i];
                break;
            }
        }
    }
    return ($ip ?: $_SERVER['REMOTE_ADDR']);
}

/**
 * @return bool
 */
function checkUserLogin(): bool
{


    // 用于获取用户是否登陆
    if (isset($_SESSION['userToken']) && isset($_SESSION['email']) && isset($_SESSION['device_id'])) {
        $device_id = $_SESSION['device_id'];
        $userToken = $_SESSION['userToken'];
        $email = $_SESSION['email'];
        initDatabase();
        global $pdo;
        // 检查设备是否已经记录在数据库中
        $checkStmt = $pdo->prepare("SELECT id FROM admin_devices 
                   WHERE email = :email AND token = :userToken AND device_id = :device_id");
        $checkStmt->execute([':email' => $email, ':userToken' => $userToken, ':device_id' => $device_id]);
        $device = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$device) {
            session_destroy();
            return false;
        }
// 更新最后登陆时间
        $updateStmt = $pdo->prepare("UPDATE admin_devices 
                            SET last_login = NOW() 
                            WHERE email = :email AND token = :userToken AND device_id = :device_id");
        $updateStmt->execute([':email' => $email, ':userToken' => $userToken, ':device_id' => $device_id]);
        return true;
    } else {
        return false;
    }
}

// 生成一个唯一的设备标识符，UUID生成
/**
 * @return string
 */
function generateUniqueDeviceId(): string
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

//[/DEL]
// 插件加载逻辑
if (!file_exists($action_file_path . 'plugin_safe')) {
    # 插件安全模式，存在此文件时不加载插件
    require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/plugin/hooks.php';
    // 定义插件目录路径
    $plugins_dir = $_SERVER['DOCUMENT_ROOT'] . '/plugins/';




// 调用函数加载插件
    load_plugins();


//// 检查插件目录是否存在
//    if (is_dir($plugins_dir)) {
//        // 遍历插件目录
//        $plugin_directories = scandir($plugins_dir);
//        foreach ($plugin_directories as $directory) {
//            // 排除非目录项
//            if ($directory !== '.' && $directory !== '..' && is_dir($plugins_dir . $directory)) {
//                // 构建main.php的路径
//                $main_php_path = $plugins_dir . $directory . '/main.php';
//                $enable_php_path = $plugins_dir . $directory . '/enable';
//
//                // 检查main.php是否存在并且可读，并且存在enable文件且可读
//                if (file_exists($main_php_path) && is_readable($main_php_path) && file_exists($enable_php_path) && is_readable($enable_php_path)) {
//                    // 包含main.php文件
//                    require_once $main_php_path;
//                }
//            }
//        }
//    }
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/template.php';

global $pdo;
define('TEMPLATE_LOADER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/templates/loader.php');

require_once TEMPLATE_LOADER_PATH;