<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Dedelivery_Dao_OriginalityRelationModel
 * @author rock.luo
 *
 */
class Dedelivery_Dao_OriginalityRelationModel extends Common_Dao_Base {
    protected $_name = 'delivery_originality_relation';
    protected $_primary = 'id';

    public function getRelationList($params = array()) {
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sql = sprintf('select id as originality_id,originality_type,ad_id,unit_id,account_id FROM %s WHERE %s', $this->getTableName(), $where);
        return $this->fetcthAll($sql);

    }

}
