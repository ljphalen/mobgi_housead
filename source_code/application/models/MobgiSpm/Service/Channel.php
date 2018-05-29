<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/15
 * Time: 15:16
 */
class MobgiSpm_Service_ChannelModel extends MobgiSpm_Service_BaseModel{

    public static function getChannelLabel(){
        $dao = self::getSpmDao('MonitorChannelLabel');
        $fileds = 'id,name';
        $result = $dao->getAllByFields($fileds);
        return $result;
    }

    public static function getChannelList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorChannel');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatChannelList($channelList) {
        if(empty($channelList)){
            return $channelList;
        }
        foreach($channelList as $key => $value){
            $channelList[$key]['create_time'] = date('Y-m-d H:i', $value['create_time']);
            $channelList[$key]['update_time'] = date('Y-m-d H:i', $value['update_time']);
        }
        return $channelList;
    }

    public static function getChannelByParams($params){
        $dao = self::getSpmDao('MonitorChannel');
        $fileds = 'id,channel_name,group_id';
        $params['delivery_type'] = 1;
        $result = $dao->getAllByFields($fileds, $params);
        return $result;
    }

    public static function getChannelByNo($channelNo){
        $dao = self::getSpmDao('MonitorChannel');
        return $dao->getBy( array('channel_no'=>$channelNo));
    }

    public static function getChannelIdsByName($name){
        $dao = self::getSpmDao('MonitorChannel');
        $result1 = $dao->getsBy( array('channel_no'=>array('LIKE', trim($name))));
        $keys1 = array_keys(Common::resetKey($result1, 'id'));
        $result2 = $dao->getsBy( array('channel_name'=>array('LIKE', trim($name))));
        $keys2 =  array_keys(Common::resetKey($result2, 'id'));;
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

    public static function addChannel($params){
        $params['create_time'] = $params['update_time'] = time();
        $params['delivery_type'] = 1;
        $params['status'] = 'ON';
        $keyMap = array(
            'channel_no','channel_name','track_type','shortlink_status','create_time','update_time','status','delivery_type','operator'
        );
        foreach($keyMap as $value){
            if(isset($params[$value])){
                $data[$value] = $params[$value];
            }
        }
        $dao = self::getSpmDao('MonitorChannel');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function addAbroadChannel($params){
        $params['create_time'] = $params['update_time'] = time();
        $params['delivery_type'] = 0;
        $params['track_type'] = 'api';
        $params['status'] = 'ON';
        $params['operator'] = 'push_api_get_install_report_android';
        $keyMap = array(
            'channel_no','channel_name','track_type','create_time','update_time','status','delivery_type','operator'
        );
        foreach($keyMap as $value){
            if(isset($params[$value])){
                $data[$value] = $params[$value];
            }
        }
        $dao = self::getSpmDao('MonitorChannel');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function updateChannel($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorChannel');
        return $dao->updateBy($data, $params);
    }

    public static function addChannelDetail($params){
        $params['create_time'] = $params['update_time'] = time();
        $keyMap = array(
            'channel_id','template','android_template','agent_template','agent_android_template',
            'callback_template','callback_android_template','callback_agent_template','callback_agent_android_template',
            'create_time','update_time','operator'
        );
        foreach($keyMap as $value){
            if(isset($params[$value])){
                $data[$value] = $params[$value];
            }
        }
        $dao = self::getSpmDao('MonitorChannelDetail');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function updateChannelDetail($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorChannelDetail');
        return $dao->updateBy($data, $params);
    }

    public static function getChannelgroup($orderBy = array('update_time' => 'DESC')) {
        $dao = self::getSpmDao('MonitorChannelGroup');
        $fileds = 'id,name';
        $where = [];
        $result = $dao->getAllByFields($fileds, $where, $orderBy);
        return $result;
    }

    public static function getChannelGroupList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorChannelGroup');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatChannelGroupList($channelGroupList) {
        if(empty($channelGroupList)){
            return $channelGroupList;
        }
        $channelLabel = self::getChannelLabel();
        $channelLabel = Common::resetKey($channelLabel, 'id');
        $keys = array_keys(Common::resetKey($channelGroupList, 'id'));
        $dao = self::getSpmDao('MonitorChannel');
        $result = $dao->groupCount( array('group_id'=>array('IN', $keys)), 'group_id');
        $result = Common::resetKey($result, 'group_id');
        foreach($channelGroupList as $key => $value){
            $channelGroupList[$key]['label_name'] = $channelLabel[$value['label_id']]['name'];
            if(isset($result[$value['id']]['count_num'])){
                $channelGroupList[$key]['num'] = $result[$value['id']]['count_num'];
            }else{
                $channelGroupList[$key]['num'] = 0;
            }
        }
        return $channelGroupList;
    }

    public static function getChannelGroupByParams($params, $id = 0){
        $dao = self::getSpmDao('MonitorChannelGroup');
        if($id != 0){
            $params['id'] = array('<>',$id);
        }
        return $dao->getBy( $params );
    }

    public static function addChannelGroup($params){
        $params['create_time'] = $params['update_time'] = time();
        $keyMap = array(
            'name','label_id','create_time','update_time'
        );
        foreach($keyMap as $value){
            if(isset($params[$value])){
                $data[$value] = $params[$value];
            }
        }
        $dao = self::getSpmDao('MonitorChannelGroup');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function updateChannelGroup($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorChannelGroup');
        return $dao->updateBy($data, $params);
    }

    public static function getChannelLabelList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorChannelLabel');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatChannelLabelList($channelLabelList) {
        if(empty($channelLabelList)){
            return $channelLabelList;
        }
        $keys = array_keys(Common::resetKey($channelLabelList, 'id'));
        $dao = self::getSpmDao('MonitorChannelGroup');
        $result = $dao->groupCount( array('label_id'=>array('IN', $keys)), 'label_id');
        $result = Common::resetKey($result, 'label_id');
        foreach($channelLabelList as $key => $value){
            if(isset($result[$value['id']]['count_num'])){
                $channelLabelList[$key]['num'] = $result[$value['id']]['count_num'];
            }else{
                $channelLabelList[$key]['num'] = 0;
            }
        }
        return $channelLabelList;
    }

    public static function addChannelLabel($data){
        $data['create_time'] = $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorChannelLabel');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function updateChannelLabel($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorChannelLabel');
        return $dao->updateBy($data, $params);
    }

    public static function getChannelLabelByParams($params, $id = 0){
        $dao = self::getSpmDao('MonitorChannelLabel');
        if($id != 0){
            $params['id'] = array('<>',$id);
        }
        return $dao->getBy( $params );
    }



    public static function getAndroidChannelGroupByParams($params, $id = 0){
        $dao = self::getSpmDao('MonitorAndroidChannelGroup');
        if($id != 0){
            $params['id'] = array('<>',$id);
        }
        return $dao->getBy( $params );
    }

    public static function getAndroidChannelgroup($orderBy = array('update_time' => 'DESC')) {
        $dao = self::getSpmDao('MonitorChannelGroup');
        $fileds = 'id,name';
        $where = [];
        $result = $dao->getAllByFields($fileds, $where, $orderBy);
        return $result;
    }

    public static function getAndroidChannelGroupList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorChannelGroup');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatAndroidChannelGroupList($channelGroupList) {
        if(empty($channelGroupList)){
            return $channelGroupList;
        }
        $keys = array_keys(Common::resetKey($channelGroupList, 'id'));
        $dao = self::getSpmDao('MonitorAndroidChannel');
        $result = $dao->groupCount( array('group_id'=>array('IN', $keys)), 'group_id');
        $result = Common::resetKey($result, 'group_id');
        foreach($channelGroupList as $key => $value){
            if(isset($result[$value['id']]['count_num'])){
                $channelGroupList[$key]['num'] = $result[$value['id']]['count_num'];
            }else{
                $channelGroupList[$key]['num'] = 0;
            }
        }
        return $channelGroupList;
    }

    public static function getAndroidChannelByGroupId($id) {
        $dao = self::getSpmDao('MonitorAndroidChannel');
        $where['group_id'] = $id;
        $result = $dao->getsBy($where);
        return $result;
    }

    public static function getAndroidChannelsByParams($params) {
        $dao = self::getSpmDao('MonitorAndroidChannel');
        return $dao->getsBy($params);
    }

    public static function getAndroidChannelFiledsByParams($params) {
        $dao = self::getSpmDao('MonitorAndroidChannel');
        $fileds = 'app_id,channel_name,channel_no';
        $result = $dao->getAllByFields($fileds, $params);
        return $result;
    }

    public static function formatAndroidChannelList($channelList) {
        if(empty($channelList)){
            return $channelList;
        }
        $newChannel = array();
        foreach($channelList as $key => $value){
            $newChannel[$value['operator']][] = $value;
        }
        return $newChannel;
    }

    public static function addAndroidChannelGroup($params){
        $params['create_time'] = $params['update_time'] = time();
        $dao = self::getSpmDao('MonitorAndroidChannelGroup');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function updateAndroidChannelGroup($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorAndroidChannelGroup');
        return $dao->updateBy($data, $params);
    }

    public static function getAndroidChannelByNo($params, $group_id){
        $dao = self::getSpmDao('MonitorAndroidChannel');
        if($group_id != 0){
            $params['group_id'] = array('<>',$group_id);
        }
        $result = $dao->getBy($params);
        return $result;
    }

    public static function delAndroidChannelByGroupId($groupId){
        $dao = self::getSpmDao('MonitorAndroidChannel');
        $params['group_id'] = $groupId;
        $result = $dao->deleteBy($params);
        return $result;
    }

    public static function addAndroidChannel($params){
        $params['create_time'] = $params['update_time'] = time();
        $dao = self::getSpmDao('MonitorAndroidChannel');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function getGdtConfigList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $limit = ($limit == 0) ? 10 : $limit;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('GdtConfig');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatGdtConfigList($gdtConfigList) {
        if(empty($gdtConfigList)){
            return $gdtConfigList;
        }
        $appList  = MobgiSpm_Service_DeliveryModel::getDeliveryApp();
        $appMap = Common::resetKey( $appList, 'app_id' );
        foreach($gdtConfigList as $key => $value){
            $gdtConfigList[$key]['app_name'] = isset($appMap[$value['monitor_app_id']]) ? $appMap[$value['monitor_app_id']]['app_name'] : '未知应用';
        }
        return $gdtConfigList;
    }

    public static function addGdtConfig($params){
        $params['create_time'] = $params['update_time'] = time();
        $dao = self::getSpmDao('GdtConfig');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function updateGdtConfig($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('GdtConfig');
        return $dao->updateBy($data, $params);
    }

    public static function getGdtPayConfigList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('GdtPayConfig');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatGdtPayConfigList($gdtPayConfigList) {
        if(empty($gdtPayConfigList)){
            return $gdtPayConfigList;
        }
        foreach($gdtPayConfigList as $key => $value){
            $gdtPayConfigList[$key]['update_time'] = date('Y-m-d H:i', $value['update_time']);
        }
        return $gdtPayConfigList;
    }

    public static function addGdtPayConfig($params){
        $params['create_time'] = $params['update_time'] = time();
        $dao = self::getSpmDao('GdtPayConfig');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function updateGdtPayConfig($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('GdtPayConfig');
        return $dao->updateBy($data, $params);
    }


    public static function getGdtActionConfigList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('GdtActionConfig');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatGdtActionConfigList($gdtActionConfigList) {
        if(empty($gdtActionConfigList)){
            return $gdtActionConfigList;
        }
        foreach($gdtActionConfigList as $key => $value){
            $gdtActionConfigList[$key]['code_time'] = date('Y-m-d H:i', $value['code_time']);
            $gdtActionConfigList[$key]['token_time'] = date('Y-m-d H:i', $value['token_time']);
            $gdtActionConfigList[$key]['update_time'] = date('Y-m-d H:i', $value['update_time']);
        }
        return $gdtActionConfigList;
    }

    public static function addGdtActionConfig($params){
        $params['create_time'] = $params['update_time'] = time();
        $dao = self::getSpmDao('GdtActionConfig');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function updateGdtActionConfig($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('GdtActionConfig');
        return $dao->updateBy($data, $params);
    }

    public static function getBaiduConfigList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('BaiduConfig');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatBaiduConfigList($configList) {
        if(empty($configList)){
            return $configList;
        }
        foreach($configList as $key => $value){
            $configList[$key]['update_time'] = date('Y-m-d H:i', $value['update_time']);
        }
        return $configList;
    }

    public static function getBaiduConfigByUserId($userId){
        $where['user_id'] = $userId;
        $dao = self::getSpmDao('BaiduConfig');
        return $dao->getBy($where);
    }

    public static function addBaiduConfig($params){
        $params['create_time'] = $params['update_time'] = time();
        $dao = self::getSpmDao('BaiduConfig');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function updateBaiduConfig($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('BaiduConfig');
        return $dao->updateBy($data, $params);
    }

    public static function getPayConfigIdsByName($Name){
        $dao = self::getSpmDao('MonitorPayChannel');
        $result1 = $dao->getsBy( array('channel_no'=>array('LIKE', trim($Name))));
        $keys1 = array_keys(Common::resetKey($result1, 'id'));
        $result2 = $dao->getsBy( array('channel_name'=>array('LIKE', trim($Name))));
        $keys2 =  array_keys(Common::resetKey($result2, 'id'));;
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

    public static function getPayConfigList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('MonitorPayChannel');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatPayConfigList($configList) {
        if(empty($configList)){
            return $configList;
        }
        foreach($configList as $key => $value){
            $configList[$key]['expression'] = '原始金额 * ' . $value['uptime'] . ' / ' . $value['downtime'] . ' + ' . $value['addval'];
            $configList[$key]['update_time'] = date('Y-m-d H:i', $value['update_time']);
        }
        return $configList;
    }

    public static function getPayConfigByChannelId($channelId, $id){
        $where['channel_id'] = $channelId;
        if($id != 0){
            $where['id'] = array('<>' , $id );
        }
        $dao = self::getSpmDao('MonitorPayChannel');
        return $dao->getBy($where);
    }

    public static function addPayConfig($params){
        $params['create_time'] = $params['update_time'] = time();
        $dao = self::getSpmDao('MonitorPayChannel');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function updatePayConfig($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorPayChannel');
        return $dao->updateBy($data, $params);
    }

    public static function getChannelGroupSpconfig($group_id){
        $params['group_id']=$group_id;
        $dao = self::getSpmDao('MonitorChannelGroupSpconfig');
        return $dao->getsBy($params);
    }

    public static function getAllChannelGroupSpconfig(){
        $dao = self::getSpmDao('MonitorChannelGroupSpconfig');
        return $dao->getAll();
    }

}