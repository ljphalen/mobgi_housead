<?php

/**
 * Created by PhpStorm.
 * User: matt.liu
 * Date: 2017/7/28
 * Time: 10:46
 */
class Monitor_ReportController extends Admin_BaseController {

   /*
    * $params = array(
            'appKey'=>'C4E37EFB26F986D67D72',
            'monitor_type'=>1,#检测项目 1，ecpm 2,活跃用户，3，人均展示
            'ad_type'=>0,#广告类型，如果ecpm值会有广告类型存在，0代表所有。默认0
            'value'=>123456,#阀值
        );
        报警等级,0:严重,1:中等,2:轻微,3:正常
    */

    public $actions = array(
        'IndexUrl' => '/Admin/Monitor_Report/index',
        'DetailsUrl' => '/Admin/Monitor_Report/details',
        'AddMonitorUrl' => '/Admin/Monitor_Report/addMonitor',
        'showMonitorLogUrl'=>'/Admin/Monitor_Report/showMonitorLog',
        'UsergroupListUrl'=>'/Admin/Monitor_Report/userGroupList',
        'AddUserUrl'=>'/Admin/Monitor_Report/addUser',
        'DeleteMonitor'=>'/Admin/Monitor_Report/deleteMonitor',
        'AddMonitorGroup'=>'/Admin/Monitor_Report/addMonitorGroup',
        'DelMonitorGroup'=>'/Admin/Monitor_Report/delMonitorGroup',
        'EditMonitorGroup'=>'/Admin/Monitor_Report/editMonitorGroup',
        'UserListUrl'=>'/Admin/Monitor_Report/UserList',
        'ChangeMonitorStatus'=>'/Admin/Monitor_Report/changeMonitorStatus',
        'DealLog'=>'/Admin/Monitor_Report/dealLog',
        'ShowMonitorDetailUrl'=>'/Admin/Monitor_Report/showMonitorDetail',
        'DeleteUser'=>'/Admin/Monitor_Report/deleteUser',
        'GlobalSetUrl'=>'/Admin/Monitor_Report/globalSet',
        'TableShowUrl'=>'/Admin/Data_Report/mobgi',
        'ScriptListUrl'=>'/Admin/Monitor_Report/scriptlist',
    );

    public $perpage = 20;

    /**
     * 数据预警概览
     */
    public function indexAction() {
        $appkey = $this->getGet('app_key');
        $taskLogList =MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorTaskLog')->getsBy(array('is_deal'=>0),array('days'=>'desc'));
        $taskParamsLoglist = MobgiMonitor_Service_MonitorModel::dealParams($taskLogList);
        $taskList = MobgiMonitor_Service_MonitorModel::getAllTask();
        $countList = MobgiMonitor_Service_MonitorModel::countMonitorType($taskList);
        $data = array(
            'appCount'=>$countList['all'],
            'perPeopleCount'=>$countList['capitalCount'],
            'ecpmCount'=>$countList['ecpmCount'],
            'actCount'=>$countList['actCount'],
            'doublecheckCount'=>$countList['doublecheckCount']
        );
        #获得已经监控的所遇appkey
        $appMap = MobgiData_Service_BaseModel::getAppKeyMap();
        $appkeyExists = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorTask')->getsBy(array('type'=>2));
        foreach ($appMap as $key=>$val){
            foreach ($appkeyExists as $keys=>$vals){
                $temp = json_decode($vals['params'],true);
                if($temp['app_key'] == $key){
                    $appMapFilter[$key] = $val;
                }
            }
        }

        $this->assign('reportList',$taskParamsLoglist);
        $this->assign('appkey',$appkey);
        $this->assign('applist',$appMapFilter);
        $this->assign('data',$data);
    }


    /*
     * 脚本管理
     */
    public function scriptlistAction(){

    }
    /**
     *  预警处理操作
     *
     */
    public function dealLogAction(){
        $ids = $this->getGet('ids');
        $idArr = explode(',',$ids);
        foreach ($idArr as $key){
            if(!MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorTaskLog')->updateBy(array('is_deal'=>1),array('id'=>$key))){
                echo 0;
            }
        }
        echo 1;
    }

