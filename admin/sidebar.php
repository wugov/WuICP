<?php
// 如果这个文件被直接访问，则退出
// if (!defined('ABSPATH')) {
//     exit('不允许直接访问');
// }
 // Start the session
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
if (!checkUserLogin()) {
    header("Location: login.php");
    exit();
}
?>
<div id="sidebar">
    <ul>
        <li<?php if (basename($_SERVER['PHP_SELF']) == 'index.php') {
            echo ' class="active"';
        } ?>><a href="index.php">Home</a></li>
        <li<?php if (basename($_SERVER['PHP_SELF']) == 'change_adm.php') {
            echo ' class="active"';
        } ?>><a href="change_adm.php">修改记录审核</a></li>
        <li<?php if (basename($_SERVER['PHP_SELF']) == 'add_adm.php') {
            echo ' class="active"';
        } ?>><a href="add_adm.php">添加备案</a></li>
        <li<?php if (basename($_SERVER['PHP_SELF']) == 'core-update.php') {
            echo ' class="active"';
        } ?>><a href="core-update.php">版本升级</a></li>
        <li<?php if (basename($_SERVER['PHP_SELF']) == 'settings.php') {
            echo ' class="active"';
        } ?>><a href="settings.php">网站设置</a></li>
        <li<?php if (basename($_SERVER['PHP_SELF']) == 'backup.php') {
            echo ' class="active"';
        } ?>><a href="backup.php">备份数据</a></li>
        <li<?php if (basename($_SERVER['PHP_SELF']) == 'logs.php') {
            echo ' class="active"';
        } ?>><a href="logs.php">日志</a></li>
        <li<?php if (basename($_SERVER['PHP_SELF']) == 'device.php') {
            echo ' class="active"';
        } ?>><a href="device.php">登陆设备管理</a></li>
        <li<?php if (basename($_SERVER['PHP_SELF']) == 'wp.php') {
            echo ' class="active"';
        } ?>><a href="wp.php">与WordPress对接</a></li>
        <li<?php if (basename($_SERVER['PHP_SELF']) == 'logout.php') {
            echo ' class="active"';
        } ?>><a href="logout.php">注销</a></li>
        <li><a href="https://github.com/yuntuanzi/TuanICP" target="_blank">GitHub</a></li>
        <li><a href="https://gitlab.biliwind.com/yuncheng/tuanicp" target="_blank">GitLab</a></li>
        <li><a href="https://www.yuncheng.fun" target="_blank"> 云团子的博客</a></li>
        <li><a href="https://www.biliwind.com" target="_blank">风屿岛</a></li>

    </ul>
</div>

<style>
    #sidebar {
        width: 10%;
        background-color: #f0f0f0;
        padding: 15px;
    }

    #sidebar ul {
        list-style-type: none;
        padding: 0;
    }

    #sidebar ul li {
        margin-bottom: 10px;
    }

    #sidebar ul li a {
        text-decoration: none;
        color: #333;
        display: block;
        padding: 8px;
    }

    #sidebar ul li a:hover {
        background-color: #ddd;
    }

    #sidebar ul li.active a {
        background-color: #555;
        color: white;
    }
</style>