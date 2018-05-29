<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * 自投数据报表
 * @author atom.zhan
 *
 */
class MobgiData_Service_HouseadModel extends MobgiData_Service_BaseModel {

    private static $baseDims = [
        'days',
        'hours',
        'account_id',
        'unit_id',
        'ad_id',
        'originality_id',
        'ad_type',
        'ad_sub_type',
        'app_key',
        'pos_key',
        'platform',
    ];


    private static $baseKpis = [
        'request',
        'request_ok',
        'download',
        'download_ok',
        'impressions',
        'closes',
        'clicks',
        'skips',
        'play_finish',
        'sdk_impressions',
        'redirect_shop',
        'redirect_browser',
        'redirect_internal_shop',
        'redirect_internal_browser',
        'download_app',
        'download_app_ok',
        'install_app',
        'install_app_ok',
        'amount'
    ];

    private static $defaultDimUser = array("app_key", "days");
    private static $defaultDimReplace = array('app_key', 'ads_id', 'platform', 'pos_key', 'channel_id', 'country', 'area', 'ad_type');
    private static $defaultSelectedKpi = ['impressions', 'clicks', 'dau_user', 'clicks_rate', 'arpu'];
    public static $conf = [
        "ad_id" => ["name" => "广告"],
        "unit_id" => ["name" => "投放单元"],
        "ad_type" => ["name" => "创意类型", 'alias' => 'ad_type_name'],
        "account_id" => ["name" => "账号"],
        "originality_id" => ["name" => "创意"],
        "pos_key" => ["name" => "广告位"],
        "app_key" => ["name" => "应用", 'alias' => 'app_name'],
        "ad_type" => ["name" => "广告类型"],
        "ad_sub_type" => ["name" => "广告子类型"],
        "platform" => ["name" => "平台"],
        "days" => ["name" => "日期"],
        "hours" => ["name" => "小时"],
        "request" => ["name" => "请求配置"],
        "request_ok" => ["name" => "请求配置成功"],
        "download" => ["name" => "下载资源次数"],
        "download_ok" => ["name" => "下载资源成功"],
        "impressions" => ["name" => "展示量"],
        "clicks" => ["name" => "点击量"],
        "closes" => ["name" => "关闭"],
        "reward" => ["name" => "触发奖励(视频)"],
        "resume" => ["name" => "重新观看(视频)"],
        "skips" => ["name" => "跳过次数"],
        "redirect_browser" => ["name" => "跳转浏览器次数"],
        "redirect_internal_browser" => ["name" => "跳转内建浏览器次数"],
        "redirect_shop" => ["name" => "跳转商店次数"],
        "redirect_internal_shop" => ["name" => "跳转商店内页"],
        "download_app" => ["name" => "下载APP次数"],
        "download_app_ok" => ["name" => "下载APP成功次数"],
        "install_app" => ["name" => "安装次数"],
        "install_app_ok" => ["name" => "安装成功次数"],
        "amount" => ["name" => "消费金额"],
        "avg_price" => ["name" => "点击均价"],
        "ecpm" => ["name" => "ECPM"],
        "click_rate" => ["name" => "点击率(%)"],
        "skips_time" => ["name" => "跳过时间均值(s)"],
    ];

    public static $baseFields = [
        'user_id' => 'user_id',
        'unit_id' => 'unit_id',
        'ad_id' => 'ad_id',
        'account_id' => 'account_id',
        'originality_id' => 'originality_id',
        'ad_type' => 'ad_type',
        'ad_sub_type' => 'ad_sub_type',
        'app_key' => 'app_key',
        'pos_key' => 'pos_key',
        'platform' => 'platform',
        'days' => 'days',
        'hours' => 'hours',
        'request' => 'sum(request)',
        'request_ok' => 'sum(request_ok)',
        'download' => 'sum(download)',
        'download_ok' => 'sum(download_ok)',
        'impressions' => 'sum(impressions)',
        'clicks' => 'sum(clicks)',
        'closes' => 'sum(closes)',
        'skips' => 'sum(skips)',
        'amount' => 'sum(amount)',
        'redirect_browser' => 'sum(redirect_browser)',
        'redirect_internal_browser' => 'sum(redirect_internal_browser)',
        'redirect_shop' => 'sum(redirect_shop)',
        'redirect_internal_shop' => 'sum(redirect_internal_shop)',
        'download_app' => 'sum(download_app)',
        'download_app_ok' => 'sum(download_app_ok)',
        'install_app' => 'sum(install_app)',
        'install_app_ok' => 'sum(install_app_ok)',
        'skip_stay_time' => 'sum(skip_stay_time)',

    ];

