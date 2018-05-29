<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/20
 * Time: 17:06
 */
class MobgiSpm_Dao_MonitorAndroidChannelModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'monitor_android_channel';
    protected $_primary = 'id';
}