<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class MobgiApi_Service_AdAppModel {

    const  OPEN_STATUS = 1;
    const  CLOSE_STATUS = 0;
    const  ISCHECK_PASS = 1;
    const  ISCHECK_NOT_PASS = -1;
    const  ISCHECKING = 2;
    const APP_TYPE = [1=>'休闲游戏',2=>'独立游戏',3=>'联盟流量'];
    
    

     public static function getAppKeysByName($appName){
     	$appRelsult = MobgiApi_Service_AdAppModel::getsBy( array('app_name'=>array('LIKE', trim($appName))));
     	$appKeys1 = array_keys(Common::resetKey($appRelsult, 'app_key'));
     	$appRelsult = MobgiApi_Service_AdAppModel::getsBy( array('app_key'=>array('LIKE', trim($appName))));
     	$appKeys2 =  array_keys(Common::resetKey($appRelsult, 'app_key'));;
     	if($appKeys1 && $appKeys2){
     	   return  array_unique(array_merge($appKeys1, $appKeys2));
     	}
     	if (!empty($appKeys1)){
     		return $appKeys1;
     	}
     	if(!empty($appKeys2)){
     		return $appKeys2;
     	}
     	return false;
     }    

    /**
     *
     * Enter description here ...
     */
    public static function getAll() {
        return array(self::_getDao()->count(), self::_getDao()->getAll());
    }
    
    public static function getCount($params){
    	if (!is_array($params)) return false;
    	return self::_getDao()->count($params);
    }



    /**
     *
     * Enter description here ...
     * @param unknown_type $params
     * @param unknown_type $page
     * @param unknown_type $limit
     */
    public static function getList($page = 1, $limit = 10, $params = array(), $orderBy = array('app_id' => 'DESC')) {
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


    /*
     * 获取创意关联的所有appkey
     */

    public static function getAppKey($params = array()) {
        $list = self::_getDao()->getFields('app_key,app_name');
         return $list;
    }


    /**
     *
     * @param unknown_type $page
     * @param unknown_type $limit
     * @param unknown_type $params
     * @return multitype:unknown multitype:
     */

    public static function getBy($params = array(), $orderBy = array('app_id' => 'DESC')) {
    	if (!is_array($params)) return false;
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

    public static function getsBy($params = array(), $orderBy = array('app_id' => 'DESC')) {
    	if (!is_array($params)) return false;
        $ret = self::_getDao()->getsBy($params, $orderBy);
        if (!$ret) return false;
        return $ret;

    }


    public static function getFieldBy($field,$params){
        $ret = self::_getDao()->getAllByFields($field,$params);
        if(!$ret)return false;
        return $ret;
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
     * @param unknown_type $data
     * @param unknown_type $sorts
     * @return boolean
     */
    public static function sortAd($sorts) {
        foreach ($sorts as $key => $value) {
            self::_getDao()->update(array('sort' => $value), $key);
        }
        return true;
    }

    /**
     *
     * @param unknown_type $data
     * @return boolean
     */
    public static function deleteGameAd($data) {
        foreach ($data as $key => $value) {
            $v = explode('|', $value);
            self::_getDao()->deleteBy(array('id' => $v[0]));
        }
        return true;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $id
     */
    public static function deleteById($id) {
        return self::_getDao()->delete(intval($id));
    }


    public static function deleteBy($params) {
        return self::_getDao()->deleteBy($params);
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $data
     */
    public static function add($data) {
        if (!is_array($data)) return false;
        $data = self::_cookData($data);
        $ret = self::_getDao()->insert($data);
        if (!$ret) return $ret;
        return self::_getDao()->getLastInsertId();
    }

//    SELECT a.app_id, app_name, platform, a.dev_id, b.`dever_pos_name`, b.`dever_pos_key` FROM ad_app AS a INNER JOIN ad_dever_pos AS b ON a.`app_id`=b.`app_id` AND a.dev_id=b.`dev_id` WHERE 1  LIMIT 0,9999999
//    SELECT a.app_id, a.appkey, app_name, platform,  b.`dever_pos_name`, b.`dever_pos_key` FROM ad_app AS a INNER JOIN ad_dever_pos AS b ON a.`app_id`=b.`app_id` AND a.dev_id=b.`dev_id` WHERE b.pos_key='PIC_INTERGRATION'  LIMIT 0,9999999

    public static function getAdAppAdDeverPos($AdAppAdDeverParam = array(), $start = 0, $limit = 9999999, $orderBy = array()) {
        $posTypeDesc = Common_Service_Const::$mAdPosType;
        if (isset($AdAppAdDeverParam['originality_type']) && $AdAppAdDeverParam['originality_type']) {
            $pos_key = $posTypeDesc[$AdAppAdDeverParam['originality_type']];
        }
        $table = 'ad_dever_pos';
        $on = 'a.`app_id`=b.`app_id` AND a.dev_id=b.`dev_id`';
        $sqlWhere = ' b.state = 1 ';
        if ($pos_key) {
            $sqlWhere .= " and b.pos_key_type='$pos_key' ";
        }
        if (isset($AdAppAdDeverParam['dever_pos_keys']) && $AdAppAdDeverParam['dever_pos_keys']) {
            if($AdAppAdDeverParam['dever_pos_keys']){
                foreach($AdAppAdDeverParam['dever_pos_keys'] as $key => $dever_pos_key){
                    $AdAppAdDeverParam['dever_pos_keys'][$key] = "'". $dever_pos_key ."'";
                    $dever_pos_keys = implode(',', $AdAppAdDeverParam['dever_pos_keys']);
                }
                $sqlWhere .= " and b.dever_pos_key in ($dever_pos_keys) ";
            }
        }
        $field = 'a.app_id, a.app_key, app_name, platform,  b.`dever_pos_name`, b.`dever_pos_key`, b.`pos_key_type`';
        $result = self::_getDao()->searchByInnerJoin($table, $on, $start, $limit, $sqlWhere, $orderBy, $field);
        return $result;
    }

    public static function getAdApp($param = array(), $start = 0, $limit = 9999999) {
        $posTypeDesc = Common_Service_Const::$mAdPosType;
        if (isset($param['originality_type']) && $param['originality_type']) {
            $pos_key_type = $posTypeDesc[$param['originality_type']];
        }
        $table = 'ad_dever_pos';
        $on = 'a.`app_id`=b.`app_id` AND a.dev_id=b.`dev_id`';

        $sqlWhere = ' 1 ';
        if ($pos_key_type) {
            $sqlWhere .= " and b.pos_key_type='$pos_key_type' ";
        }
        if (isset($param['app_name']) && $param['app_name']) {
            $sqlWhere .= " and app_name like '%" . $param['app_name'] . "%' ";
        }
        if (isset($param['app_key']) && $param['app_key']) {
            $sqlWhere .= " and a.app_key like '%" . $param['app_key'] . "%' ";
        }
        $orderBy = array();
        $groupBy = 'a.app_id';
        $field = 'a.app_id, a.app_key, app_name, platform,  b.`dever_pos_name`, b.`dever_pos_key`, b.`pos_key_type`, count(*) as num';
        $result = self::_getDao()->searchByInnerJoinGroup($table, $on, $start, $limit, $sqlWhere, $orderBy, $groupBy, $field);

        $field = 'count(distinct(a.app_id)) as total';
        $totalResult = self::_getDao()->searchByInnerJoin($table, $on, 0, 99999, $sqlWhere, $orderBy, $field);
        $total = $totalResult[0]['total'];
        return array($total, $result);
    }


    public static function getAdPos($param = array(), $start = 0, $limit = 9999999) {
        $table = 'ad_dever_pos';
        $on = 'a.`app_id`=b.`app_id` AND a.dev_id=b.`dev_id`';
  
        $sqlWhere = ' 1 and b.del = 1 ';
        if (isset($param['pos_key_type']) && $param['pos_key_type']) {
            $sqlWhere .= " and b.pos_key_type='" . $param['pos_key_type'] . "' ";
        }
        if (isset($param['app_name']) && $param['app_name']) {
            $sqlWhere .= " and app_name like '%" . $param['app_name'] . "%' ";
        }
        if (isset($param['app_key']) && $param['app_key']) {
            $sqlWhere .= " and a.app_key ='" . $param['app_key'] . "' ";
        }
        if (isset($param['dever_pos_key']) && $param['dever_pos_key']) {
            $sqlWhere .= " and b.dever_pos_key ='" . $param['dever_pos_key'] . "' ";
        }

        $orderBy = array();
        $field = 'a.app_id, a.app_key, app_name, platform,  b.`dever_pos_name`, b.`dever_pos_key`, b.`pos_key_type`, b.`state`';
        $result = self::_getDao()->searchByInnerJoin($table, $on, $start, $limit, $sqlWhere, $orderBy, $field);

        $field = 'count(distinct(b.dever_pos_key)) as total';
        $totalResult = self::_getDao()->searchByInnerJoin($table, $on, 0, 99999, $sqlWhere, $orderBy, $field);
        $total = $totalResult[0]['total'];
        return array($total, $result);
    }
    
    public static function getAppInfoByAppKey($appKey) {
        $params ['app_key'] = $appKey;
        $params ['state'] = MobgiApi_Service_AdAppModel::OPEN_STATUS;
        $params ['is_check'] = MobgiApi_Service_AdAppModel::ISCHECK_PASS;
        $appInfo = MobgiApi_Service_AdAppModel::getBy ( $params );
        return $appInfo;
    }
    

    /**
     *
     * Enter description here ...
     * @param unknown_type $data
     */
    private static function _cookData($data) {
        $tmp = array();
        if (isset($data['app_id'])) $tmp['app_id'] = intval($data['app_id']);
        if (isset($data['app_name'])) $tmp['app_name'] = $data['app_name'];
        if (isset($data['app_key'])) $tmp['app_key'] = $data['app_key'];
        if (isset($data['package_name'])) $tmp['package_name'] = $data['package_name'];
        if (isset($data['platform'])) $tmp['platform'] = $data['platform'];
        if (isset($data['app_desc'])) $tmp['app_desc'] = $data['app_desc'];
        if (isset($data['app_type'])) $tmp['app_type'] = $data['app_type'];
        if (isset($data['appcate_id'])) $tmp['appcate_id'] = $data['appcate_id'];
        if (isset($data['state'])) $tmp['state'] = $data['state'];
        if (isset($data['dev_id'])) $tmp['dev_id'] = $data['dev_id'];
        if (isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if (isset($data['operator'])) $tmp['operator'] = $data['operator'];
        if (isset($data['acounting_method'])) $tmp['acounting_method'] = $data['acounting_method'];
        if (isset($data['income_rate'])) $tmp['income_rate'] = $data['income_rate'];
        if (isset($data['denominated'])) $tmp['denominated'] = $data['denominated'];
        if (isset($data['is_check'])) $tmp['is_check'] = $data['is_check'];
        if (isset($data['from'])) $tmp['from'] = $data['from'];
        if (isset($data['icon'])) $tmp['icon'] = $data['icon'];
        if (isset($data['keyword'])) $tmp['keyword'] = $data['keyword'];
        if (isset($data['check_msg'])) $tmp['check_msg'] = $data['check_msg'];
        if (isset($data['apk_url'])) $tmp['apk_url'] = $data['apk_url'];
        if (isset($data['out_game_id'])) $tmp['out_game_id'] = $data['out_game_id'];
        if (isset($data['is_track'])) $tmp['is_track'] = $data['is_track'];
        if (isset($data['delivery_type'])) $tmp['delivery_type'] = $data['delivery_type'];
        if (isset($data['appstore_id'])) $tmp['appstore_id'] = $data['appstore_id'];
        if (isset($data['consumer_key'])) $tmp['consumer_key'] = $data['consumer_key'];
        $tmp['update_time'] = Common::getTime();
        return $tmp;
    }

    /**
     *
     * @return MobgiApi_Dao_AdAppModel
     */
    private static function _getDao() {
        return Common::getDao("MobgiApi_Dao_AdAppModel");
    }

    public static function getFields($field = '*', $where = null) {
        return self::_getDao()->getFields($field, $where);
    }

}
