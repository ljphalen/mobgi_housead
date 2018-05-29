<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-11-3 20:48:22
 * $Id: GdtCreative.php 62100 2016-11-3 20:48:22Z hunter.fang $
 */



if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_GdtCreativeModel{
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function getCreative($creative_id) {
		if (!$creative_id) return false;
		$result =  self::_getDao()->getby(array('creative_id'=>$creative_id));
        if($result){
            $result['config'] = json_decode($result['config'], true);
            $result['sync_response'] = json_decode($result['sync_response'], true);
        }
        return $result;
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function getCreativebyAdgroupid($advertiser_uid,$adgroup_id,$clone) {
		if (!$adgroup_id) return false;
		$result =  self::_getDao()->getsBy(array('advertiser_uid'=>$advertiser_uid,'adgroup_id'=>$adgroup_id));
		$creative_arr = array();
		if($result){
			// 组合成数组返回
			foreach($result as $key => $value){
				$config = json_decode($value['config'], true);
				$creative_arr[$key]['template_id'] = $config['creative_template_id'];
				if($clone){
					$creative_arr[$key]['creative_name'] = $config['creative_name']."-副本";
					$creative_arr[$key]['is_edit'] = 0;
				}else{
					$creative_arr[$key]['creative_name'] = $config['creative_name'];
					$creative_arr[$key]['is_edit'] = 1;
				}
				$creative_elements = json_decode($config['creative_elements'],true);
				$creative_arr[$key]['creative_desc'] = $creative_elements['title'];
				if($config['creative_template_id'] == 65){
					$creative_arr[$key]['template65_img'] = $creative_elements['image'];
					// 根据image_id获取图片链接地址
					$creative_arr[$key]['template65_img_url'] = Advertiser_Service_GdtImageModel::getImageUrlById($creative_elements['image']);
				}elseif($config['creative_template_id'] == 271){
					$creative_arr[$key]['template271_img1'] = $creative_elements['element_story'][0]['image'];
					$creative_arr[$key]['template271_img1_url'] = Advertiser_Service_GdtImageModel::getImageUrl($creative_elements['element_story'][0]['image']);
					$creative_arr[$key]['template271_img2'] = $creative_elements['element_story'][1]['image'];
					$creative_arr[$key]['template271_img2_url'] = Advertiser_Service_GdtImageModel::getImageUrl($creative_elements['element_story'][1]['image']);
					$creative_arr[$key]['template271_img3'] = $creative_elements['element_story'][2]['image'];
					$creative_arr[$key]['template271_img3_url'] = Advertiser_Service_GdtImageModel::getImageUrl($creative_elements['element_story'][2]['image']);
				}elseif($config['creative_template_id'] == 351){
					$creative_arr[$key]['template351_img1'] = $creative_elements['image'];
					$creative_arr[$key]['template351_img1_url'] = Advertiser_Service_GdtImageModel::getImageUrl($creative_elements['image']);
					$creative_arr[$key]['template351_img2'] = $creative_elements['image2'];
					$creative_arr[$key]['template351_img2_url'] = Advertiser_Service_GdtImageModel::getImageUrl($creative_elements['image2']);
					$creative_arr[$key]['template351_video'] = $creative_elements['video'];
				}
			}
//			$result['sync_response'] = json_decode($result['sync_response'], true);
		}
		return $creative_arr;
	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $id
	 */
	public static function updateCreative($data, $id) {
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
	public static function deleteCreative($id) {
		return self::_getDao()->delete(intval($id));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addCreative($data) {
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
		if(isset($data['adgroup_id'])) $tmp['adgroup_id'] = $data['adgroup_id'];
        if(isset($data['creative_id'])) $tmp['creative_id'] = $data['creative_id'];
        if(isset($data['creative_name'])) $tmp['creative_name'] = $data['creative_name'];
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
	public static function getCreativeByName($imagename) {
		if (!$imagename) return false;
		return self::_getDao()->getBy(array('creative_name'=>$imagename));
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
		return Common::getDao("Advertiser_Dao_GdtCreativeModel");
	}
    
}


