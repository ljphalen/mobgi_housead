<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/21
 * Time: 20:04
 */
class MobgiMarket_Dao_VideoModel extends Common_Dao_Base {
    public $adapter = 'mobgiMarket';
    protected $_name = 'market_video';
    protected $_primary = 'id';
}