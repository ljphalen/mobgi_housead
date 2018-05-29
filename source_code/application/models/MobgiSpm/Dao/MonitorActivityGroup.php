<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/6
 * Time: 15:15
 */
class MobgiSpm_Dao_MonitorActivityGroupModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'monitor_activitygroup';
    protected $_primary = 'id';
}