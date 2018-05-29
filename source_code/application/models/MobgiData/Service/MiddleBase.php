<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/1
 * Time: 14:34
 * Mark: this is a middle model for report model
 */
class MobgiData_Service_MiddleBaseModel extends MobgiData_Service_BaseModel {
    protected static $baseDims = ['app_key', 'days', 'hours', 'platform', 'ad_type', 'channel_gid', 'ads_id'];
    protected static $apiKpis = ['third_views', 'third_clicks', 'ad_income', 'third_ad_income', 'ad_income_adjust'];
    protected static $dauKpis = ['new_user', 'total_user', 'user_dau', 'game_dau', 'total_init'];
    protected static $dspKpis = ['dsp_request', 'dsp_response', 'dsp_win', 'dsp_notice',];
    protected static $baseKpis = [
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
        'effective_exits',
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
        'cache_fail',
        'cache_success',
        'cache_all',
        'cache_show'
    ];

    public static $conf = [
        "app_key" => ["name" => "应用", 'alias' => 'app_name'],
        "pos_key" => ["name" => "广告位"],
        "ads_id" => ["name" => "广告商"],
        "ssp_id" => ["name" => "流量主"],
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
        "dsp_request" => ["name" => "DSP请求"],
        "dsp_response" => ["name" => "DSP响应"],
        "dsp_win" => ["name" => "DSP竞价成功"],
        "dsp_notice" => ["name" => "DSP通知"],


        "third_views" => ["name" => "第三方展示次数"],
        "third_clicks" => ["name" => "第三方点击次数"],
        "third_ad_income" => ["name" => "第三方广告收益"],
        "third_click_rate" => ["name" => "第三方展示点击率"],
        "third_views_cmp_rate" => ["name" => "第三方展示对比率"],
        "third_clicks_cmp_rate" => ["name" => "第三方点击对比率"],
        "third_ecpm" => ["name" => "第三方ECPM"],
        "third_ecpm_discount" => ["name" => "折算第三方ECPM"],
        "third_views_dau" => ["name" => "第三方展示次数／广告活跃用户"],
        "third_views_deu" => ["name" => "第三方展示次数／观看广告用户"],
        "ad_income" => ["name" => "广告收益"],
        "ad_income_adjust" => ["name" => "实际广告收益"],

        "skips" => ["name" => "跳过次数"],
        "inits" => ["name" => "初始化次数"],
        "exits" => ["name" => "退出次数"],
        "effective_exits" => ["name" => "有效退出次数"],
        "effective_exits_rate" => ["name" => "有效退出占比"],

        "online_time" => ["name" => "平均在线时长(s)"],
        "online_time_dau" => ["name" => "累计在线时长(s)"],
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
        "cache_fail" => ["name" => "缓存失败次数"],
        "cache_success" => ["name" => "缓存成功次数"],
        "cache_show" => ["name" => "缓存成功展示次数"],
        "cache_success_rate" => ["name" => "缓存成功率(%)"],

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
        'effective_exits' => 'sum(effective_exits)',
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
        'third_ad_income' => 'sum(ad_income*division_rate)',
        'ad_income_adjust' => 'sum(ad_income+ad_income_adjust)',
        'third_clicks' => 'sum(third_clicks)',
        'third_views' => 'sum(third_views)',
        'cache_ready_ok' => 'sum(cache_ready_ok)',
        'cache_ready_fail' => 'sum(cache_ready_fail)',
        'cache_ready' => 'sum(cache_ready_ok+cache_ready_fail)',

        'cache_fail' => 'sum(cache_fail)',
        'cache_success' => 'sum(cache_success)',
        'cache_show' => 'sum(cache_show)',
        'cache_all' => 'sum(cache_fail+cache_success)',


        'dsp_request' => 'sum(dsp_request)',
        'dsp_response' => 'sum(dsp_response)',
        'dsp_win' => 'sum(dsp_win)',
        'dsp_notice' => 'sum(dsp_notice)',

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
        'third_ecpm_discount' => ['third_ecpm', 100, 'third_views_cmp_rate', 2],

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
        'user_init_count' => ['total_init', 'user_dau', 1, 2],

        'impressions_effective_init' => ['impressions', 'effective_init', 1, 2],

        'impression_download_ok_rate' => ['impressions', 'download_ok', 100, 2],
        'cache_ready_rate' => ['cache_ready_ok', 'cache_ready', 100, 2],
        'impressions_cache_ready_rate' => ['impressions', 'cache_ready_ok', 100, 2],
        'third_views_dau' => ['third_views', 'user_dau', 1, 2],
        'third_views_deu' => ['third_views', 'total_user', 1, 2],

        'effective_impressions_rate' => ['effective_impressions', 'impressions', 100, 2],
        'online_time' => ['exit_stay_time', 'effective_exits', 1, 2],
        'online_time_dau' => ['exit_stay_time', 'user_dau', 1, 2],
        'effective_exits_rate' => ['effective_exits', 'exits', 1, 2],

        'cache_success_rate' => ['cache_success', 'cache_all', 1, 2],


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
                "data" => '/Admin/Data_Report/getMobgiData',
                "conf" => '/Admin/Data_Report/updateMobgiKpi',
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
                    "hours" => "小时",
                    "hr1" => "-",
                    "app_key" => "应用",
                    "pos_key" => "广告位",
                    "ad_type" => "广告类型",
                    "platform" => "平台",
                    "channel_gid" => "渠道",
                    "hr2" => "-",
                    "ads_id" => "广告商",
                    "ssp_id" => "流量主",
                    "hr3" => "-",
                    "app_version" => "游戏版本",
                    "sdk_version" => "SDK版本",
                    "hr4" => "-",
                    "country" => "国家",
                    "province" => "省份",
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
    }


