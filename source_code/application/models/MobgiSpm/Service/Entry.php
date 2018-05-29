<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/1/14
 * Time: 19:11
 */
class MobgiSpm_Service_EntryModel extends MobgiSpm_Service_BaseModel {

    public static function formatListMap($list, $firstKey, $valueKeys) {
        if (empty($list)) {
            return $list;
        }
        foreach ($list as $val) {
            foreach ($valueKeys as $perKey) {
                $listMap[$val[$firstKey]][$perKey] = $val[$perKey];
            }
        }
        return $listMap;
    }

    public static function getActivityMap() {
        $dao = self::getSpmDao('MonitorActivity');
        $fileds = 'id,app_id,group_id,channel_id,operator';
        $where['data_platform'] = 'monitor';
        $result = $dao->getAllByFields($fileds, $where);
        $result = self::formatListMap($result, 'id', ['app_id', 'group_id', 'channel_id', 'operator']);
        return $result;
    }

    public static function getActivityGroupMap() {
        $dao = self::getSpmDao('MonitorActivityGroup');
        $fileds = 'id,name,channel_no';
        $where = [];
        $result = $dao->getAllByFields($fileds, $where);
        $result = self::formatListMap($result, 'id', ['name', 'channel_no']);
        return $result;
    }

    public static function getAppMap() {
        $dao = self::getSpmDao('MonitorApp');
        $fileds = 'app_id,consumer_key,platform';
        $where = [];
        $result = $dao->getAllByFields($fileds, $where);
        $result = self::formatListMap($result, 'app_id', ['consumer_key', 'platform']);
        return $result;
    }

    public static function getAppNameMap() {
        $dao = self::getSpmDao('MonitorApp');
        $fileds = 'app_id,app_name,consumer_key,platform';
        $where = [];
        $result = $dao->getAllByFields($fileds, $where);
        $result = self::formatListMap($result, 'app_name', ['app_id', 'consumer_key']);
        return $result;
    }

    public static function getStaffMap() {
        $where['user_type'] = 5;
        $result = self::getAdminDao('User')->getFields('user_name,user_name', $where);
        return $result;
    }

    public static function getChannelMap() {
        $dao = self::getSpmDao('MonitorChannel');
        $fileds = 'id,channel_name,group_id';
        $where = [];
        $result = $dao->getAllByFields($fileds, $where);
        $result = self::formatListMap($result, 'id', ['channel_name', 'group_id']);
        return $result;
    }

    public static function getChannelGroupNameMap() {
        $dao = self::getSpmDao('MonitorChannelGroup');
        $fileds = 'id,name';
        $where = [];
        $result = $dao->getAllByFields($fileds, $where);
        $result = self::formatListMap($result, 'name', ['id']);
        return $result;
    }

    public static function getAndroidChannelMap() {
        $dao = self::getSpmDao('MonitorAndroidChannel');
        $fileds = 'channel_no,group_id';
        $where = [];
        $result = $dao->getAllByFields($fileds, $where);
        $result = self::formatListMap($result, 'channel_no', ['group_id']);
        return $result;
    }

    public static function getEntryCostById($id) {
        $dao = self::getSpmDao('MonitorDeliveryDailyCost');
        $result = $dao->get($id);
        return $result;
    }

    public static function getEntryPlanById($id) {
        $dao = self::getSpmDao('MonitorDeliveryPlan');
        $result = $dao->get($id);
        return $result;
    }


    public static function getStaffPlanById($id) {
        $dao = self::getSpmDao('MonitorStaffPlan');
        $result = $dao->get($id);
        return $result;
    }

    public static function batchDelCost($idArr) {
        $dao = self::getSpmDao('MonitorDeliveryDailyCost');
        $where['id'] = array('IN', $idArr);
        $result = $dao->deleteBy($where);
        return $result;
    }

