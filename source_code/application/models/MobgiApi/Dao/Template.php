<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * MobgiApi_Dao_TemplateModel
 * @author rock.luo
 *
 */
class MobgiApi_Dao_TemplateModel extends Common_Dao_Base{
    public  $adapter = 'mobgiApi';
	protected $_name = 'template';
	protected $_primary = 'id';
	
	
}
