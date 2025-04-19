<?php
//define('ABSPATH', dirname(__DIR__));
global $pdo;
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
if (!checkUserLogin()) {
    header("Location: login.php");
    exit();
}
initDatabase();

// Retrieve the current website info
$query = "SELECT * FROM website_info LIMIT 1";
$stmt = $pdo->query($query);
$websiteInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if we got the data
if (!$websiteInfo) {
    die("网站信息不存在");
}
extract($websiteInfo); // 将数组转换为变量
/*
 * 转换后的变量：
 * site_name 网站名
 * site_url 网站URL
 * site_avatar 网站图标
 * site_abbr 网站简称
 * site_keywords 网站SEO关键字
 * site_description 网站SEO描述
 * admin_nickname 管理员昵称
 * admin_email 管理员邮箱
 * admin_qq 管理员QQ
 * footer_code 页脚代码
 * audit_duration 审核时长
 * feedback_link 反馈链接
 * background_image 背景图片
 * enable_template_name 启用的模板文件夹名
 */

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
    <!--    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"-->
    <!--            integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="-->
    <!--            crossorigin="anonymous" referrerpolicy="no-referrer"></script>-->
    <style>
        /* 隐藏加载动画 */
        .layui-btn .layui-icon-loading {
            display: none;
        }

        /* 显示加载动画时隐藏按钮文本 */
        .layui-btn.loading .layui-btn-text {
            display: none;
        }

        .layui-btn.loading .layui-icon-loading {
            display: inline-block;
        }
    </style>
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
            <form class="layui-form" action="">
                <div class="layui-form-item layui-row">
                    <div class="layui-block layui-col-xs4">
                        <label class="layui-form-label">站点名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="website_name" lay-verify="required" placeholder="请输入"
                                   autocomplete="off" class="layui-input" value="<?php echo $site_name; ?>">
                        </div>
                    </div>
                    <div class="layui-block layui-col-xs4">
                        <label class="layui-form-label">网站简称</label>
                        <div class="layui-input-block">
                            <input type="text" name="website_abbr" placeholder="请输入" autocomplete="off"
                                   class="layui-input" value="<?php echo $site_abbr; ?>">
                        </div>
                    </div>
                </div>

                <div class="layui-form-item layui-row">
                    <div class="layui-block">
                        <label class="layui-form-label">站点URL</label>
                        <div class="layui-input-block">
                            <input type="text" name="website_url" placeholder="请输入" autocomplete="off"
                                   class="layui-input" lay-verify="url" value="<?php echo $site_url; ?>">
                        </div>
                    </div>
                </div>

                <div class="layui-form-item layui-row">
                    <label class="layui-form-label">网站图标（必须为URL）</label>
                    <div class="layui-input-block layui-input-wrap">
                        <input type="text" name="website_avatar" lay-verify="url" placeholder="请输入"
                               autocomplete="off" lay-affix="clear"
                               class="layui-input" value="<?php echo $site_avatar; ?>">
                    </div>
                </div>

                <div class="layui-form-item layui-row">
                    <div class="layui-block">
                        <label class="layui-form-label">SEO关键词（使用英文逗号分隔）</label>
                        <div class="layui-input-block">
                            <input type="text" name="website_keywords" placeholder="请输入" autocomplete="off"
                                   class="layui-input" value="<?php echo $site_keywords; ?>">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item layui-row">
                    <div class="layui-block">
                        <label class="layui-form-label">SEO描述</label>
                        <div class="layui-input-block">
                            <input type="text" name="website_description" placeholder="请输入" autocomplete="off"
                                   class="layui-input" value="<?php echo $site_description; ?>">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item layui-row">
                    <div class="layui-block">
                        <label class="layui-form-label">站点管理员昵称</label>
                        <div class="layui-input-block">
                            <input type="text" name="website_admin_nickname" placeholder="请输入" autocomplete="off"
                                   class="layui-input" value="<?php echo $admin_nickname; ?>">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item layui-row">
                    <div class="layui-block">
                        <label class="layui-form-label">站点管理员邮箱</label>
                        <div class="layui-input-block">
                            <input type="text" name="website_admin_email" placeholder="请输入" autocomplete="off"
                                   class="layui-input" lay-verify="email" value="<?php echo $admin_email; ?>">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item layui-row">
                    <div class="layui-block">
                        <label class="layui-form-label">站点管理员QQ</label>
                        <div class="layui-input-block">
                            <input type="text" name="website_admin_qq" placeholder="请输入" autocomplete="off"
                                   class="layui-input" value="<?php echo $admin_qq; ?>">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item layui-row">
                    <div class="layui-block">
                        <label class="layui-form-label">站点反馈链接</label>
                        <div class="layui-input-block">
                            <input type="text" name="website_feedback_link" placeholder="请输入" autocomplete="off"
                                   class="layui-input" lay-verify="url" value="<?php echo $feedback_link; ?>">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item layui-row">
                    <div class="layui-block">
                        <label class="layui-form-label">站点背景图片</label>
                        <div class="layui-input-block">
                            <input type="text" name="website_bg_image" placeholder="请输入" autocomplete="off"
                                   class="layui-input" lay-verify="url" value="<?php echo $background_image; ?>">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item layui-row">
                    <div class="layui-block">
                        <label class="layui-form-label">启用的模板名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="enable_template_name" id="enable_template_name"
                                   placeholder="请输入" autocomplete="off"
                                   class="layui-input" value="<?php echo $enable_template_name; ?>">
                            <br>
                            <button id="testButton" class="layui-btn">测试模板可用性</button>
                            <script>
                                document.getElementById('testButton').addEventListener('click', function (event) {
                                    // 阻止表单默认提交行为
                                    event.preventDefault();

                                    var templateName = document.getElementById('enable_template_name').value;
                                    var xhr = new XMLHttpRequest();
                                    xhr.open('GET', 'settings_test.php?template=' + encodeURIComponent(templateName), true);
                                    xhr.onreadystatechange = function () {
                                        if (xhr.readyState == 4 && xhr.status == 200) {
                                            alert(xhr.responseText); // 显示来自settings_test.php的响应
                                        }
                                    };
                                    xhr.send();
                                });
                            </script>
                        </div>
                    </div>
                </div>
                <!--                <div class="layui-form-item layui-row"> 这些留着以后完善模板选择了再弄-->
                <!--                    <label class="layui-form-label">单行选择框</label>-->
                <!--                    <div class="layui-input-block">-->
                <!--                        <select name="interest" lay-filter="aihao">-->
                <!--                            <option value=""></option>-->
                <!--                            <option value="0">写作</option>-->
                <!--                            <option value="1" selected>阅读</option>-->
                <!--                            <option value="2">游戏</option>-->
                <!--                            <option value="3">音乐</option>-->
                <!--                            <option value="4">旅行</option>-->
                <!--                        </select>-->
                <!--                    </div>-->
                <!--                </div>-->
                <!--                <div class="layui-form-item layui-row"> 留着做功能选择吧-->
                <!--                    <label class="layui-form-label">复选框</label>-->
                <!--                    <div class="layui-input-block">-->
                <!--                        <input type="checkbox" name="arr[0]" title="选项1">-->
                <!--                        <input type="checkbox" name="arr[1]" title="选项2" checked>-->
                <!--                        <input type="checkbox" name="arr[2]" title="选项3">-->
                <!--                    </div>-->
                <!--                        </div>-->
                <div class="layui-form-item layui-row">
                    <label class="layui-form-label">Redis状态</label>
                    <div class="layui-input-block">
                        <input type="checkbox" name="redis_sw" lay-skin="switch" lay-filter="redis_sw"
                               title="启用|禁用" <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/redis.lock')) {
                            echo '';
                        } else {
                            echo 'checked';
                        } ?>>
                    </div>
                </div>
                <div class="layui-form-item layui-row layui-form-text">
                    <label class="layui-form-label">页脚代码</label>
                    <div class="layui-input-block">
                        <div id="container" style="width: 800px; height: 600px; border: 1px solid grey"></div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <button id="saveBtn" class="layui-btn layui-btn-fluid" lay-submit lay-filter="setting_table">
                        <span class="layui-btn-text">保存设置</span>
                        <i class="layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop"></i>
                    </button>
                </div>
            </form>

            <script>
                var require = {paths: {vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs'}};
            </script>
            <!-- OR ANY OTHER AMD LOADER HERE INSTEAD OF loader.js -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/loader.min.js"
                    integrity="sha512-ZG31AN9z/CQD1YDDAK4RUAvogwbJHv6bHrumrnMLzdCrVu4HeAqrUX7Jsal/cbUwXGfaMUNmQU04tQ8XXl5Znw=="
                    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/editor/editor.main.js"
                    integrity="sha512-Rvm80pldXwjZQPHcW9Dx3U8pygTafC25yXwrxrPC6ZuNVgBsR2BN2pFAU2ImvYYeFcUaKo7bRcf7KxHD+LMp6w=="
                    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/nls.messages.zh-cn.min.js"
                    integrity="sha512-sP8Z8jdZllDwimPaSG6r2Eh64Nyoq3zD7wyGIhApPGIZ2ispHLhL09842UkAnfSpQ2klL+JVLsEqglFsMtDA2A=="
                    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
            <script>
                require(['vs/editor/editor.main'], function () {
                    // 创建 Monaco Editor 实例
                    var editor = monaco.editor.create(document.getElementById('container'), {
                        value: <?php echo json_encode($footer_code); ?>,
                        language: 'html',
                        theme: 'vs-dark'
                    });

                    layui.use(function () {
                        var form = layui.form;
                        var layer = layui.layer;
                        var $ = layui.jquery;

                        // 提交事件
                        form.on('submit(setting_table)', function (data) {
                            var field = data.field; // 获取表单字段值
                            var $saveBtn =$('#saveBtn');

                            // 获取编辑器内容
                            var footer = editor.getValue();

                            // 将编辑器内容添加到要发送的数据中
                            field.footer = footer;

                            // 禁用按钮并显示加载动画
                            $saveBtn.addClass('loading').prop('disabled', true);

                            // 添加AJAX请求
                            $.ajax({
                                url: '/api/v1/sets.php', // 目标 URL
                                type: 'POST', // 请求方法
                                data: field, // 发送的数据，包括编辑器内容
                                dataType: 'json', // 预期服务器返回的数据类型
                                success: function (res) {
                                    if (res.status === 'success') {
                                        layer.msg('已保存', {icon: 1}, function () {
                                            // 可以在这里添加额外的逻辑，比如关闭弹层或跳转页面
                                        });
                                    } else {
                                        layer.msg(res.message, {icon: 2});
                                    }
                                },
                                error: function () {
                                    layer.msg('请求失败，请稍后再试', {icon: 2});
                                },
                                complete: function () {
                                    $saveBtn.removeClass('loading').prop('disabled', false);
                                }
                            });

                            return false; // 阻止默认 form 跳转
                        });
                    });
                });
            </script>

        </div>
    </div>
</div>
<div class="layui-footer">
    <p>“自由”二字看似条条框框，但总有一笔冲出牢笼。</p>
</div>

</body>
</html>