    public static function batchDelPlan($idArr) {
        $dao = self::getSpmDao('MonitorDeliveryPlan');
        $where['id'] = array('IN', $idArr);
        $result = $dao->deleteBy($where);
        if ($result) {
            $res = self::delDeliveryPlanDay($idArr);
        }
        return $res;
    }

    public static function delStaffPlan($idArr) {
        $dao = self::getSpmDao('MonitorStaffPlan');
        $where['id'] = array('IN', $idArr);
        $result = $dao->deleteBy($where);
        if ($result) {
            $res = self::delStaffPlanDay($idArr);
        }
        return $res;
    }

    public static function getCostDataByParams($params, $id) {
        $where = array(
            'activity_id' => $params['activity_id'],
            'supplier' => $params['supplier'],
            'account' => $params['account'],
            'date_of_log' => $params['date_of_log']
        );
        if ($id != 0) {
            $where['id'] = array('<>', $id);
        }
        $dao = self::getSpmDao('MonitorDeliveryDailyCost');
        return $dao->getBy($where);
    }

    public static function getPlanDataByParams($params, $id) {
        $where = array(
            'app_id' => $params['app_id'],
            'channel_group_id' => $params['channel_group_id'],
            'start_date' => $params['start_date'],
            'end_date' => $params['end_date']
        );
        if ($id != 0) {
            $where['id'] = array('<>', $id);
        }
        $dao = self::getSpmDao('MonitorDeliveryPlan');
        return $dao->getBy($where);
    }

    public static function getStaffPlanDataByParams($params, $id) {
        $where = array(
            'app_id' => $params['app_id'],
            'staff' => $params['staff'],
            'start_date' => $params['start_date'],
            'end_date' => $params['end_date']
        );
        if ($id != 0) {
            $where['id'] = array('<>', $id);
        }
        $dao = self::getSpmDao('MonitorStaffPlan');
        return $dao->getBy($where);
    }

    public static function getStaffPlanDataById($id) {
        $where['id'] = $id;
        $dao = self::getSpmDao('MonitorStaffPlan');
        return $dao->getBy($where);
    }

    public static function getDeliveryPlanById($id) {
        $where['id'] = $id;
        $dao = self::getSpmDao('MonitorDeliveryPlan');
        return $dao->getBy($where);
    }

    public static function updateEntryCost($data, $params) {
        $data['update_time'] = date('Y-m-d H:i:s', time());
        $data['months'] = date('Ym', strtotime($data['date_of_log']));
        $dao = self::getSpmDao('MonitorDeliveryDailyCost');
        return $dao->updateBy($data, $params);
    }

    public static function updateEntryPlan($data, $params) {
        $data['update_time'] = date('Y-m-d H:i:s', time());
        $dao = self::getSpmDao('MonitorDeliveryPlan');
        return $dao->updateBy($data, $params);
    }


    public static function checkCostByUniqueKey($data) {
        $keyMap = array(
            'date_of_log',
            'activity_id',
            'supplier',
            'account',
        );
        $where = [];
        foreach ($keyMap as $value) {
            if (isset($data[$value])) {
                $where[$value] = $data[$value];
            }
        }
        $dao = self::getSpmDao('MonitorDeliveryDailyCost');
        $result = $dao->getBy($where);
        return $result;
    }

