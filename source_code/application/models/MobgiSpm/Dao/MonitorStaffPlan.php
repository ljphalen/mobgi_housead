<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/1/15
 * Time: 14:04
 */
class MobgiSpm_Dao_MonitorStaffPlanModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'monitor_staff_plan';
    protected $_primary = 'id';
}