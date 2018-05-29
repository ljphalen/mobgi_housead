<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/26
 * Time: 10:15
 */
class MobgiSpm_Dao_BaiduConfigModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'baidu_config';
    protected $_primary = 'id';
}