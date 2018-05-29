<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-9 14:22:23
 * $Id: Operatelog.php 62100 2016-9-9 14:22:23Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Admin_Dao_OperatelogModel extends Common_Dao_Base {
    public $adapter = 'mobgiAdmin';
	protected $_name = 'admin_operate_log';
	protected $_primary = 'id';
	

}

