<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * MobgiCharge_Dao_AdxChargeMinuteModel
 * @author rock.luo
 *
 */

class  MobgiCharge_Dao_AdxChargeMinuteModel extends Common_Dao_Base{
    public  $adapter = 'mobgiCharge';
	protected $_name = 'adx_charge_minute';
	protected $_primary = 'id';
	
	
	
	public function getOriginalitySumResult($params = array()){
	    $where = Db_Adapter_Pdo::sqlWhere($params);
	    $sql = sprintf('select sum(clicks) as clicks , sum(actives) as actives , sum(views) as views, sum(amount) as amount ,originality_id FROM %s WHERE %s  ', $this->getTableName(), $where);
	    return   Db_Adapter_Pdo::fetch($sql);
	}
	
}
