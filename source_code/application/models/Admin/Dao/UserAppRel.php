<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Admin_Dao_UserAppRelModel
 * @author rock.luo
 *
 */
class Admin_Dao_UserAppRelModel extends Common_Dao_Base {
    public $adapter = 'mobgiAdmin';
	protected $_name = 'admin_user_app_rel';
	protected $_primary = 'id';
	

}
