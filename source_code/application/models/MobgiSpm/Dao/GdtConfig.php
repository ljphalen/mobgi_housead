<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/12
 * Time: 11:17
 */
class MobgiSpm_Dao_GdtConfigModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'gdt_config';
    protected $_primary = 'id';
}