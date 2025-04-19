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
initDatabase();
// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Directly use the POST data without sanitizing it since it's HTML content
    $footer_code = $_POST['footer']; //页脚内容
    $website_name = $_POST['website_name']; //网站名
    $website_url = $_POST['website_url']; //网站url
    $website_avatar = $_POST['website_avatar']; //网站图标
    $website_abbr = $_POST['website_abbr']; //site abbr
    $website_keywords = $_POST['website_keywords']; //站点SEO关键字
    $website_description = $_POST['website_description']; //站点SEO描述
    $website_admin_nickname = $_POST['website_admin_nickname']; //站点管理员昵称
    $website_admin_email = $_POST['website_admin_email']; //站点管理员邮箱
    $website_admin_qq = $_POST['website_admin_qq']; //站点管理员QQ
    $website_feedback_link = $_POST['website_feedback_link']; //站点反馈链接
    $website_bg = $_POST['website_bg_image']; //站点背景图片
    $enable_template_name = $_POST['enable_template_name']; // 启用主题的文件夹名

    if (isset($_POST['redis'])) {
        if (file_exists(dirname(__FILE__) . '/../redis.lock')) {
            unlink(dirname(__FILE__) . '/../redis.lock');
        }
    } else {
        file_put_contents(dirname(__FILE__) . '/../redis.lock', 'redis is disable');
    }

    // Prepare the SQL statement
    $sql = "UPDATE website_info SET footer_code = :footer , site_name = :website_name , site_url = :site_url , 
                        site_avatar = :site_avatar , site_abbr = :site_abbr , 
                        site_keywords = :site_keywords , site_description = :site_description , 
                        admin_nickname = :admin_nickname , admin_email = :admin_email , 
                        admin_qq = :admin_qq , feedback_link = :website_feedback_link , 
                        background_image = :website_bg_image , enable_template_name = :enable_template_name  WHERE 1 LIMIT 1";
    $stmt = $pdo->prepare($sql);

    // Bind the parameter
    $stmt->bindParam(':footer', $footer_code);
    $stmt->bindParam(':website_name', $website_name);
    $stmt->bindParam(':site_url', $website_url);
    $stmt->bindParam(':site_avatar', $website_avatar);
    $stmt->bindParam(':site_abbr', $website_abbr);
    $stmt->bindParam(':site_keywords', $website_keywords);
    $stmt->bindParam(':site_description', $website_description);
    $stmt->bindParam(':admin_nickname', $website_admin_nickname);
    $stmt->bindParam(':admin_email', $website_admin_email);
    $stmt->bindParam(':admin_qq', $website_admin_qq);
    $stmt->bindParam(':website_feedback_link', $website_feedback_link);
    $stmt->bindParam(':website_bg_image', $website_bg);
    $stmt->bindParam(':enable_template_name', $enable_template_name);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<script>alert('设置已保存。');</script>";
    } else {
        // Capture any errors during execution
        $errorInfo = $stmt->errorInfo();
        echo "保存失败: " . $errorInfo[2]; // Display the error message
    }
}

// Retrieve the current website info
$query = "SELECT * FROM website_info LIMIT 1";
$stmt = $pdo->query($query);
$websiteInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if we got the data
if (!$websiteInfo) {
    die("网站信息不存在");
}
extract($websiteInfo); // Extract the array keys as variable names and values
?>
<div id="sidebar"><?php include 'sidebar.php'; ?></div>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>ICP Records Backend</title>
    <style>
        .settings {
            position: fixed;
            top: 5%;
            left: 20%;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 20px auto;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="checkbox"] {
            margin-bottom: 20px;
        }

        input[type="submit"] {
            background-color: #5cb85c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #4cae4c;
        }
    </style>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/settings.css">
</head>
<body>
<div class="settings" id="icpTable">
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="text-align: center">
        <h2>页脚内容</h2>
        <textarea name="footer" style="width: 300px;height: 300px"><?php echo $footer_code; ?></textarea>
        <h2>站点名称</h2>
        <input type="text" name="website_name" value="<?php echo $site_name; ?>">
        <h2>站点URL</h2>
        <input type="text" name="website_url" value="<?php echo $site_url; ?>">
        <h2>站点图标</h2>
        <input type="text" name="website_avatar" value="<?php echo $site_avatar; ?>">
        <h2>站点简称</h2>
        <input type="text" name="website_abbr" value="<?php echo $site_abbr; ?>">
        <h2>站点关键词</h2>
        <input type="text" name="website_keywords" value="<?php echo $site_keywords; ?>">
        <h2>站点描述</h2>
        <input type="text" name="website_description" value="<?php echo $site_description; ?>">
        <h2>站点管理员昵称</h2>
        <input type="text" name="website_admin_nickname" value="<?php echo $admin_nickname; ?>">
        <h2>站点管理员邮箱</h2>
        <input type="text" name="website_admin_email" value="<?php echo $admin_email; ?>">
        <h2>站点管理员QQ</h2>
        <input type="text" name="website_admin_qq" value="<?php echo $admin_qq; ?>">
        <h2>站点反馈链接</h2>
        <input type="text" name="website_feedback_link" value="<?php echo $feedback_link; ?>">
        <h2>站点背景图片</h2>
        <input type="text" name="website_bg_image" value="<?php echo $background_image; ?>">
        <h2>是否启用Redis</h2>
        <span>勾选则启用，取消勾选则禁用：</span>
        <input type="checkbox" name="redis"
               class="checkbox-class" <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/redis.lock')) {
            echo '';
        } else {
            echo 'checked';
        } ?>>
        <h2>启用的模板名称</h2>
        <input type="text" name="enable_template_name" id="enable_template_name" value="<?php echo $enable_template_name; ?>">
        <button id="testButton">测试模板可用性</button>
        <script>
            document.getElementById('testButton').addEventListener('click', function(event) {
                // 阻止表单默认提交行为
                event.preventDefault();

                var templateName = document.getElementById('enable_template_name').value;
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'settings_test.php?template=' + encodeURIComponent(templateName), true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        alert(xhr.responseText); // 显示来自settings_test.php的响应
                    }
                };
                xhr.send();
            });
        </script>

        <div style="height: 30px;width: 5px"></div>
        <button type="submit" class="button-class">保存</button>
    </form>
</div>
</body>
</html>