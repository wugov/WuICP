<?php
require __DIR__ . '/vendor/autoload.php';

/* ========================
 * 配置引入与初始化
 * ======================== */

// 引入配置文件
$config = require __DIR__ . '/config.php';

// 验证必要配置
$dbConfig = $config['db'] ?? [];
$mailConfig = $config['mail'] ?? [];
if (empty($dbConfig) || empty($mailConfig)) {
    exit('配置信息不完整，请检查config.php');
}

/* ========================
 * 数据库连接
 * ======================== */
try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $dbConfig['host'] ?? 'localhost',
        $dbConfig['dbname'] ?? ''
    );
    
    $pdo = new PDO(
        $dsn,
        $dbConfig['user'] ?? '',
        $dbConfig['pass'] ?? '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // 获取网站信息
    $stmt = $pdo->query("SELECT site_name FROM website_info LIMIT 1");
    $websiteInfo = $stmt->fetch();
    if (!$websiteInfo) {
        exit('网站信息未配置');
    }
    $siteName = $websiteInfo['site_name'];
    
} catch (PDOException $e) {
    error_log('[DB Error] ' . $e->getMessage());
    exit('数据库连接失败');
}

// 获取当前域名
$currentDomain = $_SERVER['HTTP_HOST'];
$baseUrl = 'https://' . $currentDomain;
/* ========================
 * 查询待处理记录
 * ======================== */
try {
    $stmt = $pdo->prepare("
        SELECT id, website_name, status, email 
        FROM icp_records 
        WHERE status != '审核通过'
          AND (last_notified IS NULL 
           OR last_notified < DATE_SUB(NOW(), INTERVAL 3 DAY))
    ");
    $stmt->execute();
    $pendingRecords = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('[Query Error] ' . $e->getMessage());
    exit('数据查询失败');
}

if (empty($pendingRecords)) {
    exit('当前没有需要通知的备案信息');
}

/* ========================
 * 新版邮件模板（动态配置版）
 * ======================== */
function sendNotification($email, $record, $mailConfig, $siteName, $baseUrl) {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // SMTP配置
        $mail->isSMTP();
        $mail->Host       = $mailConfig['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailConfig['username'];
        $mail->Password   = $mailConfig['password'];
        $mail->SMTPSecure = $mailConfig['encryption'];
        $mail->Port       = $mailConfig['port'];
        $mail->CharSet    = 'UTF-8';

        // 发件人信息
        $mail->setFrom(
            $mailConfig['from'],
            "{$siteName}备案中心"
        );
        $mail->addAddress($email);

        // 邮件内容
        $mail->isHTML(true);
        $mail->Subject = "{$siteName}网站备案整改要求通知";
        
        $mail->Body = sprintf('
            <div style="max-width:680px; margin:20px auto; padding:30px; border:1px solid #e5e5e5; font-family: Microsoft YaHei, sans-serif;">
                <h3 style="color:#333; border-bottom:2px solid #f0f0f0; padding-bottom:15px; margin:0 0 25px 0;">%s</h3>
                
                <p>尊敬的网站管理员：</p>
                <p style="line-height:1.6;">您的网站 <strong style="color:#1890ff;">%s</strong> 当前不符合备案要求，请立即进行以下整改：</p>
                
                <div style="background:#f8f9fa; padding:20px; margin:25px 0; border-radius:4px;">
                    <h4 style="color:#f5222d; margin-top:0;">主要问题：</h4>
                    <p style="color:#666; margin:10px 0;">%s</p>
                </div>

                <h4 style="color:#333; margin:25px 0 15px 0;">整改要求：</h4>
                <ul style="padding-left:20px; color:#666;">
                    <li style="margin-bottom:12px;">必须完成%s系统对接</li>
                    <li style="margin-bottom:12px;">内容需符合公序良俗和道德规范</li>
                    <li style="margin-bottom:12px;">必须启用HTTPS安全加密连接</li>
                </ul>

                <div style="margin:30px 0; padding:15px; background:#fffbe6; border:1px solid #ffe58f;">
                    <h4 style="color:#faad14; margin:0 0 10px 0;">处理指引：</h4>
                    <ol style="color:#666; padding-left:20px;">
                        <li>登录<a href="%s">%s系统</a>查看检测报告</li>
                        <li>完成整改后等待系统自动复核</li>
                    </ol>
                </div>

                <hr style="border:none; border-top:1px solid #e5e5e5; margin:30px 0;">
                <p style="color:#999; font-size:0.9em; line-height:1.6;">
                    系统发送时间：%s<br>
                    %s 审核中心
                </p>
            </div>',
            htmlspecialchars($siteName),
            htmlspecialchars($record['website_name']),
            htmlspecialchars($record['status']),
            htmlspecialchars($siteName),
            $baseUrl,
            htmlspecialchars($siteName),
            date('Y-m-d H:i'),
            htmlspecialchars($siteName)
        );

        return $mail->send();
    } catch (Exception $e) {
        error_log("[Mail Error] {$email} - {$mail->ErrorInfo}");
        return false;
    }
}

/* ========================
 * 执行邮件发送
 * ======================== */
$successCount = 0;
foreach ($pendingRecords as $record) {
    $email = filter_var($record['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("无效邮箱：{$record['email']}");
        continue;
    }

    try {
        if (sendNotification($email, $record, $mailConfig, $siteName, $baseUrl)) {
            $stmt = $pdo->prepare("UPDATE icp_records SET last_notified = NOW() WHERE id = ?");
            $stmt->execute([$record['id']]);
            $successCount++;
            echo "成功发送至：{$email}<br>";
        }
    } catch (Exception $e) {
        error_log("[Process Error] {$email} - {$e->getMessage()}");
    }
}

echo "<hr>操作完成，成功发送 {$successCount}/" . count($pendingRecords) . " 封邮件";