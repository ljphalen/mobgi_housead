<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * RedisQ操作类
 * @author rock.luo
 *
 */

class Queue_Redis implements Queue_Base {
	
    protected $prefixKey;
    
    const DEFAULT_TIME_OUT = 5.0;
    const TAG_KEY_PREFIX = 'key-prefix';
    const TAG_PASSWORD ='password';
	/**
	 * 
	 * 自身对象
	 *
	 * @var unknown_type
	 */
	protected $mRedis;
	
	static $sRedisConnList = array();
	
	protected  $mQueueInfo;
	
	/**
	 * 
	 * 构造函数
	 * @param unknown_type $cacheInfo
	 */
	public function __construct($queueInfo = NULL) {
	    if(!is_array($queueInfo)){
	        Util_Log::info ( __CLASS__, 'redisQueue.log', 'redis配置错误' );
	        throw new Exception ( 'redis队列配置错误' );
	    }
	    try {
		  $this->mRedis = new Redis();
	    } catch ( Exception $e ) {
				Util_Log::info ( __CLASS__, 'redisQueue.log', 'redis配置错误或者服务器繁忙:' . json_encode($queueInfo) . '端口:' . json_encode($queueInfo ['port']) );
				throw new Exception ( 'redis队列配置错误或者服务器繁忙:' . json_encode($queueInfo)  . '端口:' . json_encode($queueInfo) );
		}
		if(is_array($queueInfo)){
		    $this->connectServer($queueInfo);
		}
		return $this;
	}

