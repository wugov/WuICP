<?php
// hooks.php

// 存储钩子回调的数组
$plugin_callbacks = [];

// 注册钩子回调
function add_action($hook, $callback)
{
    global $plugin_callbacks;
    if (!isset($plugin_callbacks[$hook])) {
        $plugin_callbacks[$hook] = [];
    }
    $plugin_callbacks[$hook][] = $callback;
}

// 触发钩子回调，并收集返回值
function do_action($hook, ...$args)
{
    global $plugin_callbacks;
    $results = []; // 用于存储每个回调的返回值
    if (!empty($plugin_callbacks[$hook])) {
        foreach ($plugin_callbacks[$hook] as $callback) {
            $result = call_user_func_array($callback, $args);
            if ($result !== null) { // 只收集非null的返回值
                $results[] = $result;
            }
        }
    }

    // 如果没有收集到任何返回值，返回null或适当的默认值
    if (empty($results)) {
        return null; // 或者可以返回一个默认值，例如 false 或 ''
    }

    // 如果只有一个返回值，直接返回该值；否则返回所有返回值的数组
    return count($results) === 1 ? array_shift($results) : $results;
}