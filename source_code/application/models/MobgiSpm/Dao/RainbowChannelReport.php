<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/22
 * Time: 11:03
 */
class MobgiSpm_Dao_RainbowChannelReportModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'rainbow_channel_report';
    protected $_primary = 'id';
}
