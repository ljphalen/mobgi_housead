<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author matt.liu
 *
 */

class MobgiData_Service_ThirdApiModel extends MobgiData_Service_BaseModel {



    /**
     *
     * 添加导入操作日志
     */
    public static function saveImportLog($ads,$content,$status,$username,$createtime){
        $data = array(
            'identifier'=>$ads,
            'event'=>$content,
            'status'=>$status,
            'createtime'=>$createtime,
            'username'=>$username,
        );
        return self::getDao('ReportImportLog')->insert($data);
    }


    public static function updateAdjustAdincome($data){
        $days = date('t', strtotime($data['date']));
        $start_day = $data['date'].'-01';
        $end_day = $data['date'].'-'.$days;
        $where['days'] = array(
            array(">=",$start_day),
            array("<=",$end_day)
        );
        $where['ads_id'] = $data['ads_id'];
        $where['third_views'] = array('!=',0);
        $list = self::getDao('ReportApi')->getsBy($where);
        $count = self::getDao('ReportApi')->sum('third_views',$where);
        foreach ($list as $key=>$val){
            $adjustincome = number_format(($val['third_views']/$count)*$data['amount'],3);
            $check = self::getDao('ReportApi')->updateBy(array('ad_income_adjust'=>$adjustincome),array('id'=>$val['id']));
            if(!$check){
                echo 'error';
                die();
            }
        }
    }
}
