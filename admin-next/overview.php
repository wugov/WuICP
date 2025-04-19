<?php


require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
initDatabase();
global $pdo;

$loadResults = [];

// 查询当前的线程数量
$query = "SHOW STATUS LIKE :status;";
$stmt = $pdo->prepare($query);
$stmt->execute(['status' => 'Threads_connected']);
if ($stmt->rowCount() > 0) {
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $loadResults['Threads_connected'] = $result['Value'];
}

// 查询创建的线程总数
$stmt = $pdo->prepare($query);
$stmt->execute(['status' => 'Threads_created']);
if ($stmt->rowCount() > 0) {
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $loadResults['Threads_created'] = $result['Value'];
}

// 查询服务器的版本和运行时间
$query = "SELECT VERSION();";
$stmt = $pdo->query($query);
if ($stmt) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $loadResults['Version'] = $row['VERSION()'];
}


?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <title>TuanICP 后台管理</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/layui/2.9.20/css/layui.min.css"
          integrity="sha512-54WyiQNseHG9u6y5QlRZQU5Xqh1llgctSRJPE26UOlXB22P+Bnt6jZT8+bf5GfyiNaY77ZLdGcgtLF3NuyVvWQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/layui/2.9.20/layui.min.js"
            integrity="sha512-z2UATz8GsuKCOTbw4ML/6YvZeAhEQsm3mSawEWnxdq65bDtMoXp501kvS93JyZ95onfEZqf/vykl3M4If4nGaw=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>
<div class="layui-layout layui-layout-admin">
    <div class="layui-header">
        <div class="layui-logo layui-hide-xs layui-bg-black">TuanICP</div>
        <!-- 头部区域（可配合layui 已有的水平导航） -->
        <ul class="layui-nav layui-layout-left">
            <!-- 移动端显示 -->
            <li class="layui-nav-item layui-show-xs-inline-block layui-hide-sm" lay-header-event="menuLeft">
                <i class="layui-icon layui-icon-spread-left"></i>
            </li>
            <li class="layui-nav-item layui-hide-xs"><a href="/admin-next">返回后台首页</a></li>
            <li class="layui-nav-item layui-hide-xs"><a href="/">返回站点首页</a></li>
            <li class="layui-nav-item layui-hide-xs"><a href="javascript:">快捷方式3</a></li>
            <li class="layui-nav-item">
                <a href="javascript:">快速访问</a>
                <dl class="layui-nav-child">
                    <dd><a href="plugin/maker.php">插件构建器</a></dd>
                    <dd><a href="template/maker.php">模板构建器</a></dd>

                </dl>
            </li>
        </ul>
        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item layui-hide layui-show-sm-inline-block">
                <a href="javascript:">
                    <img src="//unpkg.com/outeres@0.0.10/img/layui/icon-v2.png" class="layui-nav-img">
                    tester
                </a>
                <dl class="layui-nav-child">
                    <dd><a href="javascript:">个人信息</a></dd>
                    <dd><a href="javascript:">设置</a></dd>
                    <dd><a href="javascript:">退出登陆</a></dd>
                </dl>
            </li>

        </ul>
    </div>
    <div class="layui-side layui-bg-black">
        <div class="layui-side-scroll">
            <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
            <ul class="layui-nav layui-nav-tree" lay-filter="test">
                <li class="layui-nav-item layui-nav-itemed">
                    <a class="" href="javascript:">站点概况</a>
                    <dl class="layui-nav-child">
                        <dd><a href="overview.php">工作负载</a></dd>
                        <dd><a href="javascript:">网络负载</a></dd>
                        <dd><a href="javascript:">访客情况</a></dd>
                        <dd><a href="javascript:">一些文档</a></dd>
                    </dl>
                </li>
                <li class="layui-nav-item">
                    <a href="javascript:">备案管理</a>
                    <dl class="layui-nav-child">
                        <dd><a href="audit.php">备案审核</a></dd>
                        <dd><a href="change.php">变更审核</a></dd>
                        <dd><a href="all_icp.php">全部备案</a></dd>
                    </dl>
                </li>
                <li class="layui-nav-item"><a href="/admin-next/plugin.php">插件管理</a></li>
                <li class="layui-nav-item"><a href="/admin-next/settings.php">站点设置</a></li>
            </ul>
        </div>
    </div>
    <div class="layui-body">
        <!-- 内容主体区域 -->
        <div style="padding: 15px;">
            <blockquote class="layui-elem-quote layui-text">
                欢迎使用 TuanICP v1.0.7
            </blockquote>
            <?php
            // 假设这是 index.php 文件的第139行
            echo "<h2>服务器概况</h2>";
            echo "<p>服务器软件: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
            echo "<p>服务器名称: " . $_SERVER['SERVER_NAME'] . "</p>";
            echo "<p>服务器地址: " . $_SERVER['SERVER_ADDR'] . "</p>";
            echo "<p>服务器端口: " . $_SERVER['SERVER_PORT'] . "</p>";
            echo "<p>文档根目录: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
            echo "<h2>负载概况</h2>";
            // 打印负载结果
            echo "<p>创建的线程总数: " . $loadResults['Threads_created'] . "</p>";
            echo "<p>连接的线程数: " . $loadResults['Threads_connected'] . "</p>";
            echo "<p>服务器版本: " . $loadResults['Version'] . "</p>";
            ?>

        </div>
    </div>
    <div class="layui-footer">
        <p>“自由”二字看似条条框框，但总有一笔冲出牢笼。</p>
    </div>
</div>

</body>
</html>
