<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * @author rock.luo
 *
 */
class Queue_Factory {
	static $instances;
	
	static public function getQueue($config, $instanceType='default'  ,$queueType = 'redis') {
		if(!in_array($queueType, array('Memcache', 'Redis'))) $queueType = 'Redis';
		$queueName = 'Queue_' . $queueType;
		$key = md5($queueName.json_encode($config[$instanceType]));
		if(isset(self::$instances[$key]) && is_object(self::$instances[$key])) return self::$instances[$key];
		if (!class_exists($queueName)) throw new Exception('empty class name');
		if(!isset($config[$instanceType])){
		    Util_Log::info ( __CLASS__, 'redisQueue.log', 'redis配置错误:'.$instanceType );
		    throw new Exception ( 'redis队列配置错误:'.$instanceType );
		}
		self::$instances[$key] = new $queueName($config[$instanceType]);
		return self::$instances[$key];
	}
}