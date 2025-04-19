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
            <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
                <legend>新增备案</legend>
            </fieldset>
            <form class="layui-form" action="">
                <div class="layui-form-item layui-row">
                    <div class="layui-col-xs4">
                        <label class="layui-form-label">网站名称</label>
                        <div class="layui-input-block layui-input-wrap">
                            <input type="text" name="site_name" lay-verify="required" placeholder="请输入"
                                   autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-col-xs4">
                        <label class="layui-form-label">网站信息</label>
                        <div class="layui-input-block layui-input-wrap">
                            <div class="layui-input-prefix layui-input-split">
                                <i class="layui-icon layui-icon-about"></i>
                            </div>
                            <input type="text" name="site_info" lay-verify="required" placeholder="请输入"
                                   autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-col-xs4">
                        <label class="layui-form-label">加入日期</label>
                        <div class="layui-input-block layui-input-wrap">
                            <div class="layui-input-prefix layui-input-split">
                                <i class="layui-icon layui-icon-date"></i>
                            </div>
                            <input type="text" class="layui-input" name="datetime" id="ID-laydate-type-datetime-1"
                                   placeholder="年月日-时分秒" autocomplete="off" lay-verify="required|datetime">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-col-xs4">
                        <label class="layui-form-label">网站URL</label>
                        <div class="layui-input-block layui-input-wrap">
                            <div class="layui-input-prefix layui-input-split">
                                <i class="layui-icon layui-icon-link"></i>
                            </div>
                            <input type="text" name="site_url" lay-verify="required|site_url"
                                   placeholder="https://example.com" autocomplete="off" class="layui-input">

                        </div>
                    </div>
                    <div class="layui-col-xs4">
                        <label class="layui-form-label">备案号</label>
                        <div class="layui-input-block">
                            <input type="text" name="icp_number" placeholder="00000000" lay-verify="required|icp_number"
                                   autocomplete="off" class="layui-input" title="请输入8位数字"></div>
                    </div>
                    <div class="layui-col-xs4">
                        <label class="layui-form-label">备案状态</label>
                        <div class="layui-input-block">
                            <select name="interest" lay-filter="icp_status" lay-verify="required">
                                <option value="pass">备案通过</option>
                                <option value="wait" selected>待审核</option>
                                <option value="deny">被驳回</option>
                                <option value="del">被删除</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item layui-row">
                    <div class="layui-col-xs4">
                        <label class="layui-form-label">所有者</label>
                        <div class="layui-input-block layui-input-wrap">
                            <div class="layui-input-prefix layui-input-split">
                                <i class="layui-icon layui-icon-username"></i>
                            </div>
                            <input type="text" name="owner" lay-verify="required" placeholder="请输入"
                                   autocomplete="off"
                                   class="layui-input">
                        </div>
                    </div>
                    <div class="layui-col-xs4">
                        <label class="layui-form-label">邮箱</label>
                        <div class="layui-input-block layui-input-wrap">
                            <div class="layui-input-prefix layui-input-split">
                                <i class="layui-icon layui-icon-email"></i>
                            </div>
                            <input type="text" name="email" lay-verify="required|email" placeholder="admin@example.com"
                                   autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-col-xs4">
                        <label class="layui-form-label">QQ</label>
                        <div class="layui-input-block layui-input-wrap">
                            <div class="layui-input-prefix layui-input-split">
                                <i class="layui-icon layui-icon-login-qq"></i>
                            </div>
                            <input type="text" name="qq" lay-verify="required" placeholder="请输入" autocomplete="off"
                                   class="layui-input">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item layui-row">
                    <div class="layui-col-xs4">
                        <label class="layui-form-label">安全码</label>
                        <div class="layui-input-block layui-input-wrap">
                            <div class="layui-input-prefix layui-input-split">
                                <i class="layui-icon layui-icon-password"></i>
                            </div>
                            <input type="password" name="security_code" lay-verify="required" placeholder="请输入"
                                   autocomplete="off" lay-affix="eye" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-mid layui-text-em">此特性即将废弃</div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button type="submit" class="layui-btn" lay-submit lay-filter="submit_icp">新增备案</button>
                        <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                    </div>
                </div>
            </form>
            <script>
                layui.use(['form', 'laydate', 'util'], function () {
                    var form = layui.form;
                    var layer = layui.layer;
                    var laydate = layui.laydate;
                    var util = layui.util;

                    // 提交事件
                    form.on('submit(submit_icp)', function (data) {
                        var field = data.field; // 获取表单字段值
                        // 显示填写结果，仅作演示用
                        layer.alert(JSON.stringify(field), {
                            title: '当前填写的字段值'
                        });
                        // 此处可执行 Ajax 等操作
                        // …
                        return false; // 阻止默认 form 跳转
                    });
                    // 自定义验证规则
                    form.verify({
                        icp_number: function (value) {
                            if (!/^\d{8}$/.test(value)) {
                                return '备案号必须为8位数字';
                            }
                        }
                    });
                    form.verify({
                        site_url: function (value) {
                            if (!/^(https?:\/\/)([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/.test(value)) {
                                return '输入的不是有效的URL';
                            }
                        }
                    });
                });
            </script>
            <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
                <legend>备案搜索</legend>
            </fieldset>
            <form class="layui-form layui-row layui-col-space16">
                <div class="layui-col-md4">
                    <div class="layui-input-wrap">
                        <div class="layui-input-prefix">
                            <i class="layui-icon layui-icon-username"></i>
                        </div>
                        <input type="text" name="owner" value="" placeholder="所有者" class="layui-input"
                               lay-affix="clear">
                    </div>
                </div>
                <div class="layui-col-md4">
                    <div class="layui-input-wrap">
                        <input type="text" name="site_name" placeholder="网站名称" lay-affix="clear"
                               class="layui-input">
                    </div>
                </div>
                <div class="layui-col-md4">
                    <div class="layui-input-wrap">
                        <input type="text" name="qq" placeholder="QQ" lay-affix="clear"
                               class="layui-input">
                    </div>
                </div>
                <div class="layui-col-md4">
                    <div class="layui-input-wrap">
                        <input type="text" name="icp_number" placeholder="备案号" lay-affix="clear"
                               class="layui-input">
                    </div>
                </div>
                <div class="layui-col-md4">
                    <div class="layui-input-wrap">
                        <input type="text" name="site_url" placeholder="网站URL" lay-affix="clear"
                               class="layui-input">
                    </div>
                </div>
                <div class="layui-col-md4">
                    <div class="layui-input-wrap">
                        <input type="text" name="email" placeholder="邮箱" lay-affix="clear"
                               class="layui-input">
                    </div>
                </div>
                <div class="layui-btn-container layui-col-xs12">
                    <button class="layui-btn" lay-submit lay-filter="table-search">搜索</button>
                    <button type="reset" class="layui-btn layui-btn-primary">清空</button>
                </div>
            </form>
            <table class="layui-hide" id="icp_table" lay-filter="icp_table"></table>
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
                        elem: '#icp_table',
                        url: '/api/v1/geticp.php', // 此处为静态模拟数据，实际使用时需换成真实接口
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
                            {field: 'id', fixed: 'left', width: 50, title: 'ID'},
                            {field: 'website_name', width: 130, title: '网站名'},
                            {field: 'website_url', width: 150, title: '网站URL'},
                            {field: 'website_info', title: '网站描述', minWidth: 260, expandedWidth: 260},
                            {field: 'icp_number', width: 150, title: 'ICP备案号'},
                            {field: 'owner', title: '所有者', width: 100, sort: true},
                            {field: 'update_time', title: '更新时间', width: 120},
                            {field: 'STATUS', title: '状态', width: 180},
                            {field: 'qq', title: 'QQ', width: 150},
                            {field: 'email', title: '邮箱', fieldTitle: '邮箱', width: 200},
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
                                            table.reload('icp_table', {
                                                lineStyle: null // 恢复单行
                                            });
                                            layer.msg('已设为单行');
                                            break;
                                        case 'multi-row':
                                            table.reload('icp_table', {
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
                    table.on('toolbar(icp_table)', function (obj) {
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
                    table.on('tool(icp_table)', function (obj) { // 双击 toolDouble
                        var data = obj.data; // 获得当前行数据
                        // console.log(obj)
                        if (obj.event === 'edit') {
                            layer.open({
                                title: '编辑 - id:' + data.id + ' - 网站名：' + data.website_name + ' - 备案号：' + data.icp_number,
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
                                    title: '审核通过',
                                    id: 'pass'
                                }, {
                                    title: '驳回审核',
                                    id: 'deny'
                                }, {
                                    title: '联系所有者',
                                    id: 'contact'
                                }, {
                                    title: '删除',
                                    id: 'del'
                                }],
                                click: function (menudata) {
                                    if (menudata.id === 'pass') {
                                        layer.confirm('请确定是否通过 - id:' + data.id + ' - 网站名：' + data.website_name + ' - 备案号：' + data.icp_number + ' 的备案信息？', function (index) {
                                            layer.msg('已通过');
                                            layer.close(index);
                                            // 向服务端发送删除指令
                                        });
                                    } else if (menudata.id === 'del') {
                                        layer.confirm('真的要删除 - id:' + data.id + ' - 网站名：' + data.website_name + ' - 备案号：' + data.icp_number + ' 么？', function (index) {
                                            obj.del(); // 删除对应行（tr）的DOM结构
                                            layer.msg('已删除');
                                            layer.close(index);
                                            // 向服务端发送删除指令
                                        });
                                    } else if (menudata.id === 'deny') {
                                        layer.confirm('请确定是否驳回 - id:' + data.id + ' - 网站名：' + data.website_name + ' - 备案号：' + data.icp_number + ' 的备案信息？', function (index) {
                                            layer.msg('已驳回');
                                            layer.close(index);
                                            // 向服务端发送删除指令
                                        });
                                    } else if (menudata.id === 'contact') {
                                        layer.confirm('真的要删除 - id:' + data.id + ' - 网站名：' + data.website_name + ' - 备案号：' + data.icp_number + '么？', function (index) {
                                            layer.msg('已驳回');
                                            layer.close(index);
                                            // 向服务端发送删除指令
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
                    var tableInst = table.getOptions('icp_table');
                    tableInst.elem.next().find('.layui-table-main').on('scroll', function () {
                        dropdown.close('dropdown-table-tool');
                    });
                    // 搜索提交
                    form.on('submit(table-search)', function (data) {
                        var field = data.field; // 获得表单字段
                        // 执行搜索重载
                        table.reload('icp_table', {
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
        <p>“自由”二字看似条条框框，但总有一笔冲出牢笼。</p>
    </div>
</div>


<script>
    layui.use(function () {
        var laydate = layui.laydate;
        laydate.render({
            elem: '#ID-laydate-type-datetime-1',
            type: 'datetime',
            fullPanel: true // 2.8+
        });
    });
</script>

</body>
</html>