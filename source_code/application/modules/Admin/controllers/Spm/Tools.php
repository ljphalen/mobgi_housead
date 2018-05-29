<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/4
 * Time: 15:07
 */

class Spm_ToolsController extends Admin_BaseController{

    public function init() {
        parent::init();
        $module = $this->getTopModule();
        $this->assign('module', $module);
    }

    public $actions = [
        'attributeUrl' => '/Admin/Spm_Tools/attribute',
        'getAattributeListUrl' => '/Admin/Spm_Tools/getAttributeList',
        'resetDataUrl' => '/Admin/Spm_Tools/resetData',
        'channelTestUrl' => '/Admin/Spm_Tools/channelTest',
        'documentUrl' => '/Admin/Spm_Tools/document',
        'documentListUrl' => '/Admin/Spm_Tools/documentList',
        'addDocumentUrl' => '/Admin/Spm_Tools/addDocument',
        'addDocumentPostUrl' => '/Admin/Spm_Tools/addDocumentPost',
        'uploadPostUrl' => '/Admin/Spm_Tools/uploadPost',
        'documentTypeUrl' => '/Admin/Spm_Tools/documentType',
        'documentTypeListUrl' => '/Admin/Spm_Tools/documentTypeList',
        'addDocumentTypeUrl' => '/Admin/Spm_Tools/addDocumentType',
        'addDocumentTypePostUrl' => '/Admin/Spm_Tools/addDocumentTypePost',
        'monitorUrl' => '/Admin/Spm_Tools/monitor',
        'processListUrl' => '/Admin/Spm_Tools/processList',
        'addProcessUrl' => '/Admin/Spm_Tools/addProcess',
        'addProcessPostUrl' => '/Admin/Spm_Tools/addProcessPost',
        'changeProcessStatusUrl' => '/Admin/Spm_Tools/changeProcessStatus',
        'directoryListUrl' => '/Admin/Spm_Tools/directoryList',
        'addDirectoryUrl' => '/Admin/Spm_Tools/addDirectory',
        'addDirectoryPostUrl' => '/Admin/Spm_Tools/addDirectoryPost',
        'changeDirectoryStatusUrl' => '/Admin/Spm_Tools/changeDirectoryStatus',
        'scriptAlarmListUrl' => '/Admin/Spm_Tools/scriptAlarmList',
        'addScriptAlarmUrl' => '/Admin/Spm_Tools/addScriptAlarm',
        'addScriptAlarmPostUrl' => '/Admin/Spm_Tools/addScriptAlarmPost',
        'changeScriptAlarmStatusUrl' => '/Admin/Spm_Tools/changeScriptAlarmStatus',
        'channelAccountUrl' => '/Admin/Spm_Tools/channelAccount',
        'channelAccountListUrl' => '/Admin/Spm_Tools/channelAccountList',
        'editChannelAccountUrl' => '/Admin/Spm_Tools/editChannelAccount',
        'editChannelAccountPostUrl' => '/Admin/Spm_Tools/editChannelAccountPost',
        'getActivityListUrl' => '/Admin/Spm_Tools/getActivityList',
        'getAndroidChannelListUrl' => '/Admin/Spm_Tools/getAndroidChannelList',
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

    public function attributeAction(){

    }

    public function getAttributeListAction(){
        $params = $this->getPost( array('app_id','deviceid') );
//        $params['app_id'] = 15;
//        $params['deviceid'] = '1232132213212';
//        $params['deviceid'] = 'B1E65C7A-888D-4000-A451-B6D742564ED4';
//        $params['deviceid'] = 'C9A2285B-41AA-4524-845B-BFE429FDA9CA';
        $params = $this->checkAttributeParams($params);
        $app = MobgiSpm_Service_DeliveryModel::getAppById($params['app_id']);
        $dataList = MobgiSpm_Service_ToolsModel::getDataList($app, $params['deviceid']);
        $this->output(0,'success',$dataList);
    }

    private function checkAttributeParams($params)
    {
        $params['app_id'] = intval($params['app_id']);
        $params['deviceid'] = trim($params['deviceid']);
        if($params['app_id'] == 0){
            $this->output ( - 1, '请选择投放应用' );
        }
        if(empty($params['deviceid'])){
            $this->output ( - 1, '请输入设备id' );
        }
        return $params;
    }

    public function resetDataAction(){
        $params = $this->getPost( array('app_id','deviceid') );
        $params = $this->checkAttributeParams($params);
        $app = MobgiSpm_Service_DeliveryModel::getAppById($params['app_id']);
        $ret = MobgiSpm_Service_ToolsModel::resetData($app, $params['deviceid']);
        if($ret){
            $this->output ( - 1, '清除成功' );
        }else{
            $this->output ( - 1, '查不到设备信息，请重新输入' );
        }
    }

    public function channelTestAction(){

    }

    public function documentAction(){
        $documentType = MobgiSpm_Service_ToolsModel::getMonitorDocumentType();
        $this->assign('documentType', $documentType);
    }

    public function documentListAction(){
        $params = $this->getPost( array('page','limit','document_type','document_name','sdate','edate') );
        $where = array(
            'document_type' => $params['document_type'],
            'document_name' => array('like',$params['document_name']),
        );
        $where = $this->filterParams($where);
        if(!empty($params['sdate']) && !empty($params['edate'])){
            $stime = strtotime($params['sdate']);
            $etime = strtotime($params['edate'] . ' 23:59:59');
            $where['update_time'] = array(array('>=', $stime), array('<=', $etime));
        }
        $orderBy = array('update_time'=>'DESC');
        list($total, $documentList)  = MobgiSpm_Service_ToolsModel::getDocumentList($params['page'], $params['limit'], $where, $orderBy);
        $documentList = MobgiSpm_Service_ToolsModel::formatDocumentList($documentList);
        $result  = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $documentList,
        );
        exit(json_encode($result));
    }

