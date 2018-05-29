<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * MobgiApi_Dao_VideoAdsModel
 * @author rock.luo
 *
 */
class MobgiApi_Dao_VideoAdsModel extends Common_Dao_Base{
    public  $adapter = 'mobgiApi';
	protected $_name = 'video_ads';
	protected $_primary = 'id';
	
	
}
