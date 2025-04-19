<?php
//define('ABSPATH', dirname(__DIR__));
// 禁用浏览器缓存
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
global $pdo;
initDatabase();
    // 从数据库中选取审核通过的网址
    $stmt = $pdo->prepare("SELECT website_url FROM icp_records WHERE STATUS = '审核通过'");
    $stmt->execute();

    // 获取所有审核通过的网址
    $urls = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $urlsJson = json_encode($urls);

$addVars = [
    'urlsJson' => $urlsJson,
];

renderPage('goto' , $addVars);

