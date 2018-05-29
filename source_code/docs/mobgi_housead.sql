/*!40101 SET NAMES utf8 */;
/*!40101 SET SQL_MODE=''*/;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE IF NOT EXISTS `mobgi_housead` DEFAULT CHARSET utf8 COLLATE utf8_general_ci;


USE `mobgi_housead`;
DROP TABLE IF EXISTS `admin_account_task`;
CREATE TABLE `admin_account_task` (
  `taskid` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `taskname` varchar(100) DEFAULT NULL COMMENT '任务名称',
  `tasktype` enum('single','batch') DEFAULT NULL COMMENT '任务类型:单个,批量',
  `taskstate` enum('not_check','checked_failed','sending','sended_success','sended_failed','sended_partial_success') DEFAULT 'not_check' COMMENT '任务状态;未审核,审核失败,发放中,发放成功,发放失败,部分发放成功',
  `opertype` enum('recharge','recovery') DEFAULT NULL COMMENT '操作类型:充值,回收',
  `applyby` varchar(50) DEFAULT NULL COMMENT '申请人',
  `applymsg` varchar(100) DEFAULT NULL COMMENT '申请备注',
  `apply_time` int(10) DEFAULT '0' COMMENT '申请时间',
  `csvfile` varchar(100) DEFAULT NULL COMMENT '批量上传的csv文件地址',
  `auditby` varchar(50) DEFAULT NULL COMMENT '审核人',
  `audit_time` int(10) DEFAULT '0' COMMENT '审核时间',
  `auditstate` enum('not_check','checked_success','checked_failed') DEFAULT 'not_check' COMMENT '审核状态',
  `auditmsg` varchar(256) DEFAULT NULL COMMENT '审核备注',
  `expire_time` int(10) DEFAULT '0' COMMENT '过期时间',
  `del` tinyint(4) DEFAULT '0' COMMENT '0正常1已删除',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`taskid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `admin_account_task_detail`;
CREATE TABLE `admin_account_task_detail` (
  `detailid` int(10) NOT NULL AUTO_INCREMENT COMMENT '详情自增id',
  `email` varchar(100) DEFAULT NULL COMMENT '对象帐号',
  `opertype` enum('recharge','recovery') DEFAULT NULL COMMENT '充值回收',
  `taskid` int(10) DEFAULT NULL COMMENT '任务id',
  `taskdetailstate` enum('not_check','checked_failed','sending','sended_success','sended_failed') DEFAULT 'not_check' COMMENT '任务详情状态',
  `virtual_account_type` enum('virtual_account1','virtual_account2','virtual_account3','virtual_account4') DEFAULT NULL COMMENT '虚拟帐户类型',
  `money` decimal(18,4) DEFAULT NULL COMMENT '金额',
  `applyby` varchar(50) DEFAULT NULL COMMENT '申请人',
  `apply_time` int(10) DEFAULT '0' COMMENT '申请时间',
  `auditby` varchar(50) DEFAULT NULL COMMENT '审核人',
  `auditstate` enum('not_check','checked_success','checked_failed') DEFAULT 'not_check' COMMENT '未审核审核成功审核失败',
  `auditmsg` varchar(100) DEFAULT NULL COMMENT '审核备注',
  `audit_time` int(10) DEFAULT '0' COMMENT '审核时间',
  `expire_time` int(10) DEFAULT '0' COMMENT '过期时间',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`detailid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `admin_group`;
CREATE TABLE `admin_group` (
  `groupid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  `descrip` varchar(255) NOT NULL DEFAULT '',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ifdefault` tinyint(10) unsigned NOT NULL DEFAULT '0',
  `rvalue` text NOT NULL COMMENT '权限值',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`groupid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

insert  into `admin_group`(`groupid`,`name`,`descrip`,`createtime`,`ifdefault`,`rvalue`,`update_time`) values (1,'管理','管理',1473244449,1,'{\"_Admin_content\":{\"_Admin_Advertiser_Manage\":\"1\",\"_Admin_Advertisergroup_Manage\":\"1\",\"_Admin_Management_Operatelog\":\"1\",\"_Admin_Accounttask_Manage\":\"1\",\"_Admin_Accounttask_Audit\":\"1\",\"_Admin_Ad_Admanage\":\"1\",\"_Admin_Ad_Config\":\"1\",\"_Admin_Ad_Origainality\":\"1\",\"_Admin_Ad_Position\":\"1\",\"_Admin_Data_Monitor\":\"1\",\"_Admin_Operatelog\":\"1\"},\"_Admin_System\":{\"_Admin_User\":\"1\",\"_Admin_Group\":\"1\",\"_Admin_User_passwd\":\"1\"},\"_Admin_Stat\":{\"_Admin_Stat_monkeynum\":\"1\"}}',0);


DROP TABLE IF EXISTS `admin_operate_log`;
CREATE TABLE `admin_operate_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) DEFAULT NULL COMMENT '操作者id',
  `object` varchar(20) DEFAULT NULL COMMENT '操作对象',
  `module` varchar(30) DEFAULT NULL COMMENT '操作功能',
  `sub_module` varchar(30) DEFAULT NULL COMMENT '子功能',
  `content` varchar(2048) DEFAULT NULL COMMENT '操作内容',
  `create_time` int(10) DEFAULT NULL COMMENT '操作日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `admin_search`;
CREATE TABLE `admin_search` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menukey` varchar(255) NOT NULL DEFAULT '',
  `menuhash` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `subname` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `descrip` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `admin_user`;
CREATE TABLE `admin_user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `hash` varchar(6) NOT NULL DEFAULT '',
  `email` varchar(60) NOT NULL DEFAULT '',
  `registertime` int(10) unsigned NOT NULL DEFAULT '0',
  `registerip` varchar(16) NOT NULL DEFAULT '',
  `groupid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `idx_email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_groupid` (`groupid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

insert  into `admin_user`(`uid`,`username`,`password`,`hash`,`email`,`registertime`,`registerip`,`groupid`) values (1,'admin','5a736950d6d93d06c69f4a228a326dfc','vpSKhA','admin@aliyun.com',0,'0',0);


DROP TABLE IF EXISTS `advertiser_account_consume_log`;
CREATE TABLE `advertiser_account_consume_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) DEFAULT '0' COMMENT '用户id',
  `account_type` enum('cache','virtual_account1','virtual_account2','virtual_account3','virtual_account4') DEFAULT 'virtual_account4' COMMENT '帐号类型',
  `balance_before` decimal(18,4) DEFAULT '0.0000' COMMENT '扣费前帐号余额',
  `balance` decimal(18,4) DEFAULT '0.0000' COMMENT '当前帐号类型的余额',
  `batchdeductionid` int(10) DEFAULT '0' COMMENT '批量扣除id',
  `price` decimal(18,4) DEFAULT '0.0000' COMMENT '消耗金额',
  `real_price` decimal(18,4) DEFAULT '0.0000' COMMENT '真实消耗金额',
  `need_price` decimal(18,4) DEFAULT '0.0000' COMMENT '还需要扣除金额',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `advertiser_account_consumption_limit`;
CREATE TABLE `advertiser_account_consumption_limit` (
  `uid` int(10) NOT NULL COMMENT '帐户id',
  `day_consumption_limit` decimal(18,4) DEFAULT NULL COMMENT '日消耗限额',
  `operator` varchar(50) DEFAULT NULL COMMENT '操作者',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `advertiser_account_day_consumption`;
CREATE TABLE `advertiser_account_day_consumption` (
  `uid` int(10) NOT NULL COMMENT '广告主id',
  `account_type` enum('cache','virtual_account1','virtual_account2','virtual_account3','virtual_account4') NOT NULL COMMENT '帐号类型',
  `date` int(10) DEFAULT NULL COMMENT '日期Ymd',
  `consumption` decimal(18,4) DEFAULT NULL COMMENT '消费',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  UNIQUE KEY `uid_date_type` (`uid`,`date`,`account_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `advertiser_account_detail`;

CREATE TABLE `advertiser_account_detail` (
  `uid` int(10) NOT NULL COMMENT '用户id',
  `account_type` enum('cache','virtual_account1','virtual_account2','virtual_account3','virtual_account4') NOT NULL COMMENT '帐户类型cache现金帐户virtual_account1,2,3,4虚拟帐户1,2,3,4',
  `balance` decimal(18,4) DEFAULT '0.0000' COMMENT '余额',
  `create_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  UNIQUE KEY `uid_accounttype` (`uid`,`account_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `advertiser_account_log`;
CREATE TABLE `advertiser_account_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) DEFAULT NULL COMMENT '帐号id',
  `account_type` enum('cache','virtual_account1','virtual_account2','virtual_account3','virtual_account4') DEFAULT NULL COMMENT '帐户类型',
  `operate_type` enum('recharge','deduction','recovery') DEFAULT NULL COMMENT 'recharge充值deduction扣费recovery回收',
  `trade_balance` decimal(18,4) DEFAULT NULL COMMENT '发生的金额',
  `description` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `advertiser_account_virtual_detail`;
CREATE TABLE `advertiser_account_virtual_detail` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) DEFAULT NULL COMMENT '广告主id',
  `account_type` enum('virtual_account1','virtual_account2','virtual_account3','virtual_account4') DEFAULT 'virtual_account1' COMMENT '虚拟帐号类型',
  `taskdetailid` int(10) DEFAULT NULL COMMENT '任务详情id',
  `balance` decimal(18,4) DEFAULT NULL COMMENT '余额',
  `status` enum('normal','runout','expired') DEFAULT 'normal' COMMENT '状态:正常,已过期',
  `expire_time` int(10) DEFAULT NULL COMMENT '过期时间',
  `operator` varchar(50) DEFAULT NULL COMMENT '操作者',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_type_taskdetailid` (`uid`,`account_type`,`taskdetailid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `advertiser_ad_global_config`;
CREATE TABLE `advertiser_ad_global_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) NOT NULL COMMENT '1插页，2视频，3自定义',
  `value` text NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `advertiser_batch_deduction_detail`;
CREATE TABLE `advertiser_batch_deduction_detail` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `originality_id` int(10) DEFAULT NULL COMMENT '创意id',
  `price` decimal(18,4) DEFAULT NULL COMMENT '扣费金额',
  `status` enum('notdeducted','partdeducted','deducted') DEFAULT 'notdeducted' COMMENT 'notdeducted未扣除,partdeducted部分扣除成功,deducted已扣除',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `advertiser_direct`;
CREATE TABLE `advertiser_direct` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `advertiser_uid` int(10) DEFAULT NULL COMMENT '用户id',
  `direct_name` varchar(50) NOT NULL DEFAULT '' COMMENT '定向名称',
  `area_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '区域类型1不限，2限制区域',
  `area_range` varchar(255) DEFAULT NULL COMMENT '区域范围json数据',
  `age_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '年龄定向类型1不限，2执行年龄段',
  `age_direct_range` varchar(200) DEFAULT NULL COMMENT '年龄定向范围json数据',
  `sex_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '性别定向类型0不限1男2女',
  `os_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'os定向类型0不限1定向',
  `network_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '网络的类型0不限1定向',
  `network_direct_range` varchar(100) DEFAULT NULL COMMENT '设置网络内容json数据',
  `operator_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '运营商类型0不限1定向',
  `operator_direct_range` varchar(255) DEFAULT NULL COMMENT '运营商json数据',
  `brand_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '品牌的类型0不限1定向',
  `brand_direct_range` varchar(512) DEFAULT NULL COMMENT '品牌的类型json数据',
  `screen_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '手机屏幕大小',
  `screen_direct_range` varchar(100) DEFAULT NULL COMMENT '手机屏幕json数据',
  `interest_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '兴趣',
  `interest_direct_range` varchar(255) DEFAULT NULL,
  `pay_ability_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '付费能力',
  `pay_ability_range` varchar(200) DEFAULT NULL,
  `game_frequency_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '游戏频率',
  `game_frequency_range` varchar(200) DEFAULT NULL,
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_direct_name` (`direct_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `advertiser_group`;
CREATE TABLE `advertiser_group` (
  `groupid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户组id',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '用户组名',
  `descrip` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `ifdefault` tinyint(10) unsigned NOT NULL DEFAULT '0',
  `rvalue` text NOT NULL COMMENT '权限数据',
  `del` tinyint(4) DEFAULT '0' COMMENT '0正常1已删除',
  `updatetime` int(10) DEFAULT '0' COMMENT '更新时间',
  `operator` int(10) DEFAULT NULL COMMENT '操作者',
  PRIMARY KEY (`groupid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `advertiser_operate_log`;
CREATE TABLE `advertiser_operate_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` int(10) DEFAULT NULL COMMENT '操作者id',
  `object` varchar(20) DEFAULT NULL COMMENT '操作对象',
  `module` varchar(30) DEFAULT NULL COMMENT '操作功能',
  `sub_module` varchar(30) DEFAULT NULL COMMENT '子功能',
  `content` varchar(512) DEFAULT NULL COMMENT '操作内容',
  `create_time` int(10) DEFAULT NULL COMMENT '操作日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `advertiser_originality_conf`;
CREATE TABLE `advertiser_originality_conf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '创意id',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '创意名称',
  `originality_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '类型2插屏-全屏1视频广告',
  `ad_target_type` varchar(500) NOT NULL DEFAULT '' COMMENT '广告目标类型 1网页 2 IOS应用 3Android应用',
  `charge_type` varchar(500) NOT NULL DEFAULT '' COMMENT '计费类型1CPC2CPM',
  `upload_content` varchar(200) NOT NULL DEFAULT '' COMMENT '上传内容',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `is_delete` tinyint(3) NOT NULL DEFAULT '0' COMMENT '软删除0未删除1删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `advertiser_originality_relation_position`;
CREATE TABLE `advertiser_originality_relation_position` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表头id',
  `originality_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联的创意的id',
  `app_key` varchar(50) NOT NULL DEFAULT '' COMMENT '应用的appkey',
  `app_name` varchar(50) NOT NULL DEFAULT '' COMMENT '应用的名称',
  `ad_position_key` varchar(50) NOT NULL DEFAULT '' COMMENT '应用的广告位的key',
  `ad_position_name` varchar(50) NOT NULL DEFAULT '' COMMENT '应用的广告位的name',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '广告位状态 暂时没有用，留扩展',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `is_delete` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否删除，0未删除1已删除',
  PRIMARY KEY (`id`),
  KEY `idx_originality_id` (`originality_id`),
  KEY `idx_ad_position_key` (`ad_position_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `advertiser_user`;
CREATE TABLE `advertiser_user` (
  `advertiser_uid` int(10) NOT NULL AUTO_INCREMENT COMMENT '广告主id',
  `advertiser_name` varchar(30) DEFAULT NULL COMMENT '广告主名称',
  `account_type` enum('advertiser','agent') DEFAULT NULL COMMENT '帐号类型',
  `password` varchar(32) DEFAULT NULL COMMENT '密码',
  `hash` varchar(6) DEFAULT NULL COMMENT '盐',
  `email` varchar(50) DEFAULT NULL COMMENT '邮箱',
  `address` varchar(100) DEFAULT NULL COMMENT '所在地区',
  `status` enum('notactive','actived','notchecked','checked_success','checked_failed') DEFAULT 'notactive' COMMENT '未激活,激活,未审核,审核通过,审核不通过',
  `agent` int(10) DEFAULT NULL COMMENT '绑定代理商',
  `agent_status` enum('notbind','bindconfirm','bindcheck','binded','unbindconfirm','unbindcheck') DEFAULT NULL COMMENT '未绑定,绑定确认,绑定审核,已绑定,解绑确认,解绑审核',
  `company_name` varchar(100) DEFAULT NULL COMMENT '公司名称',
  `business_license` varchar(512) DEFAULT NULL COMMENT '营业执照',
  `ad_qualification` varchar(512) DEFAULT NULL COMMENT '广告资质',
  `register_ip` varchar(16) DEFAULT NULL COMMENT '注册ip',
  `register_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `groupid` int(10) DEFAULT NULL COMMENT '所属组',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`advertiser_uid`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `advertiser_user_nonce`;
CREATE TABLE `advertiser_user_nonce` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `nonce` int(10) DEFAULT NULL COMMENT '现时标识',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间戳',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `delivery_ad_conf_list`;
CREATE TABLE `delivery_ad_conf_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '广告id',
  `ad_name` varchar(50) NOT NULL DEFAULT '' COMMENT '广告名称',
  `ad_target_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '广告目标类型 1网页 2 IOS应用 3Android应用',
  `ad_target` varchar(200) NOT NULL DEFAULT '' COMMENT '广告目标地址',
  `unit` int(10) NOT NULL DEFAULT '0' COMMENT '投放单元',
  `date_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1指定开始时间2指定开始与结束时间',
  `date_range` varchar(60) DEFAULT NULL COMMENT '日期范围json数据',
  `time_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1全部时间段，2指定时间段',
  `time_range` varchar(50) DEFAULT NULL COMMENT '时间段范围json数据',
  `area_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '区域类型1不限，2限制区域',
  `area_range` varchar(255) DEFAULT NULL COMMENT '区域范围json数据',
  `age_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '年龄定向类型1不限，2执行年龄段',
  `age_direct_range` varchar(200) DEFAULT NULL COMMENT '年龄定向范围json数据',
  `sex_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '性别定向类型0不限1男2女',
  `os_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'os定向类型0不限1定向',
  `network_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '网络的类型0不限1定向',
  `network_direct_range` varchar(100) DEFAULT NULL COMMENT '设置网络内容json数据',
  `operator_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '运营商类型0不限1定向',
  `operator_direct_range` varchar(255) DEFAULT NULL COMMENT '运营商json数据',
  `brand_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '品牌的类型0不限1定向',
  `brand_direct_range` varchar(512) DEFAULT NULL COMMENT '品牌的类型json数据',
  `screen_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '手机屏幕大小',
  `screen_direct_range` varchar(100) DEFAULT NULL COMMENT '手机屏幕json数据',
  `interest_direct_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '兴趣',
  `interest_direct_range` varchar(255) DEFAULT NULL,
  `pay_ability_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '付费能力',
  `pay_ability_range` varchar(200) DEFAULT NULL,
  `game_frequency_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '游戏频率',
  `game_frequency_range` varchar(200) DEFAULT NULL,
  `charge_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '计费类型1CPC2CPM',
  `price` float(18,4) NOT NULL COMMENT '单价',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `account_id` int(10) DEFAULT NULL COMMENT '用户的ID',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '1=>''投放中'',2=>''审核中'',3=>''审核未通过'',4=>''已暂停'',5=>''已删除'',6=>''已过期''',
  `direct_id` int(10) NOT NULL DEFAULT '0' COMMENT '定向配置的id',
  PRIMARY KEY (`id`),
  KEY `idx_account_id` (`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `delivery_originality_relation`;
CREATE TABLE `delivery_originality_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表头',
  `originality_conf_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联的创意的id',
  `ad_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '广告的id',
  `unit_id` int(11) NOT NULL DEFAULT '0' COMMENT '广告单元的id冗余的字段',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '创意名称',
  `desc` varchar(50) NOT NULL DEFAULT '' COMMENT '创意描述',
  `originality_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '类型2插屏-全屏1视频广告',
  `strategy` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1优选曝光2优选曝光',
  `upload_content` varchar(2000) NOT NULL DEFAULT '' COMMENT '上传内容',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `account_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户id',
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '1=>''投放中'',2=>''审核中'',3=>''审核未通过'',4=>''已暂停'',5=>''已删除'',6=>''已过期''',
  `filter_app_conf` text COMMENT '过滤的应用',
  `weight` decimal(18,1) NOT NULL DEFAULT '1.0' COMMENT '权重',
  PRIMARY KEY (`id`),
  KEY `idx_account_id` (`account_id`),
  KEY `idx_originality_conf_id` (`originality_conf_id`),
  KEY `idx_unit_id` (`unit_id`),
  KEY `idx_ad_id` (`ad_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `delivery_unit_conf`;
CREATE TABLE `delivery_unit_conf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '投放单元id',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '广告名称',
  `limit_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '投放限额的类型0不限1定向',
  `limit_range` float(8,2) NOT NULL DEFAULT '0.00' COMMENT '投放限额的',
  `mode_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1匀速2普通',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `account_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `status` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;



#新增广点通直投配置 广告组
DROP TABLE IF EXISTS `advertiser_gdt_adgroup`;

CREATE TABLE `advertiser_gdt_adgroup` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `advertiser_uid` INT(10) DEFAULT NULL COMMENT 'uid',
  `adgroup_id` INT(10) DEFAULT NULL COMMENT '广点通广告id',
  `adgroup_name` VARCHAR(120) DEFAULT NULL COMMENT '广点通广告名称',
  `local_config` TEXT COMMENT '广点通本地定向配置',
  `config` TEXT COMMENT '广点通定向配置',
  `sync_status` ENUM('success','failed') DEFAULT NULL COMMENT '广点同步状态',
  `sync_response` VARCHAR(100) DEFAULT NULL COMMENT '广点通同步返回信息',
  `del` TINYINT(4) DEFAULT '0' COMMENT '0正常1已删除',
  `create_time` INT(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` INT(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_adgroup_id` (`adgroup_id`),
  UNIQUE KEY `idx_adgroup_name` (`adgroup_name`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#新增广点通直投配置 创意表
DROP TABLE IF EXISTS `advertiser_gdt_creative`;
CREATE TABLE `advertiser_gdt_creative` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `advertiser_uid` INT(10) DEFAULT NULL COMMENT 'uid',
  `creative_id` VARCHAR(50) DEFAULT NULL COMMENT '广点通创意id',
  `creative_name` VARCHAR(30) DEFAULT NULL COMMENT '广点通创意名称',
  `config` TEXT COMMENT '广点通创意配置',
  `sync_status` ENUM('success','failed') DEFAULT NULL COMMENT '广点同步状态',
  `sync_response` VARCHAR(1000) DEFAULT NULL COMMENT '广点通同步返回信息',
  `del` TINYINT(4) DEFAULT '0' COMMENT '0正常1已删除',
  `create_time` INT(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` INT(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_creative_id` (`creative_id`),
  UNIQUE KEY `idx_creative_name` (`creative_name`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#新增广点通直投配置 是否开通广点通直投
DROP TABLE IF EXISTS `advertiser_gdt_direct_config`;
CREATE TABLE `advertiser_gdt_direct_config` (
  `uid` int(10) NOT NULL COMMENT '广告主id',
  `status` enum('on','off') DEFAULT 'off' COMMENT 'on开,off关',
  `advertiser_id` int(10) DEFAULT NULL COMMENT '广点通广告主id',
  `app_id` int(10) DEFAULT NULL COMMENT '广点通app_id',
  `app_key` varchar(50) DEFAULT NULL COMMENT '广点通私钥',
  `plan_id` int(10) DEFAULT NULL COMMENT '广点通推广计划id',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#新增广点通直投配置 图片资源
DROP TABLE IF EXISTS `advertiser_gdt_image`;
CREATE TABLE `advertiser_gdt_image` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `advertiser_uid` INT(10) DEFAULT NULL COMMENT 'uid',
  `image_id` VARCHAR(50) DEFAULT NULL COMMENT '广点通图片id',
  `image_name` VARCHAR(120) DEFAULT NULL COMMENT '广点通图片名称',
  `config` TEXT COMMENT '广点通定向配置',
  `sync_status` ENUM('success','failed') DEFAULT NULL COMMENT '广点同步状态',
  `sync_response` TEXT COMMENT '广点通同步返回信息',
  `del` TINYINT(4) DEFAULT '0' COMMENT '0正常1已删除',
  `create_time` INT(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` INT(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_image_id` (`image_id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


#新增广点通直投配置 流媒体
DROP TABLE IF EXISTS `advertiser_gdt_media`;
CREATE TABLE `advertiser_gdt_media` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `advertiser_uid` INT(10) DEFAULT NULL COMMENT 'uid',
  `media_id` VARCHAR(50) DEFAULT NULL COMMENT '广点通视频id',
  `media_name` VARCHAR(120) DEFAULT NULL COMMENT '广点通视频名称',
  `config` TEXT COMMENT '广点通定向配置',
  `sync_status` ENUM('success','failed') DEFAULT NULL COMMENT '广点同步状态',
  `sync_response` TEXT COMMENT '广点通同步返回信息',
  `del` TINYINT(4) DEFAULT '0' COMMENT '0正常1已删除',
  `create_time` INT(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` INT(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_video_id` (`media_id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#新增广点通直投配置 定向
DROP TABLE IF EXISTS `advertiser_gdt_targeting`;
CREATE TABLE `advertiser_gdt_targeting` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `advertiser_uid` INT(10) DEFAULT NULL COMMENT 'uid',
  `targeting_id` INT(10) DEFAULT NULL COMMENT '广点通定向id',
  `targeting_name` VARCHAR(120) DEFAULT NULL COMMENT '广点通定向名称',
  `config` TEXT COMMENT '广点通定向配置',
  `sync_status` ENUM('success','failed') DEFAULT NULL COMMENT '广点同步状态',
  `sync_response` VARCHAR(100) DEFAULT NULL COMMENT '广点通同步返回信息',
  `del` TINYINT(4) DEFAULT '0' COMMENT '0正常1已删除',
  `create_time` INT(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` INT(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_targeting_id` (`targeting_id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
#新增appkey下发配置表
CREATE TABLE `advertiser_ad_appkey_config` (
  `id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` VARCHAR(60) DEFAULT NULL COMMENT '配置名称',
  `config` TEXT COMMENT '配置,json格式,key 1插页，2视频，3自定义',
  `del` TINYINT(4) DEFAULT '0' COMMENT '0正常 1删除',
  `operator` VARCHAR(50) DEFAULT NULL COMMENT '操作者',
  `create_time` INT(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` INT(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
#新增appkey_config_id关联
ALTER TABLE `advertiser_originality_relation_position` ADD COLUMN `appkey_config_id` TINYINT(4) DEFAULT NULL COMMENT '下发配置表' AFTER STATUS;
ALTER TABLE `advertiser_originality_relation_position` ADD INDEX `idx_app_key` (`app_key`);
#广点通直投标的物表
CREATE TABLE `advertiser_gdt_product` (
  `id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `advertiser_uid` INT(10) DEFAULT NULL COMMENT 'uid',
  `product_refs_id` VARCHAR(128) DEFAULT NULL COMMENT '产品id',
  `product_type` VARCHAR(50) DEFAULT NULL COMMENT '标的物类型',
  `config` TEXT COMMENT '配置',
  `sync_status` ENUM('success','failed') DEFAULT NULL COMMENT '广点通同步状态',
  `sync_response` VARCHAR(100) DEFAULT NULL COMMENT '广点通同步返回信息',
  `del` TINYINT(4) DEFAULT '0' COMMENT '0正常1删除',
  `create_time` INT(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` INT(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_ref_type` (`product_refs_id`,`product_type`)
) ENGINE=INNODB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

#################20161212 hunter.fang 支持housead管理后台和广告主的报表帐号设置 start#################
CREATE TABLE `advertiser_user_report` (
  `advertiser_uid` INT(10) NOT NULL COMMENT '帐号id',
  `related_advertiser_uid` INT(10) NOT NULL COMMENT '关联的帐号id',
  `operator` INT(10) DEFAULT NULL COMMENT '操作者',
  `create_time` INT(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` INT(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`advertiser_uid`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;
ALTER TABLE `mobgi_housead`.`advertiser_user` ADD COLUMN `isreport` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0:正常帐号 1:报表查看帐号' AFTER account_type;
#################20161212 hunter.fang 支持housead管理后台和广告主的报表帐号设置 end#################


#################20161206_20161231 housead sona api开发中 start#################
#1.添加字段
alter table `mobgi_housead`.`advertiser_user` add column `appkey` varchar(50) NULL COMMENT 'appkey' after `email`;
#2.查看数据
SELECT CONCAT(advertiser_uid, HASH), MD5(CONCAT(advertiser_uid, HASH)), appkey FROM advertiser_user ;

统一设置appkey
update advertiser_user set appkey=MD5(CONCAT(advertiser_uid, HASH));
#3.投放单元表: 新增调用方投放单元id, 索引
alter table `mobgi_housead`.`delivery_unit_conf` add column `outer_unit_id` int(10) DEFAULT '0' NULL COMMENT '调用方投放单元id' after `account_id`;
ALTER TABLE `delivery_unit_conf` ADD KEY accountid_outerunitid(`account_id`,  `outer_unit_id`);
alter table `mobgi_housead`.`delivery_unit_conf` add column `del` tinyint(1) DEFAULT '0' NOT NULL COMMENT '0正常1已删除' after `status`;
alter table `mobgi_housead`.`delivery_ad_conf_list` change `ad_target_type` `ad_target_type` tinyint(3) default '0' NOT NULL comment '广告目标类型 3网页 2IOS应用 1Android应用'; #改注释
#4广告投放表: 新增调用方广告id, 索引
alter table `mobgi_housead`.`delivery_ad_conf_list` add column `outer_ad_id` int(10) DEFAULT '0' NOT NULL COMMENT '调用方广告id' after `direct_id`;
ALTER TABLE `delivery_ad_conf_list` ADD KEY accountid_outeradid(`account_id`,  `outer_ad_id`);
alter table `mobgi_housead`.`delivery_ad_conf_list` add column `del` tinyint(1) DEFAULT '0' NOT NULL COMMENT '0正常1已删除' after `status`;
alter table `mobgi_housead`.`delivery_ad_conf_list` add column `time_series` varchar(336) DEFAULT '' NOT NULL COMMENT '投放时间段,48*7位字符串且都是0或1' after `time_range`;
#操作日志表操作内容字段长度变更
alter table `mobgi_housead`.`advertiser_operate_log` change `content` `content` varchar(2048) character set utf8 collate utf8_general_ci NULL  comment '操作内容';
#5广告创意表:新增调用方创意id, 索引
alter table `mobgi_housead`.`delivery_originality_relation` add column `outer_originality_id` int(10) DEFAULT '0' NULL COMMENT '调用方创意id' after `weight`;
ALTER TABLE `delivery_originality_relation` ADD KEY accountid_outeroriginalityid(`account_id`,  `outer_originality_id`);
alter table `mobgi_housead`.`delivery_originality_relation` add column `del` tinyint(1) DEFAULT '0' NOT NULL COMMENT '0正常1已删除' after `status`;
#6定向:新增调用方定向id, 索引
alter table `mobgi_housead`.`advertiser_direct` add column `del` tinyint(1) DEFAULT '0' NOT NULL COMMENT '0正常1删除' after `direct_config`;
alter table `mobgi_housead`.`advertiser_direct` add column `outer_direct_id` int(10) DEFAULT '0' NOT NULL COMMENT '调用方定向id' after `del`;
ALTER TABLE `advertiser_direct` ADD KEY advertiseruid_outerdirectid(`advertiser_uid`,  `outer_direct_id`);
ALTER TABLE `advertiser_direct` DROP INDEX idx_direct_name;
ALTER TABLE `advertiser_direct` ADD UNIQUE KEY advertiseruid_directname(`advertiser_uid`,  `direct_name`);
#7视频模块:
CREATE TABLE `advertiser_video` (
  `id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `advertiser_uid` INT(10) DEFAULT NULL COMMENT '帐号id',
  `video_id` VARCHAR(50) NOT NULL COMMENT '帐号id:视频md5串',
  `video_name` VARCHAR(50) DEFAULT NULL COMMENT '视频名称',
  `url` VARCHAR(255) DEFAULT NULL COMMENT '视频地址',
  `signature` VARCHAR(32) DEFAULT NULL COMMENT '视频签名',
  `width` INT(10) DEFAULT NULL COMMENT '视频长',
  `height` INT(10) DEFAULT NULL COMMENT '视频宽',
  `frames` INT(10) DEFAULT NULL COMMENT '视频帧数',
  `size` INT(10) DEFAULT NULL COMMENT '视频大小',
  `file_format` ENUM('avi','mp4') DEFAULT NULL COMMENT '视频格式',
  `outer_video_id` INT(10) DEFAULT NULL COMMENT '调用方视频id',
  `create_time` INT(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` INT(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_video_id` (`video_id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#8图片模块:
CREATE TABLE `advertiser_image` (
  `id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `advertiser_uid` INT(10) DEFAULT NULL COMMENT '帐号id',
  `image_id` VARCHAR(50) NOT NULL COMMENT '帐号id:图片md5串',
  `image_name` VARCHAR(50) DEFAULT NULL COMMENT '图片名称',
  `url` VARCHAR(255) DEFAULT NULL COMMENT '图片地址',
  `signature` VARCHAR(32) DEFAULT NULL COMMENT '图片签名',
  `width` INT(10) DEFAULT NULL COMMENT '图片长',
  `height` INT(10) DEFAULT NULL COMMENT '图片宽',
  `size` INT(10) DEFAULT NULL COMMENT '图片大小',
  `file_format` ENUM('jpg','png') DEFAULT NULL COMMENT '图片格式',
  `outer_image_id` INT(10) DEFAULT NULL COMMENT '调用方图片id',
  `create_time` INT(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` INT(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_image_id` (`image_id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#9字段更改
#varchar类型字段，扩展到支持120英文字符（1中文=3英文）,涉及的表有投放单元,广告,创意,定向四个
alter table `mobgi_housead`.`delivery_ad_conf_list` change `ad_name` `ad_name` varchar(120) character set utf8 collate utf8_general_ci default '' NOT NULL comment '广告名称';
alter table `mobgi_housead`.`delivery_originality_relation` change `title` `title` varchar(120) character set utf8 collate utf8_general_ci default '' NOT NULL comment '创意名称', change `desc` `desc` varchar(120) character set utf8 collate utf8_general_ci default '' NOT NULL comment '创意描述';
alter table `mobgi_housead`.`delivery_unit_conf` change `name` `name` varchar(120) character set utf8 collate utf8_general_ci default '' NOT NULL comment '投放单元名称';
alter table `mobgi_housead`.`advertiser_direct` change `direct_name` `direct_name` varchar(120) character set utf8 collate utf8_general_ci default '' NOT NULL comment '定向名称';
#################20161206_20161231 housead sona api开发中 end#################

alter table `mobgi_housead`.`delivery_ad_conf_list` drop column `area_type`, drop column `area_range`, drop column `age_direct_type`, drop column `age_direct_range`, drop column `sex_direct_type`, drop column `os_direct_type`, drop column `network_direct_type`, drop column `network_direct_range`, drop column `operator_direct_type`, drop column `operator_direct_range`, drop column `brand_direct_type`, drop column `brand_direct_range`, drop column `screen_direct_type`, drop column `screen_direct_range`, drop column `interest_direct_type`, drop column `interest_direct_range`, drop column `pay_ability_type`, drop column `pay_ability_range`, drop column `game_frequency_type`, drop column `game_frequency_range`,
   add column `direct_config` text NULL COMMENT '定向配置' ,
   change `unit` `unit_id` int(10) default '0' NOT NULL comment '投放单元';
   
   alter table `mobgi_housead`.`advertiser_direct` drop column `game_frequency_range`, drop column `game_frequency_type`, drop column `pay_ability_range`, drop column `pay_ability_type`, drop column `interest_direct_range`, drop column `interest_direct_type`, drop column `screen_direct_range`, drop column `screen_direct_type`, drop column `brand_direct_range`, drop column `brand_direct_type`, drop column `operator_direct_range`, drop column `operator_direct_type`, drop column `network_direct_range`, drop column `network_direct_type`, drop column `os_direct_type`, drop column `sex_direct_type`, drop column `age_direct_range`, drop column `age_direct_type`, drop column `area_range`, drop column `area_type`,
   add column `direct_config` text NULL COMMENT '定向配置' ;
   

alter table `mobgi_housead`.`delivery_originality_relation` 
   add column `del` tinyint(2) DEFAULT '0' NOT NULL COMMENT '1删除，0，未删除' after `status`;
   
   
  alter table `mobgi_housead`.`advertiser_originality_relation_position` 
   change `originality_id` `originality_conf_id` int(10) default '0' NOT NULL comment '关联的创意的id';
   
   
   /********************删除多余的表*******************/
  DROP TABLE `mobgi_housead`.`advertiser_ad_global_config`;
   
   
  DROP TABLE IF EXISTS `mobgi_housead`.`advertiser_config`;
CREATE TABLE `mobgi_housead`.`advertiser_config` (
   `config_key` varchar(50) NOT NULL,
   `config_value` varchar(50) NOT NULL,
   `admin_id` int(10) NOT NULL,
   `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   UNIQUE KEY `config_key` (`config_key`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
 
DROP TABLE IF EXISTS `mobgi_housead`.`advertiser_ad_policy_config`;
CREATE TABLE `mobgi_housead`.`advertiser_ad_policy_config` (
   `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
   `name` varchar(60) NOT NULL COMMENT '配置名称',
   `config` text NOT NULL COMMENT '配置,json格式,key 2插页，1视频，3自定义',
   `del` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0正常 1删除',
   `admin_id` int(10) NOT NULL COMMENT '操作者',
   `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
   `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
 alter table `mobgi_housead`.`advertiser_originality_relation_position` 
   add column `policy_config_id` int(10) DEFAULT '0' NOT NULL COMMENT '广告商策略配置Id' after `appkey_config_id`,
   change `appkey_config_id` `appkey_config_id` int(10) NULL  comment '下发配置表';
   

ALTER TABLE `mobgi_housead`.`advertiser_user` ADD COLUMN `type` TINYINT(4) DEFAULT '1' NOT NULL COMMENT '1普通2广点通3报表查看4广告商（实时）5广告商（同步）' AFTER `account_type`;
ALTER TABLE `mobgi_housead`.`advertiser_user` DROP COLUMN `isreport`;


DROP TABLE IF EXISTS `mobgi_housead`.`advertiser_user_lable`;
CREATE TABLE `mobgi_housead`.`advertiser_user_lable` (
   `ad_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '广告商名称',
   `label_type` tinyint(3) NOT NULL COMMENT '用户标签类型1普通2广点通3报表查看4广告商（实时）5广告商（同步）',
   `ads_name` varchar(10) NOT NULL DEFAULT '' COMMENT '广告商名称',
   `advertiser_uid` int(10) NOT NULL DEFAULT '0' COMMENT '关联的账号id',
   `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
   `update_time` timestamp NULL DEFAULT NULL COMMENT '更新时间',
   PRIMARY KEY (`ad_id`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
 insert into `advertiser_user_lable` (`ad_id`, `label_type`, `ads_name`, `advertiser_uid`, `create_time`, `update_time`) values('1','1','普通','0','2016-12-22 18:30:08','2016-12-22 18:30:08');
insert into `advertiser_user_lable` (`ad_id`, `label_type`, `ads_name`, `advertiser_uid`, `create_time`, `update_time`) values('2','4','椰子传媒','0','2016-12-22 18:30:08','2016-12-22 18:30:08');
#################20161219 housead 流量分配开发中 end#################

#################20170103 housead 管理后台权限设置 hunter.fang start#################
alter table `mobgi_housead`.`admin_group` 
   add column `del` tinyint(4) DEFAULT '0' NULL COMMENT '0正常1已删除' after `rvalue`, 
   add column `operator` int(10) NULL COMMENT '操作者' after `del`;
#################20170103 housead 管理后台权限设置 hunter.fang end#################

#################20170105 housead 管理后台计费脚本,新增日限额,单元限额限制. hunter.fang start#################
#新增状态
alter table `mobgi_housead`.`advertiser_batch_deduction_detail` 
   change `status` `status` enum('notdeducted','partdeducted','deducted','outofdaylimit','outofunitlimit') character set utf8 collate utf8_general_ci default 'notdeducted' NULL  comment 'notdeducted未扣除,partdeducted部分扣除成功,deducted已扣除,outofdaylimit超过日限额,outofunitlimit超过单元限额';
#新增投放单元,创意日消耗额表
CREATE TABLE `advertiser_unit_originality_day_consumption` (
  `unit_id` INT(10) NOT NULL COMMENT '投放单元id',
  `originality_id` INT(10) NOT NULL COMMENT '创意id',
  `date` INT(10) DEFAULT NULL COMMENT '日期Ymd',
  `consumption` DECIMAL(18,4) DEFAULT NULL COMMENT '消费',
  `create_time` INT(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` INT(10) DEFAULT NULL COMMENT '更新时间',
  UNIQUE KEY `unit_date_originality` (`unit_id`,`date`, `originality_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;
#################20170105 housead 管理后台计费脚本,新增日限额,单元限额限制. hunter.fang end#################

#################20170511 housead 投放优化. hunter.fang start#################
-- #广告表新增创意类型
-- ALTER TABLE `mobgi_housead`.`delivery_ad_conf_list`
-- ADD COLUMN `originality_conf_id`  int(10) NOT NULL DEFAULT '0' COMMENT '关联创意类型的id' AFTER `id`;
-- 联表更新添加字段：
-- UPDATE `mobgi_housead`.delivery_ad_conf_list a, `mobgi_housead`.delivery_originality_relation b SET a.originality_conf_id = b.originality_conf_id WHERE a.id = b.ad_id;
-- ALTER TABLE `mobgi_housead`.`delivery_ad_conf_list` drop column `originality_conf_id`;

#广告表新增创意类型
ALTER TABLE `mobgi_housead`.`delivery_ad_conf_list` ADD COLUMN `originality_type` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '类型2插屏-全屏1视频广告3交叉推广';
UPDATE `mobgi_housead`.delivery_ad_conf_list a, `mobgi_housead`.delivery_originality_relation b SET a.originality_type = b.originality_type WHERE a.id = b.ad_id;
-- #创意表去除创意类型关联id
-- ALTER TABLE `mobgi_housead`.`delivery_originality_relation` drop column `originality_conf_id`; 

#修改注释
alter table `mobgi_housead`.`delivery_ad_conf_list` 
   change `time_series` `time_series` varchar(336) character set utf8 collate utf8_general_ci default '' NOT NULL comment '周一至周日';

alter table `mobgi_housead`.`delivery_ad_conf_list` add column `hour_set_type` tinyint(1) DEFAULT '0' NULL COMMENT '0指定时间段快捷设置1指定时间段高级设置' after `time_type`;
alter table `mobgi_housead`.`delivery_ad_conf_list` change `time_type` `time_type` tinyint(3) default '0' NOT NULL comment '0全部时间段，1指定时间段';
#新增应用名，曝光地址监控，点击地址监控
alter table `mobgi_housead`.`delivery_ad_conf_list` 
   add column `app_name` varchar(100) DEFAULT '' NOT NULL COMMENT '应用名' after `package_name`, 
   add column `imp_trackers` varchar(1000) DEFAULT '' NOT NULL COMMENT '曝光地址监控' after `app_name`, 
   add column `click_trackers` varchar(1000) DEFAULT '' NULL COMMENT '点击地址监控' after `imp_trackers`,
   add column `jump_type` tinyint(1) DEFAULT '1' NOT NULL COMMENT '点击后动作0android静默下载 1android跳转应用市场下载 1IOS AppStore打开 2网页默认浏览器打开 3网页自建浏览器打开 7android 通知栏下载 8IOS 应用内商店内页打开' after `package_name`;

#日消耗表新增广告id字段
alter table `mobgi_housead`.`advertiser_unit_originality_day_consumption` 
   add column `ad_id` int(10)  default '0' NOT NULL COMMENT '广告id' after `unit_id`;
#同步广告id到旧表
UPDATE advertiser_unit_originality_day_consumption SET ad_id = (SELECT ad_id FROM `delivery_originality_relation` WHERE id=advertiser_unit_originality_day_consumption.originality_id);

#交叉推广项目 新增广告子类型
alter table `mobgi_api`.`ad_dever_pos` 
   add column `ad_sub_type` varchar(20) DEFAULT '' NOT NULL COMMENT '广告位子类型' after `pos_key`;
alter table `mobgi_api`.`ad_dever_pos` 
   change `ad_sub_type` `ad_sub_type` tinyint(1) default '0' NOT NULL comment '自定义广告的子类型 1（精品橱窗-焦点图）2（精品橱窗-应用墙）3（原生banner）';

#新建应用级别的配置表
CREATE TABLE `advertiser_appkey_config` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `app_key` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '应用的appkey',
  `app_name` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '应用的名称',
  `appkey_config_id` INT(10) DEFAULT '0' COMMENT '下发配置表',
  `policy_config_id` INT(10) NOT NULL DEFAULT '0' COMMENT '广告商策略配置Id',
  `create_time` INT(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` INT(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `del` TINYINT(3) NOT NULL DEFAULT '0' COMMENT '是否删除，0未删除1已删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_app_key` (`app_key`)
) ENGINE=INNODB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;


#################20170511 housead 投放优化. hunter.fang end#################



#################20170623 housead 定向包开发. hunter.fang start#################
CREATE TABLE `delivery_device_direct` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `imei` VARCHAR(32) DEFAULT '' COMMENT 'android设备标识',
  `appkey` VARCHAR(32) DEFAULT '' COMMENT '游戏appkey',
  `app_interest` VARCHAR(100) DEFAULT '[]' COMMENT '兴趣定向',
  `pay_ability` TINYINT(1) DEFAULT '0' COMMENT '付费能力',
  `game_frequency` TINYINT(1) DEFAULT '0' COMMENT '游戏频率',
  `create_time` INT(10) DEFAULT '0' COMMENT '创建时间',
  `update_time` INT(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_imei_appkey` (`imei`,`appkey`)
) ENGINE=INNODB DEFAULT CHARSET=utf8
#################20170623 housead 定向包开发. hunter.fang end#################



#################20170707 housead 线上版本优化. hunter.fang start#################

alter table `mobgi_housead`.`delivery_ad_conf_list` 
   add column `upload_info` varchar(1000) DEFAULT '[]' NULL COMMENT '上传包信息' after `originality_type`;

alter table `mobgi_housead`.`delivery_ad_conf_list` 
   add column `deeplink` varchar(512) DEFAULT '' NULL COMMENT 'deeplink' after `upload_info`;

alter table `mobgi_housead`.`delivery_ad_conf_list` 
   change `ad_target` `ad_target` varchar(1000) character set utf8 collate utf8_general_ci default '' NOT NULL comment '广告目标地址';

alter table `mobgi_housead`.`delivery_ad_conf_list` 
   add column `frequency_type` varchar(20) DEFAULT '' NULL COMMENT '频控维度:ad广告 originality创意' after `deeplink`, 
   add column `frequency` int(4) DEFAULT '0' NULL COMMENT '频控次数' after `frequency_type`;
#################20170707 housead 线上版本优化. hunter.fang end#################


#################20170623 架构的调整 start#################
alter table `mobgi_api`.`ad_app` 
   change `app_name` `app_name` varchar(100) character set utf8 collate utf8_general_ci default '' NOT NULL comment '应用名称', 
   change `appkey` `app_key` varchar(32) character set utf8 collate utf8_general_ci default '' NOT NULL comment '应用key', 
   change `packagename` `package_name` varchar(100) character set utf8 collate utf8_general_ci default '' NOT NULL comment '包名', 
   change `app_desc` `app_desc` varchar(200) character set utf8 collate utf8_general_ci default '' NOT NULL comment '应用描述', 
   change `state` `state` tinyint(2) default '0' NOT NULL comment '状态1为开启,0为关闭', 
   change `dev_id` `dev_id` int(11) default '0' NOT NULL comment '所属开发者id', 
   change `createdate` `create_time` int(11) default '0' NOT NULL, 
   change `updatedate` `update_time` int(11) default '0' NOT NULL, 
   change `icon` `icon` varchar(100) character set utf8 collate utf8_general_ci default '' NOT NULL, 
   change `keyword` `keyword` varchar(100) character set utf8 collate utf8_general_ci default '' NULL  comment '关键字', 
   change `check_msg` `check_msg` varchar(100) character set utf8 collate utf8_general_ci default '' NULL  comment '审批意见', 
   change `apk_url` `apk_url` varchar(200) character set utf8 collate utf8_general_ci default '' NULL,
   change `ischeck` `is_check` tinyint(2) default '2' NOT NULL comment '-1-未通过 1-通过,2为申请中,3为编辑后再申请';
   
   
   alter table `mobgi_api`.`ad_dever_pos` 
   change `pos_key` `pos_key_type` varchar(30) character set utf8 collate utf8_general_ci NOT NULL comment '广告位类型', 
   change `dever_pos_key` `dever_pos_key` varchar(64) character set utf8 collate utf8_general_ci default '' NOT NULL comment '广告位KEY', 
   change `dever_pos_name` `dever_pos_name` varchar(100) character set utf8 collate utf8_general_ci default '' NOT NULL comment '广告位名称', 
   change `state` `state` tinyint(1) default '1' NOT NULL comment '1开启,0为关闭 默认为开启', 
   change `app_id` `app_id` int(11) default '0' NOT NULL comment '应用appid', 
   change `dev_id` `dev_id` int(11) default '0' NOT NULL comment '开发者id', 
   change `pos_desc` `pos_desc` varchar(50) character set utf8 collate utf8_general_ci default '' NOT NULL comment '广告位描述', 
   change `createdate` `create_time` int(11) default '0' NOT NULL, 
   change `updatetime` `update_time` int(11) default '0' NOT NULL;
   
   alter table `mobgi_api`.`intergration_policy_area_conf` 
   drop column `policy_conf_id`,
   add column `show_time` int(11) DEFAULT '3600' NOT NULL ,
   add column `platform` tinyint(2) DEFAULT '0' NOT NULL COMMENT '平台1安卓2ios' ,
   change `app_key` `app_key` varchar(50) character set utf8 collate utf8_general_ci default '' NOT NULL comment '应用的app_key', 
   change `intergration_type` `intergration_type` tinyint(3) default '0' NOT NULL comment '聚合类型', 
   change `ads_list_conf` `ads_list_conf` text character set utf8 collate utf8_general_ci NOT NULL comment '广告商列表', 
   change `createtime` `create_time` int(11) default '0' NOT NULL, 
   change `updatetime` `update_time` int(11) default '0' NOT NULL, 
   change `area_ids` `area_ids` text character set utf8 collate utf8_general_ci NOT NULL comment '区域列表', 
   change `priority_ads_conf` `priority_ads_conf` text character set utf8 collate utf8_general_ci NOT NULL comment '优先显示广告商的信息';

alter table `mobgi_api`.`intergration_condition_fiter_conf` drop index `idx_conf_id`;

alter table `mobgi_api`.`intergration_condition_fiter_conf` 
    drop column `type`,
    drop column  `policy_id`,
    drop column  `conf_id`,
    add column `platform` tinyint(2) DEFAULT '1' NOT NULL, comment '1安卓２ios', 
   change `id` `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
   change `app_name` `app_name` varchar(30) character set utf8 collate utf8_general_ci default '' NULL  comment '应用名称', 
   change `app_key` `app_key` varchar(30) character set utf8 collate utf8_general_ci default '' NOT NULL comment '应用appkey', 
   change `fiter_conf` `fiter_conf` text character set utf8 collate utf8_general_ci NULL  comment '过滤配置 key-value形式 channel_conf渠道过滤province_conf省份,user_conf用户定向game_version_conf游戏版本', 
   change `createtime` `create_time` int(11) default '0' NOT NULL, 
   change `updatetime` `update_time` int(11) default '0' NOT NULL, 
   change `conf_desc` `conf_desc` varchar(500) character set utf8 collate utf8_general_ci default '' NOT NULL comment '定向配置描述', 
   change `ads_params` `ads_params` text character set utf8 collate utf8_general_ci NOT NULL, 
   change `ads_positon` `ads_positon` text character set utf8 collate utf8_general_ci NOT NULL, 
   change `policy_area_id` `policy_area_id` int(11) default '0' NOT NULL;
   
   
CREATE TABLE `mobgi_api`.`ads_list` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `ads_id` varchar(50) NOT NULL DEFAULT '' COMMENT '视频广告商编号',
   `name` varchar(50) NOT NULL DEFAULT '' COMMENT '视频广告商名称',
   `ad_sub_type` varchar(50) NOT NULL DEFAULT '' COMMENT '支持广告类型 1视频广告2插图广告 3自定义，4开屏 5原生流式',
   `out_url` varchar(100) NOT NULL DEFAULT '' COMMENT '外部的跳转的url',
   `ad_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '聚合类型：1聚合广告商2渠道广告商3DSP广告商',
   `settlement_method` tinyint(2) NOT NULL DEFAULT '1' COMMENT '结算方式,1cpm,2cpc',
   `settlement_price` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '结算单价',
   `del` int(11) NOT NULL DEFAULT '1' COMMENT '软删除 0正常 1删除',
   `is_foreign` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否国外0国内1国外',
   `create_time` int(11) DEFAULT NULL,
   `update_time` int(11) DEFAULT NULL,
   `is_bid` enum('1','0') NOT NULL DEFAULT '1' COMMENT '是否实时竞价 1 是 0否',
   `interface_url` varchar(100) DEFAULT NULL COMMENT '接口地址',
   PRIMARY KEY (`id`),
   UNIQUE KEY `uk_ads_id` (`ads_id`,`del`)
 ) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8;
 
insert into mobgi_api.ads_list (id, ads_id, name, ad_sub_type, out_url, ad_type, settlement_method, settlement_price, del, is_foreign, create_time, update_time, is_bid, interface_url)
select 	id, identifier, name, intergration_sub_type, out_url, intergration_type, settlement_method, settlement_price, 0 AS del, is_foreign, createtime, updatetime, is_bid, interface_url	 from mobgi_api.video_ads_com ；

update `mobgi_api`.intergration_policy_area_conf inner join ad_app on `mobgi_api`.intergration_policy_area_conf.app_key = `mobgi_api`.ad_app.app_key 
set `mobgi_api`.intergration_policy_area_conf.platform = `mobgi_api`.ad_app.platform;
	
	
alter table `mobgi_api`.`channel` 
   change `id` `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
   change `channel_id` `channel_id` varchar(64) character set utf8 collate utf8_general_ci default '' NOT NULL, 
   change `channel_name` `channel_name` varchar(64) character set utf8 collate utf8_general_ci default '' NOT NULL, 
   change `createtime` `create_time` int(11) default '0' NOT NULL, 
   change `updatetime` `update_time` int(11) default '0' NOT NULL;
   
 insert into `mobgi_api`.`channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('﻿1','百度','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('2','华为','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('3','小米','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('4','联想','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('5','阿里云','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('6','oppo','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('7','金立','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('8','360','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('9','酷派','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('10','豌豆荚','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('11','安智','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('12','魅族','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('13','4399','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('14','UC','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('15','三星','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('16','2345','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('17','酷狗','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('18','力天保利','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('19','卓游','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('20','美图','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('21','掌星','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('22','掌星立意','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('23','连尚','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('24','TCL','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('25','应用汇','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('26','拇指玩','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('27','掌越','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('28','云雀','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('29','走马','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('30','邻动','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('31','聚乐','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('32','锤子','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('33','7k','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('34','努比亚','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('35','酷比','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('36','安锋网','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('37','益玩','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('38','乐泾达','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('39','酷我','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('40','3533','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('41','宝软','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('42','福建风灵','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('43','迅瑞','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('44','游戏狗','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('45','移卓','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('46','翱海科技','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('47','腾讯','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('48','西西软件','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('49','vivo','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('50','PPTV','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('51','乐视','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('52','青柠','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('53','搜狗','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('54','搜狐','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('55','新浪','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('56','优酷','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('57','其它','0','1478080757','1478080757');
insert into `channel` (`channel_id`, `channel_name`, `group_id`, `create_time`, `update_time`) values('58','金立奥软','0','1478080757','1478080757');



CREATE TABLE `mobgi_api`.`ads_app_rel` (
   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
   `app_name` varchar(30) NOT NULL DEFAULT '' COMMENT '应用名称',
   `app_key` varchar(30) NOT NULL DEFAULT '' COMMENT '应用appkey',
   `platform` tinyint(2) NOT NULL DEFAULT '1' COMMENT '平台1安卓2ios',
   `create_time` int(11) NOT NULL DEFAULT '0',
   `update_time` int(11) NOT NULL DEFAULT '0',
   `ad_sub_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '1视频广告,2插图广告,3,自定义，4开屏广告,5原生信息流',
   `ads_id` varchar(30) NOT NULL DEFAULT '' COMMENT '广告商id',
   `third_party_app_key` varchar(300) NOT NULL DEFAULT '' COMMENT '第三方的appkey',
   `third_party_secret` varchar(300) NOT NULL DEFAULT '' COMMENT '第三方的密钥',
   `third_party_report_id` varchar(300) NOT NULL DEFAULT '' COMMENT '第三方报表id',
   `play_network` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0wifi下，1全网',
   `life_cycle` int(11) NOT NULL DEFAULT '0' COMMENT '生命周期，单位秒',
   `is_show_view` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否显示悬浮窗口 1显示，0不显示',
   `show_view_time` int(11) NOT NULL DEFAULT '0' COMMENT '显示悬浮窗口的时间 单位为秒',
   PRIMARY KEY (`id`),
   KEY `idx_app_key` (`app_key`,`ad_sub_type`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `mobgi_api`.`ads_pos_rel` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `app_name` varchar(50) NOT NULL DEFAULT '' COMMENT '应用名',
   `platform` tinyint(2) NOT NULL DEFAULT '1' COMMENT '平台1安卓2ios',
   `app_key` varchar(30) NOT NULL DEFAULT '' COMMENT '应用',
   `create_time` int(11) NOT NULL DEFAULT '0',
   `update_time` int(11) NOT NULL DEFAULT '0',
   `pos_id` int(11) NOT NULL DEFAULT '0',
   `ad_sub_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '1视频广告,2插图广告,3,自定义，4开屏广告,5原生信息流',
   `pos_key` varchar(64) NOT NULL DEFAULT '' COMMENT '广告位key',
   `ads_id` varchar(20) NOT NULL DEFAULT '' COMMENT '广告商',
   `third_party_block_id` varchar(300) NOT NULL DEFAULT '' COMMENT '第三方blockid',
   `third_party_report_id` varchar(300) NOT NULL DEFAULT '',
   PRIMARY KEY (`id`),
   KEY `idx_app_key` (`app_key`,`ad_sub_type`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

alter table `mobgi_api`.`video_ads`        
 change `name` `name` varchar(30) character set utf8 collate utf8_general_ci NULL  comment '配置名称',     
 change `conf_desc` `conf_desc` varchar(30) character set utf8 collate utf8_general_ci NULL  comment '描述',  
 change `app_key` `app_key` varchar(30) NULL  comment '应用标识',     
 change `video_ads_com_conf` `video_ads_com_conf` varchar(2000) NULL  comment '广告商配置';

 /**下面这些不要执行，用于导入数据**/
//导入用户
insert into mobgi_admin.admin_user 
	(user_id, 
	user_name, 
	password, 
	email, 
	mobile, 
	card_id, 
	operator, 
	`from`, 
	tel, 
	address, 
	is_check, 
	is_active, 
	check_msg, 
	register_type, 
	contact, 
	group_id, 
	user_type, 
	is_lock
	)
select
  dev_id,
  user_name,
  password,
  email,
  mobile,
  card_id,
  operator,
  `from`,
  tel,
  address,
  ischeck,
  isactive,
  check_msg,
  user_type,
  contact,
  0 as group_id,
  3 as user_type,
  0 as is_lock
from mobgi_api_old.ad_developer;

//导入用户
insert into mobgi_admin.admin_user 
	(user_id, 
	user_name, 
	password, 
	email, 
	is_check, 
	is_active,  
	group_id, 
	user_type, 
	is_lock
	)
select 	advertiser_uid, 
	advertiser_name, 
	password, 
	email,
	1 as is_check,
	1 as is_active,
    11 as group_id,
	2 as user_type,
	0 as is_lock		 
	from 
	mobgi_housead.advertiser_user where advertiser_uid < 12;

／／导入用户的应用
insert into admin_user_app_rel (user_id,app_key)
select dev_id, app_key from mobgi_api.ad_app where is_check = 1;




#################20170727 housead-cpa开发. hunter.fang start#################
alter table `mobgi_housead_stat`.`stat_day` 
   add column `actives` int(11) UNSIGNED DEFAULT '0' NULL COMMENT '激活次数' after `views`,
   change `views` `views` int(11) UNSIGNED default '0' NULL  comment '展示次数';

alter table `mobgi_housead_stat`.`stat_minute` 
   add column `actives` int(10) UNSIGNED DEFAULT '0' NULL COMMENT '激活次数' after `views`;

alter table `mobgi_housead_stat`.`report_base` 
   add column `active` int(11) DEFAULT '0' NULL COMMENT '激活' after `install_app_ok`;


use mobgi_housead_stat;

CREATE TABLE `active` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `request_id` varchar(40) DEFAULT NULL COMMENT '请求id',
  `unit_id` int(10) DEFAULT NULL COMMENT '创意id',
  `ad_id` int(10) DEFAULT NULL COMMENT '广告id',
  `originality_id` varchar(30) DEFAULT NULL COMMENT '创意id',
  `device_id` varchar(40) DEFAULT NULL COMMENT '设备id ios:idfa android:imei',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `create_date` date DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_id` (`request_id`),
  UNIQUE KEY `originalityid_deviceid` (`originality_id`,`device_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

#################20170727 housead-cpa开发. hunter.fang end#################


#################20170803 白名单二期开发. hunter.fang start#################
use mobgi_api;
CREATE TABLE `intergration_ads_conf_whitelist` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `platform` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '平台1安卓2ios',
  `intergration_type` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '1视频广告,2插图广告,3,自定义，4开屏广告,5原生信息流',
  `ads_conf` TEXT COMMENT '广告位配置',
  `createtime` INT(11) DEFAULT NULL,
  `updatetime` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
#################20170803 白名单二期开发. hunter.fang end#################

#################20170824 白名单移植  hunter.fang start#################

use mobgi_api;
CREATE TABLE `whitelist_video_ads_stat` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `app_version` varchar(100) DEFAULT NULL COMMENT '宿主应用版本',
  `version` varchar(100) DEFAULT NULL COMMENT '广告SDK版本',
  `consumerkey` varchar(100) DEFAULT NULL COMMENT '宿主应用AppKey',
  `cid` varchar(100) DEFAULT NULL COMMENT '内部渠道ID',
  `eventtype` int(11) DEFAULT NULL COMMENT '事件类型',
  `server_time` int(11) NOT NULL DEFAULT '0' COMMENT '服务器请求时间',
  `uuid` varchar(255) DEFAULT NULL COMMENT '设备号uuid, (ios为idfv)',
  `sdk_version` varchar(100) DEFAULT NULL COMMENT 'Sdk版本 公司广告传入此参数',
  `imei` varchar(100) DEFAULT NULL COMMENT '手机imei',
  `client_ip` varchar(100) DEFAULT NULL COMMENT '客户端ip',
  `device_brand` varchar(100) DEFAULT NULL COMMENT '设备型号',
  `device_model` varchar(100) DEFAULT NULL COMMENT '设备类型',
  `operator` varchar(100) DEFAULT NULL COMMENT '运营商',
  `blockid` varchar(100) DEFAULT NULL COMMENT '广告位ID',
  `udid` varchar(100) DEFAULT NULL COMMENT 'udid(android udid，ios为idfa)',
  `os` varchar(100) DEFAULT NULL COMMENT 'OS 0安卓 1IOS',
  `android_id` varchar(100) DEFAULT NULL COMMENT 'android_id',
  `mober` varchar(100) DEFAULT NULL COMMENT '聚合广告平台的标签，vungle  inmobi',
  `intergration_type` int(10) NOT NULL DEFAULT '1' COMMENT '聚合类型1 视频聚合 2插页聚合',
  PRIMARY KEY (`id`),
  KEY `idx_imei` (`imei`),
  KEY `idx_udid` (`udid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#################20170824 白名单移植  hunter.fang start#################

#################20170828 adx自有原生广告  hunter.fang start#################

alter table `mobgi_api`.`ad_dever_pos` 
   add column `size` varchar(50) DEFAULT '' NOT NULL COMMENT '尺寸' after `ad_sub_type`;
alter table `mobgi_api`.`ads_pos_rel` 
   add column `state` tinyint(4) DEFAULT '1' NOT NULL COMMENT '0关闭1打开' after `third_party_report_id`;
alter table `mobgi_housead`.`delivery_ad_conf_list` 
   add column `ad_sub_type` tinyint(4) DEFAULT '0' NULL COMMENT '广告子类型51原生单图52原生组图' after `originality_type`;
alter table `mobgi_housead`.`delivery_originality_relation` 
   add column `ad_sub_type` tinyint(4) DEFAULT '0' NOT NULL COMMENT '广告子类型51原生单图52原生组图' after `originality_type`;

#################20170828 adx自有原生广告  hunter.fang start#################

###################20170905后台功能的优化开发##########################
alter table `mobgi_admin`.`admin_user` 
   add column `is_admin` tinyint(2) DEFAULT '0' NOT NULL COMMENT '是否为管理员' after `is_lock`;


#################20170907 adx自有原生广告  hunter.fang start#################

alter table `mobgi_housead`.`delivery_unit_conf` 
   add column `unit_type` tinyint(3) DEFAULT '1' NOT NULL COMMENT '1外部订单2内部订单' after `status`;

#################20170907 adx自有原生广告  hunter.fang end#################

#################20170908 白名单二期  hunter.fang start#################
use mobgi_api;

CREATE TABLE `ads_app_rel_whitelist` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_name` VARCHAR(30) NOT NULL DEFAULT '' COMMENT '应用名称',
  `app_key` VARCHAR(30) NOT NULL DEFAULT '' COMMENT '应用appkey',
  `platform` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '平台1安卓2ios',
  `create_time` INT(11) NOT NULL DEFAULT '0',
  `update_time` INT(11) NOT NULL DEFAULT '0',
  `ad_sub_type` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '1视频广告,2插图广告,3,自定义，4开屏广告,5原生信息流',
  `ads_id` VARCHAR(30) NOT NULL DEFAULT '' COMMENT '广告商id',
  `third_party_app_key` VARCHAR(300) NOT NULL DEFAULT '' COMMENT '第三方的appkey',
  `third_party_secret` VARCHAR(300) NOT NULL DEFAULT '' COMMENT '第三方的密钥',
  `third_party_report_id` VARCHAR(300) NOT NULL DEFAULT '' COMMENT '第三方报表id',
  `play_network` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '0wifi下，1全网',
  `life_cycle` INT(11) NOT NULL DEFAULT '0' COMMENT '生命周期，单位秒',
  `is_show_view` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '是否显示悬浮窗口 1显示，0不显示',
  `show_view_time` INT(11) NOT NULL DEFAULT '0' COMMENT '显示悬浮窗口的时间 单位为秒',
  PRIMARY KEY (`id`),
  KEY `idx_app_key` (`app_key`,`ad_sub_type`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `ads_pos_rel_whitelist` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `app_name` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '应用名',
  `platform` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '平台1安卓2ios',
  `app_key` VARCHAR(30) NOT NULL DEFAULT '' COMMENT '应用',
  `create_time` INT(11) NOT NULL DEFAULT '0',
  `update_time` INT(11) NOT NULL DEFAULT '0',
  `pos_id` INT(11) NOT NULL DEFAULT '0',
  `ad_sub_type` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '1视频广告,2插图广告,3,自定义，4开屏广告,5原生信息流',
  `pos_key` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '广告位key',
  `ads_id` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '广告商',
  `third_party_block_id` VARCHAR(300) NOT NULL DEFAULT '' COMMENT '第三方blockid',
  `third_party_report_id` VARCHAR(300) NOT NULL DEFAULT '',
  `state` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '0关闭1打开',
  PRIMARY KEY (`id`),
  KEY `idx_app_key` (`app_key`,`ad_sub_type`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#################20170908 白名单二期  hunter.fang end#################

####################广告目标类型修改ｓｔａｒｔ＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃
update `mobgi_housead`.`delivery_ad_conf_list`  set ad_target_type = 2  where ad_target_type = 4;

#################20170924 落地页模板一期  hunter.fang end#################
use mobgi_housead;
CREATE TABLE `advertiser_landingpage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `app_id` int(10) DEFAULT NULL COMMENT '关联应用id',
  `title` varchar(100) DEFAULT NULL COMMENT '标题',
  `url` varchar(200) DEFAULT NULL COMMENT '链接地址',
  `status` tinyint(1) DEFAULT NULL COMMENT '1未发布2已发布',
  `template_type` tinyint(1) DEFAULT NULL COMMENT '1动态模板2静态模板',
  `template_id` varchar(100) DEFAULT NULL COMMENT '使用模板id',
  `template_url` varchar(200) DEFAULT '' COMMENT '模板地址',
  `template_data` text COMMENT '模板参数',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

CREATE TABLE `advertiser_landingpagetemplate` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` VARCHAR(100) DEFAULT NULL COMMENT '模板名称',
  `zip` VARCHAR(100) DEFAULT '' COMMENT '压缩包地址',
  `url` VARCHAR(100) DEFAULT NULL COMMENT '模板地址',
  `type` TINYINT(1) DEFAULT NULL COMMENT '模板类型1动态2静态',
  `app_id` INT(10) DEFAULT NULL COMMENT '关联应用id',
  `create_time` INT(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` INT(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8

#################20170924 落地页模板一期  hunter.fang end#################


#################20170927 adx白名单数据落地  hunter.fang end#################
use bh_adx_stats;
CREATE TABLE `ad_client_whitelist` (
  `id` BIGINT(20) NOT NULL,
  `ssp_id` INT(10) DEFAULT '0',
  `ads_id` VARCHAR(32) DEFAULT '-1',
  `orig_id` INT(10) DEFAULT '0' COMMENT '创意ID',
  `bit_id` CHAR(32) DEFAULT '-1' COMMENT '请求id',
  `app_key` CHAR(20) DEFAULT '-1' COMMENT '应用的appkey',
  `pos_key` VARCHAR(64) DEFAULT '-1' COMMENT '广告位',
  `ad_type` TINYINT(2) DEFAULT '0' COMMENT '广告类型 1插页,2视频,3自定义',
  `ad_sub_type` TINYINT(2) DEFAULT '0' COMMENT '广告子类型',
  `cid` CHAR(11) DEFAULT '-1' COMMENT '渠道号',
  `brand` VARCHAR(32) DEFAULT '-1' COMMENT '设备型号',
  `model` VARCHAR(32) DEFAULT '-1' COMMENT '设备类型',
  `operator` TINYINT(2) DEFAULT '0' COMMENT '运营商1:联通,2:电信,3:移动,4:其他',
  `net_type` TINYINT(2) DEFAULT '0' COMMENT '网络类型1:wifi,2:2G,3:3G,4:4G,5:5G',
  `event_type` INT(6) DEFAULT '0' COMMENT '事件类型',
  `event_value` INT(10) DEFAULT '0' COMMENT '事件参数',
  `imei` CHAR(64) DEFAULT '-1' COMMENT '国际移动设备标识码(idfa)',
  `imsi` BIGINT(15) DEFAULT '0' COMMENT '国际移动用户识别码',
  `platform` TINYINT(2) DEFAULT '0' COMMENT '平台,1:安卓,2:ios',
  `uuid` VARCHAR(64) DEFAULT '-1' COMMENT '用户标识',
  `app_version` VARCHAR(15) DEFAULT '-1' COMMENT '应用版本',
  `sdk_version` VARCHAR(15) DEFAULT '-1' COMMENT 'Sdk版本',
  `client_ip` VARCHAR(15) DEFAULT '-1' COMMENT '客户端ip',
  `server_time` INT(10) DEFAULT '0' COMMENT '服务器时间',
  `charge_type` TINYINT(2) DEFAULT '0' COMMENT '计费类型',
  `currency` TINYINT(2) DEFAULT '0' COMMENT '货币类型',
  `price` DECIMAL(10,4) DEFAULT '0.0000' COMMENT '计费单价',
  `vh` TINYINT(1) DEFAULT '0' COMMENT '横竖屏',
  `point_x` INT(6) DEFAULT '-1' COMMENT 'x坐标',
  `point_y` INT(6) DEFAULT '-1' COMMENT 'y坐标',
  `width` INT(6) DEFAULT '0' COMMENT '分辨率-宽',
  `height` INT(6) DEFAULT '0' COMMENT '分辨率-高',
  `ver` INT(6) DEFAULT '0' COMMENT '数据版本'
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COMMENT='白名单设备数据上报表';
#################20170927 adx白名单数据落地  hunter.fang end#################



＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃dsp的开发＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃
alter table `mobgi_api`.`ads_list` 
   change `del` `del` int(11) default '0' NOT NULL comment '软删除 0正常 1删除';

＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃dsp的开发end＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃

＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃流量分配的开发ｓｔａｒｔ２０１７１０１８＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃＃
alter table `mobgi_api`.`ad_app` 
   add column `out_game_id` int(10) DEFAULT '0' NOT NULL COMMENT '外部的游戏ID';

#################20171107 配置验证工具二期  hunter.fang end#################
#新增字段：渠道是否使用到配置验证工具
alter table `mobgi_api`.`channel` 
   add column `is_check_config` tinyint(4) DEFAULT '0' NOT NULL COMMENT '0:不检测1:检测' after `is_custom`;
UPDATE channel SET is_check_config=1 WHERE channel_id IN('OP0S0N02002',
'CURRENT00000','XM0S0N00002','NT0S0N00002','XO0S0N00001','MZ0S0N00001','SJ0S0N50504','SJ0S0N50001','SX0S0N20030','ZC0S0N00002','JL0S0N10019','LS0S0N00003','SG0S0N10007','JL0S0N00018',
'LX0S0N20001','ES0S0N00003','BD0S0N50004','TX0S0N70000','YH0S0N50001','WD0S0N00002','AZ0S0N00004','HX0S0N20043','LB0S0N20003','TX0S0N20066','PS0S0N00005','XO0S0N10001','KP0S0N00008','SG0S0N10005',
'ZXOSON10001','KB0S0N30001','TD0S0N02002','MT0S0N20005','QN0S0N00002','NB0S0N00370','JL0S0N00004','XX0S0N00001','CZ0S0N02002','VK0S0N00000','LE0S0N00000','VK0S0N00001','YK0S0N10002','QC0S0N01001',
'DW0S0N20000','MG0S0N70002');
#################20171107 配置验证工具二期  hunter.fang end#################


#################20171110 动态模板上传  hunter.fang end#################
ALTER TABLE `mobgi_housead`.`advertiser_landingpagetemplate` 
   ADD COLUMN `create_name` VARCHAR(100) DEFAULT '' NULL COMMENT '创建模板名称' AFTER `url`, 
   ADD COLUMN `create_zip` VARCHAR(100) DEFAULT '' NULL COMMENT '压缩包地址' AFTER `create_name`, 
   ADD COLUMN `create_url` VARCHAR(100) DEFAULT '' NULL COMMENT '创建模板地址' AFTER `create_zip`,
   CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' NULL  COMMENT '模板名称', 
   CHANGE `zip` `zip` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' NULL  COMMENT '压缩包地址', 
   CHANGE `url` `url` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' NULL  COMMENT '模板地址';
#################20171110 配置验证工具二期  hunter.fang end#################



#######################20171116业务的应用分类start#################################
alter table `mobgi_api`.`ad_app` 
add column`app_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '应用自定义类型1休闲游戏2独立游戏3联盟流量';
#######################20171116业务的应用分类end#################################

#######################20171117支持客户端分级加载start#################################
alter table `mobgi_api`.`ads_pos_rel` 
   add column `position` tinyint(2) DEFAULT '1' NOT NULL COMMENT '位置' after `ads_id`;  
 alter table `mobgi_api`.`ads_pos_rel_whitelist` 
   add column `position` tinyint(2) DEFAULT '1' NOT NULL COMMENT '位置' after `ads_id`;  
alter table `mobgi_api`.`flow_pos_rel` 
   add column `position` tinyint(2) DEFAULT '1' NOT NULL COMMENT '位置' after `ads_id`;   
alter table `mobgi_api`.`flow_conf` 
   add column `sys_conf_type` tinyint DEFAULT '0' NOT NULL COMMENT '系统版本的类型 0全部1 定向' after `game_conf`, 
   add column `sys_conf` varchar(500) NULL COMMENT '系统版本配置' after `sys_conf_type`;
   
   CREATE TABLE `template` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '模板id',
   `ad_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '模板类型',
   `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
   `url` varchar(200) NOT NULL DEFAULT '' COMMENT '模板url',
   `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
 
 alter table `mobgi_api`.`ads_app_rel` 
  add column`is_use_template` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否使用模板',
  add column `template_show_time` int(10) NOT NULL DEFAULT '3' COMMENT '模板展示时间',
   add column`template_id` int(10) DEFAULT '0' NOT NULL COMMENT '模板id';
   
   
   


#################20171113 试玩广告一期  hunter.fang end#################
#增加试玩类型的注释
alter table `mobgi_housead`.`delivery_ad_conf_list` 
   change `originality_type` `originality_type` tinyint(3) default '0' NOT NULL comment '类型1视频广告2插屏-全屏3交叉推广4开屏5原生6试玩', 
   change `ad_sub_type` `ad_sub_type` tinyint(4) default '0' NULL  comment '广告子类型51原生单图52原生组图61悬浮窗62下一送一63公告64插屏65角标66激励试玩67试玩墙';

#新增试玩包表
CREATE TABLE `advertiser_trial_package` (
  `id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `app_name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '应用名称',
  `app_key` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '应用appkey',
  `package_name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '试玩包名称',
  `package_url` VARCHAR(300) NOT NULL DEFAULT '' COMMENT '试玩包地址',
  `package_version` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '试玩包版本',
  `package_size` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '试玩包大小',
  `upload_info` VARCHAR(1000) DEFAULT '[]' COMMENT '上传包信息',
  `upload_time` INT(10) NOT NULL DEFAULT '0' COMMENT '上传时间',
  `create_time` INT(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` INT(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

alter table `mobgi_housead`.`delivery_originality_relation` 
   add column `trial_package_id` int(10) DEFAULT '0' NULL COMMENT '试玩包id' after `outer_originality_id`, 
   add column `trial_ad_target_type` int(10) DEFAULT '0' NULL COMMENT '试玩推广目标' after `trial_package_id`, 
   add column `entry` varchar(100) DEFAULT '' NULL COMMENT '入口' after `trial_ad_target_type`, 
   add column `installation_hint` varchar(100) DEFAULT '' NULL COMMENT '安装提示' after `entry`, 
   add column `float_view` tinyint(3) DEFAULT '0' NULL COMMENT '悬浮球1显示２不显示' after `installation_hint`, 
   add column `shortcut` tinyint(2) DEFAULT '0' NULL COMMENT '桌面快捷方式1显示2不显示' after `float_view`, 
   add column `shortcut_name` varchar(100) DEFAULT '' NULL COMMENT '桌面快捷名称' after `shortcut`;

ALTER TABLE `delivery_ad_conf_list` ADD COLUMN   `trial_package_id` INT(10) DEFAULT '0' COMMENT '试玩包id';
alter table `mobgi_housead`.`advertiser_trial_package` 
   add column `name` varchar(100) DEFAULT '' NOT NULL COMMENT '试玩包名称' after `app_key`,
   change `package_name` `package_name` varchar(100) character set utf8 collate utf8_general_ci default '' NOT NULL comment '试玩包包名';
ALTER TABLE `advertiser_trial_package` ADD UNIQUE KEY `appkey_name`(`app_key`, `name`);

alter table `mobgi_housead`.`advertiser_trial_package` 
   add column `md5file` varchar(50) character set utf8 collate utf8_general_ci default '' NULL  comment '文件校验码';

CREATE TABLE `advertiser_trial_flow_conf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conf_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '配置类型 1默认配置，2定向配置',
  `conf_name` varchar(100) NOT NULL DEFAULT '' COMMENT '配置名称',
  `app_key` varchar(30) NOT NULL DEFAULT '' COMMENT '应用名称',
  `game_conf_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '游戏版本的类型 0全部1 定向',
  `game_conf` varchar(500) DEFAULT '[]' COMMENT '游戏版本配置',
  `isopen` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否开启:0关闭1开启',
  `operator_id` int(10) NOT NULL DEFAULT '0' COMMENT '操作人',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `del` tinyint(2) NOT NULL DEFAULT '0' COMMENT '删除标志，0未删除1删除',
  PRIMARY KEY (`id`),
  KEY `idx_app_key` (`app_key`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
#################20171113 试玩广告一期  hunter.fang end#################

#################20171211 广点通luna api hunter.fang end#################
use mobgi_admin;
CREATE TABLE `admin_user_gdt` (
  `user_id` int(10) NOT NULL COMMENT 'admin_user表的user_id',
  `state` enum('on','off') DEFAULT 'off' COMMENT 'on开,off关',
  `advertiser_id` int(10) DEFAULT NULL COMMENT '广点通广告主id',
  `app_id` int(10) DEFAULT NULL COMMENT '广点通app_id',
  `app_key` varchar(50) DEFAULT NULL COMMENT '广点通私钥',
  `plan_id` int(10) DEFAULT NULL COMMENT '广点通推广计划id',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='广点通直投帐户表';

use mobgi_admin;
CREATE TABLE `admin_user_gdt_auth` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `client_id` INT(10) UNSIGNED NOT NULL COMMENT '应用id,开发者创建应用后获得,小于4294967295',
  `client_secret` VARCHAR(256) DEFAULT '' COMMENT '应用secret，应用被审核通过后获得',
  `app_name` VARCHAR(64) DEFAULT '' COMMENT '应用名称',
  `account_id` INT(10) UNSIGNED NOT NULL COMMENT '广点通账户id',
  `authorization_code` VARCHAR(64) DEFAULT '' COMMENT 'Oauth2认证Code',
  `code_time` INT(10) DEFAULT '0' COMMENT 'code获取时间',
  `access_token` VARCHAR(256) DEFAULT '' COMMENT '认证token',
  `token_time` INT(10) DEFAULT '0' COMMENT 'token获取时间',
  `refresh_token` VARCHAR(256) DEFAULT '' COMMENT '刷新token',
  `access_token_expires_in` INT(10) UNSIGNED NOT NULL COMMENT 'access_token生命周期',
  `refresh_token_expires_in` INT(10) UNSIGNED NOT NULL COMMENT 'refresh_token生命周期',
  `operator` VARCHAR(32) DEFAULT NULL COMMENT '操作者',
  `create_time` INT(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` INT(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_id` (`client_id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='广点通oauth2.0验证';

insert  into `admin_user_gdt_auth`(`id`,`client_id`,`client_secret`,`app_name`,`account_id`,`authorization_code`,`code_time`,`access_token`,`token_time`,`refresh_token`,`access_token_expires_in`,`refresh_token_expires_in`,`operator`,`create_time`,`update_time`) values (1,1106075364,'bLesQJjhrS3ObOy5','乐逗游戏',1237127,'2c42a702b2f1c9b480a84fda5968081a',1507705701,'4a2414098e2104c9f8e749c9f33a830a',1507705720,'daf52fb64c4e14bbe18c6300b7ce00e1',86400,63072000,'kyle.ke',1490696083,1507705720);

-- alter table `mobgi_admin`.`admin_user_gdt_auth` 
--    change `app_id` `client_id` int(10) UNSIGNED NOT NULL comment '应用id,开发者创建应用后获得,小于4294967295', 
--    change `app_key` `client_secret` varchar(256) character set utf8 collate utf8_general_ci default '' NULL  comment '应用secret，应用被审核通过后获得';

#################20171211 广点通luna api hunter.fang end#################

 alter table `mobgi_api`.`ads_app_rel_whitelist` 
  add column`is_use_template` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否使用模板',
  add column `template_show_time` int(10) NOT NULL DEFAULT '3' COMMENT '模板展示时间',
  add column`template_id` int(10) DEFAULT '0' NOT NULL COMMENT '模板id';



#################20170328 广告增加限额stat#################

alter table `mobgi_housead`.`delivery_ad_conf_list` 
   add column `ad_limit_type` tinyint(2) DEFAULT '0' NOT NULL COMMENT '广告限制类型 0无限1限制' after `frequency`, 
   add column `ad_limit_amount` int(10) DEFAULT '0' NOT NULL COMMENT '限制的金额' after `ad_limit_type`;

#################20170328 广告增加限额end#################

#################2018041１ 互动广告stat#################
CREATE TABLE `mobgi_api`.`interactive_ad_conf` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `conf_rel_id` int(11) DEFAULT NULL COMMENT '配置关联id',
   `conf_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0全局配置1安卓配置,2ios配置',
   `ads_id` varchar(20) NOT NULL DEFAULT '' COMMENT '广告商',
   `status` tinyint(1) DEFAULT '1' COMMENT '状态1开启0关闭',
   `template_id` int(10) NOT NULL DEFAULT '0' COMMENT '模板id',
   `url` varchar(500) NOT NULL DEFAULT '' COMMENT '活动链接',
   `weight` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT '权重',
   `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   `operator` varchar(30) NOT NULL COMMENT '操作人',
   PRIMARY KEY (`id`),
   KEY `idx_conf_rel_id` (`conf_rel_id`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
 
 
CREATE TABLE `mobgi_api`.`interactive_ad_conf_rel` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `app_key` varchar(20) NOT NULL DEFAULT '' COMMENT '应用key',
   `pos_key` varchar(32) NOT NULL DEFAULT '' COMMENT '广告位key',
   `operator` varchar(30) NOT NULL COMMENT '操作人',
   `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   PRIMARY KEY (`id`),
   KEY `idx_app_key_pos_key` (`app_key`,`pos_key`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
 
 CREATE TABLE `mobgi_api`.`interactive_ad_template` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `template_name` varchar(10) NOT NULL DEFAULT '' COMMENT '模板名称',
   `url` varchar(200) NOT NULL DEFAULT '' COMMENT '模板地址',
   `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
 

 
#################20180411 end#################

#################20180507 活动开启#################

drop table push_config,push_data,push_harass,push_limit,push_log,push_plan,push_plan_log,push_weight;
drop table msg,rtb_blacklist,rtb_config,rtb_data,rtb_limit,rtb_plan,rtb_weight;
drop table intergration_ads;
drop table intergration_ads_bak20170510;
drop table intergration_ads_conf;
drop table intergration_ads_conf_bak20170510;
drop table intergration_condition_fiter;
drop table intergration_condition_fiter_conf;
drop table intergration_policy_area_conf;
drop table intergration_policy_area_conf_bak20170510;
drop table intergration_policy_conf;
drop table whitelist_video_ads_stat;
drop table video_ads_com_bak20170510;
drop table video_ads_com;
drop table ad_app_version,ad_condition_manage,ad_config,ad_config_details,ad_config_tags,ad_customized_info,ad_financial,ad_game_config,ad_info,ad_incentive_video_info,ad_incentive_video_limit;
drop table ad_embedded_info,ad_instatll_remind,ad_not_embedded_info,ad_order,ad_other_config,ad_pid_in_show,ad_pos,ad_product_acounting,ad_product_info,ad_product_limit,ad_publish;
drop table intergration_whitelist,intergration_policy_area_conf_test,intergration_ads_conf_whitelist;



CREATE TABLE `interactive_ad_activity` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '活动id',
   `limit_type` tinyint(2) NOT NULL COMMENT '1每天2永久',
   `limit_num` int(10) NOT NULL COMMENT '抽奖次数',
   `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
   `desc` text NOT NULL COMMENT '描述',
   `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '活动状态',
   `start_time` date NOT NULL DEFAULT '0000-00-00' COMMENT '有效时间',
   `end_time` date NOT NULL DEFAULT '0000-00-00' COMMENT '有效时间',
   `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   `del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除标志',
   `operator` varchar(20) NOT NULL DEFAULT '' COMMENT '操作人',
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


  CREATE TABLE `interactive_ad_activity_rel` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表头',
   `activity_id` int(10) NOT NULL DEFAULT '0' COMMENT '活动id',
   `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品id',
   `rate` decimal(18,2) NOT NULL DEFAULT '0.00' COMMENT '概率',
   `position` tinyint(2) NOT NULL DEFAULT '0' COMMENT '位置',
   `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   `del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除标志',
   `operator` varchar(20) NOT NULL DEFAULT '' COMMENT '操作人',
   PRIMARY KEY (`id`),
   KEY `idx_activity_id` (`activity_id`),
   KEY `idx_goods_id` (`goods_id`)
 ) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
 

 CREATE TABLE `interactive_ad_goods` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品id',
   `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '商品分类1实体，2虚拟，3优惠券4谢谢惠顾',
   `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
   `desc` text NOT NULL COMMENT '描述',
   `icon` varchar(200) NOT NULL DEFAULT '' COMMENT '商品icon',
   `big_img` varchar(500) NOT NULL DEFAULT '' COMMENT '商品详情图片',
   `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '商品状态0下架1上架',
   `stock` int(10) NOT NULL DEFAULT '0' COMMENT '库存',
   `used_num` int(11) NOT NULL DEFAULT '0' COMMENT '使用数量',
   `start_time` date NOT NULL DEFAULT '0000-00-00' COMMENT '有效时间',
   `end_time` date NOT NULL DEFAULT '0000-00-00' COMMENT '有效时间',
   `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   `del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除标志',
   `operator` varchar(20) NOT NULL DEFAULT '' COMMENT '操作人',
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

 CREATE TABLE `interactive_ad_goods_code` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '兑换码的id',
   `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '商品类型，冗余字段',
   `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
   `code` varchar(20) NOT NULL DEFAULT '' COMMENT '兑换码',
   `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '兑换码状态0生成1抽中2领取',
   `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   `del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除标志',
   `operator` varchar(20) NOT NULL DEFAULT '' COMMENT '操作人',
   PRIMARY KEY (`id`),
   KEY `idx_goods_id` (`goods_id`)
 ) ENGINE=InnoDB AUTO_INCREMENT=1262 DEFAULT CHARSET=utf8;
 
CREATE TABLE `interactive_ad_goods_exchange_log` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `user_id` varchar(32) NOT NULL DEFAULT '' COMMENT '用户id',
   `activity_id` int(10) NOT NULL DEFAULT '0' COMMENT '活动id',
   `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品id',
   `code_id` int(11) NOT NULL DEFAULT '0' COMMENT '兑换码id',
   `code` varchar(20) NOT NULL DEFAULT '' COMMENT '兑换码',
   `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态1抽中2已领取',
   `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   `operator` varchar(20) NOT NULL DEFAULT '' COMMENT '操作人',
   PRIMARY KEY (`id`),
   KEY `idx_code_id` (`code_id`),
   KEY `idx_code` (`code`),
   KEY `idx_goods_id` (`goods_id`),
   KEY `idx_user_id` (`user_id`),
   KEY `idx_activity_id` (`activity_id`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
 
 CREATE TABLE `interactive_ad_user` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表头',
   `user_id` varchar(32) NOT NULL COMMENT '用户id',
   `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   PRIMARY KEY (`id`),
   UNIQUE KEY `idx_uuid` (`user_id`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
 
 CREATE TABLE `interactive_ad_user_day_draw_times` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '表头',
   `user_id` varchar(32) NOT NULL DEFAULT '' COMMENT '用户id',
   `day` date NOT NULL COMMENT '抽奖日期',
   `activity_id` int(10) NOT NULL DEFAULT '0',
   `times` int(11) NOT NULL DEFAULT '1' COMMENT '用户抽奖次数',
   `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
   PRIMARY KEY (`id`),
   KEY `idx_uuid_date` (`user_id`,`day`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;





