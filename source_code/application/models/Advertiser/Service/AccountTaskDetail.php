<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-13 14:47:14
 * $Id: Accounttaskdetail.php 62100 2016-9-13 14:47:14Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_AccountTaskDetailModel{
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $params
	 * @param unknown_type $page
	 * @param unknown_type $limit
	 */
	public static function getList($page = 1, $limit = 10, $params = array()) {
		if ($page < 1) $page = 1; 
		$start = ($page - 1) * $limit;
		$ret = self::_getDao()->getList($start, $limit, $params);
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
     * 查询批量结果集
     * @param type $params
     * @param type $orderBy
     * @return boolean
     */
    public static function getsBy($params = array(),$orderBy = array('detailid'=>'DESC')){
	    $ret = self::_getDao()->getsBy($params, $orderBy);
	    if(!$ret) return false;
	    return $ret;
	
	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addTaskdetail($data) {
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
	public static function updateTaskdetail($data, $taskid) {
		if (!is_array($data)) return false; 
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->update($data, intval($taskid));
	}
    
    /**
     * 
     * @param type $data
     * @param type $taskid
     * @return boolean
     */
    public static function updateTaskdetailBy($data, $params) {
		if (!is_array($data)) return false; 
        if (!is_array($params)) return false; 
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->updateBy($data, $params);
	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
		$tmp = array();
		if(isset($data['email'])) $tmp['email'] = $data['email'];
		if(isset($data['taskid'])) $tmp['taskid'] = $data['taskid'];
		if(isset($data['taskdetailstate'])) $tmp['taskdetailstate'] = $data['taskdetailstate'];
        if(isset($data['opertype'])) $tmp['opertype'] = $data['opertype'];
        if(isset($data['virtual_account_type'])) $tmp['virtual_account_type'] = $data['virtual_account_type'];
		if(isset($data['money'])) $tmp['money'] = $data['money'];
        if(isset($data['applyby'])) $tmp['applyby'] = $data['applyby'];
        if(isset($data['apply_time'])) $tmp['apply_time'] = $data['apply_time'];
        if(isset($data['auditby'])) $tmp['auditby'] = $data['auditby'];
        if(isset($data['auditstate'])) $tmp['auditstate'] = $data['auditstate'];
        if(isset($data['auditmsg'])) $tmp['auditmsg'] = $data['auditmsg'];
        if(isset($data['audit_time'])) $tmp['audit_time'] = $data['audit_time'];
        if(isset($data['expire_time'])) $tmp['expire_time'] = $data['expire_time'];
        if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}
    
    /**
	 * 
	 * @return Advertiser_Dao_AccountTaskDetailModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_AccountTaskDetailModel");
	}
    
}