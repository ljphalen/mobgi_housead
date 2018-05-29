<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/22
 * Time: 11:05
 */
class MobgiSpm_Cache_MonitorActivityModel extends Cache_Base{
    public $resource = 'spm';
    public $expire = 3600;
}