<?php
include_once '../../lib/function.php';
require_once '../../vendor/autoload.php';
initDatabase();
global $pdo;

if (!checkUserLogin()) {
    header('Location: /admin/login.php');
}
/**
 * @param $pdo
 * @param $plugin_name
 * @return void
 */
function getContactInfo($pdo, $plugin_name)
{

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $plugin_name = $_POST['plugin_name'] ?? null;
    $plugin_entry = $_POST['plugin_entry'] ?? null;
    if (is_null($action) && is_null($plugin_name)) {
        $response = [
            'status' => 'error',
            'plugin_name' => '',
            'action' => '',
            'message' => '传入参数不能为空'
        ];
        goto output;
    }
    if (is_null($action)) {
        $response = [
            'status' => 'error',
            'plugin_name' => $plugin_name,
            'action' => '',
            'message' => '方法不能为空'
        ];
        goto output;
    }
    if (is_null($plugin_name)) {
        $response = [
            'status' => 'error',
            'plugin_name' => '',
            'action' => $action,
            'message' => '插件名不能为空'
        ];
        goto output;
    }
    switch ($action) {
        case 'activate':
            $name = $plugin_name;
            if (is_null($plugin_entry)) {
                $response = [
                    'status' => 'error',
                    'plugin_name' => $plugin_name,
                    'action' => 'activate',
                    'message' => '插件入口文件参数不能为空'
                ];
                goto output;
            }
            $path = $plugin_entry;
            if(activate_plugin($name , $path)){
                $response = [
                    'status' => 'success',
                    'plugin_name' => $plugin_name,
                    'action' => 'activate',
                    'message' => '已激活'
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'plugin_name' => $plugin_name,
                    'action' => 'activate',
                    'message' => '插件已经被激活'
                ];
            }

            break;
        case 'deactivate':
            $name = $plugin_name;
            if (is_null($plugin_entry)) {
                $response = [
                    'status' => 'error',
                    'plugin_name' => $plugin_name,
                    'action' => 'activate',
                    'message' => '插件入口文件参数不能为空'
                ];
                goto output;
            }
            $path = $plugin_entry;
            if(deactivate_plugin($name , $path)){
                $response = [
                    'status' => 'success',
                    'plugin_name' => $plugin_name,
                    'action' => 'activate',
                    'message' => '已禁用'
                ];
            } else {
                $response = [
                    'status' => 'error',
                    'plugin_name' => $plugin_name,
                    'action' => 'activate',
                    'message' => '插件已经被禁用'
                ];
            }
            break;
        case 'delete':

            $response = [
                'status' => 'success',
                'plugin_name' => $plugin_name,
                'action' => 'delete',
                'message' => '已删除审核'
            ];
            break;
        case 'check_update':
            $response = [
                'status' => 'none',
                'plugin_name' => $plugin_name,
                'action' => 'check_update',
                'message' => '不支持检查更新'
            ];
            break;
        default:
            $response = [
                'status' => 'error',
                'plugin_name' => '',
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