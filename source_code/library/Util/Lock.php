<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 并发锁
 * @author rock.luo
 *
 */
class Util_Lock {
	
	private $_lockStatus = true; //锁状态，是否启用锁，默认当redis失效的情况下，锁机制不启用
	private $_lockId = null;  //每个线程创建一个随机住一标识号
	public  $redisErrReturn = true;
	
	private function __construct() {
		//给每一个使用锁的线程生成一个唯一ID号，解锁和对锁延长过期时间，需要ID做判断，防止其它线程误解锁
		$this->_lockId = md5(mt_rand() . microtime(true));
	}
	
	static public function getInstance() {
		static $lock = null;
		if (!is_object($lock)) {
			$lock = new self();
		}
		return $lock;
	}

	private function __clone(){}
	
	/**
	 * 加锁  【新的加锁算法，采用redis做锁，redis失效情况下，返回 变量redisErrReturn 设定的值】
	 * @param string $key 唯一标识
	 * @param int $expire
	 * @return
	 */
	public function lock($key, $expire = 5) {
		try {
			list($key, $lockIdKey) = $this->_getLockKey($key);
			//抢锁，第一个线程抢到，并把过期时间写入锁
			if (Common::getQueue()->setnx($key, Common::getTime() + $expire)) {
				Common::getQueue()->set($lockIdKey, $this->_lockId);
				return true;
			}
			//没有抢到锁的线程，判断锁是否异常死锁，如果锁没有过期，返回false
			if (Common::getQueue()->get($key)  > Common::getTime()) return false;
			//锁因异常死锁并过期的情况下，多个并发线程再次抢锁，getset命令到过期时间，如果未过期，表示锁已被其它线程抢得，返回false
			if (Common::getQueue()->getset($key, Common::getTime() + $expire) > Common::getTime()) return false;
			Common::getQueue()->set($lockIdKey, $this->_lockId);
			return true;
		} catch (RedisException $e) {
			// 当捕捉到redis异常时，锁中断，返回变量redisErrReturn
			$this->_lockStatus = false;
			return $this->redisErrReturn;
		}
	}
	
	/**
	 * 给锁进行续期，延长锁的生效周期
	 * @param string $key
	 * @param int $expire       延时锁失效的秒数
	 * @param int $triggerTime  触发续期倒计时间
	 * @return
	 */
	public function updateExpire($key, $expire = 3, $triggerTime = 2) {
		if (!$this->_lockStatus) return true; //当redis失效，中断锁
		list($key, $lockIdKey) = $this->_getLockKey($key);
		$lockId = Common::getQueue()->get($lockIdKey);
		if ($lockId != $this->_lockId) return false;
		$time = Common::getQueue()->get($key);
		if ($time - $triggerTime <= Common::getTime()) return Common::getQueue()->set($key, $time + $expire);
	}
	
	/**
	 * 解锁
	 * @param string $key
	 * @return
	 */
	public function unlock($key) {
		
		if (!$this->_lockStatus) return true; //当redis失效，中断解锁
		list($key, $lockIdKey) = $this->_getLockKey($key);
		$lockId = Common::getQueue()->get($lockIdKey);
		if ($lockId != $this->_lockId) return false;
		Common::getQueue()->del($lockIdKey);
		Common::getQueue()->del($key);
		return true;
	}
	
	/**
	 * 清除死锁产生的无用key 默认清除24小时以前的无用key
	 * @param int $cleanTime  默认24小时
	 */
	public function cleanLockKey($cleanTime = 86400) {
		if ($cleanTime < 3600 || !$keys = Common::getQueue()->keys('PwLock:*')) return false;
		$i = 0;
		foreach ($keys as $value) {
			list($time, $lockId) = explode('|', Common::getQueue()->get($value));
			if (Common::getTime() - $time > $cleanTime) {
				if (Common::getQueue()->del($value)) $i++;
			}
		}
		return $i;
	}
	
	/**
	 * 加锁后执行一个回调函数
	 * @param string $key
	 * @param array $callback
	 * @param array $parameter
	 * @param int $expire
	 * @return
	 */
	public function lockCallback($key, $callback, $parameter, $expire = '') {
		if ($this->lock($key, $expire) === false) return false;
		$return = call_user_func_array($callback, $parameter);
		$this->unlock($key);
		return $return;
	}
	
	/**
	 * 采用管道方式进行加锁，限制同时进行某个流程的并发数
	 * @param string $name
	 * @param int $max
	 * @param int $delay
	 * @return
	 */
	public function queueLock($name, $delay = 5) {
		$beanstalk = Common::getBeanstalkHandle();
		$beanstalk->watch($name);
		$return = false;
		$job = $beanstalk->reserve_with_timeout();
		if ($job['id'] > 0) $return = $beanstalk->release($job['id'], 1024, $delay);
		$beanstalk->ignore($name);
		return $return;
	}
	
	private function _getLockKey($key) {
		return array('Lock:' . $key, 'Lock:' . $key . ':lockId');
	}
}
