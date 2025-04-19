<?php
//define('ABSPATH', dirname(__DIR__));
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
global $pdo;

// 获取URL参数 keyword
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

// 创建PDO实例并设置错误模式
initDatabase();
// 防止数据库注入：使用预处理语句
$sql = "SELECT * FROM icp_records WHERE icp_number = :keyword OR website_url = :keyword";
$stmt = $pdo->prepare($sql);
$stmt->execute(['keyword' => $keyword]);
$icp_record = $stmt->fetch(PDO::FETCH_ASSOC);

// 检查备案信息是否存在
if (!$icp_record) {
    // 备案信息不存在，弹窗提示并重定向
    echo '<script type="text/javascript">';
    echo 'alert("备案号不存在");';
    echo 'window.location.href="/";';
    echo '</script>';
    exit;
}

// 新增状态检查逻辑
if ($icp_record['STATUS'] === '审核通过') {
    // 使用JavaScript跳转避免header冲突
    echo '<script type="text/javascript">';
    echo 'window.location.href = "/id.php?keyword=' . $icp_record['icp_number'] . '";';
    echo '</script>';
    exit;
}

$query = "SELECT * FROM website_info LIMIT 1";
$stmt = $pdo->query($query);
$websiteInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// 检查是否获取到了数据
if (!$websiteInfo) {
    die("网站信息不存在");
}
extract($websiteInfo);

$addVars = [
    'icp_status' => $icp_record['STATUS'],
    'icp_number' => $icp_record['icp_number'],
];

renderPage('xg', $addVars);