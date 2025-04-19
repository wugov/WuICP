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

// 查询设备信息
$query = "SELECT device_id, last_login, ip_address FROM admin_devices WHERE token = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$userToken]);
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 检查是否有设备记录
if ($devices) {
    // 开始表单
    echo '<form action="logout_device.php" method="post">';

    // 遍历设备并输出信息及下线选项
    foreach ($devices as $device) {
        echo '<div>';
        echo '设备ID: ' . htmlspecialchars($device['device_id']) . '，最后登录时间: ' . htmlspecialchars($device['last_login'] . '，登陆IP地址:' . htmlspecialchars($device['ip_address']));
        echo '<input type="radio" name="device_id" value="' . htmlspecialchars($device['device_id']) . '">';
        echo '</div>';
    }

    // 提交按钮
    echo '<input type="submit" name="logout_device" value="下线所选设备">';
    echo '</form>';
} else {
    // 如果没有找到设备，可以输出一条消息
    echo "<h2 style='position: absolute; top:20px; right: 20px;'>---------------没有找到登录的设备。---------------</h2>";
}

?>
</body>
</html>
