<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Admin_Dao_GroupModel
 * @author rock.luo
 *
 */
class Admin_Dao_GroupModel extends Common_Dao_Base {
    public $adapter = 'mobgiAdmin';
	protected $_name = 'admin_group';
	protected $_primary = 'group_id';
	

}
