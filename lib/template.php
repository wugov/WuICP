<?php
// 如果这个文件被直接访问，则退出
// if (!defined('ABSPATH')) {
//     exit('不允许直接访问');
// }

$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$host = $config['db']['host'];
$dbname = $config['db']['dbname'];
$user = $config['db']['user'];
$pass = $config['db']['pass'];
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/plugin/hooks.php';
// 自定义函数，用于生成ICP备案信息的HTML代码
function generateIcpHtml($oldRecords)
{
    $html = '<div class="records">';
    foreach ($oldRecords as $record) {
        $html .= '<div class="record" onclick="location.href=\'id.php?keyword=' . urlencode(htmlspecialchars($record['icp_number'])) . '\'">';
        $html .= '<div class="website-name">' . htmlspecialchars($record['website_name']) . '</div>';
        $html .= '<div class="icp-number">' . htmlspecialchars($record['icp_number']) . '</div>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

function renderTemplate($templateName, $pageName, $vars = [])
{
    $path = "templates/{$templateName}/{$pageName}.html";
    // 允许插件在模板渲染前执行代码
    do_action('before_render_template', $templateName, $pageName, $vars);
    if (!file_exists($path)) {
        throw new Exception("Template file not found: {$path}");
    }
    $template = file_get_contents($path);

    // 正则表达式匹配新的标签格式
    $pattern = "/\[\[(.*?)\]\]/";
    preg_match_all($pattern, $template, $matches);
    $replacements = [];

    if (!empty($matches[1])) {
        // 处理匹配到的每个标签
        foreach ($matches[1] as $match) {
            $attributes = explode(';', $match);
            $type = isset($attributes[0]) ? $attributes[0] : '';

            // 初始化 $opt 数组
            $opt = [];

            // 检查并记录过滤条件
            foreach ($attributes as $attribute) {
                // 忽略类型属性
                if ($attribute === $type) {
                    continue;
                }

                // 解析键值对
                if (strpos($attribute, '=') !== false) {
                    $parts = explode('=', $attribute);
                    if (count($parts) === 2) {
                        $key = trim($parts[0]);
                        $value = trim($parts[1], "' ");
                        $opt[$key] = $value;
                    }
                }
            }

            // 根据type处理标签
            if ($type == 'show_icp') {
                global $pdo;
                // 构建查询语句
                $queryOldRecords = "SELECT icp_number, website_name FROM icp_records";
                $whereClauses = [];

                // 构建 WHERE 子句
                foreach ($opt as $key => $value) {
                    $whereClauses[] = "$key = :$key";
                }

                if (!empty($whereClauses)) {
                    $queryOldRecords .= " WHERE " . implode(" AND ", $whereClauses);
                }

                $stmtOldRecords = $pdo->prepare($queryOldRecords);

                // 绑定参数
                foreach ($opt as $key => $value) {
                    $stmtOldRecords->bindParam(":$key", $opt[$key]);
                }

                $stmtOldRecords->execute();
                $oldRecords = $stmtOldRecords->fetchAll(PDO::FETCH_ASSOC);

                // 调用自定义函数生成HTML代码
                $icpHtml = generateIcpHtml($oldRecords);
                // 收集替换内容
                $replacements["[[$match]]"] = $icpHtml;
            }
        }
    }

    // 收集变量替换内容
    foreach ($vars as $key => $value) {
        if (empty($value)) {
            $replacements["{{{$key}}}"] = '';
        } else {
            if (is_string($key) && strpos($key, '_raw') !== false) {
                $replacements["{{{$key}}}"] = $value;
            } else {
                $replacements["{{{$key}}}"] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
    }

    // 批量替换
    // 允许插件在替换变量执行代码
    do_action('before_replace_template', $templateName, $pageName, $vars);
    $template = str_replace(array_keys($replacements), array_values($replacements), $template);
    // 允许插件在模板渲染后执行代码
    do_action('after_render_template', $templateName, $pageName, $vars);
    return $template;
}

//// 使用示例
//$pageVars = [
//    'pageTitle' => '欢迎页面',
//    'content' => '这里是页面内容。',
//    'items' => [
//        ['name' => '项目1', 'description' => '描述1'],
//        ['name' => '项目2', 'description' => '描述2'],
//    ]
//];
//
//echo renderTemplate('default','index', $pageVars);
/**
 * @param string $pageName
 * @param array $additionalVars
 * @return void
 * @throws Exception
 */
function renderPage(string $pageName = 'index', array $additionalVars = []): void
{
    global $pdo;
    initDatabase();
    // 查询数据库获取网站信息
    $query = "SELECT * FROM website_info LIMIT 1";
    $stmt = $pdo->query($query);
    $websiteInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    // 检查是否获取到了数据
    if (!$websiteInfo) {
        die("网站信息不存在");
    }
    extract($websiteInfo); // 将数组键名作为变量名，将数组键值作为变量值

    // 初始化$pageVars数组
    global $pageVars;
    $pageVars = [
        'site_name' => $site_name,
        'site_url' => $site_url,
        'site_keywords' => $site_keywords,
        'site_description' => $site_description,
        'footer_code_raw' => $footer_code,
        'site_avatar' => $site_avatar,
        'site_abbr' => $site_abbr,
        'admin_nickname' => $admin_nickname,
        'admin_email' => $admin_email,
        'admin_qq' => $admin_qq,
        'feedback_link' => $feedback_link,
        'background_image' => $background_image,
        'audit_duration' => $audit_duration,
        'enable_template_name' => TEMPLATE_NAME,
    ];

    // 合并额外的内容到$pageVars数组中
    $pageVars = array_merge($pageVars, $additionalVars);
// 触发钩子，并获取返回值
    $pluginAddPageVars = do_action('add_page_vars');
    if (!empty($pluginAddPageVars)) {
        // 如果插件返回了值，则合并到$pageVars数组中
        $pageVars = array_merge($pageVars, $pluginAddPageVars);
    }

    echo renderTemplate(templateName: TEMPLATE_NAME, pageName: $pageName, vars: $pageVars);
}