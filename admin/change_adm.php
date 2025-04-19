<?php
//define('ABSPATH', dirname(__DIR__));
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';
 // Start the session
require '../lib/function.php';
if (!checkUserLogin()) {
    header("Location: login.php");
    exit();
}
initDatabase();// Function to send a pass email
global $pdo;
function sendPassEmail($pdo, $recordId)
{
    $stmt = $pdo->prepare("SELECT email FROM icp_records WHERE id = :id");
    $stmt->execute(['id' => $recordId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    $to = $record['email'];
    $subject = "备案信息通过通知";
    $message = "您的备案申请已经通过！";
    $headers = "From: yun@yuncheng.fun";

    mail($to, $subject, $message, $headers);
}

// Function to send a reject email
function sendRejectEmail($pdo, $recordId)
{
    $stmt = $pdo->prepare("SELECT email FROM icp_records WHERE id = :id");
    $stmt->execute(['id' => $recordId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    $to = $record['email'];
    $subject = "备案信息驳回通知";
    $message = "您的备案申请被驳回，请贵站尽快按照要求与我们完成对接！";
    $headers = "From: yun@yuncheng.fun";

    mail($to, $subject, $message, $headers);
}

// Function to pass a record
function passRecordC($pdo, $recordICP): void
{
    sendPassCEmail($pdo, $recordICP); // Send pass email
    // 获取要更改的记录信息
    $selectStmt = $pdo->prepare("SELECT * FROM icp_records_change WHERE icp_number = :icp_number");
    $selectStmt->execute(['icp_number' => $recordICP]);
    $record = $selectStmt->fetch(PDO::FETCH_ASSOC);
    // 更新主表记录
    $mainStmt = $pdo->prepare("UPDATE icp_records SET website_name = :website_name, website_url = :website_url, website_info = :website_info, owner = :owner, STATUS = '审核通过', email = :email, qq = :qq WHERE icp_number = :icp_number");
    $mainStmt->execute([
        'icp_number' => $recordICP,
        'website_name' => $record['website_name'],
        'website_url' => $record['website_url'],
        'website_info' => $record['website_info'],
        'owner' => $record['owner'],
        'email' => $record['email'],
        'qq' => $record['qq']
    ]);
    // 更新状态
    $stmt = $pdo->prepare("DELETE FROM icp_records_change WHERE icp_number = :icp_number");
    $stmt->execute(['icp_number' => $recordICP]);
}

// 发送通过更改的邮件
function sendPassCEmail($pdo, $recordICP)
{
    $stmt = $pdo->prepare("SELECT email FROM icp_records_change WHERE icp_number = :icp_number");
    $stmt->execute(['icp_number' => $recordICP]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    $to = $record['email'];
    $subject = "备案信息修改通过通知";
    $message = "您的备案信息修改申请已经通过！";
    $headers = "From: yun@yuncheng.fun";

    mail($to, $subject, $message, $headers);
}


// Function to reject a record
function rejectRecordC($pdo, $recordId)
{
    $stmt = $pdo->prepare("UPDATE icp_records_change SET STATUS = '修改信息驳回' WHERE id = :id");
    $stmt->execute(['id' => $recordId]);
    $stmt->fetch(PDO::FETCH_ASSOC);
    sendRejectEmail($pdo, $recordId); // Send reject email
}


// Function to delete a record
function deleteRecordC($pdo, $recordId)
{
    $stmt = $pdo->prepare("UPDATE icp_records_change SET STATUS = '被删除' WHERE id = :id");
    $stmt->execute(['id' => $recordId]);
}


// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $recordId = $_POST['id'] ?? 0;
    $recordICP = $_POST['icp_number'] ?? '';

    switch ($action) {
        case 'pass':
            passRecordC($pdo, $recordICP);
            break;
        case 'reject':
            rejectRecordC($pdo, $recordId);
            break;
        case 'delete':
            deleteRecordC($pdo, $recordId);
            break;
        default:
            echo "Invalid action.";
            break;
    }
    exit; // Stop further execution after handling AJAX request
}

// Fetch records from the database
$stmt = $pdo->query("SELECT * FROM icp_records_change");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div id="sidebar"><?php include 'sidebar.php'; ?></div>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>ICP Records Backend</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .editable {
            cursor: pointer;
        }
    </style>
    <script src="js/jquery-3.6.0.js"></script>
    <script src="js/change.js"></script>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
<table id="icpTable">
    <thead>
    <tr>
        <th>ID</th>
        <th>网站名称</th>
        <th>网站URL</th>
        <th>网站信息</th>
        <th>ICP编号</th>
        <th>所有者</th>
        <th>更新时间</th>
        <th>状态</th>
        <th>邮箱</th>
        <th>QQ</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($records as $record): ?>
        <tr>
            <td><?php echo htmlspecialchars($record['id']); ?></td>
            <td class="editable" data-id="<?php echo $record['id']; ?>"
                data-column="website_name"><?php echo htmlspecialchars($record['website_name']); ?></td>
            <td class="editable" data-id="<?php echo $record['id']; ?>"
                data-column="website_url"><?php echo htmlspecialchars($record['website_url']); ?></td>
            <td class="editable" data-id="<?php echo $record['id']; ?>"
                data-column="website_info"><?php echo htmlspecialchars($record['website_info']); ?></td>
            <td class="editable" data-id="<?php echo $record['id']; ?>"
                data-column="icp_number"><?php echo htmlspecialchars($record['icp_number']); ?></td>
            <td class="editable" data-id="<?php echo $record['id']; ?>"
                data-column="owner"><?php echo htmlspecialchars($record['owner']); ?></td>
            <td><?php echo htmlspecialchars($record['update_time']); ?></td>
            <td><?php echo htmlspecialchars($record['STATUS']); ?></td>
            <td class="editable" data-id="<?php echo $record['id']; ?>"
                data-column="email"><?php echo htmlspecialchars($record['email']); ?></td>
            <td class="editable" data-id="<?php echo $record['id']; ?>"
                data-column="qq"><?php echo htmlspecialchars($record['qq']); ?></td>
            <td>
                <button onclick="passRecordC(<?php echo $record['icp_number']; ?>)">通过</button>
                <button onclick="rejectRecordC(<?php echo $record['id']; ?>)">驳回</button>
                <button onclick="deleteRecordC(<?php echo $record['id']; ?>)">删除</button>
                <!-- Save button is not needed as changes are saved on focus out -->
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>

<!--
                   _ooOoo_
                  o8888888o
                  88" . "88
                  (| -_- |)
                  O\  =  /O
               ____/`---'\____
             .'  \\|     |//  `.
            /  \\|||  :  |||//  \
           /  _||||| -:- |||||-  \
           |   | \\\  -  /// |   |
           | \_|  ''\---/''  |   |
           \  .-\__  `-`  ___/-. /
         ___`. .'  /--.--\  `. . __
      ."" '<  `.___\_<|>_/___.'  >'"".
     | | :  `- \`.;`\ _ /`;.`/ - ` : | |
     \  \ `-.   \_ __\ /__ _/   .-` /  /
======`-.____`-.___\_____/___.-`____.-'======
                   `=---='
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
            佛祖保佑       永无BUG
-->


</html>
