<?php
include_once '../lib/function.php';
if (!checkUserLogin()) {
    header("Location: login.php");
    exit();
}
// 远程最新版本的zip文件URL
$remoteZipUrl = 'https://page.yuncheng.fun/latest.zip';

// 本地临时文件路径
$localZipFile = 'latest.zip';

// 下载远程zip文件
if (!copy($remoteZipUrl, $localZipFile)) {
    die('下载最新版本文件失败。');
}

// 解压缩文件到当前目录的上一级目录
$zip = new ZipArchive;
if ($zip->open($localZipFile) === TRUE) {
    // 获取当前目录的上一级目录路径
    $extractPath = dirname(__DIR__);
    $zip->extractTo($extractPath);
    $zip->close();

    // 删除临时zip文件
    unlink($localZipFile);

    echo "升级成功，文件已解压并覆盖到上一级目录。";
} else {
    die('无法解压文件。');
}
