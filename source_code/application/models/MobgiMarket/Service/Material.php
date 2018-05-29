<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/21
 * Time: 20:23
 */
class MobgiMarket_Service_MaterialModel extends MobgiMarket_Service_BaseModel{

    public static function getImageBySignature($accountId, $signature)
    {
        $dao = self::getMarketDao('Image');
        $where['account_id'] = $accountId;
        $where['signature'] = $signature;
        $result = $dao->getBy($where);
        return $result;
    }

    public static function addImage($params){
        $params['update_time'] = date('Y-m-d H:i:s');
        $keyMap = array(
            'account_id',
            'image_id',
            'signature',
            'width',
            'height',
            'file_size',
            'type',
            'preview_url',
        );
        foreach($keyMap as $value){
            if(isset($params[$value])){
                $data[$value] = $params[$value];
            }
        }
        $dao = self::getMarketDao('Image');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function getVideoBySignature($accountId, $signature)
    {
        $dao = self::getMarketDao('Video');
        $where['account_id'] = $accountId;
        $where['signature'] = $signature;
        $result = $dao->getBy($where);
        return $result;
    }

    public static function addVideo($params){
        $params['update_time'] = date('Y-m-d H:i:s');
        $keyMap = array(
            'account_id',
            'video_id',
            'signature',
            'width',
            'height',
            'file_size',
            'type',
            'preview_url',
        );
        foreach($keyMap as $value){
            if(isset($params[$value])){
                $data[$value] = $params[$value];
            }
        }
        $dao = self::getMarketDao('Video');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }
}