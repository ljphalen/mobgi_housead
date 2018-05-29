<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * MobgiApi_Dao_InteractiveAdUserModel
 * @author rock.luo
 *
 */
class MobgiApi_Dao_InteractiveAdUserModel extends Common_Dao_Base{
    public  $adapter = 'mobgiApi';
	protected $_name = 'interactive_ad_user';
	protected $_primary = 'id';
	
	
}
