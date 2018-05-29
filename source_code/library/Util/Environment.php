<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Util_Environment{

    public static function isOnline() {
        return self::isEnvFor('product');
    }

    public static function isTest() {
        return self::isEnvFor('test');
    }

    public static function isDevelop() {
        return self::isEnvFor('develop');
    }

    private static function isEnvFor($envName) {
        if (!defined('ENV')) {
            return true;
        }
        return (ENV == $envName) ? true : false;
    }
}