    //   获取指标
    protected static function getChartKpis($userId, $kpis) {

        $defaultConf = [
            "third" => [
                "third_views" => 0,
                "third_clicks" => 0,
                "third_click_rate" => 0,
                "third_views_cmp_rate" => 0,
                "third_clicks_cmp_rate" => 0,
                "third_ecpm" => 0,
                "third_ecpm_discount" => 0,
                "third_views_dau" => 0,
                "third_views_deu" => 0,
                "ad_income" => 0,
                "ad_income_adjust" => 0,
            ],
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
                "cache_success_rate" => 0,
                "impressions_cache_ready_rate" => 0,
                "dsp_request" => 0,
                "dsp_response" => 0,
                "dsp_win" => 0,
                "dsp_notice" => 0,

            ],
            "other" => [
                "game_dau" => 0,
                "dau_rate" => 0,
                "skips" => 0,
                "effective_exits" => 0,
                "exits" => 0,
                "sdk_impressions" => 0,
                "redirect_browser" => 0,
                "redirect_internal_browser" => 0,
                "redirect_shop" => 0,
                "redirect_internal_shop" => 0,
                "download_app" => 0,
                "download_app_ok" => 0,
                "install_app" => 0,
                "install_app_ok" => 0,
                "download_app_rate" => 0,


                "cache_fail" => 0,
                "cache_success" => 0,
            ],
            "common" => [
                "user_dau" => 0,
                "new_user" => 0,
                "new_user_rate" => 0,
                "total_user" => 0,
                "total_user_rate" => 0,
                "impressions_user_dau" => 0,
                "impressions_total_user" => 0,
                "arpdau" => 0,
                "arpdeu" => 0,
                "total_init" => 0,
                //"effective_total_init" => 0,
                "impressions_total_init" => 0,
                //"impressions_effective_init" => 0,
                "user_init_count" => 0,
                "online_time" => 0,
                "online_time_dau" => 0,


            ],
        ];


