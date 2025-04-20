<?php
require 'vendor/autoload.php';

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use OTPHP\TOTP;

if (file_exists('install.lock')) {
    echo "install.lock 系统已安装，请勿重复安装。如发生错误/需要重新安装，请删除 install.lock \n";
    exit;
}
if (file_exists('redis.lock')) {
    unlink('redis.lock');
}
if (file_exists('qrcode.png')) {
    unlink('qrcode.png');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dbHost = trim(filter_var($_POST['dbHost'] ?? '127.0.0.1', FILTER_VALIDATE_IP) ?: '127.0.0.1');
    $dbName = trim(htmlspecialchars($_POST['dbName']));
    $dbUser = trim(htmlspecialchars($_POST['dbUser']));
    $dbPass = trim(htmlspecialchars($_POST['dbPass']));
    $account = trim(htmlspecialchars($_POST['account']));
    $password = trim(htmlspecialchars($_POST['password']));
    $adminEmail = trim(filter_var($_POST['admin_email'], FILTER_VALIDATE_EMAIL) ?: 'admin@example.com');
    $inputPassword = trim(htmlspecialchars($_POST['customPassword']));
    $productId = '1';
    $authMethod = $_POST['authMethod'];

    switch ($authMethod) {
        case 'password':
            if (empty($inputPassword)) {
                die('不能使用空密码，请返回重新输入！');
            }
            $customPassword = password_hash($inputPassword, PASSWORD_DEFAULT);
            $useTotp = false;
            $usePassword = true;
            break;
        case 'totp':
            $useTotp = true;
            $usePassword = false;
            $customPassword = '';
            break;
        case 'both':
            if (empty($inputPassword)) {
                die('不能使用空密码，请返回重新输入！');
            }
            $customPassword = password_hash($inputPassword, PASSWORD_DEFAULT);
            $useTotp = true;
            $usePassword = true;
            break;
        default:
            die('输入错误，请返回重新输入！');
    }

    if (isset($_POST['redis'])) {
        echo '启用Redis';
    } else {
        file_put_contents('redis.lock', 'redis is disable');
        echo "redis.lock 文件已创建。\n";
    }

    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_error) {
        die('数据库连接失败: ' . $mysqli->connect_error);
    }

    $sqlContent = file_get_contents('install.sql');
    if ($mysqli->multi_query($sqlContent)) {
        do {} while ($mysqli->next_result());
        
        $configFile = "<?php\nreturn [\n    'db' => [\n        'host' => '{$dbHost}',\n        'dbname' => '{$dbName}',\n        'user' => '{$dbUser}',\n        'pass' => '{$dbPass}'\n    ],\n    'auth' => [\n        'account' => '{$account}',\n        'password' => '{$password}',\n        'product_id' => '{$productId}'\n    ],\n    'mail' => [\n        'host' => '',\n        'port' => 465,\n        'username' => '',\n        'password' => '',\n        'from' => '{$adminEmail}',\n        'encryption' => 'ssl'\n    ]\n];";
        file_put_contents('config.php', $configFile);
        
        $config = require 'config.php';
        if (file_exists('qrcode.png')) {
            unlink('qrcode.png');
        }

        try {
            $pdo = new PDO("mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset=utf8", 
                          $config['db']['user'], $config['db']['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("数据库连接失败: " . $e->getMessage());
        }

        if ($useTotp) {
            $totp = TOTP::create();
            $totp->setLabel($adminEmail);
            $totp->setIssuer('TuanICP');
            $secret = $totp->getSecret();
            $qrCodeUrl = $totp->getProvisioningUri();

            $renderer = new GDLibRenderer(400);
            $writer = new Writer($renderer);
            file_put_contents('qrcode.png', $writer->writeString($qrCodeUrl));
        }

        if ($usePassword && !$useTotp) {
            $stmt = $pdo->prepare("INSERT INTO admin (email, password, password_enabled) VALUES (?, ?, ?)");
            $stmt->execute([$adminEmail, $customPassword, '1']);
        } elseif ($useTotp && !$usePassword) {
            $stmt = $pdo->prepare("INSERT INTO admin (email, totp_secret, totp_enabled) VALUES (?, ?, ?)");
            $stmt->execute([$adminEmail, $secret, '1']);
        } elseif ($usePassword && $useTotp) {
            $stmt = $pdo->prepare("INSERT INTO admin (email, password, totp_secret, totp_enabled, password_enabled) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$adminEmail, $customPassword, $secret, '1', '1']);
        }

        file_put_contents('install.lock', 'installed');
        echo "安装成功！请删除安装文件！\n";

        if ($useTotp && file_exists('qrcode.png')) {
            echo '<div><img src="qrcode.png"></div>';
            echo "<h2>请使用TOTP应用扫描二维码</h2>";
        }
        exit;
    } else {
        die("数据库安装失败: " . $mysqli->error);
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>系统安装向导</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        label { display: inline-block; width: 150px; margin-bottom: 10px; }
        input, select { padding: 8px; width: 300px; }
        #passwordDiv { margin-top: 15px; }
    </style>
</head>
<body>
    <h1>系统安装向导</h1>
    <form method="post">
        <label for="dbHost">数据库地址:</label>
        <input type="text" id="dbHost" name="dbHost" value="127.0.0.1" required><br>
        
        <label for="dbName">数据库名:</label>
        <input type="text" id="dbName" name="dbName" required><br>
        
        <label for="dbUser">数据库用户名:</label>
        <input type="text" id="dbUser" name="dbUser" required><br>
        
        <label for="dbPass">数据库密码:</label>
        <input type="password" id="dbPass" name="dbPass" required><br>
        
        <label for="admin_email">管理员邮箱:</label>
        <input type="email" id="admin_email" name="admin_email" value="admin@example.com" required><br>
        
        <label for="authMethod">认证方式:</label>
        <select id="authMethod" name="authMethod" required>
            <option value="password">密码认证</option>
            <option value="totp">TOTP认证</option>
            <option value="both">双重认证</option>
        </select><br>
        
        <div id="passwordDiv">
            <label for="customPassword">设置密码:</label>
            <input type="password" id="customPassword" name="customPassword">
        </div>
        
        <label><input type="checkbox" name="redis" value="1"> 启用Redis</label><br><br>
        
        <input type="submit" value="开始安装" style="padding: 10px 20px;">
    </form>

    <script>
        document.getElementById('authMethod').addEventListener('change', function() {
            document.getElementById('passwordDiv').style.display = 
                (this.value === 'totp') ? 'none' : 'block';
        });
    </script>
</body>
</html>