    /**
     *  预警等级高级设定
     *
     */
    public function globalSetAction(){
        if($this->getPost('id')){
            $data = array(
                'float_rate'=>$_POST['float_rate'],
                'n_changepoints'=>$_POST['n_changepoints'],
                'changepoint_prior_scale'=>$_POST['changepoint_prior_scale'],
                'interval_width'=>$_POST['interval_width'],
                'holidays'=>$_POST['holidays'],
                'holidays_prior_scale'=>$_POST['holidays_prior_scale'],
                'periods'=>$_POST['periods'],
                'freq'=>$_POST['freq'],
                'is_open'=>$_POST['is_open'],
                'task_id'=>$_POST['id']
            );

            if(MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorGlobalSet')->getBy(array('task_id'=>$this->getPost('id'))))
                $return = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorGlobalSet')->updateBy($data,array('task_id'=>$this->getPost('id')));
            else
                $return = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorGlobalSet')->insert($data);
            if($return){
                echo 1;
            }else{
                echo 0;
            }
        }else{
            $id = $this->getGet('id');
            $params = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorGlobalSet')->getBy(array('task_id'=>$id));
            $this->assign('task_id',$id);
            $this->assign('params',$params);
        }
    }

    /**
     * 数据预警详情
     */
    public function detailsAction() {
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 0;
        $search= $this->getInput(array('type','search'));
        if(!empty($search['search'])){
            $params['title'] =trim($search['search']);
        }
        $params['type'] = 2;#2代表是业务监控，不要显示系统监控
        $appMap = MobgiData_Service_BaseModel::getAppKeyMap();
        $channelMap = MobgiData_Service_BaseModel::getChannels();
        $posKeyMap = MobgiData_Service_BaseModel::getPosKeyMap();
        $list =MobgiMonitor_Service_MonitorModel::getAllTask($params);
        foreach ($list as $key=>&$val){
            $val['app_name'] = empty($appMap[$val['params']['app_key']])?'所有应用':$appMap[$val['params']['app_key']];
            if($val['params']['channel_gid'] == 0) $channel = "不区分渠道"; else $channel =$channelMap[$val['params']['channel_gid']];
            if($val['params']['pos_key'] == 0) $poskey = "不区分广告位"; else $poskey = $posKeyMap[$val['params']['pos_key']];
            if($val['params']['monitor_type'] == 'ecpm'){
                #ecpm值区分广告类型
                $val['desc'] = "最小ecpm值:".$val['params']['ecpm_min_val'].'<br>'."广告类型:".Common_Service_Config::AD_TYPE[$val['params']['ad_type']];
            }elseif($val['params']['monitor_type'] == 'doublecheck'){
                $val['desc'] = "监控广告商:".$val['params']['ads_id']."<br>数据重跑周期:".$val['params']['time_length'].'天';
            }elseif($val['params']['monitor_type'] == 'dau'){
                $val['desc'] = "渠道:".$channel."<br>监控精度:".$val['period'].'s';
            }elseif($val['params']['monitor_type'] == 'impressions'){
                $val['desc'] = "广告位:".$poskey."<br>监控精度:".$val['period'].'s';
            }
        }

        $total = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorTask')->count($params);
        $url = $this->actions['AdPosSynUrl'].'/?' . http_build_query($search) . '&';
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('total', $total);
        $this->assign('List', $list);
    }



    /*
     * 添加监控
     */
    public function addMonitorAction(){
        if($_POST){
            //基础写入信息
            $baseData = array(
                'title'=>$this->getPost('title'),
                'warning_type'=>empty($this->getPost('warning_type'))?0:$this->getPost('warning_type'),
                'user_id'=>$this->userInfo['user_id'],
                'period'=>empty($this->getPost('period'))?86400:$this->getPost('period'),
                'script_id'=>$this->getPost('script_id'),
                'warning_target'=>empty($this->getPost('gid'))?0:$this->getPost('gid'),
                'create_time'=>date('Y-m-d H:i:s',time()),
                'next_time'=>empty($this->getPost('period'))?date('Y-m-d 00:05:00',time()+84600):date('Y-m-d H:30:00',time()+intval($this->getPost('period'))),
                'warming_period'=>empty($this->getPost('period'))?86400:$this->getPost('period'),
            );
            //附加类别信息写入
            if($this->getPost('monitor_type') == 'ecpm'){
                $TypeData = array(
                    'params'=>json_encode(
                        array(
                            'ad_type'=>$this->getPost('ad_type'),
                            'ecpm_min_val'=>$this->getPost('ecpm_min_val'),
                            'app_key'=>$this->getPost('app_key'),
                            'monitor_type'=>$this->getPost('monitor_type'),
                        )
                    ),
                );
            }else if($this->getPost('monitor_type') == 'doublecheck'){
                $TypeData = array(
                    'params'=>json_encode(
                        array(
                            'ads_id'=>$this->getPost('ads_id'),
                            'monitor_type'=>$this->getPost('monitor_type'),
                            'time_length'=>$this->getPost('time_length'),
                        )
                    ),
                );
            }else if($this->getPost('monitor_type') == 'dau'){
                $TypeData = array(
                    'params' => json_encode(
                        array(
                            'channel_gid' => $this->getPost('channel_gid'),
                            'app_key' => $this->getPost('app_key'),
                            'monitor_type' => $this->getPost('monitor_type'),
                        )
                    ),
                );
            }else{
                $TypeData = array(
                    'params' => json_encode(
                        array(
                            'pos_key' => $this->getPost('pos_key'),
                            'app_key' => $this->getPost('app_key'),
                            'monitor_type' => $this->getPost('monitor_type'),
                        )
                    ),
                );
            }
            $inserData = array_merge($baseData,$TypeData);
            if(MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorTask')->insert($inserData)){
                echo 1;
            }else{
                echo 0;
            }
        }else{
            $adsMap =MobgiData_Service_BaseModel::getAdsIdMap();
            $appMap = MobgiData_Service_BaseModel::getAppKeyMap([],1);
            $adType = Common_Service_Config::AD_TYPE;
            $channel = MobgiData_Service_BaseModel::getChannels();
            $userGroup = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorGroup')->getsBy(array('status'=>1));
            $posKey = MobgiData_Service_BaseModel::getPosKeyMap();
            $scripts = $this->getScriptMap();
            $this->assign('posKey',$posKey);
            $this->assign('scripts',$scripts);
            $this->assign('channel_gid',$channel);
            $this->assign('ads',$adsMap);
            $this->assign('user_group',$userGroup);
            $this->assign('ad_type',$adType);
            $this->assign('app',$appMap);
        }
    }


    /*
     * ajax根据应用找广告位
     */
    public function getAppkeyPosKeyAction(){
        $appKey[] = $this->getPost('app_key');
        $result = MobgiData_Service_BaseModel::getAppPosKeyMap($appKey);
        echo json_encode($result[$this->getPost('app_key')]);
    }

    /*
     * 获取脚本map
     *
     */
    public function getScriptMap(){
        $list = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorScript')->getAll();
        $map = array();
        foreach ($list as $key=>$val){
            $map[$val['id']] = $val['title'];
        }
        return $map;
    }

    /*
     * 检测邮箱是否存在
     */
    public function checkEmail(){
        $email = $this->getGet('email');
        if(MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorUser')->getsBy(array('email'=>$email))){
            return 1;
        }else{
            return 0;
        }
    }

    /*
     * 检测手机号是否存在
     */
    public function checkTel(){
        $tel = $this->getGet('tel');
        if(MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorUser')->getsBy(array('tel'=>$tel))){
            return 1;
        }else{
            return 0;
        }
    }
    /*
     * 监控用户组列表
     */
    public function userGroupListAction(){
        $usergroup = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorGroup')->getList();
        $this->assign('user_group',$usergroup);
    }

    /*
     * 添加用户组
     */
    public function addMonitorGroupAction(){
        if($_POST){
            //检测用户组是否重名
            $check = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorGroup')->getBy(array('group_name'=>trim($_POST['group_name'])));
            if($check){
                MobgiMonitor_Service_MonitorModel::ajaxReturn(0,'已经存在该用户组!');
            }
            $data = array('group_name'=>trim($this->getPost('group_name')),'create_time'=>date('Y-m-d H:i:s',time()));
            if(MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorGroup')->insert($data)){
                MobgiMonitor_Service_MonitorModel::ajaxReturn(1,'添加成功!');
            }else{
                MobgiMonitor_Service_MonitorModel::ajaxReturn(0,'添加失败!');
            }
        }else{
            $usergroup = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorGroup')->getList();
            $this->assign('user_group',$usergroup);
        }
    }

    /*
     * 编辑用户组
     */
    public function editMonitorGroupAction(){
        $usergroup = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorGroup')->getList();
        $this->assign('user_group',$usergroup);
    }

    /*
     * 删除用户组
     */
    public function delMonitorGroupAction(){
        $ids = $this->getGet('ids');
        $idArr = explode(',',$ids);
        if(MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorGroup')->deletes('id',$idArr)){
            echo 1;
        }else{
            echo 0;
        }
    }

    /*
     * 新增监控用户
     */
    public function addUserAction(){
        if($_POST){
            $data = array(
                'user_name'=>$this->getPost('user_name'),
                'tel'=>$this->getPost('tel'),
                'email'=>$this->getPost('email'),
                'create_time'=>date('Y-m-d,H:i:s',time()),
            );
            if(MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorUser')->insert($data)){
                $uid = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorUser')->getLastInsertId();
                #插入关系表
                $linkData = array('uid'=>$uid,'gid'=>$this->getPost('gid'));
                if(MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorUserGroup')->insert($linkData)){
                    echo 1;
                }else{
                    echo 0;
                }
            }else{
                echo 0;
            }
        }else{
            $gid = $this->getGet('gid');
            if(!empty($gid)){
                $this->assign('gid',$gid);
            }
            $userGroup = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorGroup')->getsBy(array('status'=>1));
            $this->assign('user_group',$userGroup);
        }
    }

    /*
    * 修改监控(废弃)
    */
    public function editMonitorAction(){
        $id = $this->getGet('id');
        if($_POST){
            //基础写入信息
            $id = $this->getPost('id');
            $baseData = array(
                'title'=>$this->getPost('title'),
                'warning_type'=>$this->getPost('warning_type'),
                'user_id'=>$this->userInfo['user_id'],
                'warning_target'=>$this->getPost('gid'),
            );
            //附加类别信息写入
            if($this->getPost('monitor_type') == 'ecpm'){
                $TypeData = array(
                    'params'=>json_encode(
                        array(
                            'ad_type'=>$this->getPost('ad_type'),
                            'ecpm_min_val'=>$this->getPost('ecpm_min_val'),
                            'app_key'=>$this->getPost('app_key'),
                            'monitor_type'=>$this->getPost('monitor_type'),
                        )
                    ),
                );
            }elseif($this->getPost('monitor_type') == 'doublecheck'){
                $TypeData = array(
                    'params'=>json_encode(
                        array(

                            'ads_id'=>$this->getPost('ads_id'),
                            'time_length'=>$this->getPost('time_length'),
                            'monitor_type'=>$this->getPost('monitor_type'),
                        )
                    ),
                );
            }else{
                $TypeData = array(
                    'params'=>json_encode(
                        array(
                            #'limit_val'=>$this->getPost('limit_val'),
                            'app_key'=>$this->getPost('app_key'),
                            'monitor_type'=>$this->getPost('monitor_type'),
                        )
                    ),
                );
            }
            $updateData = array_merge($baseData,$TypeData);
            if(MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorTask')->update($updateData,$id)){
                echo 1;
            }else{
                echo 0;
            }
        }else{
            if(!empty($id)){
                $info = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorTask')->getsBy(array('id'=>$id));
                $temp = json_decode($info[0]['params'],true);
                if(is_array($temp)){
                    $data = array_merge($info[0],$temp);
                }
                $ads = MobgiData_Service_BaseModel::getAdsIdMap();
                $appMap = MobgiData_Service_BaseModel::getAppKeyMap();
                $adType = Common_Service_Config::AD_TYPE;
                $userGroup = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorGroup')->getsBy(array('status'=>1));
                $this->assign('ads',$ads);
                $this->assign('user_group',$userGroup);
                $this->assign('ad_type',$adType);
                $this->assign('app',$appMap);
                $this->assign('data',$data);
            }
        }
    }

        /*
        * 删除监控
        */
    public function deleteMonitorAction(){
        $ids = $this->getGet('ids');
        $idArr = explode(',',$ids);
        if(MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorTask')->deletes('id',$idArr)){
            echo 1;
        }else{
            echo 0;
        }
    }

    /*
     * 用户列表
     */
    public function userListAction(){
        $gid = $this->getGet('gid');
        $uidGidList = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorUserGroup')->getsBy(array('gid'=>$gid));
        $userList = array();
        foreach ($uidGidList as $key=>$val){
            $userList[$key] = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorUser')->get($val['uid']);
        }
        $this->assign('userlist',$userList);
    }

    /*
     * 改变监控状态
     */
    public function changeMonitorStatusAction(){
            $data = $this->getInput(array('id','status'));
            //检查状态，如果运行状态则不能改变
            $checkStatus = MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorTask')->get($data['id']);
            if($checkStatus['status'] == 2){
                MobgiMonitor_Service_MonitorModel::ajaxReturn(0,'该状态现在不能被改变请稍后重试!');
            }
            if($data['status'] == 1 || $data['status'] == 3){
                #变成stop
                $changeStatus = 4;
            }elseif($data['status'] == 0 || $data['status'] == 4){
                #变成等待
                $changeStatus = 1;
            }
            if(MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorTask')->updateBy(array('status'=>$changeStatus),array('id'=>$data['id']))){
                MobgiMonitor_Service_MonitorModel::ajaxReturn(1,'修改成功!');
            }else{
                MobgiMonitor_Service_MonitorModel::ajaxReturn(0,'修改失败!');
            }
    }
    /*
     * 改变用户组状态
     */
    public function changeMonitorGroupStatusAction(){
        $data = $this->getInput(array('id','status'));
        if($data['status'] == 1){
            $changeStatus = 0;
        }elseif($data['status'] == 0){
            $changeStatus = 1;
        }
        if(MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorGroup')->updateBy(array('status'=>$changeStatus),array('id'=>$data['id']))){
            MobgiMonitor_Service_MonitorModel::ajaxReturn(1,'修改成功!');
        }else{
            MobgiMonitor_Service_MonitorModel::ajaxReturn(0,'修改失败!');
        }
    }

    /*
     * 删除用户组中某个用户
     *
     */
    public function deleteUserAction(){
        $id = $this->getGet('id');
        if(MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorUser')->delete($id)&&MobgiMonitor_Service_MonitorModel::getMonitorDao('MonitorUserGroup')->deleteBy(array('uid'=>$id))){
            echo 1;
        }else{
            echo 0;
        }
    }

    /*
     * 监控日志显示
     */

    public function showMonitorLogAction(){
        $infos = $this->getGet('id');
        list($taskId,$monitorType) = explode('_',$infos);
        $this->assign('sdate',date("Y-m-d",strtotime('-7 days')));
        $this->assign('edate', date('Y-m-d'));
        $this->assign('taskId',$taskId);
        $this->assign('monitorType',$monitorType);
    }


    /*
     * ajax获取日志情况
     */

    public function getMonitorLogAction(){
        $taskId = $this->getGet('taskId');
        list($sdate,$edate) = explode(' - ',$this->getGet('days'));
        $monitorType = $this->getGet('monitorType');
        $where = array(
            'days'=>array(array('>=', $sdate), array('<=', $edate)),
            'task_id'=>$taskId,
        );
        $taskLogList = MobgiMonitor_Service_MonitorModel::getTaskLog($monitorType,$where);
        $data =array();
        foreach ($taskLogList as $key=>$val){
            $data[$key]['value'] = $val['value'];
            $data[$key]['predict'] = $val['predict'];
            $data[$key]['upper'] = $val['upper'];
            $data[$key]['lower'] = $val['lower'];
            $data[$key]['days'] = $val['days'].' '.$val['hours'].":00";
        }
        echo json_encode($data);
    }
}