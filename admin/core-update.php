<?php
//define('ABSPATH', dirname(__DIR__));
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';
 // Start the session
global $pdo;
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
if (!checkUserLogin()) {
    header("Location: login.php");
    exit();
}
?>
<div id="sidebar"><?php include 'sidebar.php'; ?></div>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>系统升级</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        #logContainer {
            margin-top: 20px;
            width: 70%;
            position: fixed;
            right: 0;
            top: 200px;
            border: 1px solid #ccc;
            text-align: left;
            display: none; /* 默认不显示日志框 */
        }
        button {
            text-align: center;
            padding: 10px;
            font-size: 16px;
            position: fixed;
            bottom: 10px;
            right: 10px;
            border: none;
            background-color: #5cb85c;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>
<button id="upgradeBtn">修复/升级数据库</button>
<div id="logContainer"></div>

<script>
    document.getElementById('upgradeBtn').addEventListener('click', function() {
        var logContainer = document.getElementById('logContainer');
        logContainer.style.display = 'block'; // 显示日志框
        logContainer.innerHTML = '正在升级，请稍等...';

        // 创建 XMLHttpRequest 对象
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // 将服务器返回的日志显示在框内
                logContainer.innerHTML = xhr.responseText;
            }
        };
        // 发送请求
        xhr.open('GET', 'upgrade_db.php?action=upgrade', true);
        xhr.send();
    });
</script>

<style>
    .version {
        position: fixed;
        top: 10px;
        left: 20%;
    }
</style>
<div class="version"><?php
    // 远程URL地址，用于获取最新版本号
    $remoteVersionUrl = 'https://icp.yuncheng.fun/version.txt';

    // 获取本地版本号
    $localVersion = file_get_contents('version.txt');

    // 获取远程版本号
    try {
        $remoteVersion = file_get_contents($remoteVersionUrl);
    } catch (Exception $e) {
        echo "无法获取远程版本号，请检查网络连接或稍后再试。";
        $remoteVersion = 'v0.0.0';
        goto jump_version_compare;
    }
    // 比较版本号
    if (version_compare($remoteVersion, $localVersion, '>')) {
        // 远程版本号更高，执行升级操作
        echo "有新版本可用，请更新！（在线升级仍在测试中，请联系开发者获取最新版本包，覆盖升级后点击下方的“修复/升级数据库”按钮）";
    } else {
        // 本地版本号已是最新
        echo "当前已是最新版本：{$localVersion}";
    }
    jump_version_compare:
    ?>
</div>

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