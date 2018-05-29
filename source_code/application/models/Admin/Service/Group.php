<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Admin_Service_GroupModel{

    
    
	public static function getBy($params) {
		$params['del'] = Common_Service_Const::NOT_DELETE_FLAG;
	    return self::_getDao()->getBy($params);
	}
	

	public static function getsBy($params) {
		$params['del'] = Common_Service_Const::NOT_DELETE_FLAG;
		return self::_getDao()->getsBy($params);
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type group_id
	 */
	public static function getGroup($groupId) {
		$result = self::_getDao()->get(intval($groupId));
		$result['top_module_config'] = json_decode($result['top_module_config'], true);
		$result['menu_config'] = json_decode($result['menu_config'], true);
		$result['menu_right'] = json_decode($result['menu_right'], true);
		return $result;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addGroup($data) {
		if (!is_array($data)) return false;
		$data['create_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->insert($data);
		
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $groupid
	 */
	public static function updateGroup($data, $groupId) {
		if (!is_array($data)) return false; 
		$data = self::_cookData($data);
		return self::_getDao()->update($data, intval($groupId));
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public static function getAllGroup() {
        $params['del'] = Common_Service_Const::NOT_DELETE_FLAG;
		return array(self::_getDao()->count($params), self::_getDao()->getsby($params));
	}
	

	
	/**
	 *
	 * 删除一个用户组
	 * @param int $groupid
	 */
	public function deleteGroup($groupId) {
	    return self::_getDao()->delete(intval($groupId));
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
	

	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
		$tmp = array();
		if(isset($data['name'])) $tmp['name'] = $data['name'];
		if(isset($data['descrip'])) $tmp['descrip'] = $data['descrip'];
		if(isset($data['top_module_config'])) $tmp['top_module_config'] = json_encode($data['top_module_config']);
		if(isset($data['menu_config'])) $tmp['menu_config'] = json_encode($data['menu_config']);
		if(isset($data['menu_right'])) $tmp['menu_right'] = json_encode($data['menu_right']);
		if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['operator'])) $tmp['operator'] = $data['operator'];
        if(isset($data['del'])) $tmp['del'] = $data['del'];
		$tmp['default'] = 1;
		$tmp['update_time'] = Common::getTime();
		return $tmp;
	}
	
	/**
	 * 
	 * @return Admin_Dao_GroupModel
	 */
	private static function _getDao() {
		return Common::getDao("Admin_Dao_GroupModel");
	}
}
