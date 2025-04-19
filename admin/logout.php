<?php
//define('ABSPATH', dirname(__DIR__));

require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
initDatabase();
global $pdo;
if (!checkUserLogin()) {
    header("Location: login.php");
    exit();
}

$deviceId = $_SESSION['device_id'];

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
    unset($_SESSION['device_id']);
    unset($_SESSION['userToken']);
    unset($_SESSION['email']);
    header("Location: index.php");
} else {
    echo "下线设备失败，请重试。";
}
