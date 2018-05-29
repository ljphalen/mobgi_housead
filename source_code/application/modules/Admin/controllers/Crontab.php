<?php
/**
 * Created by PhpStorm.
 * User: matt.liu
 * Date: 2017/8/7
 * Time: 15:29
 */

#配置变更检测
#监控API表进行数据变更监控

class CrontabController extends Common_BaseController {

    private static $synType = array(
        'app',
        'ads',
        'ad_pos',
        'ads_app_id',
        'third_pos',
        'channel',
    );
    private static $userType = array(
        'system_auto',
        'system_change_auto',
    );

    /**
     *
     * 每日日常同步机制
     */
    public function normalAutoSynAction() {
        foreach (self::$synType as $type){
            MobgiData_Service_SynModel::sync_auto($type,self::$userType[0]);
        }
        exit();
    }

    /**
     *
     * 特殊更改同步机制
     */
    public function changeAutoSynAction() {
        $sql = "select type from `api_change_log` where status = 0 GROUP by type";
        $result = MobgiData_Service_SynModel::getDao('ApiChangeLog')->fetcthAll($sql);
        if(!empty($result)){
            foreach ($result as $key=>$val){
                if(in_array($val['type'],self::$synType)){
                    MobgiData_Service_SynModel::sync_auto($val['type'],self::$userType[1]);
                    //置空status位置
                    $data = array('status'=>1);
                    $where = array('type'=>$val['type']);
                    MobgiData_Service_SynModel::getDao('ApiChangeLog')->updateBy($data,$where);
                }else{
                    continue;
                }
            }
        }else{
            exit();
        }
        exit();
    }
}
