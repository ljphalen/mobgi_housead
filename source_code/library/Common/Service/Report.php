<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * 报表通用服务
 * @author atom.zhan
 *
 */
class Common_Service_Report {
    /**
     * 获取Dao
     * @param $dao
     * @return Common_Dao_Base
     */
    public static function getDao($dao) {
        return Common::getDao('MobgiData_Dao_' . $dao . 'Model');
    }

    /**
     * 获取ApiDao
     * @param $dao
     * @return Common_Dao_Base
     */
    public static function getApiDao($dao) {
        return Common::getDao('MobgiApi_Dao_' . $dao . 'Model');
    }

    /**
     * 获取AdminDao
     * @param $dao
     * @return Common_Dao_Base
     */
    public static function getAdminDao($dao) {
        return Common::getDao('Admin_Dao_' . $dao . 'Model');
    }

    /**
     * 获取SpmDao
     * @param $dao
     * @return Common_Dao_Base
     */
    public static function getSpmDao($dao) {
        return Common::getDao('MobgiSpm_Dao_' . $dao . 'Model');
    }

    /**
     * 获取MonitorDao
     * @param $dao
     * @return Common_Dao_Base
     */
    public static function getMonitorDao($dao) {
        return Common::getDao('MobgiMonitor_Dao_' . $dao . 'Model');
    }


    /**
     * 获取用户kpi
     * @param $userId
     * @param $type
     * @return bool|mixed
     */
    public static function getUserKpi($userId, $type) {
        $dao = self::getDao('AdminKpis');
        $where['user_id'] = $userId;
        $where['type'] = $type;
        $result = $dao->getBy($where);
        return $result;
    }

    /**
     * 更新kpi
     * @param $userId
     * @param $type
     * @param $kpis
     * @return bool|int
     */
    public static function updateUserKpi($userId, $type, $kpis) {
        $info = self::getUserKpi($userId, $type);
        $data['kpis'] = $kpis;
        if (isset($info['id'])) {
            $result = self::getDao('AdminKpis')->update($data, $info['id']);
        } else {
            $data['type'] = $type;
            $data['user_id'] = $userId;
            $result = self::getDao('AdminKpis')->insert($data);
        }
        return $result;
    }

}