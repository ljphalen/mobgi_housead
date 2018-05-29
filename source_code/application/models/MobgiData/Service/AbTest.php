<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * 自投数据报表
 * @author atom.zhan
 *
 */
class MobgiData_Service_AbTestModel extends MobgiData_Service_BaseModel {
    protected static $baseDims = [
        'app_key',
        'pos_key',
        'days',
        'hours',
        'platform',
        'ad_type',
        'channel_gid',
        'ads_id',
        'flow_id',
        'conf_id',
        'app_version',
        'sdk_version'
    ];
    protected static $dauKpis = ['new_user', 'total_user', 'user_dau', 'game_dau', 'total_init'];
    private static $baseKpis = [
        'request',
        'request_ok',
        'download',
        'download_ok',
        'impressions',
        'effective_impressions',
        'closes',
        'clicks',
        'skips',
        'inits',
        'exits',
        'reward',
        'sdk_impressions',
        'redirect_shop',
        'redirect_browser',
        'redirect_internal_shop',
        'redirect_internal_browser',
        'download_app',
        'download_app_ok',
        'install_app',
        'install_app_ok',
        'cache_ready',
        'cache_ready_ok',
        'cache_ready_fail',
        'skip_stay_time',
        'exit_stay_time',
    ];

    private static $defaultDimUser = array("app_key", "days");
    private static $defaultDimReplace = array(
        'app_key',
        'ads_id',
        'platform',
        'pos_key',
        'channel_id',
        'country',
        'area',
        'ad_type',
        'flow_id',
        'conf_id'
    );
    private static $defaultSelectedKpi = ['impressions', 'clicks', 'dau_user', 'clicks_rate', 'arpu'];
    public static $conf = [
        "flow_id" => ["name" => "流量配置"],
        "conf_id" => ["name" => "测试配置"],
        "app_key" => ["name" => "应用", 'alias' => 'app_name'],
        "pos_key" => ["name" => "广告位"],
        "ads_id" => ["name" => "广告商"],
        "ad_type" => ["name" => "广告类型"],
        "platform" => ["name" => "平台"],
        "channel_gid" => ["name" => "渠道"],
        "days" => ["name" => "日期"],
        "hours" => ["name" => "小时"],
        "app_version" => ["name" => "游戏版本"],
        "sdk_version" => ["name" => "SDK版本"],
        "country" => ["name" => "国家"],
        "province" => ["name" => "省"],

        "request" => ["name" => "请求配置次数"],
        "request_ok" => ["name" => "请求配置成功"],
        "request_rate" => ["name" => "配置请求成功率"],

        "download" => ["name" => "请求广告次数"],
        "download_ok" => ["name" => "请求广告成功"],
        "download_rate" => ["name" => "广告请求成功率"],

        "impressions" => ["name" => "展示次数"],
        "effective_impressions" => ["name" => "有效展示次数"],
        "effective_impressions_rate" => ["name" => "广告有效展示率"],

        "clicks" => ["name" => "点击次数"],
        "click_rate" => ["name" => "点击率"],
        "ecpm" => ["name" => "eCPM"],
        "reward" => ["name" => "视频触发奖励"],
        "closes" => ["name" => "关闭广告(插页、开屏)"],
        "resume" => ["name" => "视频重新观看"],
        "impression_download_ok_rate" => ["name" => "广告商资源利用率"],
        "cache_ready_ok" => ["name" => "广告就绪成功"],
        "cache_ready_fail" => ["name" => "广告就绪失败"],
        "cache_ready_rate" => ["name" => "广告就绪成功率"],
        "impressions_cache_ready_rate" => ["name" => "触发观看率"],

        "skips" => ["name" => "跳过次数"],
        "inits" => ["name" => "初始化次数"],
        "exits" => ["name" => "退出次数"],
        "online_time" => ["name" => "平均在线时长(s)"],
        "online_time_dau" => ["name" => "人均在线时长(s)"],
        "skip_stay_time" => ["name" => "跳过时间均值(s)"],

        "sdk_impressions" => ["name" => "sdk展示次数"],
        "redirect_browser" => ["name" => "跳转浏览器次数"],
        "redirect_internal_browser" => ["name" => "跳转内建浏览器次数"],
        "redirect_shop" => ["name" => "跳转商店次数"],
        "redirect_internal_shop" => ["name" => "跳转商店内页次数"],
        "download_app" => ["name" => "下载APP次数"],
        "download_app_ok" => ["name" => "下载APP成功次数"],
        "download_app_rate" => ["name" => "下载成功率(%)"],
        "install_app" => ["name" => "安装应用"],
        "install_app_ok" => ["name" => "安装应用成功"],
        "install_app_rate" => ["name" => "安装成功率(%)"],
        "game_dau" => ["name" => "游戏活跃用户"],
        "dau_rate" => ["name" => "广告活跃覆盖率"],
        "user_dau" => ["name" => "广告活跃用户"],
        "new_user" => ["name" => "广告新增用户"],
        "new_user_rate" => ["name" => "广告新增用户占比"],
        "total_user" => ["name" => "观看广告用户"],
        "total_user_rate" => ["name" => "观看广告用户占比"],
        "impressions_user_dau" => ["name" => "展示/广告活跃用户"],
        "impressions_total_user" => ["name" => "展示/观看广告用户"],
        "arpdau" => ["name" => "ARPDAU"],
        "arpdeu" => ["name" => "ARPDEU"],
        "total_init" => ["name" => "应用启动次数"],
        "effective_total_init" => ["name" => "有效启动次数"],
        "impressions_total_init" => ["name" => "展示/应用启动次数"],
        "impressions_effective_init" => ["name" => "展示/有效启动次数"],
        "amount" => ["name" => "消费金额"],
        "rpm" => ["name" => "RPM($)"],
        "user_view_count" => ["name" => "人均展示次数"],
        "user_init_count" => ["name" => "人均启动次数", "title" => "应用启动次数/DAU"]
    ];

