<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-3 18:51:08
 * $Id: Usernonce.php 62100 2016-9-3 18:51:08Z hunter.fang $
 */
if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_UsernonceModel{
	static private $hash = 'xysoza'; //hash值
	static private $sessionTime = 3600;
	static private $sessionName = 'AdvertiserUser';
    
    /**
	 *
	 * 查询一条结果集
	 * @param array $search
	 */
	public static function getBy($params, $orderBy= array()) {
	    if (!is_array($params)) return false;
	    return self::_getDao()->getBy($params, $orderBy);
	}

	/**
	 * 
	 * Enter description here ...
	 */
	public static function getNonceByEmail($email, $orderBy= array()) {
		if (!$email) return false;
		return self::_getDao()->getBy(array('email'=>$email), $orderBy);
	}
    
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function deleteNonce($id) {
		return self::_getDao()->delete(intval($id));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addNonce($data) {
		if (!is_array($data)) return false;
		$data['create_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->insert($data);
	}

    /**
     * 
     * @param type $id
     * @return type
     */
    public static function getNonce($id){
        return self::_getDao()->get($id);
    }
    
    /**
     * 批删除
     * @param type $params
     * @return boolean
     */
    public static function deleteBy($params){
        if(empty($params)){
            return false;
        }
        return self::_getDao()->deleteBy($params);
    }

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
		$tmp = array();
		if(isset($data['email'])) $tmp['email'] = $data['email'];
        if(isset($data['nonce'])) $tmp['nonce'] = $data['nonce'];
		if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
		return $tmp;
	}
	
	/**
	 * 
	 * @return Advertiser_Dao_UserModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_UsernonceModel");
	}
}



