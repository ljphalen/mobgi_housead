<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-11-7 14:00:47
 * $Id: GdtMedia.php 62100 2016-11-7 14:00:47Z hunter.fang $
 */



if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_GdtMediaModel{
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function getMedia($media_id) {
		if (!$media_id) return false;
		$result =  self::_getDao()->getby(array('id'=>$media_id));
        if($result){
            $result['config'] = json_decode($result['config'], true);
            $result['sync_response'] = json_decode($result['sync_response'], true);
        }
        return $result;
	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $id
	 */
	public static function updateMedia($data, $id) {
		if (!is_array($data)) return false;
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->update($data, intval($id));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $id
	 */
	public static function deleteMedia($id) {
		return self::_getDao()->delete(intval($id));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addMedia($data) {
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
		if(isset($data['advertiser_uid'])) $tmp['advertiser_uid'] = $data['advertiser_uid'];
        if(isset($data['media_id'])) $tmp['media_id'] = $data['media_id'];
        if(isset($data['media_name'])) $tmp['media_name'] = $data['media_name'];
        if(isset($data['config'])) $tmp['config'] = json_encode ($data['config']);
        if(isset($data['sync_status'])) $tmp['sync_status'] = $data['sync_status'];
        if(isset($data['sync_response'])) $tmp['sync_response'] = json_encode ($data['sync_response']);
        if(isset($data['del'])) $tmp['del'] = $data['del'];
        if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}
    
    /**
	 * 
	 * Enter description here ...
	 */
	public static function getMediaByName($medianame) {
		if (!$medianame) return false;
		return self::_getDao()->getBy(array('media_name'=>$medianame));
	}
	
	
	public static function getBy($params = array(),$orderBy = array('id'=>'DESC')){
	    $ret = self::_getDao()->getBy($params, $orderBy);
	    if(!$ret) return false;
	    return $ret;
	
	}
	

	public static function getsBy($params = array(),$orderBy = array('id'=>'DESC')){
	    $ret = self::_getDao()->getsBy($params, $orderBy);
	    if(!$ret) return false;
	    return $ret;
	
	}
	
	/**
	 * 
	 * @return Advertiser_Dao_DirectModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_GdtMediaModel");
	}
    
}


