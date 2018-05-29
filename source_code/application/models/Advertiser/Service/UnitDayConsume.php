<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-1-10 15:59:50
 * $Id: UnitDayConsume.php 62100 2017-1-10 15:59:50Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_UnitDayConsumeModel{
    
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
	public static function getsBy($params, $orderBy = array('unit_id'=>'ASC')) {
	    if (!is_array($params)) return false;
	    return self::_getDao()->getsBy($params,$orderBy);
	}
    
	
    /**
     * 获取投放单元今日消费
     * @param type $unit_id
     * @param type $date
     * @return boolean
     */
    public static function getUnitConsumption($unit_id){
        if(empty($unit_id)) return false;
        $date = date("Ymd");
        $rediskey=  Util_CacheKey::UNIT_DAY_CONSUMPTION.'_'.$unit_id.'_'.$date;
        $cache = self::getCache();
        $redisvalue = $cache->get($rediskey);
        if($redisvalue === false){
            $dayconsumptionsInfo = self::getsBy(array('unit_id'=>$unit_id, 'date'=>$date));
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


    public static function getUnitTodayConsumeAmountList($unitIds){
		if (empty ($unitIds)) {
			return false;
		}
		$consumeAmountList = array();
		foreach ($unitIds as $unitId) {
			$amount = self::getUnitConsumption($unitId);
			$consumeAmountList [$unitId] ['amount'] = $amount;
		}
		return $consumeAmountList;
	}


	public static function getAdInfoTodayConsumeAmountList($adIds){
		if (empty ($adIds)) {
			return false;
		}
		$consumeAmountList = array();
		foreach ($adIds as $adId) {
			$amount = self::getAdInfoConsumption($adId);
			$consumeAmountList [$adId] ['amount'] = $amount;
		}
		return $consumeAmountList;
	}

	public static function getAdInfoConsumption($adId){
		if(empty($adId)) return false;
		$date = date("Ymd");
		$redisKey=  Util_CacheKey::ADINFO_DAY_CONSUMPTION.'_'.$adId.'_'.$date;
		$cache = self::getCache();
		$redisValue = $cache->get($redisKey);
		if($redisValue === false){
			$dayConsumptionInfo = self::getsBy(array('ad_id'=>$adId, 'date'=>$date));
			if(empty($dayConsumptionInfo)) return 0;
			$consumption = 0;
			foreach($dayConsumptionInfo as $key=>$item){
				$consumption+=$item['consumption'];
			}
			$cache->set($redisKey, $consumption, 600);
			return floatval($consumption);
		}else{
			return floatval($redisValue);
		}
	}


	/**
     * 获取广告今日消费(不走cache)
     * @param type $ad_id
     * @param type $date
     * @return boolean
     */
    public static function getAdConsumption($ad_id){
        if(empty($ad_id)) return false;
        $date = date("Ymd");
        $dayconsumptionsInfo = self::getsBy(array('ad_id'=>$ad_id, 'date'=>$date));
        if(empty($dayconsumptionsInfo)) return 0;
        $consumption = 0;
        foreach($dayconsumptionsInfo as $key=>$item){
            $consumption+=$item['consumption'];
        }
        return floatval($consumption);
    }
    
    /**
     * 获取广告僮今日消费(不走cache)
     * @param type $originality_id
     * @param type $date
     * @return boolean
     */
    public static function getOriginalityConsumption($originality_id){
        if(empty($originality_id)) return false;
        $date = date("Ymd");
        $dayconsumptionsInfo = self::getsBy(array('originality_id'=>$originality_id, 'date'=>$date));
        if(empty($dayconsumptionsInfo)) return 0;
        $consumption = 0;
        foreach($dayconsumptionsInfo as $key=>$item){
            $consumption+=$item['consumption'];
        }
        return floatval($consumption);
    }
    
    /**
     * 删除单元今日消费缓存
     * @param type $uid
     * @return boolean
     */
    public static function delUnitConsumption($unit_id){
        if(empty($unit_id)) return false;
        $date = date("Ymd");
        $rediskey=  Util_CacheKey::UNIT_DAY_CONSUMPTION.'_'.$unit_id.'_'.$date;
        $cache = self::getCache();
        return $cache->delete($rediskey);
    }
    
    /**
	 * 
	 * @return Advertiser_Dao_UnitDayConsumeModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_UnitDayConsumeModel");
	}
    
}

