<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/22
 * Time: 14:59
 */
class MobgiSpm_Service_TrackModel extends MobgiSpm_Service_BaseModel{

    public static function getAppByConsumerKey($consumerKey){
        $dao = self::getSpmDao('MonitorApp');
        $fileds = 'app_id,appstore_id';
        $where['consumer_key'] = $consumerKey;
        $result = $dao->getAllByFields($fileds, $where);
        return $result;
    }

    public static function getActiveTableName($uidmd5){
        $tableId = crc32($uidmd5);
        $dao = self::getSpmDao('Active',$tableId);
        return $dao->getTableName();
    }

    public static function getActiveByIdConsumerKey($uidmd5, $consumerKey){
        $tableId = crc32($uidmd5);
        $dao = self::getSpmDao('Active',$tableId);
        $where['idfamd5'] = $uidmd5;
        $where['consumer_key'] = $consumerKey;
        $result = $dao->getBy($where);
        return $result;
    }

    public static function updateActiveData($uidmd5, $data, $params){
        $tableId = crc32($uidmd5);
        $dao = self::getSpmDao('Active',$tableId);
        return $dao->updateBy($data, $params);
    }

    public static function getUdidByIdConsumerKey($udidmd5, $consumerKey){
        $tableId = crc32($udidmd5);
        $dao = self::getSpmDao('Udid',$tableId);
        $where['udidmd5'] = $udidmd5;
        $where['consumer_key'] = $consumerKey;
        $result = $dao->getBy($where);
        return $result;
    }

    public static function getAdidByIdConsumerKey($adidmd5, $consumerKey){
        $tableId = crc32($adidmd5);
        $dao = self::getSpmDao('Adid',$tableId);
        $where['adidmd5'] = $adidmd5;
        $where['consumer_key'] = $consumerKey;
        $result = $dao->getBy($where);
        return $result;
    }

    public static function updateAdidData($adidmd5, $data, $params){
        $tableId = crc32($adidmd5);
        $dao = self::getSpmDao('Adid',$tableId);
        return $dao->updateBy($data, $params);
    }

    public static function getClickData($where, $orderBy = array('clicktime' => 'DESC')){
        $dao = self::getSpmDao('Click');
        $result = $dao->getBy($where, $orderBy);
        return $result;
    }

    public static function updateClickData($data, $params){
        $dao = self::getSpmDao('Click');
        return $dao->updateBy($data, $params);
    }

    public static function addActiveData($data,$uidmd5){
        $tableId = crc32($uidmd5);
        $dao = self::getSpmDao('Active',$tableId);
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function addUdidData($data,$udidmd5){
        $tableId = crc32($udidmd5);
        $dao = self::getSpmDao('Udid',$tableId);
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function addAdidData($data,$adidmd5){
        $tableId = crc32($adidmd5);
        $dao = self::getSpmDao('Adid',$tableId);
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function getGdtConfigByAppAdvertiser($appId, $advertiserId){
        $dao = self::getSpmDao('GdtConfig');
        $where['app_id'] = $appId;
        $where['advertiser_id'] = $advertiserId;
        $result = $dao->getby($where);
        return $result;
    }

    public static function getBackFlowData($uidmd5,$consumerKey,$backFlowDate){
        $dao = self::getSpmDao('BackFlow');
        $where['idfamd5'] = $uidmd5;
        $where['consumer_key'] = $consumerKey;
        $where['backflow_date'] = $backFlowDate;
        $result = $dao->getby($where);
        return $result;
    }
}