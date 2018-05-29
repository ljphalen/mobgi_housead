<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * Apc操作类
 * @author huyuke
 *
 */
class Cache_Apcu implements  Cache_Interface {
    
    const DEFAULT_EXPIRE = 60; //second
    
    private $mIsApcuInstalled = false;
    
    function __construct(){
        $backEnd = Yaf_Registry::get('backEnd');
        if(!$backEnd) {
            $this->mIsApcuInstalled = extension_loaded('apcu');
        }
    }
    
     /**
      * @param mixed $key 使用 apcu_store() 存储的键名, 
      *                   如果传递的是一个数组，则数组中的每个元素的值都被返回
      * @return mixed 失败返回false, 成功返回使用 apcu_store() 存入的值
      */
    public function get($key) {
        if(!$this->mIsApcuInstalled){
            return false;
        }
        return apcu_fetch($key);
    }

     /**
      * @param string $key
      * @param mixed  $value
      * @param int    $expire second
      * @return boolean  success true else false
      */
    public function set($key, $value, $expire = self::DEFAULT_EXPIRE) {
        if(!$this->mIsApcuInstalled){
            return false;
        }     
        return apcu_store($key, $value, $expire);
    }

     /**
      * @param string $key
      * @param int    $value
      * @return int   lastest value
      */
    public  function incrBy($key, $step = 1) {
        if(!$this->mIsApcuInstalled){
            return false;
        }
        return apcu_inc($key, $step);
    }

     /**
      * @param string $key
      * @return boolean  success true else false
      */
    public function delete($key) {
        if(!$this->mIsApcuInstalled){
            return false;
        }
        return apcu_delete($key);
    }

     /**
      * 清除用户缓存数据,暂不支持清除系统缓存
      * @return boolean  success true else false
      */
    public function flush() {
        if(!$this->mIsApcuInstalled){
            return false;
        }
        return apcu_clear_cache('user');
    }
}
