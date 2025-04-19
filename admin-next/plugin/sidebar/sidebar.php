<?php
// 检查插件实例是否存在
if (!isset($plugin)) {
    return;
}

echo '<ul>';
echo '<li' . (basename($_SERVER['PHP_SELF']) == 'index.php' ? ' class="active"' : '') . '><a href="index.php">Home</a></li>';
echo '<li' . (basename($_SERVER['PHP_SELF']) == 'change_adm.php' ? ' class="active"' : '') . '><a href="change_adm.php">修改记录审核</a></li>';
echo '<li' . (basename($_SERVER['PHP_SELF']) == 'add_adm.php' ? ' class="active"' : '') . '><a href="add_adm.php">添加备案</a></li>';
// ... 其他菜单项

// 动态添加菜单项
$plugin->addMenuItem(['name' => '新页面', 'url' => 'new-page.php']);

echo '</ul>';