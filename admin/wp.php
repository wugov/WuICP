<?php
//define('ABSPATH', dirname(__DIR__));
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';
require_once '../vendor/autoload.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
if (!checkUserLogin()) {
    header("Location: login.php");
    exit();
}
$userToken = $_SESSION['userToken'];
global $pdo;
initDatabase(); ?>
<div id="sidebar"><?php include 'sidebar.php'; ?></div>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>ICP Records Backend</title>
    <link rel="stylesheet" href="css/device.css">
    <link rel="stylesheet" href="css/settings.css">
</head>
<body>

<?php


    echo "<h2 style='position: absolute; top:20px; right: 20px;'><a href='https://qm.qq.com/q/6NgEjobS3S'>点击链接加入群聊【云团子 站长2群】获取插件</a></h2>";


?>
</body>
</html>
