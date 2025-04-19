<?php
// Include the database configuration file
function customHash($password, $salt)
{
    // 组合密码和盐
    $combined = $password . $salt;

    // 使用 SHA-256 哈希算法生成哈希值
    $hash = hash('sha256', $combined);

    // 返回生成的哈希值
    return $hash;
}

function customHashVerify($password, $salt)
{
    // 组合密码和盐
    $combined = $password . $salt;

    // 使用 SHA-256 哈希算法生成哈希值
    $hash = hash('sha256', $combined);

    // 返回生成的哈希值
    return $hash;
}

function custom_password_hash($password)
{
    // 生成一个随机的盐
    $salt = random_bytes(16);
    // 创建一个基于密码和盐的哈希
    $hash = crypt($password, '$2y$10$' . substr(strtr(base64_encode($salt), '+', '.'), 0, 22) . '$');

    // 返回盐和哈希的组合，以便存储
    return $hash;
}

function custom_password_verify($password, $storedHash)
{
    // 从存储的哈希中提取盐
    $salt = substr($storedHash, 0, 29);
    // 使用相同的盐和密码创建一个新的哈希
    $hash = crypt($password, $salt);

    // 比较新哈希和存储的哈希
    return hash_equals($storedHash, $hash);
}

function do_hash($password): string
{
    // 生成一个随机的22字符盐
    $salt = random_bytes(22);
    // 对密码进行哈希，并将盐附加到哈希值的末尾
    $passwordHash = customHash($password, ['salt' => $salt]);
    // 将盐和哈希值拼接，存入数据库的password_hash字段
    $storedHash = $passwordHash . bin2hex($salt);
    // 确保总长度不超过96个字符
    if (strlen($storedHash) === 96) {
        return $storedHash;
    } else {
        throw new Exception("Generated hash is too long for the database field.");
    }

}

function do_hash_old($password): string
{
    // 生成一个随机的22字符盐
    $salt = random_bytes(22);
    // 对密码进行哈希，并将盐附加到哈希值的末尾
    $passwordHash = password_hash($password, PASSWORD_DEFAULT, ['salt' => $salt]);
    // 将盐和哈希值拼接，存入数据库的password_hash字段
    $storedHash = $passwordHash . bin2hex($salt);
    // 确保总长度不超过96个字符
    if (strlen($storedHash) === 96) {
        return $storedHash;
    } else {
        throw new Exception("Generated hash is too long for the database field.");
    }

}

function verify_hash($password, $salt): string
{
    // 对密码进行哈希，并将盐附加到哈希值的末尾
    $passwordHash = customHash($password, ['salt' => $salt]);
    // 将盐和哈希值拼接，存入数据库的password_hash字段
    $storedHash = $passwordHash . bin2hex($salt);
    // 确保总长度不超过96个字符
    if (strlen($storedHash) > 96) {
        throw new Exception("Generated hash is too long for the database field.");
    }
    return $storedHash;
}

function verify_hash_old($password, $salt): string
{
    // 对密码进行哈希，并将盐附加到哈希值的末尾
    $passwordHash = password_hash($password, PASSWORD_DEFAULT, ['salt' => $salt]);
    // 将盐和哈希值拼接，存入数据库的password_hash字段
    $storedHash = $passwordHash . bin2hex($salt);
    // 确保总长度不超过96个字符
    if (strlen($storedHash) > 96) {
        throw new Exception("Generated hash is too long for the database field.");
    }
    return $storedHash;
}

function login($inputUsername, $inputPassword)
{
    // 这个函数根据用户名从数据库中获取密码哈希和盐
    $storedHash = getUserPasswordHashFromDatabase($inputUsername);

    // 检查是否获取到了密码哈希
    if (!$storedHash) {
        return false;
    }

    // 从存储的哈希中提取盐
    $salt = hex2bin(substr($storedHash, -44));
    // 提取密码哈希
    $passwordHash = substr($storedHash, 0, -44);

    // 使用提取的盐和用户输入的密码进行哈希，然后与存储的哈希值进行比较
    if (!$salt || !$passwordHash) { // 确保获取了盐和哈希
        return false;
    }
    $passwordHash = verify_hash($inputPassword, $salt);
    return password_verify($password, $passwordHash);
}

// 模拟从数据库获取密码哈希和盐的函数
function getUserPasswordHashFromDatabase($inputUsername)
{
    $config = require '../../config.php';

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
    // 这里应该是数据库查询代码，我们假设返回了存储的密码哈希和盐
    $stmt = $pdo->prepare("SELECT password_hash FROM admin WHERE username = :username");
    $stmt->execute(['username' => $inputUsername]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
