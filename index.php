<?php
//define('ABSPATH', dirname(__DIR__));
require_once 'lib/function.php';

if (!file_exists('install.lock')) {
    // 当 install.lock 不存在时，表示系统未安装
    echo "系统未安装，请完成安装流程。如需重新安装，请确保创建 install.lock 文件。\n";
    
    // 修正跳转逻辑：添加参数名并正确拼接URL
    $redirectUrl = 'install.php' . urlencode($keyword);
    header("Location: $redirectUrl");
    exit; // 退出脚本
}

// 创建PDO实例并设置错误模式
initDatabase();
global $pdo;
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

// 检查是否有GET请求，并处理备案查询
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];

    // 查询备案信息
    $sql = "SELECT * FROM icp_records WHERE icp_number = :keyword OR website_url LIKE :urlPattern";
    $stmt = $pdo->prepare($sql);
    $urlPattern = "%{$keyword}%"; // 用于模糊匹配URL
    $stmt->execute(['keyword' => $keyword, 'urlPattern' => $urlPattern]);
    $icp_record = $stmt->fetch(PDO::FETCH_ASSOC);

    // 如果没有找到记录，则显示提示信息
    if (!$icp_record) {
        $noRecordMessage = "喵喵：未查询到该备案记录";
    } else {
        // 如果找到记录，则跳转到id.php
        header("Location: id.php?keyword=" . urlencode($keyword));
        exit;
    }
}

$addVars = [
    'noRecordMessage' => $noRecordMessage ?? '',
];

renderPage('index', $addVars);

?>
