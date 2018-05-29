<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: matt.liu
 * Date: 2017/12/22
 * Time: 10:29
 */
class MobgiSpm_Dao_RainbowRetentionModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'rainbow_retention';
    protected $_primary = 'id';
}