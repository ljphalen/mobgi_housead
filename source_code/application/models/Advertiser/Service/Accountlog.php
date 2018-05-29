<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-8-31 18:51:49
 * $Id: Accountlog.php 62100 2016-8-31 18:51:49Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_AccountlogModel{
    
    /**
     * 
     * @param int $page
     * @param type $limit
     * @param type $params
     * @return type
     */
    public static function getList($page = 1, $limit = 10, $params = array(), $orderBy= array()) {
		if ($page < 1) $page = 1; 
		$start = ($page - 1) * $limit;
		$ret = self::_getDao()->getList($start, $limit, $params, $orderBy);
		$total = self::_getDao()->count($params);
		return array($total, $ret);
	}
    
    /**
	 * 
	 * @return Advertiser_Dao_AccountlogModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_AccountlogModel");
	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function add($data) {
		if (!is_array($data)) return false;
		$data['create_time'] = Common::getTime();
		$data = self::_cookData($data);
		 $ret = self::_getDao()->insert($data);
		 if (!$ret) return $ret;
		 return self::_getDao()->getLastInsertId();
	}
        
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
		$tmp = array();
        if(isset($data['uid'])) $tmp['uid'] = $data['uid'];
		if(isset($data['account_type'])) $tmp['account_type'] = $data['account_type'];
        if(isset($data['operate_type'])) $tmp['operate_type'] = $data['operate_type'];
        if(isset($data['trade_balance'])) $tmp['trade_balance'] = $data['trade_balance'];
        if(isset($data['description'])) $tmp['description'] = $data['description'];
        if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
		return $tmp;
	}
    
}