    public static function addDailyCost($params) {
        $keyMap = array(
            'consumer_key',
            'channel_group_id',
            'android_channel_no',
            'android_channel_group_id',
            'activity_id',
            'app_id',
            'channel_id',
            'activity_gid',
            'supplier',
            'account',
            'staff',
            'date_of_log',
            'account_consumption',
            'rebate',
            'real_consumption',
            'deposit',
            'impressions',
            'clicks'
        );
        foreach ($keyMap as $value) {
            if (isset($params[$value])) {
                $data[$value] = $params[$value];
            }
        }
        $data['months'] = date('Ym', strtotime($data['date_of_log']));
        $dao = self::getSpmDao('MonitorDeliveryDailyCost');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function checkPlanByUniqueKey($data) {
        $keyMap = array(
            'app_id',
            'consumer_key',
            'channel_group_id',
            'start_date',
            'end_date',
        );
        $where = [];
        foreach ($keyMap as $value) {
            if (isset($data[$value])) {
                $where[$value] = $data[$value];
            }
        }
        $dao = self::getSpmDao('MonitorDeliveryPlan');
        $result = $dao->getBy($where);
        return $result;
    }

    public static function checkStaffPlanByUniqueKey($data) {
        $keyMap = array(
            'app_id',
            'staff',
            'start_date',
            'end_date',
        );
        $where = [];
        foreach ($keyMap as $value) {
            if (isset($data[$value])) {
                $where[$value] = $data[$value];
            }
        }
        $dao = self::getSpmDao('MonitorStaffPlan');
        $result = $dao->getBy($where);
        return $result;
    }


    public static function addDailyPlan($params) {
        $keyMap = array(
            'app_id',
            'consumer_key',
            'channel_group_id',
            'start_date',
            'end_date',
            'daily_consumption',
            'daily_amount',
            'daily_cost',
        );
        foreach ($keyMap as $value) {
            if (isset($params[$value])) {
                $data[$value] = $params[$value];
            }
        }
        $dao = self::getSpmDao('MonitorDeliveryPlan');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function updateStaffPlan($data, $params) {
        $dao = self::getSpmDao('MonitorStaffPlan');
        return $dao->updateBy($data, $params);
    }

    public static function addStaffPlan($params) {
        $keyMap = array(
            'app_id',
            'staff',
            'consumer_key',
            'start_date',
            'end_date',
            'daily_consumption',
            'daily_amount'
        );

        foreach ($keyMap as $value) {
            if (isset($params[$value])) {
                $data[$value] = $params[$value];
            }
        }
        $dao = self::getSpmDao('MonitorStaffPlan');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }
    public static function delDeliveryPlanDay($planId) {
        $where['plan_id'] = is_array($planId) ? ['in', $planId] : $planId;
        $dao = self::getSpmDao('MonitorDeliveryPlanDay');
        return $dao->deleteBy($where);

    }

    public static function delStaffPlanDay($planId) {
        $where['plan_id'] = is_array($planId) ? ['in', $planId] : $planId;
        $dao = self::getSpmDao('MonitorStaffPlanDay');
        return $dao->deleteBy($where);

    }



    public static function addDeliveryPlanDay($planId, $item) {
        $dao = self::getSpmDao('MonitorDeliveryPlanDay');
        $data = array(
            'plan_id' => $planId,
            'app_id' => $item['app_id'],
            'channel_group_id' => $item['channel_group_id'],
            'consumer_key' => $item['consumer_key'],
            'daily_consumption' => $item['daily_consumption'],
            'daily_amount' => $item['daily_amount'],
        );
        $start = strtotime($item['start_date']);
        $end = strtotime($item['end_date']);

        for ($time = $start; $time <= $end; $time += 86400) {
            $data['days'] = date('Y-m-d', $time);
            $res = $dao->insert($data);
            if ($res == false) {
                return false;
            }
        }
        return true;
    }



    public static function addStaffPlanDay($planId, $item) {
        $dao = self::getSpmDao('MonitorStaffPlanDay');
        $data = array(
            'plan_id' => $planId,
            'app_id' => $item['app_id'],
            'staff' => $item['staff'],
            'consumer_key' => $item['consumer_key'],
            'daily_consumption' => $item['daily_consumption'],
            'daily_amount' => $item['daily_amount'],
        );
        $start = strtotime($item['start_date']);
        $end = strtotime($item['end_date']);

        for ($time = $start; $time <= $end; $time += 86400) {
            $data['days'] = date('Y-m-d', $time);
            $res = $dao->insert($data);
            if ($res == false) {
                return false;
            }
        }
        return true;
    }


    public static function getCostByParams($params = array(), $orderBy = array('id' => 'DESC')) {
        $dao = self::getSpmDao('MonitorDeliveryDailyCost');
        $result = $dao->getsBy($params, $orderBy);
        return $result;
    }

    public static function getCostList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorDeliveryDailyCost');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatCostList($list) {
        if (empty($list)) {
            return $list;
        }
        $appList = MobgiSpm_Service_DeliveryModel::getDeliveryApp();
        $appMap = Common::resetKey($appList, 'app_id');
        $activityList = MobgiSpm_Service_DeliveryModel::getDeliveryActivity();
        $activityMap = Common::resetKey($activityList, 'id');
        $channelList = MobgiSpm_Service_DeliveryModel::getDeliveryChannel();
        $channelMap = Common::resetKey($channelList, 'id');
        $channelGroupList = MobgiSpm_Service_ChannelModel::getChannelGroup();
        $channelGroupMap = Common::resetKey($channelGroupList, 'id');
        $androidChannelGroupList = MobgiSpm_Service_ChannelModel::getAndroidChannelGroup();
        $androidChannelGroupMap = Common::resetKey($androidChannelGroupList, 'id');
        foreach ($list as $key => $value) {
            $list[$key]['create_time'] = date('Y-m-d H:i', $value['create_time']);
            $list[$key]['app_name'] = isset($appMap[$value['app_id']]) ? $appMap[$value['app_id']]['app_name'] : '未知应用';
            $list[$key]['activity_name'] = isset($activityMap[$value['activity_id']]) ? $activityMap[$value['activity_id']]['name'] : '';
            $list[$key]['channel_name'] = isset($channelMap[$value['channel_id']]) ? $channelMap[$value['channel_id']]['channel_name'] : '';
            $list[$key]['channel_group_name'] = isset($channelGroupMap[$value['channel_group_id']]) ? $channelGroupMap[$value['channel_group_id']]['name'] : '';
            $list[$key]['android_channel_group_name'] = isset($androidChannelGroupMap[$value['android_channel_group_id']]) ? $androidChannelGroupMap[$value['android_channel_group_id']]['name'] : '';
        }
        return $list;
    }

    public static function getPlanByParams($params = array(), $orderBy = array('id' => 'DESC')) {
        $dao = self::getSpmDao('MonitorDeliveryPlan');
        $result = $dao->getsBy($params, $orderBy);
        return $result;
    }

    public static function getStaffPlanByParams($params = array(), $orderBy = array('id' => 'DESC')) {
        $dao = self::getSpmDao('MonitorStaffPlan');
        $result = $dao->getsBy($params, $orderBy);
        return $result;
    }

    public static function getPlanList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorDeliveryPlan');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function getStaffPlanList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorStaffPlan');
        $ret = $dao->getList($start, $limit, $params, $orderBy);

        foreach ($ret as $key => $val) {
            $ret[$key]['daily_cost'] = $val['daily_amount'] > 0 ? round($val['daily_consumption'] / $val['daily_amount'], 2) : 0;
        }

        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatPlanList($list) {
        if (empty($list)) {
            return $list;
        }
        $appList = MobgiSpm_Service_DeliveryModel::getDeliveryApp();
        $appMap = Common::resetKey($appList, 'app_id');
        $channelGroupList = MobgiSpm_Service_ChannelModel::getChannelGroup();
        $channelGroupMap = Common::resetKey($channelGroupList, 'id');
        foreach ($list as $key => $value) {
            $list[$key]['create_time'] = date('Y-m-d H:i', $value['create_time']);
            $list[$key]['app_name'] = isset($appMap[$value['app_id']]) ? $appMap[$value['app_id']]['app_name'] : '未知应用';
            $list[$key]['channel_group_name'] = isset($channelGroupMap[$value['channel_group_id']]) ? $channelGroupMap[$value['channel_group_id']]['name'] : '';
            $list[$key]['daily_cost'] = $value['daily_amount'] > 0 ? round($value['daily_consumption'] / $value['daily_amount'], 2) : 0;
        }
        return $list;
    }
}