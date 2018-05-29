<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class MobgiApi_Service_AdsListModel{

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
	    $data['create_time'] = Common::getTime();
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
	    if(isset($data['ads_id'])) $tmp['ads_id'] = $data['ads_id'];
	    if(isset($data['name'])) $tmp['name'] = $data['name'];
	    if(isset($data['ad_sub_type'])) $tmp['ad_sub_type'] = $data['ad_sub_type'];
	    if(isset($data['out_url'])) $tmp['out_url'] = $data['out_url'];
	    if(isset($data['ad_type'])) $tmp['ad_type'] = $data['ad_type'];
	    if(isset($data['settlement_method'])) $tmp['settlement_method'] = $data['settlement_method'];
	    if(isset($data['settlement_price'])) $tmp['settlement_price'] = $data['settlement_price'];
	    if(isset($data['del'])) $tmp['del'] = $data['del'];
	    if(isset($data['is_foreign'])) $tmp['is_foreign'] = $data['is_foreign'];
	    if(isset($data['is_bid'])) $tmp['is_bid'] = $data['is_bid'];
	    if(isset($data['interface_url'])) $tmp['interface_url'] = $data['interface_url'];
	    if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
	    $tmp['update_time'] =  Common::getTime();
	    return $tmp;
	}
	

	/**
	 * 获取配置的广告商列表
	 *
	 * @param unknown $intergrationSubType
	 */
	public static  function getAdsListBySubType($adSubType) {
	    $params ['ad_type'] = 1;
	    $params ['del'] = Common_Service_Const::NOT_DELETE_FLAG;
	    $result = MobgiApi_Service_AdsListModel::getsBy ( $params );
	    $adsList = array ();
	    foreach ( $result as $val ) {
	        $arr = json_decode ( $val ['ad_sub_type'], true );
	        if (in_array ( $adSubType, $arr )) {
	            $adsList [$val ['ads_id']] = $val ['name'];
	        }
	    }
	    return $adsList;
	}
	
	
	/**
	 * 获取配置的DSP接口地址
	 */
	public static  function getDspInterFaceUrl($dspList) {
        if (empty ( $dspList )) {
            return array ();
        }
        $list = array ();
        $params ['ads_id'] = array (
                'IN',
                $dspList 
        );
        $result = MobgiApi_Service_AdsListModel::getsBy ( $params );
        if (! $result) {
            return $list;
        }
        foreach ( $result as $val ) {
            if ($val ['interface_url']) {
                $list [$val ['ads_id']] = $val ['interface_url'];
            }
        }
        return $list;
    }
    
    /**
     * 获取ｆｉｘ price DSP list
     */
    public static function getFixPriceByDspId($dspId) {
        if (empty ( $dspId )) {
            return 0;
        }
        $params ['ads_id'] = $dspId;
        $params ['del'] = Common_Service_Const::NOT_DELETE_FLAG;
        $result = MobgiApi_Service_AdsListModel::getBy ( $params );
        if (! $result) {
            return $result;
        }
        return  $result;
    }
    
    

    public static function getDspChargeType($dspId) {
        if (empty ( $dspId )) {
            return '';
        }
        $params ['ads_id'] = $dspId;
        $params ['del'] = Common_Service_Const::NOT_DELETE_FLAG;
        $result = MobgiApi_Service_AdsListModel::getBy ( $params );
        if (! $result) {
            return '';
        }
        return $result['settlement_method'];
    }
    
	
	
	/**
	 * 
	 * @return MobgiApi_Dao_AdsListModel
	 */
	private static function _getDao() {
		return Common::getDao("MobgiApi_Dao_AdsListModel");
	}
}
