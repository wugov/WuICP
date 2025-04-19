<?php
//define('ABSPATH', dirname(__DIR__));
// 引入配置文件
$config = require $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// 从配置文件中获取数据库连接参数
$host = $config['db']['host'];
$dbname = $config['db']['dbname'];
$user = $config['db']['user'];
$pass = $config['db']['pass'];
$salt = $config['salt'];

// 创建PDO实例并设置错误模式
initDatabase();
// Retrieve the current website info
$query = "SELECT * FROM website_info LIMIT 1";
$stmt = $pdo->query($query);
$websiteInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if we got the data
if (!$websiteInfo) {
    die("网站信息不存在");
}
extract($websiteInfo); // Extract the array keys as variable names and values

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 清理和验证输入
    $username = trim($_POST['username']);
    $email = trim($_POST['email']); // 假设使用 email 作为用户名
    $password = trim($_POST['password']);

    // 验证输入
    if (empty($username) || empty($email) || empty($password)) {
        echo "<script>alert('请填写所有必填字段');</script>";
        exit;
    }

    // 检查邮箱是否已存在
    $check_email_sql = "SELECT COUNT(*) FROM users WHERE email = :email";
    $check_email_stmt = $pdo->prepare($check_email_sql);
    $check_email_stmt->bindParam(':email', $email);
    $check_email_stmt->execute();
    $email_exists = $check_email_stmt->fetchColumn();

    if ($email_exists) {
        // 邮箱已存在
        echo "<script>alert('邮箱已存在，请使用其他邮箱注册');</script>";
        exit;
    }
    // 检查用户名是否已存在
    $check_username_sql = "SELECT COUNT(*) FROM users WHERE username = :username";
    $check_username_stmt = $pdo->prepare($check_username_sql);
    $check_username_stmt->bindParam(':username', $username);
    $check_username_stmt->execute();
    $username_exists = $check_username_stmt->fetchColumn();

    if ($username_exists) {
        // 用户名已存在
        echo "<script>alert('用户名已存在，请使用其他用户名注册');</script>";
        exit;
    }

    // 哈希密码

    $password_hash = hash_hmac('sha256', $password, $salt);


    // 插入新用户信息到数据库
    $insert_user_sql = "INSERT INTO users (username,email, password_hash, created_at) VALUES (:username, :email, :`password_hash`, CURRENT_TIMESTAMP)";
    $insert_user_stmt = $pdo->prepare($insert_user_sql);
    $insert_user_stmt->bindParam(':username', $username);
    $insert_user_stmt->bindParam(':email', $email);
    $insert_user_stmt->bindParam(':password_hash', $password_hash);

    if ($insert_user_stmt->execute()) {
        // 注册成功
        echo "<script>alert('注册成功！');</script>";
    } else {
        // 注册失败
        echo "<script>alert('注册失败，请稍后再试');</script>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册</title>
    <link rel="stylesheet" href="css/register.css">
    <link rel="stylesheet" href="/templates/default/css/footer.css">
</head>
<body>
<script>
    function checkForm() {
        // 获取表单数据
        var username = document.getElementById("username").value;
        var email = document.getElementById("email").value;
        var password = document.getElementById("password").value;
        var confirmPassword = document.getElementById("confirmPassword").value;
        if (email === "" || password === "" || confirmPassword === "") {
            alert("请填写所有必填字段");
            return false;
        }
        if (password !== confirmPassword) {
            alert("两次输入的密码不一致");
            return false;
        }
        return true;
    }
</script>
<div class="container">
    <div class="header">
        <h1>{{site_name}}ICP备案注册</h1>
    </div>
    <div class="reg-box">
        <form action="register.php" method="post" id="regForm">
            <input type="text" id="username" name="username" class="input" placeholder="用户名" required><br>
            <input type="email" id="email" name="email" class="input" placeholder="邮箱" required><br>
            <input type="password" id="password" name="password" class="input" placeholder="密码" required><br>
            <input type="password" id="confirmPassword" name="confirmPassword" class="input" placeholder="确认密码"
                   required><br>
            <button type="submit" class="tj">提交备案</button>
        </form>
    </div>
    <p>xxx<br></p>
</div>
<div class="footer">
    {{footer_code_raw}}
</div>
</body>
</html>

