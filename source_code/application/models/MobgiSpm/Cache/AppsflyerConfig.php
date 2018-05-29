<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/1/2
 * Time: 18:59
 */
class MobgiSpm_Cache_AppsflyerConfigModel extends Cache_Base{
    public $resource = 'spm';
    public $expire = 3600;
}