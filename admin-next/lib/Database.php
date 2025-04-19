<?php

class Database
{
    private static $instance = null;
    private $connection = null;

    private function __construct()
    {
        // 引入数据库配置文件
        $config = require_once '../db_conf.php';

        // 创建数据库连接
        $this->connection = new mysqli(
            $config['host'], // 主机名
            $config['username'], // 用户名
            $config['password'], // 密码
            $config['dbname'], // 数据库名
            $config['port'] ?? 3306, // 端口号，如果没有提供则默认为3306
            $config['charset'] // 套接字或命名管道
        );

        // 检查连接
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}