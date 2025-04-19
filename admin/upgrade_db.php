<?php
//define('ABSPATH', dirname(__DIR__));
// 连接数据库
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
$host = $config['db']['host'];
$dbname = $config['db']['dbname'];
$user = $config['db']['user'];
$pass = $config['db']['pass'];
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 预定义的表和字段
$predefinedTables = [
    'users' => [
        'fields' => [
            'id' => 'int NOT NULL AUTO_INCREMENT',
            'username' => "varchar(255) NOT NULL",
            'email' => "varchar(255) NOT NULL",
            'created_at' => "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",
        ],
        'indexes' => [
            'PRIMARY' => ['id'],
        ],
        'data' => [
            ['username' => 'admin', 'email' => 'admin@example.com']
        ]
    ]
];

// 遍历预定义的表结构
foreach ($predefinedTables as $tableName => $predefinedTable) {
    // 检查表是否存在
    $query = "SHOW TABLES LIKE '{$tableName}'";
    $stmt = $pdo->query($query);
    $tableExists = $stmt->rowCount() > 0;

    if (!$tableExists) {
        // 创建表
        $fields = [];
        foreach ($predefinedTable['fields'] as $fieldName => $fieldType) {
            $fields[] = "`$fieldName` $fieldType";
        }
        $indexes = [];
        foreach ($predefinedTable['indexes'] as $indexName => $indexColumns) {
            $indexes[] = "ADD INDEX `$indexName` (`" . implode('`, `', $indexColumns) . "`)";
        }
        $createQuery = "CREATE TABLE IF NOT EXISTS `$tableName` (\n" . implode(",\n", $fields) . "\n" . implode(",\n", $indexes) . "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        $pdo->exec($createQuery);
        echo "表 $tableName 被创建。\n";

        // 插入数据
        if (!empty($predefinedTable['data'])) {
            foreach ($predefinedTable['data'] as $data) {
                $query = "INSERT INTO `$tableName` (`username`, `email`) VALUES (:username, :email)";
                $stmt = $pdo->prepare($query);
                $stmt->execute($data);
            }
            echo "向表 $tableName 插入了初始数据。\n";
        }
    } else {
        // 检查字段是否存在
        foreach ($predefinedTable['fields'] as $fieldName => $fieldType) {
            $query = "SHOW COLUMNS FROM `$tableName` WHERE `Field`='$fieldName'";
            $stmt = $pdo->query($query);
            $fieldExists = $stmt->rowCount() > 0;

            if (!$fieldExists) {
                // 添加字段
                $alterQuery = "ALTER TABLE `$tableName` ADD COLUMN `$fieldName` $fieldType";
                $pdo->exec($alterQuery);
                echo "在表 $tableName 中添加了字段 $fieldName 。\n";
            }
        }
    }
}
?>