        $kpiConf = self::getUserKpi($userId, 'mobgi');
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
            'ad_income_adjust',
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
            'total_init',
            'total_user',
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
    protected static function replaceReportData($items, $dims = []) {
        if (empty($items)) return [];
        //        foreach ($items as $key => $item) {
        //            empty($item['app_key']) or $appKey[$item['app_key']] = $item['app_key'];
        //            empty($item['pos_key']) or $posKey[$item['pos_key']] = $item['pos_key'];
        //        }
        in_array('app_key', $dims) and $appKey = self::getAppKeyMap();
        in_array('pos_key', $dims) and $posKey = self::getPosKeyMap();
        in_array('channel_gid', $dims) and $channelGid = self::getUserChannelGidMap();
        in_array('country', $dims) and $country = self::getCountryMap();
        in_array('ssp_id', $dims) and $ssp = [
            0 => 'Mobgi0',
            1 => 'Mobgi',
            2147483647 => '4399'
        ];

        $adType = Common_Service_Config::AD_TYPE;
        $platform = Common_Service_Config::PLATFORM;
        foreach ($items as $key => $item) {
            isset($item['ad_type']) and isset($adType[$item['ad_type']]) and $items[$key]['ad_type'] = $adType[$item['ad_type']];
            isset($item['app_key']) and isset($appKey[$item['app_key']]) and $items[$key]['app_key'] = $appKey[$item['app_key']];
            isset($item['pos_key']) and isset($posKey[$item['pos_key']]) and $items[$key]['pos_key'] = $posKey[$item['pos_key']];
            isset($item['platform']) and isset($platform[$item['platform']]) and $items[$key]['platform'] = $platform[$item['platform']];
            isset($item['country']) and isset($country[$item['country']]) and $items[$key]['country'] = $country[$item['country']];
            isset($item['channel_gid']) and isset($channelGid[intval($item['channel_gid'])]) and $items[$key]['channel_gid'] = $channelGid[intval($item['channel_gid'])];
            isset($item['ssp_id']) and isset($ssp[$item['ssp_id']]) and $items[$key]['ssp_id'] = $ssp[$item['ssp_id']];

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
        return array_intersect(array_merge(self::$baseKpis, self::$apiKpis, self::$dauKpis, self::$dspKpis), array_unique($kpis));
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
        if (array_intersect($dims, ['country', 'province'])) {
            array_push($tables, 'city');
            if (array_intersect($kpis, ['new_user', 'total_user', 'user_dau', 'game_dau', 'total_init'])) {
                array_push($tables, 'dauCity');
            }
        } else {
            if (array_intersect($kpis, ['new_user', 'total_user', 'user_dau', 'game_dau', 'total_init'])) {
                if (array_intersect($dims, ['app_version', 'sdk_version'])) {
                    array_push($tables, 'dauVer');
                } else {
                    array_push($tables, 'dau');
                }
            }
            if (array_intersect($kpis, ['third_views', 'third_clicks', 'ad_income'])) {
                if (!in_array('channel_gid', $dims) and (isset($params['is_custom']) and $params['is_custom'] === 0)) {
                    array_push($tables, 'api');
                } else {
                    array_push($tables, 'finance');
                }
            }
            if (array_intersect($kpis, ['dsp_request', 'dsp_response', 'dsp_win', 'dsp_notice'])) {
                array_push($tables, 'dsp');
            }


            if (array_intersect($kpis, self::$baseKpis)) {
                if (array_intersect($dims, ['hours'])) {
                    array_push($tables, 'hour');
                } else {
                    array_push($tables, 'day');
                }
            }


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
        $data = self::getFinanceData($params);
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

    public static function getApiData($params) {
        if (array_intersect($params['dims'], ['ssp_id'])) {
            return [];
        }
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

        $strField = self::buildFields($fields);
        $ret = self::getDao("ReportApi")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;
    }


    protected static function getChannelData($params) {
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


    protected static function getFinanceData($params) {
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
        $ret = self::getDao("ReportFinance")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;
    }


    protected static function getDauData($params) {

        if (array_intersect($params['dims'], ['ads_id', 'ssp_id', 'pos_key'])) {
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
        $table = in_array('hours', $params['dims']) ? 'ReportDauHour' : 'ReportDau';
        $ret = self::getDao($table)->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;

    }

    protected static function getDauVerData($params) {

        if (array_intersect($params['dims'], ['hours', 'ads_id', 'ssp_id', 'pos_key', 'channel_gid', 'ad_type'])) {
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

        $ret = self::getDao("ReportDauVer")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;

    }

    protected static function getDauCityData($params) {

        if (array_intersect($params['dims'], ['hours', 'ads_id', 'ssp_id', 'pos_key', 'channel_gid', 'ad_type', 'app_version', 'sdk_version'])) {
            return [];
        }

        $mydims = array_merge(self::$baseDims, ['country', 'province']);
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

        $where['country'] = in_array('country', $dims) ? array('!=', 0) : 0;
        $where['province'] = in_array('province', $dims) ? array('!=', 0) : 0;

        $ret = self::getDao("ReportDauCity")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;

    }

    protected static function getCityData($params) {
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

    protected static function getDspData($params) {


        if (array_intersect($params['dims'], ['channel_gid'])) {
            return [];
        }

        $mydims = array_merge(self::$baseDims, ['pos_key']);
        $mykpis = self::$dspKpis;
        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);

        $allFields = self::$baseFields;
        $allFields['channel_gid'] = 0;
        $allFields['is_custom'] = 0;

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

        $ret = Common::getDao("MobgiData_Dao_ReportDspModel")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;

    }

    protected static function getDayData($params) {
        $mydims = array_merge(self::$baseDims, ['ssp_id', 'pos_key', 'app_version', 'sdk_version']);
        $mykpis = array_merge(self::$baseKpis, ['used_time', 'exit_stay_time', 'skip_stay_time']);

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

    protected static function getHourData($params) {
        $mydims = array_merge(self::$baseDims, ['ssp_id', 'hour', 'pos_key', 'app_version', 'sdk_version']);
        $mykpis = array_merge(self::$baseKpis, ['used_time', 'exit_stay_time', 'skip_stay_time']);

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
        $sspMap = [
            0 => 'Mobgi0',
            1 => 'Mobgi',
            2147483647 => '4399'
        ];
        $dims = [
            'ad_type' => Common_Service_Config::AD_TYPE,
            'ads_id' => self::getAdsIdMapWithForeign(),
            'app_key' => self::getAppKeyMap($appKeys, 1),
            'channel_gid' => self::getUserChannelGidMap(),
            'platform' => Common_Service_Config::PLATFORM,
            'country' => Common_Service_Config::COUNTRY,
            'province' => Common_Service_Config::PROVINCE,
            'ssp_id' => $sspMap
        ];

        $dims['pos_key'] = self::getAppPosKeyMap(array_keys($dims['app_key']));
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


    public static function getAdsIncome($sdate, $edate, $dims) {
        $params['dims'] = $dims;
        $params['kpis'] = ['third_views', 'third_clicks', 'ad_income'];
        $params['sdate'] = $sdate;
        $params['edate'] = $edate;
        $list = self::getFinanceData($params);
        return $list;
    }


    /**
     * 求两个日期之间相差的天数
     * (针对1970年1月1日之后，求之前可以采用泰勒公式)
     * @param string $day1
     * @param string $day2
     * @return number
     */
    public static function diffBetweenTwoDays($day1, $day2) {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);

        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }


    public static function getDevIdByKey($key) {
        $where['secret_key'] = $key;
        $ret = self::getDao("ConfigSsp")->getBy($where);
        return empty($ret['dev_id']) ? 0 : $ret['dev_id'];
    }

    public static function getSspAppKeyByDevId($id) {
        $where['dev_id'] = $id;
        $appKeys = self::getApiDao("AdApp")->getFields('app_id,app_key', $where);
        return $appKeys;
    }


}