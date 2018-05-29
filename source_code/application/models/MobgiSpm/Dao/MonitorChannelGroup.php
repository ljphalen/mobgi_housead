<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/15
 * Time: 16:29
 */
class MobgiSpm_Dao_MonitorChannelGroupModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'monitor_channel_group';
    protected $_primary = 'id';
}