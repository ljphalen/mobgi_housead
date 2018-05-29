<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 *
 * Dedelivery_Cache_OriginalityRelationModel
 * @author rock.luo
 *
*/
class Dedelivery_Cache_OriginalityRelationModel extends Cache_Base{
	public $resource = 'houseadInfo';
	public $expire = 86400;
}