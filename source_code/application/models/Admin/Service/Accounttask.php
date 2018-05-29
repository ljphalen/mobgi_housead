<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-12 18:36:28
 * $Id: Accounttask.php 62100 2016-9-12 18:36:28Z hunter.fang $
 */
if (!defined('BASE_PATH')) exit('Access Denied!');

class Admin_Service_AccounttaskModel{
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $params
	 * @param unknown_type $page
	 * @param unknown_type $limit
	 */
	public static function getList($page = 1, $limit = 10, $params = array(),$orderBy=array()) {
		if ($page < 1) $page = 1; 
		$start = ($page - 1) * $limit;
		$ret = self::_getDao()->getList($start, $limit, $params, $orderBy);
		$total = self::_getDao()->count($params);
		return array($total, $ret);
	}
    
    /**
     *
     * 查询一条结果集
     * @param array $search
     */
    public static function getBy($search) {
        return self::_getDao()->getBy($search);
    }
    
    /**
	 *
	 * Enter description here ...
	 * @param unknown_type $id
	 */
	public static function getByTaskid($taskid) {
	    if (!intval($taskid)) return false;
	    return self::_getDao()->get(intval($taskid));
	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addTask($data) {
		if (!is_array($data)) return false;
		$data['create_time'] = Common::getTime();
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		$ret = self::_getDao()->insert($data);
	    if (!$ret) return $ret;
        return self::_getDao()->getLastInsertId();
	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $groupid
	 */
	public static function updateTask($data, $taskid) {
		if (!is_array($data)) return false; 
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->update($data, intval($taskid));
	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
		$tmp = array();
		if(isset($data['taskname'])) $tmp['taskname'] = $data['taskname'];
		if(isset($data['tasktype'])) $tmp['tasktype'] = $data['tasktype'];
		if(isset($data['taskstate'])) $tmp['taskstate'] = $data['taskstate'];
		if(isset($data['opertype'])) $tmp['opertype'] = $data['opertype'];
        if(isset($data['applyby'])) $tmp['applyby'] = $data['applyby'];
        if(isset($data['applymsg'])) $tmp['applymsg'] = $data['applymsg'];
        if(isset($data['apply_time'])) $tmp['apply_time'] = $data['apply_time'];
        if(isset($data['auditby'])) $tmp['auditby'] = $data['auditby'];
        if(isset($data['audit_time'])) $tmp['audit_time'] = $data['audit_time'];
        if(isset($data['auditstate'])) $tmp['auditstate'] = $data['auditstate'];
        if(isset($data['auditmsg'])) $tmp['auditmsg'] = $data['auditmsg'];
        if(isset($data['expire_time'])) $tmp['expire_time'] = $data['expire_time'];
        if(isset($data['del'])) $tmp['del'] = $data['del'];
        if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}
    
    /**
	 * 
	 * @return Admin_Dao_UserModel
	 */
	private static function _getDao() {
		return Common::getDao("Admin_Dao_AccounttaskModel");
	}
    
}


