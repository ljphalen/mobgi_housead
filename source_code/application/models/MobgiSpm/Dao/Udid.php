<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/10
 * Time: 14:42
 */
class MobgiSpm_Dao_UdidModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'udid';
    protected $_primary = 'id';
    protected $_tableNum = 100;

    public function setTableId($id)
    {
        $tableId = intval($id % $this->_tableNum);
        $this->_name = 'udid_' . $tableId;
    }
}