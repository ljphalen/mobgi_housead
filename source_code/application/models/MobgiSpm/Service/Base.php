<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/22
 * Time: 14:35
 */
class MobgiSpm_Service_BaseModel extends Common_Service_Base{

    public static function getActivityByShortKey($shortKey)
    {
        $dao = self::getSpmDao('MonitorActivity');
        $where['short_link'] = $shortKey;
        $result = $dao->getBy($where);
        return $result;
    }

    public static function getActivityById($acid)
    {
        $dao = self::getSpmDao('MonitorActivity');
        $result = $dao->get($acid);
        return $result;
    }

    public static function getActivityGroupById($id)
    {
        $dao = self::getSpmDao('MonitorActivityGroup');
        $result = $dao->get($id);
        return $result;
    }

    public static function getAdAppById($id){
        $dao = self::getApiDao('AdApp');
        $result = $dao->get($id);
        return $result;
    }

    public static function getAppById($id){
        $dao = self::getSpmDao('MonitorApp');
        $result = $dao->get($id);
        return $result;
    }

    public static function getAppDetailById($id){
        $dao = self::getSpmDao('MonitorAppDetail');
        $result = $dao->get($id);
        return $result;
    }

    public static function getMonitorPlatformById($id){
        $dao = self::getSpmDao('MonitorPlatform');
        $result = $dao->get($id);
        return $result;
    }

    public static function getChannelById($id){
        $dao = self::getSpmDao('MonitorChannel');
        $result = $dao->get($id);
        return $result;
    }

    public static function getChannelDetailById($id){
        $dao = self::getSpmDao('MonitorChannelDetail');
        $result = $dao->get($id);
        return $result;
    }

    public static function getChannelGroupById($id){
        $dao = self::getSpmDao('MonitorChannelGroup');
        $result = $dao->get($id);
        return $result;
    }

    public static function getChannelLabelById($id){
        $dao = self::getSpmDao('MonitorChannelLabel');
        $result = $dao->get($id);
        return $result;
    }

    public static function getAndroidChannelGroupById($id){
        $dao = self::getSpmDao('MonitorAndroidChannelGroup');
        $result = $dao->get($id);
        return $result;
    }

    public static function getGdtConfigById($id){
        $dao = self::getSpmDao('GdtConfig');
        $result = $dao->get($id);
        return $result;
    }

    public static function getGdtPayConfigById($id){
        $dao = self::getSpmDao('GdtPayConfig');
        $result = $dao->get($id);
        return $result;
    }

    public static function getGdtActionConfigById($id){
        $dao = self::getSpmDao('GdtActionConfig');
        $result = $dao->get($id);
        return $result;
    }

    public static function getBaiduConfigById($id){
        $dao = self::getSpmDao('BaiduConfig');
        $result = $dao->get($id);
        return $result;
    }

    public static function getPayConfigById($id){
        $dao = self::getSpmDao('MonitorPayChannel');
        $result = $dao->get($id);
        return $result;
    }

    public static function getAppsflyerAppById($id){
        $dao = self::getSpmDao('AppsflyerConfig');
        $result = $dao->get($id);
        return $result;
    }

    public static function getMonitorProcessById($id){
        $dao = self::getSpmDao('MonitorProcess');
        $result = $dao->get($id);
        return $result;
    }

    public static function getMonitorDirectoryById($id){
        $dao = self::getSpmDao('MonitorDirectory');
        $result = $dao->get($id);
        return $result;
    }

    public static function getMonitorScriptAlarmById($id){
        $dao = self::getSpmDao('MonitorScriptAlarm');
        $result = $dao->get($id);
        return $result;
    }

    public static function getMonitorDocumentById($id){
        $dao = self::getSpmDao('MonitorDocument');
        $result = $dao->get($id);
        return $result;
    }

    public static function getMonitorDocumentTypeById($id){
        $dao = self::getSpmDao('MonitorDocumentType');
        $result = $dao->get($id);
        return $result;
    }

    public static function getActiveId($tableId,$id){
        $dao = self::getSpmDao('Active',$tableId);
        $result = $dao->get($id);
        return $result;
    }

    public static function getUdidId($tableId,$id){
        $dao = self::getSpmDao('Udid',$tableId);
        $result = $dao->get($id);
        return $result;
    }
}