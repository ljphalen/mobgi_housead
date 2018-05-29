<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: matt.liu
 * Date: 2017/12/5
 * Time: 20:16
 */
class MobgiSpm_Dao_MaterialLabelsModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'material_labels';
    protected $_primary = 'id';
}