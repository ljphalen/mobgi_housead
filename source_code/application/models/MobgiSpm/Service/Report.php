<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * 自投数据报表
 * @author atom.zhan
 *
 */
class MobgiSpm_Service_ReportModel extends Common_Service_Report {
    protected static $appId = 0;
    protected static $userId = 0;
    protected static $userName = "";
    protected static $baseDims = [
        'is_natural' => ['name' => '用户类型'],
        'app_id' => ['name' => '应用'],
        'consumer_key' => ['name' => '游戏'],
        'app_key' => ['name' => '游戏'],
        'activity_id' => ['name' => '活动'],
        'activity_gid' => ['name' => '活动组'],
        'days' => ['name' => '日期'],
        'months' => ['name' => '月份'],
        'channel_id' => ['name' => '渠道'],
        'channel_gid' => ['name' => '渠道组'],
        'android_channel_no' => ['name' => '安卓渠道'],
        //        'android_channel_group_id' => ['name' => '安卓渠道组'],
        'staff' => ['name' => '投放师'],
    ];
    protected static $extDims = [];


    private static $baseKpis = [
        'device_dau' => ['name' => '活跃设备数', 'group' => 'rainbow'],
        'registers' => ['name' => '新增用户数', 'group' => 'rainbow'],
        'user_dau' => ['name' => '活跃用户数', 'group' => 'rainbow'],
        'user_pay' => ['name' => '付费用户数', 'group' => 'rainbow'],
        'user_new_pay' => ['name' => '新增付费用户数', 'group' => 'rainbow'],
        'user_first_pay' => ['name' => '首次付费用户数', 'group' => 'rainbow'],
        'pay_times' => ['name' => '付费次数', 'group' => 'rainbow'],
        'income' => ['name' => '收入', 'group' => 'rainbow'],
        'income_new_user' => ['name' => '新增用户收入', 'group' => 'rainbow'],
        'online_time' => ['name' => '平均在线时长(m)', 'group' => 'rainbow'],
        'total_time' => ['name' => '总时长', 'field' => 'sum(online_time*user_dau)', 'group' => 'rainbow'],

        'clicks' => ['name' => '点击数', 'group' => 'rainbow'],
        'effect_clicks' => ['name' => '去重点击数', 'group' => 'rainbow'],
        'actives' => ['name' => '激活数', 'group' => 'rainbow'],
        'callbacks' => ['name' => '回调数', 'group' => 'rainbow'],
        'unusual_callbacks' => ['name' => '异常回调数', 'group' => 'rainbow'],
        'overtime_callbacks' => ['name' => '超时回调数', 'group' => 'rainbow'],

        //----------------------------
        'impressions' => ['name' => '第三方展示数', 'group' => 'third'],
        'third_clicks' => ['name' => '第三方点击数', 'field' => 'sum(clicks)', 'group' => 'third'],
        'account_consumption' => ['name' => '广告消耗', 'group' => 'third'],
        'real_consumption' => ['name' => '实际消耗', 'group' => 'third'],
        'cpm' => ['name' => 'CPM', 'group' => 'common'],
        'cpc' => ['name' => 'CPC', 'group' => 'common'],
        'arpdau' => ['name' => '活跃ARPU', 'group' => 'common'],
        'arppay' => ['name' => '付费ARPU', 'group' => 'common'],

        'active_price' => ['name' => '激活均价', 'group' => 'common'],
        'user_price' => ['name' => '新增用户均价', 'group' => 'common'],
        'active_rate' => ['name' => '点击激活率(%)', 'title' => '激活数/点击数', 'group' => 'common'],
        'click_rate' => ['name' => '点击率(%)', 'title' => '第三方展示数/点击数', 'group' => 'common'],
        'third_click_rate' => ['name' => '第三方点击率(%)', 'title' => '第三方展示数/第三方点击数', 'group' => 'common'],
        'third_click_cmp_rate' => ['name' => '点击对比率(%)', 'title' => '第三方点击数/点击数', 'group' => 'common'],

        'pay_rate' => ['name' => '付费率(%)', 'title' => '付费用户数/活跃用户数', 'group' => 'common'],
        'new_user_pay_rate' => ['name' => '新增用户付费率(%)', 'title' => '新增用户数/活跃用户数', 'group' => 'common'],
        'daily_consumption' => ['name' => '计划消耗金额', 'group' => 'common'],
        'daily_amount' => ['name' => '计划新增', 'group' => 'common'],
        'daily_cost' => ['name' => '计划成本', 'group' => 'common'],

        'ltv1' => ['name' => 'LTV1', 'title' => 'LTV1', 'group' => 'ltv_retention'],
        'ltv3' => ['name' => 'LTV3', 'title' => 'LTV3', 'group' => 'ltv_retention'],
        'ltv7' => ['name' => 'LTV7', 'title' => 'LTV7', 'group' => 'ltv_retention'],
        'ltv14' => ['name' => 'LTV14', 'title' => 'LTV14', 'group' => 'ltv_retention'],
        'ltv30' => ['name' => 'LTV30', 'title' => 'LTV30', 'group' => 'ltv_retention'],

        'retention1' => ['name' => '留存1(%)', 'title' => '留存1', 'group' => 'ltv_retention'],
        'retention2' => ['name' => '留存2(%)', 'title' => '留存2', 'group' => 'ltv_retention'],
        'retention3' => ['name' => '留存3(%)', 'title' => '留存3', 'group' => 'ltv_retention'],
        'retention7' => ['name' => '留存7(%)', 'title' => '留存7', 'group' => 'ltv_retention'],
        'retention14' => ['name' => '留存14(%)', 'title' => '留存14', 'group' => 'ltv_retention'],
        'retention30' => ['name' => '留存30(%)', 'title' => '留存30', 'group' => 'ltv_retention'],

        'roi1' => ['name' => 'ROI1(%)', 'group' => 'ltv_retention'],
        'roi7' => ['name' => 'ROI7(%)', 'group' => 'ltv_retention'],
        'roi14' => ['name' => 'ROI14(%)', 'group' => 'ltv_retention'],
        'roi30' => ['name' => 'ROI30(%)', 'group' => 'ltv_retention'],
        'roi' => ['name' => '累计ROI(%)', 'group' => 'ltv_retention'],


        //        'real_consumption1' => ['name' => 'real_consumption1', 'group' => 'ltv_retention'],
        //        'real_consumption7' => ['name' => 'real_consumption7', 'group' => 'ltv_retention'],
        //        'real_consumption14' => ['name' => 'real_consumption14', 'group' => 'ltv_retention'],
        //        'real_consumption30' => ['name' => 'real_consumption30', 'group' => 'ltv_retention'],
        //
        //        'amount1' => ['name' => 'amount1', 'group' => 'ltv_retention'],
        //        'amount3' => ['name' => 'amount3', 'group' => 'ltv_retention'],
        //        'amount7' => ['name' => 'amount7', 'group' => 'ltv_retention'],
        //        'amount14' => ['name' => 'amount14', 'group' => 'ltv_retention'],
        //        'amount30' => ['name' => 'amount30', 'group' => 'ltv_retention'],
        'total_amount' => ['name' => '新增用户累计收入', 'group' => 'ltv_retention'],

        //        'retention_stay1' => ['name' => 'retention_stay1', 'group' => 'ltv_retention'],
        //        'retention_stay2' => ['name' => 'retention_stay2', 'group' => 'ltv_retention'],
        //        'retention_stay3' => ['name' => 'retention_stay3', 'group' => 'ltv_retention'],
        //        'retention_stay7' => ['name' => 'retention_stay7', 'group' => 'ltv_retention'],
        //
        //        'retention_reg1' => ['name' => 'retention_reg1', 'group' => 'ltv_retention'],
        //        'retention_reg2' => ['name' => 'retention_reg2', 'group' => 'ltv_retention'],
        //        'retention_reg3' => ['name' => 'retention_reg3', 'group' => 'ltv_retention'],
        //        'retention_reg7' => ['name' => 'retention_reg7', 'group' => 'ltv_retention'],

    ];
    private static $extKpis = [
        'amount1',
        'amount3',
        'amount7',
        'amount14',
        'amount30',

        'real_consumption1',
        'real_consumption7',
        'real_consumption14',
        'real_consumption30',

        'ltv_reg1',
        'ltv_reg3',
        'ltv_reg7',
        'ltv_reg14',
        'ltv_reg30',

        'retention_stay1',
        'retention_stay2',
        'retention_stay3',
        'retention_stay7',
        'retention_stay14',
        'retention_stay30',

        'retention_reg1',
        'retention_reg2',
        'retention_reg3',
        'retention_reg7',
        'retention_reg14',
        'retention_reg30',
        'kpi_rate',
        'consumption_rate',
        'cost_rate',
        'cost',
    ];

