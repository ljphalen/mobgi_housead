<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * MobgiApi_Dao_SdkInfoModel
 * @author rock.luo
 *
 */
class MobgiApi_Dao_SdkInfoModel extends Common_Dao_Base{
    public  $adapter = 'mobgiApi';
	protected $_name = 'sdk_info';
	protected $_primary = 'id';
	
	
}
