<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * MobgiData_Dao_ReportCityModel
 * @author atom.zhan
 *
 */
class MobgiData_Dao_ReportLtvDauModel extends MobgiData_Dao_ReportBaseModel {
    protected $_name = 'report_ltv_dau';

    public function getCreateDau($fields, $where, $groupBy, $orderBy) {
        if ($fields == '') return false;
        if (!is_array($where)) return false;
        $where[] = 'create_date=action_date';
        $where = Db_Adapter_Pdo::sqlWhere($where);
        $sql = sprintf('select %s FROM %s WHERE %s %s ', $fields, $this->getTableName(), $where, $groupBy);
        return Db_Adapter_Pdo::fetchAll($sql);
    }

}
