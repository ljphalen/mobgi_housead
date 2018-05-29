<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/4
 * Time: 15:03
 */

class Spm_DeliveryController extends Admin_BaseController{

    public function init() {
        parent::init();
        $module = $this->getTopModule();
        $this->assign('module', $module);
    }

    public $actions = [
        'activityUrl' => '/Admin/Spm_Delivery/activity',
        'activityListUrl' => '/Admin/Spm_Delivery/activityList',
        'activityUnitUrl' => '/Admin/Spm_Delivery/activityUnit',
        'addActivityUrl' => '/Admin/Spm_Delivery/addActivity',
        'addActivityPostUrl' => '/Admin/Spm_Delivery/addActivityPost',
        'editActivityUrl' => '/Admin/Spm_Delivery/editActivity',
        'editActivityPostUrl' => '/Admin/Spm_Delivery/editActivityPost',
        'editActivityNameUrl' => '/Admin/Spm_Delivery/editActivityName',
        'activityGroupUrl' => '/Admin/Spm_Delivery/activityGroup',
        'activityGroupUnitUrl' => '/Admin/Spm_Delivery/activityGroupUnit',
        'activityGroupListUrl' => '/Admin/Spm_Delivery/activityGroupList',
        'addActivityGroupPostUrl' => '/Admin/Spm_Delivery/addActivityGroupPost',
    ];

    public function getPost($var) {
        $post = $_POST;
        if(is_string($var)) return $post[$var];
        $return = array();
        if (is_array($var)) {
            foreach ($var as $key=>$value) {
                $return[$value] = $post[$value];
            }
            return $return;
        }
        return null;
    }

    /**
     * filter params
     * @param $params
     * @return array
     * @throws Exception
     */
    protected function filterParams($params) {
        foreach ($params as $field => $val) {
            if(is_array($val)){
                list($op, $value) = $val;
                if(is_null($value) || $value === ''){
                    unset($params[$field]);
                }
            }else{
                if(is_null($val) || $val === ''){
                    unset($params[$field]);
                }
            }
        }
        return $params;
    }

    public function activityAction(){
        $channelList  = MobgiSpm_Service_DeliveryModel::getDeliveryChannel();
        $this->assign('channelList', $channelList);
        $userType = Admin_Service_UserModel::SPM_USER; # delivery user
        $userList  = Admin_Service_UserModel::getsBy( array( 'user_type' => $userType));
        $this->assign('userList', $userList);
    }