    public function addDocumentAction(){
        $id = intval($this->getInput('id'));
        if($id != 0){
            $document = MobgiSpm_Service_ToolsModel::getMonitorDocumentById($id);
            $this->assign('document', $document);
        }
        $documentType = MobgiSpm_Service_ToolsModel::getMonitorDocumentType();
        $this->assign('documentType', $documentType);
    }

    public function addDocumentPostAction(){
        $id = intval($this->getInput('id'));
        if($id == 0){
            $this->addDocumentData();
        }else{
            $this->editDocumentData($id);
        }
    }

    private function addDocumentData(){
        $params = $this->getPost( array('document_name','document_type','document_url') );
        $data = $this->checkAddDocumentParams($params, 0);
        $id = MobgiSpm_Service_ToolsModel::addDocument($data);
        $this->output(0, '新建成功');
    }

    private function editDocumentData($id){
        $params = $this->getPost( array('document_name','document_type','document_url') );
        $data = $this->checkAddDocumentParams($params, $id);
        MobgiSpm_Service_ToolsModel::updateDocument($data, array('id' => $id));
        $this->output(0, '修改成功');
    }


    private function checkAddDocumentParams($params, $id)
    {
        $params['document_name'] = trim($params['document_name']);
        if($params['document_name'] == ''){
            $this->output ( - 1, '请填写文档名称' );
        }
        $result = MobgiSpm_Service_ToolsModel::getDocumentByParams($params, $id);
        if($result){
            $this->output ( - 1, '文档名称已存在，请重新填写' );
        }
        if(empty($params['document_type'])){
            $this->output ( - 1, '请选择文档类型' );
        }
        $params['document_url'] = trim($params['document_url']);
        if(empty($params['document_url'])){
            $this->output ( - 1, '请上传文档' );
        }
        $urlArr = explode('.', $params['document_url']);
        $params['document_format'] = array_pop($urlArr);
        $params['operator'] = $this->userInfo['user_name'];
        return $params;
    }

    public function documentTypeAction(){

    }

