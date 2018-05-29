<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-6 16:35:42
 * $Id: Direct.php 62100 2016-9-6 16:35:42Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_DirectModel{
    
    /**
     * 
     * @param int $page
     * @param type $limit
     * @param type $params
     * @return type
     */
    public static function getList($page = 1, $limit = 10, $params = array(), $orderBy = array('update_time' => 'DESC')) {
		if ($page < 1) $page = 1; 
		$start = ($page - 1) * $limit;
		$ret = self::_getDao()->getList($start, $limit, $params, $orderBy);
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
	public static function getDirect($id) {
		if (!intval($id)) return false;
		$result =  self::_getDao()->get(intval($id));
        return $result;
	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $id
	 */
	public static function updateDirect($data, $id) {
		if (!is_array($data)) return false;
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->update($data, intval($id));
	}
    public static function updateBy($data, $params) {
        if (!is_array($data) || !is_array($params)) return false;
        $data = self::_cookData($data);
        return self::_getDao()->updateBy($data, $params);
    }
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $id
	 */
	public static function deleteDirect($id) {
		return self::_getDao()->delete(intval($id));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addDirect($data) {
		if (!is_array($data)) return false;
		$data['create_time'] = Common::getTime();
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
		if(isset($data['direct_name'])) $tmp['direct_name'] = $data['direct_name'];
        if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
        if(isset($data['direct_config'])) $tmp['direct_config'] = $data['direct_config'];
        if(isset($data['outer_direct_id'])) $tmp['outer_direct_id'] = $data['outer_direct_id'];
        if(isset($data['del'])) $tmp['del'] = $data['del'];
        $tmp['update_time'] =  Common::getTime();
		return $tmp;
	}
    
    /**
	 * 
	 * Enter description here ...
	 */
	public static function getDirectByName($directname) {
		if (!$directname) return false;
		return self::_getDao()->getBy(array('direct_name'=>$directname));
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
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function createGdtTargeting($data, $id) {
		if (!is_array($data)) return false;
        $data['update_time'] = Common::getTime();
		$data = self::_cookGdtData($data);
        return self::_getDao()->update($data, intval($id));
	}
    /**
     * 
     * @param type $data
     * @return type
     */
    private static function _cookGdtData($data) {
		$tmp = array();
		if(isset($data['targeting_id'])) $tmp['targeting_id'] = $data['targeting_id'];
        if(isset($data['gdt_config'])) $tmp['gdt_config'] = $data['gdt_config'];
		if(isset($data['gdt_sync_status'])) $tmp['gdt_sync_status'] = $data['gdt_sync_status'];
        if(isset($data['gdt_sync_response'])) $tmp['gdt_sync_response'] = $data['gdt_sync_response'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}
	/**
	 * 
	 * @return Advertiser_Dao_DirectModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_DirectModel");
	}
    
}



