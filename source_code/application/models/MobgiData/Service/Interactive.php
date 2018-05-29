<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * 互动数据报表
 * @author atom.zhan
 *
 */
class MobgiData_Service_InteractiveModel extends Common_Service_Report {
    protected static $appId = 0;
    protected static $userId = 0;
    protected static $userName = "";
    protected static $baseDims = [
        'app_key' => ['name' => '应用'],
        'block_id' => ['name' => '广告位'],
        'ads_id' => ['name' => '广告商'],
        'days' => ['name' => '日期'],
        'hours' => ['name' => '小时'],
        'os' => ['name' => '系统'],
        'brand' => ['name' => '品牌'],
        'model' => ['name' => '设备'],
        'url_id' => ['name' => '连接地址'],
        'url_type' => ['name' => '连接类型'],
        'goods_id' => ['name' => '商品'],
        'activity_id' => ['name' => '活动'],
    ];

    private static $qrKpis = [
        'request' => ['name' => '请求次数', 'group' => 'common'],
        'redirect' => ['name' => '跳转次数', 'group' => 'common'],
        'dau' => ['name' => '去重点击数', 'group' => 'common'],
    ];

    public static $baseKpis = [
        'pv' => ['name' => 'PV', 'group' => 'common'],
        'uv' => ['name' => 'UV', 'group' => 'common'],
        'active' => ['name' => '参与人次', 'group' => 'common'],
        'active_dau' => ['name' => '参与人数', 'group' => 'common'],
        'awards' => ['name' => '中奖人数', 'group' => 'common'],
        'receive' => ['name' => '奖品兑换数', 'group' => 'common'],
    ];

    public static $kpiFields = [
        'report_inter' => ['request', 'dau', 'redirect'],
        'report_interactive' => ['request', 'dau', 'redirect'],
    ];

    public static $kpiGroup = [
        'common' => ['name' => '通用'],
    ];

    public static $sumField = ['dau', 'request', 'redirect', 'pv', 'uv', 'active_dau', 'active', 'dau', 'awards', 'receive'];

    public static $expandFields = [];

    protected static $urlType = ['内部连接', '外部连接'];
    protected static $os = ['Android', 'Ios', 'Windows'];


    public static function getFilterFields() {
        return array_keys(self::$baseDims);
    }

    public static function setUserId($id) {
        self::$userId = $id;
    }

    public static function setUserName($name) {
        self::$userName = $name;
    }

    //获取Qr报表配置
    public static function getQrChartConf($params) {
        $appId = $params['app_id'];
        $conf = [
            'api' => [
                "data" => '/Admin/Interative_Report/getQrData',
                "conf" => '/Admin/Interative_Report/updateQrKpi',
            ],
            'kpi' => self::getQrKpis(),
            "dim" => self::getQrChartDims(self::$userName, $appId, isset($params['type'])),
            'my_dim' => ['days' => []],
            'box' => [],
            //            'sortBy' => 'registers'
        ];
        $conf['dim_fields'] = [
            'days' => '日期',
            'hours' => '小时',
            'hr1' => '-',
            'app_key' => '应用',
            'block_id' => '广告位',
            'ads_id' => '广告商',
            'hr2' => '-',
            'os' => '系统',
            'brand' => '品牌',
            'model' => '设备',
            'url_id' => '链接地址',
            'url_type' => '链接类型',
        ];

        $conf['conf'] = self::getKeyMap();
        $conf['my_kpi'] = self::getMyQrKpis(self::$userId);
        foreach ($conf['dim'] as $key => $val) {
            $conf['box'][$key] = [];
        }
        return $conf;
    }


    //获取报表配置
    public static function getChartConf($params) {
        $appId = $params['app_id'];
        $conf = [
            'api' => [
                "data" => '/Admin/Interative_Report/getData',
                "conf" => '/Admin/Interative_Report/updateKpi',
            ],
            'kpi' => self::getKpis(),
            "dim" => self::getChartDims(self::$userName, $appId, isset($params['type'])),
            'my_dim' => ['days' => []],
            'box' => [],
        ];
        $conf['dim_fields'] = [
            'days' => '日期',
            'hr1' => '-',
            'activity_id' => '活动',
            'goods_id' => '商品',
        ];

        $conf['conf'] = self::getKeyMap();
        $conf['my_kpi'] = self::getInterKpis(self::$userId);
        foreach ($conf['dim'] as $key => $val) {
            $conf['box'][$key] = [];
        }
        return $conf;
    }


