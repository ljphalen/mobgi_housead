<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/4
 * Time: 15:04
 */
class Spm_ChannelController extends Admin_BaseController {

    public static $specType = [1, 2, 3];//渠道素材种类

    public function init() {
        parent::init();
        $module = $this->getTopModule();
        $this->assign('module', $module);
    }

    public $actions = [
        'indexUrl' => '/Admin/Spm_Channel/index',
        'channelListUrl' => '/Admin/Spm_Channel/channelList',
        'channelViewUrl' => '/Admin/Spm_Channel/channelView',
        'addChannelUrl' => '/Admin/Spm_Channel/addChannel',
        'addChannelPostUrl' => '/Admin/Spm_Channel/addChannelPost',
        'replaceLinkParamsUrl' => '/Admin/Spm_Channel/replaceLinkParams',
        'changeChannelStatusUrl' => '/Admin/Spm_Channel/changeChannelStatus',
        'changeChannelShortLinkStatusUrl' => '/Admin/Spm_Channel/changeChannelShortLinkStatus',
        'groupUrl' => '/Admin/Spm_Channel/group',
        'channelGroupListUrl' => '/Admin/Spm_Channel/channelGroupList',
        'getChannelByGroupIdUrl' => '/Admin/Spm_Channel/getChannelByGroupId',
        'addChannelGroupUrl' => '/Admin/Spm_Channel/addChannelGroup',
        'addChannelGroupPostUrl' => '/Admin/Spm_Channel/addChannelGroupPost',
        'labelUrl' => '/Admin/Spm_Channel/label',
        'labelListUrl' => '/Admin/Spm_Channel/labelList',
        'addLabelUrl' => '/Admin/Spm_Channel/addLabel',
        'addLabelPostUrl' => '/Admin/Spm_Channel/addLabelPost',
        'androidGroupUrl' => '/Admin/Spm_Channel/androidGroup',
        'androidChannelGroupListUrl' => '/Admin/Spm_Channel/androidChannelGroupList',
        'addAndroidChannelGroupUrl' => '/Admin/Spm_Channel/addAndroidChannelGroup',
        'addAndroidChannelGroupPostUrl' => '/Admin/Spm_Channel/addAndroidChannelGroupPost',
        'gdtConfigUrl' => '/Admin/Spm_Channel/gdtConfig',
        'gdtConfigListUrl' => '/Admin/Spm_Channel/gdtConfigList',
        'gdtConfigUnitUrl' => '/Admin/Spm_Channel/gdtConfigUnit',
        'addGdtConfigPostUrl' => '/Admin/Spm_Channel/addGdtConfigPost',
        'gdtPayConfigUrl' => '/Admin/Spm_Channel/gdtPayConfig',
        'gdtPayConfigListUrl' => '/Admin/Spm_Channel/gdtPayConfigList',
        'gdtPayConfigUnitUrl' => '/Admin/Spm_Channel/gdtPayConfigUnit',
        'addGdtPayConfigPostUrl' => '/Admin/Spm_Channel/addGdtPayConfigPost',
        'changeGdtPayConfigStatusUrl' => '/Admin/Spm_Channel/changeGdtPayConfigStatus',
        'gdtActionConfigUrl' => '/Admin/Spm_Channel/gdtActionConfig',
        'gdtActionConfigListUrl' => '/Admin/Spm_Channel/gdtActionConfigList',
        'gdtActionConfigUnitUrl' => '/Admin/Spm_Channel/gdtActionConfigUnit',
        'addGdtActionConfigPostUrl' => '/Admin/Spm_Channel/addGdtActionConfigPost',
        'baiduConfigUrl' => '/Admin/Spm_Channel/baiduConfig',
        'baiduConfigListUrl' => '/Admin/Spm_Channel/baiduConfigList',
        'baiduConfigUnitUrl' => '/Admin/Spm_Channel/baiduConfigUnit',
        'addBaiduConfigPostUrl' => '/Admin/Spm_Channel/addBaiduConfigPost',
        'payConfigUrl' => '/Admin/Spm_Channel/payConfig',
        'payConfigListUrl' => '/Admin/Spm_Channel/payConfigList',
        'addPayConfigUrl' => '/Admin/Spm_Channel/addPayConfig',
        'changePayConfigStatusUrl' => '/Admin/Spm_Channel/changePayConfigStatus',
        'addPayConfigPostUrl' => '/Admin/Spm_Channel/addPayConfigPost',
        'channelSepcConfigUrl' => '/Admin/Spm_Channel/specConfig',
    ];

    public function getPost($var) {
        $post = $_POST;
        if (is_string($var)) return $post[$var];
        $return = array();
        if (is_array($var)) {
            foreach ($var as $key => $value) {
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
            if (is_array($val)) {
                list($op, $value) = $val;
                if (is_null($value) || $value === '') {
                    unset($params[$field]);
                }
            } else {
                if (is_null($val) || $val === '') {
                    unset($params[$field]);
                }
            }
        }
        return $params;
    }

    public function indexAction() {

    }

    public function channelListAction() {
        $params = $this->getPost(array('page', 'limit', 'channel_name'));
        $where = array();
        if (trim($params['channel_name'])) {
            $channelIds = MobgiSpm_Service_ChannelModel::getChannelIdsByName(trim($params['channel_name']));
            if ($channelIds) {
                $where['id'] = array('IN', $channelIds);
            } else {
                $return = array(
                    'success' => 0,
                    'msg' => '',
                    'count' => 0,
                    'data' => array(),
                );
                exit(json_encode($return));
            }
        }
        $where['delivery_type'] = 1;
        $where = $this->filterParams($where);
        $orderBy = array('update_time' => 'DESC');
        list($total, $channelList) = MobgiSpm_Service_ChannelModel::getChannelList($params['page'], $params['limit'], $where, $orderBy);
        $result = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $channelList,
        );
        exit(json_encode($result));
    }

