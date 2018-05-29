<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Common_Dao_Base {

    /**
     *
     * 默认db
     * @var string
     */
    public $adapter = 'default';

    /**
     *
     * 构造函数
     */
    public function __construct() {
        $this->initAdapter();
    }

    /**
     * @throws Exception
     */
    public function initAdapter() {
        $adapter = $this->adapter . 'Adapter';
        if ($adapter != Db_Adapter_Pdo::getAdaterName()) {
            Db_Adapter_Pdo::setAdapter($adapter);
        }
    }


    /**
     * 获取键值对
     * @param string $field
     * @param array $where
     * @return array
     */
    public function getFields($field, $where = null) {
        $where = Db_Adapter_Pdo::sqlWhere($where);
        $sql = sprintf('SELECT %s FROM %s WHERE %s ', $field, $this->getTableName(), $where);
        $list = Db_Adapter_Pdo::fetchAll($sql);
        list($keyName, $valName) = explode(',', $field);
        $result = [];
        foreach ($list as $item) {
            $result[$item[$keyName]] = $item[$valName];
        }
        return $result;
    }


    /**
     * 获取相应条件下的所以数据的相应字段
     * @param string $field
     * @param array $where
     * @param array $orderBy
     * @return array
     */
    public function getAllByFields($field, $where = [], $orderBy = array()) {
        $sort = Db_Adapter_Pdo::sqlSort($orderBy);
        $sql = sprintf('SELECT %s FROM %s WHERE %s %s', $field, $this->getTableName(), Db_Adapter_Pdo::sqlWhere($where), $sort);
        return Db_Adapter_Pdo::fetchAll($sql);
    }

    /**
     * 获取单条数据
     * @param int $value
     * @return mixed
     */
    public function get($value) {
        $sql = sprintf('SELECT * FROM %s WHERE %s = %s', $this->getTableName(), $this->_primary, $value);
        return Db_Adapter_Pdo::fetch($sql);
    }

    /**
     * 获取多条数据
     * @param string $field
     * @param array $values
     * @return array
     */
    public function gets($field, $values) {
        $sql = sprintf('SELECT * FROM %s WHERE %s IN %s', $this->getTableName(), $field, Db_Adapter_Pdo::quoteArray($values));
        return Db_Adapter_Pdo::fetchAll($sql);
    }

    /**
     * 根据sql查询(单条)
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public function query($sql, $params = array()) {
        return Db_Adapter_Pdo::fetch($sql, $params);
    }

    /**
     * 根据sql查询（多条）
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetcthAll($sql, $params = array()) {
        return Db_Adapter_Pdo::fetchAll($sql, $params);
    }

    /**
     * 查询所有
     * @param array $orderBy
     * @return array
     */
    public function getAll($orderBy = array()) {
        $sort = Db_Adapter_Pdo::sqlSort($orderBy);
        $sql = sprintf('SELECT * FROM %s %s', $this->getTableName(), $sort);
        return Db_Adapter_Pdo::fetchAll($sql);
    }

    /**
     * 最大值
     * @param string $field
     * @param array $params
     * @param array $params
     * @return string
     */
    public function max($field = "", $params = array()) {
        if ($field == "") $field = $this->_primary;
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sql = sprintf('SELECT max(%s) AS num FROM %s WHERE %s ', $field, $this->getTableName(), $where);
        return Db_Adapter_Pdo::fetchCloum($sql, 0);
    }


    /**
     * 字段求和
     * @param string $field
     * @param array $params
     * @param array $params
     * @return string
     */
    public function sum($field = "", $params = array()) {
        if ($field == "") $field = $this->_primary;
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sql = sprintf('SELECT sum(%s) AS num FROM %s WHERE %s ', $field, $this->getTableName(), $where);
        return Db_Adapter_Pdo::fetchCloum($sql, 0);
    }

    /**
     * 查询所有数据
     * @param string $field
     * @param array $params
     * @return string
     */
    public function min($field = "", $params = array()) {
        if ($field == "") $field = $this->_primary;
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sql = sprintf('SELECT min(%s) AS num FROM %s WHERE %s ', $field, $this->getTableName(), $where);
        return Db_Adapter_Pdo::fetchCloum($sql, 0);
    }

    /**
     * 获取分页列表数据
     * @param int $start
     * @param int $limit
     * @param array $params
     * @param array $orderBy
     * @return array
     */
    public function getList($start = 0, $limit = 20, array $params = array(), array $orderBy = array()) {
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sort = Db_Adapter_Pdo::sqlSort($orderBy);
        $sql = sprintf('SELECT * FROM %s WHERE %s %s LIMIT %d,%d', $this->getTableName(), $where, $sort, $start, intval($limit));
        return Db_Adapter_Pdo::fetchAll($sql);
    }

    /**
     * 根据条件查询（单条）
     * @param array $params
     * @param array $orderBy
     * @return bool|mixed
     */
    public function getBy($params, array $orderBy = array()) {
        if (!is_array($params)) return false;
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sort = Db_Adapter_Pdo::sqlSort($orderBy);
        $sql = sprintf('SELECT * FROM %s WHERE %s %s LIMIT 1', $this->getTableName(), $where, $sort);
        return Db_Adapter_Pdo::fetch($sql);
    }

    /**
     * 根据条件查询（多条）
     * @param array $params
     * @param array $orderBy
     * @return array|bool
     */
    public function getsBy($params, $orderBy = array()) {
        if (!is_array($params) || !is_array($orderBy)) return false;
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sort = Db_Adapter_Pdo::sqlSort($orderBy);
        $sql = sprintf('SELECT * FROM %s WHERE %s %s', $this->getTableName(), $where, $sort);
        return Db_Adapter_Pdo::fetchAll($sql);
    }

    /**
     * 根据参数统计总数
     * @param array $params
     * @return string
     */
    public function count($params = array()) {
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sql = sprintf('SELECT COUNT(*) FROM %s WHERE %s', $this->getTableName(), $where);
        return Db_Adapter_Pdo::fetchCloum($sql, 0);
    }

    /**
     * 根据参数分组统计总数
     * @param array $params
     * @param string $groupBy
     * @return string
     */
    public function groupCount($params = array(), $groupBy = '') {
        $field = $groupBy;
        if ($groupBy) {
            $groupBy = ' GROUP BY ' . $groupBy;
        } else {
            return array();
        }
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sql = sprintf('SELECT %s,COUNT(*) AS count_num FROM %s WHERE %s %s', $field, $this->getTableName(), $where, $groupBy);
        return $this->fetcthAll($sql);
    }

    /**
     * 根据where条件语句查询
     * @param int $start
     * @param int $limit
     * @param string $sqlWhere
     * @param array $orderBy
     * @return array
     */
    public function searchBy($start, $limit, $sqlWhere = 1, array $orderBy = array()) {
        $sort = Db_Adapter_Pdo::sqlSort($orderBy);
        $sql = sprintf('SELECT * FROM %s WHERE %s %s LIMIT %d,%d', $this->getTableName(), $sqlWhere, $sort, $start, $limit);
        return $this->fetcthAll($sql);
    }

    /**
     * 根据where条件语句统计
     * @param string $sqlWhere
     * @return string
     */
    public function searchCount($sqlWhere) {
        $sql = sprintf('SELECT COUNT(*) FROM %s WHERE %s', $this->getTableName(), $sqlWhere);
        return Db_Adapter_Pdo::fetchCloum($sql, 0);
    }

    /**
     * 插入数据
     * @param array $data
     * @return bool|int
     */
    public function insert($data) {
        if (!is_array($data)) return false;
        $sql = sprintf('INSERT INTO %s SET %s', $this->getTableName(), Db_Adapter_Pdo::sqlSingle($data));
        return Db_Adapter_Pdo::execute($sql);
    }

    /**
     * 插入数据
     * @param array $data
     * @return bool|int
     */
    public function mutiInsert($data) {
        if (!is_array($data)) return false;
        $sql = sprintf('INSERT INTO %s VALUES %s', $this->getTableName(), Db_Adapter_Pdo::quoteMultiArray($data));
        return Db_Adapter_Pdo::execute($sql);
    }

    /**
     * 任意字段的多插入
     * @param array $data
     */
    public function mutiFieldInsert($data) {
        if (!is_array($data)) return false;
        $sql = sprintf('INSERT INTO %s %s VALUES %s', $this->getTableName(), Db_Adapter_Pdo::sqlKey($data), Db_Adapter_Pdo::quoteMultiArray($data));
        return Db_Adapter_Pdo::execute($sql);
    }

    /**
     * 更新数据并返回影响行数
     * @param array $data
     * @param mixed $value
     * @return bool|int
     */
    public function update($data, $value) {
        if (!is_array($data)) return false;
        $sql = sprintf('UPDATE %s SET %s WHERE %s = %d', $this->getTableName(), Db_Adapter_Pdo::sqlSingle($data), $this->_primary, intval($value));
        return Db_Adapter_Pdo::execute($sql, array(), false);
    }

    /**
     * 批量更新并返回执行结果
     * @param string $field
     * @param array $values
     * @param array $data
     * @return bool|int
     */
    public function updates($field, $values, $data) {
        if (!$field || !is_array($values) || empty($values)) return false;
        $sql = sprintf('UPDATE %s SET %s WHERE %s IN %s', $this->getTableName(), Db_Adapter_Pdo::sqlSingle($data), $field, Db_Adapter_Pdo::quoteArray($values));
        return Db_Adapter_Pdo::execute($sql, array(), false);
    }

    /**
     * 指量更新
     * @param array $data
     * @param array $params
     * @return boolean
     */
    public function updateBy($data, $params) {
        if (!is_array($data) || !is_array($params)) return false;
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sql = sprintf('UPDATE %s SET %s WHERE %s', $this->getTableName(), Db_Adapter_Pdo::sqlSingle($data), $where);
        return Db_Adapter_Pdo::execute($sql, array(), false);
    }

    /**
     * 指量更新不带条件
     * @param array $data
     * @param array $params
     * @return boolean
     */
    public function updateByNoWhere($data) {
        if (!is_array($data)) return false;
        $sql = sprintf('UPDATE %s SET %s WHERE %s', $this->getTableName(), Db_Adapter_Pdo::sqlSingle($data), '1=1');
        return Db_Adapter_Pdo::execute($sql, array(), false);
    }


    /**
     * 替换
     * @param array $data
     * @return bool|int
     */
    public function replace($data) {
        if (!is_array($data)) return false;
        $sql = sprintf('REPLACE %s SET %s', $this->getTableName(), Db_Adapter_Pdo::sqlSingle($data));
        return Db_Adapter_Pdo::execute($sql, array(), false);
    }

    /**
     * 自增
     * @param string $field
     * @param array $params
     * @param int $step
     * @return bool|int
     */
    public function increment($field, $params, $step = 1) {
        if (!$field || !$params) return false;
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sql = sprintf('UPDATE %s SET %s=%s+%d WHERE %s ', $this->getTableName(), $field, $field, $step, $where);
        return Db_Adapter_Pdo::execute($sql, array(), false);
    }

    /**
     * 删除数据并返回影响行数
     * @param int $value
     * @return bool|int
     */
    public function delete($value) {
        $sql = sprintf('DELETE FROM %s WHERE %s = %d', $this->getTableName(), $this->_primary, intval($value));
        return Db_Adapter_Pdo::execute($sql, array(), true);
    }

    /**
     * 清除指定的表
     * @param int $value
     * @return bool|int
     */

    public function deleteDB($table) {
        $sql = sprintf('DELETE FROM %s', $table);
        return Db_Adapter_Pdo::execute($sql, array(), true);
    }

    /**
     * 删除多条数据并返回执行结果
     * @param string $field
     * @param array $values
     * @return bool|int
     */
    public function deletes($field, $values) {
        if (!$field || !is_array($values)) return false;
        $sql = sprintf('DELETE FROM %s WHERE %s IN %s', $this->getTableName(), $field, Db_Adapter_Pdo::quoteArray($values));
        return Db_Adapter_Pdo::execute($sql, array(), false);
    }

    /**
     * 通过条件删除
     * @param array $params
     * @return boolean
     */
    public function deleteBy($params) {
        if (!is_array($params)) return false;
        $where = Db_Adapter_Pdo::sqlWhere($params);
        $sql = sprintf('DELETE FROM %s WHERE %s', $this->getTableName(), $where);
        return Db_Adapter_Pdo::execute($sql, array(), true);
    }

    /**
     * 获取最后插入的ID
     */
    public function getLastInsertId() {
        return Db_Adapter_Pdo::getLastInsertId();
    }


    /**
     * 分页按条件联表(内联)搜索，支持模糊查询
     * @param string $table 表名
     * @param string $on 联表条件
     * @param int $cur_page 当前页数
     * @param int $pagesize 每页显示记录数
     * @param string $condition 查询条件，sql语句
     * @param array $orderBy 排序数组
     * @return array
     */
    public function getSearchByPageInnerJoin($table, $on, $cur_page, $pagesize, $condition = '1', $orderBy = array(), $field = '*') {
        $data = $this->searchByInnerJoin($table, $on, $cur_page, $pagesize, $condition, $orderBy, $field);
        $count = $this->searchCountInnerJoin($table, $on, $condition);
        return array('lists' => $data, 'count' => $count);
    }

    /**
     * 分页按条件联表(左联)搜索，支持模糊查询
     * @param string $table 表名
     * @param string $on 联表条件
     * @param int $cur_page 当前页数
     * @param int $pagesize 每页显示记录数
     * @param string $condition 查询条件，sql语句
     * @param array $orderBy 排序数组
     * @return array
     */
    public function getSearchByPageLeftJoin($table, $on, $cur_page, $pagesize, $condition = '1', $orderBy = array(), $field = '*') {
        $data = $this->searchByLeftJoin($table, $on, $cur_page, $pagesize, $condition, $orderBy, $field);
        $count = $this->searchCountLeftJoin($table, $on, $condition);
        return array('lists' => $data, 'count' => $count);
    }


    public function searchByLeftJoin($table, $on, $start, $limit, $sqlWhere = 1, array $orderBy = array(), $field = '*') {
        $sort = Db_Adapter_Pdo::sqlSort($orderBy);
        $sql = sprintf('SELECT %s FROM %s AS a LEFT JOIN %s AS b ON %s WHERE %s %s LIMIT %d,%d', $field, $this->getTableName(), $table, $on, $sqlWhere, $sort, $start, $limit);
        return $this->fetcthAll($sql);
    }

    public function searchByLeftJoinNoLimit($table, $on, $sqlWhere = 1, array $orderBy = array(), $field = '*') {
        $sort = Db_Adapter_Pdo::sqlSort($orderBy);
        $sql = sprintf('SELECT %s FROM %s AS a LEFT JOIN %s AS b ON %s WHERE %s %s ', $field, $this->getTableName(), $table, $on, $sqlWhere, $sort);
        return $this->fetcthAll($sql);
    }

    /**
     * 根据条件联表（左联）统计总数
     * @param string $table
     * @param string $on
     * @param string $sqlWhere
     * @return string
     */
    public function searchCountLeftJoin($table, $on, $sqlWhere) {
        $sql = sprintf('SELECT COUNT(*) FROM %s AS a LEFT JOIN %s AS b ON %s WHERE %s', $this->getTableName(), $table, $on, $sqlWhere);
        return Db_Adapter_Pdo::fetchCloum($sql, 0);
    }


    /**
     * 按条件联表(内联)搜索，支持模糊查询
     * @param string $table 表名字
     * @param unknown $on 联表条件
     * @param unknown $start 当前页数
     * @param unknown $limit 每页显示记录数
     * @param number $sqlWhere 查询条件，sql语句
     * @param array $orderBy 排序数组
     * @return array
     */
    public function searchByInnerJoin($table, $on, $start, $limit, $sqlWhere = 1, array $orderBy = array(), $field = '*') {
        $sort = Db_Adapter_Pdo::sqlSort($orderBy);
        $sql = sprintf('SELECT %s FROM %s AS a INNER JOIN %s AS b ON %s WHERE %s %s LIMIT %d,%d', $field, $this->getTableName(), $table, $on, $sqlWhere, $sort, $start, $limit);
        return $this->fetcthAll($sql);
    }

    /**
     * 按条件联表(内联)搜索，支持模糊查询
     * @param string $table 表名字
     * @param unknown $on 联表条件
     * @param unknown $start 当前页数
     * @param unknown $limit 每页显示记录数
     * @param number $sqlWhere 查询条件，sql语句
     * @param array $orderBy 排序数组
     * @param string $groupBy 分组
     * @return array
     */
    public function searchByInnerJoinGroup($table, $on, $start, $limit, $sqlWhere = 1, array $orderBy = array(), $groupBy = '', $field = '*') {
        $sort = Db_Adapter_Pdo::sqlSort($orderBy);
        if ($groupBy) {
            $group = ' group by ' . $groupBy;
        }
        $sql = sprintf('SELECT %s FROM %s AS a INNER JOIN %s AS b ON %s WHERE %s %s %s LIMIT %d,%d', $field, $this->getTableName(), $table, $on, $sqlWhere, $sort, $group, $start, $limit);
        return $this->fetcthAll($sql);
    }

    /**
     * 根据条件联表（内联）统计总数
     * @param string $table
     * @param string $on
     * @param string $sqlWhere
     * @return string
     */
    public function searchCountInnerJoin($table, $on, $sqlWhere) {
        $sql = sprintf('SELECT COUNT(*) FROM %s AS a INNER JOIN %s AS b ON %s WHERE %s', $this->getTableName(), $table, $on, $sqlWhere);
        return Db_Adapter_Pdo::fetchCloum($sql, 0);
    }


    /**
     *
     * 获取表名
     */
    public function getTableName() {
        return $this->_name;
    }


    public function getData($fields, $where, $groupBy, $orderBy = null, $limit = 0) {
        if ($fields == '') return false;
        if (!is_array($where)) return false;
        $where = Db_Adapter_Pdo::sqlWhere($where);
        if ($orderBy) {
            if (is_array($orderBy)) {
                $orderStr = [];
                foreach ($orderBy as $key => $val) {
                    $orderStr[] = $key . ' ' . $val;
                }
                $orderBy = join(',', $orderStr);
            }
            $orderBy = 'order by ' . $orderBy;
        }
        $limit = $limit > 0 ? 'limit ' . $limit : '';
        $sql = sprintf('select %s FROM %s WHERE %s %s %s %s', $fields, $this->getTableName(), $where, $groupBy, $orderBy, $limit);
        return Db_Adapter_Pdo::fetchAll($sql);
    }
}
