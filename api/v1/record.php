<?php
include_once '../../lib/function.php';
require_once '../../vendor/autoload.php';
initDatabase();
global $pdo;

if (!checkUserLogin()) {
    header('Location: /admin/login.php');
}
// Function to send a pass email
function sendPassEmail($pdo, $recordId)
{
    $stmt = $pdo->prepare("SELECT email FROM icp_records WHERE id = :id");
    $stmt->execute(['id' => $recordId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    $to = $record['email'];
    $subject = "备案信息通过通知";
    $message = "您的备案申请已经通过！";
    $headers = "From: yun@yuncheng.fun";

//    mail($to, $subject, $message, $headers);
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

//    mail($to, $subject, $message, $headers);
}

// Function to pass a record
function passRecord($pdo, $recordId)
{
    $stmt = $pdo->prepare("UPDATE icp_records SET STATUS = '审核通过' WHERE id = :id");
    $stmt->execute(['id' => $recordId]);

    sendPassEmail($pdo, $recordId); // Send pass email
}

// Function to reject a record
function rejectRecord($pdo, $recordId)
{
    $stmt = $pdo->prepare("UPDATE icp_records SET STATUS = '备案驳回' WHERE id = :id");
    $stmt->execute(['id' => $recordId]);

    sendRejectEmail($pdo, $recordId); // Send reject email
}


// Function to delete a record
function deleteRecord($pdo, $recordId)
{
    $stmt = $pdo->prepare("UPDATE icp_records SET STATUS = '被删除' WHERE id = :id");
    $stmt->execute(['id' => $recordId]);
}
// Handle AJAX requests
/**
 * @param $pdo
 * @param $recordId
 * @return void
 */
function getContactInfo($pdo, $recordId)
{

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $recordId = $_POST['id'] ?? null;
    if (is_null($action) && is_null($recordId)) {
        $response = [
            'status' => 'error',
            'id' => '',
            'action' => '',
            'message' => '传入参数不能为空'
        ];
        goto output;
    }
    if (is_null($action)) {
        $response = [
            'status' => 'error',
            'id' => $recordId,
            'action' => '',
            'message' => '方法不能为空'
        ];
        goto output;
    }
    if (is_null($recordId)) {
        $response = [
            'status' => 'error',
            'id' => '',
            'action' => $action,
            'message' => 'ID不能为空'
        ];
        goto output;
    }
    switch ($action) {
        case 'pass':
            passRecord($pdo, $recordId);
            $response = [
                'status' => 'success',
                'id' => $recordId,
                'action' => 'pass',
                'message' => '已通过审核'
            ];
            break;
        case 'reject':
            rejectRecord($pdo, $recordId);
            $response = [
                'status' => 'success',
                'id' => $recordId,
                'action' => 'reject',
                'message' => '已驳回审核'
            ];
            break;
        case 'delete':
            deleteRecord($pdo, $recordId);
            $response = [
                'status' => 'success',
                'id' => $recordId,
                'action' => 'delete',
                'message' => '已删除审核'
            ];
            break;
        case 'contact':
            getContactInfo($pdo, $recordId);
            $response = [
                'status' => 'success',
                'id' => $recordId,
                'action' => 'contact',
                'message' => $email
            ];
            break;
        default:
            $response = [
                'status' => 'error',
                'id' => '',
                'action' => 'unknown',
                'message' => '无法处理此方法'
            ];
            break;
    }
    output:
    header('Content-Type: application/json');
    echo json_encode($response);
    exit; // Stop further execution after handling AJAX request
}