<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/5
 * Time: 20:46
 */
class MobgiSpm_Dao_MonitorChannelDetailModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'monitor_channel_detail';
    protected $_primary = 'channel_id';
}