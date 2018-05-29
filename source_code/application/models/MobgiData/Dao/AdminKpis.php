<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * MobgiData_Dao_AdminKpisModel
 * @author atom.zhan
 *
 */
class MobgiData_Dao_AdminKpisModel extends MobgiData_Dao_ReportBaseModel {
    public $adapter = 'mobgiAdmin';
    protected $_name = 'admin_kpis';

    public function getKpis($userId, $type) {
        $where['user_id'] = $userId;
        $where['type'] = $type;
        $where = Db_Adapter_Pdo::sqlWhere($where);

        $sql = sprintf('select id,kpis FROM %s WHERE %s  ', $this->getTableName(), $where);
        return Db_Adapter_Pdo::fetch($sql);
    }

    public function updateKpi($userId, $type, $kpis) {
        $oldKpis = self::getKpis($userId, $type);

        $where['user_id'] = $userId;
        $where['type'] = $type;
        $data['kpis'] = $kpis;

        if ($oldKpis) {
            $result = MobgiData_Servers_MobgiModel::getDao('AdminKpis')->insert($data);
        } else {
            $result = MobgiData_Servers_MobgiModel::getDao('AdminKpis')->update($data, $oldKpis['id']);
        }
        return $result;
    }


}
