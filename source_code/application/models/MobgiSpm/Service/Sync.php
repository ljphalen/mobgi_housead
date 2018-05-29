<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/28
 * Time: 16:22
 */
class MobgiSpm_Service_SyncModel extends MobgiSpm_Service_BaseModel{


    public static function getAdApp(){
        $dao = self::getApiDao('AdApp');
        $where['is_track'] = 1;
        $result = $dao->getsBy($where);
        return $result;
    }

    public static function addMonitorApp($params){
        $dao = self::getSpmDao('MonitorApp');
        $dao->insert($params);
        return $dao->getLastInsertId();
    }

    public static function updateMonitorApp($data, $params){
        $data['update_time'] = time();
        $dao = self::getSpmDao('MonitorApp');
        return $dao->updateBy($data, $params);
    }

    public static function syncApp(){
        $adApp = MobgiSpm_Service_DeliveryModel::getAdApp();
        $platformType = array(
            '1' => 'android',
            '2' => 'ios',
        );
        foreach($adApp as $key => $value){
            $monitorApp = self::getAppById($value['app_id']);
            if($monitorApp){
                # update
                $params = array(
                    'app_name' => $value['app_name'],
                    'bundleid' => $value['package_name'],
                    'consumer_key' => $value['consumer_key'],
                    'appstore_id' => $value['appstore_id'],
                    'appstore_url' => $value['apk_url'],
//                    'icon' => $value['icon'],
                    'ledou_gameid' => $value['out_game_id'],
                    'delivery_type' => $value['delivery_type'],
                    'platform' => $platformType[$value['platform']],
                    'operator' => $value['operator'],
                    'operator' => $value['operator'],
                    'create_time' => $value['create_time'],
                    'update_time' => $value['update_time'],
                );
            }else{
                # insert
                $params = array(
                    'app_id' => $value['app_id'],
                    'app_name' => $value['app_name'],
                    'bundleid' => $value['package_name'],
                    'consumer_key' => $value['consumer_key'],
                    'appstore_id' => $value['appstore_id'],
                    'appstore_url' => $value['apk_url'],
//                    'icon' => $value['icon'],
                    'ledou_gameid' => $value['out_game_id'],
                    'delivery_type' => $value['delivery_type'],
                    'platform' => $platformType[$value['platform']],
                    'operator' => $value['operator'],
                    'operator' => $value['operator'],
                    'create_time' => $value['create_time'],
                    'update_time' => $value['update_time'],
                );
            }
        }
    }

}