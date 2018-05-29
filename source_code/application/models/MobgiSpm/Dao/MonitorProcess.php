<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/27
 * Time: 16:09
 */
class MobgiSpm_Dao_MonitorProcessModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'monitor_process';
    protected $_primary = 'id';
}