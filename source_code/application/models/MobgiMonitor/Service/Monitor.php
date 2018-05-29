<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author matt.liu
 *
 */

class MobgiMonitor_Service_MonitorModel extends MobgiMonitor_Service_BaseModel {

        public static function getYesterdayValue($day,$appkey,$userid){
            $params =array(
                'sdate'=>$day,
                'edate'=>$day,
                'dims'=>['days','app_key'],
                'app_key'=>array($appkey),
                'theader'=>['third_ecpm','user_view_count','user_dau']
            );
            return MobgiData_Service_MobgiModel::getData($params,$userid);
        }

        public static function ajaxReturn($status,$msg){
            if(empty($status)&&empty($msg)) return false;
            $message['status'] = $status;
            $message['msg'] = $msg;
            echo json_encode($message);
            exit();
        }


        public static function showMonitorDetailAction($logId){
            $log = self::getMonitorDao('MonitorTaskLog')->getBy(array('id'=>$logId));
            $taskSetting = self::getMonitorDao('MonitorTask')->getBy(array('id'=>$log['task_id']));
            $temp = json_decode($taskSetting['params'],true);
            if($temp['monitor_type'] == 'ecpm'){
                #ecpm值区分广告类型
                $info = "最小ecpm值:".$temp['ecpm_min_val'].'<br>'."广告类型:".Common_Service_Config::AD_TYPE[$temp['ad_type']];
            }else{
                $info = "阀值:".$temp['limit_val'];
            }
           return $info;
        }

        //计算次数
        public static function countMonitorType($list = array()){
            if (!is_array($list)) return [];
            $ecpmCount=$capitalCount=$actCount=$all=$doublecheckCount=0;
            foreach ($list as $key=>$val){
                if($val['params']['monitor_type'] == 'ecpm'){
                    $ecpmCount++;
                }else if ($val['params']['monitor_type'] == 'impressions'){
                    $capitalCount++;
                }else if ($val['params']['monitor_type'] == 'dau'){
                    $actCount++;
                }else if($val['params']['monitor_type'] == 'doublecheck'){
                    $doublecheckCount++;
                }
                $all++;
            }
            $count = array(
                'all'=>$all,
                'ecpmCount'=>$ecpmCount,
                'capitalCount'=>$capitalCount,
                'actCount'=>$actCount,
                'doublecheckCount'=>$doublecheckCount,
            );
            return $count;
        }

    //计算某个appkey报警程度次数
    public static function countMonitorLogType($list = array(),$appkey,$level){
        if (!is_array($list)) return [];
        $count = 0;
        foreach ($list as $key=>$val) {
            $temp = json_decode($val['info'],true);
            if($temp['app_key'] == $appkey && $level == $val['warming_level']){
                $count++;
            }
        }
        return $count;
    }

    //处理以及过滤paramsjson参数
    public static function dealParams($list = array()){
        foreach ($list as $key=>&$val){
            switch ($val['monitor_type']){
                case 'dau':$dao = 'MonitorDau';break;
                case 'impressions':$dao = 'MonitorImpressions';break;
                case 'ecpm':$dao = 'MonitorEcpm';break;
            }
            $val['log'] = self::getTaskLogDetail($val['log_id'],$dao);
            $val['desc'] = self::getDesc($val['task_id']);#获取desc
        }
        foreach ($list as $keys =>&$vals){
            if($vals['monitor_type'] != 'ecpm'){
                $vals['log']['value'] = intval(pow(M_E,$vals['log']['value']));
                $vals['log']['predict'] = intval(pow(M_E,$vals['log']['predict']));
                $vals['log']['upper'] = intval(pow(M_E,$vals['log']['upper']));
                $vals['log']['lower'] = intval(pow(M_E,$vals['log']['lower']));
            }
        }
        return $list;
    }

    //获取desc描述
    public static function getDesc($taskId){
        $config = self::getTaskDetail($taskId,true);
        $channelMap = MobgiData_Service_BaseModel::getChannels();
        $adTypeMap = Common_Service_Config::AD_TYPE;
        $posKeyMap = MobgiData_Service_BaseModel::getPosKeyMap();
        switch ($config['monitor_type']){
            case 'dau':$desc = '渠道:'.$channelMap[$config['channel_gid']];break;
            case 'impressions':$desc = '广告位:'.$posKeyMap[$config['pos_key']];break;
            case 'ecpm':$desc = '广告类型:'.$adTypeMap[$config['ad_type']];break;
        }
        return $desc;
    }

    //获取任务每天跑的详情
    public static function getTaskLogDetail($logId,$dao){
       return MobgiMonitor_Service_MonitorModel::getMonitorDao($dao)->get($logId);
    }

    //获取任务所有的详情
    public static function getTaskAllLog($dao,$params){
        return MobgiMonitor_Service_MonitorModel::getMonitorDao($dao)->getsBy($params);
    }

    //获取任务log
    public static function getTaskLog($type,$where){
        switch ($type){
            case 'dau':$dao = 'MonitorDau';break;
            case 'impressions':$dao = 'MonitorImpressions';break;
            case 'ecpm':$dao = 'MonitorEcpm';break;
        }
        return self::getTaskAllLog($dao,$where);
    }



    //获取任务详情
    public static function getTaskDetail($taskId,$needConfig=false){
        $taskInfo = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorTask')->get($taskId);
        $config = json_decode($taskInfo['params'],true);
        if($needConfig){
            return $config;
        }else{
            return $taskInfo;
        }
    }


    //获取所有任务
    public static function getAllTask($params = null){
        if($params){
            $tasks = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorTask')->getsBy($params);
        }else{
            $tasks = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorTask')->getAll();
        }

        foreach ($tasks as $key=>&$val){
            $val['params'] = json_decode($val['params'],true);
        }
        return $tasks;
    }
}
