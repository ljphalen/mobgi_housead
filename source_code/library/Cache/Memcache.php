<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * memcache操作类
 * @author rock.luo
 *
 */
class Cache_Memcache implements Cache_Interface {
	
	protected $cache;
	protected $prefixKey = '';
	protected $version = '';
	
	/**
	 * 
	 * 构造函数
	 */
	public function __construct($cacheConfig = NULL) {
		$this->cache = new Memcache;
		if (isset($cacheConfig['host']) && isset($cacheConfig['port'])) $this->connectCache($cacheConfig);
		if (isset($cacheConfig['key-prefix'])) $this->prefixKey = $cacheConfig['key-prefix'];
		if (isset($cacheConfig['version'])) $this->version = $cacheConfig['version'];
		return $this;
	}
	
	/**
	 * 
	 * 连接memcache
	 * @param array $cacheInfo
	 */
	public function connectCache($cacheInfo) {
		$this->cache->addserver($cacheInfo['host'], $cacheInfo['port']);
	}
	
	/**
	 * 从内存中获取数据
	 * @param string $key
	 */
	public function get($key) {
		$key = $this->_getKey($key);
		return $this->cache->get($key);
	}
	
	/**
	 * 设置一个key-value到内存中
	 * 
	 */
	public function set($key, $value, $expire = 0) {
		$expire = $expire >= 2592000 ? 0 : $expire;
		$key = $this->_getKey($key);
		return $this->cache->set($key, $value, 0, $expire);
	}
	
	public function add($key, $value, $expire = 0) {
		$key = $this->_getKey($key);
		return $this->cache->add($key, $value, 0, $expire);
	}
	
	/**
	 * 
	 * 自增长
	 * @param string $key
	 * @param int $step
	 */
	public function increment($key, $step = 1) {
		$key = $this->_getKey($key);
		return $this->cache->increment($key, $step);
	}
	/**
	 * 
	 * 递减
	 * @param string $key
	 * @param int $step
	 */
	public function decrement($key, $step = 1) {
		$key = $this->_getKey($key);
		return $this->cache->decrement($key, $step);
	}
	
	/**
	 * 删除内存中键值为$key的数据
	 * 
	 */
	public function delete($key) {
		$key = $this->_getKey($key);
		return $this->cache->delete($key);
	}
	
	/**
	 * 组装键值
	 * @param string $key
	 * @return
	 */
	private function _getKey($key) {
		return $this->prefixKey . $key . ':ver:' . $this->version;
	}
	
	/**
	 * 清空内存
	 * 
	 */
	public function flush() {
		return $this->cache->flush();
	}
	
}
