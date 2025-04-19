<?php
//define('ABSPATH', dirname(__DIR__));
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';
global $pdo;
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
if (!checkUserLogin()) {
    header("Location: login.php");
    exit();
}
initDatabase();
// 如果表单被提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 清理和验证输入
    $website_name = trim($_POST['website_name']);
    $website_url = trim($_POST['website_url']);
    $website_info = trim($_POST['website_info']);
    $icp_number = trim($_POST['icp_number']);
    $owner = trim($_POST['owner']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $qq = trim($_POST['qq']);
    $security_code = trim($_POST['security_code']); // 获取安全码

    // 验证输入
    if (empty($website_name) || empty($website_url) || empty($icp_number) || empty($owner) || empty($security_code)) {
        echo "<script>alert('请填写所有必填字段');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('无效的邮箱地址');</script>";
    } else {
        // 检查备案号是否已存在
        $check_icp_number_sql = "SELECT COUNT(*) FROM icp_records WHERE icp_number = :icp_number";
        $check_icp_number_stmt = $pdo->prepare($check_icp_number_sql);
        $check_icp_number_stmt->bindParam(':icp_number', $icp_number);
        $check_icp_number_stmt->execute();
        $icp_number_exists = $check_icp_number_stmt->fetch(PDO::FETCH_ASSOC);


        // 检查域名是否已存在
        $check_website_url_sql = "SELECT COUNT(*) FROM icp_records WHERE website_url = :website_url";
        $check_website_url_stmt = $pdo->prepare($check_website_url_sql);
        $check_website_url_stmt->bindParam(':website_url', $website_url);
        $check_website_url_stmt->execute();
        $website_url_exists = $check_website_url_stmt->fetchColumn() > 0;

        // 检查备案号是否为8位纯数字
        if (!preg_match('/^\d{8}$/', $icp_number)) {
            echo "<script>alert('备案号必须是8位纯数字，早就猜到你会瞎改参数。');</script>";
            echo "window.location.href = 'add_adm.php';";
            exit;
        }

        // 接下来是检查备案号是否存在的代码
        if ($icp_number_exists && $icp_number_exists['COUNT(*)'] > 0) {
            echo "<script>alert('备案号已被占用'); window.location.href = 'add_adm.php';</script>";
            exit;
        }

        // 接下来是检查域名是否存在的代码
        if ($website_url_exists) {
            echo "<script>alert('域名已存在'); window.location.href = 'add_adm.php';</script>";
            exit;
        } else {
            // 使用预处理语句插入数据
            $sql = "INSERT INTO icp_records (website_name, website_url, website_info, icp_number, owner, update_time, email, qq, security_code) 
                    VALUES (:website_name, :website_url, :website_info, :icp_number, :owner, NOW(), :email, :qq, :security_code)";
            $stmt = $pdo->prepare($sql);

            // 绑定参数
            $stmt->bindParam(':website_name', $website_name);
            $stmt->bindParam(':website_url', $website_url);
            $stmt->bindParam(':website_info', $website_info);
            $stmt->bindParam(':icp_number', $icp_number);
            $stmt->bindParam(':owner', $owner);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':qq', $qq);
            $stmt->bindParam(':security_code', $security_code); // 绑定安全码参数

            // 执行预处理语句
            $stmt->execute();

            echo "<script>alert('已添加！');";
            echo "window.location.href = '../xg.php?keyword=" . htmlspecialchars($icp_number) . "';</script>";
            exit;
        }
    }

}
?>
<div id="sidebar"><?php include 'sidebar.php'; ?></div>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>ICP Records Backend</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .editable {
            cursor: pointer;
        }
    </style>
    <script src="js/jquery-3.6.0.js"></script>
    <script src="js/add.js"></script>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<table id="icpTable">
    <thead>
    <tr>
        <th>ID</th>
        <th>网站名称</th>
        <th>网站URL</th>
        <th>网站信息</th>
        <th>ICP编号</th>
        <th>所有者</th>
        <th>更新时间</th>
        <th>状态</th>
        <th>邮箱</th>
        <th>QQ</th>
        <th>安全码</th>
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <td><input type="hidden" name="addRecord" value="true"></td>
            <td><input type="text" name="website_name" value="网站名" required></td>
            <td><input type="text" name="website_url" value="网站域名" required></td>
            <td><input type="text" name="website_info" value="网站简介" required></td>
            <td><input type="text" name="icp_number" value="备案号" required></td>
            <td><input type="text" name="owner" value="所有者" required></td>
            <td></td> <!-- 更新时间由数据库自动生成 -->
            <td>
                <select name="status" required>
                    <option value="审核通过">审核通过</option>
                    <option value="待审核">待审核</option>
                </select>
            </td>
            <td><input type="email" name="email" value="邮箱" required></td>
            <td><input type="text" name="qq" value="QQ号" required></td>
            <td><input type="password" name="security_code" value="安全码" required></td>
            <td>
                <button type="submit">提交</button>
            </td>
        </form>
    </tr>
    </tbody>
</table>
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
