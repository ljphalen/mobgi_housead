<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-6-27 11:28:19
 * $Id: DeviceDirect.php 62100 2017-6-27 11:28:19Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Dedelivery_Service_DeviceDirectModel{
    
    /**
     *
     * 查询一条结果集
     * @param array $search
     */
    public static function getBy($params) {
        if (!is_array($params)) return false;
        return self::_getDao()->getBy($params);
    }
    
    /**
     *
     * Enter description here ...
     * @param unknown_type $id
     */
    public static function getsBy($params,$orderBy = array('id'=>'ASC')) {
        if (!is_array($params)) return false;
        return self::_getDao()->getsBy($params,$orderBy);
    }
    
    /**
     *
     * @return Dedelivery_Dao_AdConfListModel
     */
    private static function _getDao() {
        return Common::getDao("Dedelivery_Dao_DeviceDirectModel");
    }
    
}