<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 * MobgiCharge_Service_AdxChargeMinuteModel
 *
 */
class MobgiCharge_Service_AdxChargeMinuteModel{
    
    
    const  CACHE_EPIRE = 600;


    public static function getOriginalityDetailCacheKey( $id, $dspId='Housead_DSP'){
        return Util_CacheKey::ORIGINALITY_TODAY_DETAIL.$dspId.'::'.date('Ymd').'::'.$id;
    }
    
    public static function getTodayOriginalityDetailFromCache($id, $dspId='Housead_DSP'){
        $key = self::getOriginalityDetailCacheKey($id, $dspId);
        $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS, 'adx_default');
        $result = $cache->hMget($key, array('clicks','actives','views','amount'));
        if($result['clicks'] ===  FALSE && $result['views'] === FALSE && $result['actives'] === FALSE && $result['amount'] === FALSE ){
            $params['originality_id'] = $id;
            $params['days'] = array('=', date('Y-m-d'));
            $ret =self::getOriginalitySumResult($params);
            if(!$ret['originality_id']) return false;
            $result['clicks'] = $ret['clicks'];
            $result['actives'] = $ret['actives'];
            $result['views'] = $ret['views'];
            $result['amount'] = $ret['amount'];
            self::saveOriginalityDetailToCache($id, $result);
        }
        return  $result;
    }
    
    public static function saveOriginalityDetailToCache($id, $data){
        $key = self::getOriginalityDetailCacheKey($id);
        $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS, 'adx_default');
        return  $cache->hMset($key, $data, self::CACHE_EPIRE);
    }
    
	/**
	 * 
	 * Enter description here ...
	 */
	public static function getAll() {
		return array(self::_getDao()->count(), self::_getDao()->getAll());
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
	
	
	public static function getOriginalitySumResult($params){
	    $ret = self::_getDao()->getOriginalitySumResult($params);
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
	    if(isset($data['ad_unit_id'])) $tmp['ad_unit_id'] = intval($data['ad_unit_id']);
	    if(isset($data['ad_id'])) $tmp['ad_id'] = intval($data['ad_id']);
	    if(isset($data['originality_id'])) $tmp['originality_id'] = $data['originality_id'];
	    if(isset($data['day'])) $tmp['day'] = $data['day'];
	    if(isset($data['clicks'])) $tmp['clicks'] = $data['clicks'];
	    if(isset($data['views'])) $tmp['views'] = $data['views'];
        if(isset($data['actives'])) $tmp['actives'] = $data['actives'];
	    if(isset($data['amount'])) $tmp['amount'] = $data['amount'];
	    if(isset($data['dsp_id'])) $tmp['dsp_id'] = $data['dsp_id'];
	    return $tmp;
	}
	
	/**
	 * 
	 * @return MobgiCharge_Dao_AdxChargeMinuteModel
	 */
	private static function _getDao() {
		return Common::getDao("MobgiCharge_Dao_AdxChargeMinuteModel");
	}
}
