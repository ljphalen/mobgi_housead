<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/1/8
 * Time: 14:26
 */
class MobgiSpm_Service_BrightHouseModel extends MobgiSpm_Service_BaseModel
{

    public static function getChannelLabel()
    {
        $dao = self::getSpmDao('MonitorChannelLabel');
        $fileds = 'id,name';
        $result = $dao->getAllByFields($fileds);
        return $result;
    }

    public static function getAppById($appId){
        $dao = self::getSpmDao('BhMonitorApp');
        return $dao->getBy( array('app_id'=>$appId));
    }

    public static function addApp($data) {
        $data['create_time'] = $data['update_time'] = time();
        $dao = self::getSpmDao('BhMonitorApp');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function updateApp($data, $params) {
        $data['update_time'] = time();
        $dao = self::getSpmDao('BhMonitorApp');
        return $dao->updateBy($data, $params);
    }
}