<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/1
 * Time: 16:31
 */
class MobgiSpm_Dao_ActiveModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'active';
    protected $_primary = 'id';
    protected $_tableNum = 100;

    public function setTableId($id)
    {
        $tableId = intval($id % $this->_tableNum);
        $this->_name = 'active_' . $tableId;
    }
}