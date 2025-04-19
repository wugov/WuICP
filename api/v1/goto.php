<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
global $pdo;
initDatabase();

// 从数据库中选取审核通过的网址
$stmt = $pdo->prepare("SELECT website_url FROM icp_records WHERE STATUS = '审核通过'");
$stmt->execute();

// 获取所有审核通过的网址
$urls = $stmt->fetchAll(PDO::FETCH_COLUMN);
// 随机选择一个URL
$randomUrl = $urls[array_rand($urls)];

// 设置响应头为JSON格式
header('Content-Type: application/json');

// 返回JSON格式数据并保持中文原样显示
echo json_encode(['url' => $randomUrl], JSON_UNESCAPED_UNICODE);