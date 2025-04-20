<?php
//define('ABSPATH', dirname(__DIR__));
// install.php
require 'vendor/autoload.php';

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use OTPHP\TOTP;

if (file_exists('install.lock')) {
    echo "install.lock 系统已安装，请勿重复安装。如发生错误/需要重新安装，请删除 install.lock \n";
    exit; // 退出脚本
}
if (file_exists('redis.lock')) {
    unlink('redis.lock');
}
if (file_exists('qrcode.png')) {
    unlink('qrcode.png');
}
// 检查是否已经提交了表单
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取用户输入的数据库连接信息
    $dbHost = trim(filter_var($_POST['dbHost'] ?? '127.0.0.1', FILTER_VALIDATE_IP) ?: '127.0.0.1');
    $dbName = trim(htmlspecialchars($_POST['dbName']));
    $dbUser = trim(htmlspecialchars($_POST['dbUser']));
    $dbPass = trim(htmlspecialchars($_POST['dbPass']));
    $account = trim(htmlspecialchars($_POST['account']));
    $password = trim(htmlspecialchars($_POST['password']));
    $adminEmail = trim(filter_var($_POST['admin_email'], FILTER_VALIDATE_EMAIL) ?: 'admin@example.com');
    $inputPassword = trim(htmlspecialchars($_POST['customPassword']));
    $productId = '1'; // 默认产品ID为1，不显示在表单中
    $authMethod = $_POST['authMethod'];
    // 处理不同认证方法
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

    // 尝试连接到数据库
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    // 检查连接是否成功
    if ($mysqli->connect_error) {
        die('数据库连接失败: ' . $mysqli->connect_error);
    }

    // 读取 SQL 文件内容
    $sqlContent = file_get_contents('install.sql');

    // 执行 SQL 语句
    if ($mysqli->multi_query($sqlContent)) {
        // 等待所有查询执行完成
        do {
            // 无需进一步处理结果
        } while ($mysqli->next_result());
        echo "创建config.php文件...\n";
        // 创建 config.php 文件
        $configFile = "<?php\n// config.php\nreturn [\n    'db' => [\n        'host' => '{$dbHost}',\n        'dbname' => '{$dbName}',\n        'user' => '{$dbUser}',\n        'pass' => '{$dbPass}'\n    ],\n    'auth' => [\n        'account' => '{$account}',\n        'password' => '{$password}',\n        'product_id' => '{$productId}'\n    ]\n];";
        file_put_contents('config.php', $configFile);
        echo "config.php 文件已创建。\n";
        $config = require 'config.php';
        if (file_exists('qrcode.png')) {
            unlink('qrcode.png');
        }
// Database connection
        $host = $config['db']['host'];
        $dbname = $config['db']['dbname'];
        $user = $config['db']['user'];
        $pass = $config['db']['pass'];

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Could not connect to the database: " . $e->getMessage());
        }

        if ($useTotp && !$usePassword) { // 生成一个新的TOTP密钥
            $totp = TOTP::create();
            $totp->setLabel($adminEmail);
            $totp->setIssuer('TuanICP');// 获取密钥和二维码URL
            $secret = $totp->getSecret();
            $qrCodeUrl = $totp->getProvisioningUri();// 将TOTP密钥和用户信息存储到数据库
            $query = "INSERT INTO admin (email, totp_secret ,totp_enabled) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$adminEmail, $secret, '1']);// 创建 GDLibRenderer 实例，设置二维码大小
            $renderer = new GDLibRenderer(400);// 创建 Writer 实例
            $writer = new Writer($renderer);// 生成并保存二维码图片
            $qrcode_image = $writer->writeString($qrCodeUrl);// 将 QRCode URL 转换为图像
            file_put_contents('qrcode.png', $qrcode_image);// 保存为 qrcode.png 文件
        }
        if ($usePassword && !$useTotp) {
            $query = "INSERT INTO admin (email, password, password_enabled) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$adminEmail, $customPassword, '1']);
        }
        if ($usePassword && $useTotp) {
            $totp = TOTP::create();
            $totp->setLabel($adminEmail);
            $totp->setIssuer('TuanICP');// 获取密钥和二维码URL
            $secret = $totp->getSecret();
            $qrCodeUrl = $totp->getProvisioningUri();// 将TOTP密钥和用户信息存储到数据库
            $query = "INSERT INTO admin (email, password, totp_secret, totp_enabled, password_enabled) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$adminEmail, $customPassword, $secret, '1', '1']);
            $renderer = new GDLibRenderer(400);// 创建 Writer 实例
            $writer = new Writer($renderer);// 生成并保存二维码图片
            $qrcode_image = $writer->writeString($qrCodeUrl);// 将 QRCode URL 转换为图像
            file_put_contents('qrcode.png', $qrcode_image);// 保存为 qrcode.png 文件
        }

        echo "安装成功，请删除安装文件！请前往phpMyAdmin进入数据库或网站后台修改网站配置信息！\n";
        if ($useTotp === true) {
            // 设置HTTP缓存控制头，确保在发送任何输出之前设置
            header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
            header("Pragma: no-cache"); // HTTP 1.0.
            header("Expires: 0"); // Proxies.

            if (file_exists('qrcode.png')) {
                echo '<div><img src="qrcode.png"></div>';
                echo "<h2>二维码已生成，请使用TOTP应用扫描二维码绑定。如果没有TOTP应用，请自行寻找并下载安装“Free OTP”。</h2><br>
              <h3>此应用是免费且开源的，并且Android（安卓）和IOS（苹果）平台都支持。</h3>\n";
            } else {
                if (isset($qrCodeUrl) && isset($secret)) {
                    echo "<h2>二维码生成失败，请手动将TOTP的URL生成为二维码导入到设备，或使用TOTP密钥手动输入：</h2><br>";
                    echo "<h3>TOTP的URL为：<a href='$qrCodeUrl'>$qrCodeUrl</a></h3><br>";
                    echo "<h3>TOTP密钥为：</h3><br>";
                    echo "<textarea>$secret</textarea>";
                } else {
                    echo "TOTP密钥为空。环境可能未被正确设置，请检查！";
                    phpinfo();
                    exit;
                }
            }
        }

        // 逻辑代码执行完毕后，创建 install.lock 文件
        file_put_contents('install.lock', 'installed');
        echo "install.lock 文件已创建。\n";
        exit;
    } else {
        // 输出错误信息
        $errorInfo = $mysqli->error;

        // 检查错误信息中是否包含“Access denied”来确定是否是用户名或密码错误
        if (strpos($errorInfo, "Access denied") !== false) {
            echo "数据库安装失败。错误信息：用户名或密码错误。";
        } else {
            // 如果不是用户名或密码错误，显示其他错误信息
            echo "数据库安装失败。错误信息：" . $errorInfo;
        }

    }

    // 关闭数据库连接
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>数据库安装</title>
    <link rel="stylesheet" href="css/install.css">
