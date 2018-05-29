<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/27
 * Time: 16:10
 */
class MobgiSpm_Dao_MonitorScriptAlarmModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'monitor_script_alarm';
    protected $_primary = 'id';
}