    //   获取Qr指标配置
    private static function getQrKpis() {
        $groups = [];
        foreach (self::$kpiGroup as $key => $val) {
            $groups[$key] = [];
        }
        foreach (self::$qrKpis as $key => $val) {
            $group = $val['group'] ?: 'common';
            $groups[$group][] = $key;
        }
        return $groups;
    }

    //   获取指标配置
    private static function getKpis() {
        $groups = [];
        foreach (self::$kpiGroup as $key => $val) {
            $groups[$key] = [];
        }
        foreach (self::$baseKpis as $key => $val) {
            $group = $val['group'] ?: 'common';
            $groups[$group][] = $key;
        }
        return $groups;
    }

    //获取Qr关联维度
    public static function getQrChartDims($userName = null, $appId, $type = 0) {
        if ($userName != null) {
            if (empty($userName)) {
                $dims = [
                    'is_natural' => [],
                    'ad_type' => [],
                    'app_key' => [],
                    'block_id' => [],
                    'channel_id' => [],
                    'channel_gid' => [],
                    'ads_id' => [],
                ];
                return $dims;
            }
        }
        $dims = [
            'app_key' => self::getAppMap(),
            'block_id' => self::getPosMap(),
            'ads_id' => self::getAdsMap(),
            'url_id' => self::getUrlMap(),
            'url_type' => self::$urlType,
            'os' => self::$os
        ];
        return $dims;
    }

    //获取关联维度
    public static function getChartDims($userName = null, $appId, $type = 0) {
        if ($userName != null) {
            if (empty($userName)) {
                $dims = [
                    'activity_id' => [],
                    'goods_id' => [],
                ];
                return $dims;
            }
        }
        $dims = [
            'activity_id' => self::getActivityMap(),
            'goods_id' => self::getGoodsMap(),

        ];
        return $dims;
    }


    //   获取用户指标
    private static function getMyQrKpis($userId) {
        $kpiConf = self::getUserKpi($userId, 'inter');
        $myKpi = empty($kpiConf['kpis']) ? ['request', 'dau', 'redirect'] : explode('|', $kpiConf['kpis']);
        return $myKpi;
    }

    private static function getInterKpis($userId) {
        $kpiConf = self::getUserKpi($userId, 'inter');
        $myKpi = empty($kpiConf['kpis']) ? ['request', 'dau', 'redirect'] : explode('|', $kpiConf['kpis']);
        return $myKpi;
    }

    //   获取指标
    private static function getKeyMap() {
        $conf = [];
        $all = array_merge(self::$baseDims, self::$qrKpis, self::$baseKpis, self::$kpiGroup);
        foreach ($all as $key => $val) {
            $conf[$key] = $val['name'];
            if (isset($val['alias'])) {
                $conf[$val['alias']] = $val['name'];
            }
        }
        return $conf;
    }


    //   获取指标
    private static function getFields() {
        $conf = [];
        foreach (self::$baseDims as $key => $val) {
            $conf[$key] = isset($val['field']) ? $val['field'] : $key;
        }
        foreach (self::$qrKpis as $key => $val) {
            $conf[$key] = isset($val['field']) ? $val['field'] : "sum({$key})";
        }
        foreach (self::$baseKpis as $key => $val) {
            $conf[$key] = isset($val['field']) ? $val['field'] : "sum({$key})";
        }
        return $conf;
    }

    /**
     * 获取之前日期的结果集
     * @param array $params
     * @return boolean|unknown
     */
    public static function getData($params) {
        // 用户权限
        if (!isset($params['permit'])) {
            $params['permit'] = [];
        }

        $data = self::getReportData($params);
        $data = self::expandReportData($data, self::$expandFields, $params['kpis']);
        $data = self::replaceReportData($data, $params['dims']);
        return $data;
    }

