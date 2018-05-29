<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/5
 * Time: 11:34
 */
class MobgiSpm_Service_DeliveryModel extends MobgiSpm_Service_BaseModel {

    public static function getAdApp() {
        $dao = self::getApiDao('AdApp');
        $fileds = 'app_id,app_name,apk_url,delivery_type';
        $where['is_track'] = 1;
        $result = $dao->getAllByFields($fileds, $where);
        return $result;
    }

    public static function getDeliveryApp($orderBy = array('update_time' => 'DESC')) {
        $dao = self::getSpmDao('MonitorApp');
        $fileds = 'app_id,app_name,consumer_key,appstore_id,appstore_url,delivery_type,platform';
        $where = [];
        $result = $dao->getAllByFields($fileds, $where, $orderBy);
        return $result;
    }

    public static function getDeliveryAppsByParams($params, $orderBy = array('app_id' => 'DESC')) {
        $dao = self::getSpmDao('MonitorApp');
        $fileds = 'app_id,app_name,consumer_key';
        $result = $dao->getAllByFields($fileds, $params, $orderBy);
        return $result;
    }

    public static function getAppsflyerConfigById($appId) {
        $dao = self::getSpmDao('AppsflyerConfig');
        $where['appsflyer_appid'] = $appId;
        $result = $dao->getBy($where);
        return $result;
    }

    public static function getAppList($page = 1, $limit = 10, $params = array(), $orderBy = array('app_id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorApp');
        //        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $ret = $dao->getsBy($params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatAppList($appList, $reportList, $reportChannelList, $natureReportList, $kpis) {
        $app = [];
        $registers = [];
        foreach ($reportChannelList as $item) {
            $registers[$item['app_id']] = $item['registers'];
        }
        foreach ($reportList as $item) {
            $app[$item['app_id']] = $item;
            if(isset($registers[$item['app_id']])){
                $app[$item['app_id']]['registers'] = $registers[$item['app_id']];
            }
        }
        foreach ($natureReportList as $item) {
            $app[$item['app_id']]['nature_actives'] = $item['actives'];
            $app[$item['app_id']]['nature_registers'] = $item['registers'];
        }
        $kpis[] = 'nature_actives';
        $kpis[] = 'nature_registers';
        foreach ($appList as $key => $value) {
            if (isset($app[$value['app_id']])) {
                foreach ($kpis as $kpi) {
                    $value[$kpi] = $app[$value['app_id']][$kpi];
                }
            } else {
                foreach ($kpis as $kpi) {
                    $value[$kpi] = 0;
                }
            }
            $appList[$key] = $value;
        }
        return $appList;
    }

    public static function pageList($list, $page, $limit, $field, $order = 'desc') {
        $sortKey = [];
        foreach ($list as $key => $item) {
            $sortKey[$key] = $item[$field];
        }
        $order = $order == 'asc' ? SORT_ASC : SORT_DESC;
        array_multisort($sortKey, $order, SORT_NUMERIC, $list);
        $result = [];
        $start = ($page - 1) * $limit;
        $end = $page * $limit;

        foreach ($list as $key => $item) {
            if ($key >= $start and $key < $end) {
                $result[] = $item;
            }
        }
        return $result;
    }


    public static function getDeliveryAppByAppstoreId($appstoreId, $appId) {
        if ($appId != 0) {
            $where['app_id'] = array('<>', $appId);
        }
        $where['appstore_id'] = $appstoreId;
        $dao = self::getSpmDao('MonitorApp');
        $result = $dao->getBy($where);
        return $result;
    }

    public static function addApp($data) {
        $data['create_time'] = $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorApp');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function updateApp($data, $params) {
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorApp');
        return $dao->updateBy($data, $params);
    }

    public static function getDeliveryChannel($params = array()) {
        $dao = self::getSpmDao('MonitorChannel');
        $fileds = 'id,channel_name,group_id';
        $params['delivery_type'] = 1;
        $result = $dao->getAllByFields($fileds, $params);
        return $result;
    }

    public static function getMonitorPlatform() {
        $dao = self::getSpmDao('MonitorPlatform');
        $fileds = 'id,name';
        $where = [];
        $result = $dao->getAllByFields($fileds, $where);
        return $result;
    }

    public static function getDeliveryActivityGroup($appId) {
        $dao = self::getSpmDao('MonitorActivityGroup');
        $fileds = 'id,name';
        $where = [];
        if (!empty($appId)) {
            $where['app_id'] = $appId;
        }
        $orderBy = array('update_time' => 'DESC');
        $result = $dao->getAllByFields($fileds, $where, $orderBy);
        return $result;
    }

    public static function getDeliveryActivityGroupByName($name) {
        $dao = self::getSpmDao('MonitorActivityGroup');
        $where['name'] = $name;
        $result = $dao->getBy($where);
        return $result;
    }

    public static function getDeliveryActivityByName($name, $id) {
        if ($id != 0) {
            $where['id'] = array('<>', $id);
        }
        $where['name'] = $name;
        $dao = self::getSpmDao('MonitorActivity');
        $result = $dao->getBy($where);
        return $result;
    }

    public static function getDeliveryActivityCount($groupId) {
        $dao = self::getSpmDao('MonitorActivity');
        $where['group_id'] = $groupId;
        $count = $dao->count($where);
        return $count;
    }

    public static function getDeliveryActivityByParams($params) {
        $dao = self::getSpmDao('MonitorActivity');
        return $dao->getBy($params);
    }

    public static function getDeliveryActivitysByParams($params, $orderBy = array('id' => 'DESC')) {
        $dao = self::getSpmDao('MonitorActivity');
        $fileds = 'id,name,app_id,channel_id';
        $result = $dao->getAllByFields($fileds, $params, $orderBy);
        return $result;
    }

