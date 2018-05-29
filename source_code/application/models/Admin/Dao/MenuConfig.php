<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Admin_Dao_MenuConfigModel
 * @author rock.luo
 *
 */
class Admin_Dao_MenuConfigModel extends Common_Dao_Base {
    public $adapter = 'mobgiAdmin';
	protected $_name = 'admin_menu_config';
	protected $_primary = 'id';
	

}
