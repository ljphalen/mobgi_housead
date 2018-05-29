<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: matt.liu
 * Date: 2018/3/19
 * Time: 15:53
 */
class MobgiMarket_Dao_AdCreativeModel extends Common_Dao_Base {
    public $adapter = 'mobgiMarket';
    protected $_name = 'market_adcreative';
    protected $_primary = 'id';
}