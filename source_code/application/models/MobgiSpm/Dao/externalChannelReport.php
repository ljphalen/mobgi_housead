<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/1
 * Time: 18:04
 */
class MobgiSpm_Dao_externalChannelReportModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpmData';
    protected $_name = 'rainbow_report_global';
    protected $_primary = 'id';
}