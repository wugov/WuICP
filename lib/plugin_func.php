<?php
/*
Plugin Name:        插件名称
Plugin URL:         插件URL
Description:        插件描述
Version:            插件版本
Author:             插件作者
Author URL:         插件作者URL
License:            插件许可证
License URL:        插件许可证URL
Text Domain:        插件文本域
Domain Path:        插件域名路径
*/
if(!defined('Func_Path')){
    exit('Access Denied');
}
initDatabase();
function get_plugin_info($plugin_file) {
    // 确保文件存在
    if (!file_exists($plugin_file)) {
        return false;
    }

    // 读取文件内容
    $plugin_data = file($plugin_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $plugin_info = array();
    $in_header = false; // 初始化 $in_header 变量
    $header_ended = false; // 标记头部注释是否已结束

    // 遍历文件的每一行，匹配头部注释
    foreach ($plugin_data as $line) {
//        echo "Processing line: " . $line . PHP_EOL;
        if (!$header_ended && strpos($line, '/*') !== false) {
            // 头部注释开始
            $in_header = true;
//            echo "Header comment started." . PHP_EOL;
        } elseif ($in_header && strpos($line, '*/') !== false) {
            // 头部注释结束
            $in_header = false;
            $header_ended = true;
//            echo "Header comment ended." . PHP_EOL;
        } elseif ($in_header && preg_match('/^\s*\*\s*(.*)$/', $line, $matches)) {
            // 匹配注释行，允许注释行前有任意数量的空格
            $line = trim($matches[1]);
//            echo "Matched comment line: " . $line . PHP_EOL;
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // 规范化键名，去除多余空格并转换为小写
                $normalized_key = strtolower(str_replace(' ', '', $key));

                // 将信息存储到数组中
                $plugin_info[$normalized_key] = $value;
//                echo "Extracted key-value pair: $normalized_key => $value" . PHP_EOL;
            }
        }

        // 如果头部注释已经结束，退出循环
        if ($header_ended) {
            break;
        }
    }
    return $plugin_info;
}


function is_plugin_active($plugin_file): bool {
    // 获取当前所有启用的插件
    $activePlugins = get_active_plugins();

    // 遍历插件数组，检查是否存在指定的插件入口文件
    foreach ($activePlugins as $plugin) {
        if ($plugin->file == $plugin_file) {
            // 如果找到匹配的插件入口文件，返回 true 表示插件已激活
            return true;
        }
    }

    // 如果没有找到匹配的插件入口文件，返回 false 表示插件未激活
    return false;
}

function get_active_plugins(): array {
    global $pdo;
    $sql = "SELECT `v` FROM icp_config WHERE `k` = 'active_plugins'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result || empty($result['v'])) {
        // 如果查询结果为空或值为空，返回空数组
        return array();
    }

    // 反序列化插件信息
    $activePlugins = @unserialize($result['v']);

    if (!is_array($activePlugins)) {
        // 如果反序列化失败或结果不是数组，返回空数组
        return array();
    }

    return $activePlugins;
}



function activate_plugin($plugin_name, $plugin_file) {
    global $pdo;
    // 获取当前所有启用的插件
    $activePlugins = get_active_plugins();

    // 检查是否有相同的插件名或入口文件
    foreach ($activePlugins as $plugin) {
        if ($plugin->name == $plugin_name || $plugin->file == $plugin_file) {
            // 如果存在相同的插件名或入口文件，返回失败
            return false;
        }
    }

    // 添加新插件到数组，存储为对象
    $activePlugins[] = (object)['name' => $plugin_name, 'file' => $plugin_file];

    // 序列化插件信息数组
    $serialized_plugin_info = serialize($activePlugins);

    // 准备SQL语句
    $sql = "INSERT INTO icp_config (`k`, `v`) VALUES ('active_plugins', :v) ON DUPLICATE KEY UPDATE `v` = :v";
    $stmt = $pdo->prepare($sql);

    // 绑定参数
    $stmt->bindParam(':v', $serialized_plugin_info);

    // 执行语句
    $stmt->execute();

    // 返回成功
    return true;
}

