<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: matt.liu
 * Date: 2017/12/27
 * Time: 17:33
 */
class MobgiSpm_Service_MaterialModel extends MobgiSpm_Service_BaseModel{


   public static function saveMaterialData($data){
       $flag = true;
       foreach ($data as $item){
           if(!self::getSpmDao('MaterialList')->insert($item)){
               $flag = false;
           }
       }
       return $flag;
   }

   public static function getLabelMap($level = true){
       if($level){
           $where['pid'] = 0;
           $list = self::getSpmDao('MaterialLabels')->getsBy($where);
       }else{
           $list = self::getSpmDao('MaterialLabels')->getAll();
       }
       $data = array();
       foreach ($list as $key=>$val){
            $data[$val['id']] = $val['title'];
       }
       return $data;
   }

   public static function get2LevelLabel(){
       $where['pid'] = array('!=',0);
       $list = self::getSpmDao('MaterialLabels')->getsBy($where);
       $data = array();
       foreach ($list as $key=>$val){
           $data[$val['id']] = $val['title'];
       }
       return $data;
   }

   public static function getMaterialGroups($app_id){
       $where['app_id'] =$app_id;
       $list = self::getSpmDao('MaterialGroup')->getsBy($where);
       $data = array();
       foreach ($list as $key=>$val){
           $data[$val['id']] = $val['name'];
       }
       return $data;
   }


   public static function getMaterialGroupName($group_id){
       $group = self::getSpmDao('MaterialGroup')->get($group_id);
       return $group['name'];
   }

   public static function getMaterialList($app_id){
       $where['app_id'] =$app_id;
       $list = self::getSpmDao('MaterialList')->getsBy($where);
       $data = array();
       foreach ($list as $key=>$val){
           $data[$val['id']] = $val['name'];
       }
       return $data;
   }

   public static function checkRepeatFile($app_id,$md5){
       $where['app_id'] = $app_id;
       $where['signature'] = $md5;
       return self::getSpmDao('MaterialList')->getBy($where);
   }

}
