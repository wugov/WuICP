<?php
//define('ABSPATH', dirname(__DIR__));
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';
require_once '../vendor/autoload.php';
global $pdo;
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
if (!checkUserLogin()) {
    header("Location: login.php");
    exit();
}
initDatabase(); ?>
<div id="sidebar"><?php include 'sidebar.php'; ?></div>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>ICP Records Backend</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/settings.css">
</head>
<body>
<div style="position: absolute;right: 20px">
    <h2>emm还没做</h2>
</div>