    //    数据扩展
    protected static function expandReportData($data, $expandFields, $kpis = []) {
        $result = [];
        foreach ($data as $key => $val) {
            foreach ($expandFields as $field => $conf) {
                if (in_array($field, $kpis)) {
                    if (empty($val[$conf[1]]) and isset($expandFields[$conf[1]])) {
                        $tmpConf = $expandFields[$conf[1]];
                        foreach ($tmpConf as $i => $tmp) {
                            if (is_string($tmpConf[$i]) and $val[$tmpConf[$i]] > 0) {
                                $tmpConf[$i] = $val[$tmpConf[$i]];
                            } else if (!is_numeric($tmpConf[$i])) {
                                $tmpConf[$i] = 0;
                            }
                        }
                        $val[$conf[1]] = empty($tmpConf[1]) ? 0 : round($tmpConf[0] / $tmpConf[1] * $tmpConf[2], $tmpConf[3]);
                    }
                    foreach ($conf as $i => $tmp) {
                        if (is_string($conf[$i]) and $val[$conf[$i]] > 0) {
                            $conf[$i] = $val[$conf[$i]];
                        } else if (!is_numeric($conf[$i])) {
                            $conf[$i] = 0;
                        }
                    }
                    $val[$field] = empty($conf[1]) ? 0 : round($conf[0] / $conf[1] * $conf[2], $conf[3]);
                }
            }
            $result[] = $val;
        }
        return $result;
    }

    //    获取汇总数据
    public static function getTotal($table, $params) {
        $sumField = self::$sumField;
        $result = [];
        foreach ($table as $key => $cell) {
            foreach ($sumField as $field) {
                $result[$field] += $cell[$field];
            }
        }
        if (isset($table[0]['days'])) {
            $result['days'] = "汇总";
        } elseif (isset($table[0]['hours'])) {
            $result['hours'] = "汇总";
        }

        $result = self::expandReportData([$result], self::$expandFields, $params['kpis']);
        return $result[0];
    }


    //    数据转换
    private static function replaceReportData($items, $dims = []) {
        if (empty($items)) return [];

        in_array('app_key', $dims) and $appKey = self::getAppMap();
        in_array('block_id', $dims) and $posKey = self::getPosMap();

        foreach ($items as $key => $item) {
            isset($item['app_key']) and isset($appKey[$item['app_key']]) and $items[$key]['app_key'] = $appKey[$item['app_key']];
            isset($item['block_id']) and isset($posKey[$item['block_id']]) and $items[$key]['block_id'] = $posKey[$item['block_id']];
        }
        return $items;

    }

    protected static function expandKpi($kpis) {
        if (empty($kpis)) {
            return [];
        }
        foreach ($kpis as $kpi) {
            $exkpis = self::getExpandKpi($kpi);
            $kpis = array_merge($kpis, $exkpis);
        }
        return array_intersect(array_merge(array_keys(self::$qrKpis), array_keys(self::$baseKpis)), array_unique($kpis));
    }

    protected static function getExpandKpi($kpi, $level = 0) {
        $exKpis = [];
        $expandFields = self::$expandFields;
        if (array_key_exists($kpi, $expandFields) and $level < 3) {
            foreach ($expandFields[$kpi] as $exKpi) {
                if (is_string($exKpi)) {
                    if (array_key_exists($exKpi, $expandFields)) {
                        $level++;
                        $exKpis = array_merge($exKpis, self::getExpandKpi($exKpi, $level));
                    } else {
                        $exKpis[] = $exKpi;
                    }
                }
            }
        }
        return $exKpis;
    }


    protected static function getUnitTable($params) {
        $dims = $params['dims'];
        $kpis = $params['kpis'];
        $tables = [];
        if (array_intersect($kpis, ['dau'])) {
            array_push($tables, 'DauInter');
        }
        if (array_intersect($kpis, ['uv', 'active_dau'])) {
            array_push($tables, 'DauInteractive');
        }
        if (array_intersect($kpis, ['pv', 'active', 'awards', 'receive'])) {
            array_push($tables, 'ReportInteractive');
        }


        if (array_intersect($kpis, ['request', 'redirect'])) {
            array_push($tables, 'ReportInter');
        }
        return $tables;
    }

    protected static function getReportData($params) {
        $params['kpis'] = self::expandKpi($params['kpis']);
        $tables = self::getUnitTable($params);
        $data = [];
        foreach ($tables as $table) {
            $method = 'get' . ucfirst($table) . 'Data';
            foreach (self::$method($params) as $key => $val) {
                $data[$table . $key] = $val;
            }
        }
        $result = [];
        $dims = $params['dims'];
        $kpis = $params['kpis'];
        foreach ($data as $key => $item) {
            $key = '';
            foreach ($dims as $dim) {
                $key .= $item[$dim] . '_';
            }
            foreach ($dims as $dim) {
                if (!isset($result[$key][$dim])) {
                    $result[$key][$dim] = $item[$dim];
                    foreach ($kpis as $kpi) {
                        $result[$key][$kpi] = 0;
                    }
                }
            }

            foreach ($kpis as $kpi) {
                if (isset($item[$kpi])) {
                    $result[$key][$kpi] += $item[$kpi];
                }
            }
        }
        return $result;
    }

