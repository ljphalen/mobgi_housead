<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/4
 * Time: 15:04
 */

class Spm_DataPlatformController extends Admin_BaseController{

    public function init() {
        parent::init();
        $module = $this->getTopModule();
        $this->assign('module', $module);
    }

    public $actions = [
        'indexUrl' => '/Admin/Spm_DataPlatform/index',
        'dataPlatformListUrl' => '/Admin/Spm_DataPlatform/dataPlatformList',
        'changePlatformStatusUrl' => '/Admin/Spm_DataPlatform/changePlatformStatus',
        'addPlatformUrl' => '/Admin/Spm_DataPlatform/addPlatform',
        'addPlatformPostUrl' => '/Admin/Spm_DataPlatform/addPlatformPost',
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

    public function indexAction(){

    }

    public function dataPlatformListAction(){
        $params = $this->getPost( array('page','limit','name') );
        $where = array();
        if (trim($params['name'])) {
            $where['name'] = array('LIKE', trim($params['name']));
        }
        $where = $this->filterParams($where);
        $orderBy = array('id'=>'DESC');
        list($total, $platformList)  = MobgiSpm_Service_DeliveryModel::getMonitorPlatformList($params['page'], $params['limit'], $where, $orderBy);
        $platformList = MobgiSpm_Service_DeliveryModel::formatMonitorPlatformList($platformList);
        $result  = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $platformList,
        );
        exit(json_encode($result));
    }

    public function changePlatformStatusAction(){
        $id = intval($this->getInput('id'));
        if($id == 0){
            $this->output ( - 1, '请选择平台' );
        }
        $params = $this->getPost( array('status') );
        if( !in_array($params['status'], array('ON','OFF')) ){
            $this->output ( - 1, '修改状态错误' );
        }
        MobgiSpm_Service_DeliveryModel::updateMonitorPlatform($params, array('id' => $id));
        $this->output(0, '修改成功');
    }

    public function addPlatformAction(){
        $id = intval($this->getInput('id'));
        $monitorPlatform = MobgiSpm_Service_DeliveryModel::getMonitorPlatformById($id);
        $this->assign('monitorPlatform', $monitorPlatform);
        $monitorConfig = Common::getConfig('spmConfig', 'MONITOR_PLATFORM');
        $platformType = $monitorConfig['type'];
        $this->assign('platformType', $platformType);
    }

    public function addPlatformPostAction(){
        $params = $this->getPost( array('id','platform_no','name','type','click_template','active_callback_template','register_callback_template') );
        $id = intval($params['id']);
        $params = $this->checkAddPlatformParams($params, $id);
        if($id == 0){ # add
            $id = MobgiSpm_Service_DeliveryModel::addMonitorPlatform($params);
            $this->output(0, '新建成功');
        }else{ # edit
            MobgiSpm_Service_DeliveryModel::updateMonitorPlatform($params, array('id' => $id));
            $this->output(0, '修改成功');
        }
    }

    private function checkAddPlatformParams($params, $id)
    {
        $data['platform_no'] = trim($params['platform_no']);
        $data['name'] = trim($params['name']);
        $data['type'] = intval($params['type']);
        $data['click_template'] = trim($params['click_template']);
        $data['active_callback_template'] = trim($params['active_callback_template']);
        $data['register_callback_template'] = trim($params['register_callback_template']);
        $data['operator'] = $this->userInfo['user_name'];
        if(empty($data['platform_no'])){
            $this->output ( - 1, '请输入平台编号' );
        }
        if(empty($data['name'])){
            $this->output ( - 1, '请输入平台名称' );
        }
        if(empty($data['type'])){
            $this->output ( - 1, '请选择平台类型' );
        }
        $config = MobgiSpm_Service_DeliveryModel::getMonitorPlatformByNo($data['platform_no'], $id);
        if($config){
            $this->output ( - 1, '该平台编号已经存在' );
        }
        if(empty($data['click_template'])){
            $this->output ( - 1, '请输入点击接收模板' );
        }
        if(empty($data['active_callback_template'])){
            $this->output ( - 1, '请输入激活接收模板' );
        }
        return $data;
    }

}