<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * MobgiApi_Dao_AdAppModel
 * @author rock.luo
 *
 */
class Stat_Dao_OriginalDataModel extends Common_Dao_Base{
    const NetType_WIFI=1;

    public  $adapter = 'mobgiApi';
	protected $_name = 'ad_app';
	protected $_primary = 'app_id';
	
	
}
