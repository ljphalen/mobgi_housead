<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-9-28 19:49:23
 * $Id: AdClientWhitelist.php 62100 2017-9-28 19:49:23Z hunter.fang $
 */


if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author matt.liu
 *
 */
class Coupon_Service_CodeModel{
    
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
    public static function getList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
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


    public static function updateBy($data,$params){
        if(!is_array($params)) return false;
        return  self::_getDao()->updateBy($data,$params);
    }


    /**
     *
     * @param unknown_type $page
     * @param unknown_type $limit
     * @param unknown_type $params
     * @return multitype:unknown multitype:
     */

    public static function getBy($params = array(), $orderBy = array('id' => 'DESC')) {
        $ret = self::_getDao()->getBy($params, $orderBy);
        if (!$ret) return false;
        return $ret;

    }

    /**
     *
     * @param unknown_type $page
     * @param unknown_type $limit
     * @param unknown_type $params
     * @return multitype:unknown multitype:
     */

    public static function getsBy($params = array(), $orderBy = array('id' => 'DESC')) {
        $ret = self::_getDao()->getsBy($params, $orderBy);
        if (!$ret) return false;
        return $ret;

    }
    
    public static function deleteBy($params) {
	    return self::_getDao()->deleteBy($params);
	}

	public static function insert($data){
        return self::_getDao()->insert($data);
    }
    /**
     *
     * @return MobgiWww_Dao_UsersNonceModel
     */
    private static function _getDao() {
        return Common::getDao("Coupon_Dao_CodeModel");
    }
    
}
