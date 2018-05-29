<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/22
 * Time: 14:36
 */
class MobgiSpm_Service_DaoModel {
    /**
     * 获取Dao
     * @param string $dao
     * @param null $id
     * @return Common_Dao_Base
     */
    public static function getDao($dao,$id=null) {
        $dao= Common::getDao('MobgiSpm_Dao_' . $dao . 'Model');
        if(!is_null($id)){
            $dao->setTableId($id);
        }
        return $dao;
    }

    /**
     * 获取ApiDao
     * @param string $dao
     * @return Common_Dao_Base
     */
    public static function getApiDao($dao) {
        return Common::getDao('MobgiApi_Dao_' . $dao . 'Model');
    }

    /**
     * 获取MonitorDao
     * @param string $dao
     * @return Common_Dao_Base
     */
    public static function getMonitorDao($dao) {
        return Common::getDao('MobgiMonitor_Dao_' . $dao . 'Model');
    }

}