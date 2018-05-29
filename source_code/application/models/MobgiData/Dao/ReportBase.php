<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * MobgiData_Dao_ReportBaseModel
 * @author atom.zhan
 *
 */
class MobgiData_Dao_ReportBaseModel extends Common_Dao_Base {
    public $adapter = 'reportData';
    protected $_name = 'report_base';
    protected $_primary = 'id';


    public function getData($fields, $where, $groupBy, $orderBy = null, $limit = 0) {
        if ($fields == '') return false;
        if (!is_array($where)) return false;
        $where = Db_Adapter_Pdo::sqlWhere($where);
        $sql = sprintf('select %s FROM %s WHERE %s %s ', $fields, $this->getTableName(), $where, $groupBy);
        return Db_Adapter_Pdo::fetchAll($sql);
    }


}
