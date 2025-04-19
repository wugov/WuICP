<?php
//define('ABSPATH', dirname(__DIR__));
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
global $pdo;
// 创建PDO实例并设置错误模式
initDatabase();
// 查询数据库获取网站信息
$query = "SELECT * FROM website_info LIMIT 1";
$stmt = $pdo->query($query);
$websiteInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// 检查是否获取到了数据
if (!$websiteInfo) {
    die("网站信息不存在");
}
extract($websiteInfo); // 将数组键名作为变量名，将数组键值作为变量值
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';

// 查询最新的8个备案信息（审核通过）
$queryNewRecords = "SELECT icp_number, website_name FROM icp_records WHERE STATUS = '审核通过' ORDER BY id DESC LIMIT 8";
$stmtNewRecords = $pdo->query($queryNewRecords);
$newRecords = $stmtNewRecords->fetchAll(PDO::FETCH_ASSOC);

// 查询历史备案信息（除了最新的8个，且审核通过）
$queryOldRecords = "SELECT icp_number, website_name FROM icp_records WHERE STATUS = '审核通过' ORDER BY id DESC LIMIT 8, 9999";
$stmtOldRecords = $pdo->query($queryOldRecords);
$oldRecords = $stmtOldRecords->fetchAll(PDO::FETCH_ASSOC);
$addVars = [
    'current_time' => '',
    'icp_number' => '',
];

renderPage('publicicp');

