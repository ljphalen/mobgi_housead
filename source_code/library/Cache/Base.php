<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * @author rock.luo
 *
 */
abstract class Cache_Base {
    public $resource = 'default';
    public $expire = 30;
    public $version_num = 30;
    public $version_expire_time = 86400;
    public $mDao = null;

    public static $sWriteMethods = array(
        "insert",
        "mutiInsert",
        "mutiFieldInsert",
        "update",
        "updates",
        "updateBy",
        "replace",
        "increment",
        "delete",
        "deletes",
        "deleteBy",
    	"updateByKey"
    );

    public static $sNotCacheMethods = array(
        "getLastInsertId",
        "getSearchByPageInnerJoin",
        "getSearchByPageLeftJoin",
        "searchByLeftJoin",
        "searchCountLeftJoin",
        "searchByInnerJoin",
        "searchCountInnerJoin",
    );

    const TABLE_VERSION_TAG = ":ver";
    const TABLE_VERSION_NO = "no";
    const TABLE_VERSION_TIMESTAMP = "ts";

    const CACHE_TIMESTAMP = "ts";
    const CACHE_DATA = "data";
    const CACHE_VERSION = "ver";
    const CACHE_SOURCE = "src";

    const LOCK_TAG = ":lock";

    /**
     *
     * @param unknown_type $method
     * @param unknown_type $args
     * @return mixed
     */
    public function __call($method, $args) {
        if ($this->isWriteOperation($method)) {
            $result = call_user_func_array(array($this->_getDao(), $method), $args);
            if ($result) {
            	Common_Service_Base::recordRefreshFunc($this, revertVersion);
                $this->revertVersion();
            }
            return $result;
        }

        if ($this->isCacheOperation($method)) {
            return $this->_cacheData($method, $args, $this->expire);
        }
        return call_user_func_array(array($this->_getDao(), $method), $args);
    }

    private function isWriteOperation($method) {
        if (self::inArray($method, self::$sWriteMethods)) {
            return true;
        }
        return false;
    }

    private function isCacheOperation($method) {
        if (self::inArray($method, self::$sNotCacheMethods)) {
            return false;
        }
        return true;
    }

    private function inArray($value, $array) {
        $value = strtolower($value);
        foreach ($array as $item) {
            if (strtolower($item) == $value) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @return unknown
     */
    public function _getDao() {
        $daoName = str_replace("_Cache_", "_Dao_", get_class($this));

        if (!$this->mDao) {
            $this->mDao = new $daoName;
        }

        if (method_exists($this->mDao, "initAdapter")) {
            $this->mDao->initAdapter();
        }

        return $this->mDao;
    }

    /**
     *
     * 处理高并发
     * @param string $method
     * @param array $params
     */
    protected function _cacheData($method, $params = array(), $expire = 30) {
        $cacheKey = self::_getKey($method, $params);
        $cache = self::_getCache()->get($cacheKey);
        $tableVersion = $this->getVersion();

        if ($cache && ($cache[self::CACHE_TIMESTAMP]  + $expire - time() < 10)) {
            if (!self::_getCache()->get($cacheKey . self::LOCK_TAG)) {
                self::_getCache()->set($cacheKey . self::LOCK_TAG, true, 2);
                unset($cache);
            }
        }
        if (!$cache || $cache[self::CACHE_VERSION] != $tableVersion) {
            $dbData = call_user_func_array(array($this->_getDao(), $method), $params);
            if (!$dbData) {
            	$dbData = ($dbData == 0) ? 0 : array();
            }

            $cache[self::CACHE_TIMESTAMP] = time();
            $cache[self::CACHE_VERSION] = $tableVersion;
            $cache[self::CACHE_SOURCE] = get_class($this) . ':' . $method;
            $cache[self::CACHE_DATA] = $dbData;

            try {
                self::_getCache()->set($cacheKey, $cache, $expire);
                self::_getCache()->delete($cacheKey . self::LOCK_TAG);
            } catch(Exception $e) {

            }
        }

        return $cache[self::CACHE_DATA];
    }

    /**
     *
     * @param unknown_type $method
     * @param unknown_type $params
     * @return string
     */
    protected function _getKey($method, $params) {
        $className = get_class($this);
        $prefix = str_replace("_Cache_", ":", get_class($this));
        //$prefix = end(explode('_', $className));
        if (strlen($prefix) > 64) {
            $prefix = dechex(crc32($prefix));
        }

        $methodName = $method;
        if (strlen($methodName) > 32) {
            $methodName = dechex(crc32($methodName));
        }

        $crcStr = $className . json_encode($params);
        $crcStr = dechex(crc32(json_encode($crcStr)));

        $key =  $prefix . ':' . $methodName . ':' . $crcStr;
        return $key;
    }

    /**
     *
     */
    public function revertVersion() {
        $key = get_class($this) . self::TABLE_VERSION_TAG;
        $version = self::_getCache()->get($key);
        if ($version[self::TABLE_VERSION_NO] < $this->version_num) {
            $version[self::TABLE_VERSION_NO]++;
        }
        else {
            $version[self::TABLE_VERSION_NO] = 0;
        }

        $version[self::TABLE_VERSION_TIMESTAMP] = time();
        self::_getCache()->set($key, $version, $this->version_expire_time);
    }

    /**
     *
     */
    protected function getVersion() {
        $key = get_class($this) . self::TABLE_VERSION_TAG;
        return self::_getCache()->get($key);
    }

    /**
     * @return Cache_Redis
     */
    protected  function _getCache() {
        return Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS_FOR_SQL,$this->resource);
    }
}