    public function documentTypeListAction(){
        $params = $this->getPost( array('page','limit','name') );
        $where = array();
        if (trim($params['name'])) {
            $where['name'] = array('LIKE', trim($params['name']));
        }
        $where = $this->filterParams($where);
        $orderBy = array('id'=>'DESC');
        list($total, $documentTypeList)  = MobgiSpm_Service_ToolsModel::getDocumentTypeList($params['page'], $params['limit'], $where, $orderBy);
        $documentTypeList = MobgiSpm_Service_ToolsModel::formatDocumentTypeList($documentTypeList);
        $result  = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $documentTypeList,
        );
        exit(json_encode($result));
    }

    public function uploadPostAction() {
        $ret = Common::upload('file', 'document',array('maxSize'=>2000000,'allowFileType'=>array('gif','jpeg','jpg','png','pdf','ppt','pptx','doc','docx','txt','csv','xls','xlsx','psd','wps','numbers','key','pages','xmind','rar','zip')), true, true );
        $this->output($ret['code'], $ret['msg'],$ret['data']);
    }

    public function addDocumentTypeAction(){
        $id = intval($this->getInput('id'));
        if($id != 0){
            $documentType = MobgiSpm_Service_ToolsModel::getMonitorDocumentTypeById($id);
            $this->assign('documentType', $documentType);
        }
    }

    public function addDocumentTypePostAction(){
        $id = intval($this->getInput('id'));
        $params = $this->getPost( array('name','description') );
        $data = $this->checkAddDocumentTypeParams($params, $id);
        if($id == 0){
            $id = MobgiSpm_Service_ToolsModel::addDocumentType($data);
            $this->output(0, '新建成功');
        }else{
            MobgiSpm_Service_ToolsModel::updateDocumentType($data, array('id' => $id));
            $this->output(0, '修改成功');
        }
    }

    private function checkAddDocumentTypeParams($params, $id)
    {
        $params['name'] = trim($params['name']);
        if($params['name'] == ''){
            $this->output ( - 1, '请填写类型名称' );
        }
        $result = MobgiSpm_Service_ToolsModel::getDocumentTypeByParams($params, $id);
        if($result){
            $this->output ( - 1, '类型名称已存在，请重新填写' );
        }
        $params['operator'] = $this->userInfo['user_name'];
        return $params;
    }

    public function monitorAction(){

    }

    public function processListAction(){
        $params = $this->getPost( array('page','limit','app_id','description') );
        $where = array();
        if (trim($params['description'])) {
            $where['description'] = array('LIKE', trim($params['description']));
        }
        $where = $this->filterParams($where);
        $orderBy = array('id'=>'DESC');
        list($total, $List)  = MobgiSpm_Service_ToolsModel::getProcessList($params['page'], $params['limit'], $where, $orderBy);
        $List = MobgiSpm_Service_ToolsModel::formatProcessList($List);
        $result  = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $List,
        );
        exit(json_encode($result));
    }

    public function addProcessAction(){
        $id = intval($this->getInput('id'));
        $monitorUnit = MobgiSpm_Service_ToolsModel::getMonitorProcessById($id);
        if(isset($monitorUnit['process_list'])){
            $monitorUnit['process_list'] = json_decode($monitorUnit['process_list'], true);
        }
        $this->assign('monitorUnit', $monitorUnit);
        $monitorConfig = Common::getConfig('spmConfig', 'MONITOR_TOOLS');
        $this->assign('monitorPlatform', $monitorConfig['monitor_platform']);

    }

    public function addProcessPostAction(){
        $params = $this->getPost( array('id','platform','description','process_list','remind_phone') );
        $id = intval($params['id']);
        $params = $this->checkAddProcessPostParams($params);
        if($id == 0){ # add
            $id = MobgiSpm_Service_ToolsModel::addProcess($params);
            $this->output(0, '新建成功');
        }else{ # edit
            MobgiSpm_Service_ToolsModel::updateProcess($params, array('id' => $id));
            $this->output(0, '修改成功');
        }
    }

    private function checkAddProcessPostParams($params)
    {
        $data['platform'] = trim($params['platform']);
        $data['description'] = trim($params['description']);
        $data['process_list'] = $params['process_list'];
        $data['remind_phone'] = trim($params['remind_phone']);
        $data['operator'] = $this->userInfo['user_name'];
        if(empty($data['platform'])){
            $this->output ( - 1, '请选择平台' );
        }
        if(empty($data['description'])){
            $this->output ( - 1, '请输入功能描述' );
        }
        if(empty($data['process_list'])){
            $this->output ( - 1, '进程列表不能为空' );
        }
        # 过滤空字符串
        $data['process_list'] = array_filter($data['process_list']);
        $data['process_list'] = array_values($data['process_list']);
        $data['process_list'] = json_encode($data['process_list']);
        if(empty($data['remind_phone'])){
            $this->output ( - 1, '请输入告警电话号码' );
        }
        return $data;
    }

    public function changeProcessStatusAction(){
        $id = intval($this->getInput('id'));
        if($id == 0){
            $this->output ( - 1, '请选择配置' );
        }
        $params = $this->getPost( array('status') );
        if( !in_array($params['status'], array('ON','OFF')) ){
            $this->output ( - 1, '修改状态错误' );
        }
        MobgiSpm_Service_ToolsModel::updateProcess($params, array('id' => $id));
        $this->output(0, '修改成功');
    }

    public function directoryListAction(){
        $params = $this->getPost( array('page','limit','app_id','description') );
        $where = array();
        if (trim($params['description'])) {
            $where['description'] = array('LIKE', trim($params['description']));
        }
        $where = $this->filterParams($where);
        $orderBy = array('id'=>'DESC');
        list($total, $List)  = MobgiSpm_Service_ToolsModel::getDirectoryList($params['page'], $params['limit'], $where, $orderBy);
        $List = MobgiSpm_Service_ToolsModel::formatDirectoryList($List);
        $result  = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $List,
        );
        exit(json_encode($result));
    }

    public function addDirectoryAction(){
        $id = intval($this->getInput('id'));
        $monitorUnit = MobgiSpm_Service_ToolsModel::getMonitorDirectoryById($id);
        if(isset($monitorUnit['directory_list'])){
            $monitorUnit['directory_list'] = json_decode($monitorUnit['directory_list'], true);
        }
        $this->assign('monitorUnit', $monitorUnit);
        $monitorConfig = Common::getConfig('spmConfig', 'MONITOR_TOOLS');
        $this->assign('monitorPlatform', $monitorConfig['monitor_platform']);

    }

    public function addDirectoryPostAction(){
        $params = $this->getPost( array('id','platform','description','directory_list','suffix','filter_file','delay_time','remind_phone') );
        $id = intval($params['id']);
        $params = $this->checkAddDirectoryPostParams($params);
        if($id == 0){ # add
            $id = MobgiSpm_Service_ToolsModel::addDirectory($params);
            $this->output(0, '新建成功');
        }else{ # edit
            MobgiSpm_Service_ToolsModel::updateDirectory($params, array('id' => $id));
            $this->output(0, '修改成功');
        }
    }

    private function checkAddDirectoryPostParams($params)
    {
        $data['platform'] = trim($params['platform']);
        $data['description'] = trim($params['description']);
        $data['directory_list'] = $params['directory_list'];
        $data['suffix'] = trim($params['suffix']);
        $data['filter_file'] = trim($params['filter_file']);
        $data['delay_time'] = intval($params['delay_time']);
        $data['remind_phone'] = trim($params['remind_phone']);
        $data['operator'] = $this->userInfo['user_name'];
        if(empty($data['platform'])){
            $this->output ( - 1, '请选择平台' );
        }
        if(empty($data['description'])){
            $this->output ( - 1, '请输入功能描述' );
        }
        if(empty($data['directory_list'])){
            $this->output ( - 1, '目录列表不能为空' );
        }
        # 过滤空字符串
        $data['directory_list'] = array_filter($data['directory_list']);
        $data['directory_list'] = array_values($data['directory_list']);
        $data['directory_list'] = json_encode($data['directory_list']);
        if(empty($data['delay_time'])){
            $this->output ( - 1, '请输入延迟时间' );
        }
        if(empty($data['remind_phone'])){
            $this->output ( - 1, '请输入告警电话号码' );
        }
        return $data;
    }

    public function changeDirectoryStatusAction(){
        $id = intval($this->getInput('id'));
        if($id == 0){
            $this->output ( - 1, '请选择配置' );
        }
        $params = $this->getPost( array('status') );
        if( !in_array($params['status'], array('ON','OFF')) ){
            $this->output ( - 1, '修改状态错误' );
        }
        MobgiSpm_Service_ToolsModel::updateDirectory($params, array('id' => $id));
        $this->output(0, '修改成功');
    }

    public function scriptAlarmListAction(){
        $params = $this->getPost( array('page','limit','app_id','description') );
        $where = array();
        if (trim($params['description'])) {
            $where['description'] = array('LIKE', trim($params['description']));
        }
        $where = $this->filterParams($where);
        $orderBy = array('id'=>'DESC');
        list($total, $List)  = MobgiSpm_Service_ToolsModel::getScriptAlarmList($params['page'], $params['limit'], $where, $orderBy);
        $List = MobgiSpm_Service_ToolsModel::formatScriptAlarmList($List);
        $result  = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $List,
        );
        exit(json_encode($result));
    }

    public function addScriptAlarmAction(){
        $id = intval($this->getInput('id'));
        $monitorUnit = MobgiSpm_Service_ToolsModel::getMonitorScriptAlarmById($id);
        $this->assign('monitorUnit', $monitorUnit);
        $monitorConfig = Common::getConfig('spmConfig', 'MONITOR_TOOLS');
        $this->assign('alarmType', $monitorConfig['alarm_type']);
    }

    public function addScriptAlarmPostAction(){
        $params = $this->getPost( array('id','script_name','description','alarm_type','alarm_phone','alarm_email') );
        $id = intval($params['id']);
        $params = $this->checkAddScriptAlarmPostParams($params);
        if($id == 0){ # add
            $id = MobgiSpm_Service_ToolsModel::addScriptAlarm($params);
            $this->output(0, '新建成功');
        }else{ # edit
            MobgiSpm_Service_ToolsModel::updateScriptAlarm($params, array('id' => $id));
            $this->output(0, '修改成功');
        }
    }

    private function checkAddScriptAlarmPostParams($params)
    {
        $data['script_name'] = trim($params['script_name']);
        $data['description'] = trim($params['description']);
        $data['alarm_type'] = trim($params['alarm_type']);
        $data['alarm_phone'] = trim($params['alarm_phone']);
        $data['alarm_email'] = trim($params['alarm_email']);
        $data['operator'] = $this->userInfo['user_name'];
        if(empty($data['script_name'])){
            $this->output ( - 1, '请输入脚本名称' );
        }
        if(empty($data['description'])){
            $this->output ( - 1, '请输入功能描述' );
        }
        if(empty($data['alarm_type'])){
            $this->output ( - 1, '请选择告警方式' );
        }
        if(empty($data['alarm_phone'])){
            $this->output ( - 1, '请输入告警电话号码' );
        }
        if(empty($data['alarm_email'])){
            $this->output ( - 1, '请输入告警邮箱' );
        }
        return $data;
    }

    public function changeScriptAlarmStatusAction(){
        $id = intval($this->getInput('id'));
        if($id == 0){
            $this->output ( - 1, '请选择配置' );
        }
        $params = $this->getPost( array('status') );
        if( !in_array($params['status'], array('ON','OFF')) ){
            $this->output ( - 1, '修改状态错误' );
        }
        MobgiSpm_Service_ToolsModel::updateScriptAlarm($params, array('id' => $id));
        $this->output(0, '修改成功');
    }

    public function channelAccountAction(){

    }

    public function channelAccountListAction(){
        $params = $this->getPost( array('page','limit','name','check_msg') );
        $groupId = Admin_Service_UserModel::SPM_CHANNEL_USER;
        if(trim($params['name'])){
            $userIds = MobgiSpm_Service_ToolsModel::getUserIdsByName(trim($params['name']), $groupId);
            if($userIds){
                $where['user_id'] = array('IN',$userIds);
            }else{
                $return = array(
                    'success' => 0,
                    'msg' => '',
                    'count' => 0,
                    'data' => array(),
                );
                exit(json_encode($return));
            }
        }
        if(trim($params['check_msg'])){
            $where['check_msg'] = array('LIKE', trim($params['check_msg']));
        }
        $where['group_id'] = $groupId;
        $where = $this->filterParams($where);
        $orderBy = array('create_time'=>'DESC');
        list($total, $accountList)  = MobgiSpm_Service_ToolsModel::getChannelAccountList($params['page'], $params['limit'], $where, $orderBy);
        $result  = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $accountList,
        );
        exit(json_encode($result));
    }

    public function editChannelAccountAction(){
        $userId = intval($this->getInput('id'));
        $userDetail = Admin_Service_UserModel::getUser($userId);
        $this->assign('userDetail', $userDetail);
        $apps = MobgiSpm_Service_DeliveryModel::getDeliveryApp();
        $this->assign('apps', json_encode($apps));
        $channels = MobgiSpm_Service_DeliveryModel::getDeliveryChannel( array('status'=>'ON'));
        $this->assign('channels', json_encode($channels));
        $androidChannelGroupList = MobgiSpm_Service_ChannelModel::getChannelgroup();
        $this->assign('androidChannelGroups', json_encode($androidChannelGroupList));
        $advertiserDetail = array('relate_app'=>'[]','relate_channel'=>'[]','relate_activity'=>'[]','relate_android_channel_group'=>'[]','relate_android_channel'=>'[]');
        if(!empty($userId)){
            $info = MobgiSpm_Service_ToolsModel::formatAdvertiserDetail($userId);
            if(!empty($info)){
                $advertiserDetail = $info;
            }
        }
        $this->assign('advertiserDetail', $advertiserDetail);
    }

    public function editChannelAccountPostAction(){
        $params = $this->getPost( array('user_id','check_msg','relate_activity','relate_android_channel') );
        $userId = intval($params['user_id']);
        # 用户备注
        Admin_Service_UserModel::updateUser(array('check_msg'=>trim($params['check_msg'])), $userId);
        # 关联权限
        $advertiserDetail = MobgiSpm_Service_ToolsModel::getAdvertiserDetailByUserId($userId);
        $data = MobgiSpm_Service_ToolsModel::formatEditAdvertiserDetail($params['relate_activity'], $params['relate_android_channel']);
        $data['admin_id'] = $userId;
        $data['operator'] = $this->userInfo['user_name'];
        if($advertiserDetail){
            MobgiSpm_Service_ToolsModel::updateAdvertiserDetail($data, array('admin_id'=>$userId));
            $this->output(0, '修改成功');
        }else{
            MobgiSpm_Service_ToolsModel::addAdvertiserDetail($data);
            $this->output(0, '修改成功');
        }
    }

    public function getActivityListAction(){
        $params = $this->getPost( array('apps','channels') );
        $appArr = json_decode($params['apps']);
        $channelArr = json_decode($params['channels']);
        $actvityArr = MobgiSpm_Service_DeliveryModel::getDeliveryActivitysByParams( array('app_id'=>array('IN',$appArr), 'channel_id'=>array('IN',$channelArr)) );
        $activityList = array();
        foreach($actvityArr as $activityVal){
            $activityList[] = array(
                'id' => $activityVal['id'],
                'name' => $activityVal['name'],
            );
        }
        die(json_encode(array("ret"=>"0","msg"=>"获取成功!","data"=>$activityList)));

    }

    public function getAndroidChannelListAction(){
        $params = $this->getPost( array('android_channel_groups') );
        $androidChannelGroupArr = json_decode($params['android_channel_groups']);
        $channelArr = MobgiSpm_Service_ChannelModel::getAndroidChannelsByParams( array('group_id'=>array('IN',$androidChannelGroupArr)) );
        $channelList = array();
        foreach($channelArr as $chanelVal){
            $channelList[] = array(
                'id' => $chanelVal['channel_no'],
                'name' => $chanelVal['channel_name'],
            );
        }
        die(json_encode(array("ret"=>"0","msg"=>"获取成功!","data"=>$channelList)));

    }

}