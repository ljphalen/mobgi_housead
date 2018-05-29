<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: atom.zhan
 * Date: 2017/12/29
 * Time: 17:41
 */
class MobgiSpm_Dao_RainbowLtvModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'rainbow_ltv';
    protected $_primary = 'id';
}