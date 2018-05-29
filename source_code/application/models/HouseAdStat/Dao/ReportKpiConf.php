<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * HouseAdStat_Dao_StatMinuteModel
 * @author rock.luo
 *
 */
class HouseAdStat_Dao_ReportKpiConfModel extends Common_Dao_Base {
    public $adapter = 'houseadStat';
    protected $_name = 'report_kpi_conf';
    protected $_primary = 'user_id';


    public function updateKpi($params = array(), $kpis) {
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sql = sprintf('update %s set `kpis`="%s" WHERE %s  ', $this->getTableName(), $kpis, $where);
        return Db_Adapter_Pdo::fetch($sql);
    }


//UPDATE `report_kpi_conf` SET `kpis` = '2' WHERE (`user_id` = '1')
//INSERT INTO `report_kpi_conf` (`user_id`, `kpis`, `updated_at`, `created_at`) VALUES ('1', '1', '1', '1')
}