function deactivate_plugin($plugin_name, $plugin_file) {
    global $pdo;
    // 获取当前所有启用的插件
    $activePlugins = get_active_plugins();

    // 查找并移除指定的插件
    $found = false;
    foreach ($activePlugins as $key => $plugin) {
        if ($plugin->name == $plugin_name && $plugin->file == $plugin_file) {
            // 找到插件，从数组中移除
            unset($activePlugins[$key]);
            $found = true;
            break;
        }
    }

    if (!$found) {
        // 如果没有找到指定的插件，返回失败
        return false;
    }

    // 重新索引数组
    $activePlugins = array_values($activePlugins);

    // 序列化插件信息数组
    $serialized_plugin_info = serialize($activePlugins);

    // 准备SQL语句
    $sql = "INSERT INTO icp_config (`k`, `v`) VALUES ('active_plugins', :v) ON DUPLICATE KEY UPDATE `v` = :v";
    $stmt = $pdo->prepare($sql);

    // 绑定参数
    $stmt->bindParam(':v', $serialized_plugin_info);

    // 执行语句
    $stmt->execute();

    // 返回成功
    return true;
}

function get_all_plugins(): array {
    // 设置plugins目录的路径
    $pluginsDir = $_SERVER['DOCUMENT_ROOT'] . '/plugins';

    // 初始化一个空数组来存储插件信息
    $all_plugins = [];

    // 检查目录是否存在
    if (is_dir($pluginsDir)) {
        // 打开目录
        $dir = opendir($pluginsDir);
        // 循环读取目录下的所有条目
        while (($subdir = readdir($dir)) !== false) {
            // 跳过'.'和'..'这两个特殊的目录
            if ($subdir != "." && $subdir != "..") {
                // 检查是否为目录
                if (is_dir($pluginsDir . '/' . $subdir)) {
                    // 构建插件信息文件路径
                    $plugin_info_file = $pluginsDir . '/' . $subdir . '/main.php';
//                    echo "Processing plugin info file: " . $plugin_info_file . PHP_EOL;

                    // 获取插件信息
                    $plugin_info = get_plugin_info($plugin_info_file);

                    if ($plugin_info) {
                        // 构建插件对象
                        $plugin = [
                            "plugin_name" => $plugin_info['name'] ?? '',
                            "plugin_info" => $plugin_info['description'] ?? '',
                            "plugin_version" => $plugin_info['version'] ?? '',
                            "plugin_author" => $plugin_info['author'] ?? '',
                            "plugin_entry" => $plugin_info_file, // 添加插件入口文件路径
                            "is_active" => is_plugin_active($plugin_info_file) // 添加激活状态
                        ];
                        // 将插件对象添加到数组中
                        $all_plugins[] = $plugin;
//                        echo "Added plugin: " . $plugin_info['Plugin Name'] . PHP_EOL;
                    } else {
                        echo "Failed to get plugin info for: " . $plugin_info_file . PHP_EOL;
                    }
                }
            }
        }
        // 关闭目录
        closedir($dir);
    } else {
        echo "Plugins directory does not exist: " . $pluginsDir . PHP_EOL;
    }

    return $all_plugins;
}

function load_plugins(): void {
    $active_plugins = get_active_plugins();

    foreach ($active_plugins as $plugin) {
        // 获取插件的文件路径
        $plugin_file = $plugin->file;

        // 确保 $plugin_file 是一个有效的字符串路径
        if (is_string($plugin_file) && file_exists($plugin_file)) {
            try {
                // 尝试加载插件文件
                include_once $plugin_file;
            } catch (Exception $e) {
                // 捕获并处理加载插件时的异常
                writeLog("Error","Error loading plugin {$plugin->name}: " . $e->getMessage());
                writeLogFile("Error","Error loading plugin {$plugin->name}: " . $e->getMessage());
                // 可以在这里记录错误日志，或者进行其他错误处理
            }
        }
    }
}
