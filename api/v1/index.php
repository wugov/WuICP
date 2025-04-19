<?php
// index.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
global $pdo;
initDatabase();
$action = isset($_GET['action']) ? $_GET['action'] : '';
// 根据请求的页面来处理不同的逻辑
switch ($action) {
    case 'islogin':
        // 用于获取用户是否登陆
        if (isset($_SESSION['userToken']) && isset($_SESSION['email']) && isset($_SESSION['device_id'])) {
            $device_id = $_SESSION['device_id'];
            $userToken = $_SESSION['userToken'];
            $email = $_SESSION['email'];
            // 检查设备是否已经记录在数据库中
            $checkStmt = $pdo->prepare("SELECT id FROM admin_devices 
                WHERE email = :email AND token = :userToken AND device_id = :device_id");
            $checkStmt->execute([':email' => $email, ':userToken' => $userToken, ':device_id' => $device_id]);
            $device = $checkStmt->fetch(PDO::FETCH_ASSOC);
            // 使用示例
            $data = array(
                'islogin' => true,
            );
            outputJson($data);
        } else {
            // 用户未登录，返回未登录状态
            $data = array(
                'islogin' => false,
            );
            outputJson($data);
        }
        break;
    case 'getinfo':
        // 处理页面2
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            die("Access Denied");
        }
        $device_id = $_SESSION['device_id'];
        if ($device_id) {
            // 设备已存在，更新最后登录时间
            $query = "UPDATE admin_devices SET last_login = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':device_id', $device_id);
        }
        // 查询用户信息
        $query = "SELECT email, device_id, token, last_login FROM admin_devices WHERE device_id = :device_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':device_id', $device_id);
        $stmt->execute(); // 执行查询
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC); // 获取结果

        if (!$userInfo) {
            die("信息不存在");
        }
        extract($userInfo);
        $data = array(
            'email' => $email,
            'device_id' => $device_id,
            'token' => $token,
            'last_login' => $last_login,
        );
        outputJson($data);
        break;

    case 'login':
        break;
    case 'logout':
        break;
    case 'logout_device':
        break;
    default:
        echo "Access Denied";
        break;
}
?>