    public function channelViewAction() {
        $id = intval($this->getInput('id'));
        $channel = MobgiSpm_Service_ChannelModel::getChannelById($id);
        $this->assign('channel', $channel);
        $channelDetail = MobgiSpm_Service_ChannelModel::getChannelDetailById($id);
        $this->assign('channelDetail', $channelDetail);
        $defaultTemplate = $this->getDefaultTemplate();
        $this->assign('defaultTemplate', $defaultTemplate);
        $marcosConfig = Common::getConfig('spmConfig', 'MACROS');
        $backendMacros = $marcosConfig['backend_macros'];
        $this->assign('backendMacros', $backendMacros);
    }

    public function addChannelAction() {
        $id = intval($this->getInput('id'));
        if ($id != 0) {
            $channel = MobgiSpm_Service_ChannelModel::getChannelById($id);
            $this->assign('channel', $channel);
            $channelDetail = MobgiSpm_Service_ChannelModel::getChannelDetailById($id);
            $this->assign('channelDetail', $channelDetail);
        }
        $statusConfig = Common::getConfig('spmConfig', 'MONITOR_STATUS');
        $this->assign('shortLinkStatusList', $statusConfig['shortlink_status']);
        $defaultTemplate = $this->getDefaultTemplate();
        $this->assign('defaultTemplate', $defaultTemplate);
        $marcosConfig = Common::getConfig('spmConfig', 'MACROS');
        $backendMacros = $marcosConfig['backend_macros'];
        $this->assign('backendMacros', $backendMacros);
        $monitorConfig = Common::getConfig('spmConfig', 'MONITOR_SETTING');
        $trackType = $monitorConfig['track_type'];
        $this->assign('trackType', $trackType);
    }

    private function getDefaultTemplate() {
        $spmroot = Yaf_Application::app()->getConfig()->spmroot;
        $shorturlroot = Yaf_Application::app()->getConfig()->shorturlroot;
//        $template['ios_track_template'] = $shorturlroot.'/{shortkey}?idfa={idfa}';
        $template['ios_track_template'] = $shorturlroot . '/{shortkey}';
//        $template['ios_track_template'] = $spmroot.'/track/common?acid={activityid}';
//        $template['android_track_template'] = $shorturlroot.'/{shortkey}?imei={imei}';
        $template['android_track_template'] = $shorturlroot . '/{shortkey}';
//        $template['android_track_template'] = $spmroot.'/track/common?acid={activityid}';
        $template['ios_agent_track_template'] = $spmroot . '/stat/common?acid={activityid}?idfa={idfa}';
        $template['android_agent_track_template'] = $spmroot . '/stat/common?acid={activityid}?imei={imei}';
        return $template;
    }

    public function addChannelPostAction() {
        $id = intval($this->getInput('id'));
        if ($id == 0) {
            $this->addChannelData();
        } else {
            $this->editChannelData($id);
        }
    }

    private function addChannelData() {
        $channelParams = $this->getPost(array('channel_no', 'channel_name', 'shortlink_status'));
        $channelDetailParams = $this->getPost(array('template', 'android_template', 'agent_template', 'agent_android_template',
                                                    'callback_template', 'callback_android_template', 'callback_agent_template', 'callback_agent_android_template'));
        $channelData = $this->checkAddChannelParams($channelParams);
        $channelDetailData = $this->checkAddChannelDetailParams($channelDetailParams);
        $channelData['operator'] = $channelDetailData['operator'] = $this->userInfo['user_name'];
        $id = MobgiSpm_Service_ChannelModel::addChannel($channelData);
        $channelDetailData['channel_id'] = $id;
        MobgiSpm_Service_ChannelModel::addChannelDetail($channelDetailData);
        $this->output(0, '新建成功');
    }

    private function editChannelData($id) {
        $channelParams = $this->getPost(array('channel_name', 'shortlink_status', 'status'));
        $channelDetailParams = $this->getPost(array('template', 'android_template', 'agent_template', 'agent_android_template',
                                                    'callback_template', 'callback_android_template', 'callback_agent_template', 'callback_agent_android_template'));
        $channelData = $this->checkEditChannelParams($channelParams);
        $channelDetailData = $this->checkAddChannelDetailParams($channelDetailParams);
        MobgiSpm_Service_ChannelModel::updateChannel($channelData, array('id' => $id));
        MobgiSpm_Service_ChannelModel::updateChannelDetail($channelDetailData, array('channel_id' => $id));
        $this->output(0, '修改成功');
    }

