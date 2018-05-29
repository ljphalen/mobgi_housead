<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * MobgiApi_Dao_ChannelModel
 * @author rock.luo
 *
 */
class MobgiApi_Dao_ChannelModel extends Common_Dao_Base{
    public  $adapter = 'mobgiApi';
	protected $_name = 'channel';
	protected $_primary = 'id';
	
    /**
     * 获取每个渠道组下的一个子渠道信息
     * @param type $fields
     * @param int $where
     * @param string $groupby
     * @return type
     */
    public function getOneParentSubChannel($fields = "*", $where = 1, $groupbyfield='') {
        $groupby = 'group by '.$groupbyfield;
        $sql = sprintf('select %s FROM %s WHERE  %s %s', $fields, $this->getTableName(), $where, $groupby);
        return Db_Adapter_Pdo::fetchAll($sql);
    }
	
}
