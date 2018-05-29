<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/1/11
 * Time: 20:45
 */
class MobgiSpm_Dao_AdidModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpmAbroad';
    protected $_name = 'adid';
    protected $_primary = 'id';
    protected $_tableNum = 100;

    public function setTableId($id)
    {
        $tableId = intval($id % $this->_tableNum);
        $this->_name = 'adid_' . $tableId;
    }
}