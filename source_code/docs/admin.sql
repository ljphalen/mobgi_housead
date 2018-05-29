-- TableName admin_administrators 后台管理员表
-- Created By aliyun.com@2011-07-18 
-- Fields uid          用户ID 
-- Fields username     用户名
-- Fields password     用户密码
-- Fields hash         hash
-- Fields email        邮箱地址
-- Fields registertime 注册时间
-- Fields registerip   注册IP
-- Fields groupid      用户组ID
DROP TABLE IF EXISTS admin_user; 
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
	 KEY `idx_username` (`username`),
	 KEY `idx_groupid` (`groupid`)
) ENGINE=INODB DEFAULT CHARSET=utf8; 

-- TableName admin_group 后台用户组
-- Created By aliyun.com@2011-07-18 
-- Fields groupid      用户组ID
-- Fields name         用户组名称
-- Fields info         用户组描述
-- Fields createtime   创建时间
-- Fields ifdefault	      是否默认
-- Fields rvalue       权限值
DROP TABLE IF EXISTS admin_group; 
CREATE TABLE IF NOT EXISTS `admin_group` (
  `groupid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  `descrip` varchar(255) NOT NULL DEFAULT '',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0',
  `ifdefault` tinyint(10) unsigned NOT NULL DEFAULT '0',
  `rvalue` text NOT NULL,
  PRIMARY KEY (`groupid`)
) ENGINE=INODB  DEFAULT CHARSET=utf8; 

-- TableName admin_search 后台搜索
-- Created By aliyun.com@2011-07-18 
-- Fields id      自增id
-- Fields menukey 菜单key
-- Fields menuhash  菜单hash
-- Fields name  名称
-- Fields url 菜单地址
-- Fields descrip 描述信息
DROP TABLE IF EXISTS admin_search; 
CREATE TABLE IF NOT EXISTS `admin_search` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menukey` varchar(255) NOT NULL DEFAULT '',
  `menuhash` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `subname` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `descrip` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INODB DEFAULT CHARSET=utf8;
-- TableName admin_log 后台日志
-- Created By aliyun.com@2011-07-18 
-- Fields id      自增id
-- Fields uid     用户ID
-- Fields username 用户名 
-- Fields message 错误信息 
DROP TABLE IF EXISTS admin_log;
CREATE TABLE `admin_log` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`uid` int(10) NOT NULL DEFAULT 0,
	`username` varchar(255) NOT NULL DEFAULT '',
	`message` varchar(255) NOT NULL DEFAULT '',
	`ip` varchar(255) NOT NULL DEFAULT '',
	`create_time` int(10) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	KEY `idx_uid` (`uid`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

INSERT INTO `admin_user` VALUES (1, 'admin', '9349bd975b8d3db9e9b47ea136e47cd3', 'hATuhV', 'admin@aliyun.com', 0, '0', 0);
