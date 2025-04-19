<?php
//define('ABSPATH', dirname(__DIR__));
//include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';
// Start the session
global $pdo;
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
if (!checkUserLogin()) {
    header("Location: login.php");
    exit();
}
// 获取传递过来的template参数
$templateName = isset($_GET['template']) ? $_GET['template'] : '';

$path = $_SERVER['DOCUMENT_ROOT'] . "/templates/{$templateName}/index.html";
if (!file_exists($path)) {
    echo "模板文件未找到: {$path}";
    exit();
}
echo "模板可用: {$path}";