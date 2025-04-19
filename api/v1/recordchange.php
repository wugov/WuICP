<?php
include_once '../../lib/function.php';
require_once '../../vendor/autoload.php';
initDatabase();
global $pdo;

if (!checkUserLogin()) {
    header('Location: /admin/login.php');
}
// Function to send a pass email
function sendPassEmail($pdo, $icp_number)
{
    $stmt = $pdo->prepare("SELECT email FROM icp_records_change WHERE icp_number = :icp_number");
    $stmt->execute(['icp_number' => $icp_number]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    $to = $record['email'];
    $subject = "备案信息通过通知";
    $message = "您的备案申请已经通过！";
    $headers = "From: yun@yuncheng.fun";

//    mail($to, $subject, $message, $headers);
}

// Function to send a reject email
function sendRejectEmail($pdo, $icp_number)
{
    $stmt = $pdo->prepare("SELECT email FROM icp_records_change WHERE icp_number = :icp_number");
    $stmt->execute(['icp_number' => $icp_number]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    $to = $record['email'];
    $subject = "备案信息驳回通知";
    $message = "您的备案申请被驳回，请贵站尽快按照要求与我们完成对接！";
    $headers = "From: yun@yuncheng.fun";

//    mail($to, $subject, $message, $headers);
}

// Function to pass a record
function passRecord($pdo, $icp_number)
{
    $stmt = $pdo->prepare("UPDATE icp_records_change SET STATUS = '审核通过' WHERE icp_number = :icp_number");
    $stmt->execute(['icp_number' => $icp_number]);

    sendPassEmail($pdo, $icp_number); // Send pass email
}

// Function to reject a record
function rejectRecord($pdo, $icp_number)
{
    $stmt = $pdo->prepare("UPDATE icp_records_change SET STATUS = '备案驳回' WHERE icp_number = :icp_number");
    $stmt->execute(['icp_number' => $icp_number]);

    sendRejectEmail($pdo, $icp_number); // Send reject email
}


// Function to delete a record
function deleteRecord($pdo, $icp_number)
{
    $stmt = $pdo->prepare("UPDATE icp_records_change SET STATUS = '被删除' WHERE icp_number = :icp_number");
    $stmt->execute(['icp_number' => $icp_number]);
}

// Handle AJAX requests
/**
 * @param $pdo
 * @param $icp_number
 * @return void
 */
function getContactInfo($pdo, $icp_number)
{

}

function applyChange($pdo, $icp_number)
{
    $stmt = $pdo->prepare("SELECT * FROM icp_records_change WHERE icp_number = :icp_number");
    $stmt->execute(['icp_number' => $icp_number]);
    $record_icp_change = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM icp_records WHERE icp_number = :icp_number");
    $stmt->execute(['icp_number' => $icp_number]);
    $record_icp = $stmt->fetch(PDO::FETCH_ASSOC);

    // 验证$record_icp_change和$record_icp是否都不为空且icp_number一致
    if ($record_icp_change && $record_icp && $record_icp_change['icp_number'] === $record_icp['icp_number']) {
        // 两个记录都存在且icp_number一致，可以继续处理
        // 将icp_records_change中的数据更新到icp_records中，更新除了icp_number的字段
        unset($record_icp_change['icp_number']); // 移除icp_number字段
        $update_fields = [];
        $update_values = [];
        foreach ($record_icp_change as $key => $value) {
            $update_fields[] = "$key = :$key";
            $update_values[$key] = $value;
        }
        $update_values['icp_number'] = $icp_number;

        $update_query = "UPDATE icp_records SET " . implode(', ', $update_fields) . " WHERE icp_number = :icp_number";
        $stmt = $pdo->prepare($update_query);
        $stmt->execute($update_values);

        // 删除icp_records_change中的记录
        $stmt = $pdo->prepare("DELETE FROM icp_records_change WHERE icp_number = :icp_number");
        $stmt->execute(['icp_number' => $icp_number]);

        // 返回成功json
        $response = [
            'status' => 'success',
            'icp_number' => $icp_number,
            'action' => 'pass',
            'message' => '记录更新成功'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        // 处理记录不存在或icp_number不一致的情况
        // 输出错误json
        $response = [
            'status' => 'error',
            'r1-main' => $record_icp,
            'r2-change' => $record_icp_change,
            'action' => 'pass',
            'message' => '记录不存在或icp_number不一致'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $icp_number = $_POST['icp_number'] ?? null;
    if (is_null($action) && is_null($icp_number)) {
        $response = [
            'status' => 'error',
            'icp_number' => '',
            'action' => '',
            'message' => '传入参数不能为空'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    if (is_null($action)) {
        $response = [
            'status' => 'error',
            'icp_number' => $icp_number,
            'action' => '',
            'message' => '方法不能为空'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    if (is_null($icp_number)) {
        $response = [
            'status' => 'error',
            'icp_number' => '',
            'action' => $action,
            'message' => 'ID不能为空'
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    switch ($action) {
        case 'pass':
            passRecord($pdo, $icp_number);
//            $response = [
//                'status' => 'success',
//                'icp_number' => $icp_number,
//                'action' => 'pass',
//                'message' => '已通过审核'
//            ];
//            header('Content-Type: application/json');
//            echo json_encode($response);
            applyChange($pdo, $icp_number);
            break;
        case 'reject':
            rejectRecord($pdo, $icp_number);
            $response = [
                'status' => 'success',
                'icp_number' => $icp_number,
                'action' => 'reject',
                'message' => '已驳回审核'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            break;
        case 'delete':
            deleteRecord($pdo, $icp_number);
            $response = [
                'status' => 'success',
                'icp_number' => $icp_number,
                'action' => 'delete',
                'message' => '已删除审核'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            break;
        case 'contact':
            getContactInfo($pdo, $icp_number);
            $response = [
                'status' => 'success',
                'icp_number' => $icp_number,
                'action' => 'contact',
                'message' => $email
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            break;
        default:
            $response = [
                'status' => 'error',
                'icp_number' => '',
                'action' => 'unknown',
                'message' => '无法处理此方法'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            break;
    }
    output:

    exit; // Stop further execution after handling AJAX request
}