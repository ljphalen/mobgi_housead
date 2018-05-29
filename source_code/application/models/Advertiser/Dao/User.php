<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Advertiser_Dao_UserModel
 * @author rock.luo
 *
 */
class Advertiser_Dao_UserModel extends Common_Dao_Base{
	protected $_name = 'advertiser_user';
	protected $_primary = 'advertiser_uid';
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function getUser($uid) {
		return self::get(intval($uid));
	}
	/**
	 * 
	 * Enter description here ...
	 */
	public function getAllUser() {
		return array(self::count(), self::getAll());
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $username
	 */
	public function getByUserName($username) {
		if (!$username) return false;
		return self::getBy(array('advertiser_name'=>$username));
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $email
	 */
	public function getByEmail($email) {
		if (!$email) return false;
		return self::getBy(array('email'=>$email));
	}
}
