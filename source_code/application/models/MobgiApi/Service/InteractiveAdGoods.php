<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class MobgiApi_Service_InteractiveAdGoodsModel
{

	const OPEN_STATUS = 1;
	const CLOSE_STATUS = 0;
	const ENTITY_GOODS_TYPE = 1;
	const VIRTUAL_GOODS_TYPE = 2;
	const COUPON_GOODS_TYPE = 3;
	const DEAFAULT_GOODS_TYPE =4;
	public static $mGoodsType = array(self::ENTITY_GOODS_TYPE => '实体物品',
		self::VIRTUAL_GOODS_TYPE => '虚拟物品',
		self::COUPON_GOODS_TYPE => '优惠券',
		self::DEAFAULT_GOODS_TYPE=>'谢谢参与'
	);
	public static $mGoodsStatus = [self::OPEN_STATUS => '上架', self::CLOSE_STATUS => '下架'];


	/**
	 * @return array
	 */
	public static function getAll()
	{
		return array(self::_getDao()->count(), self::_getDao()->getAll());
	}

	public static function mutiFieldInsert($data)
	{
		if (!is_array($data)) return false;
		return self::_getDao()->mutiFieldInsert($data);
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
		if (isset($data['type'])) $tmp['type'] = intval($data['type']);
		if (isset($data['icon'])) $tmp['icon'] = $data['icon'];
		if (isset($data['big_img'])) $tmp['big_img'] = $data['big_img'];
		if (isset($data['title'])) $tmp['title'] = $data['title'];
		if (isset($data['desc'])) $tmp['desc'] = $data['desc'];
		if (isset($data['status'])) $tmp['status'] = $data['status'];
		if (isset($data['stock'])) $tmp['stock'] = $data['stock'];
		if (isset($data['used_num'])) $tmp['used_num'] = $data['used_num'];
		if (isset($data['start_time'])) $tmp['start_time'] = $data['start_time'];
		if (isset($data['end_time'])) $tmp['end_time'] = $data['end_time'];
		if (isset($data['del'])) $tmp['del'] = $data['del'];
		if (isset($data['operator'])) $tmp['operator'] = $data['operator'];
		$tmp['update_time'] = date('Y-m-d H:i:s');
		return $tmp;
	}


	/**
	 *
	 * @return MobgiApi_Dao_InteractiveAdGoodsModel
	 */
	private static function _getDao()
	{
		return Common::getDao("MobgiApi_Dao_InteractiveAdGoodsModel");
	}
}
