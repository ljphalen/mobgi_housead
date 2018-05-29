<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-1 10:18:18
 * $Id: Accountdetail.php 62100 2016-9-1 10:18:18Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_AccountDetailModel{
    
    /**
     * 自定义使用cache
     * @return type
     */
    public  static  function  getCache(){
       $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS);
       return $cache;  
    }
    
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
	
    /**
     * 获取指定帐号的总余额
     * @param type $uid
     * @return boolean
     */
    public static function getTotalbalance($uid){
        if(empty($uid)) return false;
        $rediskey=  Util_CacheKey::ACCOUNT_TOTAL_BALANCE.'_'.$uid;
        $cache = self::getCache();
        $redisvalue = $cache->get($rediskey);
        if($redisvalue === false){
            $accountdetailinfo = self::getsBy(array('uid'=>$uid));
            if(empty($accountdetailinfo)) return false;
            $totalbalance=0;
            foreach($accountdetailinfo as $account){
                $totalbalance += $account['balance'];
            }
            $cache->set($rediskey, $totalbalance, 600);
            return floatval($totalbalance);
        }else{
            return floatval($redisvalue);
        }
    }


	public static function getAccountAmountList($accountIds)
	{
		if (empty ($accountIds)) {
			return false;
		}
		$totalAmountList = array();
		foreach ($accountIds as $accountId) {
			$totalBalance = self::getTotalbalance($accountId);
			$totalAmountList [$accountId] ['totalBalance'] = $totalBalance;
		}
		return $totalAmountList;
	}
    
    /**
     * 删除用户余额缓存
     * @param type $uid
     * @return boolean
     */
    public static function delTotalbalanceCache($uid){
        if(empty($uid)) return false;
        $rediskey=  Util_CacheKey::ACCOUNT_TOTAL_BALANCE.'_'.$uid;
        $cache = self::getCache();
        return $cache->delete($rediskey);
    }
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addAccountdetail($data) {
		if (!is_array($data)) return false;
		$data['create_time'] = Common::getTime();
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
        return $ret = self::_getDao()->insert($data);
	}


	/**
	 *
	 * @param type $data
	 * @param type $params
	 * @return boolean
	 */
	public static function updateAccountdetailBy($data, $params) {
		if (!is_array($data)) return false;
		if (!is_array($params)) return false;
		$data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->updateBy($data, $params);
	}


	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
		$tmp = array();
		if(isset($data['uid'])) $tmp['uid'] = $data['uid'];
		if(isset($data['account_type'])) $tmp['account_type'] = $data['account_type'];
        if(isset($data['balance'])) $tmp['balance'] = $data['balance'];
		if(isset($data['create_time'])) $tmp['create_time'] = json_encode ($data['create_time']);
		if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}
    
    /**
	 * 
	 * @return Advertiser_Dao_AccountDetailModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_AccountDetailModel");
	}

}


