<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/26
 * Time: 16:07
 */
class MobgiSpm_Service_ToolsModel extends MobgiSpm_Service_BaseModel{


    private static $keyMap = array(
        'click_num' => '点击记录数',
        'active_num' => '激活记录数',
        'click_id' => '点击 ID',
        'active_id' => '激活 ID',
        'idfa' => '设备 ID',
        'idfamd5' => '设备 ID 加密值',
        'pid' => '账号 ID',
        'ip' => 'ip',
        'ipua' => 'ip+ua 加密值',
        'game_id' => '应用 ID',
        'app_name' => '应用名',
        'mober' => '投放渠道',
        'sub_channel' => '子渠道',
        'activity_id' => '投放活动',
        'callback' => '回调地址',
        'isactive' => '激活状态',
        'iscallback' => '回调状态',
        'clicktime' => '点击时间',
        'activetime' => '激活时间',
        'callbacktime' => '回调时间',
        'udid' => '内部设备 ID',
        'ua' => 'user-agent',
        'area' => '地区',
        'platform' => '平台',
        'version_type' => '是否能获取到设备 ID',
        'cid' => '渠道号'
    );

    public static function getClickData($where, $orderBy = array('clicktime' => 'DESC')){
        $dao = self::getSpmDao('Click');
        $result = $dao->getsBy($where, $orderBy);
        return $result;
    }

    public static function getActiveByIdConsumerKey($uidmd5, $consumerKey){
        $tableId = crc32($uidmd5);
        $dao = self::getSpmDao('Active',$tableId);
        $where['idfamd5'] = $uidmd5;
        $where['consumer_key'] = $consumerKey;
        $result = $dao->getBy($where);
        return $result;
    }

    public static function delUdidDataByIdConsumerKey($udidmd5, $consumerKey){
        $tableId = crc32($udidmd5);
        $dao = self::getSpmDao('Udid',$tableId);
        $where['udidmd5'] = $udidmd5;
        $where['consumer_key'] = $consumerKey;
        $result = $dao->deleteBy($where);
        return $result;
    }

    public static function delActiveCache($uidmd5, $consumerKey){
        $key = Util_CacheKey::SPM_ACTIVE_UIDMD5_CONSUMERKEY . $uidmd5 . '_' . $consumerKey;
        $cache = Cache_Factory::getCache (Cache_Factory::ID_REMOTE_REDIS,'spm');
        $ret = $cache->delete($key); // 1：删除成功；0：不存在或者删除失败
        return $ret;
    }

    public static function delUdidCache($udidmd5, $consumerKey){
        $key = Util_CacheKey::SPM_ACTIVE_UDIDMD5_CONSUMERKEY . $udidmd5 . '_' . $consumerKey;
        $cache = Cache_Factory::getCache (Cache_Factory::ID_REMOTE_REDIS,'spm');
        $ret = $cache->delete($key); // 1：删除成功；0：不存在或者删除失败
        return $ret;
    }

    public static function delActiveDataById($uidmd5, $id){
        $tableId = crc32($uidmd5);
        $dao = self::getSpmDao('Active',$tableId);
        $result = $dao->delete($id);
        return $result;
    }

    public static function formatAttributeData($data, $activeType, $callbackType){
        unset($data['id']);
        unset($data['consumer_key']);
        $data['clicktime'] = date("Y-m-d H:i:s", $data['clicktime']);
        $data['activetime'] = empty($data['activetime']) ? '' : date("Y-m-d H:i:s", $data['activetime']);
        $data['callbacktime'] = empty($data['callbacktime']) ? '' : date("Y-m-d H:i:s", $data['callbacktime']);
        $data['isactive'] = $activeType[$data['isactive']];
        $data['iscallback'] = $callbackType[$data['iscallback']];
        $data['version_type'] = empty($data['version_type']) ? '能' : '不能';
        return $data;
    }

