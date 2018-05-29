<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-10-26 20:08:33
 * $Id: GdtAdgroup.php 62100 2016-10-26 20:08:33Z hunter.fang $
 */


if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_GdtAdgroupModel{
    
    const CACHE_EPIRE = 3600;
    
    public  static  function  getCache(){
       $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS);
       return $cache;  
   }
   
   public  static  function  getGdtAddAdStepKey($type, $uId, $adId){
       return Util_CacheKey::GDT_ADDAD_STEP. "_".$uId.'_Step'.$type.'_'.$adId;
   }
   
   
   public static function deleteAdGroupKey($key1, $key2, $key3){
        $cache = self::getCache();
        $cache->delete($key1);
        $cache->delete($key2);
        $cache->delete($key3);
    }
   
//   
//    /**
//     * 
//     * @param int $page
//     * @param type $limit
//     * @param type $params
//     * @return type
//     */
//    public static function getList($page = 1, $limit = 10, $params = array()) {
//		if ($page < 1) $page = 1; 
//		$start = ($page - 1) * $limit;
//		$ret = self::_getDao()->getList($start, $limit, $params);
//		$total = self::_getDao()->count($params);
//        if($ret){
//            foreach($ret as $key=>$direct){
//                $ret[$key]['area_range'] = json_decode($direct['area_range'], true);
//                $ret[$key]['age_direct_range'] = json_decode($direct['age_direct_range'], true);
//                $ret[$key]['network_direct_range'] = json_decode($direct['network_direct_range'], true);
//                $ret[$key]['operator_direct_range'] = json_decode($direct['operator_direct_range'], true);
//                $ret[$key]['brand_direct_range'] = json_decode($direct['brand_direct_range'], true);
//                $ret[$key]['screen_direct_range'] = json_decode($direct['screen_direct_range'], true);
//                $ret[$key]['interest_direct_range'] = json_decode($direct['interest_direct_range'], true);
//                $ret[$key]['pay_ability_range'] = json_decode($direct['pay_ability_range'], true);
//                $ret[$key]['game_frequency_range'] = json_decode($direct['game_frequency_range'], true);
//            }
//        }
//        return array($total, $ret);
//	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function getAdgroup($adgroup_id) {
		if (!intval($adgroup_id)) return false;
		$result =  self::_getDao()->getby(array('adgroup_id'=>intval($adgroup_id)));
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
	public static function updateAdgroup($data, $id) {
		if (!is_array($data)) return false;
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->update($data, intval($id));
	}

    /**
	 *
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $id
	 */
	public static function updateAdgroupByParams($data, $params) {
		if (!is_array($data)) return false;
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->updateBy($data, $params);
	}

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $id
	 */
	public static function deleteAdgroup($id) {
		return self::_getDao()->delete(intval($id));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addAdgroup($data) {
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
        if(isset($data['adgroup_id'])) $tmp['adgroup_id'] = $data['adgroup_id'];
		if(isset($data['advertiser_uid'])) $tmp['advertiser_uid'] = $data['advertiser_uid'];
        if(isset($data['adgroup_name'])) $tmp['adgroup_name'] = $data['adgroup_name'];
        if(isset($data['local_config'])) $tmp['local_config'] = json_encode ($data['local_config']);
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
	public static function getAdgroupByName($adgrouupname,$advertiser_uid) {
		if (!$adgrouupname) return false;
		return self::_getDao()->getBy(array('adgroup_name'=>$adgrouupname,'advertiser_uid'=>$advertiser_uid));
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
		return Common::getDao("Advertiser_Dao_GdtAdgroupModel");
	}
    
}

