<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-28 15:45:18
 * $Id: AccountVirtualDetail.php 62100 2016-9-28 15:45:18Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_AccountVirtualDetailModel{
    
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function add($data) {
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
	 */
	private static function _cookData($data) {
		$tmp = array();
        if(isset($data['uid'])) $tmp['uid'] = $data['uid'];
		if(isset($data['account_type'])) $tmp['account_type'] = $data['account_type'];
        if(isset($data['balance'])) $tmp['balance'] = $data['balance'];
        if(isset($data['status'])) $tmp['status'] = $data['status'];
        if(isset($data['taskdetailid'])) $tmp['taskdetailid'] = $data['taskdetailid'];
        if(isset($data['expire_time'])) $tmp['expire_time'] = $data['expire_time'];
        if(isset($data['operator'])) $tmp['operator'] = $data['operator'];
        if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}
    
    /**
	 * 
	 * @return Advertiser_Dao_AccountlogModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_AccountVirtualDetailModel");
	}
    
}

