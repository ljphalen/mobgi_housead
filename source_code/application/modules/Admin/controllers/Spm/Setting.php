<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/29
 * Time: 15:34
 */

class Spm_SettingController extends Admin_BaseController{

    public function init() {
        parent::init();
        $module = $this->getTopModule();
        $this->assign('module', $module);
    }

    public $actions = [
        'productUrl' => '/Admin/Spm_Setting/product',
        'addProductDetailPostUrl' => '/Admin/Spm_Setting/addProductDetailPost',
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

    public function productAction(){
        $appId = $_COOKIE['app_id'];
        if(empty($appId)){
            return false;
        }
        $monitorConfig = Common::getConfig('spmConfig', 'MONITOR_SETTING');
        $trackTime = $monitorConfig['track_time'];
        $deliveryType = $monitorConfig['delivery_type'];
        $this->assign('trackTime', $trackTime);
        $trackStatus = $monitorConfig['track_status'];
        $this->assign('trackStatus', $trackStatus);
        $app = MobgiSpm_Service_DeliveryModel::getAppById($appId);
        if($app){
            $app['delivery_type'] = isset($deliveryType[$app['delivery_type']]) ? $deliveryType[$app['delivery_type']] : '未知类型';
        }
        $this->assign('app', $app);
        $appDetail = MobgiSpm_Service_DeliveryModel::getAppDetailById($appId);
        $this->assign('appDetail', $appDetail);
    }

    public function addProductDetailPostAction(){
        $params = $this->getPost( array('app_id','api_click_period','ipua_click_period',
            'backflow_status','second_track_status','active_front_time','active_behind_time') );
        $appId = intval($params['app_id']);
        $params = $this->checkaddProductDetailParams($params, $appId);
        $appDetail = MobgiSpm_Service_DeliveryModel::getAppDetailById($appId);
        if($appDetail){ # edit
            MobgiSpm_Service_DeliveryModel::updateAppDetail($params, array('app_id' => $appId));
            $this->output(0, '保存成功');
        }else{ # add
            $id = MobgiSpm_Service_DeliveryModel::addAppDetail($params);
            $this->output(0, '保存成功');
        }
    }

    private function checkaddProductDetailParams($params)
    {
        $data['app_id'] = intval($params['app_id']);
        $data['api_click_period'] = intval($params['api_click_period']);
        $data['ipua_click_period'] = intval($params['ipua_click_period']);
        $data['backflow_status'] = intval($params['backflow_status']);
        $data['second_track_status'] = intval($params['second_track_status']);
        $data['active_front_time'] = intval($params['active_front_time']);
        $data['active_behind_time'] = intval($params['active_behind_time']);
        $data['operator'] = $this->userInfo['user_name'];
        if(empty($data['app_id'])){
            $this->output ( - 1, '应用参数错误' );
        }
        if($data['api_click_period'] == 0){
            $this->output ( - 1, '请选择api点击有效期' );
        }
        if($data['ipua_click_period'] == 0){
            $this->output ( - 1, '请选择ipua点击有效期' );
        }
        if($data['second_track_status'] == 1){
            if($data['active_front_time'] <= 0 || $data['active_behind_time'] <= 0){
                $this->output ( - 1, '重新匹配范围区间必须为正值' );
            }
        }
        return $data;
    }

    public function defendAction(){

    }
}
