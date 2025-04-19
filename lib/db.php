<?php

class DatabaseConnection {
    private $pdo;

    public function __construct($configPath) {
        try {
            $config = include $configPath;
            $this->validateConfig($config);
            $this->connect($config);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Could not connect to the database.");
        } catch (Exception $e) {
            error_log("Initialization error: " + $e->getMessage());
            throw new Exception("Initialization failed: " . $e->getMessage());
        }
    }

    private function validateConfig($config) {
        $requiredKeys = [
            'mysql' => ['host', 'dbname', 'user', 'pass', 'charset'],
            'sqlite' => ['dbname'],
            'postgresql' => ['host', 'dbname', 'user', 'pass']
        ];

        if (!isset($config['db']['type']) || !isset($requiredKeys[$config['db']['type']])) {
            throw new Exception("Unsupported database type or missing configuration.");
        }

        foreach ($requiredKeys[$config['db']['type']] as $key) {
            if (!isset($config['db'][$key]) || empty($config['db'][$key])) {
                throw new Exception("Missing required configuration key: $key");
            }
        }
    }

    private function connect($config) {
        $dbType = $config['db']['type'];
        switch ($dbType) {
            case 'mysql':
                $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset={$config['db']['charset']}";
                $this->pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass']);
                break;
            case 'sqlite':
                $dsn = "sqlite:" . $config['db']['dbname'];
                $this->pdo = new PDO($dsn);
                break;
            case 'postgresql':
                $dsn = "pgsql:host={$config['db']['host']};dbname={$config['db']['dbname']};user={$config['db']['user']};password={$config['db']['pass']}";
                $this->pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass']);
                break;
            default:
                throw new Exception("Unsupported database type: $dbType");
        }

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getPdo() {
        return $this->pdo;
    }
}

// 示例用法
try {
    $configPath = include $_SERVER['DOCUMENT_ROOT'] . '/config/db_conf.php';
    $dbConnection = new DatabaseConnection($configPath);
    $pdo = $dbConnection->getPdo();
} catch (Exception $e) {
    // 处理异常，例如返回友好错误页面
    echo "Error: " . $e->getMessage();
}