</head>
<body>
<h1>数据库安装</h1>
<form action="install.php" method="post">
    <label for="dbHost">数据库地址:</label>
    <input type="text" id="dbHost" name="dbHost" value="127.0.0.1"><br><br>
    <label for="dbName">数据库名:</label>
    <input type="text" id="dbName" name="dbName" required><br><br>
    <label for="dbUser">数据库用户名:</label>
    <input type="text" id="dbUser" name="dbUser" required><br><br>
    <label for="dbPass">数据库密码:</label>
    <input type="password" id="dbPass" name="dbPass" required><br><br>
    <label for="account">授权账号:</label>
    <input type="text" id="account" name="account" required><br><br>
    <label for="password">授权密码:</label>
    <input type="password" id="password" name="password" required><br><br>
    <label for="redis">是否启用Redis:</label>
    <input type="checkbox" id="redis" name="redis" value="1"><br><br>
    <label for="admin_email">管理员邮箱:</label>
    <input type="text" name="admin_email" value="admin@example.com" required><br><br>
    <label for="authMethod">密码类型:</label>
    <p>不懂或不清楚什么意思，请选择“传统密码”</p>
    <select id="authMethod" name="authMethod" required>
        <option value="password">传统密码</option>
        <option value="totp">TOTP</option>
        <option value="both">同时支持</option>
    </select><br><br>
    <div id="passwordDiv" style="display:block;">
        <label for="customPassword">自定义密码:</label>
        <input type="password" id="customPassword" name="customPassword">
    </div>
    <div style="display:block;">
    <label for="testData">测试数据：</label>
    <p>测试数据用于快速测试网站功能。完整数据是直接将开发环境的数据库dump下来。</p>
    <p>精简数据是从开发环境导出的数据，仅包含部分必要数据。</p>
    <p>不导入测试数据将仅创建数据库结构，不导入任何数据。</p>
    <p>此选项目前没用。</p>
    <select id="testData" name="testData" required>
        <option value="full">完整测试数据</option>
        <option value="must">精简测试数据</option>
        <option value="none">不导入测试数据</option>
    </select>
    </div>
    <input type="submit" value="安装">
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('authMethod').addEventListener('input', function () {
            var authMethod = this.value;
            var passwordDiv = document.getElementById('passwordDiv');
            if (authMethod === 'password' || authMethod === 'both') {
                passwordDiv.style.display = 'block';
            } else {
                passwordDiv.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>
