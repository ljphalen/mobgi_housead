<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-11-30 10:59:27
 * $Id: Trialflow.php 62100 2017-11-30 10:59:27Z hunter.fang $
 */

class Intergration_TrialflowController extends Admin_BaseController {
    public $actions = array (
            'listUrl' => '/Admin/Intergration_Trialflow/index',
            'addUrl' => '/Admin/Intergration_Trialflow/add',
            'addPostUrl' => '/Admin/Intergration_Trialflow/addPost',
            'deleteUrl' => '/Admin/Intergration_Trialflow/delete',
            'viewUrl' => '/Admin/Intergration_Trialflow/add',
    );
    public $perpage = 10;
    
    public function indexAction() {
        $this->getAppKeyList (  );

    }
    
    private function getAppKeyList() {
        $params = array ();
        $page = intval ( $this->getInput ( 'page' ) );
        if ($page < 1)
            $page = 1;
        $search['app_name'] = $this->getInput ('app_name');
        $search['platform'] = Common_Service_Const::ANDRIOD_PLATFORM;
        if (trim ( $search ['app_name'] )) {
            $appKeys = MobgiApi_Service_AdAppModel::getAppKeysByName ( $search ['app_name'] );
            if ($appKeys) {
                $params ['app_key'] = array (
                        'IN',
                        $appKeys 
                );
            } else {
                $params ['app_key'] = '0';
            }
        }
        $params ['platform'] = Common_Service_Const::ANDRIOD_PLATFORM;
        $params ['is_check'] = MobgiApi_Service_AdAppModel::ISCHECK_PASS;
        list ( $total, $appList ) = MobgiApi_Service_AdAppModel::getList ( $page, $this->perpage, $params, array ('update_time' => 'DESC') );
        $url = $this->actions ['listUrl'] . '/?' . http_build_query ( $search ) . '&';
        $this->assign ( 'pager', Common::getPages ( $total, $page, $this->perpage, $url ) );
        foreach ( $appList as $key => $value ) {
            if (! stristr ( $value ['icon'], 'http' )) {
                $appList [$key] ['icon'] = Common::getAttachPath () . $value ['icon'];
            }
            if ($value ['platform'] == Common_Service_Const::ANDRIOD_PLATFORM) {
                $appList [$key] ['platform_class'] = 'android';
                $appList [$key] ['platform_name'] = 'Android';
            }
            $appList [$key] ['is_config'] = Advertiser_Service_TrialflowconfModel::getBy ( array ('app_key' => $value ['app_key'],'conf_type' => Advertiser_Service_TrialflowconfModel::DEAFAULT_CONF_TYPE ) ) ? '已配置' : '未配置';
        }
        $this->assign ( 'appList', $appList );
        $this->assign ( 'search', $search );
        $this->assign ( 'total', $total );
    }
    
    public function getQueryString(){
        $search = $this->getInput ( array (
                'platform',
                'app_name', 
                'page'
        ) );
        
        return http_build_query ( $search );
        
    }
    
    public function addAction() {
        $this->getAppKeyList ();
        $flowId = $this->getInput ( 'flow_id' );
        if ($flowId) {
            $data = $this->getEditFlowInfo ( $flowId );
        } else {
            foreach ( Common_Service_Const::$mAdSubType as $adSubType => $val ) {
                $tmp [] = array (
                        'ad_type' => $adSubType,
                        'name' => $val 
                );
            }
            $data ['ad_Info'] = $tmp;
            $data ['app_key'] = $this->getInput ( 'app_key' );
        }
        $this->assign ( 'info', $data );
        $this->assign ( 'queryString', $this->getQueryString() );
    }

    public function addPostAction() {
        $info = $this->getRequest ()->getPost ();
        $info = $this->checkPostParam ( $info );  
        $flowId = $this->updateFlowConf($info);
        if(!$flowId){
            $this->output ( - 1, '操作失败' );
        }
        $this->output ( 0, '操作成功' );
    }
    
    public function updateFlowConf($info){
        $data['app_key'] = $info['app_key'];
        $data['conf_type'] = $info['conf_type'];
        $data['conf_name'] = $info['conf_name'];
        $data['game_conf_type'] = $info['game_conf_type'];
        $data['operator_id'] = $this->userInfo['user_id'];
        $data['game_conf']='';
        if($info['game_conf_type']){
            $data['game_conf'] = json_encode($info['game_conf'] );
        }
        $data['isopen'] = $info['isopen'];
        if($info['flow_id']){
            Advertiser_Service_TrialflowconfModel::updateByID($data, $info['flow_id']);
            $flowId = $info['flow_id'];
        }else{
            $flowId = Advertiser_Service_TrialflowconfModel::add($data);
         }
        return $flowId;
    }
    