    public static $baseFields = [
        'flow_id' => 'flow_id',
        'conf_id' => 'conf_id',
        'ad_type' => 'ad_type',
        'ads_id' => 'ads_id',
        'ssp_id' => 'ssp_id',
        'app_key' => 'app_key',
        'app_version' => 'app_version',
        'sdk_version' => 'sdk_version',
        'channel_gid' => 'channel_gid',
        'platform' => 'platform',
        'pos_key' => 'pos_key',
        'days' => 'days',
        'hours' => 'hours',
        'country' => 'country',
        'province' => 'province',
        'request' => 'sum(request)',
        'request_ok' => 'sum(request_ok)',
        'download' => 'sum(download)',
        'download_ok' => 'sum(download_ok)',
        'impressions' => 'sum(impressions)',
        'effective_impressions' => 'sum(effective_impressions)',
        'clicks' => 'sum(clicks)',
        'closes' => 'sum(closes)',
        'skips' => 'sum(skips)',
        'reward' => 'sum(reward)',
        'inits' => 'sum(inits)',
        'exits' => 'sum(exits)',
        'exit_stay_time' => 'sum(exit_stay_time)',
        'skip_stay_time' => 'sum(skip_stay_time)',
        'sdk_impressions' => 'sum(sdk_impressions)',
        'redirect_browser' => 'sum(redirect_browser)',
        'redirect_internal_browser' => 'sum(redirect_internal_browser)',
        'redirect_shop' => 'sum(redirect_shop)',
        'redirect_internal_shop' => 'sum(redirect_internal_shop)',
        'download_app' => 'sum(download_app)',
        'download_app_ok' => 'sum(download_app_ok)',
        'install_app' => 'sum(install_app)',
        'install_app_ok' => 'sum(install_app_ok)',
        'amount' => 'sum(amount)',
        'new_user' => 'sum(new_user)',
        'total_user' => 'sum(total_user)',
        'user_dau' => 'sum(user_dau)',
        'game_dau' => 'sum(game_dau)',
        'total_init' => 'sum(total_init)',
        'ad_income' => 'sum(ad_income)',
        'adjust_income' => 'sum(adjust_income)',


    ];

    public static $expandFields = [
        'click_rate' => ['clicks', 'impressions', 100, 2],
        'request_rate' => ['request_ok', 'request', 100, 2],
        'download_rate' => ['download_ok', 'download', 100, 2],
        'download_app_rate' => ['download_app_ok', 'download_app', 100, 2],
        'install_app_rate' => ['install_app_ok', 'install_app', 100, 2],
        'user_view_count' => ['impressions', 'user_dau', 1, 2],
        'total_user_rate' => ['total_user', 'user_dau', 100, 2],
        'new_user_rate' => ['new_user', 'user_dau', 100, 2],

        'impressions_user_dau' => ['impressions', 'user_dau', 1, 2],
        'impressions_total_user' => ['impressions', 'total_user', 1, 2],
        'impressions_effective_init' => ['impressions', 'effective_init', 1, 2],
        'impression_download_ok_rate' => ['impressions', 'download_ok', 100, 2],
        'effective_impressions_rate' => ['effective_impressions', 'impressions', 100, 2],
        'online_time' => ['exit_stay_time', 'exits', 1, 2],
        'online_time_dau' => ['exit_stay_time', 'user_dau', 1, 2],
    ];