    public static $kpiFields = [
        'MonitorDeliveryPlanDay' => ['daily_consumption', 'daily_amount'],
        'MonitorReport' => ['impressions', 'third_clicks', 'account_consumption', 'real_consumption'],
        'RainbowReport' => [
            'device_dau',
            'user_dau',
            'user_pay',
            'user_new_pay',
            'user_first_pay',
            'pay_times',
            'income',
            'income_new_user',
            'total_time',
            'clicks',
            'effect_clicks',
            'actives',
            'callbacks',
            'registers',
            'unusual_callbacks',
            'overtime_callbacks'
        ],
    ];
    public static $sumField = [
        'device_dau',
        'registers',
        'user_dau',
        'user_pay',
        'user_new_pay',
        'user_first_pay',
        'pay_times',
        'income',
        'income_new_user',
        'clicks',
        'effect_clicks',
        'actives',
        'callbacks',
        'unusual_callbacks',
        'overtime_callbacks',
        'impressions',
        'third_clicks',
        'account_consumption',
        'real_consumption',
        'daily_consumption',
        'daily_amount',
        'total_time',
        'amount1',
        'amount3',
        'amount7',
        'amount14',
        'amount30',
        'total_amount',
        'ltv_reg1',
        'ltv_reg3',
        'ltv_reg7',
        'ltv_reg14',
        'ltv_reg30',
        'real_consumption1',
        'real_consumption7',
        'real_consumption14',
        'real_consumption30',
        'retention_stay1',
        'retention_stay2',
        'retention_stay3',
        'retention_stay7',
        'retention_reg1',
        'retention_reg2',
        'retention_reg3',
        'retention_reg7',

    ];
    public static $kpiGroup = [
        'rainbow' => ['name' => '彩虹数据'],
        'common' => ['name' => '通用'],
        'third' => ['name' => '第三方数据'],
        'ltv_retention' => ['name' => 'LTV|留存'],
    ];
    public static $expandFields = [
        'active_rate' => ['actives', 'clicks', 100, 2],
        'click_rate' => ['clicks', 'impressions', 100, 2],
        'third_click_rate' => ['third_clicks', 'impressions', 100, 2],
        'third_click_cmp_rate' => ['third_clicks', 'clicks', 100, 2],
        'cpm' => ['account_consumption', 'impressions', 1000, 2],
        'cpc' => ['account_consumption', 'clicks', 1, 2],
        'active_price' => ['real_consumption', 'actives', 1, 2],
        'user_price' => ['real_consumption', 'registers', 1, 2],
        'arpdau' => ['income', 'user_dau', 1, 2],
        'arppay' => ['income', 'user_pay', 1, 2],
        'pay_rate' => ['user_pay', 'user_dau', 100, 2],
        'new_user_pay_rate' => ['user_new_pay', 'registers', 100, 2],
        'online_time' => ['total_time', 'user_dau', 1, 2],

        'ltv1' => ['amount1', 'ltv_reg1', 1, 2],
        'ltv3' => ['amount3', 'ltv_reg3', 1, 2],
        'ltv7' => ['amount7', 'ltv_reg7', 1, 2],
        'ltv14' => ['amount14', 'ltv_reg14', 1, 2],
        'ltv30' => ['amount30', 'ltv_reg30', 1, 2],

        'retention1' => ['retention_stay1', 'retention_reg1', 100, 2],
        'retention2' => ['retention_stay2', 'retention_reg2', 100, 2],
        'retention3' => ['retention_stay3', 'retention_reg3', 100, 2],
        'retention7' => ['retention_stay7', 'retention_reg7', 100, 2],
        'retention14' => ['retention_stay14', 'retention_reg14', 100, 2],
        'retention30' => ['retention_stay30', 'retention_reg30', 100, 2],

        'roi1' => ['amount1', 'real_consumption1', 100, 2],
        'roi7' => ['amount7', 'real_consumption7', 100, 2],
        'roi14' => ['amount14', 'real_consumption14', 100, 2],
        'roi30' => ['amount30', 'real_consumption30', 100, 2],
        'roi' => ['total_amount', 'real_consumption', 100, 2],


        'daily_cost' => ['daily_consumption', 'daily_amount', 1, 2],
        'cost' => ['real_consumption', 'registers', 1, 2],

        'kpi_rate' => ['registers', 'daily_amount', 100, 2],
        'consumption_rate' => ['real_consumption', 'daily_consumption', 100, 2],
        'cost_rate' => ['cost', 'daily_cost', 100, 2],


    ];


    public static $retentionFields = [
        'app_id' => 'app_id',
        'is_natural' => 'is_natural',
        'consumer_key' => 'consumer_key',
        'activity_id' => 'activity_id',
        'activity_gid' => 'activity_gid',
        'channel_id' => 'channel_id',
        'channel_gid' => 'channel_gid',
        'android_channel_no' => 'android_channel_no',
        'months' => 'months',
        'days' => 'create_date',
        'action_date' => 'action_date',
        'rday' => 'rday',
        'staff' => 'staff',
        'user_counts' => 'sum(`user_counts`)',
    ];
    public static $ltvFields = [
        'app_id' => 'app_id',
        'is_natural' => 'is_natural',
        'consumer_key' => 'consumer_key',
        'activity_id' => 'activity_id',
        'activity_gid' => 'activity_gid',
        'channel_id' => 'channel_id',
        'channel_gid' => 'channel_gid',
        'months' => 'months',
        'days' => 'create_date',
        'action_date' => 'action_date',
        'staff' => 'staff',
        'android_channel_no' => 'android_channel_no',
        'rday' => 'rday',
        'amount' => 'sum(`amount`)',
        'user_counts' => 'sum(`user_counts`)',
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
    private static $defaultSelectedKpi = ['impressions', 'clicks', 'actives', 'income', 'dau_user', 'clicks_rate', 'arpu'];


    public static $retentionDays = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 30, 60, 90];
    public static $channelRetentionDays = [1, 2, 3, 4, 5, 6, 7, 10, 14, 30];


    public static function setUserId($id) {
        self::$userId = $id;
    }

    public static function setUserName($name) {
        self::$userName = $name;
    }

    public static function setAppId($id) {
        self::$appId = $id;
    }

    public static function getFilterFields() {
        return array_keys(self::$baseDims);
    }

    //获取报表配置
    public static function getChartConf($params) {
        $appId = $params['app_id'];
        $conf = [
            'api' => [
                "data" => '/Admin/Spm_Report/getSpmData',
                "conf" => '/Admin/Spm_Report/updateSpmKpi',
            ],
            'kpi' => self::getKpis(),
            "dim" => self::getChartDims(self::$userName, $appId, isset($params['type'])),
            'my_dim' => ['days' => []],
            'box' => [],
            sortBy => 'registers'
        ];

        if (isset($params['type'])) {
            $conf['dim_fields'] = [
                "days" => "日期",
                "months" => "月份",
                "hr1" => "-",
                "android_channel_no" => "安卓渠道号",
                "channel_gid" => "渠道组",
            ];
            $conf['api']["data"] = '/Admin/Spm_Report/getSpmData?type=channel';
        } else {
            $conf['my_dim']['is_natural'] = [0];
            $conf['dim_fields'] = [
                "days" => "日期",
                "months" => "月份",
                "hr1" => "-",
                "is_natural" => "用户类型",
                "activity_id" => "活动",
                "activity_gid" => "活动组",
                "channel_id" => "渠道",
                "channel_gid" => "渠道组",
            ];
        }
        if ($appId == 1) {
            $conf['dim_fields']["hr2"] = "-";
            $conf['dim_fields']["app_id"] = "应用";
        }
        if (Yaf_Registry::get('is_admin')) {
            $conf['dim_fields']["hr2"] = "-";
            $conf['dim_fields']["staff"] = "投放师";
        }


        $conf['conf'] = self::getKeyMap();
        $conf['my_kpi'] = self::getMyKpis(self::$userId);
        foreach ($conf['dim'] as $key => $val) {
            $conf['box'][$key] = [];
        }
        return $conf;
    }

