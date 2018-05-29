<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * Redis操作类
 * @author rock.luo
 *
 */
class Cache_Redis implements  Cache_Interface {
	protected $readCache;
	protected $writeCache;
	protected $cacheConfig = '';
	protected $isReadWriteNotSeparate = false;
	protected $prefixKey;
	
	const TAG_READ = "read";
	const TAG_WRITE = "write";
	const TAG_HOST = "host";
	const TAG_POST = "port";
	const TAG_KEY_PREFIX = 'key-prefix';
	const TAG_PASSWORD ='password';
	
	const DEFAULT_TIME_OUT = 5.0;
	
	static $sRedisConnList = array();
	
	/**
	 * 
	 * 构造函数
	 * 
	 */
	public function __construct($cacheConfig) {
	    $this->cacheConfig = $cacheConfig;
	    if(isset($cacheConfig[self::TAG_WRITE][self::TAG_KEY_PREFIX])){	   
	        $this->prefixKey = $cacheConfig[self::TAG_WRITE][self::TAG_KEY_PREFIX];
	    }
		return $this;
	}
	
	/**
	 * 主从redis同步需要时间，所以但配置了“读写分离”时，立即读取刚修改的缓存数据，可能结果
	 * 不是最新的。
	 * 本接口就是用于应对这种场景。一旦设置为“读写不分离”后，一旦有过写缓存操作，则读将从主
	 * redis上请求。
	 * 决大多数场景不会读取刚修改的缓存数据，请在必要的场景下才调用这个接口。
	 * @param bool $isNotSeparate  true：是打开”读写不分离“开关，false：关闭开关
	 */
	public function setReadWriteNoSeparate($isNotSeparate = true) {
		$this->isReadWriteNotSeparate = $isNotSeparate;
	}
	
	private function _getCacheByTag($tagReadOrWrite = self::TAG_READ) {
		if ($this->isReadWriteNotSeparate && $this->writeCache) {
			return $this->writeCache;
		}
		
	    if (self::TAG_READ == $tagReadOrWrite) {
	        $cache = &$this->readCache;
	    }
	    else {
	        $cache = &$this->writeCache;
	    }
	    
	    if (!$cache) {
	        $this->_connectCache($cache, $tagReadOrWrite);
	    }
	     
	    return $cache;
	}
	
	private function _connectCache(&$cache, $tagReadOrWrite) {
	    $connConfig = $this->cacheConfig;
	    
	    if (isset($this->cacheConfig[$tagReadOrWrite])) {
	        $connConfig = $this->cacheConfig[$tagReadOrWrite];
	        if (self::TAG_READ == $tagReadOrWrite) {
	            $key = array_rand($connConfig);
	            $connConfig = $connConfig[$key];
	        }
	    }
	    if (isset($connConfig[self::TAG_HOST]) && isset($connConfig[self::TAG_POST])) {
	        $key = $connConfig[self::TAG_HOST]. ':' . $connConfig[self::TAG_POST];
	        if (isset(self::$sRedisConnList[$key])) {
	            $cache = self::$sRedisConnList[$key];
	            return;
	        }

	        $cache = new Redis();
	        try {	     
	            $cache->connect($connConfig[self::TAG_HOST], $connConfig[self::TAG_POST], self::DEFAULT_TIME_OUT);
	           if(isset($connConfig[self::TAG_PASSWORD])){
	               $cache->auth($connConfig[self::TAG_PASSWORD]);
	           }
	            if($cache->ping()){
	            	self::$sRedisConnList[$key] = $cache;
	            }
	        } catch (Exception $e) {
	        	Util_Log::info(__CLASS__, 'redis.log', 'redis配置错误或者服务器繁忙:'.$connConfig[self::TAG_HOST].'端口:'.$connConfig[self::TAG_POST]);
	            throw new Exception('redis配置错误或者服务器繁忙:'.$connConfig[self::TAG_HOST].'端口:'.$connConfig[self::TAG_POST]); 
	        }
	    }
	    else {
	    	Util_Log::info(__CLASS__, 'redis.log', 'redis配置有误:'.json_encode($connConfig));
	        throw new Exception('redis配置有误');
	    }
	}
	
	protected function getReadCache() {
	    return $this->_getCacheByTag(self::TAG_READ);
	}
	
	protected function getWriteCache() {
	    return $this->_getCacheByTag(self::TAG_WRITE);
	}
	
	/**
	 * 选择redis库
	 * @param unknown_type $db
	 * @return unknown
	 */
	public function select($db = 0) {
		return $this->getReadCache()->select($db);;
	}
	