    public static function getDeliveryActivity($orderBy = array('update_time' => 'DESC')) {
        $dao = self::getSpmDao('MonitorActivity');
        $fileds = 'id,name,app_id';
        $where['data_platform'] = 'monitor';
        $result = $dao->getAllByFields($fileds, $where, $orderBy);
        return $result;
    }

    public static function getActivityList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorActivity');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatActivityList($activityList) {
        if (empty($activityList)) {
            return $activityList;
        }
        $appList = self::getDeliveryApp();
        $appMap = Common::resetKey($appList, 'app_id');
        $channelList = self::getDeliveryChannel();
        $channelMap = Common::resetKey($channelList, 'id');
        $monitorConfig = Common::getConfig('spmConfig', 'MONITOR_STATUS');
        $statusMap = $monitorConfig['activity_status'];
        $settingConfig = Common::getConfig('spmConfig', 'MONITOR_SETTING');
        $trackType = $settingConfig['track_type'];
        foreach ($activityList as $key => $value) {
            $activityList[$key]['create_time'] = date('Y-m-d H:i', $value['create_time']);
            $activityList[$key]['id_sign'] = '-N' . $value['id'];
            $activityList[$key]['app_name'] = isset($appMap[$value['app_id']]) ? $appMap[$value['app_id']]['app_name'] : '未知应用';
            $activityList[$key]['channel_name'] = isset($channelMap[$value['channel_id']]) ? $channelMap[$value['channel_id']]['channel_name'] : '未知渠道';
            $activityList[$key]['status'] = isset($statusMap[$value['status']]) ? $statusMap[$value['status']] : '未知状态';
            # 临时适配逻辑，后期删除
            if ($value['track_type'] == 'shortlink') {
                $shortUrlHost = Yaf_Application::app()->getConfig()->shorturlroot;
                if (empty($value['api_link'])) { # 空链接直接使用短链接
                    $activityList[$key]['api_link'] = $shortUrlHost . '/' . $value['short_link'];
                } elseif (empty($value['callback_config'])) { # 非空链接判断是否存在回调地址，没有直接使用短链接
                    $activityList[$key]['api_link'] = $shortUrlHost . '/' . $value['short_link'];
                }
            }
            $activityList[$key]['track_type'] = isset($trackType[$value['track_type']]) ? $trackType[$value['track_type']] : '未知方式';
        }
        return $activityList;
    }

    public static function addActivity($params) {
        $params['create_time'] = $params['update_time'] = time();
        $params['status'] = 2;
        $keyMap = array(
            'name',
            'group_id',
            'channel_id',
            'app_id',
            'track_type',
            'origin_url',
            'short_link',
            'shortlink_status',
            'status',
            'checkpoint',
            'create_time',
            'update_time',
            'api_link',
            'redirect_config',
            'callback_config',
            'data_platform',
            'platform',
            'monitor_platform',
            'operator'
        );
        foreach ($keyMap as $value) {
            if (isset($params[$value])) {
                $data[$value] = $params[$value];
            }
        }
        $dao = self::getSpmDao('MonitorActivity');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function updateActivity($data, $params) {
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorActivity');
        return $dao->updateBy($data, $params);
    }

    public static function getActivityGroupList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorActivityGroup');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatActivityGroupList($activityGroupList) {
        if (empty($activityGroupList)) {
            return $activityGroupList;
        }
        $keys = array_keys(Common::resetKey($activityGroupList, 'id'));
        $dao = self::getSpmDao('MonitorActivity');
        $result = $dao->groupCount(array('group_id' => array('IN', $keys)), 'group_id');
        $result = Common::resetKey($result, 'group_id');
        foreach ($activityGroupList as $key => $value) {
            $activityGroupList[$key]['create_time'] = date('Y-m-d H:i', $value['create_time']);
            if (isset($result[$value['id']]['count_num'])) {
                $activityGroupList[$key]['num'] = $result[$value['id']]['count_num'];
            } else {
                $activityGroupList[$key]['num'] = 0;
            }
        }
        return $activityGroupList;
    }

    public static function addActivityGroup($params) {
        $params['create_time'] = $params['update_time'] = time();
        $dao = self::getSpmDao('MonitorActivityGroup');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function updateActivityGroup($data, $params) {
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorActivityGroup');
        return $dao->updateBy($data, $params);
    }

    public static function getMonitorPlatformList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorPlatform');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatMonitorPlatformList($platformList) {
        if (empty($platformList)) {
            return $platformList;
        }
        $monitorConfig = Common::getConfig('spmConfig', 'MONITOR_PLATFORM');
        $platformType = $monitorConfig['type'];
        foreach ($platformList as $key => $value) {
            $platformList[$key]['type'] = $platformType[$value['type']];
            $platformList[$key]['update_time'] = date('Y-m-d H:i', $value['update_time']);
        }
        return $platformList;
    }

    public static function getMonitorPlatformByNo($platformNo, $id) {
        if ($id != 0) {
            $where['id'] = array('<>', $id);
        }
        $where['platform_no'] = $platformNo;
        $dao = self::getSpmDao('MonitorPlatform');
        return $dao->getBy($where);
    }

    public static function addMonitorPlatform($params) {
        $params['create_time'] = $params['update_time'] = time();
        $dao = self::getSpmDao('MonitorPlatform');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function updateMonitorPlatform($data, $params) {
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorPlatform');
        return $dao->updateBy($data, $params);
    }

    public static function addAppDetail($params) {
        $params['create_time'] = $params['update_time'] = time();
        $dao = self::getSpmDao('MonitorAppDetail');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function updateAppDetail($data, $params) {
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorAppDetail');
        return $dao->updateBy($data, $params);
    }
}