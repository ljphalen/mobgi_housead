<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-12-20 10:59:17
 * $Id: Image.php 62100 2016-12-20 10:59:17Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_ImageModel{
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function getImage($id) {
		if (!$id) return false;
		$result =  self::_getDao()->getby(array('id'=>$id));
        return $result;
	}
    
    /**
	 * 
     * @param type $image_id
     * @return boolean
     */
    public static function getByImageid($image_id) {
		if (!$image_id) return false;
		$result =  self::_getDao()->getby(array('image_id'=>$image_id));
        return $result;
	}
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $id
	 */
	public static function updateImage($data, $id) {
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
	public static function deleteImage($id) {
		return self::_getDao()->delete(intval($id));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addImage($data) {
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
        if(isset($data['image_id'])) $tmp['image_id'] = $data['image_id'];
        if(isset($data['image_name'])) $tmp['image_name'] = $data['image_name'];
        if(isset($data['url'])) $tmp['url'] = $data['url'];
        if(isset($data['signature'])) $tmp['signature'] = $data['signature'];
        if(isset($data['width'])) $tmp['width'] = $data['width'];
        if(isset($data['height'])) $tmp['height'] = $data['height'];
        if(isset($data['size'])) $tmp['size'] = $data['size'];
        if(isset($data['file_format'])) $tmp['file_format'] = $data['file_format'];
        if(isset($data['outer_image_id'])) $tmp['outer_image_id'] = $data['outer_image_id'];
        if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}
    
    /**
	 * 
	 * Enter description here ...
	 */
	public static function getImageByName($imagename) {
		if (!$imagename) return false;
		return self::_getDao()->getBy(array('image_name'=>$imagename));
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
		return Common::getDao("Advertiser_Dao_ImageModel");
	}
}