    public static $expandFields = [
        'click_rate' => ['clicks', 'impressions', 100, 2],
        'ecpm' => ['amount', 'impressions', 1000, 2],
        'avg_price' => ['amount', 'clicks', 1, 2],
        'skips_time' => ['skip_stay_time', 'skips', 1, 2],

    ];

    //    获取过滤字段
    public static function getFilterFields() {
        return self::$baseDims;
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
        'theader'
    ];


    /**
     * 获取报表配置
     * @param unknown $params
     * @return boolean|unknown
     */
    public static function getChartConf($accountId) {
        $conf = [
            'api' => [
                "data" => '/Admin/Data_Report/getHouseadData',
                "conf" => '/Admin/Data_Report/updateHouseadKpi',
            ],
            'box' => [
                "account_id" => [],
                "unit_id" => [],
                "ad_id" => [],
                "originality_id" => [],
                "ad_type" => [],
                "ad_sub_type" => [],
                "app_key" => [],
                "pos_key" => [],
                "platform" => [],
            ],
            'conf' => self::$conf,
            'kpi' => [],
            'dim' => [
                "default_dim_dom" => "#dim",
                "default_dim_fields" => [
                    "days" => "日期",
                    "hours" => "小时",
                    "hr1" => "-",
                    "account_id" => "账号",
                    "unit_id" => "投放单元",
                    "ad_id" => "广告",
                    "originality_id" => "创意",
                    "hr2" => "-",
                    "ad_type" => "创意类型",
                    "ad_sub_type" => "创意子类型",
                    "app_key" => "应用",
                    "pos_key" => "广告位",
                    "platform" => "平台"
                ],
                "default_dim_value" => ["days" => []],
                "dims" => [],
            ],
        ];
        $conf['kpi'] = self::getChartKpis($accountId);
        $conf['dim']['dims'] = self::getChartDims();
        //        $conf['dim']['relations'] = self::getRelations();
        return $conf;
    }

    //   获取指标
    private static function getChartKpis($userId) {
        $defaultConf = [
            "client" => [
                "impressions" => 0,
                "clicks" => 0,
                "closes" => 0,
                "skips" => 0,
                "amount" => 0,
                "reward" => 0,
                "resume" => 0,
                "request" => 0,
                "request_ok" => 0,
                "download" => 0,
                "download_ok" => 0,
                "redirect_browser" => 0,
                "redirect_internal_browser" => 0,
                "redirect_shop" => 0,
                "redirect_internal_shop" => 0,
                "download_app" => 0,
                "download_app_ok" => 0,
                "install_app" => 0,
                "install_app_ok" => 0,
            ],
            "common" => [
                "avg_price" => 0,
                "ecpm" => 0,
                "click_rate" => 0,
                "skips_time" => 0,
            ],
        ];
        $kpiConf = self::getDao('AdminKpis')->getKpis($userId, 'housead');
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
        in_array('account_id', $dims) and $accountId = self::getAccountMap(0);
        in_array('unit_id', $dims) and $unitId = self::getUnitIdMap(0);
        in_array('ad_id', $dims) and $adId = self::getAdIdMap(0);
        in_array('originality_id', $dims) and $origId = self::getOrigIdMap(0);

        $adType = Common_Service_Config::AD_TYPE;
        $adSubType = Common_Service_Config::AD_SUB_TYPE;
        $platform = Common_Service_Config::PLATFORM;

        foreach ($items as $key => $item) {
            isset($item['ad_type']) and isset($adType[$item['ad_type']]) and $items[$key]['ad_type'] = $adType[$item['ad_type']];
            isset($item['ad_sub_type']) and isset($adType[$item['ad_sub_type']]) and $items[$key]['ad_sub_type'] = $adSubType[$item['ad_sub_type']];
            isset($item['app_key']) and isset($appKey[$item['app_key']]) and $items[$key]['app_key'] = $appKey[$item['app_key']];
            isset($item['pos_key']) and isset($posKey[$item['pos_key']]) and $items[$key]['pos_key'] = $posKey[$item['pos_key']];
            isset($item['platform']) and isset($platform[$item['platform']]) and $items[$key]['platform'] = $platform[$item['platform']];
            isset($item['account_id']) and isset($accountId[$item['account_id']]) and $items[$key]['account_id'] = $accountId[$item['account_id']];
            isset($item['unit_id']) and isset($unitId[$item['unit_id']]) and $items[$key]['unit_id'] = $unitId[$item['unit_id']];
            isset($item['ad_id']) and isset($adId[$item['ad_id']]) and $items[$key]['ad_id'] = $adId[$item['ad_id']];
            isset($item['originality_id']) and isset($origId[$item['originality_id']]) and $items[$key]['originality_id'] = $origId[$item['originality_id']];
        }
        return $items;

    }

