<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Dedelivery_Service_AdConfListModel{
    
    const CACHE_EPIRE = 3600;
    //投放中的状态
    const OPEN_STATUS = 1;
    
 
       
   public  static  function  getCache(){
       $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS);
       return $cache;  
   }
   
   public  static  function  getAddAdStepKey($type, $uId, $adId){
       $key =$uId.'_addADStep'.$type.'_'.$adId;
       return $key;
   }
   
   
   
   /**
    *
    * Enter description here ...
    */
   public static function getAll() {
       return array(self::_getDao()->count(), self::_getDao()->getAll());
   }
   
   
   public static function getCountBy($params){
       if (!is_array($params)) return false;
       return self::_getDao()->count($params);
   }
   
   /**
    *
    * Enter description here ...
    * @param unknown_type $params
    * @param unknown_type $page
    * @param unknown_type $limit
    */
   public static function getList($page = 1, $limit = 10, $params = array(),$orderBy = array('del'=>'ASC','status'=>'ASC','originality_type'=>'ASC')) {
       if ($page < 1) $page = 1;
       $start = ($page - 1) * $limit;
      $ret = self::_getDao()->getList($start, $limit, $params, $orderBy);
      $total = self::_getDao()->count($params);
      return array($total, $ret);
   }
    /**
     *
     * 查询一条结果集
     * @param array $search
     */
    public static function getBy($params) {
        if (!is_array($params)) return false;
        return self::_getDao()->getBy($params);
    }
    
    /**
     *
     * Enter description here ...
     * @param unknown_type $id
     */
    public static function getsBy($params,$orderBy = array('id'=>'ASC')) {
        if (!is_array($params)) return false;
        return self::_getDao()->getsBy($params,$orderBy);
    }
    
    /**
     *
     * Enter description here ...
     * @param unknown_type $data
     * @param unknown_type $id
     */
    public static function updateById($data, $id) {
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
    public static function delete($id) {
        return self::_getDao()->delete(intval($id));
    }

	public static function getAdInfoDayLimitAmountList($adList)
	{
		if (empty ($adList)) {
			return false;
		}
		$adInfoLimitList = array();
		foreach ($adList as $val) {
			if ($val ['ad_limit_type']) {
				$adInfoLimitList [$val ['id']] = floatval($val ['ad_limit_amount']);
			} else {
				$adInfoLimitList [$val ['id']] = 999999999.00;
			}
		}
		return $adInfoLimitList;
	}
    
    
    
    public static function deleteAdConfKey($key1, $key2, $key3){
        $cache = self::getCache();
        $cache->delete($key1);
        $cache->delete($key2);
        $cache->delete($key3);
    }
    
    /*
     *
     * @param array $param
     * @return boolean
     */
    public static function deleteBy($search) {
        return self::_getDao()->deleteBy($search);
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
        if(isset($data['id'])) $tmp['id'] = intval($data['id']);
        if(isset($data['originality_type'])) $tmp['originality_type'] = intval($data['originality_type']);
        if(isset($data['ad_name'])) $tmp['ad_name'] = $data['ad_name'];
        if(isset($data['ad_target_type'])) $tmp['ad_target_type'] = $data['ad_target_type'];
        if(isset($data['ad_target'])) $tmp['ad_target'] = $data['ad_target'];
        if(isset($data['package_name'])) $tmp['package_name'] = $data['package_name'];
        if(isset($data['app_name'])) $tmp['app_name'] = $data['app_name'];
        if(isset($data['jump_type'])) $tmp['jump_type'] = $data['jump_type'];
        if(isset($data['imp_trackers'])) $tmp['imp_trackers'] = $data['imp_trackers'];
        if(isset($data['click_trackers'])) $tmp['click_trackers'] = $data['click_trackers'];
        if(isset($data['unit_id'])) $tmp['unit_id'] = $data['unit_id'];
        if(isset($data['date_type'])) $tmp['date_type'] = $data['date_type'];
        if(isset($data['date_range'])) $tmp['date_range'] = $data['date_range'];
        if(isset($data['time_type'])) $tmp['time_type'] = $data['time_type'];
        if(isset($data['hour_set_type'])) $tmp['hour_set_type'] = $data['hour_set_type'];
        if(isset($data['time_range'])) $tmp['time_range'] = $data['time_range'];
        if(isset($data['time_series'])) $tmp['time_series'] = $data['time_series'];
        if(isset($data['charge_type'])) $tmp['charge_type'] = $data['charge_type'];
        if(isset($data['price'])) $tmp['price'] = $data['price'];
        if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['account_id'])) $tmp['account_id'] = $data['account_id'];
        if(isset($data['status'])) $tmp['status'] = $data['status'];
        if(isset($data['del'])) $tmp['del'] = $data['del'];
        if(isset($data['direct_id'])) $tmp['direct_id'] = $data['direct_id'];
        if(isset($data['direct_config'])) $tmp['direct_config'] = $data['direct_config'];
        if(isset($data['outer_ad_id'])) $tmp['outer_ad_id'] = $data['outer_ad_id'];
        if(isset($data['upload_info'])) $tmp['upload_info'] = $data['upload_info'];
        if(isset($data['deeplink'])) $tmp['deeplink'] = $data['deeplink'];
        if(isset($data['frequency_type'])) $tmp['frequency_type'] = $data['frequency_type'];
        if(isset($data['frequency'])) $tmp['frequency'] = $data['frequency'];
        if(isset($data['ad_sub_type'])) $tmp['ad_sub_type'] = $data['ad_sub_type'];
		if(isset($data['ad_limit_type'])) $tmp['ad_limit_type'] = $data['ad_limit_type'];
		if(isset($data['ad_limit_amount'])) $tmp['ad_limit_amount'] = $data['ad_limit_amount'];
        $tmp['update_time'] = date('Y-m-d H:i:s');
        return $tmp;
    }
    
    /**
     *
     * @return Dedelivery_Dao_AdConfListModel
     */
    private static function _getDao() {
        return Common::getDao("Dedelivery_Dao_AdConfListModel");
    }

    public static function getFields($field = '*', $where = null) {
        return self::_getDao()->getFields($field, $where);
    }

    public static function getAllByFields($field = '*', $where = null) {
        return self::_getDao()->getAllByFields($field, $where);
    }
}
