<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-7-6 10:40:52
 * $Id: IntergrationWhitelist.php 62100 2017-7-6 10:40:52Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class MobgiApi_Service_IntergrationWhitelistModel{
    
    const  CACHE_WHITELIST_KEY = 'ad_whitelist_platform_userid';
    const  CACHE_EXPRIE             = 36000;
    
    /**
     * 自定义使用cache
     * @return type
     */
    public  static  function  getCache(){
       $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS);
       return $cache;
    }
    
    /**
	 * 
	 * Enter description here ...
	 */
	public static function getAll() {
		return array(self::_getDao()->count(), self::_getDao()->getAll());
	}
	
	
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $params
	 * @param unknown_type $page
	 * @param unknown_type $limit
	 */
	public static function getList($page = 1, $limit = 10, $params = array(),$orderBy = array('id'=>'DESC')) {
	    if ($page < 1) $page = 1;
	    $start = ($page - 1) * $limit;
	    $ret = self::_getDao()->getList($start, $limit, $params, $orderBy);
	    $total = self::_getDao()->count($params);
	    return array($total, $ret);
	}
	
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $id
	 */
	public static function getByID($id) {
	    if (!intval($id)) return false;
	    return self::_getDao()->get(intval($id));
	}
	
	
	/**
	 *
	 * @param unknown_type $page
	 * @param unknown_type $limit
	 * @param unknown_type $params
	 * @return multitype:unknown multitype:
	 */
	
	public static function getBy($params = array(),$orderBy = array('id'=>'DESC')){
	    $ret = self::_getDao()->getBy($params, $orderBy);
	   if(!$ret) return false;
	    return $ret;
	
	}
	
	/**
	 *
	 * @param unknown_type $page
	 * @param unknown_type $limit
	 * @param unknown_type $params
	 * @return multitype:unknown multitype:
	 */
	
	public static function getsBy($params = array(),$orderBy = array('id'=>'DESC')){
	    $ret = self::_getDao()->getsBy($params, $orderBy);
	    if(!$ret) return false;
	    return $ret;
	
	}
    
    public static function getIsWhiteList($imei_idfa, $platform){
        
    }
    
    /**
     * 
     * @param type $platform
     * @param type $imei_idfa
     * @return type
     */
    public static function getWhitelistFromCache($platform, $imei_idfa){
        $rediskey =self::getWhitelistKey($platform, $imei_idfa);
        $cache = self::getCache();
        $redisvalue = $cache->get($rediskey);
        return $redisvalue;
    }
    
    /**
     * 
     * @param type $platform
     * @param type $imei_idfa
     * @return type
     */
    private static function getWhitelistKey($platform, $imei_idfa){
        return self::CACHE_WHITELIST_KEY. '_'. $platform."_". $imei_idfa;
    }
    
    /**
	 * 
	 * @return MobgiApi_Dao_IntergrationWhitelistModel
	 */
	private static function _getDao() {
		return Common::getDao("MobgiApi_Dao_IntergrationWhitelistModel");
	}
    
    /**
     * 删除白名单缓存
     * @param type $platform
     * @param type $imei_idfa
     */
    public static function delWhitelistCache($platform, $imei_idfa){
        $key = self::getWhitelistKey($platform, $imei_idfa);
        $cache = self::getCache();
        $cache->delete($key);
    }
    
    /**
     * 设置白名单缓存
     * @param type $platform
     * @param type $imei_idfa
     */
    public static function setWhitelistCache($platform, $imei_idfa){
        $key = self::getWhitelistKey($platform, $imei_idfa);
        $data =  self::getBy(array('imei_idfa'=>$imei_idfa, 'platform'=>$platform));
        if ($data){
            $cache = self::getCache();
            $cache->set($key, $data, self::CACHE_EXPRIE);
        }
    }
    
    /**
     *
     * Enter description here ...
     * @param unknown_type $data
     * @param unknown_type $id
     */
    public static function updateByID($data, $id) {
        if (!is_array($data)) return false;
        $data = self::_cookData($data);
        return self::_getDao()->update($data, intval($id));
    }

    public static function updateBy($data, $params) {
        if (!is_array($data) || !is_array($params)) return false;
        $data = self::_cookData($data);
        return self::_getDao()->updateBy($data, $params);
    }
    
    /**
	 *
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function add($data) {
	    if (!is_array($data)) return false;
	    $data = self::_cookData($data);
	    $data['createtime'] = Common::getTime();
	    $ret = self::_getDao()->insert($data);
	    if (!$ret) return $ret;
	    return self::_getDao()->getLastInsertId();
	}
    
    /**
     *
     * Enter description here ...
     * @param unknown_type $data
     */
    private static function _cookData($data) {
        $tmp = array();
        if (isset($data['id'])) $tmp['id'] = intval($data['id']);
        if (isset($data['platform'])) $tmp['platform'] = $data['platform'];
        if (isset($data['device_name'])) $tmp['device_name'] = $data['device_name'];
        if (isset($data['imei_idfa'])) $tmp['imei_idfa'] = $data['imei_idfa'];
        if (isset($data['config'])) $tmp['config'] = $data['config'];
        if (isset($data['isReport'])) $tmp['isReport'] = $data['isReport'];
        if (isset($data['devMode'])) $tmp['devMode'] = $data['devMode'];
        if (isset($data['del'])) $tmp['del'] = $data['del'];
        $tmp['updatetime'] =  Common::getTime();
        return $tmp;
    }
    
}
