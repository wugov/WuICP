<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/redis.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
global $pdo;

initDatabase();

/**
 * @param $log_type
 * @param $log_content
 * @return int
 */
function writeLog($log_type, $log_content): int
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO logs (log_type, log_content) VALUES (?, ?)");
    $stmt->execute([$log_type, $log_content]);
    return $stmt->rowCount();
}

/**
 * @param $log_type
 * @param $log_content
 * @return void
 */
function writeLogFile($log_type, $log_content)
{
    $log_file = 'logs/' . date('Y-m-d') . '.log';
    $log_content = date('Y-m-d H:i:s') . ' logger.php' . $log_type . ' ' . $log_content . "\n";
    file_put_contents($log_file, $log_content, FILE_APPEND);
}

/**
 * @param $id
 * @return mixed
 */
function getLog($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM logs WHERE id = ? LIMIT 100");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}