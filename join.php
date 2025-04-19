<?php
//define('ABSPATH', dirname(__DIR__));
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
global $pdo;
// 开启输出缓冲
ob_start();
// 创建PDO实例并设置错误模式
initDatabase();
$query = "SELECT * FROM website_info LIMIT 1";
$stmt = $pdo->query($query);
$websiteInfo = $stmt->fetch(PDO::FETCH_ASSOC);


// 检查是否获取到了数据
if (!$websiteInfo) {
    die("网站信息不存在");
}
extract($websiteInfo); // 将数组键名作为变量名，将数组键值作为变量值

// 检查是否有POST请求
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取用户输入的备案号并清理
    $icp_number = isset($_POST['icp_number']) ? trim($_POST['icp_number']) : '';

    // 查询数据库以检查备案号是否已被占用
    $sql = "SELECT COUNT(*) FROM icp_records WHERE icp_number = :icp_number";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['icp_number' => $icp_number]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // 如果备案号已被占用，则使用JavaScript弹窗提示用户
        echo '<script type="text/javascript">alert("备案号已被占用，请重新填写。");</script>';
    } else {
        // 如果备案号未被占用
        echo "<script type='text/javascript'>";
        echo "alert('恭喜，该备案号可以注册！');";
        echo "window.location.href = 'reg.php?number=" . urlencode($icp_number) . "';";
        echo "</script>";
        exit(); // 确保脚本在这里终止

    }
}

ob_end_flush(); // 发送输出缓冲并关闭输出缓冲
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';
renderPage('join');

?>

