<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/27
 * Time: 17:33
 */
class MobgiSpm_Service_AbroadModel extends MobgiSpm_Service_BaseModel{

    public static function getAppsflyerAppList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $limit = ($limit == 0) ? 10 : $limit;
        $start = ($page - 1) * $limit;
        $dao = self::getSpmDao('AppsflyerConfig');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

    public static function formatAppsflyerAppList($List) {
        if(empty($List)){
            return $List;
        }
        foreach($List as $key => $value){
            $List[$key]['update_time'] = date('Y-m-d H:i', $value['update_time']);
        }
        return $List;
    }

    public static function getAppsflyerAppByAppId($appId, $appsflyerAppId, $id){
        if($id != 0){
            $where['id'] = array('<>' , $id );
        }
        $where['app_id'] = $appId;
        $where['appsflyer_appid'] = $appsflyerAppId;
        $dao = self::getSpmDao('AppsflyerConfig');
        return $dao->getBy($where);
    }

    public static function addAppsflyerApp($params){
        $params['create_time'] = $params['update_time'] = time();
        $dao = self::getSpmDao('AppsflyerConfig');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function updateAppsflyerApp($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('AppsflyerConfig');
        return $dao->updateBy($data, $params);
    }

    public static function getDeliveryApp(){
        $dao = self::getSpmDao('MonitorApp');
        $fileds = 'app_id,app_name,consumer_key,appstore_id,appstore_url,delivery_type';
        $where = [];
        $result = $dao->getAllByFields($fileds, $where);
        return $result;
    }

    public static function getDeliveryChannel($params = array()){
        $dao = self::getSpmDao('MonitorChannel');
        $fileds = 'id,channel_name,group_id';
        $params['delivery_type'] = 0;
        $result = $dao->getAllByFields($fileds, $params);
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
        if(empty($activityList)){
            return $activityList;
        }
        $appList  = self::getDeliveryApp();
        $appMap = Common::resetKey( $appList, 'app_id' );
        $channelList  = self::getDeliveryChannel();
        $channelMap = Common::resetKey( $channelList, 'id' );
        foreach($activityList as $key => $value){
            $activityList[$key]['create_time'] = date('Y-m-d H:i', $value['create_time']);
            $activityList[$key]['id_sign'] = '-N' . $value['id'];
            $activityList[$key]['app_name'] = isset($appMap[$value['app_id']]) ? $appMap[$value['app_id']]['app_name'] : '未知应用';
            $activityList[$key]['channel_name'] = isset($channelMap[$value['channel_id']]) ? $channelMap[$value['channel_id']]['channel_name'] : '未知渠道';
        }
        return $activityList;
    }

}
