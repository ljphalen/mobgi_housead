<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Common_Service_Factory
 * @author rock.luo
 *
 */
class Common_Service_Factory {
	static $instances;
	
	/**
	 * service factory
	 * @param string $serviceName
	 * @return object
	 */
	static public function getService($serviceName) {
		$key = md5($serviceName);
		if (isset(self::$instances[$key])) return self::$instances[$key];
		self::$instances[$key] = new $serviceName();
		return self::$instances[$key];
	}
}