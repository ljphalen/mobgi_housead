<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-14 14:58:17
 * $Id: Accountdetail.php 62100 2016-9-14 14:58:17Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Admin_Service_AccountdetailModel{
    
    /**
     * 
     * @param int $page
     * @param type $limit
     * @param type $params
     * @return type
     */
    public static function getList($page = 1, $limit = 10, $params = array()) {
		if ($page < 1) $page = 1; 
		$start = ($page - 1) * $limit;
		$ret = self::_getDao()->getList($start, $limit, $params);
        return $ret;
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
	 * @param unknown_type $data
	 */
	public static function addAccountdetail($data) {
		if (!is_array($data)) return false;
		$data['create_time'] = Common::getTime();
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return  self::_getDao()->insert($data);
	}
    
    /**
     * 
     * @param type $data
     * @param type $params
     * @return boolean
     */
    public static function updateAccountdetailBy($data, $params) {
		if (!is_array($data)) return false; 
        if (!is_array($params)) return false; 
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->updateBy($data, $params);
	}
    
    /**
	 * 
	 * Enter descriptioon here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
		$tmp = array();
		if(isset($data['uid'])) $tmp['uid'] = $data['uid'];
		if(isset($data['account_type'])) $tmp['account_type'] = $data['account_type'];
		if(isset($data['balance'])) $tmp['balance'] = $data['balance'];
		if(isset($data['consumption_today'])) $tmp['consumption_today'] = $data['consumption_today'];
        if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}
    
    /**
	 * 
	 * @return Advertiser_Dao_AccountlogModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_AccountdetailModel");
	}
    
}

