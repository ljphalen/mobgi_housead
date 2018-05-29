<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author atom.zhan
 *
 */
class MobgiData_Service_BaseModel extends Common_Service_Report {
    public static $userId = null;
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
        'block_id'
    );
    private static $defaultDimUser = array("app_key", "date");
    private static $defaultDimReplace = array('app_key', 'ads_id', 'platform', 'block_id', 'channel_id', 'country', 'area', 'intergration_type');
    private static $defaultSelectedKpi = array(
        'view',
        'click',
        'dau_user',
        'impressions_third_clicks_rate',
        'third_ecpm',
        'impressions_third_per_person',
        'arpu'
    );
    public static $conf = [
        "ad_id" => ["name" => "广告"],
        "unit_id" => ["name" => "投放单元"],
        "originality_type" => ["name" => "创意类型", 'alias' => 'originality_type_name'],
        "account_id" => ["name" => "账号"],
        "originality_id" => ["name" => "创意"],
        "block_id" => ["name" => "广告位"],
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
        'block_id',
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
    public static $expandFields = [];

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
        if (!isset($params['permit'])) {
            $params['permit'] = [];
        }
        $dims = array_unique(array_merge($params['dims'], array_keys($params['permit']), ['is_custom']));
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

    //    数据扩展
    protected static function expandReportData($data, $expandFields, $kpis = []) {
        $result = [];
        foreach ($data as $key => $val) {
            foreach ($kpis as $kpi) {
                if (array_key_exists($kpi, $expandFields)) {
                    $val[$kpi] = self::getExpandDate($val, $expandFields, $kpi);
                }
            }
            $result[] = $val;
        }
        return $result;
    }

    protected static function getExpandDate($val, $expandFields, $kpi) {
        $fields = $expandFields[$kpi];
        foreach ($fields as $key => $field) {
            if (is_string($field)) {
                if (isset($val[$field])) {
                    $fields[$key] = $val[$field];
                } else if (array_key_exists($field, $expandFields)) {
                    $fields[$key] = self::getExpandDate($val, $expandFields, $field);
                } else {
                    $fields[$key] = 0;
                }
            }
        }
        if ($fields[1] != 0) {
            $val[$kpi] = round($fields[0] / $fields[1] * $fields[2], $fields[3]);
        } else {
            $val[$kpi] = 0;
        }
        return $val[$kpi];
    }


    /**
     * 获取用户app_key
     * @param int $userId
     * @return array
     */
    protected static function getUserAppKey($userId = 0) {
        $where = [];
        if ($userId > 0 and Yaf_Registry::get('is_admin') != true) {
            $where['user_id'] = $userId;
        }
        $result = Common::getDao("Admin_Dao_UserAppRelModel")->getFields('id,app_key', $where);
        return empty($result) ? ['no_app'] : $result;
    }

    protected static function getUserOrigId($userId = 0) {
        $where = [];
        if ($userId > 0) {
            $where['account_id'] = $userId;
        }
        return Common::getDao("Dedelivery_Dao_OriginalityRelationModel")->getFields('id,id', $where);
    }


    protected static function getUserChannelGidMap($userId = 0) {
        $where = [];
        if ($userId > 0) {
            //            $where['user_id'] = $userId;
        }
        $where['group_id'] = 0;
        //        $where['status'] = 1;
        return self::getDao("ConfigChannels")->getFields('channel_id,channel_name', $where);
    }


    protected static function getUserUnitIdMap($userId = 0) {
        $where = [];
        if ($userId > 0) {
            $where['account_id'] = $userId;
        }
        //        $where['status'] = 1;
        return Common::getDao("Dedelivery_Dao_UnitConfModel")->getFields('id,name', $where);
    }

    protected static function getUserAdIdMap($userId = 0) {
        $where = [];
        if ($userId > 0) {
            $unit = self::getUserUnitIdMap($userId);
            if ($unit) {
                $where['unit_id'] = array_keys($unit);
            }
        }
        $list = Common::getDao("Dedelivery_Dao_AdConfListModel")->getAllByFields('id,unit_id,ad_name', $where);
        $map = [];
        foreach ($list as $item) {
            $map[$item['unit_id']][$item['id']] = $item['ad_name'];
        }
        return $map;
    }

    protected static function getUserOrigIdMap($userId = 0) {
        $where = [];
        if ($userId > 0) {
            $where['account_id'] = $userId;
        }
        $list = Common::getDao("Dedelivery_Dao_OriginalityRelationModel")->getAllByFields('originality_conf_id as id,unit_id,title', $where);
        $map = [];
        foreach ($list as $item) {
            $map[$item['unit_id']][$item['id']] = $item['title'];
        }
        return $map;
    }


    public static function getAccountMap($userId) {
        $where = [];
        if ($userId > 0) {
            $where['user_id'] = $userId;
        } else {
            $where['is_check'] = 1;
        }
        return Common::getDao("Admin_Dao_UserModel")->getFields('user_id,user_name', $where);

    }

    public static function getUnitIdMap($userId = 0) {
        $where = [];
        if ($userId > 0) {
            $where['account_id'] = $userId;
        }
        return Common::getDao("Dedelivery_Dao_UnitConfModel")->getFields('id,name', $where);

    }

    public static function getAdIdMap($userId = 0) {
        $where = [];
        if ($userId > 0) {
            $where['account_id'] = $userId;
        }
        return Common::getDao("Dedelivery_Dao_AdConfListModel")->getFields('id,ad_name', $where);

    }


    public static function getOrigIdMap($userId = 0) {
        $where = [];
        if ($userId > 0) {
            $where['account_id'] = $userId;
        }
        return Common::getDao("Dedelivery_Dao_OriginalityRelationModel")->getFields('id,title', $where);

    }


    /**
     * 获取app_key映射
     * @param $keys
     * @return array
     */
    public static function getAppKeyMap($keys = [], $status = null) {
        if (is_array($keys) and count($keys) > 0) {
            $where['app_key'] = ['in', $keys];
        }
        is_null($status) or $where['status'] = $status;
        $list = self::getDao('ConfigApp')->getAllByFields('app_key,app_name,platform', $where);
        $map = [];
        $platform = array(
            Common_Service_Const::ANDRIOD_PLATFORM => '(A)',
            Common_Service_Const::IOS_PLATFORM => '(I)'
        );
        foreach ($list as $item) {
            $map[$item['app_key']] = $item['app_name'] . $platform[$item['platform']];
        }
        return $map;

    }


    public static function getAdsIdMap() {
        $where['status'] = 1;
        return self::getDao('ConfigAds')->getFields('identifier,identifier', $where);
    }

    public static function getAdsIdMapWithForeign() {
        $where['status'] = 1;
        $adsList = self::getDao('ConfigAds')->getFields('identifier,is_foreign', $where);
        $foreign = [
            0 => '国内',
            1 => '国外',
        ];
        $map = [];
        foreach ($adsList as $key => $item) {
            $map[$foreign[$item]][$key] = $key;
        }
        return $map;

    }

    public static function getCountryMap() {
        $state = Common_Service_Config::COUNTRY;
        $map = [];
        foreach ($state as $countrys) {
            foreach ($countrys as $key => $country) {
                $map[$key] = $country;
            }
        }
        return $map;
    }


    public static function getFlowMap() {
        $where = [];
        $result = self::getApiDao("AbFlowConf")->getFields('flow_id,conf_name', $where);
        return $result;

    }

    public static function getConfMap() {
        $where = [];
        $where['conf_type'] = MobgiApi_Service_AbConfModel::ABTEST_CONF_TYPE;
        $result = self::getApiDao("AbConf")->getFields('conf_id,conf_name', $where);
        return $result;

    }


    /**
     * 获取pos_key映射
     * @param $keys
     * @return array
     */
    public static function getAppPosKeyMap($appKeys) {
        if (is_array($appKeys) and count($appKeys) > 0) {
            $where['app_key'] = ['in', $appKeys];
        }
        $where['status'] = 1;
        $list = self::getDao('ConfigPos')->getAllByFields('app_key,pos_key,pos_name', $where);
        $map = [];
        foreach ($list as $item) {
            $map[$item['app_key']][$item['pos_key']] = $item['pos_name'];
        }
        return $map;
    }

    /**
     * 获取pos_key映射
     * @param $keys
     * @return array
     */
    public static function getPosKeyMap($posKeys = []) {
        if (is_array($posKeys) and count($posKeys) > 0) {
            $where['pos_key'] = ['in', $posKeys];
        }
        //        $where['status'] = 1;
        return self::getDao('ConfigPos')->getFields('pos_key,pos_name', $where);

    }


    //    数据扩展
    protected static function expandReportData2($data, $expandFields) {
        $result = [];
        foreach ($data as $key => $val) {
            foreach ($expandFields as $field => $conf) {
                if ($val[$conf[1]] > 0) {
                    $val[$field] = round($val[$conf[0]] / $val[$conf[1]] * $conf[2], $conf[3]);
                } else {
                    $val[$field] = 0;
                }
            }
            $result[] = $val;
        }
        return $result;
    }

    public static function getChannels() {
        return self::getDao("ConfigChannels")->getFields('channel_id,channel_name', ['group_id' => 0]);
    }

    public static function getApps() {
        return self::getDao("ConfigApp")->getFields('app_key,app_name', ['status' => 1]);
    }


    public static function getChargeMap() {
        return self::getDao("ConfigAds")->getFields('identifier,charge_type', ['status' => 1]);

    }


    protected static function _cookData($data) {
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
}
