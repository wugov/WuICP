<?php
//define('ABSPATH', dirname(__DIR__));
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';

initDatabase();
global $pdo;
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';

// 获取URL参数 keyword
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

// 查询备案信息
$sql = "SELECT * FROM icp_records WHERE icp_number = :keyword OR website_url LIKE :urlPattern";
$stmt = $pdo->prepare($sql);
$urlPattern = "%{$keyword}%";
$stmt->execute(['keyword' => $keyword, 'urlPattern' => $urlPattern]);
$icp_record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$icp_record) {
    echo "<script>alert('没有找到对应的ICP备案信息。');</script>";
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

if ($icp_record['STATUS'] !== '审核通过') {
    echo "<script type='text/javascript'>";
    echo "alert('该备案信息未通过审核');";
    echo "window.location.href = 'xg.php?keyword=" . urlencode($keyword) . "';";
    echo "</script>";
    exit;
}

$icp_number = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$current_time = date('Y-m-d H:i:s');
$icp_number = preg_replace('/[^a-zA-Z0-9]/', '', $icp_number);

$query = "SELECT * FROM website_info LIMIT 1";
$stmt = $pdo->query($query);
$websiteInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$websiteInfo) {
    die("网站信息不存在");
}
extract($websiteInfo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $website_name = trim($_POST['website_name']);
    $website_url = trim($_POST['website_url']);
    $website_info = trim($_POST['website_info']);
    $icp_number = trim($_POST['icp_number']);
    $owner = trim($_POST['owner']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $qq = trim($_POST['qq']);
    $security_code = trim($_POST['passwd']);

    if (empty($website_name) || empty($website_url) || empty($icp_number) || empty($owner)) {
        echo "<script>alert('请填写所有必填字段');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('无效的邮箱地址');</script>";
    } else {
        $check_icp_number_sql = "SELECT COUNT(*), (SELECT security_code FROM icp_records WHERE icp_number = :icp_number LIMIT 1) as security_code FROM icp_records WHERE icp_number = :icp_number";
        $check_icp_number_stmt = $pdo->prepare($check_icp_number_sql);
        $check_icp_number_stmt->bindParam(':icp_number', $icp_number);
        $check_icp_number_stmt->execute();
        $icp_number_exists = $check_icp_number_stmt->fetch(PDO::FETCH_ASSOC);

        if ($icp_number_exists && $icp_number_exists['COUNT(*)'] > 0 && $icp_number_exists['security_code'] !== $security_code) {
            echo "<script>alert('安全码不正确');window.location.href = 'change.php';</script>";
            exit;
        }

        if (!$icp_number_exists || $icp_number_exists['COUNT(*)'] == 0) {
            echo "<script>alert('备案号还没被注册，有疑问请联系{$site_name}ICP备案中心'); window.location.href = 'join.php';</script>";
            exit;
        }

        $sql = "INSERT INTO icp_records_change (website_name, website_url, website_info, icp_number, owner, update_time, email, qq) 
                VALUES (:website_name, :website_url, :website_info, :icp_number, :owner, NOW(), :email, :qq)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':website_name', $website_name);
        $stmt->bindParam(':website_url', $website_url);
        $stmt->bindParam(':website_info', $website_info);
        $stmt->bindParam(':icp_number', $icp_number);
        $stmt->bindParam(':owner', $owner);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':qq', $qq);

        $stmt->execute();

        echo "<script>alert('您的申请已提交，请耐心等待审核！');";
        echo "window.location.href = 'xg.php?keyword=" . htmlspecialchars($icp_number) . "';</script>";
        exit;
    }
}

$addVars = [
    'current_time' => $current_time,
    'icp_number' => $icp_number,
    'website_name' => $icp_record['website_name'],
    'website_url' => str_replace(['http://','https://'], '', $icp_record['website_url']),
    'website_info' => $icp_record['website_info'],
    'owner' => $icp_record['owner'],
    'email' => $icp_record['email'],
    'qq' => $icp_record['qq'],
    'site_name' => $site_name
];

renderPage('changeinfo', $addVars);
?>