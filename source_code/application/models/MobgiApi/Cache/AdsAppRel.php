<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * MobgiApi_Cache_AdsRelAppModel
 * @author rock.luo
 *
 */
class MobgiApi_Cache_AdsAppRelModel extends Cache_Base{
	public $resource = 'adsRelInfo';
	public $expire = 86400;
	
	
}
