<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/5/16
 * Time: 11:02
 */
class MobgiMarket_Service_LogModel extends MobgiMarket_Service_BaseModel{

    public static function addCurlLog($params){
        $keyMap = array(
            'user_id',
            'account_id',
            'resource_name',
            'resource_action',
            'action_method',
        );
        foreach($keyMap as $value){
            if(isset($params[$value])){
                $data[$value] = $params[$value];
            }
        }
        $dao = self::getMarketDao('CurlLog');
        $dao->insert($data);
        return $dao->getLastInsertId();
    }
}