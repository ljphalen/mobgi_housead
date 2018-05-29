<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Advertiser_Service_GroupModel{
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $groupid
	 */
	public static function getGroup($groupid) {
	    if(!$groupid) {
	        return false;
	    }
		$result = self::_getDao()->get(intval($groupid));
		if(!$result) {
		    return false;
		}
		$result['rvalue'] = json_decode($result['rvalue'], true);
		return $result;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addGroup($data) {
		if (!is_array($data)) return false;
		$data['createtime'] = Common::getTime();
		$data = self::_cookData($data);
		$ret =  self::_getDao()->insert($data);
	    if (!$ret) return $ret;
        return self::_getDao()->getLastInsertId();
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $groupid
	 */
	public static function updateGroup($data, $groupid) {
		if (!is_array($data)) return false; 
		$data = self::_cookData($data);
		return self::_getDao()->update($data, intval($groupid));
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public static function getAllGroup() {
		return array(self::_getDao()->count(), self::_getDao()->getAll());
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $page
	 * @param unknown_type $limit
	 * @param unknown_type $params
	 */
	public static function getList($page = 1, $limit = 20, $params = array()) {
		if ($page < 1) $page = 1;
		$start = ($page -1) * $limit;
		$ret = self::_getDao()->getList(intval($start), intval($limit), $params);
		$total = self::_getDao()->count($params);
		return array($total, $ret); 
	}
	
	public static function getMainLevels(){
	    return self::_getDao()->getMainLevels();
	}
	
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $groupid
	 */
	public static function deleteGroup($groupid) {
		return self::_getDao()->delete(intval($groupid));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
		$tmp = array();
		if(isset($data['name'])) $tmp['name'] = $data['name'];
		if(isset($data['descrip'])) $tmp['descrip'] = $data['descrip'];
		if(isset($data['rvalue'])) $tmp['rvalue'] = json_encode($data['rvalue']);
		if(isset($data['createtime'])) $tmp['createtime'] = $data['createtime'];
        if(isset($data['updatetime'])) $tmp['updatetime'] = $data['updatetime'];
        if(isset($data['operator'])) $tmp['operator'] = $data['operator'];
        if(isset($data['del'])) $tmp['del'] = $data['del'];
		$tmp['ifdefault'] = 1;
		return $tmp;
	}
	
	/**
	 * Advertiser_Dao_GroupModel
	 * @return Advertiser_Dao_GroupModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_GroupModel");
	}
}