    public static function getLtvChartConf($params) {
        $appId = $params['app_id'];
        $conf = [
            'chart_type' => 'ltv',
            'api' => [
                "data" => '/Admin/Spm_Report/getLtvData',
            ],
            'kpi' => self::getKpis(),
            "dim" => self::getChartDims(self::$userName, $appId, isset($params['type'])),
            'my_dim' => ["days" => []],
            'fix_dim' => [],
            'box' => [],
        ];
        if (isset($params['type'])) {
            $conf['dim_fields'] = [
                "days" => "日期",
                "months" => "月份",
                "hr1" => "-",
                "android_channel_no" => "安卓渠道号",
                "channel_gid" => "渠道组",
            ];
            $conf['api']["data"] = '/Admin/Spm_Report/getLtvData?type=channel';
        } else {
            $conf['my_dim']['is_natural'] = [0];
            $conf['dim_fields'] = [
                "days" => "日期",
                "months" => "月份",
                "hr1" => "-",
                "is_natural" => "用户类型",
                "activity_id" => "活动",
                "activity_gid" => "活动组",
                "channel_id" => "渠道",
                "channel_gid" => "渠道组",
            ];
        }
        if (Yaf_Registry::get('is_admin')) {
            $conf['dim_fields']["hr2"] = "-";
            $conf['dim_fields']["staff"] = "投放师";
        }

        $conf['conf'] = self::getKeyMap();
        $conf['my_kpi'] = self::getMyKpis(self::$userId);
        foreach ($conf['dim'] as $key => $val) {
            $conf['box'][$key] = [];
        }
        return $conf;
    }

    public static function getRetentionChartConf($params) {
        $appId = $params['app_id'];
        $conf = [
            'chart_type' => 'retention',
            'api' => [
                "data" => '/Admin/Spm_Report/getRetentionData',
            ],
            'kpi' => self::getKpis(),
            "dim" => self::getChartDims(self::$userName, $appId, isset($params['type'])),
            'my_dim' => ["days" => []],
            'box' => [],
            'chart' => [
                'show' => 0
            ]
        ];
        if (isset($params['type'])) {
            $conf['dim_fields'] = [
                "days" => "日期",
                "months" => "月份",
                "hr1" => "-",
                "android_channel_no" => "安卓渠道号",
                "channel_gid" => "渠道组",
            ];
            $conf['api']["data"] = '/Admin/Spm_Report/getRetentionData?type=channel';
        } else {
            $conf['my_dim']['is_natural'] = [0];
            $conf['dim_fields'] = [
                "days" => "日期",
                "months" => "月份",
                "hr1" => "-",
                "is_natural" => "用户类型",
                "activity_id" => "活动",
                "activity_gid" => "活动组",
                "channel_id" => "渠道",
                "channel_gid" => "渠道组",
            ];

        }
        if (Yaf_Registry::get('is_admin')) {
            $conf['dim_fields']["hr2"] = "-";
            $conf['dim_fields']["staff"] = "投放师";
        }

        $conf['conf'] = self::getKeyMap();
        $conf['my_kpi'] = self::getMyKpis(self::$userId);
        foreach ($conf['dim'] as $key => $val) {
            $conf['box'][$key] = [];
        }
        return $conf;
    }


    //   获取指标
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

    //   获取指标
    private static function getMyKpis($userId) {
        $kpiConf = self::getUserKpi($userId, 'spm');
        $myKpi = empty($kpiConf['kpis']) ? ['clicks', 'actives'] : explode('|', $kpiConf['kpis']);
        return $myKpi;
    }


    //   获取指标
    private static function getKeyMap() {
        $conf = [];
        $all = array_merge(self::$baseDims, self::$baseKpis, self::$kpiGroup);
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
        foreach (self::$baseKpis as $key => $val) {
            $conf[$key] = isset($val['field']) ? $val['field'] : "sum({$key})";
        }
        return $conf;
    }

    //获取广告计划
    public static function getPlan($params) {
        $kpi = ['daily_consumption', 'daily_amount', 'daily_cost'];
        $params['dims'] = ['app_id'];
        $params['kpis'] = self::expandKpi($kpi);
        if (isset($params['app_id'][0])) {
            $os = self::getAppOs($params['app_id'][0]);
            if ($os == 'android') {
                $params['type'] = ['channel'];
            }
        }
        $data = self::getCommonData($params);
        return $data;

    }

    //获取广告月计划
    public static function getMonthPlan($params) {
        $kpi = ['daily_consumption', 'daily_amount', 'daily_cost'];
        $params['order'] = ['days' => 'asc'];
        $params['dims'] = ['app_id', 'channel_gid', 'months'];
        $params['kpis'] = self::expandKpi($kpi);
        $data = self::getCommonData($params);
        return $data;
    }


    //获取广告计划app
    public static function getPlanApp($params) {
        if (isset($params['app_id'])) {
            $mywhere['app_id'] = ['in', $params['app_id']];
        }
        $mywhere['start_date'] = array('>=', $params['sdate']);
        $mywhere['end_date'] = array('<=', $params['edate']);
        $ret = self::getSpmDao("MonitorDeliveryPlan")->getFields('app_id,app_id', $mywhere);
        return empty($ret) ? [] : $ret;
    }


    /**
     * 获取总体新增
     * @param $params
     * @return array
     */
    public static function getTotalRegisters($params) {

        $where = self::buildWhere($params);
        $data = self::getSpmDao('RainbowReport')->sum('registers', $where);

        return intval($data);
    }

    /**
     * 投放师KPI完成进度
     * @param $params
     * @return array
     */
    public static function getStaffKpi($params) {
        $kpi = [
            'registers',
            'daily_amount',
            'real_consumption',
            'daily_consumption',
            'cost',
            'daily_cost',
            'kpi_rate',
            'consumption_rate',
            'cost_rate'
        ];
        $params['dims'] = ['staff'];
        $params['kpis'] = self::expandKpi($kpi);
        if (isset($params['app_id'][0])) {
            $os = self::getAppOs($params['app_id'][0]);
            if ($os == 'android') {
                $params['type'] = ['channel'];
            }
        }
        $data = self::getCommonData($params);
        foreach ($data as $key => $item) {
            if ($item['daily_consumption'] == 0) {
                unset($data[$key]);
            }
        }
        return $data;
    }


    /**
     * 投放师KPI完成进度
     * @param $params
     * @return array
     */
    public static function getChannelKpi($params) {
        $kpi = [
            'registers',
            'daily_amount',
            'real_consumption',
            'daily_consumption',
            'cost',
            'daily_cost',
            'kpi_rate',
            'consumption_rate',
            'cost_rate'
        ];
        $params['dims'] = ['channel_gid'];
        $params['kpis'] = self::expandKpi($kpi);
        $data = self::getCommonData($params);
        $channels = self::getChannelGroupMap();
        foreach ($data as $key => $item) {
            if (empty($item['daily_consumption'])) {
                unset($data[$key]);
            } else {
                $data[$key]['channel_name'] = $channels[$item['channel_gid']] ?: $item['channel_gid'];
            }
        }
        return $data;
    }

    /**
     * 获取top5数据
     * @param $params
     * @return array
     */
    public static function getCommonData($params) {
        $data = self::getReportData($params);
        $data = self::expandReportData($data, self::$expandFields, $params['kpis']);
        $data = self::replaceReportData($data, $params['dims']);
        return $data;
    }

