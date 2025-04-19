<?php
header('Content-Type: text/plain; charset=utf-8');
$config = require __DIR__ . '/../config.php';
$dbConfig = $config['db'] ?? [];

if (empty($dbConfig)) {
    exit('数据库配置缺失，请检查config.php');
}

/* ========================
 * 数据库连接 (PDO)
 * ======================== */
try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $dbConfig['host'] ?? 'localhost',
        $dbConfig['dbname'] ?? ''
    );
    
    $pdo = new PDO(
        $dsn,
        $dbConfig['user'] ?? '',
        $dbConfig['pass'] ?? '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    exit('数据库连接失败: ' . $e->getMessage());
}

// 获取网站设置
try {
    $stmt = $pdo->query("SELECT site_name, site_url FROM website_info LIMIT 1");
    $settings = $stmt->fetch();
    
    if (!$settings) {
        exit('网站配置不存在，请检查web_settings表');
    }
    
    $shortName = $settings['site_name'];
    $siteDomain = $settings['site_url'];
} catch (PDOException $e) {
    exit('配置查询失败: ' . $e->getMessage());
}

function abort($message) {
    die("ERROR:" . $message);
}

if (!isset($_GET['url'], $_GET['number'])) {
    abort('Missing parameters');
}

$targetUrl = trim($_GET['url']);
$number = trim($_GET['number']);

if (empty($targetUrl) || empty($number)) {
    abort('Empty value');
}

$protocolPattern = '/^https?:\/\//i';
$idnPattern = '/[^\x20-\x7E]/u';

if (!preg_match($protocolPattern, $targetUrl)) {
    $targetUrl = 'http://' . ltrim($targetUrl, '/');
}

try {
    $parsed = parse_url($targetUrl);
    if (!$parsed || !isset($parsed['host'])) {
        abort('Invalid URL structure');
    }
    
    $host = $parsed['host'];
    if (preg_match($idnPattern, $host)) {
        $converted = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46, $info);
        if ($converted === false) {
            abort('IDN conversion failed: ' . implode(',', $info['errors']));
        }
        
        $hostPos = strpos($targetUrl, $host);
        if ($hostPos !== false) {
            $targetUrl = substr_replace($targetUrl, $converted, $hostPos, strlen($host));
        }
    }
} catch (Exception $e) {
    abort('URL processing error: ' . $e->getMessage());
}

$convertedNumber = idn_to_ascii($number, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46, $info);
if ($convertedNumber === false) {
    $convertedNumber = $number;
}

if (!filter_var($targetUrl, FILTER_VALIDATE_URL)) {
    abort('Invalid URL');
}

$curlOptions = [
    CURLOPT_URL            => $targetUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS      => 3,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    CURLOPT_TIMEOUT        => 5,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_ENCODING       => '',
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2_0,
    CURLOPT_HEADER         => false
];

$ch = curl_init();
curl_setopt_array($ch, $curlOptions);
$response = curl_exec($ch);
$errno = curl_errno($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($errno !== CURLE_OK) {
    abort('Request failed: ' . curl_strerror($errno));
}

if ($httpCode !== 200) {
    die("HTTP:" . $httpCode);
}

$patterns = [
    '/' . preg_quote($shortName, '/') . 'ICP备/u',
    '/' . preg_quote($shortName, '/') . '备/u',
    '/https?:\/\/' . preg_quote($siteDomain, '/') . '\/id\.php\?keyword=(' 
        . preg_quote($number, '/') . '|' . preg_quote($convertedNumber, '/') . ')/i',
    '/https?:\/\/' . preg_quote($siteDomain, '/') . '\/id\.php\?keyword=(' 
        . preg_quote(urlencode($number), '/') . '|' . preg_quote(urlencode($convertedNumber), '/') . ')/i'
];

foreach ($patterns as $pattern) {
    if (preg_match($pattern, $response)) {
        echo "true";
        exit;
    }
}

echo "404";