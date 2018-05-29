<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-9-8 18:52:31
 * $Id: AdsAppRelWhitelist.php 62100 2017-9-8 18:52:31Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class MobgiApi_Dao_AdsAppRelWhitelistModel extends Common_Dao_Base{
    public  $adapter = 'mobgiApi';
	protected $_name = 'ads_app_rel_whitelist';
	protected $_primary = 'id';
	
	
}
