<?php
//define('ABSPATH', dirname(__DIR__));
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
global $pdo;

initDatabase();
// 获取URL参数 keyword
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

// 查询备案信息
// 使用 OR 逻辑来查询备案号或域名
$sql = "SELECT * FROM icp_records WHERE icp_number = :keyword OR website_url LIKE :urlPattern";
$stmt = $pdo->prepare($sql);
$urlPattern = "%{$keyword}%"; // 用于模糊匹配URL
$stmt->execute(['keyword' => $keyword, 'urlPattern' => $urlPattern]);
$icp_record = $stmt->fetch(PDO::FETCH_ASSOC);

// 如果没有找到记录，则弹窗提示并跳转
if (!$icp_record) {
    // 使用单行注释
    echo "<script>alert('没有找到对应的ICP备案信息。');</script>";
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

// 检查备案状态是否为“审核通过”
if ($icp_record['STATUS'] !== '审核通过') {
    // 如果状态不是“审核通过”，则弹窗提示用户
    echo "<script type='text/javascript'>";
    echo "alert('该备案信息未通过审核');";
    echo "window.location.href = 'xg.php?keyword=" . urlencode($keyword) . "';";
    echo "</script>";
    exit; // 终止脚本执行
}
$query = "SELECT * FROM website_info LIMIT 1";
$stmt = $pdo->query($query);
$websiteInfo = $stmt->fetch(PDO::FETCH_ASSOC);


// 检查是否获取到了数据
if (!$websiteInfo) {
    die("网站信息不存在");
}
extract($websiteInfo); // 将数组键名作为变量名，将数组键值作为变量值


include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';

$addVars = [
        'user_icp_number' => $icp_record['icp_number'],
        'user_website_url' => $icp_record['website_url'],
        'user_website_name' => $icp_record['website_name'],
        'owner' => $icp_record['owner'],
    'status' => $icp_record['STATUS'],
    'user_website_info' => $icp_record['website_info'],
    'update_time' => $icp_record['update_time'],
];

renderPage('id' , $addVars);
