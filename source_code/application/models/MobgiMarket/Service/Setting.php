<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/19
 * Time: 14:48
 */
class MobgiMarket_Service_SettingModel extends MobgiMarket_Service_BaseModel{

    public static function getUserAuthByParams($params)
    {
        $dao = self::getMarketDao('UserAuth');
        return $dao->getBy($params);
    }

    public static function getUserAuthsByParams($params)
    {
        $dao = self::getMarketDao('UserAuth');
        return $dao->getsBy($params);
    }

    public static function addUserAuth($params){
        $params['status'] = 'OFF';
        $params['message'] = '待授权';
        $params['update_time'] = date('Y-m-d H:i:s');
        $keyMap = array(
            'user_id',
            'account_id',
            'account_name',
            'qq',
            'message',
            'state',
            'client_id',
            'client_secret',
            'authorization_code',
            'code_time',
            'access_token',
            'token_time',
            'refresh_token',
            'access_token_expires_in',
            'refresh_token_expires_in',
            'update_time',
        );
        foreach($keyMap as $value){
            if(isset($params[$value])){
                $data[$value] = $params[$value];
            }
        }
        $dao = self::getMarketDao('UserAuth');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function updateUserAuth($data, $params){
        $data['update_time'] = date('Y-m-d H:i:s');
        $dao = self::getMarketDao('UserAuth');
        return $dao->updateBy($data, $params);
    }

    public static function getProductsByParams($params)
    {
        $dao = self::getMarketDao('Product');
        return $dao->getsBy($params);
    }

    public static function addProduct($params){
        $params['update_time'] = date('Y-m-d H:i:s');
        $keyMap = array(
            'account_id',
            'product_refs_id',
            'product_name',
            'product_type',
            'product_url',
            'update_time',
        );
        foreach($keyMap as $value){
            if(isset($params[$value])){
                $data[$value] = $params[$value];
            }
        }
        $dao = self::getMarketDao('Product');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }

    public static function getProductList($page = 1, $limit = 10, $params = array(), $orderBy = array('id' => 'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $dao = self::getMarketDao('Product');
        $ret = $dao->getList($start, $limit, $params, $orderBy);
        $total = $dao->count($params);
        return array($total, $ret);
    }

}