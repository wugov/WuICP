<?php

// 指定要检查的文件路径
$filePath = $_SERVER['DOCUMENT_ROOT'] . '/redis.lock';

// 检查文件是否存在
if (!file_exists($filePath)) {
    // 如果文件不存在，脚本会继续执行以下代码

// 连接到Redis服务器
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    //$redis->auth(''); // 设置密码

// 显式选择数据库0
    $redis->select(0);

// 设置用于记录访问频率的键名
    $key = 'rate_limit:' . $_SERVER['REMOTE_ADDR']; // 使用IP地址作为键的一部分

// 设置每分钟的最大访问次数
    $max_requests = 15;

// 设置Redis键的过期时间（秒）
    $expire_time = 60;

// 获取当前访问次数
    $current_requests = $redis->get($key);

    if ($current_requests >= $max_requests) {
        // 如果访问次数超过限制，则等待5秒
        sleep(5);
    } else {
        // 如果键不存在或访问次数未超过限制，增加访问次数
        if ($current_requests === false) {
            // 第一次访问，设置访问次数为1，并设置过期时间
            $redis->set($key, 1, $expire_time);
        } else {
            // 访问次数未超过限制，增加访问次数
            $redis->incr($key);
        }
    }
}
?>
