<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 *
 * Dedelivery_Cache_AdConfListModel
 * @author rock.luo
 *
*/
class Dedelivery_Cache_AdConfListModel extends Cache_Base{
	public $resource = 'houseadInfo';
	public $expire = 86400;
}