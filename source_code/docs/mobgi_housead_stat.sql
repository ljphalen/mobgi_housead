/*
Navicat MySQL Data Transfer

Source Server         : 192.168.0.14
Source Server Version : 50527
Source Host           : 192.168.0.14:3306
Source Database       : mobgi_housead_stat

Target Server Type    : MYSQL
Target Server Version : 50527
File Encoding         : 65001

Date: 2016-10-25 10:03:38
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for import_log
-- ----------------------------
DROP TABLE IF EXISTS `import_log`;
CREATE TABLE `import_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `last_id` bigint(20) unsigned DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='导入数据记录表';

-- ----------------------------
-- Table structure for original_stat
-- ----------------------------
DROP TABLE IF EXISTS `original_stat`;
CREATE TABLE `original_stat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orig_id` int(11) DEFAULT '0' COMMENT '创意ID',
  `block_id` varchar(64) DEFAULT '' COMMENT '广告位',
  `app_key` varchar(64) DEFAULT '',
  `ad_type` tinyint(2) DEFAULT '0' COMMENT '广告类型',
  `event_type` tinyint(2) DEFAULT '0' COMMENT '事件类型',
  `net_type` tinyint(2) DEFAULT '0' COMMENT '网络类型',
  `charge_type` tinyint(2) DEFAULT '0' COMMENT '计费类型',
  `operator` tinyint(2) DEFAULT '0' COMMENT '运营商',
  `platform` tinyint(2) DEFAULT '0' COMMENT '平台,1:安卓,2:ios',
  `uuid` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL COMMENT '天',
  `hour` tinyint(2) DEFAULT '0' COMMENT '小时',
  `count` int(11) DEFAULT '0' COMMENT '事件统计次数',
  `price` decimal(10,4) DEFAULT '0.0000' COMMENT '事件统计价格',
  `start_id` bigint(20) DEFAULT '0' COMMENT '原始表统计开始ID',
  `end_id` bigint(20) DEFAULT '0' COMMENT '原始表统计结束ID',
  `len` int(11) DEFAULT '0' COMMENT '记录统计长度',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for report_base
-- ----------------------------
DROP TABLE IF EXISTS `report_base`;
CREATE TABLE `report_base` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ad_id` int(11) unsigned DEFAULT '0' COMMENT '广告ID',
  `unit_id` int(11) DEFAULT '0' COMMENT '投放单元ID',
  `originality_type` int(11) DEFAULT '0' COMMENT '创意类型',
  `account_id` int(11) DEFAULT '0' COMMENT '账户ID',
  `originality_id` int(11) DEFAULT '0' COMMENT '创意ID',
  `block_id` varchar(64) DEFAULT '' COMMENT '广告位ID',
  `app_key` varchar(64) DEFAULT '',
  `ad_type` tinyint(2) DEFAULT '0' COMMENT '广告类型',
  `platform` tinyint(2) DEFAULT '0' COMMENT '平台',
  `date` date DEFAULT NULL,
  `hour` tinyint(2) DEFAULT NULL,
  `request` int(11) DEFAULT '0' COMMENT '请求配置成功',
  `request_ok` int(11) DEFAULT '0' COMMENT '请求配置成功',
  `download` int(11) DEFAULT '0' COMMENT '下载资源次数',
  `download_ok` int(11) DEFAULT '0' COMMENT '下载资源成功',
  `view` int(11) DEFAULT '0' COMMENT '展示次数',
  `click` int(11) DEFAULT '0' COMMENT '点击次数',
  `close` int(11) DEFAULT '0' COMMENT '关闭',
  `reward` int(11) DEFAULT '0' COMMENT '触发奖励(视频)',
  `resume` int(11) DEFAULT '0' COMMENT '重新观看(视频)',
  `redirect_browser` int(11) DEFAULT '0' COMMENT '跳转浏览器次数',
  `redirect_Internal_browser` int(11) DEFAULT '0' COMMENT '跳转内建浏览器次数',
  `redirect_shop` int(11) DEFAULT '0' COMMENT '跳转商店次数',
  `download_app` int(11) DEFAULT '0' COMMENT '下载APP次数',
  `download_app_ok` int(11) DEFAULT '0' COMMENT '下载APP成功次数',
  `install_app` int(11) DEFAULT '0' COMMENT '安装APP次数',
  `install_app_ok` int(11) DEFAULT '0' COMMENT '安装成功次数',
  `amount` decimal(12,4) DEFAULT '0.0000' COMMENT '消费金额',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_primary` (`originality_id`,`app_key`,`platform`,`date`,`hour`,`block_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for stat_day
-- ----------------------------
DROP TABLE IF EXISTS `stat_day`;
CREATE TABLE `stat_day` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ad_unit_id` int(11) unsigned DEFAULT '0' COMMENT '广告单元ID',
  `ad_id` int(11) unsigned DEFAULT '0' COMMENT '广告ID',
  `originality_id` int(11) unsigned DEFAULT '0' COMMENT '创意ID',
  `day` date DEFAULT NULL,
  `clicks` int(11) unsigned DEFAULT '0' COMMENT '点击次数',
  `views` int(11) unsigned DEFAULT '0' COMMENT '展示次数',
  `dau` int(11) unsigned DEFAULT NULL COMMENT '活跃用户数量',
  `amount` float(10,4) unsigned DEFAULT '0.0000' COMMENT '消费金额',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_origid_day` (`day`,`originality_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='消费数据日统计表';

-- ----------------------------
-- Table structure for stat_minute
-- ----------------------------
DROP TABLE IF EXISTS `stat_minute`;
CREATE TABLE `stat_minute` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `originality_id` int(11) unsigned DEFAULT '0' COMMENT '创意ID',
  `minute` datetime DEFAULT NULL,
  `clicks` int(10) unsigned DEFAULT '0' COMMENT '点击次数',
  `views` int(10) unsigned DEFAULT '0' COMMENT '展示次数',
  `amount` float(10,4) unsigned DEFAULT NULL COMMENT '消费金额',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_origid_minute` (`minute`,`originality_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='消费数据分钟统计表';
