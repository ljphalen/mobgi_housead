<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: matt.liu
 * Date: 2017/12/15
 * Time: 16:29
 */
class MobgiSpm_Dao_MonitorChannelGroupSpconfigModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'monitor_channel_group_spconfig';
    protected $_primary = 'id';
}