    private  function getEditFlowInfo($flowId) {
        $flowConf = Advertiser_Service_TrialflowconfModel::getByID ( $flowId );
        if (! $flowConf) {
            return array();
        }
        $data = array (
            'flow_id' => $flowId,
            'app_key' => $flowConf ['app_key'],
            'conf_type' => $flowConf ['conf_type'],
            'conf_name' => $flowConf ['conf_name'],
            'game_conf_type' => $flowConf ['game_conf_type'],
            'isopen' => $flowConf ['isopen'],
        );
        $appInfo = MobgiApi_Service_AdAppModel::getBy ( array (
                'app_key' => $flowConf ['app_key'] 
        ) );
        $data ['icon'] = $appInfo ['icon'];
        $data ['app_key'] = $appInfo ['app_key'];
        $data ['app_name'] = $appInfo ['app_name'];
        $data ['platform'] = $appInfo ['platform'];
        if (! stristr ( $appInfo ['icon'], 'http' )) {
            $data ['icon'] = Common::getAttachPath () . $appInfo ['icon'];
        } else {
            $data ['icon'] = $appInfo ['icon'];
        }
        if ($appInfo ['platform'] == Common_Service_Const::ANDRIOD_PLATFORM) {
            $data ['platform_class'] = 'android';
        } 
        if ($flowConf ['game_conf_type']) {
            $data ['game_conf'] = json_decode ( $flowConf ['game_conf'], true );
        }
        return $data;
    }
    
    public function checkPostParam($info) {
        if (! trim ( $info ['app_key'] )) {
            $this->output ( - 1, 'app_key为空' );
        }
        if (! trim ( $info ['conf_name'] )) {
            $this->output ( - 1, '配置名称为空' );
        }
        if ($info ['game_conf_type'] == '') {
            $this->output ( - 1, '应用版本没有选择' );
        }
//        if($info ['conf_type']==Advertiser_Service_TrialflowconfModel::DEAFAULT_CONF_TYPE){
//            foreach ( Common_Service_Const::$mAdSubType as $adSubType => $adTypeName ) {
//                if ($info ['is_default_'.$adSubType]) {
//                    $this->output ( - 1, $adTypeName . '中的不能选择使用全局配置按钮' );
//                }
//            }
//        }
        
        $this->checkDeafaultConfig ( $info );
        if ($info ['conf_type']==Advertiser_Service_TrialflowconfModel::CUSTOME_CONF_TYPE) {
            $this->checkConditionIsExits ( $info );
        }
        return $info;
    }
    
    public function checkDeafaultConfig($info) {
        $params ['conf_type'] = Advertiser_Service_TrialflowconfModel::DEAFAULT_CONF_TYPE;
        $params ['app_key'] = $info ['app_key'];
        if($info ['flow_id']){
            $params ['id'] = array('<>',$info['flow_id']);
        }
        $restult = Advertiser_Service_TrialflowconfModel::getBy ( $params );
        if (!  $restult && $info ['conf_type'] == Advertiser_Service_TrialflowconfModel::CUSTOME_CONF_TYPE) {
            $this->output ( - 1, '请先配置全局配置' );
        }       
        if($info['conf_type'] == Advertiser_Service_TrialflowconfModel::DEAFAULT_CONF_TYPE){
            if ($restult) {
                $this->output ( - 1, '全局配置只能已经存在，全局配置有且仅有一个'  );
            }
        }
        if ($info ['conf_type'] == Advertiser_Service_TrialflowconfModel::DEAFAULT_CONF_TYPE && ($info ['game_conf_type'] != '0')) {
            $this->output ( - 1, '全局配置不能应用版本定向条件' );
        }
    }
    
