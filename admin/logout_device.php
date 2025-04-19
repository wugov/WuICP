<?php
//define('ABSPATH', dirname(__DIR__));
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';
require_once '../vendor/autoload.php';
$config = require $_SERVER['DOCUMENT_ROOT'] . '/config.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
if (!checkUserLogin()) {
    header("Location: login.php");
    exit();
}
initDatabase();
global $pdo;
// 检查是否有设备ID提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['device_id'])) {
    $deviceId = $_POST['device_id'];

    // 清理输入以防止SQL注入
    $deviceId = strip_tags($deviceId);
    $deviceId = filter_var($deviceId, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $deviceId = trim($deviceId);
    // 查找对应的session id
    $query = "SELECT session_id FROM admin_devices WHERE device_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$deviceId]);
    // 从数据库中删除设备记录
    $query = "DELETE FROM admin_devices WHERE device_id = ?";
    $stmt = $pdo->prepare($query);

    if ($stmt->execute([$deviceId])) {
        echo "设备已成功下线。";
    } else {
        echo "下线设备失败，请重试。";
    }
} else {
    echo "无效的请求。";
}
?>
