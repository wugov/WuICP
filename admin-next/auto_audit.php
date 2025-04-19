<?php

//define('ABSPATH', dirname(__DIR__));
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
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
            <div class="layui-card layui-panel">
                <div class="layui-card-header">
                    <p>版本变更</p>
                </div>
                <div class="layui-card-body">
                    <p>
                        这个版本最大的更新是后台系统的重构，现在后台系统已经可以支持多账号登录，并且可以支持多账号同时登录。</p>
                </div>
            </div>
            <br><br>
        </div>
    </div>
    <div class="layui-footer">
        <p>“自由”二字看似条条框框，但总有一笔冲出牢笼。</p>
    </div>
</div>


</body>
</html>