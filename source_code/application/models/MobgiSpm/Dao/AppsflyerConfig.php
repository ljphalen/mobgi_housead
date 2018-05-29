<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/27
 * Time: 17:35
 */
class MobgiSpm_Dao_AppsflyerConfigModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'appsflyer_config';
    protected $_primary = 'id';
}