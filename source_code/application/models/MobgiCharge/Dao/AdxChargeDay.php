<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * MobgiCharge_Dao_AdxChargeDayModel
 * @author rock.luo
 *
 */

class MobgiCharge_Dao_AdxChargeDayModel extends Common_Dao_Base{
    public  $adapter = 'mobgiCharge';
	protected $_name = 'adx_charge_day';
	protected $_primary = 'id';
	
	

	public function getPreDaysTotal($params = array()){
	    $where = Db_Adapter_Pdo::sqlWhere($params);
	    $sql = sprintf('select sum(clicks) as clicks ,sum(actives) as actives , sum(views) as views, sum(amount) as amount,originality_id,ad_unit_id,ad_id FROM %s WHERE %s  ', $this->getTableName(), $where);
	    return   Db_Adapter_Pdo::fetchAll($sql);
	}

	public function getApiData($field,$where,$group,$order){
        $where = Db_Adapter_Pdo::sqlWhere($where);
        $sql = sprintf('select %s FROM %s WHERE %s GROUP BY %s ORDER BY %s', $field,$this->getTableName(),$where,$group,$order);
        return Db_Adapter_Pdo::fetchAll($sql);
    }

}
