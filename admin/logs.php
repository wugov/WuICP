<?php
//define('ABSPATH', dirname(__DIR__));
global $pdo;
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
if (!checkUserLogin()) {
    header("Location: login.php");
    exit();
}
initDatabase();
$stmt = $pdo->query("SELECT * FROM logs");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    </style>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<table id="logTable">
    <thead>
    <tr>
        <th>ID</th>
        <th>日志类型</th>
        <th>日志内容</th>
        <th>日志时间</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($records as $record): ?>
        <tr>
            <td><?php echo htmlspecialchars($record['id']); ?></td>
            <td class="editable" data-id="<?php echo $record['id']; ?>"
                data-column="log_type"><?php echo htmlspecialchars($record['log_type']); ?></td>
            <td class="editable" data-id="<?php echo $record['id']; ?>"
                data-column="log_content"><?php echo htmlspecialchars($record['log_content']); ?></td>
            <td class="editable" data-id="<?php echo $record['id']; ?>"
                data-column="log_time"><?php echo htmlspecialchars($record['log_time']); ?></td>
        </tr>
    <?php endforeach; ?>
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
