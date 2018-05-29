<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Advertiser_Service_OriginalityRelationPositionModel {

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


    public static function getAppList($page = 1, $limit = 10, $params = array(), $orderBy = array()) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $ret = self::_getDao()->getAppList($start, $limit, $params, $orderBy);
        $total = self::_getDao()->getAppListCount($params);
        return array($total, $ret);

    }
    /**
     * 获取
     * @param type $params
     * @return type
     */
    public static function getAppListCount($params){
        $total = self::_getDao()->getAppListCount($params);
        return $total;
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
        if (isset($data['originality_type'])) $tmp['originality_type'] = $data['originality_type'];
        if (isset($data['app_key'])) $tmp['app_key'] = $data['app_key'];
        if (isset($data['app_name'])) $tmp['app_name'] = $data['app_name'];
        if (isset($data['ad_position_key'])) $tmp['ad_position_key'] = $data['ad_position_key'];
        if (isset($data['ad_position_name'])) $tmp['ad_position_name'] = $data['ad_position_name'];
        if (isset($data['status'])) $tmp['status'] = $data['status'];
        if (isset($data['appkey_config_id'])) $tmp['appkey_config_id'] = $data['appkey_config_id'];
        if (isset($data['policy_config_id'])) $tmp['policy_config_id'] = $data['policy_config_id'];
        if (isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if (isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
        if (isset($data['is_delete'])) $tmp['is_delete'] = $data['is_delete'];
        return $tmp;
    }

//    获取关联关系
    public static function getRelationOfAppkey($param) {
        $list = self::_getDao()->getsBy($param);
        $relations = [];
        foreach ($list as $item) {
            $relations['originality_conf']['app_key'][$item['originality_id']][] = $item['app_key'];
        }
        foreach ($relations as $primaryKey => $relation) {
            foreach ($relation as $subKey => $subRelation) {
                foreach ($subRelation as $item => $value) {
                    $relations[$primaryKey][$subKey][$item] = array_values(array_unique($value));
                }
            }
        }
        return $relations;
    }


//    获取创意关联的所有appkey
    public static function getAppKey($params = array()) {
        $list = self::_getDao()->getsBy($params);
        $appkeys = [];
        foreach ($list as $item) {
            $appkeys[$item['app_key']] = $item['app_name'];
        }
        return $appkeys;
    }


//    获取创意关联的所有广告位
    public static function getBlockId($params = array()) {
        $list = self::_getDao()->getsBy($params);
        $pos_key = [];
        foreach ($list as $item) {
            $pos_key[$item['ad_position_key']] = $item['ad_position_name'];
        }
        return $pos_key;
    }




    /**
     *
     * @return Advertiser_Dao_OriginalityRelationPositionModel
     */
    private static function _getDao() {
        return Common::getDao("Advertiser_Dao_OriginalityRelationPositionModel");
    }

    public static function getFields($field = '*', $where = null) {
        return self::_getDao()->getFields($field, $where);
    }
}