	/**
	 * 连接缓存服务器 
	 * 
	 * @param mixed $cacheInfo 
	 * @access public
	 * @return void
	 */
	public function connectServer($queueConfigInfo) {
	    $key = array_rand($queueConfigInfo);
	    $queueInfo = $queueConfigInfo[$key];
	    $this->mQueueInfo = $queueInfo;
		if (isset ( $queueInfo [self::TAG_KEY_PREFIX] )) {
			$this->prefixKey = $queueInfo [self::TAG_KEY_PREFIX];
		}
	    
		if(isset($queueInfo ['host']) && isset($queueInfo['port'])){
			$key = crc32($queueInfo ['host'].$queueInfo['port'].$this->prefixKey);
			if (isset(self::$sRedisConnList[$key])) {
				$this->mRedis = self::$sRedisConnList[$key];
				return;
			}
			try {
				$this->mRedis->connect ( $queueInfo ['host'], $queueInfo ['port'], self::DEFAULT_TIME_OUT );
				if(isset($queueInfo[self::TAG_PASSWORD])){
				    $this->mRedis->auth($queueInfo[self::TAG_PASSWORD]);
				}
				$this->mRedis->ping();
			} catch ( Exception $e ) {
				Util_Log::info ( __CLASS__, 'redisQueue.log', 'redis配置错误或者服务器繁忙:' . $queueInfo ['host'] . '端口:' . $queueInfo ['port'] );
				throw new Exception ( 'redis队列配置错误或者服务器繁忙:' . $queueInfo ['host'] . '端口:' . $queueInfo ['port'] );
			}
		}else{
			Util_Log::info(__CLASS__, 'redisQueue.log', 'redis队列配置有误:'.json_encode($queueInfo));
			throw new Exception('redis队列配置错误');
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
	
	/**
	 * 
	 * 入队列
	 * @param unknown_type $key
	 * @param unknown_type $value
	 */
	public function push($key, $value ,$right = true) {
	    $key = $this->_getKey($key);
		$value = is_array($value) ? json_encode($value) : $value;
		try {
		    $data = $right ? $this->mRedis->rPush($key, $value) : $this->mRedis->lPush($key, $value);
		    
		}catch ( Exception $e ) {
				Util_Log::info ( __CLASS__, 'redisQueue.log', 'redis配置错误或者服务器繁忙:' . $this->mQueueInfo ['host'] . '端口:' . $this->mQueueInfo ['port'] );
				throw new Exception ( 'redis队列配置错误或者服务器繁忙:' . $this->mQueueInfo ['host'] . '端口:' . $this->mQueueInfo ['port'] );
			}
		return $data;
	}
	
	/**
	 * 
	 * 出队列
	 * @param unknown_type $key
	 */
	public function pop($key , $left = true) {
	    $key = $this->_getKey($key);
		return $left ? $this->mRedis->lPop($key) : $this->mRedis->rPop($key);
	}
	
	/**
	 * 
	 * 去重入队列
	 * @param string $key
	 * @param string $value
	 * @param string $prefix
	 */
	public function noRepeatPush($key, $value, $prefix) {
	    $key = $this->_getKey($key);
		$ckey = $value;
		if ($this->get($ckey)) return true;
		$this->set($ckey, $value);
		return $this->push($key, $value);
	}
	
	/**
	 * 
	 * 出队列时删除cachekey
	 * @param string $key
	 * @param string $prefix
	 */
	public function noRepeatPop($key, $prefix) {
	    $key = $this->_getKey($key);
		$value = $this->pop($key);
		$ckey =  $value;
		$this->del($ckey);
		return $value;		
	}

	/**
	 * 
	 * 队列长度
	 * @param unknown_type $key
	 */
	public function len($key) {
	    $key = $this->_getKey($key);
		return $this->mRedis->llen($key);
	}

	/**
	 * 
	 * 自增长
	 * @param unknown_type $key
	 */
	public function increment($key) {
	    $key = $this->_getKey($key);
		return $this->mRedis->incr($key);
	}

	/**
	 * 
	 * 自增减
	 * @param unknown_type $key
	 */
	public function decrement($key) {
	    $key = $this->_getKey($key);
		return $this->mRedis->decr($key);
	}
	
	/**
	 * 
	 * 取出值
	 * @param unknown_type $key
	 */
	public function get($key) {
	    $key = $this->_getKey($key);
		return $this->mRedis->get($key);
	}
	/**
	 * 
	 * 返回名称为$key的list中start至end之间的元素（end为 -1 ，返回所有）
	 * @param unknown_type $key
	 * @param integer $start
	 * @param integer $length  要获取的个数，0 表示获取全部
	 */
	public function getList($key, $start, $length = 0) {
	    $key = $this->_getKey($key);
		if ($length < 0) {
			$length = 0;
		}
		$start < 0 && $start = 0;
		$end = $start + $length - 1;
		return $this->mRedis->lRange($key, $start, $end);
	}
	/**
	 * 
	 * 清空
	 */
	public function clear() {
		return $this->mRedis->flushAll();
	}
	
	/**
	 * 
	 * 设置值
	 * @param string $key
	 * @param string/array $value
	 */
	public function set($key, $value, $timeOut = 0) {
	    $key = $this->_getKey($key);
		$value = is_array($value) ? json_encode($value) : $value;
		$retRes = $this->mRedis->set($key, $value);
		if ($timeOut > 0) $this->mRedis->setTimeout($key, $timeOut);
		return $retRes;
	}
	
	/**
	 * 
	 * 删除值
	 * @param unknown_type $key
	 */
	public function del($key) {
	    $key = $this->_getKey($key);
		return $this->mRedis->delete($key);
	}
	
	public function setnx($key, $value) {
	    $key = $this->_getKey($key);
		return $this->mRedis->setnx($key, $value);
	}
	
	public function getset($key, $value) {
	    $key = $this->_getKey($key);
		return $this->mRedis->getset($key, $value);
	}
	
	public function expire($key, $second) {
	    $key = $this->_getKey($key);
		return $this->mRedis->expire($key, $second);
	}
	
	public function keys($key) {
	    $key = $this->_getKey($key);
		return $this->mRedis->keys($key);
	}
	
	
	
		
}
