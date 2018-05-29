<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Advertiser_Service_PolicyConfigModel  {

    //视频
    const VIDEO = 1;
    //插页
    const PIC = 2;
    //自定义
    const CUSTOME = 3;
    //自定义
    const SPLASH = 4;
    

    const  IS_PRIORITY = 1;
    
    //默认的策略配置
    CONST APPKEY_POLICYCONFIG_FOR_DEFAULT = 1;

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
     * 查询一条结果集
     * @param array $search
     */
    public static function getBy($params) {
        if (!is_array($params)) return false;
        return self::_getDao()->getBy($params);
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
        $data['update_time'] = date('Y-m-d H:i:s');
        $data = self::_cookData($data);
        return self::_getDao()->update($data, intval($id));
    }

    public static function updateBy($data, $params) {
        if (!is_array($data) || !is_array($params)) return false;
        $data['update_time'] = date('Y-m-d H:i:s');
        $data = self::_cookData($data);
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
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');
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
        if (isset($data['name'])) $tmp['name'] = $data['name'];
        if (isset($data['config'])) $tmp['config'] = $data['config'];
        if (isset($data['del'])) $tmp['del'] = $data['del'];
        if (isset($data['admin_id'])) $tmp['admin_id'] = $data['admin_id'];
        if (isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if (isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
        return $tmp;
    }

    /**
     *
     * @return Advertiser_Dao_PolicyConfigModel
     */
    private static function _getDao() {
        return Common::getDao("Advertiser_Dao_PolicyConfigModel");
    }

}
