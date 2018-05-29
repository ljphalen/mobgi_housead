<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Advertiser_Service_OriginalityConfModel {


//    获取创意keyval
    public static function getOriginality($params = array()) {
        $list = self::getsBy($params, $orderBy = array('title' => 'ASC'));
        $originality = [];
        foreach ($list as $item) {
            $originality[$item['id']] = $item['title'];
        }

        return $originality;
    }

//    获取创意类型
    public static function getOriginalityType($params = array()) {
        $originalityType =Common::getConfig('deliveryConfig', 'originalityType');
        return $originalityType;
    }

//    获取创意子类型
    public static function getAdSubType($params = array()) {
        $adSubType =Common::getConfig('deliveryConfig', 'adSubType');
        return $adSubType;
    }


    /**
     *
     * Enter description here ...
     */
    public static function getAll() {
        return array(self::_getDao()->count(), self::_getDao()->getAll());
    }


    public static function getSearchByPageLeftJoin($table, $on, $page = 1, $limit = 10, $sqlWhere = 1, $orderBy = array(), $field = '*') {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $ret = self::_getDao()->searchByLeftJoin($table, $on, $start, $limit, $sqlWhere, $orderBy, $field);
        $total = self::_getDao()->searchCountLeftJoin($table, $on, $sqlWhere);
        return array($total, $ret);

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
        if (isset($data['title'])) $tmp['title'] = $data['title'];
        if (isset($data['originality_type'])) $tmp['originality_type'] = $data['originality_type'];
        if (isset($data['ad_target_type'])) $tmp['ad_target_type'] = $data['ad_target_type'];
        if (isset($data['charge_type'])) $tmp['charge_type'] = $data['charge_type'];
        if (isset($data['upload_content'])) $tmp['upload_content'] = $data['upload_content'];
        if (isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if (isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
        if (isset($data['is_delete'])) $tmp['is_delete'] = $data['is_delete'];
        return $tmp;
    }

    /**
     *
     * @return Advertiser_Dao_OriginalityConfModel
     */
    private static function _getDao() {
        return Common::getDao("Advertiser_Dao_OriginalityConfModel");
    }

    public static function getFields($field = '*', $where = null) {
        return self::_getDao()->getFields($field, $where);
    }

}
