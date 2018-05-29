<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/22
 * Time: 10:29
 */
class MobgiSpm_Dao_MonitorAppModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'monitor_app';
    protected $_primary = 'app_id';
}