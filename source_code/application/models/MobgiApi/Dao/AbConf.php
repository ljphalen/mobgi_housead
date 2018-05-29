<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * MobgiApi_Dao_AbConfModel
 * @author rock.luo
 *
 */
class MobgiApi_Dao_AbConfModel extends Common_Dao_Base{
    public  $adapter = 'mobgiApi';
	protected $_name = 'ab_conf';
	protected $_primary = 'conf_id';
	
	
}
