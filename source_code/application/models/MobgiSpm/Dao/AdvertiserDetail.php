<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/2/27
 * Time: 11:24
 */
class MobgiSpm_Dao_AdvertiserDetailModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'advertiser_detail';
    protected $_primary = 'id';
}