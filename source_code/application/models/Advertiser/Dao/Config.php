<?php
/**
 *
 * Advertiser_Dao_ConfigModel
 * @author rock.luo
 *
 */

if (!defined('BASE_PATH')) exit('Access Denied!');
class Advertiser_Dao_ConfigModel extends Common_Dao_Base{
	protected $_name = 'advertiser_config';

	
	public function updateByKey($key, $value, $operator) {
		$createTime = date('Y-m-d H:i:s');
		$sql = sprintf('REPLACE INTO %s VALUES (%s,%s,%s,%s)', $this->getTableName(), Db_Adapter_Pdo::quote($key), Db_Adapter_Pdo::quote($value),$operator,Db_Adapter_Pdo::quote($createTime));
		return Db_Adapter_Pdo::execute($sql, array(), false);
	}


	
}