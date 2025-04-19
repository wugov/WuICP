<?php
// if (!defined('ABSPATH')) {
//     exit('不允许直接访问');
// }
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';
initDatabase();
global $pdo;
$query = "SELECT enable_template_name FROM website_info LIMIT 1";
$stmt = $pdo->query($query);
$websiteInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if we got the data
if (!$websiteInfo) {
    die("网站信息不存在");
}
extract($websiteInfo);
// 定义常量 template_name
define('TEMPLATE_NAME', $enable_template_name); // 这里替换为实际的模板名称

// 获取当前目录
$currentDir = __DIR__;

// 打开目录
if ($handle = opendir($currentDir)) {
    // 遍历目录中的所有文件和文件夹
    while (false !== ($entry = readdir($handle))) {
        // 跳过当前目录和上级目录
        if ($entry != "." && $entry != "..") {
            $fullPath = $currentDir . DIRECTORY_SEPARATOR . $entry;
            // 检查是否是文件夹
            if (is_dir($fullPath)) {
                $templatePath = $fullPath . DIRECTORY_SEPARATOR . 'template.php';
                // 检查文件夹中是否存在 template.php
                if (file_exists($templatePath)) {
                    // 如果文件夹名称与 TEMPLATE_NAME 匹配
                    if ($entry === TEMPLATE_NAME) {
                        include $templatePath;
                    }
                }
            }
        }
    }
    // 关闭目录
    closedir($handle);
} else {
    echo "无法打开目录: " . $currentDir . "\n";
}
?>