    /**
     * 获取之前日期的结果集
     * @param unknown $params
     * @return boolean|unknown
     */
    public static function getData($params) {
        // 用户权限
        if (!isset($params['permit'])) {
            $params['permit'] = [];
        }

        if (self::$userId > 0) {
            if (!Yaf_Registry::get('is_admin')) {
                $params['permit']['staff'] = self::$userName;
            }
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
        foreach ($items as $key => $item) {
            empty($item['app_key']) or $appKey[$item['app_key']] = $item['app_key'];
            empty($item['pos_key']) or $posKey[$item['pos_key']] = $item['pos_key'];
        }
        in_array('app_key', $dims) and $appKey = self::getAppKeyMap($appKey);
        in_array('pos_key', $dims) and $posKey = self::getPosKeyMap($posKey);
        in_array('flow_id', $dims) and $flowId = self::getFlowMap();
        in_array('conf_id', $dims) and $confId = self::getKeyMapMap();


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


    //    private static function expandKpi($kpis) {
    //        if (empty($kpis)) {
    //            return [];
    //        }
    //        $expandFields = self::$expandFields;
    //        foreach ($kpis as $key => $kpi) {
    //            if (array_key_exists($kpi, $expandFields)) {
    //                //                unset($kpis[$key]);
    //                array_push($kpis, $expandFields[$kpi][0], $expandFields[$kpi][1]);
    //
    //            }
    //        }
    //        return array_intersect(array_merge(array_keys(self::$baseKpis)), array_unique($kpis));
    //    }

    protected static function expandKpi($kpis) {
        if (empty($kpis)) {
            return [];
        }
        foreach ($kpis as $kpi) {
            $exkpis = self::getExpandKpi($kpi);
            $kpis = array_merge($kpis, $exkpis);
        }
        return array_intersect(array_merge(array_keys(self::$baseKpis), self::$extKpis), array_unique($kpis));
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
        if (array_intersect($kpis, ['daily_consumption', 'daily_amount'])) {
            if (array_intersect($dims, ['staff'])) {
                array_push($tables, 'monitorStaffPlanDay');
            } else {
                array_push($tables, 'monitorDeliveryPlanDay');
            }


        }
        if (array_intersect($kpis, ['impressions', 'third_clicks', 'account_consumption', 'real_consumption'])) {
            array_push($tables, 'monitorReport');
        }

        if (array_intersect($kpis, ['ltv1', 'ltv3', 'ltv7', 'ltv14', 'ltv30', 'roi', 'roi1', 'roi7', 'roi14', 'roi30', 'total_amount'])) {
            array_push($tables, 'ltvReport');
        }
        if (array_intersect($kpis, ['retention1', 'retention2', 'retention3', 'retention7', 'retention14', 'retention30'])) {
            array_push($tables, 'retentionReport');
        }

        if (array_intersect($kpis, [
            'device_dau',
            'user_dau',
            'user_pay',
            'user_new_pay',
            'user_first_pay',
            'pay_times',
            'income',
            'income_new_user',
            'online_time',
            'clicks',
            'effect_clicks',
            'actives',
            'callbacks',
            'unusual_callbacks',
            'overtime_callbacks',
            'registers'
        ])) {
            if (array_intersect($dims, ['android_channel_no']) or (isset($params['type']) and $params['type'] = 'channel')) {
                array_push($tables, 'rainbowChannelReport');
            } else {
                array_push($tables, 'rainbowReport');
            }

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
        return self::mergeDate($data, $params['dims'], $params['kpis']);

    }


    public static function mergeDate($data, $dims, $kpis) {
        $result = [];
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

    //获取账户关联维度
    public static function getChartDims($userName = null, $appId, $type = 0) {
        if ($appId == 1) {
            $appId = 0;
        }
        if ($userName != null) {
            if (empty($userName)) {
                $dims = [
                    'is_natural' => [],
                    'ad_type' => [],
                    'app_key' => [],
                    'pos_key' => [],
                    'channel_id' => [],
                    'channel_gid' => [],
                    'ads_id' => [],
                ];
                return $dims;
            }
        }
        $activityMap = self::getActivityMap($userName, $appId);
        $myGroupId = self::getActivityGroup($userName, $appId);
        $dims = [
            'is_natural' => ['广告投放', '自然量'],
            'app_id' => self::getAppMap(),
            'activity_id' => $activityMap,
            'activity_gid' => self::getActivityGroupMap($myGroupId, $appId),
            'channel_id' => self::getChannelMap($appId),
            'channel_gid' => self::getChannelGroupMap(),
        ];
        if (Yaf_Registry::get('is_admin')) {
            $dims ['staff'] = self::getStaffMap();
        }
        if (empty($appId)) {
            $dims ['app_id'] = self::getAppMap();
        }
        if ($type) {
            $dims ['android_channel_no'] = self::getAndroidChannelMap($appId);
//            $dims ['android_channel_group_id'] = self::getAndroidChannelGroupMap();
        }
        return $dims;
    }

    protected static function getMonitorReportData($params) {
        $mydims = array_merge(array_keys(self::$baseDims));
        $mykpis = array_intersect(array_keys(self::$baseKpis), self::$kpiFields['MonitorReport']);
        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);
        $allFields = self::getFields();
        $allFields['days'] = 'date_of_log';
        $allFields['channel_gid'] = 'channel_group_id';

        //        $allFields['activity_gid'] = 'group_id';
        $fields = [];
        foreach (array_merge($dims, $kpis, ['is_natural']) as $item) {
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
        $where = self::exchangeWhere($where, [
            'days' => 'date_of_log',
            'channel_gid' => 'channel_group_id'
        ]);
        $strField = self::buildFields($fields);

        $ret = self::getSpmDao("MonitorReport")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;

    }

    protected static function getRainbowReportData($params) {
        if (array_intersect($params['dims'], ['android_channel_no', 'android_channel_group_id'])) {
            return [];
        }
        $mydims = array_merge(array_keys(self::$baseDims));
        $mykpis = array_intersect(array_keys(self::$baseKpis), self::$kpiFields['RainbowReport']);
        $dims = array_intersect($params['dims'], $mydims) ?: [];
        $kpis = array_intersect($params['kpis'], $mykpis);
        $allFields = self::getFields();

        $fields = [];
        foreach (array_merge($dims, $kpis) as $item) {
            if (isset($item, $allFields)) {
                $fields[$item] = $allFields[$item];
            }
        }
        $groupBy = empty($dims) ? null : 'GROUP BY ' . implode(',', $dims);
        // 日期倒序

        $orderBy = $params['order'] ?: null;
        $limit = $params['limit'] ?: 0;

        $where = self::buildWhere($params);
        $strField = self::buildFields($fields);

        $ret = self::getSpmDao("RainbowReport")->getData($strField, $where, $groupBy, $orderBy, $limit);
        return empty($ret) ? [] : $ret;

    }

    protected static function getRainbowChannelReportData($params) {
        if (array_intersect($params['dims'], ['activity_id', 'activity_gid'])) {
            return [];
        }
        $mydims = array_merge(array_keys(self::$baseDims));
        $mykpis = array_intersect(array_keys(self::$baseKpis), self::$kpiFields['RainbowReport']);
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


        if (!isset($where['channel_gid'])) {
            $where['channel_gid'] = array('>', 0);
        }


        $strField = self::buildFields($fields);

        $ret = self::getSpmDao("RainbowChannelReport")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;

    }

    //活动LTV
    public static function getLtvData($params) {
        $ltvFields = self::$ltvFields;
        $title = [
            'app_id' => '应用',
            'is_natural' => '用户类型',
            'consumer_key' => '应用',
            'activity_id' => '活动',
            'activity_gid' => '活动组',
            'channel_id' => '渠道',
            'channel_gid' => '渠道组',
            'create_date' => '日期',
            'action_date' => '日期',
            'staff' => '投放师',
            'days' => '日期',
            'registers' => '新增用户数',
            'android_channel_no' => '安卓渠道号',
            'total_amount' => '累计收入',
        ];
        $ltvRange = [0, 1, 2, 3, 4, 5, 6, 13, 29, 59, 89, 179, 359];

        $dims = $params['dims'] ?: [];
        if (!in_array('days', $params['dims'])) {
            $params['dims'][] = 'days';
        }
        $mydims = $params['dims'];
        $registers = self::getLtvNewUser($params);
        $where = self::buildWhere($params);
        foreach ($mydims as $key => $dim) {
            if (isset($ltvFields[$dim])) {
                $mydimsFields[] = $ltvFields[$dim];
            }
        }
        $kpis = ['amount', 'user_counts', 'days', 'action_date', 'rday'];
        if (!empty($mydimsFields)) {
            $orderBy = [];
            foreach ($mydimsFields as $dkey => $dimField) {
                array_push($orderBy, $dimField . ' asc');
            }
            $orderBy = implode(',', $orderBy);
        } else {
            $orderBy = NULL;
        }
        $where['create_date'] = $where['days'];
        unset($where['days']);
        $groupBy = 'GROUP BY ' . implode(',', array_unique(array_merge($mydimsFields ?: [], ['create_date', 'rday'])));

        $fields = [];
        foreach (array_merge($dims, $kpis) as $item) {
            if (array_key_exists($item, $ltvFields)) {
                $fields[$item] = $ltvFields[$item];
            }
        }
        $strField = self::buildFields($fields);
        if (isset($params['type']) and $params['type'] = 'channel') {
            $mydao = "RainbowChannelLtv";
            if (!isset($where['channel_gid'])) {
                $where['channel_gid'] = array('>', 0);
            }
        } else {
            $mydao = "RainbowLtv";
        }
        $list = self::getSpmDao($mydao)->getData($strField, $where, $groupBy, $orderBy);

        foreach ($list as $key => $item) {
            $rday = $item['rday'];
            $myKey = '0';
            foreach ($mydims as $dim) {
                $myKey .= '_' . $item[$dim];
            }
            if (empty($result[$myKey])) {
                foreach ($mydims as $dim) {
                    $result[$myKey][$dim] = $item[$dim];
                }
            }
            $result[$myKey]['ltv'][$rday] += $item['amount'];
        }
        $getLtv = function ($ltvs, $regs, $maxday, $ltvRange) {
            $ltv = [];
            $amount = 0;
            for ($i = 0; $i < $maxday; $i++) {
                if (isset($ltvs[$i])) {
                    $amount += $ltvs[$i];
                }
                if (in_array($i, $ltvRange)) {
                    $ltv[$i + 1] = $regs > 0 ? round($amount / $regs, 2) : 0;
                }
            }
            return $ltv;
        };
        $getAmount = function ($ltvs, $maxday, $ltvRange) {
            $sum = 0;
            $amount = [];
            for ($i = 0; $i < $maxday; $i++) {
                if (isset($ltvs[$i])) {
                    $sum += $ltvs[$i];
                }
                if (in_array($i, $ltvRange)) {
                    $amount[$i + 1] = $sum;
                }
            }
            return $amount;
        };

        //        判断是否带日期维度
        if (!in_array('days', $dims)) {
            foreach ($result as $myKey => $val1) {
                $diff = date_diff(date_create(date('Y-m-d')), date_create($val1['days']))->days;
                $result[$myKey]['amount'] = $getAmount($val1['ltv'], $diff, $ltvRange);
                $result[$myKey]['registers'] = array_sum($registers[$myKey]);
            }
            //中间转换结果
            $result2 = [];
            $amounts = [];
            foreach ($result as $val1) {
                $myKey = '0';
                foreach ($dims as $dim) {
                    $myKey .= '_' . $val1[$dim];
                }
                if (empty($result[$myKey])) {
                    foreach ($dims as $dim) {
                        $result2[$myKey][$dim] = $val1[$dim];
                    }
                }
                $result2[$myKey]['registers'] += $val1['registers'];
                $result2[$myKey]['total_amount'] += array_sum($val1['ltv']);
                foreach ($val1['amount'] as $a_key => $amount) {
                    $amounts[$myKey][$a_key] += $amount;
                    $result2[$myKey]['regs'][$a_key] += $val1['registers'];
                }
            }
            $result = [];
            foreach ($result2 as $key2 => $val2) {
                $result[$key2] = $val2;
                if (isset($amounts[$key2])) {
                    foreach ($amounts[$key2] as $akey2 => $amount) {
                        $result[$key2]['ltv'][$akey2] = $val2['regs'][$akey2] > 0 ? round($amount / $val2['regs'][$akey2], 2) : 0;
                        //                        $result[$key2]['ltv'][$akey2] = $amount;
                        //                         $result[$key2]['ltv'][$akey2] = $val2['regs'][$akey2];
                    }
                }
            }

        } else {
            foreach ($result as $myKey => $val1) {
                $diff = date_diff(date_create(date('Y-m-d')), date_create($val1['days']))->days;
                $result[$myKey]['ltv'] = $getLtv($val1['ltv'], array_sum($registers[$myKey]), $diff, $ltvRange);
                $result[$myKey]['total_amount'] = array_sum($val1['ltv']);
                $result[$myKey]['registers'] = array_sum($registers[$myKey]);
            }
        }

        $ex_kpis = ['registers', 'total_amount'];
        $cols = [];

        $ltvRange = array_map(function ($v) {
            return $v + 1;
        }, $ltvRange);


        if (!empty($list)) {
            foreach (array_merge($dims, $ex_kpis) as $dim) {
                $col = [
                    'field' => $dim,
                    'title' => $title[$dim],
                    'width' => 120,
                    'fixed' => 'left'
                ];
                $cols[] = $col;
            }
            foreach ($ltvRange as $ltv) {
                $col = [
                    'field' => 'ltv' . $ltv,
                    'title' => 'ltv' . $ltv,
                    'width' => 100,
                    'sort' => true
                ];
                $cols[] = $col;
            }
        }

        $data['dims'] = $dims;
        $data['ex_kpis'] = $ex_kpis;
        $data['data'] = array_values($result);
        $data['cols'] = $cols;
        $data['title'] = 'LTV报表';
        $data['days'] = $ltvRange;
        return $data;
    }


    //活动LTV
    public static function getLtvReportData($params) {
        $ltvFields = self::$ltvFields;
        $ltvRange = [0, 2, 6, 13, 29];
        $dims = $params['dims'] ?: [];
        if (!in_array('days', $params['dims'])) {
            $params['dims'][] = 'days';
        }
        $mydims = $params['dims'];
        $registers = self::getLtvNewUser($params);
        $consumptions = self::getRealConsumption($params);

        $where = self::buildWhere($params);
        foreach ($mydims as $key => $dim) {
            if (isset($ltvFields[$dim])) {
                $mydimsFields[] = $ltvFields[$dim];
            }
        }
        $kpis = ['amount', 'user_counts', 'days', 'action_date', 'rday'];
        if (!empty($params['order'])) {
            $orderBy = $params['order'];
        } else {
            $orderBy = NULL;
        }


        $where['create_date'] = $where['days'];
        $where['rday'] = array('<=', 30);
        unset($where['days']);
        $groupBy = 'GROUP BY ' . implode(',', array_unique(array_merge($mydimsFields ?: [], ['create_date', 'rday'])));

        $fields = [];
        foreach (array_merge($dims, $kpis) as $item) {
            if (array_key_exists($item, $ltvFields)) {
                $fields[$item] = $ltvFields[$item];
            }
        }
        $strField = self::buildFields($fields);
        if (isset($params['type']) and $params['type'] = 'channel') {
            $mydao = "RainbowChannelLtv";
            if (!isset($where['channel_gid'])) {
                $where['channel_gid'] = array('>', 0);
            }
        } else {
            $mydao = "RainbowLtv";
        }

        $limit = $params['limit'] ?: 0;
        $list = self::getSpmDao($mydao)->getData($strField, $where, $groupBy, $orderBy, $limit);

        foreach ($list as $key => $item) {
            $rday = $item['rday'];
            $myKey = '0';
            foreach ($mydims as $dim) {
                $myKey .= '_' . $item[$dim];
            }
            if (empty($result[$myKey])) {
                foreach ($mydims as $dim) {
                    $result[$myKey][$dim] = $item[$dim];
                }
            }
            $result[$myKey]['ltv'][$rday] += $item['amount'];
        }
        $getAmount = function ($ltvs, $maxday, $ltvRange) {
            $sum = 0;
            $amount = [];
            for ($i = 0; $i < $maxday; $i++) {
                if (isset($ltvs[$i])) {
                    $sum += $ltvs[$i];
                }
                if (in_array($i, $ltvRange)) {
                    $amount[$i + 1] = $sum;
                }

            }
            $amount[0] = $sum;
            return $amount;
        };
        //        判断是否带日期维度
        if (!in_array('days', $dims)) {
            foreach ($result as $myKey => $val1) {
                $diff = date_diff(date_create(date('Y-m-d')), date_create($val1['days']))->days;
                $result[$myKey]['amount'] = $getAmount($val1['ltv'], $diff, $ltvRange);
                $result[$myKey]['registers'] = array_sum($registers[$myKey]);
                $result[$myKey]['consumptions'] = $consumptions[$myKey] ? array_sum($consumptions[$myKey]) : 0;
            }
            //中间转换结果
            $result2 = [];
            $amounts = [];
            $regs = [];
            $consumptions = [];
            foreach ($result as $val1) {
                $myKey = '0';
                foreach ($dims as $dim) {
                    $myKey .= '_' . $val1[$dim];
                }
                if (empty($result[$myKey])) {
                    foreach ($dims as $dim) {
                        $result2[$myKey][$dim] = $val1[$dim];
                    }
                }
                //$result2[$myKey]['registers'] += $val1['registers'];
                //$result2[$myKey]['total_amount'] += array_sum($val1['ltv']);
                foreach ($val1['amount'] as $a_key => $amount) {
                    $amounts[$myKey][$a_key] += $amount;
                    $regs[$myKey][$a_key] += $val1['registers'];
                    $consumptions[$myKey][$a_key] += $val1['consumptions'];
                }
            }
            $result = [];
            foreach ($result2 as $key2 => $val2) {
                $result[$key2] = $val2;
                if (isset($amounts[$key2])) {
                    foreach ($amounts[$key2] as $akey2 => $amount) {
                        if ($akey2 == 0) {
                            $result[$key2]['total_amount'] = $amount;
                        } else {
                            $result[$key2]['amount' . $akey2] = $amount;
                            $result[$key2]['ltv_reg' . $akey2] = $regs[$key2][$akey2];
                            $result[$key2]['real_consumption' . $akey2] = $consumptions[$key2][$akey2];
                        }

                    }
                }
            }
        } else {
            foreach ($result as $myKey => $val1) {
                $diff = date_diff(date_create(date('Y-m-d')), date_create($val1['days']))->days;
                $amount_list = $getAmount($val1['ltv'], $diff, $ltvRange);
                unset($result[$myKey]['ltv']);
                foreach ($amount_list as $key => $amount) {
                    $result[$myKey]['amount' . $key] = $amount;
                    $result[$myKey]['ltv_reg' . $key] = array_sum($registers[$myKey]);
                    $result[$myKey]['real_consumption' . $key] = array_sum($consumptions[$myKey]);
                }
                $result[$myKey]['total_amount'] = array_sum($val1['ltv']);
            }
        }
        return $result;
    }


    //活动留存
    public static function getRetentionData($params) {
        $retentionFields = self::$retentionFields;
        $title = [
            'app_id' => '应用',
            'is_natural' => '用户类型',
            'consumer_key' => '应用',
            'activity_id' => '活动',
            'activity_gid' => '活动组',
            'channel_id' => '渠道',
            'channel_gid' => '渠道组',
            'android_channel_no' => '安卓渠道号',
            'months' => '月份',
            'create_date' => '日期',
            'action_date' => '日期',
            'days' => '日期',
            'staff' => '投放师',
            'registers' => '新增用户数'
        ];
        $dims = $params['dims'] ?: [];
        if (!in_array('days', $params['dims'])) {
            $params['dims'][] = 'days';
        }
        $mydims = $params['dims'];
        $registers = self::getNewUser($params);
        $where = self::buildWhere($params);
        foreach ($mydims as $key => $dim) {
            if (isset($ltvFields[$dim])) {
                $mydimsFields[] = $ltvFields[$dim];
            }
        }
        $kpis = ['user_counts', 'days', 'action_date', 'rday'];

        if (!empty($mydims)) {
            $orderBy = 'create_date desc';
        } else {
            $orderBy = NULL;
        }
        $where['create_date'] = $where['days'];
        unset($where['days']);
        $groupBy = 'GROUP BY ' . implode(',', array_unique(array_merge($mydims ?: [], ['create_date', 'rday'])));
        $fields = [];
        foreach (array_merge($dims, $kpis) as $item) {
            if (array_key_exists($item, $retentionFields)) {
                $fields[$item] = $retentionFields[$item];
            }
        }
        $strField = self::buildFields($fields);

        if (isset($params['type'])) {
            $mydao = "RainbowChannelRetention";
            $myRetentionDays = self::$channelRetentionDays;
            if (!isset($where['channel_gid'])) {
                $where['channel_gid'] = array('>', 0);
            }
        } else {
            $mydao = "RainbowRetention";
            $myRetentionDays = self::$retentionDays;
        }

        //        $where['rday'] = array('in', $myRetentionDays);
        $list = self::getSpmDao($mydao)->getData($strField, $where, $groupBy, $orderBy);
        foreach ($list as $key => $item) {
            $rday = $item['rday'];
            $myKey = '0';
            foreach ($mydims as $dim) {
                $myKey .= '_' . $item[$dim];
            }
            if (empty($result[$myKey])) {
                foreach ($dims as $dim) {
                    $result[$myKey][$dim] = $item[$dim];
                }
            }
            $result[$myKey]['registers'] = $registers[$myKey];
            $result[$myKey]['retention'][$rday] += $item['user_counts'];
        }

        //        判断是否带日期维度
        if (!in_array('days', $dims)) {
            $result2 = [];
            foreach ($result as $a_key => $myval) {
                $myKey = '0';
                foreach ($dims as $dim) {
                    $myKey .= '_' . $myval[$dim];
                }
                if (empty($result2[$myKey])) {
                    foreach ($dims as $dim) {
                        $result2[$myKey][$dim] = $myval[$dim];
                    }
                }
                $retentions = $myval['retention'];
                $result2[$myKey]['registers'][] = $myval['registers'];
                foreach ($retentions as $rday => $count) {
                    $result2[$myKey]['retention'][$rday] [] = $myval['retention'][$rday];
                }
            }
            $result = [];
            foreach ($result2 as $myKey => $myval) {
                $retentions = $myval['retention'];
                $registers = $myval['registers'];
                unset($myval['retention']);
                unset($myval['registers']);
                $result[$myKey] = $myval;
                foreach ($retentions as $rday => $counts) {
                    if ($rday > 0) {
                        $regs = 0;
                        foreach ($counts as $i => $count) {
                            if (isset($registers[$i])) {
                                $regs += $registers[$i];
                            }
                        }
                        $result[$myKey]['retention'][$rday] = $regs > 0 ? round(array_sum($counts) / $regs * 100, 2) : '-';
                    }

                }
                $result[$myKey]['retention'] = array_values($result[$myKey]['retention']);
                $result[$myKey]['registers'] = array_sum($registers);
            }
        } else {
            foreach ($result as $myKey => $val1) {
                $retentions = $val1['retention'];
                $myRetentions = [];
                if (isset($registers[$myKey])) {
                    $result[$myKey]['registers'] = $registers[$myKey];
                    foreach ($myRetentionDays as $i => $rday) {
                        $myRetentions[$i] = !empty($registers[$myKey]) ? round($retentions[$rday] / $registers[$myKey] * 100, 2) : '-';
                    }
                }
                $result[$myKey]['retention'] = $myRetentions;
            }
        }


        $cols = [];
        $ex_kpis = ['registers'];
        if (!empty($list)) {
            foreach (array_merge($dims, $ex_kpis) as $dim) {
                $col = [
                    'field' => $dim,
                    'title' => $title[$dim],
                    'width' => 120,
                    'fixed' => 'left'
                ];
                $cols[] = $col;
            }
            foreach ($myRetentionDays as $i => $day) {
                $col = [
                    'field' => 'retention' . $i,
                    'title' => '第' . $day . '天',
                    'width' => 100,
                    'sort' => true
                ];
                $cols[] = $col;
            }
        }


        $data['ex_kpis'] = $ex_kpis;
        $data['dims'] = $dims;
        $data['data'] = array_values($result);
        $data['cols'] = $cols;
        $data['title'] = '留存报表';
        $data['days'] = count($myRetentionDays) - 1;
        return $data;
    }


    //活动留存
    public static function getRetentionReportData($params) {
        $retentionFields = self::$retentionFields;
        $retentionRange = [1, 2, 3, 7, 14, 30];
        $dims = $params['dims'] ?: [];
        if (!in_array('days', $params['dims'])) {
            $params['dims'][] = 'days';
        }
        $mydims = $params['dims'];
        $registers = self::getNewUser($params);
        $where = self::buildWhere($params);
        foreach ($mydims as $key => $dim) {
            if (isset($ltvFields[$dim])) {
                $mydimsFields[] = $ltvFields[$dim];
            }
        }
        $kpis = ['user_counts', 'days', 'action_date', 'rday'];

        if (!empty($mydimsFields)) {
            $orderBy = [];
            foreach ($mydimsFields as $dkey => $dimField) {
                array_push($orderBy, $dimField . ' asc');
            }
            $orderBy = implode(',', $orderBy);
        } else {
            $orderBy = NULL;
        }
        $where['create_date'] = $where['days'];
        unset($where['days']);
        $groupBy = 'GROUP BY ' . implode(',', array_unique(array_merge($mydims ?: [], ['create_date', 'rday'])));
        $fields = [];
        foreach (array_merge($dims, $kpis) as $item) {
            if (array_key_exists($item, $retentionFields)) {
                $fields[$item] = $retentionFields[$item];
            }
        }
        $strField = self::buildFields($fields);

        if (isset($params['type'])) {
            $mydao = "RainbowChannelRetention";
            $myRetentionDays = self::$channelRetentionDays;
            if (!isset($where['channel_gid'])) {
                $where['channel_gid'] = array('>', 0);
            }
        } else {
            $mydao = "RainbowRetention";
            $myRetentionDays = self::$retentionDays;
        }

        $where['rday'] = array('in', $myRetentionDays);
        $list = self::getSpmDao($mydao)->getData($strField, $where, $groupBy, $orderBy);
        foreach ($list as $key => $item) {
            $rday = $item['rday'];
            $myKey = '0';
            foreach ($mydims as $dim) {
                $myKey .= '_' . $item[$dim];
            }
            if (empty($result[$myKey])) {
                foreach ($dims as $dim) {
                    $result[$myKey][$dim] = $item[$dim];
                }
            }
            $result[$myKey]['regs'] = $registers[$myKey];
            $result[$myKey]['retention'][$rday] += $item['user_counts'];
        }

        //        判断是否带日期维度
        if (!in_array('days', $dims)) {
            $result2 = [];
            foreach ($result as $a_key => $myval) {
                $myKey = '0';
                foreach ($dims as $dim) {
                    $myKey .= '_' . $myval[$dim];
                }
                if (empty($result2[$myKey])) {
                    foreach ($dims as $dim) {
                        $result2[$myKey][$dim] = $myval[$dim];
                    }
                }

                foreach ($retentionRange as $rday) {
                    if (isset($myval['retention'][$rday])) {
                        $result2[$myKey]['retention_stay' . $rday] += $myval['retention'][$rday];
                        $result2[$myKey]['retention_reg' . $rday] += $myval['regs'];
                    }
                }
            }
            $result = $result2;

        } else {
            foreach ($result as $myKey => $val1) {
                $retentions = $val1['retention'];
                if (isset($registers[$myKey]) and $registers[$myKey] > 0) {
                    foreach ($retentions as $rday => $stay) {
                        if (in_array($rday, $retentionRange)) {
                            $result[$myKey]['retention_stay' . $rday] = $retentions[$rday];
                            $result[$myKey]['retention_reg' . $rday] = $registers[$myKey];
                        }
                    }
                }
                unset($result[$myKey]['retention']);
            }
        }

        return $result;
    }


    /////////////////Base///////////////////////
    protected static function getLtvNewUser($params) {
        $dims = $params['dims'];
        if (isset($params['dims'])) {
            if (is_array($params['dims']) and !in_array('days', $params['dims'])) {
                $params['dims'][] = 'days';
            }
        } else {
            $params['dims'] = ['days'];
        }
        $params['kpis'] = ['registers'];
        //        $params['activity_id'] = array('!=', 0);

        if (isset($params['type']) and $params['type'] = 'channel') {
            $list = self::getRainbowChannelReportData($params);
        } else {
            $list = self::getRainbowReportData($params);
        }
        $startTime = intval(strtotime($params['sdate']) / 86400);
        $endTime = intval(strtotime($params['edate']) / 86400);
        $regs = [];
        $initRegs = [];
        for ($i = 0; $i < $endTime - $endTime; $i++) {
            $initRegs[$i] = 0;
        }
        foreach ($list as $key => $item) {
            $myKey = '0';
            if (!empty($dims)) {
                foreach ($dims as $dim) {
                    $myKey .= '_' . $item[$dim];
                }
            }
            if (!isset($regs[$myKey])) {
                $regs[$myKey] = $initRegs;
            }
            $days = intval(strtotime($item['days']) / 86400) - $startTime;
            $regs[$myKey][$days] = intval($item['registers']);
        }


        return $regs;
    }

    // 每天实际消耗
    protected static function getRealConsumption($params) {
        $dims = $params['dims'];
        if (isset($params['dims'])) {
            if (is_array($params['dims']) and !in_array('days', $params['dims'])) {
                $params['dims'][] = 'days';
            }
        } else {
            $params['dims'] = ['days'];
        }

        $params['kpis'] = ['real_consumption'];
        $list = self::getMonitorReportData($params);
        $startTime = intval(strtotime($params['sdate']) / 86400);
        $endTime = intval(strtotime($params['edate']) / 86400);
        $consumptions = [];
        $initConsumptions = [];
        for ($i = 0; $i < $endTime - $endTime; $i++) {
            $initRegs[$i] = 0;
        }
        foreach ($list as $key => $item) {
            $myKey = '0';
            if (!empty($dims)) {
                foreach ($dims as $dim) {
                    $myKey .= '_' . $item[$dim];
                }
            }
            if (!isset($regs[$myKey])) {
                $regs[$myKey] = $initConsumptions;
            }
            $days = intval(strtotime($item['days']) / 86400) - $startTime;
            $consumptions[$myKey][$days] = intval($item['real_consumption']);
        }
        return $consumptions;
    }


    // 获取广告计划

    protected static function getMonitorDeliveryPlanDayData($params) {
        if (array_intersect($params['dims'], [
            'staff',
            'activity_id',
            'activity_gid',
            'channel_id',
            'android_channel_no'
        ])) {
            return [];
        }

        $mydims = array_merge(array_keys(self::$baseDims));
        $mykpis = array_intersect(array_merge(array_keys(self::$baseKpis), self::$extKpis), self::$kpiFields['MonitorDeliveryPlanDay']);
        $mydims[] = 'months';
        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);
        $allFields = self::getFields();
        $allFields['channel_gid'] = 'channel_group_id';
        $allFields['months'] = 'DATE_FORMAT(days,"%Y%m")';
        $allFields['is_natural'] = 0;

        $groups = [];
        foreach ($dims as $item) {
            if (isset($item, $allFields)) {
                $groups[$item] = is_string($allFields[$item]) ? $allFields[$item] : $item;
            }
        }
        $groupBy = empty($dims) ? null : 'GROUP BY ' . implode(',', $groups);

        $fields = [];
        foreach (array_merge($dims, $kpis) as $item) {
            if (isset($item, $allFields)) {
                $fields[$item] = $allFields[$item];
            }
        }

        // 日期倒序
        if (!empty($params['order'])) {
            $orderBy = $params['order'];
        } else {
            $orderBy = NULL;
        }
        $where = self::buildWhere($params);
        $strField = self::buildFields($fields);

        unset($where['is_natural']);
        $ret = self::getSpmDao("MonitorDeliveryPlanDay")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;
    }


    // 获取投放师计划

    protected static function getMonitorStaffPlanDayData($params) {
        if (array_intersect($params['dims'], [
            'activity_id',
            'activity_gid',
            'channel_id',
            'channel_gid',
            'android_channel_no',
        ])) {
            return [];
        }

        $mydims = array_merge(array_keys(self::$baseDims));
        $mykpis = array_intersect(array_merge(array_keys(self::$baseKpis), self::$extKpis), self::$kpiFields['MonitorDeliveryPlanDay']);

        $dims = array_intersect($params['dims'], $mydims);
        $kpis = array_intersect($params['kpis'], $mykpis);
        $allFields = self::getFields();
        $allFields['is_natural'] = 0;

        $groups = [];
        foreach ($dims as $item) {
            if (isset($item, $allFields)) {
                $groups[$item] = is_string($allFields[$item]) ? $allFields[$item] : $item;
            }
        }
        $groupBy = empty($dims) ? null : 'GROUP BY ' . implode(',', $groups);

        $fields = [];
        foreach (array_merge($dims, $kpis) as $item) {
            if (isset($item, $allFields)) {
                $fields[$item] = $allFields[$item];
            }
        }

        // 日期倒序
        if (!empty($params['order'])) {
            $orderBy = $params['order'];
        } else {
            $orderBy = NULL;
        }
        $where = self::buildWhere($params);
        $strField = self::buildFields($fields);

        if (isset($where['is_natural'])) {
            unset($where['is_natural']);
        }


        $ret = self::getSpmDao("MonitorStaffPlanDay")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;
    }


    protected static function getNewUser($params) {
        $dims = $params['dims'] ?: [];
        $params['kpis'] = ['registers'];

        if (isset($params['type']) and $params['type'] = 'channel') {
            $list = self::getRainbowChannelReportData($params);
        } else {
            $list = self::getRainbowReportData($params);
        }
        $regs = [];
        foreach ($list as $key => $item) {
            $myKey = '0';
            if (!empty($dims)) {
                foreach ($dims as $dim) {
                    $myKey .= '_' . $item[$dim];
                }
            }
            $regs[$myKey] = intval($item['registers']);
        }
        return $regs;
    }


    public static function getLtvChannelNewUser($params, $dims) {
        $where['sdate'] = $params['sdate'];
        $where['edate'] = $params['edate'];
        $where['dims'] = array_merge($dims, ['days']);
        $where['kpis'] = ['registers'];
        $list = self::getRainbowChannelReportData($where);
        return $list;
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
        $where['app_id'] = ['!=', 1];
        $result = self::getSpmDao("MonitorApp")->getFields('app_id,app_name', $where);
        return empty($result) ? [] : $result;
    }

    public static function getStaffMap() {
        $userType = Admin_Service_UserModel::SPM_USER;
        $where = [];
        $where['user_type'] = $userType;
        $result = self::getAdminDao("User")->getFields('user_name,user_name', $where);
        //        $result['unknown'] = 'unknown';
        return $result;
    }


    public static function getActivityMap($operator = null, $appId = 0) {
        $where = [];
        if (!empty($operator) and Yaf_Registry::get('is_admin') != true) {
            $where['operator'] = $operator;
        }
        if ($appId) {
            $where['app_id'] = $appId;
        }


        $result = self::getSpmDao("MonitorActivity")->getFields('id,name', $where);
        $result[0] = '自然量';
        return empty($result) ? [] : $result;
    }

    public static function getActivityGroup($operator = null, $appId = 0) {
        $where = [];
        if (!empty($operator) and Yaf_Registry::get('is_admin') != true) {
            $where['operator'] = $operator;
        }
        $where['app_id'] = $appId;
        $result = self::getSpmDao("MonitorActivity")->getFields('id,group_id', $where);
        $result[0] = '自然量';
        return empty($result) ? [] : $result;
    }

    public static function getActivityGroupMap($groupId = [], $app_id = 0) {
        $where = [];
        if (!empty($groupId)) {
            $where['id'] = array('in', array_unique($groupId));
        }
        $where['app_id'] = $app_id;
        $result = self::getSpmDao("MonitorActivityGroup")->getFields('id,name', $where);
        $result[0] = '自然量';
        return empty($result) ? [] : $result;
    }

    public static function getChannelMap($appId) {
        $where = [];
        if (!empty($appId)) {
            //            $where['app_id'] = array(is_array($appId) ? 'in' : '=', $appId);
        }
        $result = self::getSpmDao("MonitorChannel")->getFields('id,channel_name', $where);
        $result[0] = '自然量';
        return empty($result) ? [] : $result;
    }

    public static function getChannelGroupMap() {
        $result = self::getSpmDao("MonitorChannelGroup")->getFields('id,name');
        $result[0] = '自然量';
        return empty($result) ? [] : $result;
    }

    public static function getOsMap() {
        //        $result = self::getSpmDao("MonitorApp")->getFields('app_id,app_name');
        $map = self::getSpmDao("MonitorApp")->getFields('app_id,platform');
        $result = [];
        foreach ($map as $appid => $os) {
            $result[$os][] = $appid;
        }
        return $result;
    }

    public static function getAppOs($appId) {
        $result = self::getSpmDao("MonitorApp")->get($appId);
        if ($result) {
            return $result['platform'];
        }
        return;
    }

    public static function getAndroidChannelMap($appId) {
        $where = [];
        if (!empty($appId)) {
            //            $where['app_id'] = array(is_array($appId) ? 'in' : '=', $appId);
        }
        $result = self::getSpmDao("MonitorAndroidChannel")->getFields('channel_no,channel_name', $where);
        return empty($result) ? [] : $result;
    }

    public static function getAndroidChannelGroupMap() {
        $result = self::getSpmDao("MonitorAndroidChannelGroup")->getFields('id,name');
        return empty($result) ? [] : $result;
    }


    public static function getRetentionList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = Common::getDao('MobgiSpm_Dao_RainbowRetentionModel');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function getChannelRetentionList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = Common::getDao('MobgiSpm_Dao_RainbowChannelRetentionModel');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatRetentionList($retentionList, $params) {
        $defaultField = ['app_id', 'activity_id', 'activity_gid', 'create_date'];
        $paramsKey = array_keys($params);
        $appMap = self::getAppMap();
        $keyField = array_intersect($defaultField, $paramsKey);
        $data = array();
        $index = '';
        foreach ($retentionList as $key => $val) {
            foreach ($keyField as $indexItem) {
                $index .= $val[$indexItem];
            }
            if (array_key_exists($index, $data)) {
                $data[$index][$val['rday']] += $val['user_counts'];
            } else {
                $data[$index]['app_id'] = array_key_exists($val['app_id'], $appMap) ? $appMap[$val['app_id']] : '未知应用';
                $data[$index]['create_date'] = $val['create_date'];
                $data[$index][$val['rday']] = $val['user_counts'];
            }
            $index = '';
        }
        return $data;
    }

    public static function formatChannelRetentionList($retentionList, $params) {
        $defaultField = ['app_id', 'channel_id', 'channel_gid', 'create_date'];
        $paramsKey = array_keys($params);
        $appMap = self::getAppMap();
        $keyField = array_intersect($defaultField, $paramsKey);
        $data = array();
        $index = '';
        foreach ($retentionList as $key => $val) {
            foreach ($keyField as $indexItem) {
                $index .= $val[$indexItem];
            }
            if (array_key_exists($index, $data)) {
                $data[$index][$val['rday']] += $val['user_counts'];
            } else {
                $data[$index]['app_id'] = array_key_exists($val['app_id'], $appMap) ? $appMap[$val['app_id']] : '未知应用';
                $data[$index]['create_date'] = $val['create_date'];
                $data[$index][$val['rday']] = $val['user_counts'];
            }
            $index = '';
        }
        return $data;
    }
}
