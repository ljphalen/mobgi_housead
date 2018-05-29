<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * 对外数据报表
 * @author matt.liu
 *
 */
class MobgiData_Service_OfficialModel extends MobgiData_Service_BaseModel {


    private static $baseDims = ['app_key', 'days', 'hours', 'platform', 'ad_type', 'channel_gid', 'ads_id'];
    private static $apiKpis = ['third_views', 'third_clicks', 'ad_income'];
    private static $dauKpis = ['new_user', 'total_user', 'user_dau', 'game_dau', 'total_init'];
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
    ];

    public static $conf = [
        "app_key" => ["name" => "应用", 'alias' => 'app_name'],
        "pos_key" => ["name" => "广告位"],
        "ads_id" => ["name" => "广告商"],
        "ad_type" => ["name" => "广告类型"],
        "platform" => ["name" => "平台"],
        "days" => ["name" => "日期"],


        "third_views" => ["name" => "第三方展示次数"],
        "third_clicks" => ["name" => "第三方点击次数"],
        "third_ecpm" => ["name" => "第三方ECPM"],
        "ad_income" => ["name" => "广告收益"],


        "user_dau" => ["name" => "广告活跃用户"],
        "new_user" => ["name" => "广告新增用户"],
        "total_user" => ["name" => "观看广告用户"],
        "arpdau" => ["name" => "ARPDAU"],

    ];

    public static $baseFields = [
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
        'sdk_impressions' => 'sum(sdk_impressions)',
        'redirect_browser' => 'sum(redirect_browser)',
        'redirect_internal_browser' => 'sum(redirect_internal_browser)',
        'redirect_shop' => 'sum(redirect_shop)',
        'redirect_internal_shop' => 'sum(redirect_internal_shop)',
        'download_app' => 'sum(download_app)',
        'download_app_ok' => 'sum(download_app_ok)',
        'install_app' => 'sum(install_app)',
        'install_app_ok' => 'sum(install_app_ok)',
        'skip_stay_time' => 'sum(skip_stay_time)',
        'amount' => 'sum(amount)',
        'new_user' => 'sum(new_user)',
        'total_user' => 'sum(total_user)',
        'user_dau' => 'sum(user_dau)',
        'game_dau' => 'sum(game_dau)',
        'total_init' => 'sum(total_init)',

        'ad_income' => 'sum(ad_income)*division_rate',
        'adjust_income' => 'sum(adjust_income)',
        'third_clicks' => 'sum(third_clicks)',
        'third_views' => 'sum(third_views)',

        'cache_ready_ok' => 'sum(cache_ready_ok)',
        'cache_ready_fail' => 'sum(cache_ready_fail)',
        'cache_ready' => 'sum(cache_ready_ok+cache_ready_fail)',
    ];

    public static $expandFields = [
        'ecpm' => ['ad_income', 'effective_impressions', 1000, 2],
        'rpm' => ['ad_income', 'download', 1000, 2],
        'arpdau' => ['ad_income', 'user_dau', 1, 4],
        'arpdeu' => ['ad_income', 'total_user', 1, 4],

        'third_ecpm' => ['ad_income', 'third_views', 1000, 2],
        'third_click_rate' => ['third_clicks', 'third_views', 100, 2],

        'click_rate' => ['clicks', 'impressions', 100, 2],
        'cache_ready_rate' => ['cache_ready_ok', 'cache_ready', 100, 2],
        'third_views_cmp_rate' => ['third_views', 'effective_impressions', 100, 2],
        'third_clicks_cmp_rate' => ['third_clicks', 'clicks', 100, 2],

        'skip_stay_time' => ['skip_stay_time', 'impressions', 0.001, 2],
        'request_rate' => ['request_ok', 'request', 100, 2],
        'download_rate' => ['download_ok', 'download', 100, 2],
        'download_app_rate' => ['download_app_ok', 'download_app', 100, 2],
        'install_app_rate' => ['install_app_ok', 'install_app', 100, 2],
        'user_view_count' => ['impressions', 'user_dau', 1, 2],

        'dau_rate' => ['user_dau', 'game_dau', 100, 2],
        'total_user_rate' => ['total_user', 'user_dau', 100, 2],
        'new_user_rate' => ['new_user', 'user_dau', 100, 2],

        'impressions_user_dau' => ['impressions', 'user_dau', 1, 2],
        'impressions_total_user' => ['impressions', 'total_user', 1, 2],
        'impressions_total_init' => ['impressions', 'total_init', 1, 2],
        'impressions_effective_init' => ['impressions', 'effective_init', 1, 2],

        'impression_download_ok_rate' => ['impressions', 'download_ok', 100, 2],
        'cache_ready_rate' => ['cache_ready_ok', 'cache_ready', 100, 2],
        'impressions_cache_ready_rate' => ['impressions', 'cache_ready_ok', 100, 2],
        'third_views_dau' => ['third_views', 'user_dau', 1, 2],
        'third_views_deu' => ['third_views', 'total_user', 1, 2],

        'effective_impressions_rate' => ['effective_impressions', 'impressions', 100, 2],


    ];

    //    获取过滤字段
    public static function getFilterFields() {
        return array_merge(self::$baseDims, ['country', 'province', 'ssp_id', 'ads_id', 'pos_key', 'app_version', 'sdk_version']);
    }

    /**
     * 获取用户报表配置
     * @param $accountId
     * @return string
     */
    public static function getChartConf($accountId, $params = []) {
        $conf = [
            'api' => [
                "data" => '/Admin/Data_Report/getOfficeData',
                "conf" => '/Admin/Data_Report/updateOfficialKpi',
            ],
            'box' => [
                "app_key" => [],
                "pos_key" => [],
                "ad_type" => [],
                "platform" => [],
                "channel_gid" => [],
                "ads_id" => [],
                "ssp_id" => [],
                "country" => [],
                "province" => [],
            ],
            'conf' => self::$conf,
            'kpi' => [],
            'dim' => [
                "default_dim_dom" => "#dim",
                "default_dim_fields" => [
                    "days" => "日期",
                    "hr1" => "-",
                    "app_key" => "应用",
                    "pos_key" => "广告位",
                    "ad_type" => "广告类型",
                    "platform" => "平台",
                    "hr2" => "-",
                    "ads_id" => "广告商",
                ],
                "default_dim_value" => ["days" => []],
                "dims" => [],
            ],
        ];
        if (isset($params['dims'])) {
            $mydims = [];
            foreach ($params['dims'] as $dim) {
                $mydims[$dim] = $params[$dim] ?: [];
            }
            if (!empty($mydims)) {
                $conf['dim']['default_dim_value'] = $mydims;
            }
        }
        $kpis = $params['kpis'] ?: [];

        $conf['kpi'] = self::getChartKpis($accountId, $kpis);
        $conf['dim']['dims'] = self::getChartDims($accountId);
        return $conf;
        //        $conf['dim']['relations'] = self::getRelations();
        //        return json_encode($conf);
    }


    //   获取指标
    private static function getChartKpis($userId, $kpis) {

        $defaultConf = [
            "third" => [
                "third_views" => 0,
                "third_clicks" => 0,
                "third_ecpm" => 0,
                "ad_income" => 0,
            ],
            "common" => [
                "user_dau" => 0,
                "new_user" => 0,
                "total_user" => 0,
                "arpdau" => 0,
            ],
        ];


        $kpiConf = self::getUserKpi($userId, 'official');
        if (empty($kpis)) {
            $kpis = empty($kpiConf['kpis']) ? ['ad_income'] : explode('|', $kpiConf['kpis']);
        }


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
     * @return object
     */
    public static function getData($params, $userId = null) {
        // 用户权限
        if (!is_null($userId)) {
            self::$userId = $userId;
            $params['permit']['app_key'] = self::getUserAppKey($userId);
        }

        $data = self::getReportData($params);
        $data = self::expandReportData($data, self::$expandFields, $params['theader']);
        $data = self::replaceReportData($data, $params['dims']);
        return $data;
    }


    //    获取汇总数据
    public static function getTotal($data, $params) {
        if (count($data) == 0) {
            return [];
        }
        $sumField = array_intersect(array_keys($data[0]), [
            'third_views',
            'third_clicks',
            'ad_income',
            'request',
            'request_ok',
            'download',
            'download_ok',
            'impressions',
            'clicks',
            'closes',
            'redirect_browser',
            'redirect_Internal_browser',
            'redirect_shop',
            'download_app',
            'download_app_ok',
            'install_app',
            'install_app_ok',
            'new_user',
            'user_dau',
            'game_dau',
        ]);
        $result = [];
        foreach ($data as $key => $cell) {
            foreach ($sumField as $field) {
                $result[$field] += $cell[$field];
            }
        }
        if (isset($data[0]['days'])) {
            $result['days'] = "汇总";
        } elseif (isset($data[0]['hours'])) {
            $result['hours'] = "汇总";
        }

        $result = self::expandReportData([$result], self::$expandFields, $params['theader']);
        return $result[0];
    }


    //    数据转换
    private static function replaceReportData($items, $dims = []) {
        if (empty($items)) return [];
        //        foreach ($items as $key => $item) {
        //            empty($item['app_key']) or $appKey[$item['app_key']] = $item['app_key'];
        //            empty($item['pos_key']) or $posKey[$item['pos_key']] = $item['pos_key'];
        //        }
        in_array('app_key', $dims) and $appKey = self::getAppKeyMap();
        in_array('pos_key', $dims) and $posKey = self::getPosKeyMap();
        in_array('channel_gid', $dims) and $channelGid = self::getUserChannelGidMap();
        in_array('country', $dims) and $country = self::getCountryMap();
        $adType = Common_Service_Config::AD_TYPE;
        $platform = Common_Service_Config::PLATFORM;

        foreach ($items as $key => $item) {
            isset($item['ad_type']) and isset($adType[$item['ad_type']]) and $items[$key]['ad_type'] = $adType[$item['ad_type']];
            isset($item['app_key']) and isset($appKey[$item['app_key']]) and $items[$key]['app_key'] = $appKey[$item['app_key']];
            isset($item['pos_key']) and isset($posKey[$item['pos_key']]) and $items[$key]['pos_key'] = $posKey[$item['pos_key']];
            isset($item['platform']) and isset($platform[$item['platform']]) and $items[$key]['platform'] = $platform[$item['platform']];
            isset($item['country']) and isset($country[$item['country']]) and $items[$key]['country'] = $country[$item['country']];
            isset($item['channel_gid']) and isset($channelGid[intval($item['channel_gid'])]) and $items[$key]['channel_gid'] = $channelGid[intval($item['channel_gid'])];
        }
        return $items;

    }


    private static function expandKpi($kpis) {
        if (empty($kpis)) {
            return [];
        }
        $expandFields = self::$expandFields;
        foreach ($kpis as $kpi) {
            if (array_key_exists($kpi, $expandFields)) {
                array_push($kpis, $expandFields[$kpi][0], $expandFields[$kpi][1]);
            }
        }
        return array_intersect(array_merge(self::$baseKpis, self::$apiKpis, self::$dauKpis), array_unique($kpis));
    }


    private static function getUnitTable($params) {
        $dims = $params['dims'];
        $kpis = $params['kpis'];
        $tables = [];
        if (array_intersect($dims, ['country', 'province'])) {
            array_push($tables, 'city');
        } else {
            if (array_intersect($kpis, ['new_user', 'total_user', 'user_dau', 'game_dau'])) {
                array_push($tables, 'dau');
            }
            if (array_intersect($kpis, ['third_views', 'third_clicks', 'ad_income'])) {
                if (array_intersect($dims, ['channel_gid'])) {
                    array_push($tables, 'channel');
                } else {
                    array_push($tables, 'api');
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
                'play_finish',
                'redirect_shop',
                'redirect_browser',
                'redirect_internal_shop',
                'redirect_internal_browser',
                'download_app',
                'download_app_ok',
                'install_app',
                'install_app_ok',
                'cache_ready_ok',
                'cache_ready_fail',
                'amount',
                'skip_stay_time',
            ])) {
                if (array_intersect($dims, ['hours'])) {
                    array_push($tables, 'hour');
                } else {
                    array_push($tables, 'day');
                }
            }


        }
        return $tables;
    }

    private static function getReportData($params) {
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

        if (in_array('user_dau', $params['kpis']) and array_intersect($params['dims'], ['ads_id', 'pos_key', 'ad_type'])) {
            $myParams = $params;
            $myParams['dims'] = array_intersect($params['dims'], ['days', 'app_key']);
            $data = self::getDauData($myParams);
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

    public static function getIncome($start, $end, $userId) {
        $params = [
            'dims' => [],
            'kpis' => ['ad_income'],
            'sdate' => $start,
            'edate' => $end
        ];
        // 用户权限
        if (!is_null($userId)) {
            self::$userId = $userId;
            $params['permit']['app_key'] = self::getUserAppKey($userId);
        }
        $data = self::getApiData($params);
        return empty($data) ? 0 : array_sum($data[0]);
    }

    public static function getIncomes($start, $end, $userId) {
        $params = [
            'dims' => ['days'],
            'kpis' => ['ad_income'],
            'sdate' => $start,
            'edate' => $end

        ];
        // 用户权限
        if (!is_null($userId)) {
            self::$userId = $userId;
            $params['permit']['app_key'] = self::getUserAppKey($userId);
        }
        return self::getApiData($params);
    }

    private static function getApiData($params) {
        $mydims = array_merge(self::$baseDims, ['pos_key']);
        $mykpis = self::$apiKpis;
        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);


        $allFields = self::$baseFields;
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

        if (in_array('pos_key', $dims) and empty($where['pos_key'])) {
            $where['pos_key'] = ['not in', [0, -1, '']];
        }

        #取is_mobgi = 0的数据
        $where['is_mobgi'] = 0;
        $strField = self::buildFields($fields);
        $ret = self::getDao("ReportApi")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;
    }


    private static function getChannelData($params) {
        $mydims = array_merge(self::$baseDims, ['channel_gid']);
        $mykpis = self::$apiKpis;
        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);

        $allFields = self::$baseFields;
        $allFields['hours'] = 0;
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
        $ret = self::getDao("ReportChannel")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;
    }


    private static function getDauData($params) {

        if (array_intersect($params['dims'], ['hours', 'ads_id', 'pos_key', 'ad_type'])) {
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

        $ret = self::getDao("ReportDau")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;

    }

    private static function getCityData($params) {
        if (array_intersect($params['dims'], ['hours', 'ads_id', 'pos_key', 'ad_type', 'channel_gid'])) {
            return [];
        }

        $mydims = array_merge(self::$baseDims, ['country', 'province']);
        $mykpis = self::$baseKpis;
        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);

        $allFields = self::$baseFields;
        $allFields['ads_id'] = 0;
        $allFields['hours'] = 0;
        $allFields['ad_type'] = 0;
        $allFields['channel_gid'] = 0;

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

        $ret = Common::getDao("MobgiData_Dao_ReportCityModel")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;

    }

    private static function getDayData($params) {
        $mydims = array_merge(self::$baseDims, ['ssp_id', 'pos_key', 'app_version', 'sdk_version']);
        $mykpis = array_merge(self::$baseKpis, ['used_time']);

        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);

        $allFields = self::$baseFields;
        $allFields['hours'] = 0;

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

        if (in_array('ads_id', $dims) and empty($where['ads_id'])) {
            $where['ads_id'] = ['!=', -1];
        }
        if (in_array('pos_key', $dims) and empty($where['pos_key'])) {
            $where['pos_key'] = ['not in', [0, -1, '']];
        }
        $ret = self::getDao("ReportDay")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;


    }

    private static function getHourData($params) {
        $mydims = array_merge(self::$baseDims, ['ssp_id', 'hour', 'pos_key', 'app_version', 'sdk_version']);
        $mykpis = array_merge(self::$baseKpis, ['used_time']);

        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);

        $allFields = self::$baseFields;

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
        if (in_array('ads_id', $dims) and empty($where['ads_id'])) {
            $where['ads_id'] = ['!=', -1];
        }
        if (in_array('pos_key', $dims) and empty($where['pos_key'])) {
            $where['pos_key'] = ['not in', [0, -1, '']];
        }
        $ret = self::getDao("ReportHour")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;


    }


    //    获取账户关联
    public static function getRelations($accountId = null) {
        if ($accountId != null) {
            if (empty($accountId)) {
                return [];
            }
            $paramAccount = is_array($accountId) ? ['account_id' => ['in', $accountId]] : ['account_id' => $accountId];
        } else {
            $paramAccount = [];
        }

        //        级联关系:账号==》投放单元==》广告==》创意
        $accountRelation = Dedelivery_Service_OriginalityRelationModel::getRelationOfAccount($paramAccount);
        //        创意类型==》应用
        $OriginalityIds = Dedelivery_Service_OriginalityRelationModel::getFieldWithAccount($paramAccount, 'originality_type');
        //        $origAppkeyRelation = empty($OriginalityIds) ? [] : Advertiser_Service_OriginalityRelationPositionModel::getRelationOfAppkey([
        //            'originality_type' => ['in', $OriginalityIds]
        //        ]);
        $origAppkeyRelation = empty($relations['originality_type']) ? [] : MobgiApi_Service_AdAppModel::getAppKey([
            'is_check' => MobgiApi_Service_AdAppModel::ISCHECK_PASS,
        ]);


        return array_merge($accountRelation, $origAppkeyRelation);
    }

    //获取账户关联维度
    public static function getChartDims($userId = null) {
        if ($userId != null) {
            if (empty($userId)) {
                $dims = [
                    'app_key' => [],
                    'pos_key' => [],
                    'platform' => [],
                    'unit_id' => [],
                    'ad_id' => [],
                ];
                return $dims;
            }
        }

        $appKeys = self::getUserAppKey($userId);
        $dims = [
            'ad_type' => Common_Service_Config::AD_TYPE,
            'ads_id' => self::getAdsIdMapWithForeign(),
            'app_key' => self::getAppKeyMap($appKeys, 1),
            'pos_key' => self::getAppPosKeyMap($appKeys),
            'channel_gid' => self::getUserChannelGidMap(),
            'platform' => Common_Service_Config::PLATFORM,
            'country' => Common_Service_Config::COUNTRY,
            'province' => Common_Service_Config::PROVINCE,
        ];
        return $dims;

    }


    //   获取指标
    public static function getKpis($accountId) {
        $defaultConf = [
            "data" => [
                "view" => 0,
                "click" => 0,
                "amount" => 0,
                "skips" => 0,
            ],
            "report" => [
                "avg_price" => 0,
                "ecpm" => 0,
                "click_rate" => 0,
                "skips_time" => 0,
            ],
        ];

        $kpiConf = self::getByID($accountId);
        $kpi = empty($kpiConf['kpis']) ? ['view'] : explode('|', $kpiConf['kpis']);
        foreach ($defaultConf as $key => $val) {
            foreach ($val as $skey => $sval) {
                if (in_array($skey, $kpi)) {
                    $defaultConf[$key][$skey] = 1;
                }
            }
        }
        return $defaultConf;
    }

}
