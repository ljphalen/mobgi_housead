<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Advertiser_Dao_OriginalityRelationPositionModel
 * @author rock.luo
 *
 */
class Advertiser_Dao_OriginalityRelationPositionModel extends Common_Dao_Base{
	protected $_name = 'advertiser_originality_relation_position';
	protected $_primary = 'id';
	
	

    public function getAppList($start = 1, $limit = 10, $params = array(), $orderBy = array('originality_type'=>'DESC', 'create_time'=>'DESC')){
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sort = Db_Adapter_Pdo::sqlSort($orderBy);
        $sql = sprintf('select app_key,app_name,ad_position_key,ad_position_name,status,originality_type,appkey_config_id,policy_config_id FROM %s WHERE %s   GROUP BY app_key %s  LIMIT %d, %d', $this->getTableName(), $where, $sort, intval($start), intval($limit));
        return  $this->fetcthAll($sql);
    }

    public function getAppListCount($params = array()){
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sql = sprintf('SELECT COUNT(DISTINCT app_key) FROM %s WHERE %s  ', $this->getTableName(), $where);
        return Db_Adapter_Pdo::fetchCloum($sql, 0);
    }


}
