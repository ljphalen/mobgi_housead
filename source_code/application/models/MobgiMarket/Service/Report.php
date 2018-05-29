<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: matt.liu
 * Date: 2018/3/19
 * Time: 14:48
 */
class MobgiMarket_Service_ReportModel extends MobgiMarket_Service_BaseModel{

    public static function getAdsCacheMap($params){
        if(empty($params)) return false;
        $info = self::getMarketDao('Ad')->getsBy($params);
        return $info;
    }

    public static function getProductCacheMap($params){
        if(empty($params)) return false;
        $info = self::getMarketDao('Product')->getsBy($params);
        return $info;
    }

    public static function getAdGroupCacheMap($params){
        if(empty($params)) return false;
        $info = self::getMarketDao('AdGroup')->getsBy($params);
        return $info;
    }

    public static function getCampaignCacheMap($params){
        if(empty($params)) return false;
        $info = self::getMarketDao('Campaign')->getsBy($params);
        return $info;
    }

    public static function getAdCreattivesCacheMap($params){
        if(empty($params)) return false;
        $info = self::getMarketDao('AdCreative')->getsBy($params);
        return $info;
    }



}