	/**
	 * 从内存获取数据 
	 *
	 */
	public function get($key) {
	 
		if (!is_array($key)){
		    return $this->_getSingleValue($key);
		}else{
		    return $this->_getMutiValue($key);
		} 
	}
	
	private function _getMutiValue($key){
	    $keys = array();
	    foreach ($key as $val) {
	        $val = $this->_getKey($val);
	        $keys[] = $val;
	    }
	    $temp = $this->getReadCache()->mget($keys);
	    $result = array();
	    foreach ($temp as $val) {
	        if (!$val) continue;
	        $result[] = json_decode($val, true);
	    }
	    return $result;
	}
	
	/**
	 * 
	 * 单个key
	 * @param unknown_type $key
	 */
	private function _getSingleValue($key) {
	    $key = $this->_getKey($key);
		$temp = $this->getReadCache()->get($key);
		if ($temp === false) return false;
		return json_decode($temp, true);
	}
	
	/**
	 * 设置值到内存中
	 */
	public function set($key, $value, $expire = 0) {
	    $key = $this->_getKey($key);
		$value = json_encode($value);
		if (!$expire) $result = $this->getWriteCache()->set($key, $value);
		else $result = $this->getWriteCache()->setex($key, $expire, $value);
		return $result;
	}
	
	/**
	 * 
	 * 自增长
	 * @param unknown_type $key
	 * @param unknown_type $step
	 */
	public function increment($key, $step = 1) {
	    $key = $this->_getKey($key);
		return $this->getWriteCache()->incr($key, $step);
	}
	
	/**
	 * 
	 * 递减
	 * @param string $key
	 * @param int $step
	 */
	public function decrement($key, $step = 1) {
	    $key = $this->_getKey($key);
		return $this->getWriteCache()->decr($key, $step);
	}
	
	/**
	 * 
	 * 删除内存$key
	 */
	public function delete($key) {
	    $key = $this->_getKey($key);
		return $this->getWriteCache()->delete($key);
	}
	
	/**
	 * 
	 * 分页取列表
	 * @param unknown_type $key
	 * @param unknown_type $limit
	 * @param unknown_type $offset
	 */
	public function get_list_range($key, $limit, $offset) {
	    $key = $this->_getKey($key);
		return $this->getReadCache()->lGetRange($key, $limit, $limit + $offset - 1);
	}

	/**
	 * 清内存
	 * 
	 */
	public  function flush() {
		return $this->getWriteCache()->flushDB();
	}
	
	/**
	 * 指定key是否存在
	 * @param string $key
	 * @return 1 存在 0 不存在
	 */
	public function exists($key){
	    $key = $this->_getKey($key);
		return $this->getReadCache()->exists($key);
	}
	/**
	 * 设置key过期方法
	 * @param unknown $key
	 * @param unknown $timeout
	 */
	public function expire($key, $timeout){
	    $key = $this->_getKey($key);
		return $this->getWriteCache()->expire($key, $timeout);
	}
	
	/**
	 * 获取超时时间
	 * @param unknown $key
	 */
	public function ttl($key) {
	    $key = $this->_getKey($key);
		return $this->getReadCache()->ttl($key);
	}
	
	/**
	 * key存活到一个unix时间戳时间
	 * @param unknown $key
	 * @param unknown $time
	 */
	public function expireAt($key, $time) {
	    $key = $this->_getKey($key);
		return $this->getWriteCache()->expireAt($key, $time);
	}
	

	//检测某元素是否在序列中
	public function sismember($key,$value){
	    $key = $this->_getKey($key);
		return $this->getReadCache()->sismember($key,$value);
	}
	
	/**
	 * hash set 向名称为h的hash中添加元素
	 * @param unknown_type $h
	 * @param unknown_type $key
	 * @param unknown_type $value
	 */
	public  function hSet($h, $key, $value, $expire=0){
	    $h = $this->_getKey($h);
		$res =$this->getWriteCache()->hSet($h,$key,$value);
		if ($expire) {
		    $this->getWriteCache()->expire($h, $expire);
		}
		return $res;
	}
	

	/**
	 * hash get 名称为h的hash中key1对应的value
	 * @param unknown_type $h
	 * @param unknown_type $key
	 */
	public  function hGet($h, $key){
	    $h = $this->_getKey($h);
		return $this->getReadCache()->hGet($h, $key);
	}
	
	/**
	 * hash key 自增
	 * @param unknown_type $h
	 * @param unknown_type $key
	 */
	public  function hIncrBy($h, $key, $step = 1, $expire = 0){
	    $h = $this->_getKey($h);
	    $ret = $this->getWriteCache()->hIncrBy($h, $key, $step);
	    if ($expire) {
	    	$this->getWriteCache()->expire($h, $expire);
	    }
	    return $ret;
	}
		
