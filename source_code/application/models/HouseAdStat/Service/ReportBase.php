<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author atom.zhan
 *
 */
class HouseAdStat_Service_ReportBaseModel {

    private static $default_dim_base = array(
        'date',
        'hour',
        'ad_id',
        'ad_type',
        'ad_sub_type',
        'platform',
        'originality_id',
        'originality_type',
        'account_id',
        'unit_id',
        'app_key',
        'pos_key'
    );
    private static $defaultDimUser = array("app_key", "date");
    private static $defaultDimReplace = array('app_key', 'ads_id', 'platform', 'pos_key', 'channel_id', 'country', 'area', 'intergration_type');
    private static $defaultSelectedKpi = array(
        'view',
        'click',
        'dau_user',
        'impressions_third_clicks_rate',
        'third_ecpm',
        'impressions_third_per_person',
        'arpu'
    );
    public static $kpiConf = [
        "ad_id" => ["name" => "广告"],
        "unit_id" => ["name" => "投放单元"],
        "originality_type" => ["name" => "创意类型", 'alias' => 'originality_type_name'],
        "account_id" => ["name" => "账号"],
        "originality_id" => ["name" => "创意"],
        "pos_key" => ["name" => "广告位"],
        "app_key" => ["name" => "应用", 'alias' => 'app_name'],
        "ad_type" => ["name" => "广告类型"],
        "ad_sub_type" => ["name" => "广告子类型"],
        "platform" => ["name" => "平台"],
        "date" => ["name" => "日期"],
        "hour" => ["name" => "小时"],
        "request" => ["name" => "请求配置"],
        "request_ok" => ["name" => "请求配置成功"],
        "download" => ["name" => "下载资源次数"],
        "download_ok" => ["name" => "下载资源成功"],
        "view" => ["name" => "展示量"],
        "click" => ["name" => "点击量"],
        "close" => ["name" => "关闭"],
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
    public static $filterFields = [
        'date',
        'hour',
        'view',
        'click',
        'ad_id',
        'ad_type',
        'platform',
        'originality_id',
        'originality_type',
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
     * 获取之前日期的结果集
     * @param unknown $params
     * @return boolean|unknown
     */
    public static function getData($params, $accountId = null) {
        // 用户权限应用
        $appPermit = is_null($accountId) ? [] : [
            'account_id' => $accountId
        ];
        $data = self::getReportData($params, $appPermit);
        $data = self::exchangeReportData($data, $params['dims']);
        $result = self::expandReportData($data);
        return $result;
    }

    /**
     * 获取结果集
     * @param $params
     * @return array|mixed
     */

    public static function getSampleData($params) {
        $appPermit = [];// 用户权限应用
        $data = self::getReportData($params);
        return $data;
    }

//    获取汇总数据
    public static function getTotal($table) {
        $sumField = [
            'request',
            'request_ok',
            'download',
            'download_ok',
            'view',
            'click',
            'close',
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
        if (isset($table[0]['date'])) {
            $result['date'] = "汇总";
        } elseif (isset($table[0]['hour'])) {
            $result['hour'] = "汇总";
        }

        $result = self::expandReportData([$result]);
        return $result[0];
    }


//    数据扩展
    private static function expandReportData($data) {
        $expandFields = [
            'avg_price' => ['amount', 'click', 1, 2],
            'ecpm' => ['amount', 'view', 1000, 2],
            'click_rate' => ['click', 'view', 100, 2],
            'skips_time' => ['used_time', 'skips', 0.001, 2]
        ];
        foreach ($data as $key => $val) {
            foreach ($expandFields as $field => $conf) {
                if ($val[$conf[1]] > 0) {
                    $data[$key][$field] = round($val[$conf[0]] / $val[$conf[1]] * $conf[2], $conf[3]);
                } else {
                    $data[$key][$field] = 0;
                }

            }
        }
        return $data;
    }

//    数据转换
    private static function exchangeReportData($items, $dims = []) {
        if (empty($items)) return [];
        if (!is_array($dims)) {
            $dims = array($dims);
        }
        foreach ($items as $key => $item) {
            empty($item['unit_id']) or $unitId[$item['unit_id']] = $item['unit_id'];
            empty($item['ad_id']) or $adId[$item['ad_id']] = $item['ad_id'];
            empty($item['originality_id']) or $originalityId[$item['originality_id']] = $item['originality_id'];
            empty($item['app_key']) or $appkey[$item['app_key']] = $item['app_key'];
            empty($item['account_id']) or $accountId[$item['account_id']] = $item['account_id'];
            empty($item['pos_key']) or $blockId[$item['pos_key']] = $item['pos_key'];
        }
        in_array('ad_id', $dims) and $adId = Dedelivery_Service_AdConfListModel::getFields('id,ad_name', empty($unitId) ? null : array(
            'id' => array(
                'in',
                $adId
            )
        ));
        in_array('unit_id', $dims) and $unitId = Dedelivery_Service_UnitConfModel::getFields('id,name', empty($unitId) ? null : array(
            'id' => array(
                'in',
                $unitId
            )
        ));
        in_array('originality_id', $dims) and $originalityId = Dedelivery_Service_OriginalityRelationModel::getFields('id,title', empty($originalityId) ? null : array(
            'id' => array('in', $originalityId)
        ));
        in_array('account_id', $dims) and $accountId = Admin_Service_UserModel::getFields('user_id,user_name', empty($accountId) ? null : array(
            'advertiser_uid' => array('in', $accountId)
        ));
        in_array('app_key', $dims) and $appkey = MobgiApi_Service_AdAppModel::getFields('appkey,app_name', empty($appkey) ? null : array(
            'appkey' => array(
                'in',
                $appkey
            )
        ));
        in_array('pos_key', $dims) and $blockId = Advertiser_Service_OriginalityRelationPositionModel::getFields('ad_position_key,ad_position_name', empty($blockId) ? null : array(
            'ad_position_key' => array(
                'in',
                $blockId
            )
        ));

        $adType = Common::getConfig('deliveryConfig', 'originalityType');
        $adSubTypeWithAdType = Common::getConfig('deliveryConfig', 'adSubType');
        $platform = Common::getConfig('deliveryConfig', 'osTypeList');
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
            isset($appkey[$item['app_key']]) and $items[$key]['app_key'] = $appkey[$item['app_key']];
            isset($platform[$item['platform']]) and $items[$key]['platform'] = $platform[$item['platform']];
            isset($accountId[$item['account_id']]) and $items[$key]['account_id'] = $accountId[$item['account_id']];
            isset($blockId[$item['pos_key']]) and $items[$key]['pos_key'] = $blockId[$item['pos_key']];
        }
        return $items;

    }

    private static function getReportData($params, $appPermit = []) {
        $dims = isset($params['dims']) ? $params['dims'] : array();
        // 维度次序根据传参确定

        if (!is_array($dims)) {
            $dims = array($dims);
        }


        $dims = array_intersect($dims, self::$default_dim_base);
        $fields = [
            'sum(`request`) as `request`',
            'sum(`request_ok`) as `request_ok`',
            'sum(`download`) as `download`',
            'sum(`download_ok`) as `download_ok`',
            'sum(`view`) as `view`',
            'sum(`click`) as `click`',
            'sum(`close`) as `close`',
            'sum(`reward`) as `reward`',
            'sum(`resume`) as `resume`',
            'sum(`redirect_browser`) as `redirect_browser`',
            'sum(`redirect_Internal_browser`) as `redirect_Internal_browser`',
            'sum(`redirect_shop`) as `redirect_shop`',
            'sum(`download_app`) as `download_app`',
            'sum(`download_app_ok`) as `download_app_ok`',
            'sum(`install_app`) as `install_app`',
            'sum(`install_app_ok`) as `install_app_ok`',
            'sum(amount) as amount',
            'sum(skips) as skips',
            'sum(used_time) as used_time'
        ];
        //        $fields
        foreach ($dims as $dim) {
            array_push($fields, $dim);
        }
        $groupby = empty($dims) ? null : 'GROUP BY ' . implode(',', $dims);
        // 日期倒序
        if (!empty($dims)) {
            $orderby = array();
            foreach ($dims as $dkey => $dim) {
                array_push($orderby, $dim . ' asc');
            }
            $orderBy = implode(',', $orderby);
        } else {
            $orderBy = NULL;
        }
        $where = self::buildWhere($params, $appPermit);
        $ret = self::_getDao()->getReportData(implode(',', $fields), $where, $groupby, $orderBy);
        return empty($ret) ? [] : $ret;


    }


    private static function buildWhere($params, $appPermit = []) {
        $where = array();
        if (isset($params['app_key'])) {
            $where['app_key'] = is_array($params['app_key']) ? array('in', $params['app_key']) : $params['app_key'];
        }
        if (isset($params['sdate']) and isset($params['edate'])) {
//            $where['date'] = array('between', array($params['sdate'], $params['edate']));
            $where['date'] = array(array('>=', $params['sdate']), array('<=', $params['edate']));
        } else {
            $where['date'] = array('=', date("Y-m-d", strtotime("-1 day")));
        }
        foreach (array(
                     'ad_id',
                     'ad_type',
                     'platform',
                     'originality_id',
                     'originality_type',
                     'account_id',
                     'unit_id',
                     'app_key',
                     'pos_key'
                 ) as $val) {
            if (isset($params[$val])) {
                $where[$val] = is_array($params[$val]) ? array('in', $params[$val]) : $params[$val];
            }
        }
        $where = array_merge($where, $appPermit);
        return $where;
    }


    /**
     * 获取之前日期的结果集
     * @param unknown $params
     * @return boolean|unknown
     */
    public static function getPreDaysData($params) {
        $ret = self::_getDao()->getPreDaysTotal($params);
        if (!$ret) return false;
        return $ret;


    }

    /**
     *
     * Enter description here ...
     */
    public static function getAll() {
        return array(self::_getDao()->count(), self::_getDao()->getAll());
    }


    /**
     *
     * Enter description here ...
     * @param unknown_type $params
     * @param unknown_type $page
     * @param unknown_type $limit
     */
    public static function getList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $ret = self::_getDao()->getList($start, $limit, $params, $orderBy);
        $total = self::_getDao()->count($params);
        return array($total, $ret);
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $id
     */
    public static function getByID($id) {
        if (!intval($id)) return false;
        return self::_getDao()->get(intval($id));
    }


    /**
     *
     * @param unknown_type $page
     * @param unknown_type $limit
     * @param unknown_type $params
     * @return multitype:unknown multitype:
     */

    public static function getBy($params = array(), $orderBy = array('id' => 'DESC')) {
        $ret = self::_getDao()->getBy($params, $orderBy);
        if (!$ret) return false;
        return $ret;

    }

    /**
     *
     * @param unknown_type $page
     * @param unknown_type $limit
     * @param unknown_type $params
     * @return multitype:unknown multitype:
     */

    public static function getsBy($params = array(), $orderBy = array('id' => 'DESC')) {
        $ret = self::_getDao()->getsBy($params, $orderBy);
        if (!$ret) return false;
        return $ret;

    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $data
     * @param unknown_type $id
     */
    public static function updateByID($data, $id) {
        if (!is_array($data)) return false;
        $data = self::_cookData($data);
        return self::_getDao()->update($data, intval($id));
    }

    public static function updateBy($data, $params) {
        if (!is_array($data) || !is_array($params)) return false;
        $data = self::_cookData($data);
        return self::_getDao()->updateBy($data, $params);
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $id
     */
    public static function deleteById($id) {
        return self::_getDao()->delete(intval($id));
    }


    public static function deleteBy($params) {
        return self::_getDao()->deleteBy($params);
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $data
     */
    public static function add($data) {
        if (!is_array($data)) return false;
        $data = self::_cookData($data);
        $ret = self::_getDao()->insert($data);
        if (!$ret) return $ret;
        return self::_getDao()->getLastInsertId();
    }


    /**
     *
     * Enter description here ...
     * @param unknown_type $data
     */
    private static function _cookData($data) {
        $tmp = array();
        if (isset($data['ad_unit_id'])) $tmp['ad_unit_id'] = intval($data['ad_unit_id']);
        if (isset($data['ad_id'])) $tmp['ad_id'] = intval($data['ad_id']);
        if (isset($data['originality_id'])) $tmp['originality_id'] = $data['originality_id'];
        if (isset($data['day'])) $tmp['day'] = $data['day'];
        if (isset($data['clicks'])) $tmp['clicks'] = $data['clicks'];
        if (isset($data['views'])) $tmp['views'] = $data['views'];
        if (isset($data['amount'])) $tmp['amount'] = $data['amount'];
        if (isset($data['dau'])) $tmp['dau'] = $data['dau'];
        return $tmp;
    }

    /**
     *
     * @return HouseAdStat_Dao_StatDayModel
     */
    private static function _getDao() {
        return Common::getDao("HouseAdStat_Dao_ReportBaseModel");
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
        $OriginalityIds = Dedelivery_Service_OriginalityRelationModel::getFieldWithAccount($paramAccount, 'originality_type');
//        $origAppkeyRelation = empty($OriginalityIds) ? [] : Advertiser_Service_OriginalityRelationPositionModel::getRelationOfAppkey([
//            'originality_type' => ['in', $OriginalityIds]
//        ]);
        $origAppkeyRelation = empty($relations['originality_type']) ? [] : MobgiApi_Service_AdAppModel::getAppKey([
            'is_check' => MobgiApi_Service_AdAppModel::ISCHECK_PASS
        ]);


        return array_merge($accountRelation, $origAppkeyRelation);
    }

    //获取账户关联维度
    public static function getDims($accountId = null) {
        if ($accountId != null) {
            if (empty($accountId)) {
                $dims = [
                    'originality_id' => [],
                    'originality_type' => [],
                    'app_key' => [],
                    'pos_key' => [],
                    'platform' => [],
                    'unit_id' => [],
                    'ad_id' => [],
                ];
                return $dims;
            }
            $paramAccount = is_array($accountId) ? array('account_id' => array('in', $accountId)) : array('account_id' => $accountId);
            $dimOfAccountId = [];
        } else {
            $paramAccount = [];
            $dimOfAccountId = Admin_Service_UserModel::getAcount();
        }

        $relations = Dedelivery_Service_OriginalityRelationModel::getFieldWithAccount($paramAccount, ['originality_id', 'originality_type']);
        $dimOfOriginalityId = empty($relations['originality_id']) ? [] : Dedelivery_Service_OriginalityRelationModel::getFields('id,title', [
            'id' => array(
                'in',
                $relations['originality_id']
            )
        ]);
        $dimOfOriginalityType = Advertiser_Service_OriginalityConfModel::getOriginalityType();

        $dimOfAdSubType = Advertiser_Service_OriginalityConfModel::getAdSubType();
        $dimOfAppKey = empty($relations['originality_type']) ? [] : MobgiApi_Service_AdAppModel::getAppKey([
            'is_check' => MobgiApi_Service_AdAppModel::ISCHECK_PASS
        ]);
        $dimOfBlockId = empty($relations['originality_type']) ? [] : MobgiApi_Service_AdDeverPosModel::getBlockId([
            'del' => MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG
        ]);

        $dimOfUnitId = Dedelivery_Service_UnitConfModel::getUnitId($paramAccount);
        $dimOfPlatform = Common::getConfig('deliveryConfig', 'osTypeList');
        $dimOfAdId = Dedelivery_Service_AdConfListModel::getFields('id,ad_name');
        $dims = [
            'originality_id' => $dimOfOriginalityId,
            'ad_type' => $dimOfOriginalityType,
            'ad_sub_type' => $dimOfAdSubType,
            'app_key' => $dimOfAppKey,
            'pos_key' => $dimOfBlockId,
            'platform' => $dimOfPlatform,
            'unit_id' => $dimOfUnitId,
            'account_id' => $dimOfAccountId,
            'ad_id' => $dimOfAdId,
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

        $kpiConf = Report_Service_MobgiModel::getByID($accountId);
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