    private function checkAddChannelParams($params) {
        $params['channel_no'] = trim($params['channel_no']);
        if (!preg_match('/^[A-Za-z0-9_]+$/', $params['channel_no'])) {
            $this->output(-1, '渠道编号只能是字母,数字,下划线组成');
        }
        $params['channel_name'] = trim($params['channel_name']);
        $params['track_type'] = 'api'; // 默认api方式
//        $params['track_type'] = trim($params['track_type']);
//        if( !in_array($params['track_type'], array('api','shortlink')) ){
//            $this->output ( - 1, '请选择跟踪方式' );
//        }
        if (!in_array($params['shortlink_status'], array('OFF', 'ON'))) {
            $this->output(-1, '请选择短链回调状态');
        }
        $params['operator'] = $this->userInfo['user_name'];
        if ($params['channel_no'] == '') {
            $this->output(-1, '请填写渠道编号');
        }
        $result = MobgiSpm_Service_ChannelModel::getChannelByNo($params['channel_no']);
        if ($result) {
            $this->output(-1, '渠道编号已经存在，请重新填写');
        }
        if ($params['channel_name'] == '') {
            $this->output(-1, '请填写渠道名');
        }
        return $params;
    }

    private function checkAddChannelDetailParams($params) {
        $keyArr = array('template', 'android_template', 'agent_template', 'agent_android_template',
                        'callback_template', 'callback_android_template', 'callback_agent_template', 'callback_agent_android_template');
        foreach ($keyArr as $value) {
            $params[$value] = trim($params[$value]);
        }
        return $params;
    }

    private function checkEditChannelParams($params) {
        $data['channel_name'] = trim($params['channel_name']);
        $data['shortlink_status'] = $params['shortlink_status'];
        if ($data['channel_name'] == '') {
            $this->output(-1, '请填写渠道名');
        }
        if (!in_array($data['shortlink_status'], array('OFF', 'ON'))) {
            $this->output(-1, '请选择短链回调状态');
        }
        return $data;
    }

    public function changeChannelStatusAction() {
        $id = intval($this->getInput('id'));
        if ($id == 0) {
            $this->output(-1, '请选择渠道');
        }
        $channelParams = $this->getPost(array('status'));
        if (!in_array($channelParams['status'], array('ON', 'OFF'))) {
            $this->output(-1, '修改状态错误');
        }
        MobgiSpm_Service_ChannelModel::updateChannel($channelParams, array('id' => $id));
        $this->output(0, '修改成功');
    }

    public function changeChannelShortLinkStatusAction() {
        $id = intval($this->getInput('id'));
        if ($id == 0) {
            $this->output(-1, '请选择渠道');
        }
        $channelParams = $this->getPost(array('shortlink_status'));
        if (!in_array($channelParams['shortlink_status'], array('ON', 'OFF'))) {
            $this->output(-1, '修改状态错误');
        }
        MobgiSpm_Service_ChannelModel::updateChannel($channelParams, array('id' => $id));
        $this->output(0, '修改成功');
    }

    /**
     * intelligent replace link params
     */
    public function replaceLinkParamsAction() {
        $params = $this->getPost(array('delivery_type', 'template', 'android_template', 'agent_template',
                                       'agent_android_template', 'callback_template', 'callback_android_template', 'callback_agent_template',
                                       'callback_agent_android_template', 'channel_param', 'backend_param'));
        $deliveryType = intval($params['delivery_type']);
        if (!in_array($deliveryType, array(1, 2, 3, 4))) {
            $this->output(-1, '请选择对接平台模式');
        }
        $channelParam = trim($params['channel_param']);
        $backendParam = trim($params['backend_param']);
        if (empty($channelParam) || empty($backendParam)) {
            $this->output(-1, '渠道参数或后台参数为空');
        }
        switch ($deliveryType) {
            case 1:
                $trackKey = 'template';
                $callbackKey = 'callback_template';
                break;
            case 2:
                $trackKey = 'agent_template';
                $callbackKey = 'callback_agent_template';
                break;
            case 3:
                $trackKey = 'android_template';
                $callbackKey = 'callback_android_template';
                break;
            case 4:
                $trackKey = 'agent_android_template';
                $callbackKey = 'callback_agent_android_template';
                break;
            default:
                $this->output(-1, '平台模式数据异常');
        }
        $result = array();
        # track template
        $template = trim(html_entity_decode($params[$trackKey]));
        $a = explode('?', $template);
        $urlHeader = $a[0];
        $query = $a[1];
        parse_str($query, $arr);
        $arr[$backendParam] = '{' . $channelParam . '}';
        $result[$trackKey] = $urlHeader . '?' . rawurldecode(http_build_query($arr));
        # callback template
        $callbackTemplate = trim(html_entity_decode($params[$callbackKey]));
        if (empty($callbackTemplate)) {
            $result[$callbackKey] = '{' . $backendParam . '}';
        } else {
            $b = explode('?', $callbackTemplate);
            $url_header = $b[0];
            $query = $b[1];
            parse_str($query, $arr);
            $arr[$channelParam] = '{' . $backendParam . '}';
            $result[$callbackKey] = $url_header . '?' . rawurldecode(http_build_query($arr));
        }
        $this->output(0, '更新成功', $result);
    }


    public function groupAction() {
        $channelLabel = MobgiSpm_Service_ChannelModel::getChannelLabel();
        $this->assign('channelLabel', $channelLabel);
    }

