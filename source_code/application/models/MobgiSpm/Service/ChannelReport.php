<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/1
 * Time: 17:58
 */
class MobgiSpm_Service_ChannelReportModel extends MobgiSpm_Service_BaseModel {

    public static function getChannelReportByParams($params, $orderBy = array()){
        $dao = self::getSpmDao('externalChannelReport');
        $fileds = 'id,consumer_key,activity_id,date_of_log,clicks,effect_clicks,actives,callbacks,registers';
        $result = $dao->getAllByFields($fileds, $params, $orderBy);
        return $result;
    }

    public static function getPackageReportByParams($params, $orderBy = array()){
        $dao = self::getSpmDao('externalPackageReport');
        $fileds = 'id,channel_no,date_of_log,registers';
        $result = $dao->getAllByFields($fileds, $params, $orderBy);
        return $result;
    }

    public static function getConsumerKeyList($appIdArr){
        if(empty($appIdArr)){
            return array();
        }
        $appList = MobgiSpm_Service_DeliveryModel::getDeliveryAppsByParams( array('app_id'=>array('IN',$appIdArr)) );
        $consumerKeylist = array_column($appList, 'consumer_key');
        return $consumerKeylist;
    }

    public static function dimValueReplace(&$tmp, $dim, $map){
        if (empty($tmp)) {
            return;
        }
        if (in_array('consumer_key', $dim)) {
            if(!empty($map['consumer_key'])){
                $relateApp = MobgiSpm_Service_DeliveryModel::getDeliveryAppsByParams( array('app_id'=>array('IN',$map['consumer_key'])) );
                $map['consumer_key'] = Common::resetKeyValue($relateApp, 'consumer_key', 'app_name');
            }
        }
        if (in_array('activity_id', $dim)) {
            if(!empty($map['activity_id'])){
                $relateActivity = MobgiSpm_Service_DeliveryModel::getDeliveryActivitysByParams( array('id'=>array('IN',$map['activity_id'])) );
                $map['activity_id'] = Common::resetKeyValue($relateActivity, 'id', 'name');
            }
        }
        if (in_array('channel_no', $dim)) {
            if(!empty($map['channel_no'])){
                $relateAndroidChannel = MobgiSpm_Service_ChannelModel::getAndroidChannelsByParams( array('channel_no'=>array('IN',$map['channel_no'])) );
                $map['channel_no'] = Common::resetKeyValue($relateAndroidChannel, 'channel_no', 'channel_name');
            }
        }
        foreach ($tmp as $tmpKey => $tmpVal) {
            foreach ($dim as $dimVal) {
                if (isset($tmpVal[$dimVal])) {
                    // 没有该维度的映射数据则删除
                    if (isset($map[$dimVal][$tmpVal[$dimVal]])) {
                        $tmp[$tmpKey][$dimVal] = $map[$dimVal][$tmpVal[$dimVal]];
                    } else {
                        unset($tmp[$tmpKey]);
                        break;
                    }
                }
            }
        }
        $tmp = array_values($tmp);
    }

    public static function calRateFunnelForData(&$tmp) {
        if(count($tmp) == count($tmp, 1)){
            if($tmp['effect_clicks'] != 0){
                $tmp['active_rate'] = round($tmp['actives'] / $tmp['effect_clicks'] * 100, 2);
            }else{
                $tmp['active_rate'] = 0;
            }
        }else{
            foreach ($tmp as $k => &$v) {
                if($v['effect_clicks'] != 0){
                    $v['active_rate'] = round($v['actives'] / $v['effect_clicks'] * 100, 2);
                }else{
                    $v['active_rate'] = 0;
                }
            }
        }
    }

    public static function totalCaculate($tmp, $calKey, $catRateType = true) {
        $result = array();
        if (empty($tmp)) {
            return $result;
        }
        // 普通数据字段叠加
        foreach ($tmp as $key => $value) {
            foreach ($calKey as $k => $v) {
                if (isset($value[$v])) $result[$v] += $value[$v];
            }
        }
        if($catRateType){
            self::calRateFunnelForData($result);
        }
        return $result;
    }

}