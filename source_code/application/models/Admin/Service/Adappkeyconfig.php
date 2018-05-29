<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-11-22 14:27:15
 * $Id: Adappkeyconfig.php 62100 2016-11-22 14:27:15Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Admin_Service_AdappkeyconfigModel{
    
    const APPKEY_CONFIGID_FOR_ANDROID = 1;
    const APPKEY_CONFIGID_FOR_IOS = 2;
    //插页边框 纯色
    const BODER_TYPE_COLOR = 0;
    //插页边框 图片
    const BODER_TYPE_PIC = 1;
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $configid
	 */
	public static function getConfig($configid) {
	    if(!$configid){
	        return false;
	    }
		$result = self::_getDao()->get(intval($configid));
		if($result){
		    $result['config'] = json_decode($result['config'], true);
		}
		return $result;
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
		$ret = self::_getDao()->insert($data);
	    if (!$ret) return $ret;
        return self::_getDao()->getLastInsertId();
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $configid
	 */
	public static function updateConfig($data, $configid) {
		if (!is_array($data)) return false; 
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->update($data, intval($configid));
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public static function getAllConfig() {
		return  array(self::_getDao()->count(), self::_getDao()->getAll());
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
        $params['del'] = 0;
		$ret = self::_getDao()->getList(intval($start), intval($limit), $params);
		$total = self::_getDao()->count($params);
		return array($total, $ret); 
	}
	
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $id
	 */
	public static function getsBy($params, $orderBy = array('id' => 'ASC')) {
		if (!is_array($params)) return false;
		return self::_getDao()->getsBy($params, $orderBy);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $configid
	 */
	public static function deleteConfig($configid) {
		return self::_getDao()->	delete(intval($configid));
	}
    
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
		$tmp = array();
		if(isset($data['name'])) $tmp['name'] = $data['name'];
		if(isset($data['config'])) $tmp['config'] = json_encode($data['config']);
        if(isset($data['del'])) $tmp['del'] = $data['del'];
        if(isset($data['operator'])) $tmp['operator'] = $data['operator'];
		if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}
    
    /**
	 *
	 * @param unknown_type $page
	 * @param unknown_type $limit
	 * @param unknown_type $params
	 * @return multitype:unknown multitype:
	 */
	
	public static function getBy($params = array(),$orderBy = array('id'=>'DESC')){
	    $ret = self::_getDao()->getBy($params, $orderBy);
	   if(!$ret) return false;
	    return $ret;
	
	}
	
	/**
	 * 
	 * @return Admin_Dao_GroupModel
	 */
	private static function _getDao() {
		return Common::getDao("Admin_Dao_AdappkeyconfigModel");
	}
}

