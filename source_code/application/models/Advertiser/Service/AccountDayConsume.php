<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-1 10:18:18
 * $Id: Accountdetail.php 62100 2016-9-1 10:18:18Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_AccountDayConsumeModel{
    
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
     * 获取日消费
     * @param type $uid
     * @param type $date
     * @return boolean
     */
    public static function getTodayConsumption($uid){
        if(empty($uid)) return false;
        $date = date("Ymd");
        $rediskey=  Util_CacheKey::ACCOUNT_DAY_CONSUMPTION.'_'.$uid.'_'.$date;
        $cache = self::getCache();
        $redisvalue = $cache->get($rediskey);
        if($redisvalue === false){
            $dayconsumptionsInfo = self::getsBy(array('uid'=>$uid, 'date'=>$date));
            if(empty($dayconsumptionsInfo)) return 0;
            $consumption = 0;
            foreach($dayconsumptionsInfo as $key=>$item){
                $consumption+=$item['consumption'];
            }
            $cache->set($rediskey, $consumption, 600);
            return floatval($consumption);
        }else{
            return floatval($redisvalue);
        }
    }

	public static function getAccountTodayConsumeTotalAmountList($accountIds)
	{
		if (empty ($accountIds)) {
			return false;
		}
		$consumeAmountList = array();
		foreach ($accountIds as $val) {
			$reulst = self::getTodayConsumption($val);
			$consumeAmountList [$val] ['consumeAmount'] = $reulst;
		}
		return $consumeAmountList;
	}

    
    /**
     * 删除今日消费缓存
     * @param type $uid
     * @return boolean
     */
    public static function delTodayConsumption($uid){
        if(empty($uid)) return false;
        $date = date("Ymd");
        $rediskey=  Util_CacheKey::ACCOUNT_DAY_CONSUMPTION.'_'.$uid.'_'.$date;
        $cache = self::getCache();
        return $cache->delete($rediskey);
    }
    
    /**
	 * 
	 * @return Advertiser_Dao_AccountDayConsumeModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_AccountDayConsumeModel");
	}
    
}


