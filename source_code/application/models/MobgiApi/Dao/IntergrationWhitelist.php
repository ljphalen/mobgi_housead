<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-7-6 10:49:30
 * $Id: IntergrationWhitelist.php 62100 2017-7-6 10:49:30Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class MobgiApi_Dao_IntergrationWhitelistModel extends Common_Dao_Base{
    public  $adapter = 'mobgiApi';
	protected $_name = 'intergration_whitelist';
	protected $_primary = 'id';
	
	
}
