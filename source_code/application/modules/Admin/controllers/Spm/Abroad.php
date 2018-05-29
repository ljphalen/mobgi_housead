<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/4
 * Time: 15:08
 */

class Spm_AbroadController extends Admin_BaseController{

    public function init() {
        parent::init();
        $module = $this->getTopModule();
        $this->assign('module', $module);
    }

    public $actions = [
        'appsflyerAppUrl' => '/Admin/Spm_Abroad/appsflyerApp',
        'appsflyerAppListUrl' => '/Admin/Spm_Abroad/appsflyerAppList',
        'addAppsflyerAppUrl' => '/Admin/Spm_Abroad/addAppsflyerApp',
        'addAppsflyerAppPostUrl' => '/Admin/Spm_Abroad/addAppsflyerAppPost',
        'appsflyerActivityUrl' => '/Admin/Spm_Abroad/appsflyerActivity',
        'appsflyerActivityListUrl' => '/Admin/Spm_Abroad/appsflyerActivityList',
        'appsflyerChannelUrl' => '/Admin/Spm_Abroad/appsflyerChannel',
        'appsflyerChannelListUrl' => '/Admin/Spm_Abroad/appsflyerChannelList',
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

    public function appsflyerAppAction(){

    }

    public function appsflyerAppListAction(){
        $params = $this->getPost( array('page','limit','app_id','appsflyer_appid') );
        $where = array();
        $where['app_id'] = $params['app_id'];
        if (trim($params['appsflyer_appid'])) {
            $where['appsflyer_appid'] = array('LIKE', trim($params['appsflyer_appid']));
        }
        $where = $this->filterParams($where);
        $orderBy = array('id'=>'DESC');
        list($total, $List)  = MobgiSpm_Service_AbroadModel::getAppsflyerAppList($params['page'], $params['limit'], $where, $orderBy);
        $List = MobgiSpm_Service_AbroadModel::formatAppsflyerAppList($List);
        $result  = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $List,
        );
        exit(json_encode($result));
    }

    public function addAppsflyerAppAction(){
        $id = intval($this->getInput('id'));
        $appsflyerConfig = MobgiSpm_Service_AbroadModel::getAppsflyerAppById($id);
        $this->assign('appsflyerConfig', $appsflyerConfig);
    }

    public function addAppsflyerAppPostAction(){
        $params = $this->getPost( array('id','app_id','appsflyer_appid') );
        $id = intval($params['id']);
        $params = $this->checkAddAppsflyerAppParams($params, $id);
        if($id == 0){ # add
            $id = MobgiSpm_Service_AbroadModel::addAppsflyerApp($params);
            $this->output(0, '新建成功');
        }else{ # edit
            MobgiSpm_Service_AbroadModel::updateAppsflyerApp($params, array('id' => $id));
            $this->output(0, '修改成功');
        }
    }

    private function checkAddAppsflyerAppParams($params, $id)
    {
        $data['app_id'] = intval($params['app_id']);
        $data['appsflyer_appid'] = trim($params['appsflyer_appid']);
        if(empty($data['app_id'])){
            $this->output ( - 1, '应用参数错误' );
        }
        if(empty($data['appsflyer_appid'])){
            $this->output ( - 1, '请输入AF的appid' );
        }
        $Config = MobgiSpm_Service_AbroadModel::getAppsflyerAppByAppId($data['app_id'], $data['appsflyer_appid'], $id);
        if($Config){
            $this->output ( - 1, '该配置已经存在' );
        }
        return $data;
    }

    public function appsflyerActivityAction(){
        $channelList  = MobgiSpm_Service_AbroadModel::getDeliveryChannel();
        $this->assign('channelList', $channelList);
    }

    public function appsflyerActivityListAction(){
        $params = $this->getPost( array('page','limit','app_id','channel_id','name','sdate','edate') );
        $where = array(
            'app_id' => $params['app_id'],
            'channel_id' => $params['channel_id'],
            'name' => array('like',$params['name']),
            'data_platform' => 'appsflyer',
        );
        $where = $this->filterParams($where);
        if(!empty($params['sdate']) && !empty($params['edate'])){
            $stime = strtotime($params['sdate']);
            $etime = strtotime($params['edate'] . ' 23:59:59');
            $where['create_time'] = array(array('>=', $stime), array('<=', $etime));
        }
        $orderBy = array('id'=>'DESC');
        list($total, $activityList)  = MobgiSpm_Service_AbroadModel::getActivityList($params['page'], $params['limit'], $where, $orderBy);
        $activityList = MobgiSpm_Service_AbroadModel::formatActivityList($activityList);
        $result  = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $activityList,
        );
        exit(json_encode($result));
    }

    public function appsflyerChannelAction(){

    }

    public function appsflyerChannelListAction(){
        $params = $this->getPost( array('page','limit','channel_name') );
        $where = array();
        if (trim($params['channel_name'])) {
            $channelIds = MobgiSpm_Service_ChannelModel::getChannelIdsByName(trim($params['channel_name']));
            if($channelIds){
                $where['id'] = array('IN',$channelIds);
            }else{
                return array(
                    'success' => 0,
                    'msg' => '',
                    'count' => 0,
                    'data' => array(),
                );
            }
        }
        $where['delivery_type'] = 0;
        $where = $this->filterParams($where);
        $orderBy = array('id'=>'DESC');
        list($total, $channelList)  = MobgiSpm_Service_ChannelModel::getChannelList($params['page'], $params['limit'], $where, $orderBy);
        $channelList = MobgiSpm_Service_ChannelModel::formatChannelList($channelList);
        $result  = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $channelList,
        );
        exit(json_encode($result));
    }

}