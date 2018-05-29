<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-10-9 18:06:28
 * $Id: Landingpagetemplate.php 62100 2017-10-9 18:06:28Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_LandingpagetemplateModel {
    
    const CACHE_EPIRE = 3600;
    const DYNAMIC_TEMPLATE = 1;#动态模板
    const STATIC_TEMPLATE = 2;#静态模板
    
    /**
     *
     * Enter description here ...
     */
    public static function getAll() {
        return array(self::_getDao()->count(), self::_getDao()->getAll());
    }


    public static function getCountBy($params) {
        $total = self::_getDao()->count($params);
        return intval($total);
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $params
     * @param unknown_type $page
     * @param unknown_type $limit
     */
    public static function getList($page = 1, $limit = 10, $params = array(), $orderBy = array()) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $ret = self::_getDao()->getList($start, $limit, $params, $orderBy);
        $total = self::_getDao()->count($params);
        return array($total, $ret);
    }

    /**
     *
     * 查询一条结果集
     * @param array $search
     */
    public static function getBy($search) {
        return self::_getDao()->getBy($search);
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $id
     */
    public static function getsBy($params, $orderBy = array('id' => 'ASC')) {
        if (!is_array($params)) return false;
        return self::_getDao()->getsBy($params, $orderBy);
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $data
     * @param unknown_type $id
     */
    public static function update($data, $id) {
        if (!is_array($data)) return false;
        $data = self::_cookData($data);
        $data['update_time'] = Common::getTime();
        return self::_getDao()->update($data, intval($id));
    }

    public static function updateBy($data, $params) {
        if (!is_array($data) || !is_array($params)) return false;
        $data = self::_cookData($data);
        $data['update_time'] = Common::getTime();
        return self::_getDao()->updateBy($data, $params);
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $id
     */
    public static function delete($id) {
        return self::_getDao()->delete(intval($id));
    }

    /*
     *
     * @param array $param
     * @return boolean
     */
    public static function deleteBy($search) {
        return self::_getDao()->deleteBy($search);
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $data
     */
    public static function add($data) {
        if (!is_array($data)) return false;
        $data = self::_cookData($data);
        $data['create_time'] = Common::getTime();
        $data['update_time'] = Common::getTime();
        $ret = self::_getDao()->insert($data);
        if (!$ret) return $ret;
        return self::_getDao()->getLastInsertId();
    }


    /**
     *
     * @param unknown_type $data
     * @return boolean
     */
    public static function mutiFieldInsert($data) {
        if (!is_array($data)) return false;
        return self::_getDao()->mutiFieldInsert($data);
    }


    /**
     *
     * Enter description here ...
     * @param unknown_type $data
     */
    private static function _cookData($data) {
        $tmp = array();
        if (isset($data['app_id'])) $tmp['app_id'] = $data['app_id'];
        if (isset($data['name'])) $tmp['name'] = $data['name'];
        if (isset($data['url'])) $tmp['url'] = $data['url'];
        if (isset($data['zip'])) $tmp['zip'] = $data['zip'];
        if (isset($data['create_name'])) $tmp['create_name'] = $data['create_name'];
        if (isset($data['create_url'])) $tmp['create_url'] = $data['create_url'];
        if (isset($data['create_zip'])) $tmp['create_zip'] = $data['create_zip'];
        if (isset($data['type'])) $tmp['type'] = $data['type'];
        if (isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if (isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
        return $tmp;
    }

    /**
     *
     * @return Advertiser_Dao_LandingpagetemplateModel
     */
    private static function _getDao() {
        return Common::getDao("Advertiser_Dao_LandingpagetemplateModel");
    }

    public static function getFields($field = '*', $where = null) {
        return self::_getDao()->getFields($field, $where);
    }
    
}

