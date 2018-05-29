<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/26
 * Time: 10:21
 */
class MobgiSpm_Dao_MonitorPayChannelModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'monitor_paychannel';
    protected $_primary = 'id';
}