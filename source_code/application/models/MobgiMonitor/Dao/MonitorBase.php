<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * MobgiMonitor_Dao_MonitorBaseModel
 * @author atom.zhan
 *
 */
class MobgiMonitor_Dao_MonitorBaseModel extends Common_Dao_Base {
    public $adapter = 'mobgiMonitor';
    protected $_name = 'monitor_task';
    protected $_primary = 'id';


    public function getData($fields, $where, $groupBy, $orderBy) {
        if ($fields == '') return false;
        if (!is_array($where)) return false;
        $where = Db_Adapter_Pdo::sqlWhere($where);
        $sql = sprintf('select %s FROM %s WHERE %s %s ', $fields, $this->getTableName(), $where, $groupBy);
        return Db_Adapter_Pdo::fetchAll($sql);
    }


}
