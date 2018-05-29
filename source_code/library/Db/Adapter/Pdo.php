<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Db_Adapter_Pdo {

    protected $_name = '';
    protected $_primary = '';

    /**
     * 设置默认的dbAdapter
     * @param PDO $adapter
     * @return mixed
     * @throws Exception
     */
    public static function setDefaultAdapter($adapter) {
        self::_registryAdapter('defaultAdapter', $adapter);
        self::setAdapter('defaultAdapter');
        return $adapter;
    }

    /**
     * 注册一个dbAdapter
     * @param string $name
     * @param PDO $adapter
     * @return bool|PDO
     */
    public static function registryAdapter($name, $adapter) {
        return self::_registryAdapter($name . 'Adapter', $adapter);
    }

    /**
     * 设置dbAdapter
     * @param string $name
     * @return bool
     * @throws Exception
     */
    public static function setAdapter($name) {
        if (!is_string($name)) {
            return false;
        }
        if (!Yaf_Registry::get($name)) {
            throw new Exception("The adapter " . $name . " not rigistry");
        }
        Yaf_Registry::set('dbAdapter', $name);
        return $name;
    }

    /**
     *
     * 获取注册的dbAdapterName
     */
    public static function getAdaterName() {
        return Yaf_Registry::get('dbAdapter');
    }


    /**
     * 查询一条结果集
     * @param string $sql
     * @param array $params
     * @param int $fetch_style
     * @return mixed
     */
    public static function fetch($sql, $params = array(), $fetch_style = PDO::FETCH_ASSOC) {
        $stmt = self::getStatement($sql, $params);
        return $stmt->fetch($fetch_style);
    }


    /**
     * 查询column列结果
     * @param string $sql
     * @param array $column_number
     * @param array $params
     * @return string
     */
    public static function fetchCloum($sql, $column_number = null, $params = array()) {
        $stmt = self::getStatement($sql, $params);
        return $stmt->fetchColumn($column_number);
    }

    /**
     *
     * 查询所有结果集
     * @param string $sql
     * @param array $params
     * @param int $fetch_style
     * @return array
     */
    public static function fetchAll($sql, $params = array(), $fetch_style = PDO::FETCH_ASSOC) {
        $stmt = self::getStatement($sql, $params);
        return $stmt->fetchAll($fetch_style);
    }

    /**
     *
     * 执行sql并返回影响行数
     * @param string $sql
     * @param array $params
     * @param bool $rowCount
     * @return bool|int
     */
    public static function execute($sql, $params = array(), $rowCount = false) {
        $stmt = self::getPDO()->prepare($sql);
        $ret = $stmt->execute($params);
        return $rowCount ? $stmt->rowCount() : $ret;
    }

    /**
     * 获取PDOStatement
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public static function getStatement($sql, $params = array()) {
        $stmt = self::getPDO()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * 获取后插入的id
     */
    public static function getLastInsertId() {
        return self::getPDO()->lastInsertId();
    }

    /**
     * 批量绑定参数
     * @param PDOStatement $stmt
     * @param array $params
     * @throws Exception
     */
    public static function bindValues($stmt, $params) {
        if (!is_array($params)) throw new Exception('Error unexpected paraments type' . gettype($params));
        $keied = (array_keys($params) !== range(0, sizeof($params) - 1));
        foreach ($params as $key => $value) {
            if (!$keied) $key = $key + 1;
            $stmt->bindValue($key, $value, self::_getDataType($value));
        }
    }

    /**
     * 字符串过滤
     * @param string $string
     * @param int $parameter_type
     * @return string
     */
    public static function quote($string, $parameter_type = null) {
        return self::getPDO()->quote($string, $parameter_type);
    }

    /**
     * 解析多个占位符
     * @param string $text
     * @param array $value
     * @param int $type
     * @param int $count
     * @return mixed
     */
    public static function quoteInto($text, $value, $type = null, $count = null) {
        if ($count === null) {
            return str_replace('?', self::quote($value, $type), $text);
        } else {
            while ($count > 0) {
                if (strpos($text, '?') !== false) {
                    $text = substr_replace($text, self::quote($value, $type), strpos($text, '?'), 1);
                }
                --$count;
            }
            return $text;
        }
    }

    /**
     * 过滤数组转换成sql字符串
     * @param array $variable
     * @return string
     */
    public static function quoteArray($variable) {
        if (empty($variable) || !is_array($variable)) return '';
        $_returns = array();
        foreach ($variable as $value) {
            $_returns[] = self::quote($value);
        }
        return '(' . implode(', ', $_returns) . ')';
    }

    /**
     * 过滤二维数组将数组变量转换为多组的sql字符串
     * @param array $var
     * @return string
     */
    public static function quoteMultiArray($var) {
        if (empty($var) || !is_array($var)) return '';
        $_returns = array();
        foreach ($var as $val) {
            if (!empty($val) && is_array($val)) {
                $_returns[] = self::quoteArray($val);
            }
        }
        return implode(', ', $_returns);
    }


    /**
     * 组装单条 key=value 形式的SQL查询语句值
     * @param array $array
     * @return string
     */
    public static function sqlSingle($array) {
        if (!is_array($array)) return '';
        $str = array();
        foreach ($array as $key => $val) {
            $str[] = self::fieldMeta($key) . '=' . self::quote($val);
        }
        return $str ? implode(',', $str) : '';
    }

    /**
     *
     * 组装多条 key值
     * @param array $array
     */
    public static function sqlKey($array) {
        if (!is_array($array)) return '';
        $str = array();
        foreach ($array[0] as $key => $val) {
            $str[] = self::fieldMeta($key);
        }
        return $str ? '(' . implode(',', $str) . ')' : '';
    }


    /**
     * where 条件组装
     * @param array $array
     * @return string
     */
    public static function sqlWhere($array) {
        if (!is_array($array)) return 1;
        $str = array();
        foreach ($array as $field => $val) {
            if (is_numeric($field) and is_string($val)) {
                $str[] = $val;
            } else if (is_array($val)) {
                if (is_array($val[0])) {//'id'=>array(array('>', 0), array('<', 10))
                    foreach ($val as $v) {
                        list($op, $value) = $v;
                        $str[] = self::_where($field, strtoupper($op), $value);
                    }
                } else {//'id'=>array('>', 0)
                    list($op, $value) = $val;
                    $str[] = self::_where($field, strtoupper($op), $value);
                }
            } else {//'id'=>0
                $str[] = self::_where($field, "=", $val);
            }
        }
        return $str ? implode(' AND ', $str) : 1;
    }

    /**
     * where 条件匹配
     * @param string $field
     * @param string $op
     * @param string $value
     * @return string
     */
    public static function _where($field, $op, $value) {
        $str = "";
        switch ($op) {
            case ">":
            case "<":
            case ">=":
            case "<=":
            case "!=":
            case "<>":
                $str .= self::fieldMeta($field) . $op . self::quote($value);
                break;
            case "IN":
                $str .= self::fieldMeta($field) . $op . self::quoteArray($value);
                break;
            case "NOT IN":
                $str .= self::fieldMeta($field) . $op . self::quoteArray($value);
                break;
            case "LIKE":
                $str .= sprintf("%s LIKE %s", self::fieldMeta($field), self::quote("%" . self::filterLike($value) . "%"));
                break;
            case "=" :
                $str .= self::fieldMeta($field) . '=' . self::quote($value);
                break;
        }
        return $str;
    }

    /**
     * whereOR 条件匹配
     * @param string $field
     * @param string $op
     * @param string $value
     * @return string
     */
    public static function _oRwhere($kv) {
        $str = "";
        foreach ($kv as $field =>$val){
            if (is_numeric($field) and is_string($val)) {
                $str[] = $val;
            } else if (is_array($val)) {
                if (is_array($val[0])) {//'id'=>array(array('>', 0), array('<', 10))
                        foreach ($val as $v) {
                            list($op, $value) = $v;
                            $str[] = self::_where($field, strtoupper($op), $value);
                        }
                } else {//'id'=>array('>', 0)
                    list($op, $value) = $val;
                    $str[] = self::_where($field, strtoupper($op), $value);
                }
            } else {//'id'=>0
                $str[] = self::_where($field, "=", $val);
            }
        }
        return $str?'('.implode(' AND ', $str).')':'';
    }



    /**
     * where 条件组装
     * @param array $array
     * @return string
     */
    public static function oRsqlWhere($array) {
        if (!is_array($array)) return 1;
        $str = array();
        $orStr = array();
        foreach ($array as $field => $val) {
            if (is_numeric($field) and is_string($val)) {
                $str[] = $val;
            } else if (is_array($val)) {
                if (is_array($val[0])) {//'id'=>array(array('>', 0), array('<', 10))
                    if($field == 'or'){ //'or'=>array(array('field',0),array('field2',000),array(array('<',0)))
                        foreach ($val as $vv){
                            $orStr[] = self::_oRwhere($vv); //组成一个字符串
                        }
                        $str[] = implode(' OR ', $orStr);
                    }else{
                        foreach ($val as $v) {
                            list($op, $value) = $v;
                            $str[] = self::_where($field, strtoupper($op), $value);
                        }
                    }
                } else {//'id'=>array('>', 0)
                    list($op, $value) = $val;
                    $str[] = self::_where($field, strtoupper($op), $value);
                }
            } else {//'id'=>0
                $str[] = self::_where($field, "=", $val);
            }
        }
        return $str ? implode(' AND ', $str) : 1;
    }

    /**
     * 排序语句
     * @param array $sort
     * @return string
     */
    public static function sqlSort($sort) {
        if (!is_array($sort) || !count($sort)) return '';
        $str = ' ORDER BY ';
        $orders = array();
        foreach ($sort as $key => $value) {
            if ($key == 'FIELD') {
                $orders[] = 'FIELD (`' . $value[0] . '`,' . implode(',', $value[1]) . ')';
            } else {
                $orders[] = $key . ' ' . $value;
            }
        }
        return $str . implode(', ', $orders);
    }

    /**
     * sql关键字段过滤
     * @param array $data
     * @return string
     */
    public static function fieldMeta($data) {
        $data = str_replace(array('`', ' '), '', $data);
        if (strpos($data, '.') === false) {
            return ' `' . $data . '` ';
        } else {
            $fildArr = explode('.', $data);
            if ($fildArr[0] && $fildArr[1]) {
                return ' `' . $fildArr[0] . '`.`' . $fildArr[1] . '` ';
            } else {
                return ' `' . $data . '` ';
            }
        }
    }

    /**
     *
     * @return array
     */
    public static function getAdapter() {
        $adapterName = self::getAdaterName();
        return Yaf_Registry::get($adapterName);
    }

    /**
     *
     * @return PDO
     */
    public static function getPDO() {
        return Db_Pdo::factory(self::getAdapter());
    }

    /**
     *
     * @param string $keyWord
     * @return string
     */
    public static function filterLike($keyWord) {
        $search = array('[', '%', '_', '/');
        $replace = array('\[', '\%', '\_', '\/');
        return str_replace($search, $replace, $keyWord);
    }

    /**
     * 注册dbAdapter
     * @param string $name
     * @param PDO $adapter
     * @return bool
     */
    private static function _registryAdapter($name, $adapter) {
        if ($adapter === null) {
            return false;
        }
        Yaf_Registry::set($name, $adapter);
        return $adapter;
    }

    /**
     * 获得绑定参数的类型
     * @param string $var
     * @return int
     */
    private static function _getDataType($var) {
        $types = array(
            'boolean' => PDO::PARAM_BOOL,
            'integer' => PDO::PARAM_INT,
            'string' => PDO::PARAM_STR,
            'NULL' => PDO::PARAM_NULL
        );
        return isset($types[gettype($var)]) ? $types[gettype($var)] : PDO::PARAM_STR;
    }
}
