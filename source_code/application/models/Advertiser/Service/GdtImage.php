<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-11-3 19:52:13
 * $Id: GdtImage.php 62100 2016-11-3 19:52:13Z hunter.fang $
 */


if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_GdtImageModel{
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function getImage($image_id) {
		if (!$image_id) return false;
		$result =  self::_getDao()->getby(array('id'=>$image_id));
        if($result){
            $result['config'] = json_decode($result['config'],true);
            $result['sync_response'] = json_decode($result['sync_response'], true);
        }
        return $result;
	}

	/**
	 *
	 * Enter description here ...
	 * @param string $image_id
	 * @return bool|string
	 */
	public static function getImageUrlById($image_id='') {
		$img_url = '';
		if (!$image_id) return $img_url;
		$result =  self::_getDao()->getby(array('image_id'=>$image_id),array('id'=>'DESC'));
		if($result){ // 用image_id查找
			$config = json_decode($result['config'],true);
			$img_url = $config['image_url'];
		}
		return $img_url;
	}


	/**
	 *
	 * Enter description here ...
	 * @param string $out_image_id
	 * @return string
	 */
	public static function getImageUrlByOutId($out_image_id='') {
		$img_url = '';
		if (!$out_image_id) return $img_url;
		$result =  self::_getDao()->getby(array('id'=>$out_image_id));
		if($result){
			$config = json_decode($result['config'],true);
			$img_url = $config['image_url'];
		}
		return $img_url;
	}

	/**
	 *
	 * Enter description here ...
	 * @param $result
	 * @return string
	 */
	public static function getImageUrls(&$result) {
		if (empty($result)) return false;
		foreach($result['creative_arr'] as $key => $value){
			// 把图片展示出来
			if($result['template_id'] == 65){
				// 根据image_id获取图片链接地址
				$result['creative_arr'][$key]['template65_img_url'] = self::getImageUrlByOutId($result['creative_arr'][$key]['template65_ourimageid']);
			}elseif($result['template_id'] == 271){
				$result['creative_arr'][$key]['template271_img1_url'] = self::getImageUrlByOutId($result['creative_arr'][$key]['template271_ourimageid1']);
				$result['creative_arr'][$key]['template271_img2_url'] = self::getImageUrlByOutId($result['creative_arr'][$key]['template271_ourimageid2']);
				$result['creative_arr'][$key]['template271_img3_url'] = self::getImageUrlByOutId($result['creative_arr'][$key]['template271_ourimageid3']);
			}elseif($result['template_id'] == 351){
				$result['creative_arr'][$key]['template351_img1_url'] = self::getImageUrlByOutId($result['creative_arr'][$key]['template351_ourimageid1']);
				$result['creative_arr'][$key]['template351_img2_url'] = self::getImageUrlByOutId($result['creative_arr'][$key]['template351_ourimageid2']);
			}
		}
		return true;
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
		return Common::getDao("Advertiser_Dao_GdtImageModel");
	}
    
}

