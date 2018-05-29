<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Common_Service_Base {
    private static $hasBeginTrans = false;    // 是否在事务中
    private static $refreshList = array();    // 刷新逻辑列表

    /**
     * beginTransaction
     * @return boolean
     */
    public static function beginTransaction($adapter = null) {
        if (!is_null($adapter)) {
            Db_Adapter_Pdo::setAdapter($adapter . 'Adapter');
        }
        try {
            self::$hasBeginTrans = true;
            return Db_Adapter_Pdo::getPDO()->beginTransaction();
        } catch (Exception $e) {
            self::$hasBeginTrans = false;
            if (stripos($e->getMessage(), "active transaction")) {
                return false;
            } else {
                return false;
            }
        }
    }

    /**
     * rollback
     * @return boolean
     */
    public static function rollBack() {
        $result = Db_Adapter_Pdo::getPDO()->rollBack();
        self::_runRefreshFuncs();
        return $result;
    }

    /**
     * commit
     * @return boolean
     */
    public static function commit() {
        $result = Db_Adapter_Pdo::getPDO()->commit();
        self::_runRefreshFuncs();
        return $result;
    }

    /**
     * 现网mysql配置的事务类型是READ-COMMITTED.要保证redis的sql缓存正确,需要
     * 在事务提交或回滚后,刷新(失效)涉及的sql缓存.
     *
     * 此函数为登记需要执行的刷新逻辑,并确保不重复登记.
     *
     * @param unknown $class
     * @param unknown $method
     * @param unknown $args
     */
    public static function recordRefreshFunc($class, $method, $args = array()) {
        if (!self::$hasBeginTrans) return;
        $value = array(
            'class' => $class,
            'method' => $method,
            'args' => $args
        );
        $key = json_encode($value);
        self::$refreshList[$key] = $value;
    }

    /**
     * 事务提交或回滚后,执行所有刷新逻辑
     */
    private static function _runRefreshFuncs() {
        foreach (self::$refreshList as $key => $value) {
            call_user_func_array(array($value['class'], $value['method']), $value['args']);
        }
        self::$hasBeginTrans = false;
        self::$refreshList = array();
    }

    /**
     * 获取Dao
     * @param $dao
     * @return Common_Dao_Base
     */
    public static function getDao($dao) {
        return Common::getDao('MobgiData_Dao_' . $dao . 'Model');
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
     * 获取ApiDao
     * @param $dao
     * @return Common_Dao_Base
     */
    public static function getApiDao($dao) {
        return Common::getDao('MobgiApi_Dao_' . $dao . 'Model');
    }

    /**
     * 获取SpmDao
     * @param $dao
     * @param $id
     * @return Common_Dao_Base
     */
    public static function getSpmDao($dao, $id = null) {
        $dao = Common::getDao('MobgiSpm_Dao_' . $dao . 'Model');
        if (!is_null($id)) {
            $dao->setTableId($id);
        }
        return $dao;
    }

    /**
     * 获取MarketDao
     * @param $dao
     * @param $id
     * @return Common_Dao_Base
     */
    public static function getMarketDao($dao, $id = null) {
        $dao = Common::getDao('MobgiMarket_Dao_' . $dao . 'Model');
        if (!is_null($id)) {
            $dao->setTableId($id);
        }
        return $dao;
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