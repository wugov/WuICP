<?php
global $pdo;
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
require_once '../../vendor/autoload.php';

use Firebase\JWT\JWT;

initDatabase();

if (!isAuth()) {
    die('未授权或授权状态不正确');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $adminEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // 清理邮箱地址
//    echo $adminEmail;
    $totp_enabled = false;
    $query = "SELECT password_enabled FROM admin WHERE email = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$adminEmail]);
    $password_enabled = $stmt->fetchColumn();

        if (empty($_POST['password'])) {
            $loginError = "请输入密码";
            goto html;
        }
        $inputUserPassword = $_POST['password'];
        $query = "SELECT password FROM admin WHERE email = ?";
        $stmt = $pdo->prepare($query);
        if ($stmt->execute([$adminEmail])) {
            $hashedPassword = $stmt->fetchColumn();
        }
        if (password_verify($inputUserPassword, $hashedPassword)) {
            $device_id = generateUniqueDeviceId(); // 使用更复杂的设备识别方法
            $_SESSION['device_id'] = $device_id; // 将自己的设备id存入session
            $_SESSION['userToken'] = $hashedPassword; // 使用哈希后的密码作为会话标识
            $_SESSION['logged_in'] = true; // 标记用户为已登录
            $_SESSION['email'] = $adminEmail;
            $userToken = $_SESSION['userToken'];

            // 确保用户已经登录并且userToken在会话中
            if (isset($_SESSION['userToken'], $_SESSION['email'])) {
                $userToken = $_SESSION['userToken'];
                $email = $_SESSION['email'];
                // 检查设备是否已经记录在数据库中
                $checkStmt = $pdo->prepare("SELECT id FROM admin_devices 
                WHERE email = :email AND token = :userToken AND device_id = :device_id");
                $checkStmt->execute([':email' => $email, ':userToken' => $userToken, ':device_id' => $device_id]);
                $device = $checkStmt->fetch(PDO::FETCH_ASSOC);
                $sessionId = session_id(); // 获取当前会话ID
                //echo $sessionId;

                if ($device) {
                    // 设备已存在，更新最后登录时间
                    $updateStmt = $pdo->prepare("UPDATE admin_devices SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
                    $updateStmt->execute([':id' => $device['id']]);
                } else {
                    // 设备不存在，插入新记录
                    $insertStmt = $pdo->prepare("INSERT INTO admin_devices (email, token, device_id, last_login, ip_address, session_id) VALUES (:email, :userToken, :device_id, CURRENT_TIMESTAMP, :ip_address,:session_id)");
                    $insertStmt->execute([':email' => $email, ':userToken' => $userToken, ':device_id' => $device_id, ':ip_address' => getRealIp(), ':session_id' => $sessionId]);
                }
            }
            session_regenerate_id(true);
            // 生成JWT
            $token = array(
                "email" => $adminEmail,
                "exp" => time() + 3600 // Token有效期为1小时
            );
            $jwt = JWT::encode($token, JWT_KEY, 'HS256');

            // 设置HTTP头
            header('Content-Type: application/json');
            // 返回JSON格式的数据
            echo json_encode(['code' => 0, 'message' => '登录成功','status' => 'success', 'jwt' => $jwt]);
            exit;
        } else {
            // 设置HTTP头
            header('Content-Type: application/json');
            echo json_encode(['code' => 1, 'message' => '邮箱或密码不正确','status' => 'success']);
            exit;
        }


} else {
    // 设置HTTP头
    header('Content-Type: application/json');
    echo json_encode(['code' => 1, 'message' => '无效的请求','status' => 'error']);
    exit;
}

html:
// 设置HTTP头
header('Content-Type: application/json');
echo json_encode(['code' => 1, 'message' => $loginError]);