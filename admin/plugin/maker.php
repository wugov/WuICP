<?php
global $pdo;
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
initDatabase();
//include '../sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>生成器</title>
    <link rel="stylesheet" href="/admin/css/main.css">
    <style>
        #blocklyDiv {
            height: 100%; /* Adjust the height as needed */
            width: 80%; /* Adjust the width as needed */
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
        }
        #codeDisplay {
            margin-top: 20px;
            width: 20%;
            height: 200px; /* Adjust the height as needed */
            position: fixed;
            right: 0;
        }
    </style>
    <style>
        #menuButton {
            position: fixed;
            bottom: 10px;
            right: 10px;
            z-index: 9999; /* 确保按钮在其他元素之上 */
        }

        #menuContainer {
            position: fixed;
            bottom: 50px; /* 按钮下方留点空间 */
            right: 15px;
            background-color: white;
            border: 1px solid black;
            padding: 10px;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.2);
            z-index: 9998; /* 略低于按钮 */
        }
    </style>
    <script src="js/maker.js"></script>
    <script src="/lib/package/blockly.min.js"></script>
    <script src="/lib/package/php_compressed.js"></script>
    <script src="/lib/package/msg/zh-hans.js"></script>
    <script src="/lib/package/blocks_compressed.js"></script>
</head>
<body>
<div id="blocklyDiv"></div>
<textarea id="codeDisplay" placeholder="Generated PHP code will appear here..."></textarea>
<button id="menuButton" style="position: fixed; bottom: 10px; right: 10px; z-index: 10000;">Open Menu</button>
<div id="menuContainer" style="display: none; position: fixed; bottom: 50px; right: 15px; z-index: 9999;">
    <ul>
        <li>Menu Item 1</li>
        <li>Menu Item 2</li>
        <li>Menu Item 3</li>
    </ul>
</div>
</body>
</html>
