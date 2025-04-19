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
extract($websiteInfo); // 将数组转换为变量
/*
 * 转换后的变量：
 * site_name 网站名
 * site_url 网站URL
 * site_avatar 网站图标
 * site_abbr 网站简称
 * site_keywords 网站SEO关键字
 * site_description 网站SEO描述
 * admin_nickname 管理员昵称
 * admin_email 管理员邮箱
 * admin_qq 管理员QQ
 * footer_code 页脚代码
 * audit_duration 审核时长
 * feedback_link 反馈链接
 * background_image 背景图片
 * enable_template_name 启用的模板文件夹名
 */
// 提交表单（更新设置），用来接收输入的参数
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $new_footer_code = $_POST['footer'] ?? ''; //页脚内容
    $new_website_name = $_POST['website_name'] ?? ''; //网站名
    $new_website_url = $_POST['website_url'] ?? ''; //网站url
    $new_website_avatar = $_POST['website_avatar'] ?? ''; //网站图标
    $new_website_abbr = $_POST['website_abbr'] ?? ''; //站点简称
    $new_website_keywords = $_POST['website_keywords'] ?? ''; //站点SEO关键字
    $new_website_description = $_POST['website_description'] ?? ''; //站点SEO描述
    $new_website_admin_nickname = $_POST['website_admin_nickname'] ?? ''; //站点管理员昵称
    $new_website_admin_email = $_POST['website_admin_email'] ?? ''; //站点管理员邮箱
    $new_website_admin_qq = $_POST['website_admin_qq'] ?? ''; //站点管理员QQ
    $new_website_feedback_link = $_POST['website_feedback_link'] ?? ''; //站点反馈链接
    $new_website_bg = $_POST['website_bg_image'] ?? ''; //站点背景图片
    $new_enable_template_name = $_POST['enable_template_name'] ?? ''; // 启用主题的文件夹名

    if (isset($_POST['redis_sw'])) {
        if (file_exists(dirname(__FILE__) . '/../redis.lock')) {
            unlink(dirname(__FILE__) . '/../redis.lock');
        }
    } else {
        file_put_contents(dirname(__FILE__) . '/../redis.lock', 'redis is disable');
    }

    // Prepare the SQL statement
    $sql = "UPDATE website_info SET footer_code = :footer , site_name = :website_name , site_url = :site_url , 
                        site_avatar = :site_avatar , site_abbr = :site_abbr , 
                        site_keywords = :site_keywords , site_description = :site_description , 
                        admin_nickname = :admin_nickname , admin_email = :admin_email , 
                        admin_qq = :admin_qq , feedback_link = :website_feedback_link , 
                        background_image = :website_bg_image , enable_template_name = :enable_template_name  WHERE 1 LIMIT 1";
    $stmt = $pdo->prepare($sql);

    // Bind the parameter
    $stmt->bindParam(':footer', $new_footer_code);
    $stmt->bindParam(':website_name', $new_website_name);
    $stmt->bindParam(':site_url', $new_website_url);
    $stmt->bindParam(':site_avatar', $new_website_avatar);
    $stmt->bindParam(':site_abbr', $new_website_abbr);
    $stmt->bindParam(':site_keywords', $new_website_keywords);
    $stmt->bindParam(':site_description', $new_website_description);
    $stmt->bindParam(':admin_nickname', $new_website_admin_nickname);
    $stmt->bindParam(':admin_email', $new_website_admin_email);
    $stmt->bindParam(':admin_qq', $new_website_admin_qq);
    $stmt->bindParam(':website_feedback_link', $new_website_feedback_link);
    $stmt->bindParam(':website_bg_image', $new_website_bg);
    $stmt->bindParam(':enable_template_name', $new_enable_template_name);

    // Execute the statement
    if ($stmt->execute()) {
        // 准备JSON响应
        $response = [
            'code' => 0,
            'status' => 'success',
            'count' => '',
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];

        // 设置响应头
        header('Content-Type: application/json; charset=utf-8');

        // 输出JSON
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        // 准备JSON响应
        $response = [
            'code' => 1,
            'status' => 'error',
            'message' => '更新失败',
            'count' => '',
            'data' => ''
        ];

        // 设置响应头
        header('Content-Type: application/json; charset=utf-8');

        // 输出JSON
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}