    //    获取过滤字段
    public static function getFilterFields() {
        return array_merge(self::$baseDims, ['flow_id', 'conf_id', 'ssp_id', 'ads_id', 'pos_key', 'app_version', 'sdk_version']);
    }

    public static $filterFields = [
        'days',
        'hours',
        'impressions',
        'clicks',
        'ad_id',
        'ad_type',
        'platform',
        'originality_id',
        'ad_type',
        'account_id',
        'unit_id',
        'app_key',
        'pos_key',
        'amount',
        'request',
        'request_ok',
        'download',
        'download_ok',
        'sdate',
        'edate',
        'dims',
        'kpi',
        'theader',
        'flow_id',
        'conf_id'
    ];


    /**
     * 获取报表配置
     * @param unknown $params
     * @return boolean|unknown
     */
    public static function getChartConf($accountId) {
        $conf = [
            'api' => [
                "data" => '/Admin/Data_Report/getAbtestData',
                "conf" => '/Admin/Data_Report/updateAbtestKpi',
            ],
            'box' => [
                "channel_gid" => [],
                "ads_id" => [],
                "ad_type" => [],
                "app_key" => [],
                "pos_key" => [],
                "platform" => [],
                "flow_id" => [],
                "conf_id" => [],
            ],
            'conf' => self::$conf,
            'kpi' => [],
            'dim' => [
                "default_dim_dom" => "#dim",
                "default_dim_fields" => [
                    "days" => "日期",
                    "hours" => "小时",
                    "hr1" => "-",
                    "app_key" => "应用",
                    "pos_key" => "广告位",
                    "ad_type" => "广告类型",
                    "platform" => "平台",
                    "channel_gid" => "渠道",
                    "ads_id" => "广告商",
                    "hr2" => "-",
                    "app_version" => "游戏版本",
                    "sdk_version" => "SDK版本",
                    "hr3" => "-",
                    "flow_id" => self::$conf['flow_id']['name'],
                    "conf_id" => self::$conf['conf_id']['name'],
                ],
                "default_dim_value" => ["days" => []],
                "dims" => [],
            ],
        ];
        $conf['kpi'] = self::getChartKpis($accountId);
        $conf['dim']['dims'] = self::getChartDims();
        return $conf;
    }

    //   获取指标
    private static function getChartKpis($userId) {
        $defaultConf = [
            "client" => [
                "request" => 0,
                "request_ok" => 0,
                "request_rate" => 0,
                "download" => 0,
                "download_ok" => 0,
                "download_rate" => 0,
                "impressions" => 0,
                "clicks" => 0,
                "click_rate" => 0,
                "ecpm" => 0,
                "effective_impressions" => 0,
                "effective_impressions_rate" => 0,
                "reward" => 0,
                //                "closes" => 0,
                "impression_download_ok_rate" => 0,
                "cache_ready_ok" => 0,
                "cache_ready_fail" => 0,
                "cache_ready_rate" => 0,
                "impressions_cache_ready_rate" => 0,
            ],
            "common" => [
                "user_dau" => 0,
                "total_user" => 0,
                "impressions_user_dau" => 0,
                "impressions_total_user" => 0,
                //                "online_time" => 0,
                "online_time_dau" => 0,
            ],
        ];

        $kpiConf = self::getUserKpi($userId, 'abtest');
        $kpis = empty($kpiConf['kpis']) ? ['ad_income'] : explode('|', $kpiConf['kpis']);

        foreach ($defaultConf as $key => $val) {
            foreach ($val as $skey => $sval) {
                if (in_array($skey, $kpis)) {
                    $defaultConf[$key][$skey] = 1;
                }
            }
        }
        return $defaultConf;
    }


    /**
     * 获取之前日期的结果集
     * @param unknown $params
     * @return boolean|unknown
     */
    public static function getData($params, $userId = null) {
        // 用户权限
        $params['permit'] = [];
        if (!is_null($userId)) {
            self::$userId = $userId;
            if (!Admin_Service_UserModel::isOperator($userId)) {
                $params['permit']['account_id'] = $userId;
                $params['permit']['originality_id'] = self::getUserOrigId($userId);
            }
        }
        $data = self::getReportData($params);
        $data = self::expandReportData($data, self::$expandFields, $params['theader']);
        $data = self::replaceReportData($data, $params['dims']);
        return $data;
    }

    public static function getSampleData($params) {
        // 用户权限
        $params['permit'] = [];
        $data = self::getReportData($params);
        $data = self::expandReportData($data, self::$expandFields, $params['theader']);
        return $data;
    }


