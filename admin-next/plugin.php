<?php

global $pdo;
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';
//require 'lib/password.php';
require_once '../vendor/autoload.php';

include_once '../lib/function.php';
// Start the session
if (file_exists('../qrcode.png')) {
    unlink('../qrcode.png');
}

initDatabase();
if (!checkUserLogin()) {
    header('Location: /admin-next/login.php');
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $recordId = $_POST['id'] ?? 0;

    switch ($action) {
        case 'pass':
            passRecord($pdo, $recordId);
            break;
        case 'reject':
            rejectRecord($pdo, $recordId);
            break;
        case 'delete':
            deleteRecord($pdo, $recordId);
            break;
        default:
            echo "Invalid action.";
            break;
    }
    exit; // Stop further execution after handling AJAX request
}

// Fetch records from the database
$stmt = $pdo->query("SELECT * FROM icp_records");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);


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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
            integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
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
            <ul class="layui-nav layui-nav-tree" lay-filter="sidebar">
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
            <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
                <legend>插件搜索</legend>
            </fieldset>
            <div style="padding: 16px;">
                <form class="layui-form layui-row layui-col-space16" id="table-search">
                    <div class="layui-col-md6">
                        <div class="layui-input-wrap">
                            <div class="layui-input-prefix">
                                <i class="layui-icon layui-icon-username"></i>
                            </div>
                            <input type="text" name="plugin_name" value="" placeholder="插件名称" class="layui-input"
                                   lay-affix="clear">
                        </div>
                    </div>
                    <div class="layui-btn-container layui-col-xs12">
                        <button class="layui-btn" lay-submit lay-filter="table-search">搜索</button>
                        <button type="reset" class="layui-btn layui-btn-primary">清空</button>
                    </div>
                </form>
                <table class="layui-hide" id="plugin_table" lay-filter="plugin_table"></table>
            </div>
            <script type="text/html" id="toolbar">
                <div class="layui-btn-container">
                    <button class="layui-btn layui-btn-sm" lay-event="getCheckData">获取选中行数据</button>
                    <button class="layui-btn layui-btn-sm" lay-event="getData">获取当前页数据</button>
                    <button class="layui-btn layui-btn-sm layui-btn-primary" id="rowMode">
                        <span>{{= d.lineStyle ? '多行' : '单行' }}模式</span>
                        <i class="layui-icon layui-icon-down layui-font-12"></i>
                    </button>
                </div>
            </script>
            <script type="text/html" id="tools">
                <div class="layui-clear-space">
                    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
                    <a class="layui-btn layui-btn-xs" lay-event="more">
                        更多
                        <i class="layui-icon layui-icon-down"></i>
                    </a>
                </div>
            </script>
            <script>
                layui.use(['table', 'dropdown', 'form', 'layer'], function () {
                    var table = layui.table;
                    var form = layui.form;
                    var dropdown = layui.dropdown;

                    // 创建渲染实例
                    table.render({
                        elem: '#plugin_table',
                        url: '/api/v1/getplugin.php', // 此处为静态模拟数据，实际使用时需换成真实接口
                        toolbar: '#toolbar',
                        defaultToolbar: ['filter', 'exports', 'print'],
                        height: 'full-35', // 最大高度减去其他容器已占有的高度差
                        css: [ // 重设当前表格样式
                            '.layui-table-tool-temp{padding-right: 145px;}'
                        ].join(''),
                        cellMinWidth: 50,
                        totalRow: false, // 开启合计行
                        page: true,
                        cols: [[
                            {type: 'checkbox', fixed: 'left'},
                            {field: 'plugin_name', width: 130, title: '插件名'},
                            {field: 'plugin_info', title: '插件描述', minWidth: 260, expandedWidth: 260},
                            {field: 'is_active', width: 130, title: '是否启用'}, // 隐藏字段
                            {field: 'plugin_author', width: 130, title: '插件作者'},
                            {field: 'plugin_version', width: 130, title: '插件版本'},
                            {field: 'plugin_entry', width: 130, title: '插件入口文件', hide: true},
                            {fixed: 'right', title: '操作', width: 134, minWidth: 125, templet: '#tools'}
                        ]],
                        done: function () {
                            var id = this.id;

                            // 行模式
                            dropdown.render({
                                elem: '#rowMode',
                                data: [{
                                    id: 'default-row',
                                    title: '单行模式（默认）'
                                }, {
                                    id: 'multi-row',
                                    title: '多行模式'
                                }],
                                // 菜单被点击的事件
                                click: function (obj) {
                                    var checkStatus = table.checkStatus(id)
                                    var data = checkStatus.data; // 获取选中的数据
                                    switch (obj.id) {
                                        case 'default-row':
                                            table.reload('plugin_table', {
                                                lineStyle: null // 恢复单行
                                            });
                                            layer.msg('已设为单行');
                                            break;
                                        case 'multi-row':
                                            table.reload('plugin_table', {
                                                // 设置行样式，此处以设置多行高度为例。若为单行，则没必要设置改参数 - 注：v2.7.0 新增
                                                lineStyle: 'height: 95px;'
                                            });
                                            layer.msg('已切换至多行模式');
                                            break;
                                    }
                                }
                            });
                        },
                        error: function (res, msg) {
                            console.log(res, msg)
                        }
                    });

                    // 工具栏事件
                    table.on('toolbar(plugin_table)', function (obj) {
                        var id = obj.config.id;
                        var checkStatus = table.checkStatus(id);
                        var othis = lay(this);
                        switch (obj.event) {
                            case 'getCheckData':
                                var data = checkStatus.data;
                                layer.alert(layui.util.escape(JSON.stringify(data)));
                                break;
                            case 'getData':
                                var getData = table.getData(id);
                                console.log(getData);
                                layer.alert(layui.util.escape(JSON.stringify(getData)));
                                break;
                        }
                    });


                    // 触发单元格工具事件
                    table.on('tool(plugin_table)', function (obj) { // 双击 toolDouble
                        var data = obj.data; // 获得当前行数据
                        // console.log(obj)
                        if (obj.event === 'edit') {
                            layer.open({
                                title: '编辑 - 插件名：' + data.plugin_name,
                                type: 1,
                                area: ['80%', '80%'],
                                content: '<div style="padding: 16px;">还没做</div>'
                            });
                        } else if (obj.event === 'more') {
                            // 更多 - 下拉菜单
                            dropdown.render({
                                elem: this, // 触发事件的 DOM 对象
                                show: true, // 外部事件触发即显示
                                data: [{
                                    title: '激活',
                                    id: 'activate'
                                }, {
                                    title: '禁用',
                                    id: 'deactivate'
                                }, {
                                    title: '检查更新',
                                    id: 'check_update'
                                }, {
                                    title: '删除',
                                    id: 'del'
                                }],
                                click: function (menudata) {
                                    if (menudata.id === 'activate') {
                                        layer.confirm('确定要激活插件 - 插件名：' + data.plugin_name + ' 么？', function (index) {
                                            layer.msg('已提交，服务器处理中');
                                            layer.close(index);
                                            // 向服务端发送指令
                                            var customData = {
                                                action: 'activate',
                                                plugin_name: data.plugin_name,
                                                plugin_entry: data.plugin_entry,
                                            };
                                            $.ajax({
                                                type: 'POST',
                                                url: '/api/v1/plugin.php', // 后端处理脚本的 URL
                                                data: customData, // 发送表单数据
                                                dataType: 'json', // 预期服务器返回的数据类型
                                                success: function (response) {
                                                    // 根据后端响应弹出提示窗口
                                                    if (response.status === 'success') {
                                                        layer.msg(response.message, {icon: 1});
                                                    } else {
                                                        layer.msg(response.message, {icon: 2});
                                                    }
                                                },
                                                error: function () {
                                                    layer.msg('请求失败，请稍后再试', {icon: 2});
                                                }
                                            });
                                            setTimeout(() => {
                                                table.reload('plugin_table');
                                            }, 300);
                                        });
                                    } else if (menudata.id === 'deactivate') {
                                        layer.confirm('确认是否禁用插件 - 插件名：' + data.plugin_name + ' 么？', function (index) {
                                            // obj.del(); // 删除对应行（tr）的DOM结构
                                            layer.msg('已提交，服务器处理中');
                                            layer.close(index);
                                            // 向服务端发送指令
                                            var customData = {
                                                action: 'deactivate',
                                                plugin_name: data.plugin_name,
                                                plugin_entry: data.plugin_entry,
                                            };
                                            $.ajax({
                                                type: 'POST',
                                                url: '/api/v1/plugin.php', // 后端处理脚本的 URL
                                                data: customData, // 发送表单数据
                                                dataType: 'json', // 预期服务器返回的数据类型
                                                success: function (response) {
                                                    // 根据后端响应弹出提示窗口
                                                    if (response.status === 'success') {
                                                        layer.msg(response.message, {icon: 1});
                                                    } else {
                                                        layer.msg(response.message, {icon: 2});
                                                    }
                                                },
                                                error: function () {
                                                    layer.msg('请求失败，请稍后再试', {icon: 2});
                                                }
                                            });
                                            setTimeout(() => {
                                                table.reload('plugin_table');
                                            }, 300);
                                        });
                                    } else if (menudata.id === 'check_update') {
                                        layer.confirm('是否检查插件 ' + data.plugin_name + ' 的更新？插件必须定义更新地址才能检查！', function (index) {
                                            layer.msg('已提交，服务器处理中');
                                            layer.close(index);
                                            // 向服务端发送指令
                                            var customData = {
                                                action: 'check_update',
                                                plugin_name: data.plugin_name,
                                                plugin_entry: data.plugin_entry,
                                            };
                                            $.ajax({
                                                type: 'POST',
                                                url: '/api/v1/plugin.php', // 后端处理脚本的 URL
                                                data: customData, // 发送表单数据
                                                dataType: 'json', // 预期服务器返回的数据类型
                                                success: function (response) {
                                                    // 根据后端响应弹出提示窗口
                                                    if (response.status === 'success') {
                                                        layer.msg(response.message, {icon: 1});
                                                    } else {
                                                        layer.msg(response.message, {icon: 2});
                                                    }
                                                },
                                                error: function () {
                                                    layer.msg('请求失败，请稍后再试', {icon: 2});
                                                }
                                            });
                                            setTimeout(() => {
                                                table.reload('plugin_table');
                                            }, 300);
                                        });
                                    } else if (menudata.id === 'del') {
                                        layer.confirm('真的要删除 - 插件名：' + data.plugin_name + ' 吗？', function (index) {
                                            layer.msg('已提交，服务器处理中');
                                            layer.close(index);
                                            // 向服务端发送指令
                                            var customData = {
                                                action: 'del',
                                                plugin_name: data.plugin_name,
                                                plugin_entry: data.plugin_entry,
                                            };
                                            $.ajax({
                                                type: 'POST',
                                                url: '/api/v1/plugin.php', // 后端处理脚本的 URL
                                                data: customData, // 发送表单数据
                                                dataType: 'json', // 预期服务器返回的数据类型
                                                success: function (response) {
                                                    // 根据后端响应弹出提示窗口
                                                    if (response.status === 'success') {
                                                        layer.msg(response.message, {icon: 1});
                                                    } else {
                                                        layer.msg(response.message, {icon: 2});
                                                    }
                                                },
                                                error: function () {
                                                    layer.msg('请求失败，请稍后再试', {icon: 2});
                                                }
                                            });
                                            setTimeout(() => {
                                                table.reload('plugin_table');
                                            }, 300);
                                        });
                                    }

                                },
                                id: 'dropdown-table-tool',
                                align: 'right', // 右对齐弹出
                                style: 'box-shadow: 1px 1px 10px rgb(0 0 0 / 12%);' // 设置额外样式
                            });
                        }
                    });

                    // table 滚动时移除内部弹出的元素
                    var tableInst = table.getOptions('plugin_table');
                    tableInst.elem.next().find('.layui-table-main').on('scroll', function () {
                        dropdown.close('dropdown-table-tool');
                    });
                    // 搜索提交
                    form.on('submit(table-search)', function (data) {
                        var field = data.field; // 获得表单字段
                        // 执行搜索重载
                        table.reload('plugin_table', {
                            page: {
                                curr: 1 // 重新从第 1 页开始
                            },
                            where: field // 搜索的字段
                        });
                        // layer.msg('搜索成功<br>此处为静态模拟数据，实际使用时换成真实接口即可');
                        return false; // 阻止默认 form 跳转
                    });
                });
            </script>
            <div style="height: 50px"></div>
        </div>
    </div>
    <div class="layui-footer">
        <!-- 底部固定区域 -->
        <p>“自由”二字看似条条框框，但总有一笔冲出牢笼。</p>
    </div>
</div>

</body>
</html>
