<?php
// config.php
return [
    'db' => [
        'host' => '127.0.0.1',
        'dbname' => 'your_db_name',
        'user' => 'your_db_user',
        'pass' => 'yuour_db_pass',
    ],
    'mail' => [
    'host' => 'your_smtp_server',     // SMTP服务器
    'port' => 465,                     // SMTP端口
    'username' => 'your_mail_user',    // 发件邮箱
    'password' => 'your_mail_pass',     // 邮箱密码/授权码
    'from' => 'your_mail_address',  // 发件人地址
    'encryption' => 'ssl'              // 加密方式（可选：tls/ssl）
    ]
];