    public function activityListAction(){
        $params = $this->getPost( array('page','limit','app_id','channel_id','operator','name','short_link','sdate','edate') );
        $where = array(
            'app_id' => $params['app_id'],
            'channel_id' => $params['channel_id'],
            'operator' => $params['operator'],
            'name' => array('like',$params['name']),
            'short_link' => array('like',$params['short_link']),
            'data_platform' => array('!=','appsflyer'),
        );
        $where = $this->filterParams($where);
        if(!empty($params['sdate']) && !empty($params['edate'])){
            $stime = strtotime($params['sdate']);
            $etime = strtotime($params['edate'] . ' 23:59:59');
            $where['create_time'] = array(array('>=', $stime), array('<=', $etime));
        }
        $orderBy = array('id'=>'DESC');
        list($total, $activityList)  = MobgiSpm_Service_DeliveryModel::getActivityList($params['page'], $params['limit'], $where, $orderBy);
        $activityList = MobgiSpm_Service_DeliveryModel::formatActivityList($activityList);
        $result  = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $activityList,
        );
        exit(json_encode($result));
    }

    public function activityUnitAction(){
        $id = $this->getPost('id');
        $activityUnit = MobgiSpm_Service_DeliveryModel::getActivityById($id);
        $this->output(0, '', $activityUnit);
    }

    public function editActivityNameAction(){
        $id = intval($this->getInput('id'));
        $params = $this->getPost( array('name') );
        $activityUnit = MobgiSpm_Service_DeliveryModel::getActivityById($id);
        if(empty($activityUnit)){
            $this->output(-1, '查无此活动');
        }
        $name = trim($params['name']);
        if(empty($name)){
            $this->output(-1, '名称不能为空');
        }
        $result = MobgiSpm_Service_DeliveryModel::getDeliveryActivityByName($name, $id);
        if($result){
            $this->output(-1, '名称已被占用');
        }
        MobgiSpm_Service_DeliveryModel::updateActivity(array('name'=>$name), array('id' => $id));
        $this->output(0, '修改成功');
    }

    public function addActivityAction(){
        $id = intval($this->getInput('id'));
        $activity = MobgiSpm_Service_DeliveryModel::getActivityById($id);
        $this->assign('activity', $activity);
        if($id == 0){
            $channelList  = MobgiSpm_Service_DeliveryModel::getDeliveryChannel(array('status' => 'ON'));
        }else{
            $channelList  = MobgiSpm_Service_DeliveryModel::getDeliveryChannel();
        }
        $this->assign('channelList', $channelList);
        $appId = $_COOKIE['app_id'];
        $activityGroupList  = MobgiSpm_Service_DeliveryModel::getDeliveryActivityGroup($appId);
        $this->assign('activityGroupList', $activityGroupList);
        $monitorPlatformList  = MobgiSpm_Service_DeliveryModel::getMonitorPlatform();
        $this->assign('monitorPlatformList', $monitorPlatformList);
        $monitorConfig = Common::getConfig('spmConfig', 'MONITOR_STATUS');
        $this->assign('shortLinkStatusList', $monitorConfig['shortlink_status']);
        $this->assign('checkpointList', $monitorConfig['checkpoint']);
        $this->assign('activityStatusList', $monitorConfig['activity_status']);
        $userType = Admin_Service_UserModel::SPM_USER; # delivery user
        $userList  = Admin_Service_UserModel::getsBy( array( 'user_type' => $userType));
        $this->assign('userList', $userList);
    }

    public function addActivityPostAction(){
        $id = intval($this->getInput('id'));
        if($id == 0){
            $this->addActivityData();
        }else{
            $this->editActivityData($id);
        }
    }

    private function addActivityData(){
        $params = $this->getPost( array('app_id','group_id','name','channel_id','origin_url','monitor_platform','checkpoint','is_batch','batch_num') );
        $params = $this->checkAddActivityParams($params);
        $app = MobgiSpm_Service_DeliveryModel::getAppById($params['app_id']);
        $channel = MobgiSpm_Service_DeliveryModel::getChannelById($params['channel_id']);
        if(empty($app) || empty($channel)){
            $this->output(-1, '无法找到应用或渠道');
        }
        $channelDetail = MobgiSpm_Service_DeliveryModel::getChannelDetailById($params['channel_id']);
        if(empty($params['monitor_platform'])){
            $montiorPlatform = array();
        }else{
            $montiorPlatform = MobgiSpm_Service_DeliveryModel::getMonitorPlatformById($params['monitor_platform']);
        }
        if($params['is_batch']){
            $activityName = $params['name'];
            for($i = 1; $i <= $params['batch_num']; $i++){
                $params['name'] = $activityName . '-' . $i;
                $this->createActivity($params, $app, $channel, $channelDetail, $montiorPlatform, $i);
            }
        }else{
            $this->createActivity($params, $app, $channel, $channelDetail, $montiorPlatform);
        }
        $this->output(0, '新建成功');
    }

    private function checkAddActivityParams($params)
    {
        $params['app_id'] = intval($params['app_id']);
        $params['group_id'] = intval($params['group_id']);
        $params['channel_id'] = intval($params['channel_id']);
        $params['name'] = trim($params['name']);
        $params['origin_url'] = trim($params['origin_url']);
        $params['monitor_platform'] = intval($params['monitor_platform']);
        $params['is_batch'] = intval($params['is_batch']);
        $params['batch_num'] = intval($params['batch_num']);
        if($params['app_id'] == 0){
            $this->output ( - 1, '请选择投放应用' );
        }
        if($params['group_id'] == 0){
            $this->output ( - 1, '请选择投放活动组' );
        }
        if($params['channel_id'] == 0){
            $this->output ( - 1, '请选择投放渠道' );
        }
        if(empty($params['name'])){
            $this->output ( - 1, '请输入推广活动名称' );
        }
        if(empty($params['origin_url'])){
            $this->output ( - 1, '请输入跳转地址' );
        }
        if(empty($params['checkpoint'])){
            $this->output ( - 1, '请选择回调类型' );
        }
        if($params['is_batch'] != 0 && $params['batch_num'] <= 0){
            $this->output ( - 1, '请选择填写大于0的创建条数' );
        }
        return $params;
    }

    private function createActivity($params, $app, $channel, $channelDetail, $montiorPlatform, $i = 1){
        $deliveryType = array(1 => 'monitor', 2 => 'agent', 3 => 'agent_sdk');
//        $platformType = array(1 => 'android', 2 => 'ios');
        $params['data_platform'] = $deliveryType[$app['delivery_type']];
        $params['platform'] = $app['platform'];
        $params['operator'] = $this->userInfo['user_name'];
        $params['shortlink_status'] = $channel['shortlink_status'];
        $params['track_type'] = $channel['track_type'];
        # get url config
        if($params['data_platform'] != 'agent'){
            $params['api_link'] = ($params['platform'] == 'ios') ? $channelDetail['template'] : $channelDetail['android_template'];
            $params['callback_config'] = ($params['platform'] == 'ios') ? $channelDetail['callback_template'] : $channelDetail['callback_android_template'];
            # 短链特殊化处理（临时方案，平滑过渡后删除）
            if($channel['track_type'] == 'shortlink' && ($params['api_link'] == 'https://spm.mobgi.com/track/common?acid={activityid}' || $params['api_link'] == 'https://spm.mobgi.com/track/common?acid={activityid}&imei={imei}')){
                $shortUrlHost = Yaf_Application::app()->getConfig()->shorturlroot;
                $params['api_link'] = $shortUrlHost . '/{shortkey}';
            }
        }else{
            $params['redirect_config'] = empty($montiorPlatform) ? '' : $montiorPlatform['template'];
            if(empty($params['redirect_config'])){
                $this->output ( - 1, '该监控平台没有配置接收点击地址' );
            }
            $params['api_link'] = ($params['platform'] == 'ios') ? $channelDetail['agent_template'] : $channelDetail['agent_android_template'];
            $params['callback_config'] = ($params['platform'] == 'ios') ? $channelDetail['callback_agent_template'] : $channelDetail['callback_agent_android_template'];
        }
        # generate short_link
        $params['short_link'] = $this->generateShortKey($i);
        $params['api_link'] = str_replace(array('{shortkey}', '{appstoreid}'), array($params['short_link'], $app['appstore_id']), $params['api_link']);
        $params['callback_config'] = str_replace(array('{appstoreid}', '{packagename}'), array($app['appstore_id'], $app['bundleid']), $params['callback_config']);
        $params['callback_config'] = str_replace('{appstoreid}', $app['appstore_id'], $params['callback_config']);
        $id = MobgiSpm_Service_DeliveryModel::addActivity($params);
        $data['name'] = $params['name'] . '-N' . $id;
        if(strpos($params['api_link'], '{activityid}') !== false){
            $data['api_link'] = str_replace('{activityid}', $id, $params['api_link']);
        }
        MobgiSpm_Service_DeliveryModel::updateActivity($data, array('id' => $id));
    }

    private function generateShortKey($i = 0){
        $time = microtime(true);
        $id = intval($time * 100);
        $id += $i;
        $shortKey= Common::dec2Any($id , 62);
        # check the shortKey
        while( !empty(MobgiSpm_Service_DeliveryModel::getActivityByShortKey($shortKey)) ){
            $id ++;
            $shortKey= Common::dec2Any($id , 62);
        }
        return $shortKey;
    }

    private function editActivityData($id){
        $params = $this->getPost( array('group_id','name','origin_url','api_link','callback_config','shortlink_status','status','checkpoint','operator') );
        $data = $this->checkEditActivityParams($params);
        MobgiSpm_Service_DeliveryModel::updateActivity($data, array('id' => $id));
        # 清除旧api的活动缓存数据
        $this->delActivityIdCache($id);
        $this->output(0, '修改成功');
    }

    public static function delActivityIdCache($id){
        $key = Util_CacheKey::SPM_ACTIVITY_ID . $id;
        $cache = Cache_Factory::getCache (Cache_Factory::ID_REMOTE_REDIS,'spm');
        $ret = $cache->delete($key); // 1：删除成功；0：不存在或者删除失败
        return $ret;
    }

    private function checkEditActivityParams($params)
    {
        $params['group_id'] = intval($params['group_id']);
        $params['name'] = trim($params['name']);
        $params['origin_url'] = trim($params['origin_url']);
        $params['api_link'] = trim($params['api_link']);
        $params['callback_config'] = trim($params['callback_config']);
        $params['shortlink_status'] = trim($params['shortlink_status']);
        $params['status'] = intval($params['status']);
        $params['checkpoint'] = intval($params['checkpoint']);
        $params['operator'] = trim($params['operator']);
        if($params['group_id'] == 0){
            $this->output ( - 1, '请选择推广活动组' );
        }
        if(empty($params['name'])){
            $this->output ( - 1, '请输入推广活动名称' );
        }
        if(empty($params['origin_url'])){
            $this->output ( - 1, '请输入跳转地址' );
        }
//        if(empty($params['api_link'])){
//            $this->output ( - 1, '请输入追踪链接' );
//        }
//        if(empty($params['callback_config'])){
//            $this->output ( - 1, '请输入回调地址' );
//        }
        if(!in_array($params['shortlink_status'], array('OFF','ON')) ){
            $this->output ( - 1, '请选择短链回调状态' );
        }
        if(empty($params['status'])){
            $this->output ( - 1, '请选择状态' );
        }
        if(empty($params['checkpoint'])){
            $this->output ( - 1, '请选择回调类型' );
        }
        if(empty($params['operator'])){
            $this->output ( - 1, '请选择投放人员' );
        }
        return $params;
    }

    public function activityGroupAction(){

    }

    public function activityGroupListAction(){
        $params = $this->getPost( array('page','limit','app_id','name','channel_no','sdate','edate') );
        $where = array(
            'app_id' => intval($params['app_id']),
            'name' => array('like',$params['name']),
            'channel_no' => array('like',$params['channel_no']),
        );
        $where = $this->filterParams($where);
        if(!empty($params['sdate']) && !empty($params['edate'])){
            $stime = strtotime($params['sdate']);
            $etime = strtotime($params['edate'] . ' 23:59:59');
            $where['create_time'] = array(array('>=', $stime), array('<=', $etime));
        }
        $orderBy = array('id'=>'DESC');
        list($total, $activityGroupList)  = MobgiSpm_Service_DeliveryModel::getActivityGroupList($params['page'], $params['limit'], $where, $orderBy);
        $activityGroupList = MobgiSpm_Service_DeliveryModel::formatActivityGroupList($activityGroupList);
        $result  = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $activityGroupList,
        );
        exit(json_encode($result));
    }

    public function activityGroupUnitAction(){
        $id = $this->getPost('id');
        $activityGroupUnit = MobgiSpm_Service_DeliveryModel::getActivityGroupById($id);
        $this->output(0, '', $activityGroupUnit);
    }

    public function addActivityGroupPostAction(){
        $params = $this->getPost( array('id','app_id','name','channel_no','activitys') );
        $id = intval($params['id']);
//        $activitys = $params['activitys'];
        $params = $this->checkAddActivityGroupParams($params);
        if($id == 0){ # add
            $id = MobgiSpm_Service_DeliveryModel::addActivityGroup($params);
            $this->output(0, '新建成功', array('id' => $id, 'name' => $params['name']));
        }else{ # edit
            MobgiSpm_Service_DeliveryModel::updateActivityGroup($params, array('id' => $id));
            $this->output(0, '修改成功');
        }
    }

    private function checkAddActivityGroupParams($params)
    {
        $params['id'] = intval($params['id']);
        $data['name'] = trim($params['name']);
        $data['app_id'] = intval($params['app_id']);
        $data['channel_no'] = trim($params['channel_no']);
        $data['operator'] = $this->userInfo['user_name'];
        if(empty($data['name'])){
            $this->output ( - 1, '请输入推广活动组名称' );
        }
        if($data['app_id'] == 0){
            $this->output ( - 1, '请选择应用' );
        }
        $app = MobgiSpm_Service_DeliveryModel::getAppById($data['app_id']);
        if(empty($app)){
            $this->output ( - 1, '应用参数异常' );
        }
        if($app['platform'] == 'android'){
            if($data['channel_no'] == ''){
                $this->output ( - 1, '请输入渠道号' );
            }
        }
        if( $params['id'] == 0 ){
            if( !empty(MobgiSpm_Service_DeliveryModel::getDeliveryActivityGroupByName($data['name']))){
                $this->output ( - 1, '活动组名称已存在' );
            }
        }
        return $data;
    }

}