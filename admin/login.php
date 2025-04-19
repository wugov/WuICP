<?php
//define('ABSPATH', dirname(__DIR__));
global $pdo;
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
require_once '../vendor/autoload.php';

use OTPHP\TOTP;

initDatabase();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $adminEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // 清理邮箱地址
    //echo $adminEmail;
    // 获取TOTP启用状态
    $query = "SELECT totp_enabled FROM admin WHERE email = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$adminEmail]);
    $totp_enabled = $stmt->fetchColumn();
    $query = "SELECT password_enabled FROM admin WHERE email = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$adminEmail]);
    $password_enabled = $stmt->fetchColumn();

    if ($totp_enabled && !$password_enabled) {
        if (empty($_POST['totp'])) {
            $loginError = "请输入TOTP";
            goto html;
        }
        $inputUserToken = $_POST['totp'];

        // 从数据库中获取用户的TOTP密钥
        $query = "SELECT totp_secret FROM admin WHERE email = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$adminEmail]);
        $secret = $stmt->fetchColumn();

        if ($secret) {
            // 创建TOTP对象
            $totp = TOTP::create($secret);
            // 验证令牌
            if ($totp->verify($inputUserToken)) {
                $device_id = generateUniqueDeviceId(); // 使用更复杂的设备识别方法
                $_SESSION['device_id'] = $device_id; //将自己的设备id存入session
                $_SESSION['userToken'] = $secret; // 使用TOTP密钥作为会话标识
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
                header('Location: index.php');
                exit;
            } else {
                $loginError = "邮箱或TOTP不正确";
            }
        } else {
            $loginError = "用户不存在";
        }
    }
    if ($password_enabled && !$totp_enabled) {
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
            $_SESSION['device_id'] = $device_id; //将自己的设备id存入session
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
            echo '登录成功';
            header('Location: index.php');
            exit;
        } else {
            $loginError = "邮箱或密码不正确";
        }

    }
    if ($totp_enabled && $password_enabled) {
        if (empty($_POST['totp'])) {
            $loginError = "请输入TOTP";
            goto html;
        }
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
        $inputUserToken = $_POST['totp'];
        $inputUserPassword = $_POST['password'];
        $query = "SELECT password FROM admin WHERE email = ?";
        $stmt = $pdo->prepare($query);
        if ($stmt->execute([$adminEmail])) {
            $hashedPassword = $stmt->fetchColumn();
        } else {
            $loginError = "用户不存在";
            goto html;
        }
        // 从数据库中获取用户的TOTP密钥
        $query = "SELECT totp_secret FROM admin WHERE email = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$adminEmail]);
        $secret = $stmt->fetchColumn();
        // 创建TOTP对象
        $totp = TOTP::create($secret);
        // 验证令牌
        if (password_verify($inputUserPassword, $hashedPassword) && $totp->verify($inputUserToken)) {
            $device_id = generateUniqueDeviceId(); // 使用更复杂的设备识别方法
            $_SESSION['device_id'] = $device_id; //将自己的设备id存入session
            $_SESSION['userToken'] = $hashedPassword . '-' . $inputUserToken; // 使用哈希后的密码和totp密钥作为会话标识
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
            }
        } else {
            $loginError = "邮箱或密码或TOTP不正确";
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: index.php');
    exit;
}
html:
// 显示登录表单
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            padding: 20px;
        }

        .login-form {
            background: #fff;
            max-width: 300px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box; /* makes sure padding doesn't affect width */
        }

        button {
            width: 100%;
            padding: 10px;
            border: none;
            background-color: #7dc3f6;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #07b9ff;
        }

        .error {
            color: #d9534f;
        }
    </style>
</head>
<body>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="login-form">
    <label for="email">请输入邮箱:</label>
    <input type="text" id="email" name="email" required>
    <p>如果您的账号选择使用TOTP，请输入TOTP密钥并留空密码区域。</p>
    <p>使用传统密码，请留空TOTP区域并填写密码。两者都使用请全部填写。</p>
    <label for="password">请输入密码:</label>
    <input type="password" id="password" name="password">
    <label for="totp">输入TOTP密钥:</label>
    <input type="password" id="totp" name="totp">
    <button type="submit">登录</button>

    <?php if (isset($loginError)): ?>
        <p class="error"><?php echo $loginError; ?></p>
    <?php endif; ?>
</form>

</body>
</html>
