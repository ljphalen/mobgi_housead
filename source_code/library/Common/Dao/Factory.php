<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Common_Dao_Factory {
    static $instances = array();


    /** dao工厂
     * @param $daoName
     * @return Common_Dao_Base
     */
    static public function getDao($daoName) {


        $key = md5($daoName);
        if (!array_key_exists($key, self::$instances)) {
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
        if (self::checkCacheFileIsExist($cacheName)) {
            return new $cacheName();
        } else {
            return new $daoName();
        }
    }

    static private function checkCacheFileIsExist($cacheName) {
        if (empty($cacheName)) return false;
        $modelsPath = Yaf_Application::app()->getConfig()->application->models;
        $path = str_replace("_", DIRECTORY_SEPARATOR, $cacheName);
        $path = str_replace("Model", '.php', $path);
        $filename = $modelsPath . DIRECTORY_SEPARATOR . $path;
        return file_exists($filename);

    }

}
