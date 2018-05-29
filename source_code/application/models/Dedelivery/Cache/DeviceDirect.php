<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-6-27 14:19:09
 * $Id: DeviceDirect.php 62100 2017-6-27 14:19:09Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Dedelivery_Cache_DeviceDirectModel extends Cache_Base{
	public $resource = 'houseadInfo';
	public $expire = 86400;
}
