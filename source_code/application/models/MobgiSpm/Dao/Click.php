<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/12
 * Time: 14:07
 */
class MobgiSpm_Dao_ClickModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'click';
    protected $_primary = 'id';
}