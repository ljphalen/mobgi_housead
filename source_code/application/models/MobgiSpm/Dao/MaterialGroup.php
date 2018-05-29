<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: matt.liu
 * Date: 2017/12/5
 * Time: 20:16
 */
class MobgiSpm_Dao_MaterialGroupModel extends Common_Dao_Base {
    public $adapter = 'mobgiSpm';
    protected $_name = 'material_group';
    protected $_primary = 'id';


    /**
     * 获取分页列表数据
     * @param int $start
     * @param int $limit
     * @param array $params
     * @param array $orderBy
     * @return array
     */
    public function getOrList($start = 0, $limit = 20, array $params = array(), array $orderBy = array()) {
        $where = Db_Adapter_Pdo::oRsqlWhere($params);
        $sort = Db_Adapter_Pdo::sqlSort($orderBy);
        $sql = sprintf('SELECT * FROM %s WHERE %s %s LIMIT %d,%d', $this->getTableName(), $where, $sort, $start, intval($limit));
//        var_dump($sql);
        return Db_Adapter_Pdo::fetchAll($sql);
    }

    /**
     * 根据参数统计总数
     * @param array $params
     * @return string
     */
    public function oRcount($params = array()) {
        $where = Db_Adapter_Pdo::oRsqlWhere($params);
        $sql = sprintf('SELECT COUNT(*) FROM %s WHERE %s', $this->getTableName(), $where);
        return Db_Adapter_Pdo::fetchCloum($sql, 0);
    }

}