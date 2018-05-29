<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class MobgiSpm_Dao_MonitorReportModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'monitor_delivery_daily_cost_new';
    protected $_primary = 'id';
}
