<?php
//define('ABSPATH', dirname(__DIR__));
global $pdo;
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
require_once '../vendor/autoload.php';

use OTPHP\TOTP;

initDatabase();

// 从config.php加载极验配置
$config = [];
if (file_exists('../config.php')) {
    $config = require '../config.php';
}

// 检查是否启用极验验证
$geetestEnabled = isset($config['geetest']['id'], $config['geetest']['key'], $config['geetest']['api_server']);

// 极验验证码验证函数
function verifyGeetest($lot_number, $captcha_output, $pass_token, $gen_time) {
    global $config;
    
    if (!isset($config['geetest'])) {
        return true; // 没有配置时跳过验证
    }
    
    $query = [
        "lot_number" => $lot_number,
        "captcha_output" => $captcha_output,
        "pass_token" => $pass_token,
        "gen_time" => $gen_time,
        "sign_token" => hash_hmac('sha256', $lot_number, $config['geetest']['key'])
    ];
    
    $url = $config['geetest']['api_server'] . "/validate" . "?" . http_build_query($query);
    
    try {
        $context = stream_context_create([
            'http' => [
                'timeout' => 5 // 5秒超时
            ]
        ]);
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            error_log("极验验证请求失败: " . error_get_last()['message']);
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (!is_array($data) || !isset($data['result'])) {
            error_log("极验验证返回无效数据: " . $response);
            return false;
        }
        
        return $data['result'] === 'success';
    } catch (Exception $e) {
        error_log("极验验证异常: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    // 如果启用了极验验证，则进行验证
    if ($geetestEnabled) {
        if (empty($_POST['lot_number']) || empty($_POST['captcha_output']) || 
            empty($_POST['pass_token']) || empty($_POST['gen_time'])) {
            $loginError = "请完成验证码验证";
            goto html;
        }
    }

    $adminEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
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
                $device_id = generateUniqueDeviceId();
                $_SESSION['device_id'] = $device_id;
                $_SESSION['userToken'] = $secret;
                $_SESSION['logged_in'] = true;
                $_SESSION['email'] = $adminEmail;
                $userToken = $_SESSION['userToken'];

                if (isset($_SESSION['userToken'], $_SESSION['email'])) {
                    $userToken = $_SESSION['userToken'];
                    $email = $_SESSION['email'];
                    $checkStmt = $pdo->prepare("SELECT id FROM admin_devices 
                    WHERE email = :email AND token = :userToken AND device_id = :device_id");
                    $checkStmt->execute([':email' => $email, ':userToken' => $userToken, ':device_id' => $device_id]);
                    $device = $checkStmt->fetch(PDO::FETCH_ASSOC);
                    $sessionId = session_id();

                    if ($device) {
                        $updateStmt = $pdo->prepare("UPDATE admin_devices SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
                        $updateStmt->execute([':id' => $device['id']]);
                    } else {
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
            $device_id = generateUniqueDeviceId();
            $_SESSION['device_id'] = $device_id;
            $_SESSION['userToken'] = $hashedPassword;
            $_SESSION['logged_in'] = true;
            $_SESSION['email'] = $adminEmail;
            $userToken = $_SESSION['userToken'];

            if (isset($_SESSION['userToken'], $_SESSION['email'])) {
                $userToken = $_SESSION['userToken'];
                $email = $_SESSION['email'];
                $checkStmt = $pdo->prepare("SELECT id FROM admin_devices 
                WHERE email = :email AND token = :userToken AND device_id = :device_id");
                $checkStmt->execute([':email' => $email, ':userToken' => $userToken, ':device_id' => $device_id]);
                $device = $checkStmt->fetch(PDO::FETCH_ASSOC);
                $sessionId = session_id();

                if ($device) {
                    $updateStmt = $pdo->prepare("UPDATE admin_devices SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
                    $updateStmt->execute([':id' => $device['id']]);
                } else {
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
        $query = "SELECT totp_secret FROM admin WHERE email = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$adminEmail]);
        $secret = $stmt->fetchColumn();
        $totp = TOTP::create($secret);
        if (password_verify($inputUserPassword, $hashedPassword) && $totp->verify($inputUserToken)) {
            $device_id = generateUniqueDeviceId();
            $_SESSION['device_id'] = $device_id;
            $_SESSION['userToken'] = $hashedPassword . '-' . $inputUserToken;
            $_SESSION['logged_in'] = true;
            $_SESSION['email'] = $adminEmail;
            $userToken = $_SESSION['userToken'];

            if (isset($_SESSION['userToken'], $_SESSION['email'])) {
                $userToken = $_SESSION['userToken'];
                $email = $_SESSION['email'];
                $checkStmt = $pdo->prepare("SELECT id FROM admin_devices 
                WHERE email = :email AND token = :userToken AND device_id = :device_id");
                $checkStmt->execute([':email' => $email, ':userToken' => $userToken, ':device_id' => $device_id]);
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
            box-sizing: border-box;
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
        #geetest-captcha {
            margin-bottom: 20px;
        }
    </style>
    <?php if ($geetestEnabled): ?>
    <script src="https://static.geetest.com/v4/gt4.js"></script>
    <?php endif; ?>
</head>
<body>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="login-form" id="login-form">
    <label for="email">请输入邮箱:</label>
    <input type="text" id="email" name="email" required>
    
    <?php if ($geetestEnabled): ?>
    <!-- 极验验证码容器 -->
    <div id="geetest-captcha"></div>
    <input type="hidden" name="lot_number" id="lotNumber">
    <input type="hidden" name="captcha_output" id="captchaOutput">
    <input type="hidden" name="pass_token" id="passToken">
    <input type="hidden" name="gen_time" id="genTime">
    <?php endif; ?>
    
    <p>如果您的账号选择使用TOTP，请输入TOTP密钥并留空密码区域。</p>
    <p>使用传统密码，请留空TOTP区域并填写密码。两者都使用请全部填写。</p>
    <label for="password">请输入密码:</label>
    <input type="password" id="password" name="password">
    <label for="totp">输入TOTP密钥:</label>
    <input type="password" id="totp" name="totp">
    <button type="submit" id="submit-btn">登录</button>

    <?php if (isset($loginError)): ?>
        <p class="error"><?php echo $loginError; ?></p>
    <?php endif; ?>
</form>

<script>
<?php if ($geetestEnabled): ?>
// 初始化极验验证码
var captcha;
initGeetest4({
    captchaId: "<?php echo $config['geetest']['id']; ?>",
    product: "bind",
    language: "zh-cn"
}, function(instance) {
    captcha = instance;
    
    captcha.onReady(function() {
        document.getElementById('geetest-captcha').style.display = 'block';
    });
    
    captcha.onSuccess(function() {
        var result = captcha.getValidate();
        document.getElementById('lotNumber').value = result.lot_number;
        document.getElementById('captchaOutput').value = result.captcha_output;
        document.getElementById('passToken').value = result.pass_token;
        document.getElementById('genTime').value = result.gen_time;
        document.getElementById('login-form').submit();
    });
    
    captcha.appendTo("#geetest-captcha");
});

document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!document.getElementById('lotNumber').value) {
        captcha.showCaptcha();
    } else {
        this.submit();
    }
});
<?php else: ?>
document.getElementById('login-form').addEventListener('submit', function(e) {
    // 没有极验验证时直接提交
    return true;
});
<?php endif; ?>
</script>

</body>
</html>