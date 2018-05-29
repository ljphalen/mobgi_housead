<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * MobgiApi_Dao_FlowAdTypeRelModel
 * @author rock.luo
 *
 */
class MobgiApi_Dao_FlowAdTypeRelModel extends Common_Dao_Base{
    public  $adapter = 'mobgiApi';
	protected $_name = 'flow_ad_type_rel';
	protected $_primary = 'id';
	
	
	public function getListGroupBy($start = 1, $limit = 10, $params = array(), $orderBy = array('update_time'=>'DESC')){
		$where = Db_Adapter_Pdo::sqlWhere($params);
		$sort = Db_Adapter_Pdo::sqlSort($orderBy);
		$sql = sprintf('select app_key FROM %s WHERE %s   GROUP BY app_key %s  LIMIT %d, %d', $this->getTableName(), $where, $sort, intval($start), intval($limit));
		return  $this->fetcthAll($sql);
	}
	
	public function getListCountGroupBy($params = array()){
		$where = Db_Adapter_Pdo::sqlWhere($params);
		$sql = sprintf('SELECT COUNT(DISTINCT app_key) FROM %s WHERE %s  ', $this->getTableName(), $where);
		return Db_Adapter_Pdo::fetchCloum($sql, 0);
	}
	
	
}
