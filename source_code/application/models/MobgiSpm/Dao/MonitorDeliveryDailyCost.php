<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/29
 * Time: 14:04
 */
class MobgiSpm_Dao_MonitorDeliveryDailyCostModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'monitor_delivery_daily_cost_new';
    protected $_primary = 'id';
}