    //    获取汇总数据
    public static function getTotal($table, $params) {
        $sumField = [
            'request',
            'request_ok',
            'download',
            'download_ok',
            'impressions',
            'clicks',
            'closes',
            'skips',
            'reward',
            'resume',
            'redirect_browser',
            'redirect_Internal_browser',
            'redirect_shop',
            'download_app',
            'download_app_ok',
            'install_app',
            'install_app_ok',
            'amount',
        ];
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

        $result = self::expandReportData([$result], self::$expandFields, $params['theader']);
        return $result[0];
    }


    //    数据转换
    private static function replaceReportData($items, $dims = []) {
        if (empty($items)) return [];
        foreach ($items as $key => $item) {
            empty($item['app_key']) or $appKey[$item['app_key']] = $item['app_key'];
            empty($item['pos_key']) or $posKey[$item['pos_key']] = $item['pos_key'];
        }
        in_array('app_key', $dims) and $appKey = self::getAppKeyMap($appKey);
        in_array('pos_key', $dims) and $posKey = self::getPosKeyMap($posKey);
        in_array('flow_id', $dims) and $flowId = self::getFlowMap();
        in_array('conf_id', $dims) and $confId = self::getConfMap();


        $adType = Common_Service_Config::AD_TYPE;
        $platform = Common_Service_Config::PLATFORM;

        foreach ($items as $key => $item) {
            isset($item['ad_type']) and isset($adType[$item['ad_type']]) and $items[$key]['ad_type'] = $adType[$item['ad_type']];
            isset($item['app_key']) and isset($appKey[$item['app_key']]) and $items[$key]['app_key'] = $appKey[$item['app_key']];
            isset($item['pos_key']) and isset($posKey[$item['pos_key']]) and $items[$key]['pos_key'] = $posKey[$item['pos_key']];
            isset($item['platform']) and isset($platform[$item['platform']]) and $items[$key]['platform'] = $platform[$item['platform']];
            isset($item['flow_id']) and isset($flowId[$item['flow_id']]) and $items[$key]['flow_id'] = $flowId[$item['flow_id']];
            isset($item['conf_id']) and isset($confId[$item['conf_id']]) and $items[$key]['conf_id'] = $confId[$item['conf_id']];
        }
        return $items;

    }


    private static function expandKpi($kpis) {
        if (empty($kpis)) {
            return [];
        }
        $expandFields = self::$expandFields;
        foreach ($kpis as $kpi) {
            if (in_array($kpi, $expandFields)) {
                array_push($kpis, $expandFields[0], $expandFields[0]);
            }
        }
        return array_intersect(array_merge(self::$baseKpis, self::$dauKpis), array_unique($kpis));
    }

    protected static function getUnitTable($params) {
        $dims = $params['dims'];
        $kpis = $params['kpis'];
        $tables = [];

        if (array_intersect($kpis, ['new_user', 'total_user', 'user_dau', 'game_dau'])) {
            if (array_intersect($dims, ['app_version', 'sdk_version'])) {
                array_push($tables, 'testDauVer');
            } else {
                array_push($tables, 'testDau');
            }
        }
        if (array_intersect($kpis, [
            'request',
            'request_ok',
            'download',
            'download_ok',
            'impressions',
            'effect_impressions',
            'sdk_impressions',
            'clicks',
            'skips',
            'inits',
            'play_finish',
            'redirect_shop',
            'redirect_browser',
            'redirect_internal_shop',
            'redirect_internal_browser',
            'download_app',
            'download_app_ok',
            'install_app',
            'install_app_ok',
            'skip_stay_time',
        ])) {
            array_push($tables, 'test');

        }


        return $tables;
    }

