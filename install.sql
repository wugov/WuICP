-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2024-12-28 16:03:06
-- 服务器版本： 8.0.24
-- PHP 版本： 8.1.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `icp`
--

-- --------------------------------------------------------

--
-- 表的结构 `admin`
--

CREATE TABLE IF NOT EXISTS `admin`
(
    `id`
    int
    NOT
    NULL,
    `permission_level`
    int
    NOT
    NULL
    DEFAULT
    '0',
    `email`
    varchar
(
    255
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `totp_secret` varchar
(
    255
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    `totp_enabled` tinyint
(
    1
) NOT NULL DEFAULT '0',
    `password` varchar
(
    128
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    `password_enabled` tinyint
(
    1
) NOT NULL DEFAULT '0'
    ) ENGINE =InnoDB DEFAULT CHARSET =utf8mb4 COLLATE =utf8mb4_general_ci;


--
-- 表的结构 `admin_devices`
--

CREATE TABLE IF NOT EXISTS `admin_devices`
(
    `id`         int                                                           NOT NULL,
    `email`      varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `token`      varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `device_id`  varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `last_login` timestamp                                                     NULL DEFAULT CURRENT_TIMESTAMP,
    `ip_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `session_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;


--
-- 表的结构 `icp_records`
--

CREATE TABLE IF NOT EXISTS `icp_records`
(
    `id`            int                                            NOT NULL,
    `website_name`  varchar(255)                                   NOT NULL,
    `website_url`   varchar(255)                                   NOT NULL,
    `website_info`  text,
    `icp_number`    varchar(50)                                    NOT NULL,
    `owner`         varchar(255)                                   NOT NULL,
    `update_time`   datetime                                       NOT NULL,
    `STATUS`        enum ('待审核','审核通过','备案驳回','被删除') NOT NULL DEFAULT '待审核',
    `last_notified` datetime                                       NOT NULL,
    `email`         varchar(255)                                            DEFAULT NULL,
    `qq`            varchar(20)                                             DEFAULT NULL,
    `security_code` varchar(255)                                   NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

--
-- 转存表中的数据 `icp_records`
--

INSERT INTO `icp_records` (`id`, `website_name`, `website_url`, `website_info`, `icp_number`, `owner`, `update_time`,
                           `STATUS`, `email`, `qq`, `security_code`)
VALUES (1, '云团子的博客', 'www.yuncheng.fun', '欸？是谁家的小可爱？', '20243017', '云团子', '2024-10-02 22:59:19',
        '审核通过', 'yun@yuncheng.fun', '937319686', ''),
       (2, '风屿岛', 'www.biliwind.com', '不知名海域的知名岛屿', '00000000', '风屿Wind', '2024-10-06 00:00:06',
        '审核通过', 'admin@biliwind.com', '1827348941', ''),
       (3, '云团子ICP备案中心', 'icp.yuncheng.fun', '欸？是谁家的小可爱？快来申请一个可爱又好玩的团ICP备号叭~',
        '20243999', '云团子', '2024-10-19 12:06:46', '审核通过', 'yun@yuncheng.fun', '937319686', ''),
       (4, '风屿团IDC', 'www.tuanidc.cn', '一定是最可爱的计算云服务商', '20243088', '风屿团数据中心',
        '2024-11-17 12:49:32', '审核通过', 'yun@yuncheng.fun', '937319686', ''),
       (5, '风屿岛GitLab', 'gitlab.biliwind.com', '不知名海域的知名岛屿', '00000001', '风屿Wind', '2024-11-22 14:30:52',
        '审核通过', 'admin@biliwind.com', '1827348941', ''),
       (6, '木屿岛', 'muyuwind.top', '普通海域的奇怪岛屿', '00000002', '风屿Wind', '2024-12-22 18:15:37', '审核通过',
        'admin@biliwind.com', '1827348941', '');
-- --------------------------------------------------------

--
-- 表的结构 `icp_records_change`
--

CREATE TABLE IF NOT EXISTS `icp_records_change`
(
    `id`           int                                                NOT NULL,
    `website_name` varchar(255)                                       NOT NULL,
    `website_url`  varchar(255)                                       NOT NULL,
    `website_info` text,
    `icp_number`   varchar(50)                                        NOT NULL,
    `owner`        varchar(255)                                       NOT NULL,
    `update_time`  datetime                                           NOT NULL,
    `STATUS`       enum ('待审核','审核通过','修改信息驳回','被删除') NOT NULL DEFAULT '待审核',
    `email`        varchar(255)                                                DEFAULT NULL,
    `qq`           varchar(20)                                                 DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `logs`
--

CREATE TABLE IF NOT EXISTS `logs`
(
    `id`          int                                                            NOT NULL,
    `log_type`    varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL,
    `log_content` varchar(2550) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `log_time`    timestamp                                                      NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE IF NOT EXISTS `users`
(
    `id`            int                                                           NOT NULL,
    `username`      varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `email`         varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `password_hash` char(96) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci     NOT NULL,
    `created_at`    datetime                                                      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `icp_number`    varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci           DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `website_info`
--

CREATE TABLE IF NOT EXISTS `website_info`
(
    `id`                   int          NOT NULL,
    `site_name`            varchar(255) NOT NULL,
    `site_url`             varchar(255) NOT NULL,
    `site_avatar`          varchar(255) NOT NULL,
    `site_abbr`            varchar(10)  NOT NULL,
    `site_keywords`        text         NOT NULL,
    `site_description`     text         NOT NULL,
    `admin_nickname`       varchar(255) NOT NULL,
    `admin_email`          varchar(255) NOT NULL,
    `admin_qq`             varchar(20)  NOT NULL,
    `footer_code`          text,
    `audit_duration`       int          NOT NULL,
    `feedback_link`        varchar(255)          DEFAULT NULL,
    `background_image`     varchar(255)          DEFAULT NULL,
    `enable_template_name` varchar(255) NOT NULL DEFAULT 'default'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb3;

--
-- 转存表中的数据 `website_info`
--

INSERT INTO `website_info` (`id`, `site_name`, `site_url`, `site_avatar`, `site_abbr`, `site_keywords`,
                            `site_description`, `admin_nickname`, `admin_email`, `admin_qq`, `footer_code`,
                            `audit_duration`, `feedback_link`, `background_image`, `enable_template_name`)
VALUES (1, '云团子', 'https://icp.yuncheng.fun/',
        'https://www.yuncheng.fun/static/webAvatar/11727945933180571.png', '团',
        '团备, 团ICP备, 云团子ICP备案中心 ,云团子 ,杜匀程',
        '哇，是谁家的小可爱？', '云团子',
        'yun@yuncheng.fun', '937319686',
        '<a href=\"index.php\">主页</a> \r\n<a href=\"about.php\">关于</a>\r\n<a href=\"joinus.php\">加入</a>
       \r\n<a href=\"change.php\">变更</a>\r\n<a href=\"gs.php\">公示</a>\r\n<a href=\"qy.php\">迁跃</a>\r\n<br>\r\n
       <img src=\"https://page.yuncheng.fun/png/cn.png\" alt=\"国旗\" class=\"cn-logo\">
       <a href=\"https://beian.miit.gov.cn/\" target=\"_blank\">冀ICP备2024092417号-1</a>
       \r\n<img src=\"https://page.yuncheng.fun/png/beian.png\" alt=\"备案图标\" class=\"beian-logo\">
       <a target=\"_blank\" href=\"http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=13010802002339\">
       冀公网安备13010802002339号</a>\r\n<a href=\"https://icp.yuncheng.fun/id.php?keyword=20243999\" target=\"_blank\">
       团ICP备20243999号</a>', 3, 'https://qm.qq.com/q/kClRRuBmOQ',
        'https://cdn.koxiuqiu.cn/ccss/ecyrw/ecy%20(68).png', 'default');

CREATE TABLE IF NOT EXISTS `icp_config`
(
    `k` varchar(256) NOT NULL,
    `v` text NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

INSERT INTO `icp_config` (`k`, `v`)
VALUES ('mail_alert', '7'),
       ('smtp_host', ''),
       ('smtp_user', ''),
       ('smtp_pass', ''),
       ('smtp_port', ''),
       ('active_plugins', ''),
       ('smtp_secure', '0');

ALTER TABLE `icp_config` ADD PRIMARY KEY (`k`);
--
-- 转储表的索引
--
ALTER TABLE `icp_config`
    ADD INDEX `index_k` (`k`);
--
-- 表的索引 `admin`
--
ALTER TABLE `admin`
    ADD PRIMARY KEY (`id`);

--
-- 表的索引 `admin_devices`
--
ALTER TABLE `admin_devices`
    ADD PRIMARY KEY (`id`);

--
-- 表的索引 `icp_records`
--
ALTER TABLE `icp_records`
    ADD PRIMARY KEY (`id`),
    ADD KEY `idx_icp_url` (`icp_number`, `website_url`);

--
-- 表的索引 `icp_records_change`
--
ALTER TABLE `icp_records_change`
    ADD PRIMARY KEY (`id`);

--
-- 表的索引 `logs`
--
ALTER TABLE `logs`
    ADD PRIMARY KEY (`id`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
    ADD PRIMARY KEY (`id`);

--
-- 表的索引 `website_info`
--
ALTER TABLE `website_info`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `site_url` (`site_url`),
    ADD UNIQUE KEY `admin_email` (`admin_email`),
    ADD UNIQUE KEY `admin_qq` (`admin_qq`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `admin`
--
ALTER TABLE `admin`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `admin_devices`
--
ALTER TABLE `admin_devices`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `icp_records`
--
ALTER TABLE `icp_records`
    MODIFY `id` int NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 7;

--
-- 使用表AUTO_INCREMENT `icp_records_change`
--
ALTER TABLE `icp_records_change`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `logs`
--
ALTER TABLE `logs`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `website_info`
--
ALTER TABLE `website_info`
    MODIFY `id` int NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
