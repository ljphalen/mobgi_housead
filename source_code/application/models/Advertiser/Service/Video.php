<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-12-22 10:18:10
 * $Id: Video.php 62100 2016-12-22 10:18:10Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_VideoModel{
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function getVideo($id) {
		if (!$id) return false;
		$result =  self::_getDao()->getby(array('id'=>$id));
        return $result;
	}
    
    /**
     * 
     * @param type $video_id
     * @return boolean
     */
    public static function getByVideoid($video_id) {
		if (!$video_id) return false;
		$result =  self::_getDao()->getby(array('video_id'=>$video_id));
        return $result;
	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $id
	 */
	public static function updateVideo($data, $id) {
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
	public static function deleteVideo($id) {
		return self::_getDao()->delete(intval($id));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addVideo($data) {
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
        if(isset($data['video_id'])) $tmp['video_id'] = $data['video_id'];
        if(isset($data['video_name'])) $tmp['video_name'] = $data['video_name'];
        if(isset($data['url'])) $tmp['url'] = $data['url'];
        if(isset($data['signature'])) $tmp['signature'] = $data['signature'];
        if(isset($data['width'])) $tmp['width'] = $data['width'];
        if(isset($data['height'])) $tmp['height'] = $data['height'];
        if(isset($data['frames'])) $tmp['frames'] = $data['frames'];
        if(isset($data['size'])) $tmp['size'] = $data['size'];
        if(isset($data['file_format'])) $tmp['file_format'] = $data['file_format'];
        if(isset($data['outer_video_id'])) $tmp['outer_video_id'] = $data['outer_video_id'];
        if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}
    
    
    /**
	 * 
	 * Enter description here ...
	 */
	public static function getVideoByName($videoname) {
		if (!$videoname) return false;
		return self::_getDao()->getBy(array('video_name'=>$videoname));
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
		return Common::getDao("Advertiser_Dao_VideoModel");
	}
}