    public static function getDataList($app, $deviceId){
        $monitorConfig = Common::getConfig('spmConfig', 'MONITOR_TOOLS');
        $activeType = $monitorConfig['active_status'];
        $callbackType = $monitorConfig['callback_status'];
        if($app['platform'] == 'ios'){
            $uid = strtoupper($deviceId);
        }else{
            $uid = strtolower($deviceId);
        }
        $muid = md5($uid);
        $where = array(
          'game_id' => $app['appstore_id'],
          'idfamd5' => $muid
        );
        $clickData = self::getClickData($where);
        if(empty($clickData)){
            $where['idfamd5'] = strtolower($deviceId);
            $clickData = self::getClickData($where);
        }
        $activeData = self::getActiveByIdConsumerKey($muid, $app['consumer_key']);
        if(!$activeData){
            $activeData = self::getActiveByIdConsumerKey(strtolower($deviceId), $app['consumer_key']);
        }
        $result = array(
            'key_map' => self::$keyMap,
            'total' => array(
                'click_num' => 0,
                'active_num' => 0
            ),
            'data' => array()
        );
        $clickId = $activeData ? $activeData['click_id'] : 0;
        $activeValueKey = array(
            'idfa','idfamd5','ipua','pid','callback','isactive','iscallback','ua',
            'udid','area','platform','version_type','cid'
        );
        $clickValueKey = array(
            'pid','udid','area','platform','version_type','cid'
        );
        if(!empty($clickData)){
            $activeStatus = false;
            $result['total']['click_num'] = count($clickData);
            foreach($clickData as $key => $value){
                $clickData[$key]['isactive'] = ($clickData[$key]['isactive'] == 1) ? -1 : $clickData[$key]['isactive'];
                if($value['id'] == $clickId && $clickId != 0){
                    $activeStatus = true;
                    $clickData[$key]['active_id'] = $activeData['id'];
                    $clickData[$key]['activetime'] = $activeData['activetime'];
                    $clickData[$key]['callbacktime'] = $activeData['callbacktime'];
                    foreach($activeValueKey as $valueKey){
                        $clickData[$key][$valueKey] = $activeData[$valueKey];
                    }
                    $result['total']['active_num'] += 1;
                }else{
                    $clickData[$key]['active_id'] = 0;
                    $clickData[$key]['iscallback'] = 0;
                    foreach($clickValueKey as $valueKey){
                        $clickData[$key][$valueKey] = '';
                    }
                }
                $clickData[$key]['app_name'] = $app['app_name'];
                $clickData[$key]['mober'] = self::getChannelNameByNo($clickData[$key]['mober']);
                $clickData[$key]['activity_id'] = self::getActivityNameById($clickData[$key]['activity_id']);
                $clickData[$key]['click_id'] = $value['id'];
                $clickData[$key] = self::formatAttributeData($clickData[$key], $activeType, $callbackType);
            }
            $result['data'] = $clickData;
            if($activeData && !$activeStatus){
                $result['total']['active_num'] += 1;
                $activeData['app_name'] = $app['app_name'];
                $activeData['active_id'] = $activeData['id'];
                $activeData = self::formatAttributeData($activeData, $activeType, $callbackType);
                array_unshift($result['data'],$activeData);
            }
        }else{
            if($activeData){
                $result['total']['active_num'] += 1;
                $activeData['app_name'] = $app['app_name'];
                $activeData['active_id'] = $activeData['id'];
                $activeData = self::formatAttributeData($activeData, $activeType, $callbackType);
                $result['data'][] = $activeData;
            }
        }
        return $result;

    }

    public static function resetData($app, $deviceId){
        if($app['platform'] == 'ios'){
            $uid = strtoupper($deviceId);
        }else{
            $uid = strtolower($deviceId);
        }
        $muid = md5($uid);
        $activeData = self::getActiveByIdConsumerKey($muid, $app['consumer_key']);
        if(!$activeData){
            $muid = strtolower($deviceId);
            $activeData = self::getActiveByIdConsumerKey($muid, $app['consumer_key']);
        }
        if(!$activeData){
            return false;
        }else{
            self::delActiveDataById($muid, $activeData['id']);
            self::delActiveCache($muid, $app['consumer_key']);
            $udidmd5 = md5($activeData['udid']);
            self::delUdidDataByIdConsumerKey($udidmd5, $app['consumer_key']);
            self::delUdidCache($udidmd5, $app['consumer_key']);
            return true;
        }
    }

    public static function getChannelNameByNo($channelNo){
        $dao = self::getSpmDao('MonitorChannel');
        $result = $dao->getBy( array('channel_no'=>$channelNo));
        if($result){
            return $result['channel_name'];
        }else{
            return '未知渠道';
        }
    }

    public static function getActivityNameById($id){
        $result = self::getActivityById($id);
        if($result){
            return $result['name'];
        }else{
            return '未知活动';
        }
    }

