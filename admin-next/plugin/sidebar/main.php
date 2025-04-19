<?php

class DynamicSidebarPlugin
{
    private $config;

    public function __construct()
    {
        $this->config = include('config.php');
        add_action('init', [$this, 'loadSidebar']);
    }

    public function loadSidebar()
    {
        if (file_exists('sidebar.php')) {
            include('sidebar.php');
        }
    }

    public function addMenuItem($item)
    {
        // 这里可以添加代码将菜单项保存到数据库或文件
        // 为了简单起见，这里只是打印出来
        echo '<li class="new-item"><a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['name']) . '</a></li>';
    }
}

// 实例化插件
$plugin = new DynamicSidebarPlugin();