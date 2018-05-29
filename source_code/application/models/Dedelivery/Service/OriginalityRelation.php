<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * Class Dedelivery_Service_OriginalityRelationModel
 */
class Dedelivery_Service_OriginalityRelationModel {

    //投放中的状态
    const OPEN_STATUS = 1;
    const CACHE_EPIRE = 172800;


    public static function getCache() {
        $resource = 'charge_info';
        $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS,$resource);
        return $cache;
    }

    public static function getOriginalityChargePriceKey($requestId) {
        $key = Util_CacheKey::ORIGINALITY_CHARGE_PRICE . $requestId;
        return $key;
    }

    public static function saveOriginalityChargePriceKeyToCache($requestId, $data) {
        if (empty($data)) return false;
        if (empty($requestId)) return false;
        $cache = self::getCache();
        $key = self::getOriginalityChargePriceKey($requestId);
        $cache->set($key, $data, self::CACHE_EPIRE);
    }


    public static function getOriginalityChargePriceByRequestId($requestId, $originalityId = 0) {
        if (empty($requestId)) return false;
        $key = self::getOriginalityChargePriceKey($requestId);
        $cache = self::getCache();
        $data = $cache->get($key);
        if ($data === false && $originalityId) {
            $params['id'] = $originalityId;
            $originalitydata = self::getBy($params);
            if (!$originalitydata) return false;
            $result = Dedelivery_Service_AdConfListModel::getBy(array('id' => $originalitydata['ad_id']));
            $price = $result['price'];
            if ($result['charge_type'] == Common_Service_Const::CHARGE_TYPE_CPM) {
                $price = $price / 1000;
            }
            $returnData = array(
                'price' => sprintf("%.4f", $price),
                'charge_type' => $result['charge_type'],
            );
            return $returnData;
        }
        return $data[$originalityId];
    }


	/**
	 * @return array
	 */
    public static function getAll() {
        return array(self::_getDao()->count(), self::_getDao()->getAll());
    }


    public static function getSearchByPageLeftJoin($table, $on, $page = 1, $limit = 10, $sqlWhere = 1, $orderBy = array('update_time'=>'DESC'), $field = '*') {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $ret = self::_getDao()->searchByLeftJoin($table, $on, $start, $limit, $sqlWhere, $orderBy, $field);
        $total = self::_getDao()->searchCountLeftJoin($table, $on, $sqlWhere);
        return array($total, $ret);

    }

    public static function getSearchLeftJoinNoLimit($table, $on, $sqlWhere = 1, $orderBy = array(), $field = '*') {
        $ret = self::_getDao()->searchByLeftJoinNoLimit($table, $on, $sqlWhere, $orderBy, $field);
        return $ret;

    }

	/**
	 * @param int $page
	 * @param int $limit
	 * @param array $params
	 * @param array $orderBy
	 * @return array
	 */
    public static function getList($page = 1, $limit = 10, $params = array(), $orderBy = array('update_time' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $ret = self::_getDao()->getList($start, $limit, $params, $orderBy);
        $total = self::_getDao()->count($params);
        return array($total, $ret);
    }

	/**
	 * @param $search
	 * @return bool|mixed
	 */
    public static function getBy($search) {
        return self::_getDao()->getBy($search);
    }

	/**
	 * @param $params
	 * @param array $orderBy
	 * @return array|bool
	 */
    public static function getsBy($params, $orderBy = array('id' => 'ASC')) {
        if (!is_array($params)) return false;
        return self::_getDao()->getsBy($params, $orderBy);
    }

	/**
	 * @param $data
	 * @param $id
	 * @return bool|int
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
	 * @param $id
	 * @return bool|int
	 */
    public static function delete($id) {
        return self::_getDao()->delete(intval($id));
    }


	/**
	 * @param $search
	 * @return bool
	 */
    public static function deleteBy($search) {
        return self::_getDao()->deleteBy($search);
    }

	/**
	 * @param $data
	 * @return bool|int|string
	 */
    public static function add($data) {
        if (!is_array($data)) return false;
        $data = self::_cookData($data);
        $ret = self::_getDao()->insert($data);
        if (!$ret) return $ret;
        return self::_getDao()->getLastInsertId();
    }

	/**
	 * @param $data
	 * @return array
	 */
	private static function _cookData($data)
	{
		$tmp = array();
		if (isset($data['originality_type'])) $tmp['originality_type'] = intval($data['originality_type']);
		if (isset($data['ad_id'])) $tmp['ad_id'] = $data['ad_id'];
		if (isset($data['unit_id'])) $tmp['unit_id'] = $data['unit_id'];
		if (isset($data['title'])) $tmp['title'] = $data['title'];
		if (isset($data['desc'])) $tmp['desc'] = $data['desc'];
		if (isset($data['originality_type'])) $tmp['originality_type'] = $data['originality_type'];
		if (isset($data['strategy'])) $tmp['strategy'] = $data['strategy'];
		if (isset($data['upload_content'])) $tmp['upload_content'] = $data['upload_content'];
		if (isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
		if (isset($data['account_id'])) $tmp['account_id'] = $data['account_id'];
		if (isset($data['status'])) $tmp['status'] = $data['status'];
		if (isset($data['del'])) $tmp['del'] = $data['del'];
		if (isset($data['filter_app_conf'])) $tmp['filter_app_conf'] = $data['filter_app_conf'];
		if (isset($data['weight'])) $tmp['weight'] = $data['weight'];
		if (isset($data['outer_originality_id'])) $tmp['outer_originality_id'] = $data['outer_originality_id'];
		if (isset($data['ad_sub_type'])) $tmp['ad_sub_type'] = $data['ad_sub_type'];
		if (isset($data['trial_package_id'])) $tmp['trial_package_id'] = $data['trial_package_id'];
		if (isset($data['trial_ad_target_type'])) $tmp['trial_ad_target_type'] = $data['trial_ad_target_type'];
		if (isset($data['entry'])) $tmp['entry'] = $data['entry'];
		if (isset($data['installation_hint'])) $tmp['installation_hint'] = $data['installation_hint'];
		if (isset($data['float_view'])) $tmp['float_view'] = $data['float_view'];
		if (isset($data['shortcut'])) $tmp['shortcut'] = $data['shortcut'];
		if (isset($data['shortcut_name'])) $tmp['shortcut_name'] = $data['shortcut_name'];
		$tmp['update_time'] = date('Y-m-d H:i:s');
		return $tmp;
	}


//    获取关联关系
    public static function getRelationOfAccount($param) {
        $list = self::_getDao()->getRelationList($param);
        $relations = [];
        foreach ($list as $item) {
            $relations['account_id']['unit_id'][$item['account_id']][] = $item['unit_id'];
            $relations['unit_id']['ad_id'][$item['unit_id']][] = $item['ad_id'];
            $relations['ad_id']['originality_id'][$item['ad_id']][] = $item['originality_id'];
            $relations['originality_type']['ad_id'][$item['originality_type']][] = $item['ad_id'];
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


    public static function getFieldWithAccount($param, $myFields) {
        $list = self::_getDao()->getRelationList($param);
        $result = [];
        $fields = is_string($myFields) ? [$myFields] : $myFields;
        foreach ($fields as $field) {
            $result[$field] = [];
            if (in_array($field, ['originality_id', 'originality_type', 'unit_id', 'ad_id'])) {
                foreach ($list as $item) {
                    $result[$field][$item[$field]] = $item[$field];
                }
            }
        }
        return is_string($myFields) ? $result[$myFields] : $result;
    }


	/**
	 * @return Common_Dao_Base
	 */
    private static function _getDao() {
        return Common::getDao("Dedelivery_Dao_OriginalityRelationModel");
    }

    public static function getFields($field = '*', $where = null) {
        return self::_getDao()->getFields($field, $where);
    }

}
