<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * MobgiApi_Dao_AdsListModel
 * @author rock.luo
 *
 */
class MobgiApi_Dao_AdsListModel extends Common_Dao_Base{
    public  $adapter = 'mobgiApi';
	protected $_name = 'ads_list';
	protected $_primary = 'id';
	
	
}