    private function checkConditionIsExits($info) {
        $conditionArr = array();
        if($info['game_conf_type']){
            $conditionArr[]= 'game_conf_type';
        }
        if($info ['conf_type'] == Advertiser_Service_TrialflowconfModel::CUSTOME_CONF_TYPE){
            if(!count($conditionArr)){
                $this->output ( - 1, '配置条件不能为空' );
            }
        }
        if($info ['game_conf_type']){
            if(empty($info['game_conf'])){
                $this->output ( - 1, '游戏版本定向配置条件不能为空' );
            }
            if(count(array_unique($info['game_conf'])) <  count($info['game_conf'])){
                $this->output ( - 1, '游戏版本的值重复' );
            }
        }
        
        $params ['conf_type'] = $info ['conf_type'];
        $params ['app_key'] = $info ['app_key'];
        if($info['flow_id']){
            $params ['id'] =  array('<>', $info['flow_id']);
        }
        $flowConf = Advertiser_Service_TrialflowconfModel::getsBy ( $params );
        $conditionRelConf = array (
                'game_conf_type' => 'game_conf',
        );
        $conditionRelPostData = array (
                'game_conf_type' => 'game_conf',
        );
        foreach ( $flowConf as $key => $val ) {
            $flag = array();
            foreach ($conditionArr as $condition){
                if($val[$condition]){
                    $conf =json_decode ( $val [$conditionRelConf[$condition]], true );
                    if ($this->checkConfIsExist ( $conf, $info [$conditionRelPostData[$condition]], $condition  )){
                        $flag[] = 1;
                    }
                }
            }
            if(count($conditionArr) == count($flag)){
                $this->output ( - 1, '配置条件重复，请检查,重复配置为：' . $val ['conf_name'] );
            }
            
        }
    }
    
    public function checkConfIsExist($targetConf, $sourceConf, $type) {
        if (! is_array ( $sourceConf ) || ! is_array ( $targetConf )) {
            return false;
        }
        foreach ( $sourceConf as $confId ) {
            if (in_array ( $confId, $targetConf )) {
                return true;
            }
        }
        return false;
    }
    
    public function getFlowListAction() {
        $appKey = $this->getInput ( 'app_key' );
        if (! $appKey) {
            $this->output ( - 1, '非法请求' );
        }
        $params ['app_key'] = $appKey;
        $flowList = Advertiser_Service_TrialflowconfModel::getsBy ( $params );
        $outData = array ();
        $appInfo = MobgiApi_Service_AdAppModel::getBy ( array (
                'app_key' => $appKey 
        ) );
        if(empty($appInfo)){
            $this->output ( -1, 'ok' );
        }
        $outData ['app_key'] = $appInfo ['app_key'];
        $outData ['app_name'] = $appInfo ['app_name'];
        $outData ['platform'] = $appInfo ['platform'];
        if (! stristr ( $appInfo ['icon'], 'http' )) {
            $outData ['icon'] = Common::getAttachPath () . $appInfo ['icon'];
        } else {
            $outData ['icon'] = $appInfo ['icon'];
        }
        if ($appInfo ['platform'] == Common_Service_Const::ANDRIOD_PLATFORM) {
            $outData ['platform_class'] = 'android';
        } 
        $list = array ();
        if ($flowList) {
            foreach ( $flowList as $key => $val ) {
                $userInfo = Admin_Service_UserModel::getBy ( array (
                        'user_id' => $val ['operator_id'] 
                ) );
                $list [] = array (
                        'id' => $val ['id'],
                        'conf_type' => $val ['conf_type'],
                        'conf_type_name' => $val ['conf_type'] == 2? '定向配置' : '全局配置',
                        'conf_name' => $val ['conf_name'],
                        'operator' => $userInfo ['user_name'],
                        'update_time' => $val ['update_time'] 
                );
            }
        }
        $outData ['list'] = $list;
        $this->output ( 0, 'ok', $outData );
    }
    
    public function viewAction() {
        $this->getAppKeyList ();
        $flowId = $this->getInput ( 'flow_id' );
        if ($flowId) {
            $data = $this->getEditFlowInfo ( $flowId );
        }
        $this->assign ( 'info', $data );
        $this->assign('act', 'view');
        $this->assign ( 'queryString', $this->getQueryString() );
        $this->getView()->display('intergration/trialflow/add.phtml');
        exit();
    }
    
    public function deleteAction() {
        $flowId = $this->getInput ( 'flow_id' );
        if (! $flowId) {
            $this->output ( - 1, '非法请求' );
        }
        $flowConf = Advertiser_Service_TrialflowconfModel::getByID ( $flowId );
        if (! $flowConf) {
            $this->output ( - 1, '非法请求' );
        }
        $result = Advertiser_Service_TrialflowconfModel::deleteById ( $flowId );
        if (! $result) {
            $this->output ( - 1, '删除失败' );
        }
        $this->output ( 0, '删除功能' );
    }
    
}

