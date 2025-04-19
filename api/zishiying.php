<?php
// 强制清除输出缓冲区
while (ob_get_level()) ob_end_clean();

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 定义安全的图片URL白名单
$allowed_hosts = ['image.lolimi.cn'];

// 定义不同条件下的图片URL
$url_mapping = [
    'day_mobile'    => 'https://image.lolimi.cn/2025/03/22/67de14d71e4c2.png',
    'day_desktop'   => 'https://image.lolimi.cn/2025/03/22/67de14ddb8c24.png',
    'night_mobile'  => 'https://image.lolimi.cn/2025/03/22/67de14dad3285.png',
    'night_desktop' => 'https://image.lolimi.cn/2025/03/01/67c31e8b8a3e8.png'
];

// 验证所有URL合法性
foreach ($url_mapping as $url) {
    $parsed = parse_url($url);
    if (!in_array($parsed['host'], $allowed_hosts)) {
        http_response_code(500);
        exit('Invalid image host configuration');
    }
}

// 获取当前时间
$hour = (int)date('H');

// 判断白天/夜间（6:00-18:00视为白天）
$is_day = ($hour >= 6 && $hour < 18);

// 增强版设备检测
function is_mobile() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // 更全面的移动设备匹配规则
    $mobile_keywords = [
        'android', 'iphone', 'ipod', 'ipad',
        'mobile', 'blackberry', 'webos',
        'opera mini', 'iemobile', 'windows phone'
    ];
    
    return preg_match(
        '/' . implode('|', $mobile_keywords) . '/i', 
        $ua
    );
}

// 选择目标URL
$device_type = is_mobile() ? 'mobile' : 'desktop';
$time_type = $is_day ? 'day' : 'night';
$target_url = $url_mapping["{$time_type}_{$device_type}"] ?? null;

// 安全验证最终URL
if (!$target_url || !filter_var($target_url, FILTER_VALIDATE_URL)) {
    http_response_code(500);
    exit('Invalid target URL');
}

// 设置302重定向
if (isset($_SERVER['SERVER_PROTOCOL'])) {
    header("HTTP/1.1 302 Found", true, 302);
}
header("Location: $target_url");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // 过去时间

exit();
?>