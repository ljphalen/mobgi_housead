<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 *
 * 分表Dao工厂
 * @author liyf
 *
 */
class Common_Dao_SplitFactory {
    static $instances;

    /**
     * dao工厂
     *
     * @param string $serviceName
     * @return object
     */
    static public function getDao($daoName) {
        $key = md5($daoName);
    
        if (!self::$instances[$key]) {
            self::$instances[$key] = self::createInstance($daoName);
        }
    
        $obj = self::$instances[$key];
        if (is_subclass_of($obj, 'Common_Dao_Base')) {
            $obj->initAdapter();
        }
    
        return $obj;
    }
    
    static private function createInstance($daoName) {
        $cacheName = str_replace("_Dao_", "_Cache_", $daoName);
        if (@class_exists($cacheName)) {
            return new $cacheName();
        } else {
            return new $daoName();
        }
    }

}