	/**
	 * hash 返回名称为h的hash中所有的键（field）及其对应的value
	 * @param unknown_type $h
	 */
	public  function hGetAll($h){
	    $h = $this->_getKey($h);
		return $this->getReadCache()->hGetAll($h);
	}
	
	/**
	 * hash hMset 向名称为h的hash中添加键值队性数组元素
	 * @param unknown $h
	 * @param unknown $data
	 */
	public  function hMset($h, $data, $expire = 0){
	    $h = $this->_getKey($h);
		$res =  $this->getWriteCache()->hMset($h, $data);
		if ($expire) {
			$this->getWriteCache()->expire($h, $expire);
		}
		return $res;
	}
	
	/**
	 * hash hMget 从名为$hash的hash中返回$keys指定的字段值
	 * @param string $hash
	 * @param array $keys
	 */
	public  function hMget($h, $keys){
	    $h = $this->_getKey($h);
		return $this->getWriteCache()->hMget($h, $keys);
	}

	/**
	 * hash 返回名称为key的hash中所有键
	 * @param unknown_type $h
	 * @param unknown_type $key
	 */
	public  function hKeys($h){
	    $h = $this->_getKey($h);
		return $this->getReadCache()->hKeys($h);
	}
	
	/**
	 * 返回名称为h的hash中所有键对应的value
	 * @param unknown_type $h
	 * @param unknown_type $key
	 */
	public  function hVals($h){
	    $h = $this->_getKey($h);
		return $this->getReadCache()->hVals($h);
	}
	
	/**
	 * 删除名称为h的hash中键为key1的域
	 * @param unknown_type $h
	 * @param unknown_type $key
	 */
	public  function hDel($h, $key){
	    $h = $this->_getKey($h);
		return $this->getWriteCache()->hDel($h, $key);
	}
	
	/**
	 * 名称为h的hash中是否存在键名字为a的域
	 * @param unknown_type $h
	 * @param unknown_type $key
	 */
	public  function hExists($h, $key){
	    $h = $this->_getKey($h);
		return $this->getReadCache()->hExists($h, $key);
	}
	
	/**
	 * 获取互斥锁
	 * @param string $key
	 * @param number $timeout
	 * @return boolean   是否获得锁
	 */
	public function lock($key, $timeout = 10) {
	    $key = self::_getLockKey($key);
	    $key = $this->_getKey($key);
	    $result = $this->getWriteCache()->setnx($key, 1);
	    if ($result == 1) {
	        $this->getWriteCache()->expire($key, $timeout);
	        return true;
	    }
	    return false;
	}
	
	/**
	 * 删除互斥锁
	 * @param string $key
	 */
	public function unlock($key) {
	    $key = self::_getLockKey($key);
	    $key = $this->_getKey($key);
	    $this->getWriteCache()->delete($key);
	}
	
	/**
	 * 锁的key，以"lock:"开头
	 */
	private function _getLockKey($key) {
	    return "lock:" . $key;
	}
	
	public function listRPush($key, $value, $expire = 0) {
	    $key = $this->_getKey($key);
	    $value = json_encode($value);
	    $result = $this->getWriteCache()->rPush($key, $value);
	    if ($expire){
	        $this->getWriteCache()->expire($key, $expire);
	    }
	    return $result;
	}
	
	public function listLength($key) {
	    $key = $this->_getKey($key);
	    return $this->getReadCache()->lLen($key);
	}
	
	public function listLPop($key) {
	    $key = $this->_getKey($key);
	    $temp = $this->getWriteCache()->lPop($key);
	    return json_decode($temp, true);
	}
	
	public function listLPops($key, $count) {
	    $key = $this->_getKey($key);
	    if ($count <= 0) {
	        return false;
	    }
	    $values = $this->getReadCache()->lGetRange($key, 0, $count - 1);
	    $this->getWriteCache()->listTrim($key, $count, - 1);
	    foreach ($values as $key => $value) {
	        $values[$key] = json_decode($value, true);
	    }
	    return $values;
	}

	public function rename($oldKey, $newKey) {
	    $oldKey = $this->_getKey($oldKey);
	    $newKey = $this->_getKey($newKey);
		return $this->getWriteCache()->rename($oldKey, $newKey);
	}

	public static function ping() {
	    foreach (self::$sRedisConnList as $cache) {
	        $cache->ping();
	    }
	}
	

	/**
	 * 组装键值
	 * @param string $key
	 * @return
	 */
	private function _getKey($key) {	    
	    if($this->prefixKey){
	        return $this->prefixKey .'_'. $key;
	    }
	    return $key;
	}
	
}
