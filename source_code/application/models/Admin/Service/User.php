<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Admin_Service_UserModel
{
	static private $hash = 'xysoza'; //hash值
	static private $sessionTime = 3600;
	static private $sessionName = 'AdminUser';

	const OPERATOR_USER = 1;
	const DEVERLOPER_USER = 3;
	const ADS_USER = 2;
	const GDT_USER = 4;
	const SPM_USER = 5;
	const SPM_CHANNEL_USER = 23;
	public static $mUserType = array(self::OPERATOR_USER => '运营用户', self::DEVERLOPER_USER => '广告主用户', self::DEVERLOPER_USER => '开发者用户', self::GDT_USER => '广点通用户', self::SPM_USER => '投放用户');
	public static $mRegisterType = array(1 => '个人', 2 => '企业');
	public static $mGroupId = array(23 => '投放渠道商');
	//审核状态
	const  ISCHECK_PASS = 1;
	const  ISCHECK_NOT_PASS = -1;
	const  ISCHECKING = 0;
	//是否锁定
	const  NO_LOCKED = 0;

	//是否为管理员
	const  IS_ADMIN = 1;
	const  IS_NOT_ADMIN = 0;


	/**
	 *
	 * 查询一条结果集
	 * @param array $search
	 * @return bool|mixed
	 */
	public static function getBy($params)
	{
		if (!is_array($params)) return false;
		return self::_getDao()->getBy($params);
	}

	public static function getsBy($params)
	{
		if (!is_array($params)) return false;
		return self::_getDao()->getsBy($params);
	}

	/**
	 *
	 * Enter description here ...
	 */
	public static function getAllUser()
	{
		return array(self::_getDao()->count(), self::_getDao()->getAll());
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $params
	 * @param unknown_type $page
	 * @param unknown_type $limit
	 */
	public static function getList($page = 1, $limit = 10, $params = array(), $orderBy = array('create_time' => 'DESC'))
	{
		if ($page < 1) $page = 1;
		$start = ($page - 1) * $limit;
		$ret = self::_getDao()->getList($start, $limit, $params, $orderBy);
		$total = self::_getDao()->count($params);
		return array($total, $ret);
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function getUser($uid)
	{
		if (!intval($uid)) return false;
		return self::_getDao()->get(intval($uid));
	}

	/**
	 *
	 * Enter description here ...
	 */
	public static function getUserByName($userName)
	{
		if (!$userName) return false;
		return self::_getDao()->getBy(array('user_name' => $userName));
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $email
	 */
	public static function getUserByEmail($email)
	{
		if (!$email) return false;
		return self::_getDao()->getBy(array('email' => $email));
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $uid
	 */
	public static function updateUser($data, $uid)
	{
		if (!is_array($data)) return false;
		$data = self::_cookData($data);
		return self::_getDao()->update($data, intval($uid));
	}

	/**
	 *
	 * @param type $data
	 * @param type $params
	 * @return boolean
	 */
	public static function updateBy($data, $params)
	{
		if (!is_array($data) || !is_array($params)) return false;
		$data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->updateBy($data, $params);
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function deleteUser($uid)
	{
		return self::_getDao()->delete(intval($uid));
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addUser($data)
	{
		if (!is_array($data)) return false;
		$data = self::_cookData($data);
		$ret = self::_getDao()->insert($data);
		if (!$ret) return $ret;
		return self::_getDao()->getLastInsertId();
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $password
	 */
	static private function _cookPasswd($password)
	{
		$hash = Common::randStr(6);
		$passwd = self::_password($password, $hash);
		return array($hash, $passwd);
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $username
	 * @param unknown_type $passwd
	 */
	public static function login($email, $password)
	{
		$result = self::checkUser($email, $password);
		if ($result['code'] == 0) {
			self::_cookieUser($result['data']);
		}
		return $result;
	}

	/**
	 *
	 * Enter description here ...
	 */
	public static function logout()
	{
		$session = Common::getSession();
		$session->del(self::$sessionName);
		return true;
	}

	/**
	 *
	 * Enter description here ...
	 */
	public static function isLogin()
	{
		$session = Common::getSession();

		if (!$session->has(self::$sessionName)) return false;
		$sessionInfo = $session->get(self::$sessionName);

		$sessionInfo = self::_cookieEncrypt($sessionInfo, 'DECODE');
		if (!$sessionInfo || !$sessionInfo[1] || !$sessionInfo[3]) return false;
		if (!$userInfo = self::getUserByName($sessionInfo[1])) return false;
		if ($sessionInfo[2] != $userInfo['user_id'] || $sessionInfo[3] != $userInfo['password']) {
			return false;
		}
		self::_cookieUser($userInfo);
		return $userInfo;
	}

	/**
	 * cookie字符串加密解密方式
	 * @param string $str 加密方式
	 * @param string $encode ENCODE-加密|DECODE-解密
	 * @return array
	 */
	static private function _cookieEncrypt($str, $encode = 'ENCODE')
	{
		if ($encode == 'ENCODE') return Common::encrypt($str);
		$result = Common::encrypt($str, 'DECODE');
		return explode('\t', $result);
	}

	/**
	 * cookie添加
	 * @param string $userInfo 用户信息
	 * @return array
	 */
	static private function _cookieUser($userInfo)
	{
		$str = Common::getTime() . '\t';
		$str .= $userInfo['user_name'] . '\t';
		$str .= $userInfo['user_id'] . '\t';
		$str .= $userInfo['password'] . '\t';
		$str .= $userInfo['email'] . '\t';
		$sessionStr = self::_cookieEncrypt($str);
		$session = Common::getSession();
		$session->set(self::$sessionName, $sessionStr);
	}

	/**
	 *
	 * Enter description here ...
	 * @param $email
	 * @param unknown_type $password
	 * @return array
	 * @internal param unknown_type $username
	 */
	public static function checkUser($email, $password)
	{
		if (!$email || !$password) return Common::formatMsg(-1, '用户名或密码为空.');
		$userInfo = self::getUserByEmail($email);
		if (!$userInfo) return Common::formatMsg(-1, '用户不存在.');
		$password = self::_password($password, $userInfo['hash']);
		if ($password != $userInfo['password']) return Common::formatMsg(-1, '当前密码不正确.');
		if ($userInfo['is_lock']) return Common::formatMsg(-1, '当前用户被锁定.');
		return Common::formatMsg(0, '成功.', $userInfo);
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $password
	 * @param unknown_type $hash
	 * @return string
	 */
	private static function _password($password, $hash)
	{
		return md5(md5(md5($password)));
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data)
	{
		$tmp = array();
		if (isset($data['user_name'])) $tmp['user_name'] = $data['user_name'];
		if (isset($data['password'])) {
			list(, $tmp['password']) = self::_cookPasswd($data['password']);
		}
		if (isset($data['group_id'])) $tmp['group_id'] = $data['group_id'];
		if (isset($data['email'])) $tmp['email'] = $data['email'];
		if (isset($data['card_id'])) $tmp['card_id'] = $data['card_id'];
		if (isset($data['operator'])) $tmp['operator'] = $data['operator'];
		if (isset($data['tel'])) $tmp['tel'] = $data['tel'];
		if (isset($data['address'])) $tmp['address'] = $data['address'];
		if (isset($data['is_check'])) $tmp['is_check'] = $data['is_check'];
		if (isset($data['is_active'])) $tmp['is_active'] = $data['is_active'];
		if (isset($data['is_admin'])) $tmp['is_admin'] = $data['is_admin'];
		if (isset($data['is_lock'])) $tmp['is_lock'] = $data['is_lock'];
		if (isset($data['check_msg'])) $tmp['check_msg'] = $data['check_msg'];
		if (isset($data['register_type'])) $tmp['register_type'] = $data['register_type'];
		if (isset($data['contact'])) $tmp['contact'] = $data['contact'];
		if (isset($data['user_type'])) $tmp['user_type'] = $data['user_type'];
		if (isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
		$tmp['update_time'] = date('Y-m-d H:i:s');
		return $tmp;
	}

	/**
	 * 获取各个组的用户数量
	 * @param type $groupid
	 * @return boolean
	 */
	public static function getusernumsByGroup($groupId)
	{
		if (empty($groupId)) {
			return false;
		}
		$params = array();
		$params['group_id'] = $groupId;
		$num = self::_getDao()->count($params);
		return $num;
	}


	//    获取账户信息
	public static function getAcount($params = array())
	{
		$list = self::_getDao()->getsBy($params);
		$appkeys = [];
		foreach ($list as $item) {
			$appkeys[$item['advertiser_uid']] = $item['advertiser_name'];
		}
		return $appkeys;
	}

	public static function getFields($field = '*', $where = null)
	{
		return self::_getDao()->getFields($field, $where);
	}

	/**
	 *
	 * @param $userId
	 * @return array
	 */
	public static function isOperator($userId)
	{

		$userInfo = self::_getDao()->get($userId);

		if (!empty($userInfo['user_id']) and $userInfo['user_id'] == 8888) {
			return true;
		} elseif (!empty($userInfo['user_type']) and $userInfo['user_type'] == self::OPERATOR_USER) {
			return true;
		} else {
			return false;
		}


	}


	/**
	 *
	 * @return Admin_Dao_UserModel
	 */
	private static function _getDao()
	{
		return Common::getDao("Admin_Dao_UserModel");
	}
}
