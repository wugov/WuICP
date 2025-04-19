<?php
$config = require __DIR__ . '/../config.php';
$dbConfig = $config['db'] ?? [];

if (empty($dbConfig)) {
    exit('数据库配置缺失，请检查config.php');
}

// 从config.php获取邮件配置
$mailConfig = $config['mail'] ?? [];
if (empty($mailConfig)) {
    exit('邮件配置缺失，请检查config.php');
}

// 获取当前访问的域名
$currentDomain = $_SERVER['HTTP_HOST'] ?? 'localhost';

// 引入PHPMailer
require __DIR__ . '/../vendor/autoload.php'; // 修改为相对路径，确保正确加载
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ========================
 * 数据库连接 (PDO)
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
} catch (PDOException $e) {
    exit('数据库连接失败: ' . $e->getMessage());
}

/* ========================
 * 处理待审核记录 - 核心逻辑
 * ======================== */
try {
    // 查询待审核记录
    $stmt = $pdo->prepare("
        SELECT 
            r.id, 
            r.website_url,
            r.website_name,
            r.icp_number,
            r.status,
            r.owner,
            r.email,
            s.site_name
        FROM icp_records AS r
        INNER JOIN website_info AS s 
        WHERE r.status != '审核通过'
    ");
    $stmt->execute();
    $pendingRecords = $stmt->fetchAll();

    foreach ($pendingRecords as $row) {
        // 变量赋值
        $record_id = $row['id'];
        $record_name = $row['website_name'];
        $website_url = $row['website_url'];
        $icp_number = $row['icp_number'];
        $email = $row['email'];  
        $uesr_name = $row['owner'];
        $web_name = $row['site_name'];

        // 处理流程
        echo "[处理中] 网站名称 {$record_name} 正在查询API...";
        flush();
        
        // 使用当前域名构建API URL
        $apiUrl = "https://{$currentDomain}/api/bot.php?" . http_build_query([
            'url' => $website_url,
            'number' => $icp_number
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $responseText = curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo "\r[失败] 网站名称 {$record_name} API请求错误: " . curl_error($ch) . PHP_EOL . PHP_EOL;
            curl_close($ch);
            continue;
        }
        curl_close($ch);
        
        echo "\r[完成] 网站名称 {$record_name} 获取响应成功" . PHP_EOL;

        $responseText = trim($responseText);
        if ($responseText === 'true') {
            try {
                // 更新数据库状态
                $pdo->beginTransaction();
                
                $updateStmt = $pdo->prepare("
                    UPDATE icp_records 
                    SET 
                        status = :status
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':status' => '审核通过',
                    ':id' => $record_id
                ]);
                $pdo->commit();
                echo "[通过] 网站名称 {$record_name} 状态已更新" . PHP_EOL;

                // 发送邮件通知
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host       = $mailConfig['host'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $mailConfig['username'];
                    $mail->Password   = $mailConfig['password'];
                    $mail->SMTPSecure = $mailConfig['encryption'] ?? PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = $mailConfig['port'];

                    // 编码设置
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';
                    $mail->setLanguage('zh_cn');

                    $mail->setFrom($mailConfig['from'], "{$web_name}ICP备案中心");
                    $mail->addAddress($email);
                    $mail->Subject = "{$web_name}备审核通知";
                    $mail->Body    = "尊敬的{$uesr_name}，您的网站 {$record_name}（{$website_url}）已通过ICP备案审核。";

                    if ($mail->send()) {
                        echo "[邮件通知] 已发送至 {$email}" . PHP_EOL;
                    }
                } catch (Exception $e) {
                    echo "[邮件发送失败] {$email}: " . $e->getMessage() . PHP_EOL;
                }

            } catch (PDOException $e) {
                $pdo->rollBack();
                echo "[错误] 网站名称 {$record_name} 更新失败: " . $e->getMessage() . PHP_EOL;
            }
        } else {
            echo "[未通过] 网站名称 {$record_name} 响应: {$responseText}" . PHP_EOL;
        }
        echo PHP_EOL;
    }
} catch (PDOException $e) {
    exit('数据处理失败: ' . $e->getMessage());
}

$pdo = null;