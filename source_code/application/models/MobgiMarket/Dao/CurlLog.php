<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/5/16
 * Time: 11:01
 */
class MobgiMarket_Dao_CurlLogModel extends Common_Dao_Base {
    public $adapter = 'mobgiMarket';
    protected $_name = 'market_curl_log';
    protected $_primary = 'id';
}