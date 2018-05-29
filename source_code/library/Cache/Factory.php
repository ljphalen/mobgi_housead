<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * 缓存工厂
 * @author rock.luo
 *
 */
class Cache_Factory {
    
    static $sCacheInstances;

    const ID_REMOTE_REDIS = 1;
    const ID_LOCAL_APCU = 2;
    const ID_REMOTE_MEMCACHE = 3;
    const ID_REMOTE_REDIS_FOR_SQL = 4;
    
    const NAME_REDIS = 'Cache_Redis';
    const NAME_MEMCACHE = 'Cache_Memcache';
    const NAME_APCU = 'Cache_Apcu';

    private static $sIdToName = array(
                                   self::ID_REMOTE_REDIS => self::NAME_REDIS,
                                   self::ID_LOCAL_APCU => self::NAME_APCU,
                                   self::ID_REMOTE_MEMCACHE => self::NAME_MEMCACHE,
                                   self::ID_REMOTE_REDIS_FOR_SQL => self::NAME_REDIS
                                  );

    private static $sIdToConfig = array(
                                   self::ID_REMOTE_REDIS => 'redisConfig',
                                   self::ID_REMOTE_REDIS_FOR_SQL => 'redisForSqlConfig'
                                  );
    /**
     * 
     * 统一获取cache接口
     * @param string $which: unique identification of Cache
     * @return Cache_Redis | Cache_Apcu
     */
    static public function getCache($which = self::ID_REMOTE_REDIS,  $resource = 'default') {
        if(!array_key_exists($which, self::$sIdToName)) {
            $which = self::ID_REMOTE_REDIS;
        }

        $cacheName = self::$sIdToName[$which];
        $config = array();
        if(array_key_exists($which, self::$sIdToConfig)) {
            $configFile = self::$sIdToConfig[$which];      
            $config = Common::getConfig($configFile);   
        }
        if($which == self::ID_LOCAL_APCU){
            $cacheId = md5($cacheName);
        }else{
            $cacheId = md5($cacheName . json_encode($config[$resource]));
        }
       
        $instanceExists = (isset(self::$sCacheInstances[$cacheId]) &&is_object(self::$sCacheInstances[$cacheId])) ? true : false;
        if($instanceExists) {
            return self::$sCacheInstances[$cacheId];
        }

        if (!class_exists($cacheName)) {
            throw new Exception('empty class name');
        }
        if($which == self::ID_LOCAL_APCU){
            self::$sCacheInstances[$cacheId] = new $cacheName();
        }else{
           if(!isset($config[$resource])){
               Util_Log::info(__CLASS__, 'redis.log', 'redis配置有误:'.$resource.'配置为：'.json_encode($config));
               throw new Exception('redis配置有误:'.$resource);
           }
            self::$sCacheInstances[$cacheId] = new $cacheName($config[$resource]);
        }
        return self::$sCacheInstances[$cacheId];
    }
}