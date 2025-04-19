<?php
//define('ABSPATH', dirname(__DIR__));
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'; // 引入PHPMailer
global $pdo;
initDatabase();
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';

// 获取网站配置
$config = require $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$mailConfig = $config['mail'];

// 检查是否有GET参数number
$icp_number = isset($_GET['number']) ? trim($_GET['number']) : '';

// 定义当前时间
$current_time = date('Y-m-d H:i:s');

// 确保备案号只包含字母和数字
$icp_number = preg_replace('/[^a-zA-Z0-9]/', '', $icp_number);

$query = "SELECT * FROM website_info LIMIT 1";
$stmt = $pdo->query($query);
$websiteInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// 检查是否获取到了数据
if (!$websiteInfo) {
    die("网站信息不存在");
}
extract($websiteInfo);

// 获取当前域名
$currentDomain = $_SERVER['HTTP_HOST'];
$baseUrl = 'https://' . $currentDomain;

// 如果表单被提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 清理和验证输入
    $website_name = trim($_POST['website_name']);
    $website_url = trim($_POST['website_url']);
    $website_info = trim($_POST['website_info']);
    $icp_number = trim($_POST['icp_number']);
    $owner = trim($_POST['owner']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $qq = trim($_POST['qq']);
    $security_code = trim($_POST['security_code']);

    // 验证输入
    if (empty($website_name) || empty($website_url) || empty($icp_number) || empty($owner) || empty($security_code)) {
        echo "<script>alert('请填写所有必填字段');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('无效的邮箱地址');</script>";
    } else {
        // 检查备案号是否已存在
        $check_icp_number_sql = "SELECT COUNT(*) FROM icp_records WHERE icp_number = :icp_number";
        $check_icp_number_stmt = $pdo->prepare($check_icp_number_sql);
        $check_icp_number_stmt->bindParam(':icp_number', $icp_number);
        $check_icp_number_stmt->execute();
        $icp_number_exists = $check_icp_number_stmt->fetch(PDO::FETCH_ASSOC);

        // 检查域名是否已存在
        $check_website_url_sql = "SELECT COUNT(*) FROM icp_records WHERE website_url = :website_url";
        $check_website_url_stmt = $pdo->prepare($check_website_url_sql);
        $check_website_url_stmt->bindParam(':website_url', $website_url);
        $check_website_url_stmt->execute();
        $website_url_exists = $check_website_url_stmt->fetchColumn() > 0;

        if (!preg_match('/^\d{8}$/', $icp_number)) {
            echo "<script>alert('备案号必须是8位纯数字，早就猜到你会瞎改参数。'); window.location.href = 'join.php';</script>";
            exit;
        }

        if ($icp_number_exists && $icp_number_exists['COUNT(*)'] > 0) {
            echo "<script>alert('备案号被抢先注册啦，有疑问请联系{$site_name}ICP备案中心'); window.location.href = 'join.php';</script>";
            exit;
        }

        if ($website_url_exists) {
            echo "<script>alert('域名已经登记过啦，有疑问请联系{$site_name}ICP备案中心'); window.location.href = 'join.php';</script>";
            exit;
        } else {
            // 调用API检查
            $apiUrl = $baseUrl . "/api/bot.php?url=".urlencode($website_url)."&number=".urlencode($icp_number);
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                // cURL请求失败（如超时、网络错误）
                error_log('CURL Error: ' . curl_error($ch));
                $status = '待审核'; // 默认状态
            } else {
                // cURL请求成功，解析API响应
                $responseText = trim($response);
                $status = ($responseText === 'true') ? '审核通过' : '待审核';
            }
            curl_close($ch);
            
            // 插入数据库
            $sql = "INSERT INTO icp_records (website_name, website_url, website_info, icp_number, owner, update_time, email, qq, security_code, status) 
                    VALUES (:website_name, :website_url, :website_info, :icp_number, :owner, NOW(), :email, :qq, :security_code, :status)";
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':website_name', $website_name);
            $stmt->bindParam(':website_url', $website_url);
            $stmt->bindParam(':website_info', $website_info);
            $stmt->bindParam(':icp_number', $icp_number);
            $stmt->bindParam(':owner', $owner);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':qq', $qq);
            $stmt->bindParam(':security_code', $security_code);
            $stmt->bindParam(':status', $status);
            
            $stmt->execute();

            // ================== 邮件通知逻辑 ==================
            $link1 = $baseUrl . "/xg.php?keyword=" . urlencode($icp_number);
            $link2 = $baseUrl . "/id.php?keyword=" . urlencode($icp_number);
            
            // 构造消息内容
            if ($status === '审核通过') {
                $messageContent = "新备案申请已被巡查机器人自动通过：\n网站名称：{$website_name}\n域名：{$website_url}\n备案号：{$icp_number}\n申请人：{$owner}\n邮箱：{$email}\nQQ：{$qq}\n安全码: ***";
            } else {
                $messageContent = "新备案申请：\n网站名称：{$website_name}\n域名：{$website_url}\n备案号：{$icp_number}\n申请人：{$owner}\n邮箱：{$email}\nQQ：{$qq}\n安全码: ***\n机器人未检测到悬挂标识，请管理员人工审查！";
            }
            
            if ($status === '审核通过') {
                $subject = "备案申请审核通过通知";
                $message = "<html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
                        h1 { color: #4CAF50; }
                        p { margin: 10px 0; }
                        a { color: #007BFF; text-decoration: none; }
                        a:hover { text-decoration: underline; }
                        .footer { margin-top: 20px; font-size: 12px; color: #777; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h1>备案申请审核通过</h1>
                        <p>尊敬的{$site_name}市民 {$owner},您好！</p>
                        <p>您的备案申请已通过自动审核，备案号：{$icp_number}。</p>
                        <p>您可以访问以下链接查看备案信息：<a href='{$link2}' target='_blank'>{$link2}</a></p>
                        <p>感谢您使用{$site_name}ICP备案服务。</p>
                        <div class='footer'>此邮件为系统自动发送，请勿直接回复。</div>
                    </div>
                </body>
                </html>";
            } else {
                $subject = "备案申请已提交";
                $message = "<html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
                        h1 { color: #FFA000; }
                        p { margin: 10px 0; }
                        a { color: #007BFF; text-decoration: none; }
                        a:hover { text-decoration: underline; }
                        .footer { margin-top: 20px; font-size: 12px; color: #777; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h1>备案申请已提交</h1>
                        <p>尊敬的{$site_name}市民 {$owner},您好！</p>
                        <p>您的备案申请已成功提交，正在等待审核。</p>
                        <p>审核通过后，您将收到通知邮件。您也可以随时访问以下链接查询审核状态：<a href='{$link1}' target='_blank'>{$link1}</a></p>
                        <div class='footer'>此邮件为系统自动发送，请勿直接回复。</div>
                    </div>
                </body>
                </html>";
            }
            
            // 调用邮件发送函数
            sendEmail($email, $subject, $message, true);

            // ================== 原有提示逻辑 ==================
            if ($status === '审核通过') {
                echo "<script>alert('恭喜，您的备案已自动通过！');";
                echo "window.location.href = 'id.php?keyword=" . htmlspecialchars($icp_number) . "';</script>";
                exit;
            } else {
                echo "<script>alert('您的申请已提交，请耐心等待审核！');";
            }
            echo "window.location.href = 'xg.php?keyword=" . htmlspecialchars($icp_number) . "';</script>";
            exit;
        }
    }
}

$addVars = [
    'current_time' => $current_time,
    'icp_number' => $icp_number,
];

renderPage('reg', $addVars);

// ================== 邮件发送函数 ==================
function sendEmail($to, $subject, $message, $isHtml = false) {
    global $mailConfig;
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->CharSet = 'UTF-8';
        // 服务器设置
        $mail->isSMTP();
        $mail->Host = $mailConfig['host']; // SMTP服务器
        $mail->SMTPAuth = true;
        $mail->Username = $mailConfig['username'];    // 邮箱账号
        $mail->Password = $mailConfig['password'];   // 邮箱密码
        $mail->SMTPSecure = $mailConfig['encryption']; // 加密方式
        $mail->Port = $mailConfig['port']; // TCP端口

        $mail->setFrom($mailConfig['from'], 'ICP备案中心');
        $mail->addAddress($to);

        // 内容
        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
    } catch (Exception $e) {
        error_log("邮件发送失败: {$mail->ErrorInfo}");
    }
}