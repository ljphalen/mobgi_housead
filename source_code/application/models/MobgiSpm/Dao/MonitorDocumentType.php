<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/1/31
 * Time: 20:41
 */
class MobgiSpm_Dao_MonitorDocumentTypeModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'monitor_document_type';
    protected $_primary = 'id';
}