<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * HouseAdStat_Dao_ReportBaseModel
 * @author atom.zhan
 *
 */
class HouseAdStat_Dao_ReportBaseModel extends Common_Dao_Base {
    public $adapter = 'houseadStat';
    protected $_name = 'report_base';
    protected $_primary = 'id';


    public function getReportData($fields, $where, $groupby, $orderBy) {
        $where = Db_Adapter_Pdo::sqlWhere($where);
        $sql = sprintf('select %s FROM %s WHERE  %s %s', $fields, $this->getTableName(), $where, $groupby);
        return Db_Adapter_Pdo::fetchAll($sql);
    }
}
