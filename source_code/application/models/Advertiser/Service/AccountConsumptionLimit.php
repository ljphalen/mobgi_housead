<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-1 10:18:18
 * Advertiser_Service_AccountConsumptionLimitModel
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_AccountConsumptionLimitModel{
    
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
        return $ret;
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
	public static function getsBy($params, $orderBy = array('uid'=>'ASC')) {
	    if (!is_array($params)) return false;
	    return self::_getDao()->getsBy($params,$orderBy);
	}



	public static function getAccountDayAmountLimitList($accountIds)
	{
		if (empty ($accountIds)) {
			return false;
		}
		$params ['uid'] = array(
			'IN',
			$accountIds
		);
		$accountInfo = self::getsBy($params);
		$totalAmountList = array();
		foreach ($accountInfo as $val) {
			$totalAmountList [$val ['uid']] ['consumeLimit'] += $val ['day_consumption_limit'];
		}
		return $totalAmountList;
	}
    
    /**
     * 获取日限额限制
     * @param type $uid
     * @return boolean
     */
    public static function getConsumptionlimit($uid){
        if(empty($uid)) return false;
        $consumptionInfo = self::getBy(array('uid'=>$uid));
        if(empty($consumptionInfo)) return false;
        return $consumptionInfo['day_consumption_limit'];
    }
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $id
	 */
	public static function update($data, $uid) {
		if (!is_array($data)) return false;
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->update($data, intval($uid));
	}
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function add($data) {
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
        if(isset($data['uid'])) $tmp['uid'] = $data['uid'];
		if(isset($data['day_consumption_limit'])) $tmp['day_consumption_limit'] = $data['day_consumption_limit'];
        if(isset($data['operator'])) $tmp['operator'] = $data['operator'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}
    
    /**
	 * 
	 * @return Advertiser_Dao_AccountConsumptionLimitModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_AccountConsumptionLimitModel");
	}
    
}


