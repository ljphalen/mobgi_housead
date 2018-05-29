<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Advertiser_Service_ConfigModel{


	/**
	 * 
	 * Enter description here ...
	 */
	public static function getAll() {
		return array(self::_getDao()->count(), self::_getDao()->getAll());
	}
	
	public static function getAllConfig() {
		$ret = self::_getDao()->getAll();
		$temp = array();
		foreach($ret as $key=>$value) {
			$temp[$value['config_key']] = $value['config_value'];
		}
		return $temp;
	}
	
	public static function getValue($key) {
		$ret = self::_getDao()->getBy(array('config_key'=>$key));
		return $ret['config_value'];
	}
	
	/**
	 *
	 * @param unknown_type $key
	 * @param unknown_type $value
	 */
	public static function setValue($key, $value, $operator) {
		if (!$key) return false;
		return self::_getDao()->updateByKey($key, $value, $operator);
	}

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
	    $tmp = array();
	    if(isset($data['config_key'])) $tmp['config_key'] = intval($data['config_key']);
	    if(isset($data['config_value'])) $tmp['config_value'] = $data['config_value'];
	    if(isset($data['admin_id'])) $tmp['admin_id'] = $data['admin_id'];
	    if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
	    return $tmp;
	}
	
	/**
	 * 
	 * @return Advertiser_Dao_ConfigModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_ConfigModel");
	}
}