    public static function getProcessList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $limit = ($limit == 0) ? 10 : $limit;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorProcess');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatProcessList($List) {
        if(empty($List)){
            return $List;
        }
        $monitorConfig = Common::getConfig('spmConfig', 'MONITOR_TOOLS');
        $monitorPlatform = $monitorConfig['monitor_platform'];
        foreach($List as $key => $value){
            $List[$key]['platform'] = isset($monitorPlatform[$value['platform']]) ? $monitorPlatform[$value['platform']]: '未知平台';
            $List[$key]['process_list_str'] = implode(' , ',json_decode($value['process_list'], true));
            $List[$key]['monitor_time'] = date('Y-m-d H:i', $value['monitor_time']);
        }
        return $List;
    }

    public static function addProcess($params){
        $params['create_time'] = $params['update_time'] = time();
        $dao = self::getSpmDao('MonitorProcess');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function updateProcess($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorProcess');
        return $dao->updateBy($data, $params);
    }

    public static function getDirectoryList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $limit = ($limit == 0) ? 10 : $limit;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorDirectory');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatDirectoryList($List) {
        if(empty($List)){
            return $List;
        }
        $monitorConfig = Common::getConfig('spmConfig', 'MONITOR_TOOLS');
        $monitorPlatform = $monitorConfig['monitor_platform'];
        foreach($List as $key => $value){
            $List[$key]['platform'] = isset($monitorPlatform[$value['platform']]) ? $monitorPlatform[$value['platform']]: '未知平台';
            $List[$key]['directory_list_str'] = implode(' , ',json_decode($value['directory_list'], true));
            $List[$key]['monitor_time'] = date('Y-m-d H:i', $value['monitor_time']);
        }
        return $List;
    }

    public static function addDirectory($params){
        $params['create_time'] = $params['update_time'] = time();
        $dao = self::getSpmDao('MonitorDirectory');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function updateDirectory($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorDirectory');
        return $dao->updateBy($data, $params);
    }

    public static function getScriptAlarmList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $limit = ($limit == 0) ? 10 : $limit;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorScriptAlarm');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatScriptAlarmList($List) {
        if(empty($List)){
            return $List;
        }
        $monitorConfig = Common::getConfig('spmConfig', 'MONITOR_TOOLS');
        $monitorPlatform = $monitorConfig['monitor_platform'];
        $alarmType = $monitorConfig['alarm_type'];
        foreach($List as $key => $value){
            $List[$key]['platform'] = isset($monitorPlatform[$value['platform']]) ? $monitorPlatform[$value['platform']]: '未知平台';
            $List[$key]['alarm_type'] = isset($alarmType[$value['alarm_type']]) ? $alarmType[$value['alarm_type']]: '未知方式';
            $List[$key]['alarm_time'] = date('Y-m-d H:i', $value['alarm_time']);
        }
        return $List;
    }

    public static function addScriptAlarm($params){
        $params['create_time'] = $params['update_time'] = time();
        $dao = self::getSpmDao('MonitorScriptAlarm');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function updateScriptAlarm($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorScriptAlarm');
        return $dao->updateBy($data, $params);
    }

    public static function getMonitorDocumentType($orderBy = array('update_time' => 'DESC')) {
        $dao = self::getSpmDao('MonitorDocumentType');
        $fileds = 'id,name';
        $where = [];
        $result = $dao->getAllByFields($fileds, $where, $orderBy);
        return $result;
    }

    public static function getDocumentList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorDocument');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatDocumentList($documentList) {
        if (empty($documentList)) {
            return $documentList;
        }
        $typeList = self::getMonitorDocumentType();
        $typeMap = Common::resetKey($typeList, 'id');
        foreach ($documentList as $key => $value) {
            $documentList[$key]['update_time'] = date('Y-m-d H:i', $value['update_time']);
            $documentList[$key]['document_type'] = isset($typeMap[$value['document_type']]) ? $typeMap[$value['document_type']]['name'] : '未知类型';
        }
        return $documentList;
    }

    public static function addDocument($data){
        $data['create_time'] = $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorDocument');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function updateDocument($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorDocument');
        return $dao->updateBy($data, $params);
    }

    public static function getDocumentByParams($params, $id = 0){
        $dao = self::getSpmDao('MonitorDocument');
        if($id != 0){
            $params['id'] = array('<>',$id);
        }
        return $dao->getBy( $params );
    }

    public static function getDocumentTypeList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorDocumentType');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatDocumentTypeList($list) {
        if(empty($list)){
            return $list;
        }
        $keys = array_keys(Common::resetKey($list, 'id'));
        $dao = self::getSpmDao('MonitorDocument');
        $result = $dao->groupCount( array('document_type'=>array('IN', $keys)), 'document_type');
        $result = Common::resetKey($result, 'document_type');
        foreach($list as $key => $value){
            if(isset($result[$value['id']]['count_num'])){
                $list[$key]['num'] = $result[$value['id']]['count_num'];
            }else{
                $list[$key]['num'] = 0;
            }
        }
        return $list;
    }

    public static function addDocumentType($data){
        $data['create_time'] = $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorDocumentType');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function updateDocumentType($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorDocumentType');
        return $dao->updateBy($data, $params);
    }

    public static function getDocumentTypeByParams($params, $id = 0){
        $dao = self::getSpmDao('MonitorDocumentType');
        if($id != 0){
            $params['id'] = array('<>',$id);
        }
        return $dao->getBy( $params );
    }

    public static function getUserIdsByName($name, $groupId){
        $result1  = Admin_Service_UserModel::getsBy( array( 'group_id' => $groupId, 'user_name'=>array('LIKE', trim($name)) ));
        $keys1 = array_keys(Common::resetKey($result1, 'user_id'));
        $result2  = Admin_Service_UserModel::getsBy( array( 'group_id' => $groupId, 'email'=>array('LIKE', trim($name)) ));
        $keys2 =  array_keys(Common::resetKey($result2, 'user_id'));;
        if($keys1 && $keys2){
            return  array_unique(array_merge($keys1, $keys2));
        }
        if (!empty($keys1)){
            return $keys1;
        }
        if(!empty($keys2)){
            return $keys2;
        }
        return false;
    }

    public static function getChannelAccountList($page = 1, $limit = 10, $params = array(), $orderBy = array('user_id' => 'DESC')) {
        list($total, $ret) = Admin_Service_UserModel::getList($page, $limit, $params, $orderBy);
        return array($total, $ret);
    }

    public static function formatAdvertiserDetail($userId){
        $advertiserDetail = self::getAdvertiserDetailByUserId($userId);
        if(!empty($advertiserDetail)){
            # 查询活动列表并拼装
            $relateActivity = json_decode($advertiserDetail['relate_activity'], true);
            if(!empty($relateActivity)){
                $activityList = MobgiSpm_Service_DeliveryModel::getDeliveryActivitysByParams( array('id'=>array('IN',$relateActivity)) );
                $advertiserDetail['relate_activity'] = array();
                foreach($activityList as $value){
                    $advertiserDetail['relate_activity'][$value['id']] = $value['name'];
                }
                $advertiserDetail['relate_activity'] = json_encode($advertiserDetail['relate_activity']);
            }
            # 查询安卓渠道号列表并拼装
            $relateAndroidChannel = json_decode($advertiserDetail['relate_android_channel'], true);
            if(!empty($relateAndroidChannel)){
                $chhanelList = MobgiSpm_Service_ChannelModel::getAndroidChannelsByParams( array('channel_no'=>array('IN',$relateAndroidChannel)) );
                $advertiserDetail['relate_android_channel'] = array();
                foreach($chhanelList as $value){
                    $advertiserDetail['relate_android_channel'][$value['channel_no']] = $value['channel_name'];
                }
                $advertiserDetail['relate_android_channel'] = json_encode($advertiserDetail['relate_android_channel']);
            }
        }
        return $advertiserDetail;
    }

    public static function formatEditAdvertiserDetail($relateActivity, $relateAndroidChannel){
        $relateActivity = empty(trim($relateActivity)) ? array() : explode(',',trim($relateActivity));
        $data['relate_activity'] = json_encode($relateActivity);
        $relateAndroidChannel = empty(trim($relateAndroidChannel)) ? array() : explode(',',trim($relateAndroidChannel));
        $data['relate_android_channel'] = json_encode($relateAndroidChannel);
        $relateApp = $relateChannel = $relateAndroidChannelGroup = array();
        if(!empty($relateActivity)){
            $activityList = MobgiSpm_Service_DeliveryModel::getDeliveryActivitysByParams( array('id'=>array('IN',$relateActivity)) );
            foreach($activityList as $activityVal){
                if(!in_array($activityVal['app_id'],$relateApp)){
                    $relateApp[] = $activityVal['app_id'];
                }
                if(!in_array($activityVal['channel_id'],$relateChannel)){
                    $relateChannel[] = $activityVal['channel_id'];
                }
            }
        }
        if(!empty($relateAndroidChannel)){
            $androidChannelList = MobgiSpm_Service_ChannelModel::getAndroidChannelsByParams( array('channel_no'=>array('IN',$relateAndroidChannel)) );
            foreach($androidChannelList as $androidChannelVal){
                if(!in_array($androidChannelVal['group_id'],$relateAndroidChannelGroup)){
                    $relateAndroidChannelGroup[] = $androidChannelVal['group_id'];
                }
            }
        }
        $data['relate_app'] = json_encode($relateApp);
        $data['relate_channel'] = json_encode($relateChannel);
        $data['relate_android_channel_group'] = json_encode($relateAndroidChannelGroup);
        return $data;
    }

    public static function getAdvertiserDetailByUserId($userId){
        $dao = self::getSpmDao('AdvertiserDetail');
        $result = $dao->getBy( array('admin_id'=>$userId));
        return $result;
        if($result){
            return $result['channel_name'];
        }else{
            return '未知渠道';
        }
    }

    public static function addAdvertiserDetail($data){
        $data['create_time'] = $data['update_time'] = time();
        $dao = self::getSpmDao('AdvertiserDetail');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function updateAdvertiserDetail($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('AdvertiserDetail');
        return $dao->updateBy($data, $params);
    }

}