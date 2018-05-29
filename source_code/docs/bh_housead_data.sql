/*
Navicat MySQL Data Transfer

Source Server         : 192.168.0.14_5029
Source Server Version : 50140
Source Host           : 192.168.0.14:5029
Source Database       : bh_housead_data

Target Server Type    : MYSQL
Target Server Version : 50140
File Encoding         : 65001

Date: 2016-10-25 10:03:53
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for charge_data
-- ----------------------------
DROP TABLE IF EXISTS `charge_data`;
CREATE TABLE `charge_data` (
  `id` bigint(20) NOT NULL,
  `originality_id` int(11) DEFAULT '0' COMMENT '创意ID',
  `uuid` varchar(64) DEFAULT '' COMMENT '用户唯一识别吗',
  `created_time` int(11) DEFAULT '0',
  `event_type` tinyint(2) DEFAULT '0' COMMENT '事件类型',
  `charge_type` smallint(4) DEFAULT '0',
  `price` decimal(10,4) DEFAULT '0.0000' COMMENT '实时价格'
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COMMENT='计费数据统计表';

-- ----------------------------
-- Table structure for original_data
-- ----------------------------
DROP TABLE IF EXISTS `original_data`;
CREATE TABLE `original_data` (
  `id` bigint(20) NOT NULL,
  `ad_unit_id` int(11) DEFAULT '0' COMMENT '广告单元ID',
  `ad_id` int(11) DEFAULT '0' COMMENT '广告的ID',
  `originality_id` int(11) DEFAULT '0' COMMENT '创意ID',
  `block_id` varchar(64) DEFAULT '-' COMMENT '广告位',
  `app_key` varchar(32) DEFAULT '-' COMMENT '应用的appkey',
  `ad_type` tinyint(2) DEFAULT '0' COMMENT '广告类型 1插页,2视频,3自定义',
  `brand` varchar(64) DEFAULT '-' COMMENT '设备型号',
  `model` varchar(64) DEFAULT '-' COMMENT '设备类型',
  `event_type` tinyint(2) DEFAULT '0' COMMENT '事件类型',
  `net_type` tinyint(2) DEFAULT '0' COMMENT '网络类型1:wifi,2:2G,3:3G,4:4G',
  `charge_type` tinyint(2) DEFAULT '0' COMMENT '计费类型',
  `price` decimal(10,4) DEFAULT '0.0000' COMMENT '计费单价',
  `imei` char(15) DEFAULT '-' COMMENT '国际移动设备标识码',
  `imsi` bigint(15) DEFAULT '0' COMMENT '国际移动用户识别码',
  `operator` tinyint(2) DEFAULT '0' COMMENT '运营商1:联通,2:电信,3:移动,4:其他',
  `platform` tinyint(2) DEFAULT '0' COMMENT '平台,1:安卓,2:ios',
  `resolution` varchar(16) DEFAULT '-' COMMENT '分辨率eg.720X1184',
  `uuid` varchar(64) DEFAULT '-' COMMENT '用户标识android为udid,ios为idfa',
  `app_version` varchar(32) DEFAULT '-' COMMENT '宿主应用版本',
  `sdk_version` varchar(32) DEFAULT '-' COMMENT 'Sdk版本 公司广告传入此参数',
  `client_ip` varchar(64) DEFAULT '-' COMMENT '客户端ip',
  `created_time` int(11) NOT NULL DEFAULT '0' COMMENT '服务器请求时间'
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COMMENT='上报原始表';

-- ----------------------------
-- Table structure for third_report_data  20161222
-- ----------------------------
DROP TABLE IF EXISTS `third_report_data`;
CREATE TABLE `third_report_data` (
  `id` bigint(20) NOT NULL COMMENT 'id',
  `request_id` char(32) DEFAULT NULL COMMENT '请求id',
  `ad_unit_id` int(11) DEFAULT '0' COMMENT '广告单元ID',
  `originality_id` char(32) DEFAULT NULL COMMENT '创意id',
  `event_type` tinyint(2) DEFAULT '0' COMMENT '事件类型:0未知,5展示,6点击',
  `request_status` tinyint(2) DEFAULT NULL COMMENT '请求状态:0失败,1成功',
  `request_time` int(11) DEFAULT NULL COMMENT '请求时间'
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COMMENT='上报第三方表';

