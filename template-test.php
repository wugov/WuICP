<?php
//define('ABSPATH', dirname(__DIR__));
// 引入配置文件
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';


try {
    $addVars = [
        'contact_email' => 'contact@example.com',
    ];
    renderPage('test', $addVars);
} catch (Exception $e) {
    echo 'Error: ',  $e->getMessage(), "\n";
}