    //获取qr数据
    protected static function getReportInterData($params) {
        $mydims = array_keys(self::$baseDims);
        $mykpis = array_keys(self::$qrKpis);
        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);
        $allFields = self::getFields();

        $fields = [];
        foreach (array_merge($dims, $kpis) as $item) {
            if (isset($allFields[$item])) {
                $fields[$item] = $allFields[$item];
            }
        }
        $groupBy = empty($dims) ? null : 'GROUP BY ' . implode(',', $dims);
        // 日期倒序
        if (!empty($dims)) {
            $orderBy = [];
            foreach ($dims as $dkey => $dim) {
                array_push($orderBy, $dim . ' asc');
            }
            $orderBy = implode(',', $orderBy);
        } else {
            $orderBy = NULL;
        }
        $where = self::buildWhere($params);
        $strField = self::buildFields($fields);

        $ret = self::getDao("ReportInter")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;

    }

    protected static function getDauInterData($params) {
        if (array_intersect($params['dims'], ['hours', 'ads_id', 'url_id', 'url_type'])) {
            return [];
        }
        $mydims = array_keys(self::$baseDims);
        $mykpis = ['dau'];
        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);
        $allFields = self::getFields();
        $fields = [];
        foreach (array_merge($dims, $kpis) as $item) {
            if (isset($item, $allFields)) {
                $fields[$item] = $allFields[$item];
            }
        }
        $groupBy = empty($dims) ? null : 'GROUP BY ' . implode(',', $dims);
        $orderBy = NULL;
        $where = self::buildWhere($params);
        $strField = self::buildFields($fields);

        if (in_array('block_id', $dims)) {
            if (empty($where['block_id'])) {
                $where['block_id'] = array('!=', '-');
            }
        } else {
            $where['block_id'] = '-';
        }
        $table = 'ReportDauInter';
        $ret = self::getDao($table)->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;

    }

    //获取互动式广告数据
    protected static function getReportInteractiveData($params) {
        $mydims = ['days', 'goods_id', 'activity_id'];
        $mykpis = ['pv', 'active', 'awards', 'receive'];
        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);
        $allFields = self::getFields();

        $fields = [];
        foreach (array_merge($dims, $kpis) as $item) {
            if (isset($allFields[$item])) {
                $fields[$item] = $allFields[$item];
            }
        }
        $groupBy = empty($dims) ? null : 'GROUP BY ' . implode(',', $dims);
        // 日期倒序
        if (!empty($dims)) {
            $orderBy = [];
            foreach ($dims as $dkey => $dim) {
                array_push($orderBy, $dim . ' asc');
            }
            $orderBy = implode(',', $orderBy);
        } else {
            $orderBy = NULL;
        }
        $where = self::buildWhere($params);
        $strField = self::buildFields($fields);


        if (array_intersect(['goods_id'], $dims) and empty($params['goods_id'])) {
            $where['goods_id'] = array('>', 0);
        }

        $ret = self::getDao("ReportInteractive")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;

    }


    protected static function getDauInteractiveData($params) {
        $mydims = ['days', 'goods_id', 'activity_id'];
        $mykpis = ['active_dau', 'uv'];
        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);
        $allFields = self::getFields();
        $fields = [];
        foreach (array_merge($dims, $kpis) as $item) {
            if (isset($item, $allFields)) {
                $fields[$item] = $allFields[$item];
            }
        }
        $groupBy = empty($dims) ? null : 'GROUP BY ' . implode(',', $dims);
        $orderBy = NULL;
        $where = self::buildWhere($params);
        $strField = self::buildFields($fields);
