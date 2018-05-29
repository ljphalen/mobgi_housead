<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-9-8 18:47:58
 * $Id: AdsPosRelWhitelist.php 62100 2017-9-8 18:47:58Z hunter.fang $
 */
if (!defined('BASE_PATH')) exit('Access Denied!');


class MobgiApi_Service_AdsPosRelWhitelistModel{
    const OPEN_STATE = 1;
    
    
	/**
	 * 
	 * Enter description here ...
	 */
	public static function getAll() {
		return array(self::_getDao()->count(), self::_getDao()->getAll());
	}
	
	
	/**
	 *
	 * @param unknown_type $data
	 * @return boolean
	 */
	public static function mutiFieldInsert($data) {
		if (!is_array($data)) return false;
		return self::_getDao()->mutiFieldInsert($data);
	}
	
	
	
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $params
	 * @param unknown_type $page
	 * @param unknown_type $limit
	 */
	public static function getList($page = 1, $limit = 10, $params = array(),$orderBy = array('id'=>'DESC')) {
	    if ($page < 1) $page = 1;
	    $start = ($page - 1) * $limit;
	    $ret = self::_getDao()->getList($start, $limit, $params, $orderBy);
	    $total = self::_getDao()->count($params);
	    return array($total, $ret);
	}
	
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $id
	 */
	public static function getByID($id) {
	    if (!intval($id)) return false;
	    return self::_getDao()->get(intval($id));
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
	 * @param unknown_type $page
	 * @param unknown_type $limit
	 * @param unknown_type $params
	 * @return multitype:unknown multitype:
	 */
	
	public static function getsBy($params = array(),$orderBy = array('id'=>'DESC')){
	    $ret = self::_getDao()->getsBy($params, $orderBy);
	    if(!$ret) return false;
	    return $ret;
	
	}
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $id
	 */
	public static function updateByID($data, $id) {
	    if (!is_array($data)) return false;
	    $data = self::_cookData($data);
	    return self::_getDao()->update($data, intval($id));
	}
	
	public static function updateBy($data, $params){
	    if (!is_array($data) || !is_array($params)) return false;
	    $data = self::_cookData($data);
	    return self::_getDao()->updateBy($data, $params);
	}
	/**
	 *
	 * @param unknown_type $data
	 * @param unknown_type $sorts
	 * @return boolean
	 */
	public static function sortAd($sorts) {
	    foreach($sorts as $key=>$value) {
	        self::_getDao()->update(array('sort'=>$value), $key);
	    }
	    return true;
	}
	
	/**
	 *
	 * @param unknown_type $data
	 * @return boolean
	 */
	public static function deleteGameAd($data) {
	    foreach($data as $key=>$value) {
	        $v = explode('|', $value);
	        self::_getDao()->deleteBy(array('id'=>$v[0]));
	    }
	    return true;
	}
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $id
	 */
	public static function deleteById($id) {
	    return self::_getDao()->delete(intval($id));
	}
	
	
	public static function deleteBy($params) {
	    return self::_getDao()->deleteBy($params);
	}
	
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function add($data) {
	    if (!is_array($data)) return false;
	    $data = self::_cookData($data);
	    $data['create_time'] = Common::getTime();
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
	    if(isset($data['id'])) $tmp['id'] = intval($data['id']);
	    if(isset($data['app_name'])) $tmp['app_name'] = $data['app_name'];
	    if(isset($data['platform'])) $tmp['platform'] = $data['platform'];
	    if(isset($data['app_key'])) $tmp['app_key'] = $data['app_key'];
	    if(isset($data['ad_sub_type'])) $tmp['ad_sub_type'] = $data['ad_sub_type'];
	    if(isset($data['ads_id'])) $tmp['ads_id'] = $data['ads_id'];
	    if(isset($data['pos_id'])) $tmp['pos_id'] = $data['pos_id'];
	    if(isset($data['pos_key'])) $tmp['pos_key'] = $data['pos_key'];
	    if(isset($data['ads_id'])) $tmp['ads_id'] = $data['ads_id'];
	    if(isset($data['third_party_block_id'])) $tmp['third_party_block_id'] = $data['third_party_block_id'];
	    if(isset($data['third_party_report_id'])) $tmp['third_party_report_id'] = $data['third_party_report_id'];
        if(isset($data['state'])) $tmp['state'] = $data['state'];
	    $tmp['update_time'] = Common::getTime();
	    return $tmp;
	}
	
	/**
	 * 
	 * @return MobgiApi_Dao_AdsPosRelModel
	 */
	private static function _getDao() {
		return Common::getDao("MobgiApi_Dao_AdsPosRelWhitelistModel");
	}
}
