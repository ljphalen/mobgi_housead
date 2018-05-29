<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Cache key constants
 *
 * @package utility
 */
class Util_CacheKey {
	/* Rules for cache hash key and field name
	 * 1. Hash key and filed name must be constant defined in this file.
	 * 2. Hash key must be module name, such as Gift, Home ...
	 */
    const CACHE_KEY_EXPRIE_ONE_DAY = 86400;
    
    const  ORIGINALITY_TODAY_DETAIL = 'dsp::';
    const  ORIGINALITY_CHARGE_PRICE ='originality_charge_price_';
    const ACCOUNT_DAY_CONSUMPTION = 'account_day_consumption';
    const UNIT_DAY_CONSUMPTION = 'unit_day_consumption';
    const ADINFO_DAY_CONSUMPTION = 'adinfo_day_consumption';
    const ACCOUNT_TOTAL_BALANCE = 'account_total_balance';
    const GDT_DIRECT_TOKEN = 'gdt_direct_token';
    const GDT_ADDAD_STEP = 'gdt_addad_step';
    const DATA_CENTER_USER_DIRECT_LABEL = 'user_direct_';
    const THIRD_API_REQUEST_URL = 'third_request_';
//    const SPM_ACTIVE_UIDMD5_CONSUMERKEY = 'spm_active_uidmd5_';
    const SPM_ACTIVE_UIDMD5_CONSUMERKEY = 'ACTIVE_IDFAMD5_CONSUMERKEY_';
//    const SPM_ACTIVE_UDIDMD5_CONSUMERKEY = 'spm_active_udidmd5_';
    const SPM_ACTIVE_UDIDMD5_CONSUMERKEY = 'ACTIVE_UDIDMD5_CONSUMERKEY_';
	const SPM_BACKFLOW_UDIDMD5_CONSUMERKEY_DATE = 'BACKFLOW_UDIDMD5_CONSUMERKEY_DATE_';
    const SPM_ACTIVE_ADIDMD5_CONSUMERKEY = 'ACTIVE_ADIDMD5_CONSUMERKEY_';
    const SPM_ACTIVITY_ID = 'ACTIVITYID_';
    //互动广告
	const INAD_ACTIVITY_QUEUE_KEY='RQ:interative_avtivity_log';



	


	/**
	 * @param array $api, such as array(Util_CacheKey::CLASS_NAME => 'Gift', Util_CacheKey::METHOD_NAME => 'myGiftList')
	 * @param string $version, such as 1.5.6, 1,5,7 ...
	 * @param $pageIndex, such as 1, 2, 3 ...
	 * @return string name of cache key, such as Gift::myGiftList_1.5.6_1
	 */
	public static function getCacheKeyForPage($api, $version = '', $pageIndex = 0) {
		if((!is_array($api)) || (!$version)) {
			return self::INVALID_KEY;
		}
		if ((!$api[self::CLASS_NAME]) || (!$api[self::METHOD_NAME])) {
			return self::INVALID_KEY;
		}

		$keyName = $api[self::CLASS_NAME] . '::' . $api[self::METHOD_NAME];
		if ($pageIndex) {
			$keyName = $keyName . self::KEY_SEPARATOR . $pageIndex;
		}
		$keyName = $keyName . self::KEY_SEPARATOR . $version;

		return $keyName;
	}
	
	public static function getCacheKeyForCommon($api, $version = ''){
		if(!is_array($api)) {
			return self::INVALID_KEY;
		}
		
		if ((!$api[self::CLASS_NAME]) || (!$api[self::METHOD_NAME])) {
			return self::INVALID_KEY;
		}
		$keyName = $api[self::CLASS_NAME] . '::' . $api[self::METHOD_NAME];
		if($version){
			$keyName = $keyName . self::KEY_SEPARATOR . $version;
		}
		return $keyName;
		
	}
	
	
	public static function getApi($className, $method) {
	    return array(Util_CacheKey::CLASS_NAME => $className, Util_CacheKey::METHOD_NAME => $method);
	}

	public static function getKey($api, $args = array()) {
	    if(!is_array($api)) {
	        return self::INVALID_KEY;
	    }
		if ((!$api[self::CLASS_NAME]) || (!$api[self::METHOD_NAME])) {
			return self::INVALID_KEY;
		}
		$keyName = $api[self::CLASS_NAME] . '::' . $api[self::METHOD_NAME];
		if($args) {
		    $keyName = $keyName . self::KEY_SEPARATOR . implode(self::KEY_SEPARATOR, $args);
		}
        return $keyName;
	}
	
	public static function getCache($api, $args) {
	    $key = self::getKey($api, $args);  
	    $cache = Cache_Factory::getCache();
	    return $cache->get($key);
	}
    
	public static function updateCache($api, $args, $data, $expireTime = 86400) {
	    $key = self::getKey($api, $args);
	    $cache = Cache_Factory::getCache();
	    $result = $cache->set($key, $data, $expireTime);
	    if(! $result) {
	        Util_Log::info('Util_CacheKey', 'cache.log', $key);
	    }
	}

	public static function deleteCache($api, $args) {
	    $key = self::getKey($api, $args);
	    $cache = Cache_Factory::getCache();
	    return $cache->delete($key);
	}

	public static function getHCache($api, $args, $key) {
	    $hash = self::getKey($api, $args);
	    $cache = Cache_Factory::getCache();
	    $data = $cache->hGet($hash, $key);
	    if ($data === false) return false;
	    return json_decode($data, true);
	}

	public static function getAllHCache($api, $args) {
	    $hash = self::getKey($api, $args);
	    $cache = Cache_Factory::getCache();
	    $list = $cache->hGetAll($hash);
	    foreach ($list as $index => $data) {
	        $list[$index] = json_decode($data, true);
	    }
	    return $list;
	}
	
	public static function updateHCache($api, $args, $key, $data, $expireTime = 86400) {
	    $hash = self::getKey($api, $args);
	    $cache = Cache_Factory::getCache();
	    $data = json_encode($data);
	    $cache->hSet($hash, $key, $data, $expireTime);
	}
	
	public static function deleteHCache($api, $args, $key) {
	    $hash = self::getKey($api, $args);
	    $cache = Cache_Factory::getCache();
	    return $cache->hDel($hash, $key);
	}

	public static function getUserInfoKey($uuid) {
		return self::USER_KEY_PREFIX . $uuid . self::USER_KEY_SUFFIX;
	}
	
}
