<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class MobgiApi_Service_FlowAdsRelModel{
    //配置类型，一般广告商，优先广告商，DSP广告商
    const GERNERAL_ADS = 1;
    const PRIORITY_ADS = 2;
    const DSP_ADS = 3;


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
	public static function getListGroupBy($page = 1, $limit = 10, $params = array(),$orderBy = array('update_time'=>'DESC')) {
		if ($page < 1) $page = 1;
		$start = ($page - 1) * $limit;
		$ret = self::_getDao()->getListGroupBy($start, $limit, $params, $orderBy);
		$total = self::_getDao()->getListCountGroupBy($params);
		return array($total, $ret);
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
	    $ret = self::_getDao()->insert($data);
	    if (!$ret) return $ret;
	    return self::_getDao()->getLastInsertId();
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
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
	    $tmp = array();
	    if(isset($data['id'])) $tmp['id'] = intval($data['id']);
	    if(isset($data['flow_id'])) $tmp['flow_id'] = $data['flow_id'];
	    if(isset($data['app_key'])) $tmp['app_key'] = $data['app_key'];
	    if(isset($data['ad_type'])) $tmp['ad_type'] = $data['ad_type'];
	    if(isset($data['ads_id'])) $tmp['ads_id'] = $data['ads_id'];
	    if(isset($data['conf_type'])) $tmp['conf_type'] = $data['conf_type'];
	    if(isset($data['position'])) $tmp['position'] = $data['position'];
	    if(isset($data['limit_num'])) $tmp['limit_num'] = $data['limit_num'];
	    if(isset($data['weight'])) $tmp['weight'] = $data['weight'];
	    if(isset($data['del'])) $tmp['del'] = $data['del'];
	    $tmp['update_time'] = date('Y-m-d H:i:s');
	    return $tmp;
	}
	
	/**
	 * 
	 * @return MobgiApi_Dao_FlowAdsRelModel
	 */
	private static function _getDao() {
		return Common::getDao("MobgiApi_Dao_FlowAdsRelModel");
	}
}
