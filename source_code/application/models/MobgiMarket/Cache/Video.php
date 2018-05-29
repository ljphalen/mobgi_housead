<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/21
 * Time: 20:04
 */
class MobgiMarket_Cache_VideoModel extends Cache_Base{
    public $resource = 'spm';
    public $expire = 86400;
}