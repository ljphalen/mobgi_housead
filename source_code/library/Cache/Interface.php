<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * cache基础接口
 * @author rock.luo
 *
 */
 interface Cache_Interface {
 	public function get($key);
 	public function set($key, $value, $expire = 0);
 	public function delete($key);
 	public function flush();
 }