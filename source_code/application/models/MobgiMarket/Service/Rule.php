<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: matt.liu
 * Date: 2018/3/19
 * Time: 14:48
 */
class MobgiMarket_Service_RuleModel extends MobgiMarket_Service_BaseModel{

    public static function getReceiverMap($where = NULL){
        if(empty($where)){
            $list = self::getMarketDao('RuleReceiver')->getAll();
        }else{
            $list = self::getMarketDao('RuleReceiver')->getsBy($where);
        }
        $map = array();
        foreach ($list as $key=>$val){
            $map[$val['id']] = $val['name'];
        }
        return $map;
    }

}