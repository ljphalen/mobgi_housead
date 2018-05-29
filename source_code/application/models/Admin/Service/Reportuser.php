<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-12-12 16:22:11
 * $Id: Reportuser.php 62100 2016-12-12 16:22:11Z hunter.fang $
 */
if (!defined('BASE_PATH')) exit('Access Denied!');

class Admin_Service_ReportuserModel{
    
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
	private static function _cookData($data) {
		$tmp = array();
        if(isset($data['advertiser_uid'])) $tmp['advertiser_uid'] = $data['advertiser_uid'];
        if(isset($data['related_advertiser_uid'])) $tmp['related_advertiser_uid'] = $data['related_advertiser_uid'];
        if(isset($data['operator'])) $tmp['operator'] = $data['operator'];
        if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addConfig($data) {
		if (!is_array($data)) return false;
		$data['create_time'] = Common::getTime();
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->insert($data);
	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $uid
	 */
	public static function updateConfigBy($data, $params) {
		if (!is_array($data)) return false;
        if (!is_array($params)) return false; 
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->updateBy($data, $params);
	}
    
    
    /**
     * 自定义使用cache
     * @return type
     */
    public static function  getCache(){
       $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS);
       return $cache;  
    }
    
    /**
	 * 
	 * @return Admin_Dao_UserModel
	 */
	private static function _getDao() {
		return Common::getDao("Admin_Dao_ReportuserModel");
        
	}
    
}

