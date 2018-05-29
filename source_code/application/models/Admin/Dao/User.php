<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Admin_Dao_UserModel
 * @author rock.luo
 *
 */
class Admin_Dao_UserModel extends Common_Dao_Base{
    public $adapter = 'mobgiAdmin';
	protected $_name = 'admin_user';
	protected $_primary = 'user_id';
	




}