//        if (!isset($dims['days'])) {
//            return [];
//        }
        if (!array_intersect(['activity_id'], $dims)) {
            $where['activity_id'] = 0;
        }
        if (!array_intersect(['goods_id'], $dims)) {
            $where['goods_id'] = 0;
        }
        $ret = self::getDao('ReportDauInteractive')->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;

    }

    /**
     * 构建字段
     * @param $fields
     * @return string
     */
    protected static function buildFields($fields) {
        $str = [];
        foreach ($fields as $key => $field) {
            $str[] = $field . ' as ' . $key;
        }
        return implode(',', $str);
    }

    /**
     * 转换条件字段
     * @param $where
     * @param $params
     * @return array
     */
    protected static function exchangeWhere($where, $params) {
        $new_where = [];
        foreach ($where as $key => $field) {
            if (isset($params[$key])) {
                $new_where[$params[$key]] = $field;
            } else {
                $new_where[$key] = $field;
            }
        }
        return $new_where;
    }


    /**
     * 构建条件
     * @param $params
     * @return array
     */
    protected static function buildWhere($params) {
        $where = array();
        if (isset($params['sdate']) and isset($params['edate'])) {

            if ($params['sdate'] == $params['edate']) {
                $where['days'] = array('=', $params['sdate']);
            } else {
                if (isset($params['compare']) and $params['compare']) {
                    $where['days'] = array('in', [$params['sdate'], $params['edate']]);
                } else {
                    $where['days'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));
                }

            }
        } else {
            $where['days'] = array('=', date("Y-m-d", strtotime("-1 day")));
        }
        if (isset($params['app_id'])) {
            $where['app_id'] = array(is_array($params['app_id']) ? 'in' : '=', $params['app_id']);
        }

        if (!isset($params['permit'])) {
            $params['permit'] = [];
        }
        $dims = array_unique(array_merge($params['dims'], array_keys($params['permit']), ['activity_id']));
        foreach ($dims as $val) {
            if (isset($params[$val])) {
                $myVal = $params[$val];
                if (!empty($params['permit'][$val])) {
                    $myVal = array_intersect($myVal, $params['permit'][$val]);
                }
                $where[$val] = is_array($myVal) ? array('in', $myVal) : $myVal;
            } elseif (isset($params['permit'][$val])) {
                $myVal = $params['permit'][$val];
                if (!empty($myVal)) {
                    $where[$val] = is_array($myVal) ? array('in', $myVal) : $myVal;
                }

            }

        }
        return $where;
    }

    public static function getAppMap() {
        $result = self::getDao("ConfigApp")->getFields('app_key,app_name');
        return empty($result) ? [] : $result;
    }

    public static function getPosMap() {
        $result = self::getDao("ConfigPos")->getFields('pos_key,pos_name', ['ad_type' => MobgiData_Service_SynModel::AD_TYPE_INTERATIVE]);
        return empty($result) ? [] : $result;
        //        $where= [
        //            'ad_type' => MobgiData_Service_SynModel::AD_TYPE_INTERATIVE,
        //            'status' => 1
        //        ];
        //
        //        $list = self::getDao('ConfigPos')->getAllByFields('app_key,block_id,pos_name', $where);
        //        $map = [];
        //        foreach ($list as $item) {
        //            $map[$item['app_key']][$item['block_id']] = $item['pos_name'];
        //        }
        //        return $map;

    }


    public static function getAdsMap() {
        $result = self::getApiDao("AdsList")->getFields('ads_id,name', ['ad_sub_type' => ['like', '"6"']]);
        return empty($result) ? [] : $result;
    }

    public static function getUrlMap() {
        $result = self::getApiDao("AdsList")->getFields('ads_id,name', ['ad_sub_type' => ['like', '"6"']]);
        return empty($result) ? [] : $result;
    }

    public static function getGoodsMap() {
        $result = self::getApiDao("InteractiveAdGoods")->getFields('id,title', ['del' => 0]);
        return empty($result) ? [] : $result;
    }

    public static function getActivityMap() {
        $result = self::getApiDao("InteractiveAdActivity")->getFields('id,title', ['del' => 0]);
        return empty($result) ? [] : $result;
    }


    public static function ipua() {

        $data['event_type'] = 1;
        $data['url_id'] = 0;
        $data['url_type'] = 0;

        $data['app_key'] = 'abcdefg';
        $data['pos_key'] = '123456789';
        $data['ip'] = Common::getClientIP();
        $data['ua'] = $_SERVER['HTTP_USER_AGENT'];
        $data['server_time'] = time();
        $redis = Common::getQueue('interative_ad_list');
        return $redis->push('RQ:client', $data);


    }


}
