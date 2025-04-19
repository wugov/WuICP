<?php
global $pdo;
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';
require_once '../../vendor/autoload.php';
include_once '../../lib/function.php';

initDatabase();
if (!checkUserLogin()) {
    header('Location: /admin/login.php');
    exit;
}

// 获取分页参数
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
// 以下是搜索时使用的参数
// owner=&site_name=&qq=&icp_number=00000000&site_url=&email=
$owner = $_GET['owner'] ?? '';
$site_name = $_GET['site_name'] ?? '';
$qq = $_GET['qq'] ?? '';
$icp_number = $_GET['icp_number'] ?? '';
$site_url = $_GET['site_url'] ?? '';

if (!empty($owner) || !empty($site_name) || !empty($qq) || !empty($icp_number) || !empty($site_url)) {
    // 执行搜索函数
    search($owner, $site_name, $qq, $icp_number, $site_url, $page, $limit);
} else {
    // 执行分页函数
    get($page, $limit);
}

function search($owner, $site_name, $qq, $icp_number, $site_url, $page, $limit): void
{
    global $pdo;

    // 构建查询条件
    $conditions = [];
    $params = [];

    if (!empty($owner)) {
        $conditions[] = "owner LIKE :owner";
        $params[':owner'] = '%' . $owner . '%';
    }

    if (!empty($site_name)) {
        $conditions[] = "website_name LIKE :site_name";
        $params[':site_name'] = '%' . $site_name . '%';
    }

    if (!empty($qq)) {
        $conditions[] = "qq LIKE :qq";
        $params[':qq'] = '%' . $qq . '%';
    }

    if (!empty($icp_number)) {
        $conditions[] = "icp_number LIKE :icp_number";
        $params[':icp_number'] = '%' . $icp_number . '%';
    }

    if (!empty($site_url)) {
        $conditions[] = "website_url LIKE :site_url";
        $params[':site_url'] = '%' . $site_url . '%';
    }

    // 计算偏移量
    $offset = ($page - 1) * $limit;

    // 构建完整的查询语句
    $query = "SELECT id as 'id', website_name as 'website_name', 
       website_url as 'website_url', website_info as 'website_info', 
       icp_number as 'icp_number', owner as 'owner', update_time as 'update_time',
       STATUS as 'STATUS', email as 'email', qq as 'qq'
FROM icp_records";

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    // 查询总记录数
    $totalQuery = "SELECT COUNT(*) FROM icp_records";
    if (!empty($conditions)) {
        $totalQuery .= " WHERE " . implode(" AND ", $conditions);
    }
    $totalStmt = $pdo->prepare($totalQuery);
    $totalStmt->execute($params);
    $totalRow = $totalStmt->fetchColumn();

    // 添加分页
    $query .= " ORDER BY id LIMIT :limit OFFSET :offset";

    try {
        $stmt = $pdo->prepare($query);

        // 绑定 limit 和 offset 参数
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        // 绑定其他参数
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 准备JSON响应
        $response = [
            'code' => 0,
            'msg' => '',
            'count' => $totalRow,
            'data' => $results
        ];

        // 设置响应头
        header('Content-Type: application/json; charset=utf-8');

        // 输出JSON
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    } catch (PDOException $e) {
        // 记录日志或采取其他措施
        error_log("Database query failed: " . $e->getMessage() . " Query: " . $query . " Params: " . json_encode($params));
        throw new RuntimeException("Database query failed.");
    }
}



function get($page, $limit)
{
    global $pdo;
// 计算偏移量
    $offset = ($page - 1) * $limit;

// 查询总记录数
    $totalQuery = "SELECT COUNT(*) FROM icp_records";
    $totalStmt = $pdo->query($totalQuery);
    $totalRow = $totalStmt->fetchColumn();

// 查询分页数据
    function fetchPaginatedData($pdo, $limit, $offset)
    {
        // 输入验证
        if (!is_int($limit) || !is_int($offset) || $limit <= 0 || $offset < 0) {
            exit("Limit and offset must be positive integers.");
        }

        // 只选择需要的字段
        $query = "SELECT id as 'id', website_name as 'website_name', 
       website_url as 'website_url', website_info as 'website_info', 
       icp_number as 'icp_number', owner as 'owner', update_time as 'update_time',
       STATUS as 'STATUS', email as 'email', qq as 'qq'
FROM icp_records ORDER BY id LIMIT :limit OFFSET :offset";

        try {
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // 明确指定返回关联数组
        } catch (PDOException $e) {
            // 记录日志或采取其他措施
            error_log("Database query failed: " . $e->getMessage());
            throw new RuntimeException("Database query failed.");
        }
    }

    try {
        // 查询分页数据
        $data = fetchPaginatedData($pdo, $limit, $offset);

        // 准备JSON响应
        $response = [
            'code' => 0,
            'msg' => '',
            'count' => $totalRow,
            'data' => $data
        ];

        // 设置响应头
        header('Content-Type: application/json; charset=utf-8');

        // 输出JSON
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } catch (InvalidArgumentException $e) {
        // 输入验证失败
        $response = [
            'code' => 1,
            'msg' => 'Invalid input parameters.',
            'count' => 0,
            'data' => []
        ];
        header('HTTP/1.1 400 Bad Request');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } catch (PDOException $e) {
        // 数据库查询失败
        error_log("Database query failed: " . $e->getMessage() . " Params: limit=$limit, offset=$offset");
        $response = [
            'code' => 1,
            'msg' => 'Database query failed.',
            'count' => 0,
            'data' => []
        ];
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