    //    数据转换
    private static function replaceReportData2($items, $dims = []) {
        if (empty($items)) return [];
        if (!is_array($dims)) {
            $dims = array($dims);
        }
        foreach ($items as $key => $item) {
            empty($item['unit_id']) or $unitId[$item['unit_id']] = $item['unit_id'];
            empty($item['ad_id']) or $adId[$item['ad_id']] = $item['ad_id'];
            empty($item['originality_id']) or $originalityId[$item['originality_id']] = $item['originality_id'];
            empty($item['app_key']) or $appKey[$item['app_key']] = $item['app_key'];
            empty($item['account_id']) or $accountId[$item['account_id']] = $item['account_id'];
            empty($item['pos_key']) or $posKey[$item['pos_key']] = $item['pos_key'];
        }
        if (in_array('ad_id', $dims)) {
            $where = empty($unitId) ? null : ['id' => ['in', $adId]];
            $adId = Common::getDao('Dedelivery_Dao_AdConfListModel')->getFields('id,ad_name', $where);
        }
        if (in_array('unit_id', $dims)) {
            $where = empty($unitId) ? null : array('id' => ['in', $unitId]);
            $unitId = Common::getDao('Dedelivery_Dao_UnitConfModel')->getFields('id,name', $where);
        }
        if (in_array('originality_id', $dims)) {
            $where = empty($originalityId) ? null : array('id' => ['in', $originalityId]);
            $originalityId = Common::getDao('Dedelivery_Dao_UnitConfModel')->getFields('id,title', $where);
        }
        if (in_array('account_id', $dims)) {
            $where = empty($accountId) ? null : array('id' => ['in', $accountId]);
            $accountId = Common::getDao('Dedelivery_Dao_UnitConfModel')->getFields('advertiser_uid,advertiser_name', $where);
        }

        in_array('app_key', $dims) and $appKey = self::getAppKeyMap($appKey);
        in_array('pos_key', $dims) and $posKey = self::getPosKeyMap($posKey);

        if (in_array('pos_key', $dims)) {
            $where = empty($posKey) ? null : array('pos_key' => ['in', $posKey]);
            $posKey = self::getDao('ConfigPos')->getFields('pos_key,pos_name', $where);
        }

        $adType = Common_Service_Config::AD_TYPE;
        $adSubTypeWithAdType = Common_Service_Config::AD_SUB_TYPE;
        $platform = Common_Service_Config::PLATFORM;
        $adSubType = [];
        foreach ($adSubTypeWithAdType as $adTypes) {
            foreach ($adTypes as $adSubKey => $adSubItems) $adSubType[$adSubKey] = $adSubItems;
        }

        foreach ($items as $key => $item) {
            isset($unitId[$item['unit_id']]) and $items[$key]['unit_id'] = $unitId[$item['unit_id']];
            isset($adId[$item['ad_id']]) and $items[$key]['ad_id'] = $adId[$item['ad_id']];
            isset($originalityId[$item['originality_id']]) and $items[$key]['originality_id'] = $originalityId[$item['originality_id']];
            isset($adType[$item['ad_type']]) and $items[$key]['ad_type'] = $adType[$item['ad_type']];
            isset($adSubType[$item['ad_sub_type']]) and $items[$key]['ad_sub_type'] = $adSubType[$item['ad_sub_type']];
            isset($appKey[$item['app_key']]) and $items[$key]['app_key'] = $appKey[$item['app_key']];
            isset($platform[$item['platform']]) and $items[$key]['platform'] = $platform[$item['platform']];
            isset($accountId[$item['account_id']]) and $items[$key]['account_id'] = $accountId[$item['account_id']];
            isset($posKey[$item['pos_key']]) and $items[$key]['pos_key'] = $posKey[$item['pos_key']];
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
        return array_intersect(self::$baseKpis, array_unique($kpis));
    }

    private static function getReportData($params) {
        $dims = array_intersect(self::$baseDims, $params['dims']);
        $kpis = array_intersect(self::$baseKpis, self::expandKpi($params['theader']));

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
        $ret = self::getDao("ReportHousead")->getData($strField, $where, $groupBy, $orderBy);
        return empty($ret) ? [] : $ret;
    }


    //    获取账户关联
    public static function getRelations($accountId = null) {
        if ($accountId != null) {
            if (empty($accountId)) {
                return [];
            }
            $paramAccount = is_array($accountId) ? array('account_id' => array('in', $accountId)) : array('account_id' => $accountId);
        } else {
            $paramAccount = [];
        }

        //        级联关系:账号==》投放单元==》广告==》创意
        $accountRelation = Dedelivery_Service_OriginalityRelationModel::getRelationOfAccount($paramAccount);
        //        创意类型==》应用
        $OrigIds = Dedelivery_Service_OriginalityRelationModel::getFieldWithAccount($paramAccount, 'ad_type');
        //        $origAppkeyRelation = empty($OrigIds) ? [] : Advertiser_Service_OriginalityRelationPositionModel::getRelationOfAppkey([
        //            'ad_type' => ['in', $OrigIds]
        //        ]);
        $origAppkeyRelation = empty($relations['ad_type']) ? [] : MobgiApi_Service_AdAppModel::getAppKey([
            'is_check' => MobgiApi_Service_AdAppModel::ISCHECK_PASS
        ]);
        return array_merge($accountRelation, $origAppkeyRelation);
    }

    //获取账户关联维度
    public static function getChartDims($userId = null) {
        if ($userId != null) {
            if (empty($userId)) {
                $dims = [
                    'originality_id' => [],
                    'ad_type' => [],
                    'app_key' => [],
                    'pos_key' => [],
                    'platform' => [],
                    'unit_id' => [],
                    'ad_id' => [],
                ];
                return $dims;
            }
            $paramAccount = is_array($userId) ? array('account_id' => array('in', $userId)) : array('account_id' => $userId);
        } else {
            $paramAccount = [];
        }

        //        $relations = Dedelivery_Service_OriginalityRelationModel::getFieldWithAccount($paramAccount, ['originality_id', 'ad_type']);
        $appKeys = self::getUserAppKey($userId);
        $dimOfAccountId = self::getAccountMap(0);
        $dims = [
            'ad_type' => Common_Service_Config::AD_TYPE,
            'ad_sub_type' => Common_Service_Config::AD_SUB_TYPE,
            'platform' => Common_Service_Config::PLATFORM,
            'app_key' => self::getAppKeyMap($appKeys),
            'originality_id' => self::getUserOrigIdMap($userId),
            'unit_id' => self::getUserUnitIdMap($userId),
            'account_id' => $dimOfAccountId,
            'ad_id' => self::getUserAdIdMap($userId),
        ];
        $dims['pos_key'] = self::getAppPosKeyMap(array_keys($dims['app_key']));
        return $dims;

    }

}
