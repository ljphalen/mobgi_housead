<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/2/6
 * Time: 11:24
 */
class MobgiSpm_Dao_BackFlowModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'backflow';
    protected $_primary = 'id';
}