<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/19
 * Time: 14:41
 */
class MobgiMarket_Cache_UserAuthModel extends Cache_Base{
    public $resource = 'spm';
    public $expire = 86400;
}