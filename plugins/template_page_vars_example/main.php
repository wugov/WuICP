<?php
/*
* Name:        示例插件1
* Description:        此插件向模板渲染器中添加一个占位符example_plugin_variable，可以在渲染模板时将此占位符替换为指定内容
* Version:            1.0
* Author:             风屿Wind
*/

// main.php
// if (!defined('ABSPATH')) {
//     exit('不允许直接访问');
// }
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/plugin/hooks.php'; // 引入钩子文件

// 注册回调
add_action('add_page_vars', function () {
    $pluginAddPageVars = [
        'example_plugin_variable' => '可以使用插件增加需要替换的占位符',
    ];
    return $pluginAddPageVars;
});