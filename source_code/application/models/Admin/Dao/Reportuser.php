<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-12-12 16:22:21
 * $Id: Reportuser.php 62100 2016-12-12 16:22:21Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Admin_Dao_ReportuserModel extends Common_Dao_Base{
    protected $_name = 'advertiser_user_report';
	protected $_primary = 'advertiser_uid';
    
}

