<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/1/2
 * Time: 19:04
 */
class MobgiSpm_Cache_MonitorAppModel extends Cache_Base{
    public $resource = 'spm';
    public $expire = 3600;
}