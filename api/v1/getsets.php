<?php
//define('ABSPATH', dirname(__DIR__));
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';
global $pdo;
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
if (!checkUserLogin()) {
    header("Location: login.php");
    exit();
}
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
/*
 * site_name
 * site_url
 * site_avatar
 * site_abbr
 * site_keywords
 * site_description
 * admin_nickname
 * admin_email
 * admin_qq
 * footer_code
 * audit_duration
 * feedback_link
 * background_image
 * enable_template_name
 */