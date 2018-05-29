<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-1 15:27:00
 * $Id: Operatelog.php 62100 2016-9-1 15:27:00Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Admin_Service_OperatelogModel{
    
    /**
     * 
     * @param int $page
     * @param type $limit
     * @param type $params
     * @return type
     */
    public static function getList($page = 1, $limit = 10, $params = array(), $orderby= array()) {
		if ($page < 1) $page = 1; 
		$start = ($page - 1) * $limit;
		$ret = self::_getDao()->getList($start, $limit, $params, $orderby);
		$total = self::_getDao()->count($params);
		return array($total, $ret);
	}
    
    /**
     * 添加操作日志
     * @param array $data
     * @return boolean
     */
    public static function addOperateLog($data) {
		if (!is_array($data)) return false;
		$data['create_time'] = Common::getTime();
        $data = self::_cookData($data);
		return self::_getDao()->insert($data);
	}
    
    public static function cache_recharge(){
        
    }

        /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
		$tmp = array();
		if(isset($data['user_id'])) $tmp['user_id'] = $data['user_id'];
		if(isset($data['object'])) $tmp['object'] = $data['object'];
        if(isset($data['module'])) $tmp['module'] = $data['module'];
		if(isset($data['sub_module'])) $tmp['sub_module'] = $data['sub_module'];
        if(isset($data['content'])) $tmp['content'] = $data['content'];
        if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
		return $tmp;
	}
    
    /**
	 * 
	 * @return Admin_Dao_OperatelogModel
	 */
	private static function _getDao() {
		return Common::getDao("Admin_Dao_OperatelogModel");
	}
    
}

