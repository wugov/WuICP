<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>登陆</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/layui/2.9.20/css/layui.min.css"
          integrity="sha512-54WyiQNseHG9u6y5QlRZQU5Xqh1llgctSRJPE26UOlXB22P+Bnt6jZT8+bf5GfyiNaY77ZLdGcgtLF3NuyVvWQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/layui/2.9.20/layui.min.js"
            integrity="sha512-z2UATz8GsuKCOTbw4ML/6YvZeAhEQsm3mSawEWnxdq65bDtMoXp501kvS93JyZ95onfEZqf/vykl3M4If4nGaw=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        .login-container {
            width: 320px;
            margin: 21px auto 0;
        }

        .login-other .layui-icon {
            position: relative;
            display: inline-block;
            margin: 0 2px;
            top: 2px;
            font-size: 26px;
        }

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
<form class="layui-form">
    <div class="login-container">
        <div class="layui-form-item">
            <div class="layui-input-wrap">
                <div class="layui-input-prefix">
                    <i class="layui-icon layui-icon-email"></i>
                </div>
                <input type="text" name="email" value="" lay-verify="required" placeholder="邮箱"
                       lay-reqtext="请填写邮箱" autocomplete="off" class="layui-input" lay-affix="clear">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-wrap">
                <div class="layui-input-prefix">
                    <i class="layui-icon layui-icon-password"></i>
                </div>
                <input type="password" name="password" value="" lay-verify="required" placeholder="密   码"
                       lay-reqtext="请填写密码" autocomplete="off" class="layui-input" lay-affix="eye">
            </div>
        </div>
        <div class="layui-form-item">
            <button id="loginBtn" class="layui-btn layui-btn-fluid" lay-submit lay-filter="login">
                <span class="layui-btn-text">登录</span>
                <i class="layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop"></i>
            </button>
        </div>
    </div>
</form>

<script>
    layui.use(function () {
        var form = layui.form;
        var layer = layui.layer;
        var $ = layui.jquery; // 使用 layui 内置的 jQuery

        // 提交事件
        form.on('submit(login)', function (data) {
            var field = data.field; // 获取表单字段值
            var $loginBtn = $('#loginBtn');

            // 禁用按钮并显示加载动画
            $loginBtn.addClass('loading').prop('disabled', true);

            // 发送 AJAX 请求
            $.ajax({
                url: '/api/v1/login.php', // 目标 URL
                type: 'POST', // 请求方法
                data: field, // 发送的数据
                dataType: 'json', // 预期服务器返回的数据类型
                success: function (res) {
                    if (res.status === 'success') {
                        // 登录成功，可以重定向到首页或其他操作
                        layer.msg('登录成功', {icon: 1}, function () {
                            window.location.href = '/admin-next/index.php';
                        });
                    } else {
                        // 登录失败，显示错误信息
                        layer.msg(res.message, {icon: 2});
                    }
                },
                error: function () {
                    layer.msg('请求失败，请稍后再试', {icon: 2});
                },
                complete: function () {
                    // 启用按钮并隐藏加载动画
                    $loginBtn.removeClass('loading').prop('disabled', false);
                }
            });

            return false; // 阻止默认 form 跳转
        });
    });
</script>

</body>
</html>
