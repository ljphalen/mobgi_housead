<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class CacheController extends Api_BaseController {

    private $mRefreshCacheKeyList = array(
        4 => 'HouseAdStat_Cache_StatDayModel',
        5 => 'Dedelivery_Cache_UnitConfModel',
        110 => 'MobgiApi_Cache_IntergrationAdsModel',
        111 => 'MobgiApi_Cache_IntergrationAdsConfModel',
        112 => 'MobgiApi_Cache_AdAppModel',
        113 => 'MobgiApi_Cache_AdDeverPosModel',
        115 => 'MobgiApi_Cache_IntergrationConditionFiterConfModel',
        117 => 'MobgiApi_Cache_IntergrationPolicyAreaConfModel',
        118 => 'MobgiApi_Cache_PolymericAdsModel',
        119 => 'MobgiApi_Cache_VideoAdsComModel',
        120 => 'MobgiApi_Cache_AdDeveloperModel',
    	
    );


    const TABLE_VERSION_TAG = ":ver";
    const TABLE_VERSION_NO = "no";
    const TABLE_VERSION_TIMESTAMP = "ts";

    private $versionNum = 30;
    private $versionExpireTime = 86400;


    public function refreshCacheAction() {

        $info = $this->getInput(array('type', 'sign'));
        if (!array_key_exists($info['type'], $this->mRefreshCacheKeyList)) {
            $this->output(1, 'refresh fail');
        }
        $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS_FOR_SQL);
        $key = $this->mRefreshCacheKeyList[$info['type']] . self::TABLE_VERSION_TAG;
        $version = $cache->get($key);

        if ($version[self::TABLE_VERSION_NO] < $this->versionNum) {
            $version[self::TABLE_VERSION_NO]++;
        } else {
            $version[self::TABLE_VERSION_NO] = 0;
        }
        $version[self::TABLE_VERSION_TIMESTAMP] = time();
        $cache->set($key, $version, $this->versionExpireTime);
        $this->output(0, 'refresh success');
    }
    
    public function refreshCacheNewAction() {
    
    	$info = $this->getInput(array('type', 'sign'));
    	$cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS_FOR_SQL);
    	$key = $info['type'] . self::TABLE_VERSION_TAG;
    	$version = $cache->get($key);
    	if ($version[self::TABLE_VERSION_NO] < $this->versionNum) {
    		$version[self::TABLE_VERSION_NO]++;
    	} else {
    		$version[self::TABLE_VERSION_NO] = 0;
    	}
    	$version[self::TABLE_VERSION_TIMESTAMP] = time();
    	$cache->set($key, $version, $this->versionExpireTime);
    	$this->output(0, 'refresh success');
    }

}