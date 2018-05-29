<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class Util_Api_Cache {

	const MINUTE = 60;
	const HOUR = 3600;
	const DAY = 86400;
	const WEEK = 604800;
	
	private static $_PREFIX = "controll";
	private static $_KEY_SEPARATOR = '_';
	private static $_CONTROLL_EXPIRE = 86400;
	
	public static function getValidKeys($api) {
        $controllValue = self::getControllValue($api);
        $deleteKeys = array();
        $result = array();
	    foreach ($controllValue as $key => $params) {
	        if($params['expire'] <= time()) {
	            $deleteKeys[$key] = $params;
	        }else{
	            $result[$key] = $params['args'];
	        }
	    }
	    if($deleteKeys) {
            $cache = Cache_Factory::getCache();
	        $controllKey = self::getControllKey($api);
	        foreach ($deleteKeys as $key => $params) {
	            $args = $params['args'];
                $cache->hDel($controllKey, $key);
	            Util_CacheKey::deleteCache($api, $args);
	        }
	    }
	    return $result;
	}
	
	private static function getControllKey($api) {
	    $class = $api[Util_CacheKey::CLASS_NAME];
	    $method = $api[Util_CacheKey::METHOD_NAME];
        $key = self::$_PREFIX . self::$_KEY_SEPARATOR . $class . self::$_KEY_SEPARATOR . $method;
	    return $key;
	}
	
	private static function getControllValue($api) {
        $controllKey = self::getControllKey($api);
        $cache = Cache_Factory::getCache();
        $controllValue = $cache->hGetAll($controllKey);
	    foreach ($controllValue as $key => $params) {
	        $controllValue[$key] = json_decode($params, true);
	    }
        return $controllValue;
	}

	public static function existsControllValueKey($api, $key) {
        $controllKey = self::getControllKey($api);
	    $cache = Cache_Factory::getCache();
	    return $cache->hExists($controllKey, $key);
	}
	
	public static function saveControllValueKey($api, $args, $expireTime) {
	    /**key =>  array(expire   args)*/
	    $controllKey = self::getControllKey($api);
        $dataKey = Util_CacheKey::getKey($api, $args);
	    $dataValue = array(
	        'expire' => time() + $expireTime,
	        'args' => $args,
	    );
	    $dataValue = json_encode($dataValue);
	    $cache = Cache_Factory::getCache();
	    $cache->hSet($controllKey, $dataKey, $dataValue, self::$_CONTROLL_EXPIRE);
	}
	
	
	
	
	public static function deleteCache($api) {
        $key = self::getControllKey($api);
        $cache = Cache_Factory::getCache();
        $cache->delete($key);
	}
	
	public static function getCache($api, $args) {
	    $dataKey = Util_CacheKey::getKey($api, $args);
        if (! $dataKey) {
            return false;
        }
        if (! self::existsControllValueKey($api, $dataKey)) {
            return false;
        }
        return Util_CacheKey::getCache($api, $args);
	}
	
	public static function updateCache($api, $args, $cacheData, $expireTime = self::DAY) {
        $dataKey = Util_CacheKey::getKey($api, $args);
        if (! $dataKey) {
            return false;
        }
        if(empty($cacheData) && is_array($cacheData)) {
            $cached = Util_CacheKey::getCache($api, $args);
            if(is_array($cached) && $cacheData == $cached) {
                return true;
            }
        }
        self::saveControllValueKey($api, $args, $expireTime);
        Util_CacheKey::updateCache($api, $args, $cacheData, $expireTime);
        return true;
	}
	
}
