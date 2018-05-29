<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * MobgiApi_Dao_PolymericAdsModel
 * @author rock.luo
 *
 */
class MobgiApi_Dao_PolymericAdsModel extends Common_Dao_Base{
    public  $adapter = 'mobgiApi';
	protected $_name = 'polymeric_ads';
	protected $_primary = 'id';
	
	
}
