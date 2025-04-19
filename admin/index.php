<?php
global $pdo;
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';
require_once '../vendor/autoload.php';
include_once '../lib/function.php';

if (file_exists('../qrcode.png')) {
    unlink('../qrcode.png');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

initDatabase();
if (!checkUserLogin()) {
    header('Location: /admin/login.php');
    exit;
}

function sendPassEmail($pdo, $recordId, $reason = '') {
    $stmt = $pdo->prepare("SELECT email, website_url, icp_number, website_name FROM icp_records WHERE id = :id");
    $stmt->execute(['id' => $recordId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    $to = $record['email'];
    $subject = "备案信息通过通知";
    $domain = $_SERVER['HTTP_HOST'];
    $link = "https://{$domain}/id.php?keyword={$record['icp_number']}";
    
    $message = "
    <html>
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
            <h1>备案信息通过通知</h1>
            <p>尊敬的雾都市民,您好！</p>
            <p>您在 <a href='{$link}' target='_blank'>{$link}</a> 的备案信息申请已审核并予以通过。</p>
            <p><strong>原因：</strong>{$reason}</p>
            <p>请及时登录雾都市政系统查看详细信息。</p>
            <p>雾都市政府向您致意!</p>
            <div class='footer'>此邮件为系统自动发送，请勿直接回复。</div>
        </div>
    </body>
    </html>
    ";
    
    sendEmail($to, $subject, $message, true);
}

function sendRejectEmail($pdo, $recordId, $reason = '') {
    $stmt = $pdo->prepare("SELECT email, website_url, icp_number, website_name FROM icp_records WHERE id = :id");
    $stmt->execute(['id' => $recordId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    $to = $record['email'];
    $subject = "备案信息驳回通知";
    $domain = $_SERVER['HTTP_HOST'];
    $link = "https://{$domain}/id.php?keyword={$record['icp_number']}";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
            h1 { color: #f44336; }
            p { margin: 10px 0; }
            a { color: #007BFF; text-decoration: none; }
            a:hover { text-decoration: underline; }
            .footer { margin-top: 20px; font-size: 12px; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>备案信息驳回通知</h1>
            <p>尊敬的雾都市民,您好！</p>
            <p>您在 <a href='{$link}' target='_blank'>{$link}</a> 的备案信息申请已审核但被驳回。</p>
            <p><strong>原因：</strong>{$reason}</p>
            <p>请及时登录雾都市政系统修改备案申请信息。并按照要求修改后重新提交。</p>
            <p>雾都市政府向您致意!</p>
            <div class='footer'>此邮件为系统自动发送，请勿直接回复。</div>
        </div>
    </body>
    </html>
    ";
    
    sendEmail($to, $subject, $message, true);
}

function sendEmail($to, $subject, $message, $isHtml = false) {
    $mail = new PHPMailer(true);
    try {
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = 'smtp.qiye.aliyun.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'icp@scfc.top';
        $mail->Password = 'SVIPyyds1016!';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->setFrom('icp@scfc.top', '雾ICP备案中心');
        $mail->addAddress($to);
        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->send();
    } catch (Exception $e) {
        echo "邮件发送失败: {$mail->ErrorInfo}";
    }
}

function passRecord($pdo, $recordId, $reason = '') {
    $stmt = $pdo->prepare("UPDATE icp_records SET STATUS = '审核通过' WHERE id = :id");
    $stmt->execute(['id' => $recordId]);
    sendPassEmail($pdo, $recordId, $reason);
}

function rejectRecord($pdo, $recordId, $reason = '') {
    $stmt = $pdo->prepare("UPDATE icp_records SET STATUS = '备案驳回' WHERE id = :id");
    $stmt->execute(['id' => $recordId]);
    sendRejectEmail($pdo, $recordId, $reason);
}

function deleteRecord($pdo, $recordId) {
    $stmt = $pdo->prepare("UPDATE icp_records SET STATUS = '被删除' WHERE id = :id");
    $stmt->execute(['id' => $recordId]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $recordId = $_POST['id'] ?? 0;
    $reason = $_POST['reason'] ?? '';

    switch ($action) {
        case 'pass':
            passRecord($pdo, $recordId, $reason);
            break;
        case 'reject':
            rejectRecord($pdo, $recordId, $reason);
            break;
        case 'delete':
            deleteRecord($pdo, $recordId);
            break;
    }
    exit;
}

$stmt = $pdo->query("SELECT * FROM icp_records");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>ICP备案管理系统</title>
    <style>
        :root {
            --primary: #2d3436;
            --secondary: #636e72;
            --success: #00b894;
            --danger: #d63031;
            --warning: #fdcb6e;
            --bg: #f5f6fa;
            --card-bg: #ffffff;
            --text: #2d3436;
            --border: rgba(0,0,0,0.1);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            line-height: 1.6;
        }

        #sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            background: var(--card-bg);
            box-shadow: 0 0 30px rgba(0,0,0,0.05);
            padding: 2rem;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .container {
            margin-left: 260px;
            padding: 3rem;
            min-height: 100vh;
            transition: margin 0.3s ease;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding: 1.5rem;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        }

        .card {
            background: var(--card-bg);
            border-radius: 14px;
            box-shadow: 0 7px 30px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
        }

        th {
            padding: 1.2rem 1.5rem;
            background: var(--primary);
            color: white;
            font-weight: 500;
            text-align: left;
            position: sticky;
            top: 0;
        }

        td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
        }

        .status-approved {
            background: rgba(0, 184, 148, 0.15);
            color: var(--success);
        }

        .status-rejected {
            background: rgba(214, 48, 49, 0.15);
            color: var(--danger);
        }

        .status-pending {
            background: rgba(253, 203, 110, 0.15);
            color: #e17055;
        }

        .action-buttons {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            background: var(--card-bg);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-secondary {
            background: var(--secondary);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--card-bg);
            border-radius: 14px;
            padding: 2rem;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
            z-index: 1002;
            display: none;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(3px);
            z-index: 1001;
            display: none;
        }

        .modal.active,
        .modal-overlay.active {
            display: block;
        }

        .modal textarea {
            width: 100%;
            height: 120px;
            padding: 1rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            margin: 1rem 0;
            resize: vertical;
            font-family: inherit;
        }

        .mobile-menu {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1003;
            background: var(--card-bg);
            padding: 0.8rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        @media (max-width: 1024px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.active {
                transform: translateX(0);
            }

            .container {
                margin-left: 0;
                padding: 1.5rem;
            }

            .mobile-menu {
                display: block;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
    <script src="js/jquery-3.6.0.js"></script>
</head>
<body>
<div class="modal-overlay"></div>
<button class="mobile-menu" onclick="toggleSidebar()">☰</button>

<div id="sidebar"><?php include 'sidebar.php'; ?></div>

<div class="container">
    <div class="header">
        <h1 style="margin:0;font-weight:600;">备案管理</h1>
        <button class="btn btn-primary" onclick="handleGlobalNotification()">
            📨 发送全局通知
        </button>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>网站名称</th>
                    <th>网址</th>
                    <th>备案号</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                <tr>
                    <td>#<?= htmlspecialchars($record['id']) ?></td>
                    <td><?= htmlspecialchars($record['website_name']) ?></td>
                    <td>
                        <a href="<?= htmlspecialchars(
                        // 检测并补全协议头
                            parse_url($record['website_url'], PHP_URL_SCHEME) ? 
                            $record['website_url'] : 'https://' . $record['website_url']) ?>" target="_blank" style="color:var(--success);text-decoration:none;">
                            <?= htmlspecialchars($record['website_url']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($record['icp_number']) ?></td>
                    <td>
                        <?php $statusClass = [
                            '审核通过' => 'status-approved',
                            '备案驳回' => 'status-rejected',
                            '被删除' => 'status-pending'
                        ][$record['STATUS']] ?? ''; ?>
                        <span class="status-badge <?= $statusClass ?>">
                            <?= htmlspecialchars($record['STATUS']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-success" 
                                    onclick="handleAction('pass', <?= $record['id'] ?>)">
                                ✓ 通过
                            </button>
                            <button class="btn btn-danger" 
                                    onclick="handleAction('reject', <?= $record['id'] ?>)">
                                ✗ 驳回
                            </button>
                            <button class="btn btn-secondary" 
                                    onclick="handleDelete(<?= $record['id'] ?>)">
                                🗑 删除
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="reasonModal" class="modal">
        <h3 style="margin:0 0 1.5rem;">操作原因说明</h3>
        <textarea id="reasonInput" placeholder="请详细说明操作原因..."></textarea>
        <div style="display:flex;gap:1rem;justify-content:flex-end;">
            <button class="btn btn-primary" onclick="submitReason()">确认提交</button>
            <button class="btn btn-secondary" onclick="closeModal()">取消操作</button>
        </div>
    </div>
</div>

<script>
const SECURITY_TOKEN = 'cxzfdfa1*56.qds!04';
let currentAction = '';
let currentRecordId = 0;

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.querySelector('.modal-overlay').classList.toggle('active');
}

function handleGlobalNotification() {
    if (!confirm('确定要发送全局通知吗？此操作不可撤销！')) return;
    // 原有全局通知逻辑保持不变
}

function handleAction(action, id) {
    currentAction = action;
    currentRecordId = id;
    document.getElementById('reasonModal').classList.add('active');
    document.querySelector('.modal-overlay').classList.add('active');
}

function closeModal() {
    document.getElementById('reasonModal').classList.remove('active');
    document.querySelector('.modal-overlay').classList.remove('active');
    document.getElementById('reasonInput').value = '';
}

function submitReason() {
    const reason = document.getElementById('reasonInput').value.trim();
    if (!reason) {
        alert('必须填写操作原因');
        return;
    }

    $.ajax({
        url: '',
        method: 'POST',
        data: { action: currentAction, id: currentRecordId, reason },
        success: () => {
            alert('操作成功');
            location.reload();
        },
        error: () => {
            alert('操作失败，请检查网络');
            closeModal();
        }
    });
}

function handleDelete(id) {
    if (!confirm('此操作将永久删除记录，是否继续？')) return;
    
    $.ajax({
        url: '',
        method: 'POST',
        data: { action: 'delete', id },
        success: () => {
            alert('删除成功');
            location.reload();
        },
        error: () => alert('删除失败')
    });
}

// 点击遮罩层关闭弹窗
document.querySelector('.modal-overlay').addEventListener('click', closeModal);
</script>
</body>
</html>