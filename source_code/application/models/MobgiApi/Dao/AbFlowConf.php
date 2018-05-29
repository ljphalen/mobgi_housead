<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * MobgiApi_Dao_AbFlowConfModel
 * @author rock.luo
 *
 */
class MobgiApi_Dao_AbFlowConfModel extends Common_Dao_Base{
    public  $adapter = 'mobgiApi';
	protected $_name = 'ab_flow_conf';
	protected $_primary = 'flow_id';
	
	
}
