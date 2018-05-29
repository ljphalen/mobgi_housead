<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-10-25 20:25:15
 * $Id: GdtTargeting.php 62100 2016-10-25 20:25:15Z hunter.fang $
 */


if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_GdtTargetingModel{
    
    /**
     * 
     * @param int $page
     * @param type $limit
     * @param type $params
     * @return type
     */
    public static function getList($page = 1, $limit = 10, $params = array()) {
		if ($page < 1) $page = 1; 
		$start = ($page - 1) * $limit;
		$ret = self::_getDao()->getList($start, $limit, $params);
		$total = self::_getDao()->count($params);
        if($ret){
            foreach($ret as $key=>$direct){
                $ret[$key]['area_range'] = json_decode($direct['area_range'], true);
                $ret[$key]['age_direct_range'] = json_decode($direct['age_direct_range'], true);
                $ret[$key]['network_direct_range'] = json_decode($direct['network_direct_range'], true);
                $ret[$key]['operator_direct_range'] = json_decode($direct['operator_direct_range'], true);
                $ret[$key]['brand_direct_range'] = json_decode($direct['brand_direct_range'], true);
                $ret[$key]['screen_direct_range'] = json_decode($direct['screen_direct_range'], true);
                $ret[$key]['interest_direct_range'] = json_decode($direct['interest_direct_range'], true);
                $ret[$key]['pay_ability_range'] = json_decode($direct['pay_ability_range'], true);
                $ret[$key]['game_frequency_range'] = json_decode($direct['game_frequency_range'], true);
            }
        }
        return array($total, $ret);
	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function getTargeting($targeting_id) {
		if (!intval($targeting_id)) return false;
		$result =  self::_getDao()->getby(array('targeting_id'=>intval($targeting_id)));
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
	public static function updateTargeting($data, $id) {
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
	public static function deleteTargeting($id) {
		return self::_getDao()->delete(intval($id));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addTargeting($data) {
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
        if(isset($data['targeting_id'])) $tmp['targeting_id'] = $data['targeting_id'];
		if(isset($data['advertiser_uid'])) $tmp['advertiser_uid'] = $data['advertiser_uid'];
        if(isset($data['targeting_name'])) $tmp['targeting_name'] = $data['targeting_name'];
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
	public static function getTargetingByName($directname) {
		if (!$directname) return false;
		return self::_getDao()->getBy(array('targeting_name'=>$directname));
	}
	
	
	public static function getBy($params = array(),$orderBy = array('id'=>'DESC')){
        if(!isset($params['del'])){
            $params['del'] =0;
        }
	    $ret = self::_getDao()->getBy($params, $orderBy);
	    if(!$ret) return false;
	    return $ret;
	
	}
	

	public static function getsBy($params = array(),$orderBy = array('id'=>'DESC')){
        if(!isset($params['del'])){
            $params['del'] =0;
        }
	    $ret = self::_getDao()->getsBy($params, $orderBy);
	    if(!$ret) return false;
	    return $ret;
	
	}
	
	/**
	 * 
	 * @return Advertiser_Dao_DirectModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_GdtTargetingModel");
	}
    
}