    protected static function getReportData($params) {
        $params['kpis'] = self::expandKpi($params['theader']);
        $tables = self::getUnitTable($params);
        $data = [];
        foreach ($tables as $table) {
            $method = 'get' . ucfirst($table) . 'Data';
            $data = array_merge($data, self::$method($params));
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
        if (in_array('user_dau', $params['kpis']) and array_intersect($params['dims'], ['ads_id', 'pos_key'])) {
            $myParams = $params;
            $myParams['dims'] = array_intersect($params['dims'], ['days', 'app_key']);
            $data = self::getTestDauData($myParams);
            $dauData = [];
            foreach ($data as $key => $item) {
                $key = '';
                foreach ($myParams['dims'] as $dim) {
                    $key .= $item[$dim] . '_';
                }
                $dauData[$key] = $item['user_dau'];
            }
            foreach ($result as $myKey => $item) {
                $key = '';
                foreach ($myParams['dims'] as $dim) {
                    $key .= $item[$dim] . '_';
                }
                if (isset($dauData[$key])) {
                    $result[$myKey]['user_dau'] = $dauData[$key];
                }
            }
        }

        return $result;

    }

    private static function getTestData($params) {
        $dims = array_intersect(self::$baseDims, $params['dims']);
        $kpis = array_intersect($params['kpis'], array_merge(self::$baseKpis, ['used_time', 'exit_stay_time', 'skip_stay_time']));
        $allFields = self::$baseFields;

        $fields = [];
        foreach (array_merge($dims, $kpis) as $item) {
            if (array_key_exists($item, $allFields)) {
                $fields[$item] = $allFields[$item];
            } else {
                $fields[$item] = $item;
            }
        }

        $groupBy = empty($dims) ? null : 'GROUP BY ' . implode(',', $dims);
        // 日期倒序
        if (!empty($dims)) {
            $orderBy = array();
            foreach ($dims as $dim) {
                array_push($orderBy, $dim . ' asc');
            }
            $orderBy = implode(',', $orderBy);
        } else {
            $orderBy = NULL;
        }
        $where = self::buildWhere($params);
        $strField = self::buildFields($fields);

        if (in_array('ads_id', $dims) and empty($where['ads_id'])) {
            $where['ads_id'] = ['!=', -1];
        }
        if (in_array('pos_key', $dims) and empty($where['pos_key'])) {
            $where['pos_key'] = ['not in', [0, -1, '']];
        }
        $ret = self::getDao("ReportTest")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;
    }

    protected static function getTestDauData($params) {

        if (array_intersect($params['dims'], ['hours', 'ads_id', 'pos_key'])) {
            return [];
        }

        $mydims = self::$baseDims;
        $mykpis = self::$dauKpis;

        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);
        $allFields = self::$baseFields;
        //        $allFields['ad_type'] = '0';

        $fields = [];
        foreach (array_merge($dims, $kpis) as $item) {
            if (isset($item, $allFields)) {
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

        if (in_array('channel_gid', $dims)) {
            if (empty($where['channel_gid'])) {
                $where['channel_gid'] = array('>', 0);
            }
        } else {
            //没有渠道时
            $where['channel_gid'] = 0;
        }

        if (in_array('ad_type', $dims)) {
            if (empty($where['ad_type'])) {
                $where['ad_type'] = array('>', 0);
            }
        } else {
            //没有广告类型
            $where['ad_type'] = 0;
        }

        $ret = self::getDao("ReportTestDau")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;

    }

    protected static function getTestDauVerData($params) {

        if (array_intersect($params['dims'], ['hours', 'ads_id', 'pos_key', 'channel_gid', 'ad_type'])) {
            return [];
        }

        $mydims = array_merge(self::$baseDims, ['app_version', 'sdk_version']);
        $mykpis = self::$dauKpis;

        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);
        $allFields = self::$baseFields;
        //        $allFields['ad_type'] = '0';

        $fields = [];
        foreach (array_merge($dims, $kpis) as $item) {
            if (isset($item, $allFields)) {
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

        $where['app_version'] = in_array('app_version', $dims) ? array('!=', 0) : 0;
        $where['sdk_version'] = in_array('sdk_version', $dims) ? array('!=', 0) : 0;

        $ret = self::getDao("ReportTestDauVer")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;

    }

    //获取账户关联维度
    public static function getChartDims($userId = null) {
        if ($userId != null) {
            if (empty($userId)) {
                $dims = [
                    'ad_type' => [],
                    'app_key' => [],
                    'pos_key' => [],
                    'platform' => [],
                    'channel_gid' => [],
                    'ads_id' => [],
                ];
                return $dims;
            }
            $paramAccount = is_array($userId) ? array('account_id' => array('in', $userId)) : array('account_id' => $userId);
        } else {
            $paramAccount = [];
        }

        $appKeys = self::getUserAppKey($userId);
        $dims = [
            'ad_type' => Common_Service_Config::AD_TYPE,
            'ads_id' => self::getAdsIdMapWithForeign(),
            'app_key' => self::getAppKeyMap($appKeys, 1),

            'channel_gid' => self::getUserChannelGidMap(),
            'platform' => Common_Service_Config::PLATFORM,

            'flow_id' => self::getFlowMap(),
            'conf_id' => self::getConfMap(),


        ];
        $dims['pos_key'] = self::getAppPosKeyMap(array_keys($dims['app_key']));


        return $dims;

    }

}
