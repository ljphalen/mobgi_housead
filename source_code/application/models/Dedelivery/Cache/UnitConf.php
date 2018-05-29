<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 *
 * Dedelivery_Cache_UnitConfModel
 * @author rock.luo
 *
*/
class Dedelivery_Cache_UnitConfModel extends Cache_Base{
	public $resource = 'houseadInfo';
	public $expire = 86400;
}