<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-7-26 14:48:41
 * $Id: Active.php 62100 2017-7-26 14:48:41Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class MobgiCharge_Dao_ActiveModel extends Common_Dao_Base {
    public $adapter = 'mobgiCharge';
    protected $_name = 'active';
    protected $_primary = 'id';
    
}

