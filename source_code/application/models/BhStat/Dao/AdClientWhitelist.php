<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-9-28 19:49:32
 * $Id: AdClientWhitelist.php 62100 2017-9-28 19:49:32Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class BhStat_Dao_AdClientWhitelistModel extends Common_Dao_Base{
    public $adapter = 'bhStat';
	protected $_name = 'ad_client_whitelist';
	protected $_primary = 'id';
	
	
}

