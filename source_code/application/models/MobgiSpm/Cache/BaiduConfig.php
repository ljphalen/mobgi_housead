<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/1/2
 * Time: 19:00
 */
class MobgiSpm_Cache_BaiduConfigModel extends Cache_Base{
    public $resource = 'spm';
    public $expire = 3600;
}