<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * @author rock.luo
 *
 */
interface Queue_Base {
	public function push($key, $value);
	public function pop($key);
	/**
	 * 
	 * 返回名称为$key的list中start至end之间的元素（end为 -1 ，返回所有）
	 * @param unknown_type $key
	 * @param integer $start
	 * @param integer $length  要获取的个数，0 表示获取全部
	 */
	public function getList($key, $start, $length=0);
	/**
	 * 
	 * 去重入队列
	 * @param string $key
	 * @param string $value
	 * @param string $prefix
	 */
	public function noRepeatPush($key, $value, $prefix);
	/**
	 * 
	 * 出队列时删除cachekey
	 * @param string $key
	 * @param string $prefix
	 */
	public function noRepeatPop($key, $prefix);
	/**
	 * 
	 * 队列长度
	 * @param unknown_type $key
	 */
	public function len($key);
	/**
	 * 
	 * 取出值
	 * @param unknown_type $key
	 */
	public function get($key);
	/**
	 * 
	 * 清空
	 */
	public function clear();
	/**
	 * 
	 * 设置值
	 * @param string $key
	 * @param string/array $value
	 */
	public function set($key, $value, $timeOut = 0);
	/**
	 * 
	 * 删除值
	 * @param unknown_type $key
	 */
	public function del($key);
}