<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/19
 * Time: 11:16
 */
class MobgiSpm_Dao_MonitorChannelLabelModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'monitor_channel_label';
    protected $_primary = 'id';
}