    public function channelGroupListAction() {
        $params = $this->getPost(array('page', 'limit', 'label_id', 'name'));
        $where = array();
        if ($params['label_id']) {
            $where['label_id'] = $params['label_id'];
        }
        if (trim($params['name'])) {
            $where['name'] = array('LIKE', trim($params['name']));
        }
        $where = $this->filterParams($where);
        $orderBy = array('id' => 'DESC');
        list($total, $channelGroupList) = MobgiSpm_Service_ChannelModel::getChannelGroupList($params['page'], $params['limit'], $where, $orderBy);
        $channelGroupList = MobgiSpm_Service_ChannelModel::formatChannelGroupList($channelGroupList);
        $result = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $channelGroupList,
        );
        exit(json_encode($result));
    }

    public function addChannelGroupAction() {
        $id = intval($this->getInput('id'));
        $channelLabel = MobgiSpm_Service_ChannelModel::getChannelLabel();
        $this->assign('channelLabel', $channelLabel);
        $channel = MobgiSpm_Service_DeliveryModel::getDeliveryChannel();
        $this->assign('channel', $channel);
        if ($id != 0) {
            $channelGroup = MobgiSpm_Service_ChannelModel::getChannelGroupById($id);
            $this->assign('channelGroup', $channelGroup);
        }
    }

    public function getChannelByGroupIdAction() {
        $id = intval($this->getInput('id'));
        $channel = array();
        if ($id == 0) {
            $channel['selected'] = array();
        } else {
            $prams['group_id'] = $id;
            $channel['selected'] = MobgiSpm_Service_ChannelModel::getChannelByParams($prams);
        }
        $prams['group_id'] = 0;
        $channel['unselected'] = MobgiSpm_Service_ChannelModel::getChannelByParams($prams);
        $this->output(0, '', $channel);
    }

    public function addChannelGroupPostAction() {
        $id = intval($this->getInput('id'));
        $params = $this->getPost(array('name', 'label_id', 'group_channel_range'));
        $groupChannelArr = $params['group_channel_range'];
        $data = $this->checkAddChannelGroupParams($params, $id);
        if ($id == 0) {
            $id = MobgiSpm_Service_ChannelModel::addChannelGroup($data);
            $this->updateChannelGroupId($groupChannelArr, $id);
            $this->output(0, '新建成功');
        } else {
            MobgiSpm_Service_ChannelModel::updateChannelGroup($data, array('id' => $id));
            $this->updateChannelGroupId($groupChannelArr, $id);
            $this->output(0, '修改成功');
        }
    }

    private function updateChannelGroupId($groupChannelArr, $id) {
        # update group_channel
        MobgiSpm_Service_ChannelModel::updateChannel(array('group_id' => 0), array('group_id' => $id));
        if (!empty($groupChannelArr)) {
            $where = array('id' => array('IN', $groupChannelArr));
            MobgiSpm_Service_ChannelModel::updateChannel(array('group_id' => $id), $where);
        }
    }

    private function checkAddChannelGroupParams($params, $id) {
        $data['name'] = trim($params['name']);
        if ($data['name'] == '') {
            $this->output(-1, '请填写渠道组名称');
        }
        $result = MobgiSpm_Service_ChannelModel::getChannelGroupByParams($data, $id);
        if ($result) {
            $this->output(-1, '渠道组名称已存在，请重新填写');
        }
        $data['label_id'] = intval($params['label_id']);
        if ($data['label_id'] == 0) {
            $this->output(-1, '请选择渠道标签');
        }
        return $data;
    }

    public function labelAction() {

    }

    public function labelListAction() {
        $params = $this->getPost(array('page', 'limit', 'name'));
        $where = array();
        if (trim($params['name'])) {
            $where['name'] = array('LIKE', trim($params['name']));
        }
        $where = $this->filterParams($where);
        $orderBy = array('id' => 'DESC');
        list($total, $channelLabelList) = MobgiSpm_Service_ChannelModel::getChannelLabelList($params['page'], $params['limit'], $where, $orderBy);
        $channelLabelList = MobgiSpm_Service_ChannelModel::formatChannelLabelList($channelLabelList);
        $result = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $channelLabelList,
        );
        exit(json_encode($result));
    }

    public function addLabelAction() {
        $id = intval($this->getInput('id'));
        if ($id != 0) {
            $channelLabel = MobgiSpm_Service_ChannelModel::getChannelLabelById($id);
            $this->assign('channelLabel', $channelLabel);
        }
    }


    public function addLabelPostAction() {
        $id = intval($this->getInput('id'));
        $params = $this->getPost(array('name'));
        $data = $this->checkAddLabelParams($params, $id);
        if ($id == 0) {
            $id = MobgiSpm_Service_ChannelModel::addChannelLabel($data);
            $this->output(0, '新建成功');
        } else {
            MobgiSpm_Service_ChannelModel::updateChannelLabel($data, array('id' => $id));
            $this->output(0, '修改成功');
        }
    }

    private function checkAddLabelParams($params, $id) {
        $params['name'] = trim($params['name']);
        if ($params['name'] == '') {
            $this->output(-1, '请填写标签名称');
        }
        $result = MobgiSpm_Service_ChannelModel::getChannelLabelByParams($params, $id);
        if ($result) {
            $this->output(-1, '标签名称已存在，请重新填写');
        }
        return $params;
    }

    public function androidGroupAction() {

    }

    public function androidChannelGroupListAction() {
        $params = $this->getPost(array('page', 'limit', 'app_id', 'name', 'channel_no'));
        $where = array();
//        $where['app_id'] = intval($params['app_id']);
        if (trim($params['name'])) {
            $where['name'] = array('LIKE', trim($params['name']));
        }
        if (trim($params['channel_no'])) {
            $channl = MobgiSpm_Service_ChannelModel::getAndroidChannelByNo(array('channel_no' => trim($params['channel_no'])), 0);
            if ($channl) {
                $where['id'] = $channl['group_id'];
            } else {
                $where['id'] = 0;
            }
        }
        $where = $this->filterParams($where);
        $orderBy = array('id' => 'DESC');
        list($total, $channelGroupList) = MobgiSpm_Service_ChannelModel::getAndroidChannelGroupList($params['page'], $params['limit'], $where, $orderBy);
        $channelGroupList = MobgiSpm_Service_ChannelModel::formatAndroidChannelGroupList($channelGroupList);
        $result = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $channelGroupList,
        );
        exit(json_encode($result));
    }


    public function addAndroidChannelGroupAction() {
        $id = intval($this->getInput('id'));
        $userType = Admin_Service_UserModel::SPM_USER; # delivery user
        $userList = Admin_Service_UserModel::getsBy(array('user_type' => $userType));
        if ($id != 0) {
            $channelGroup = MobgiSpm_Service_ChannelModel::getChannelGroupById($id);
            $this->assign('channelGroup', $channelGroup);
            $channel = MobgiSpm_Service_ChannelModel::getAndroidChannelByGroupId($id);
            $channel = MobgiSpm_Service_ChannelModel::formatAndroidChannelList($channel);
            $this->assign('channel', $channel);
            # 清除已经存在列表的user
            foreach ($userList as $key => $value) {
                if (array_key_exists($value['user_name'], $channel)) {
                    unset($userList[$key]);
                }
            }
        }
        $this->assign('userList', $userList);
    }

    public function addAndroidChannelGroupPostAction() {
        $id = intval($this->getInput('id'));
        $params = $this->getPost(array('name', 'app_id', 'channel_name', 'channel_no', 'operator'));
        $channlNameArr = $params['channel_name'];
        $channlNoArr = $params['channel_no'];
        $operatorArr = $params['operator'];
        $data['operator'] = $this->userInfo['user_name'];

        if ($id == 0) {
//            $id = MobgiSpm_Service_ChannelModel::addAndroidChannelGroup($data);
//            $this->updateAndroidChannelGroupId($channlNameArr, $channlNoArr, $operatorArr, $data['app_id'], $id);
            $this->output(0, '渠道组不能为空');
        } else {
            $data = $this->checkAddAndroidChannelGroupParams($params, $id);
//            MobgiSpm_Service_ChannelModel::updateAndroidChannelGroup($data, array('id' => $id));
            $this->updateAndroidChannelGroupId($channlNameArr, $channlNoArr, $operatorArr, $data['app_id'], $id);
            $this->output(0, '修改成功');
        }
    }

    private function updateAndroidChannelGroupId($channelNameArr, $channelNoArr, $operatorArr, $appId, $id) {
        # update list
        MobgiSpm_Service_ChannelModel::delAndroidChannelByGroupId($id);
        if (!empty($channelNoArr)) {
            $data = array(
                'app_id' => $appId,
                'group_id' => $id,
            );
            foreach ($channelNoArr as $key => $value) {
                $data['channel_name'] = $channelNameArr[$key];
                $data['channel_no'] = $channelNoArr[$key];
                $data['operator'] = $operatorArr[$key];
                MobgiSpm_Service_ChannelModel::addAndroidChannel($data);
            }
        }
    }

    private function checkAddAndroidChannelGroupParams($params, $id) {
        $data['app_id'] = intval($params['app_id']);
        $data['operator'] = $this->userInfo['user_name'];
        $channlNameArr = $params['channel_name'];
        $channlNoArr = $params['channel_no'];
        foreach ($channlNameArr as $key => $value) {
            $channelName = trim($channlNameArr[$key]);
            $channelNo = trim($channlNoArr[$key]);
            $num = $key + 1;
            if (empty($channelName)) {
                $this->output(-1, '第' . $num . '行渠道名称为空');
            }
            if (empty($channelNo)) {
                $this->output(-1, '第' . $num . '行渠道号为空');
            }
            # check channel_no
            $where['channel_no'] = $channelNo;
            $result = MobgiSpm_Service_ChannelModel::getAndroidChannelByNo($where, $id);
            if ($result) {
                $this->output(-1, '第' . $num . '行渠道号已被其他渠道组占用');
            }
        }
        $count = count($channlNoArr);
        $distinctCount = count(array_unique($channlNoArr));
        if ($count != $distinctCount) {
            $this->output(-1, '列表中存在重复渠道号，请核查');
        }
        return $data;
    }

    public function gdtConfigAction() {

    }

    public function gdtConfigListAction() {
        $params = $this->getPost(array('page', 'limit', 'app_id', 'advertiser_id'));
        $where = array();
        if (!empty($params['app_id'])) {
            $where['monitor_app_id'] = $params['app_id'];
        }
        if (trim($params['advertiser_id'])) {
            $where['advertiser_id'] = array('LIKE', trim($params['advertiser_id']));
        }
        $where = $this->filterParams($where);
        $orderBy = array('id' => 'DESC');
        list($total, $gdtConfigList) = MobgiSpm_Service_ChannelModel::getGdtConfigList($params['page'], $params['limit'], $where, $orderBy);
        $gdtConfigList = MobgiSpm_Service_ChannelModel::formatGdtConfigList($gdtConfigList);
        $result = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $gdtConfigList,
        );
        exit(json_encode($result));
    }

    public function gdtConfigUnitAction() {
        $id = $this->getPost('id');
        $Unit = MobgiSpm_Service_ChannelModel::getGdtConfigById($id);
        $this->output(0, '', $Unit);
    }

    public function addGdtConfigPostAction() {
        $params = $this->getPost(array('id', 'app_id', 'gdt_app_id', 'advertiser_id', 'sign_key', 'encrypt_key'));
        $id = intval($params['id']);
        $params = $this->checkAddGdtConfigParams($params);
        if ($id == 0) { # add
            $id = MobgiSpm_Service_ChannelModel::addGdtConfig($params);
            $this->output(0, '新建成功');
        } else { # edit
            MobgiSpm_Service_ChannelModel::updateGdtConfig($params, array('id' => $id));
            $this->output(0, '修改成功');
        }
    }

    private function checkAddGdtConfigParams($params) {
        $data['monitor_app_id'] = intval($params['app_id']);
        $data['app_id'] = intval(trim($params['gdt_app_id']));
        $data['advertiser_id'] = intval(trim($params['advertiser_id']));
        $data['sign_key'] = trim($params['sign_key']);
        $data['encrypt_key'] = trim($params['encrypt_key']);
        $data['operator'] = $this->userInfo['user_name'];
        if (empty($data['monitor_app_id'])) {
            $this->output(-1, '请选择投放应用');
        }
        if (empty($data['app_id'])) {
            $this->output(-1, '请填写广点通应用ID');
        }
        if (empty($data['advertiser_id'])) {
            $this->output(-1, '请输入广告主id');
        }
        if (empty($data['sign_key'])) {
            $this->output(-1, '请输入sign_key');
        }
        if (empty($data['encrypt_key'])) {
            $this->output(-1, '请输入encrypt_key');
        }
//        $app = MobgiSpm_Service_DeliveryModel::getAppById($data['app_id']);
//        $data['app_id'] = $app['appstore_id'];
        return $data;
    }

    public function gdtPayConfigAction() {

    }

    public function gdtPayConfigListAction() {
        $params = $this->getPost(array('page', 'limit', 'advertiser_id'));
        $where = array();
        if (trim($params['advertiser_id'])) {
            $where['advertiser_id'] = array('LIKE', trim($params['advertiser_id']));
        }
        $where = $this->filterParams($where);
        $orderBy = array('id' => 'DESC');
        list($total, $gdtPayConfigList) = MobgiSpm_Service_ChannelModel::getGdtPayConfigList($params['page'], $params['limit'], $where, $orderBy);
        $gdtPayConfigList = MobgiSpm_Service_ChannelModel::formatGdtPayConfigList($gdtPayConfigList);
        $result = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $gdtPayConfigList,
        );
        exit(json_encode($result));
    }

    public function gdtPayConfigUnitAction() {
        $id = $this->getPost('id');
        $Unit = MobgiSpm_Service_ChannelModel::getGdtPayConfigById($id);
        $this->output(0, '', $Unit);
    }

    public function addGdtPayConfigPostAction() {
        $params = $this->getPost(array('id', 'advertiser_id', 'notice'));
        $id = intval($params['id']);
        $params = $this->checkAddGdtPayConfigParams($params);
        if ($id == 0) { # add
            $id = MobgiSpm_Service_ChannelModel::addGdtPayConfig($params);
            $this->output(0, '新建成功');
        } else { # edit
            MobgiSpm_Service_ChannelModel::updateGdtPayConfig($params, array('id' => $id));
            $this->output(0, '修改成功');
        }
    }

    private function checkAddGdtPayConfigParams($params) {
        $data['advertiser_id'] = intval($params['advertiser_id']);
        $data['notice'] = trim($params['notice']);
        $data['operator'] = $this->userInfo['user_name'];
        if (empty($data['advertiser_id'])) {
            $this->output(-1, '请输入广告主id');
        }
        return $data;
    }

    public function changeGdtPayConfigStatusAction() {
        $id = intval($this->getInput('id'));
        if ($id == 0) {
            $this->output(-1, '请选择配置');
        }
        $params = $this->getPost(array('status'));
        if (!in_array($params['status'], array('ON', 'OFF'))) {
            $this->output(-1, '修改状态错误');
        }
        MobgiSpm_Service_ChannelModel::updateGdtPayConfig($params, array('id' => $id));
        $this->output(0, '修改成功');
    }

    public function gdtActionConfigAction() {

    }

    public function gdtActionConfigListAction() {
        $params = $this->getPost(array('page', 'limit', 'advertiser_id'));
        $where = array();
        if (trim($params['advertiser_id'])) {
            $where['account_id'] = array('LIKE', trim($params['advertiser_id']));
        }
        $where = $this->filterParams($where);
        $orderBy = array('id' => 'DESC');
        list($total, $gdtActionConfigList) = MobgiSpm_Service_ChannelModel::getGdtActionConfigList($params['page'], $params['limit'], $where, $orderBy);
        $gdtActionConfigList = MobgiSpm_Service_ChannelModel::formatGdtActionConfigList($gdtActionConfigList);
        $result = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $gdtActionConfigList,
        );
        exit(json_encode($result));
    }

    public function gdtActionConfigUnitAction() {
        $id = $this->getPost('id');
        $Unit = MobgiSpm_Service_ChannelModel::getGdtActionConfigById($id);
        $this->output(0, '', $Unit);
    }

    public function addGdtActionConfigPostAction() {
        $params = $this->getPost(array('id', 'app_id', 'app_key', 'app_name', 'account_id'));
        $id = intval($params['id']);
        $params = $this->checkAddGdtActionConfigParams($params);
        if ($id == 0) { # add
            $id = MobgiSpm_Service_ChannelModel::addGdtActionConfig($params);
            $this->output(0, '新建成功');
        } else { # edit
            MobgiSpm_Service_ChannelModel::updateGdtActionConfig($params, array('id' => $id));
            $this->output(0, '修改成功');
        }
    }

    private function checkAddGdtActionConfigParams($params) {
        $data['app_id'] = intval($params['app_id']);
        $data['app_key'] = trim($params['app_key']);
        $data['app_name'] = trim($params['app_name']);
        $data['account_id'] = intval($params['account_id']);
        $data['operator'] = $this->userInfo['user_name'];
        if (empty($data['app_id'])) {
            $this->output(-1, '请输入app_id');
        }
        if (empty($data['app_key'])) {
            $this->output(-1, '请输入应用secret');
        }
        if (empty($data['app_name'])) {
            $this->output(-1, '请输入应用名称');
        }
        if (empty($data['account_id'])) {
            $this->output(-1, '请输入广点通账号id');
        }
        return $data;
    }

    public function baiduConfigAction() {

    }

    public function baiduConfigListAction() {
        $params = $this->getPost(array('page', 'limit', 'user_id'));
        $where = array();
        if (trim($params['user_id'])) {
            $where['user_id'] = array('LIKE', trim($params['user_id']));
        }
        $where = $this->filterParams($where);
        $orderBy = array('id' => 'DESC');
        list($total, $configList) = MobgiSpm_Service_ChannelModel::getBaiduConfigList($params['page'], $params['limit'], $where, $orderBy);
        $configList = MobgiSpm_Service_ChannelModel::formatBaiduConfigList($configList);
        $result = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $configList,
        );
        exit(json_encode($result));
    }

    public function baiduConfigUnitAction() {
        $id = $this->getPost('id');
        $Unit = MobgiSpm_Service_ChannelModel::getBaiduConfigById($id);
        $this->output(0, '', $Unit);
    }

    public function addBaiduConfigPostAction() {
        $params = $this->getPost(array('id', 'user_id', 'akey'));
        $id = intval($params['id']);
        $params = $this->checkAddBaiduConfigParams($params);
        if ($id == 0) { # add
            $id = MobgiSpm_Service_ChannelModel::addBaiduConfig($params);
            $this->output(0, '新建成功');
        } else { # edit
            MobgiSpm_Service_ChannelModel::updateBaiduConfig($params, array('id' => $id));
            $this->output(0, '修改成功');
        }
    }

    private function checkAddBaiduConfigParams($params) {
        $data['user_id'] = intval($params['user_id']);
        $data['akey'] = trim($params['akey']);
        $data['operator'] = $this->userInfo['user_name'];
        if (empty($data['user_id'])) {
            $this->output(-1, '请输入账户id');
        }
        if (empty($data['akey'])) {
            $this->output(-1, '请输入应用akey');
        }
        return $data;
    }

    public function payConfigAction() {

    }

    public function payConfigListAction() {
        $params = $this->getPost(array('page', 'limit', 'channel_name'));
        $where = array();
        if (trim($params['channel_name'])) {
            $channelIds = MobgiSpm_Service_ChannelModel::getPayConfigIdsByName(trim($params['channel_name']));
            if ($channelIds) {
                $where['id'] = array('IN', $channelIds);
            } else {
                return array(
                    'success' => 0,
                    'msg' => '',
                    'count' => 0,
                    'data' => array(),
                );
            }
        }
        $where = $this->filterParams($where);
        $orderBy = array('id' => 'DESC');
        list($total, $configList) = MobgiSpm_Service_ChannelModel::getPayConfigList($params['page'], $params['limit'], $where, $orderBy);
        $configList = MobgiSpm_Service_ChannelModel::formatPayConfigList($configList);
        $result = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $configList,
        );
        exit(json_encode($result));
    }

    public function addPayConfigAction() {
        $id = intval($this->getInput('id'));
        $payConfig = MobgiSpm_Service_ChannelModel::getPayConfigById($id);
        $this->assign('payConfig', $payConfig);
        $channel = MobgiSpm_Service_DeliveryModel::getDeliveryChannel();
        $this->assign('channel', $channel);
    }

    public function changePayConfigStatusAction() {
        $id = intval($this->getInput('id'));
        if ($id == 0) {
            $this->output(-1, '请选择配置');
        }
        $params = $this->getPost(array('status'));
        if (!in_array($params['status'], array('ON', 'OFF'))) {
            $this->output(-1, '修改状态错误');
        }
        MobgiSpm_Service_ChannelModel::updatePayConfig($params, array('id' => $id));
        $this->output(0, '修改成功');
    }


    public function addPayConfigPostAction() {
        $params = $this->getPost(array('id', 'channel_id', 'pay_callback', 'uptime', 'downtime', 'addval'));
        $id = intval($params['id']);
        $params = $this->checkAddPayConfigParams($params, $id);
        if ($id == 0) { # add
            $id = MobgiSpm_Service_ChannelModel::addPayConfig($params);
            $this->output(0, '新建成功');
        } else { # edit
            MobgiSpm_Service_ChannelModel::updatePayConfig($params, array('id' => $id));
            $this->output(0, '修改成功');
        }
    }

    private function checkAddPayConfigParams($params, $id) {
        $data['channel_id'] = intval($params['channel_id']);
        $data['pay_callback'] = trim($params['pay_callback']);
        $data['uptime'] = intval($params['uptime']);
        $data['downtime'] = trim($params['downtime']);
        $data['addval'] = trim($params['addval']);
        $data['operator'] = $this->userInfo['user_name'];
        if (empty($data['channel_id'])) {
            $this->output(-1, '请选择广告渠道');
        }
        $payConfig = MobgiSpm_Service_ChannelModel::getPayConfigByChannelId($data['channel_id'], $id);
        if ($payConfig) {
            $this->output(-1, '该广告渠道已经存在配置');
        }
        $channel = MobgiSpm_Service_ChannelModel::getChannelById($data['channel_id']);
        if (!$channel) {
            $this->output(-1, '广告渠道异常');
        }
        $data['channel_name'] = $channel['channel_name'];
        $data['channel_no'] = $channel['channel_no'];
        if (empty($data['pay_callback'])) {
            $this->output(-1, '请输入付费回调地址');
        }
        return $data;
    }

    public function specConfigAction() {
        $id = $this->getInput('id');
        if (empty($id)) exit('error！');
        $channelGroup = MobgiSpm_Service_ChannelModel::getChannelGroupById($id);
        $where['group_id'] = $id;
        $configs = MobgiSpm_Service_ChannelModel::getChannelGroupSpconfig($id);
        $configsList = array();
        foreach ($configs as $key => $val) {
            $configsList[$val['type']][] = json_decode($val['conf'], true);
        }
        $this->assign('configs', $configsList);
        $this->assign('channelGroup', $channelGroup);
    }


    public function delSpecAction() {
        $id = $this->getInput('id');
        if (MobgiSpm_Service_ChannelModel::getSpmDao('MonitorChannelGroupSpconfig')->delete($id)) {
            $this->output(1, '删除成功!');
        } else {
            $this->output(0, '删除失败!');
        }
    }

    public function addSpecAction() {
        if ($this->isPost()) {
            $flag = true;
            foreach (self::$specType as $type) {
                if ($type == 1) {
                    $length = count($_POST['width-' . $type]) - 1;
                    $width = $this->getInput('width-' . $type);
                    $height = $this->getInput('height-' . $type);
                    $max_time = $this->getInput('max_time-' . $type);
                    $min_time = $this->getInput('min_time-' . $type);
                    $size = $this->getInput('size-' . $type);
                } else if ($type == 2) {
                    $width = $this->getInput('width-' . $type);
                    $height = $this->getInput('height-' . $type);
                    $size = $this->getInput('size-' . $type);
                    $length = count($_POST['width-' . $type]) - 1;
                } else if ($type == 3) {
                    $max = $this->getInput('max-' . $type);
                    $min = $this->getInput('min-' . $type);
                    $length = count($_POST['max-' . $type]) - 1;
                }
                $ids = $this->getInput('id-' . $type);
                for ($i = 0; $i <= $length; ++$i) {
                    $oneInsert = array(
                        'type' => $type,
                        'group_id' => intval($this->getInput('group_id')),
                        'create_time' => date("Y-m-d")
                    );
                    //检查是更新操作还是新增
                    if (intval($ids[$i]) === 0) {
                        if (MobgiSpm_Service_ChannelModel::getSpmDao('MonitorChannelGroupSpconfig')->insert($oneInsert)) {
                            $id = MobgiSpm_Service_ChannelModel::getSpmDao('MonitorChannelGroupSpconfig')->getLastInsertId();
                        }
                    } else {
                        $id = $ids[$i];
                    }
                    switch ($type) {
                        case 1:
                            $jsonData = array(
                                'id' => $id,
                                'width' => $width[$i],
                                'height' => $height[$i],
                                'min_time' => $min_time[$i],
                                'max_time' => $max_time[$i],
                                'size' => $size[$i]
                            );
                            break;
                        case 2:
                            $jsonData = array(
                                'id' => $id,
                                'width' => $width[$i],
                                'height' => $height[$i],
                                'size' => $size[$i]
                            );
                            break;
                        case 3:
                            $jsonData = array(
                                'id' => $id,
                                'min' => $min[$i],
                                'max' => $max[$i],
                            );
                            break;
                    }
                    //var_dump($jsonData);
                    $updateData = array('conf' => json_encode($jsonData));
                    $where = array('id' => $id);
                    if (!MobgiSpm_Service_ChannelModel::getSpmDao('MonitorChannelGroupSpconfig')->updateBy($updateData, $where)) {
                        $flag = false;
                    }
                }
            }
            if ($flag) {
                $this->output(0, '修改成功!');
            } else {
                $this->output(-1, '修改失败!');
            }
        }
    }
}