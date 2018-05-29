<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Dedelivery_Service_UnitConfModel
{
	const OPEN_STATUS = 1;
	const CLOSE_STATUS = 2;


	/**
	 * @return array
	 */
	public static function getAll()
	{
		return array(self::_getDao()->count(), self::_getDao()->getAll());
	}


	/**
	 * @param int $page
	 * @param int $limit
	 * @param array $params
	 * @param array $orderBy
	 * @return array
	 */
	public static function getList($page = 1, $limit = 10, $params = array(), $orderBy = array('update_time' => 'DESC'))
	{
		if ($page < 1) $page = 1;
		$start = ($page - 1) * $limit;
		$ret = self::_getDao()->getList($start, $limit, $params, $orderBy);
		$total = self::_getDao()->count($params);
		return array($total, $ret);
	}

	/**
	 * @param $id
	 * @return bool|mixed
	 */
	public static function getByID($id)
	{
		if (!intval($id)) return false;
		return self::_getDao()->get(intval($id));
	}


	/**
	 *
	 * @param array $params
	 * @param array $orderBy
	 * @return multitype :unknown multitype:
	 * @internal param unknown_type $page
	 * @internal param unknown_type $limit
	 */

	public static function getBy($params = array(), $orderBy = array('id' => 'DESC'))
	{
		$ret = self::_getDao()->getBy($params, $orderBy);
		if (!$ret) return false;
		return $ret;

	}

	/**
	 * @param array $params
	 * @param array $orderBy
	 * @return array|bool
	 */

	public static function getsBy($params = array(), $orderBy = array('id' => 'DESC'))
	{
		$ret = self::_getDao()->getsBy($params, $orderBy);
		if (!$ret) return false;
		return $ret;

	}

	/**
	 * @param $data
	 * @param $id
	 * @return bool|int
	 */
	public static function updateByID($data, $id)
	{
		if (!is_array($data)) return false;
		$data = self::_cookData($data);
		$data['update_time'] = date('Y-m-d H:i:s');
		return self::_getDao()->update($data, intval($id));
	}

	public static function updateBy($data, $params)
	{
		if (!is_array($data) || !is_array($params)) return false;
		$data = self::_cookData($data);
		return self::_getDao()->updateBy($data, $params);
	}

	/**
	 * @param $sorts
	 * @return bool
	 */
	public static function sortAd($sorts)
	{
		foreach ($sorts as $key => $value) {
			self::_getDao()->update(array('sort' => $value), $key);
		}
		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public static function deleteGameAd($data)
	{
		foreach ($data as $key => $value) {
			$v = explode('|', $value);
			self::_getDao()->deleteBy(array('id' => $v[0]));
		}
		return true;
	}

	/**
	 * @param $id
	 * @return bool|int
	 */
	public static function deleteById($id)
	{
		return self::_getDao()->delete(intval($id));
	}


	public static function deleteBy($params)
	{
		return self::_getDao()->deleteBy($params);
	}

	/**
	 * @param $data
	 * @return bool|int|string
	 */
	public static function add($data)
	{
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
		if (isset($data['id'])) $tmp['id'] = intval($data['id']);
		if (isset($data['name'])) $tmp['name'] = $data['name'];
		if (isset($data['limit_type'])) $tmp['limit_type'] = $data['limit_type'];
		if (isset($data['limit_range'])) $tmp['limit_range'] = $data['limit_range'];
		if (isset($data['mode_type'])) $tmp['mode_type'] = $data['mode_type'];
		if (isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
		if (isset($data['account_id'])) $tmp['account_id'] = $data['account_id'];
		if (isset($data['outer_unit_id'])) $tmp['outer_unit_id'] = $data['outer_unit_id'];
		if (isset($data['status'])) $tmp['status'] = $data['status'];
		if (isset($data['unit_type'])) $tmp['unit_type'] = $data['unit_type'];
		if (isset($data['del'])) $tmp['del'] = $data['del'];
		$tmp['update_time'] = date('Y-m-d H:i:s');
		return $tmp;
	}

//    获取投放单元
	public static function getUnitId($params = array())
	{
		$list = self::_getDao()->getsBy($params);
		$unitId = [];
		foreach ($list as $item) {
			$unitId[$item['id']] = $item['name'];
		}
		return $unitId;
	}


	/**
	 *
	 * @return Dedelivery_Dao_UnitConfModel
	 */
	private static function _getDao()
	{
		return Common::getDao("Dedelivery_Dao_UnitConfModel");
	}

	public static function getFields($field = '*', $where = null)
	{
		return self::_getDao()->getFields($field, $where);
	}

	public static function getUnitDayLimitAmountList($unitIds)
	{
		if (empty ($unitIds)) {
			return false;
		}
		$params ['id'] = array(
			'IN',
			$unitIds
		);
		$unitInfoList = self::getsBy($params);
		$unitLimitList = array();
		foreach ($unitInfoList as $val) {
			if ($val ['limit_type']) {
				$unitLimitList [$val ['id']] = floatval($val ['limit_range']);
			} else {
				$unitLimitList [$val ['id']] = 999999999.00;
			}
		}
		return $unitLimitList;
	}

	/**
	 * @param $accountIds
	 * @return array|bool
	 */
	public static function getUnitConfByAccountIds($accountIds)
	{
		$params ['status'] = self::OPEN_STATUS;
		$params ['del'] = Common_Service_Const::NOT_DELETE_FLAG;
		$params ['account_id'] = array(
			'IN',
			$accountIds
		);
		$result = self::getsBy($params);
		if ($result) {
			$result = Common::resetKey($result, "id");
			return array_keys($result);
		}
		return false;
	}


}
