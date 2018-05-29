<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Advertiser_Service_UserModel{
	static private $hash = 'xysoza'; //hash值
	static private $sessionTime = 3600;
	static private $sessionName = 'AdvertiserUser';
	
	//1普通2广点通3报表查看4广告商（实时）5广告商（同步）
	static public  $adsTypeDesc = array(1=>'普通',2=>'广点通',3=>'报表查看',4=>'广告商（实时）',5=>'广告商（同步）'
			
	);

	const ADVERTISER_COMMON = 1;    //①普通
	const ADVERTISER_GDT = 2;       //②广点通
	const ADVERTISER_REPORT = 3;    //③报表查看
	const ADVERTISER_THIRD = 4;     //④广告商（实时）
	const ADVERTISER_SONA = 5;      //⑤广告商（同步）

	/**
	 * 
	 * Enter description here ...
	 */
	public static function getAllUser() {
		return array(self::_getDao()->count(), self::_getDao()->getAll());
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
	public static function getsBy($params,$orderBy = array('advertiser_uid'=>'ASC')) {
	    if (!is_array($params)) return false;
	    return self::_getDao()->getsBy($params,$orderBy);
	}
	/**
	 * 获取各个组的用户数量
	 * @param type $groupid
	 * @return boolean
	 */
	public static function getAdvertisernumsByGroup($groupid){
	    if(empty($groupid)){
	        return false;
	    }
	    $params = array();
	    $params['groupid'] = $groupid;
	    $num = self::_getDao()->count($params);
	    return $num;
	}
	
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $params
	 * @param unknown_type $page
	 * @param unknown_type $limit
	 */
	public static function getList($page = 1, $limit = 10, $params = array()) {
		if ($page < 1) $page = 1; 
		$start = ($page - 1) * $limit;
		$ret = self::_getDao()->getList($start, $limit, $params);
		$total = self::_getDao()->count($params);
		return array($total, $ret);
	}
	
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function getUser($uid) {
		if (!intval($uid)) return false;
		return self::_getDao()->get(intval($uid));
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public static function getUserByName($username) {
	   if (!$username) return false;
		return self::_getDao()->getBy(array('advertiser_name'=>$username));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $email
	 */
	public static function getUserByEmail($email) {
		if (!$email) return false;
		return self::_getDao()->getBy(array('email'=>$email));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $uid
	 */
	public static function updateUser($data, $uid) {
		if (!is_array($data)) return false;
		$data = self::_cookData($data);
		return self::_getDao()->update($data, intval($uid));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public static function deleteUser($uid) {
		return self::_getDao()->delete(intval($uid));
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addUser($data) {
		if (!is_array($data)) return false;
		$data['register_time'] = Common::getTime();
        $data['register_ip'] = Common::getClientIP();
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
	static private function _cookPasswd($password) {
		$hash = Common::randStr(6);
		$passwd = self::_password($password, $hash);
		return array($hash, $passwd);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $email
	 * @param unknown_type $passwd
	 */
	public static function login($email, $password) {
		$result = self::checkUser($email, $password);
		if (!$result || Common::isError($result)) return false;
		self::_cookieUser($result);
		return true;
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public static function logout() {
		$session = Common::getSession();
		$session->del(self::$sessionName);
		return true;
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public static function isLogin() {
		$session = Common::getSession();
		
		if(!$session->has(self::$sessionName)) return false;
		$sessionInfo = $session->get(self::$sessionName);
		
		$sessionInfo = self::_cookieEncrypt($sessionInfo, 'DECODE');
		if (!$sessionInfo || !$sessionInfo[1] || !$sessionInfo[3]) return false;
		if (!$userInfo = self::getUserByEmail($sessionInfo[1])) return false;
		if ($sessionInfo[2] != $userInfo['advertiser_uid'] || $sessionInfo[3] != $userInfo['password']) {
			return false;
		}
		self::_cookieUser($userInfo);
		return $userInfo;
	}
    /**
     * 设置appkey
     * @param type $advertiser_uid
     */
    public static function setAppkey($advertiser_uid){
        $userinfo = self::getUser($advertiser_uid);
        if(empty($userinfo)){
            return false;
        }
        $appkey = md5($userinfo['advertiser_uid'].$userinfo['hash']);
        return self::updateUser(array('appkey'=>$appkey), $advertiser_uid);
    }
	
	/**
	 * cookie字符串加密解密方式
	 * @param string $str      加密方式
	 * @param string $encode   ENCODE-加密|DECODE-解密
	 * @return array
	 */
	static private function _cookieEncrypt($str, $encode = 'ENCODE') {
		if ($encode == 'ENCODE') return Common::encrypt($str);
		$result = Common::encrypt($str, 'DECODE');
		return explode('\t', $result);
	}
	
	/**
	 * cookie添加
	 * @param string $userInfo  用户信息
	 * @return array
	 */
	static private function _cookieUser($userInfo) {
		$str = Common::getTime() . '\t';
		$str .= $userInfo['email'] . '\t';
		$str .= $userInfo['advertiser_uid'] . '\t';
		$str .= $userInfo['password'] . '\t';
		
		$sessionStr = self::_cookieEncrypt($str);
		$session = Common::getSession();
		$session->set(self::$sessionName, $sessionStr);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $email
	 * @param unknown_type $password
	 */
	public static function checkUser($email, $password) {
		if (!$email || !$password) return false;
		$userInfo = self::getUserByEmail($email);
		if (!$userInfo)  return Common::formatMsg(-1, '用户不存在.');
		$password = self::_password($password, $userInfo['hash']);
		if ($password != $userInfo['password']) return Common::formatMsg(-1, '当前密码不正确.');
		return $userInfo;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $password
	 * @param unknown_type $hash
	 */
	private static function _password($password, $hash) {
		return md5(md5($password) . $hash);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
		$tmp = array();
		if(isset($data['advertiser_name'])) $tmp['advertiser_name'] = $data['advertiser_name'];
        if(isset($data['account_type'])) $tmp['account_type'] = $data['account_type'];
        if(isset($data['type'])) $tmp['type'] = $data['type'];
		if(isset($data['password'])) {
			list($tmp['hash'], $tmp['password']) = self::_cookPasswd($data['password']); 
		}
		if(isset($data['groupid'])) $tmp['groupid'] = $data['groupid'];
		if(isset($data['email'])) $tmp['email'] = $data['email'];
        if(isset($data['appkey'])) $tmp['appkey'] = $data['appkey'];
		if(isset($data['register_time'])) $tmp['register_time'] = $data['register_time'];
        if(isset($data['register_ip'])) $tmp['register_ip'] = $data['register_ip'];
        if(isset($data['address'])) $tmp['address'] = $data['address'];
        if(isset($data['company_name'])) $tmp['company_name'] = $data['company_name'];
        if(isset($data['business_license'])) $tmp['business_license'] = $data['business_license'];
        if(isset($data['ad_qualification'])) $tmp['ad_qualification'] = $data['ad_qualification'];
        if(isset($data['status'])) $tmp['status'] = $data['status'];
        if(isset($data['groupid'])) $tmp['groupid'] = $data['groupid'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}
    public static function updateBy($data, $params){
        if (!is_array($data) || !is_array($params)) return false;
        $data['update_time'] = Common::getTime();
        $data = self::_cookData($data);
        return self::_getDao()->updateBy($data, $params);
    }
	/**
	 * 
	 * @return Advertiser_Dao_UserModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_UserModel");
	}

    //    获取账户信息
    public static function getAcount($params = array()) {
        $list = self::_getDao()->getsBy($params);
        $appkeys = [];
        foreach ($list as $item) {
            $appkeys[$item['advertiser_uid']] = $item['advertiser_name'];
        }
        return $appkeys;
    }

    public static function getFields($field = '*', $where = null) {
        return self::_getDao()->getFields($field, $where);
    }


}
