<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class MobgiApi_Service_InteractiveAdConfRelModel
{



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
	public static function getList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC'))
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
	 * @param array $params
	 * @param array $orderBy
	 * @return bool|mixed
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
		return self::_getDao()->update($data, intval($id));
	}

	/**
	 * @param $data
	 * @param $params
	 * @return bool
	 */
	public static function updateBy($data, $params)
	{
		if (!is_array($data) || !is_array($params)) return false;
		$data = self::_cookData($data);
		return self::_getDao()->updateBy($data, $params);
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
		if (isset($data['app_key'])) $tmp['app_key'] = $data['app_key'];
		if (isset($data['pos_key'])) $tmp['pos_key'] = $data['pos_key'];
		if (isset($data['operator'])) $tmp['operator'] = $data['operator'];
		$tmp['update_time'] = date('Y-m-d H:i:s');
		return $tmp;
	}


	/**
	 *
	 * @return MobgiApi_Dao_InteractiveAdConfRelModel
	 */
	private static function _getDao()
	{
		return Common::getDao("MobgiApi_Dao_InteractiveAdConfRelModel");
	}
}
