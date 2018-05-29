<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/9/14
 * Time: 15:05
 */

class Data_BalanceController extends Admin_BaseController {

    public $actions = [
        'globalConfigUrl' => '/Admin/Data_Balance/globalConfig',
        'addGlobalConfigUrl' => '/Admin/Data_Balance/addGlobalConfig',
        'addGlobalConfigPostUrl' => '/Admin/Data_Balance/addGlobalConfigPost',
        'customConfigUrl' => '/Admin/Data_Balance/customConfig',
        'addCustomConfigUrl' => '/Admin/Data_Balance/addCustomConfig',
        'addCustomConfigPostUrl' => '/Admin/Data_Balance/addCustomConfigPost',
    ];

    public $perpage = 20;

    /*
     * 媒体分成全局配置
     */
    public function globalConfigAction() {
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $orderBy = array('effect_date'=>'DESC','create_time'=>'DESC');
        list($total, $configList)  = MobgiData_Service_BalanceModel::getDivisionGlobalList($page, $this->perpage, $params, $orderBy);
        $url = $this->actions['globalConfigUrl'];
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));

        $this->assign('total', $total);
        $this->assign('configList', $configList);
        $config_status = Common::getConfig('adminConfig', 'Admin_division_config_status');
        $this->assign('configStatus', $config_status);

    }

    /*
     * 新增媒体分成全局配置
     */
    public function addGlobalConfigAction() {
        $this->assign('navTitle', '添加');
    }

    public function addGlobalConfigPostAction() {
        $info = $this->getPost ( array (
            'ads_division_rate',
            'own_division_rate',
            'notice'
        ) );
        $info = $this->checkPostParam ( $info );
        MobgiData_Service_BalanceModel::updateDivisionGlobalConfig(
            array('status'=>Common_Service_Const::STATUS_NOT_ACTIVE),array('status'=>Common_Service_Const::STATUS_ACTIVE)
        );
        $effect_date = date('Y-m-d');
        $operator = $this->userInfo['user_name'];
        $result = MobgiData_Service_BalanceModel::addDivisionGlobalConfig(
            $info['ads_division_rate'],$info['own_division_rate'],
            $effect_date,Common_Service_Const::STATUS_ACTIVE,time(),$operator,$info['notice']
        );
        if (! $result) {
            $this->output ( - 1, '操作失败' );
        }
        $this->output ( 0, '操作成功');

    }

    private function checkPostParam($info)
    {
        $info['ads_division_rate'] = floatval($info['ads_division_rate']);
        $info['own_division_rate'] = floatval($info['own_division_rate']);
        if ($info['ads_division_rate'] < 0 || $info['ads_division_rate'] > 1){
            $this->output ( - 1, '广告商利益分成比例范围 0 - 1' );
        }
        if ($info['own_division_rate'] < 0 || $info['own_division_rate'] > 1){
            $this->output ( - 1, '自投利益分成比例范围 0 - 1' );
        }
        $info['notice'] = trim($info['notice']);
        return $info;
    }

    /*
     * 媒体分成定制配置
     */
    public function customConfigAction() {
        $search = $this->getInput ( array (
            'developer',
            'app_name',
            'status',
        ) );
        if(!isset($search['status'])){
            $search['status'] = Common_Service_Const::STATUS_ACTIVE;
        }
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
        $orderBy = array('effect_date'=>'DESC','create_time'=>'DESC');
        list($total, $configList) = MobgiData_Service_BalanceModel::getDivisionCustomList($page, $this->perpage, $search, $orderBy);
        $url = $this->actions['customConfigUrl'];
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));

        $this->assign('total', $total);
        $this->assign('configList', $configList);
        $userList = Common::resetKey($this->getUserList(),'user_id');
        $this->assign('userList', $userList);
        $platform = array(
            Common_Service_Const::ANDRIOD_PLATFORM => '(A)',
            Common_Service_Const::IOS_PLATFORM => '(I)'
        );
        $this->assign('platform', $platform);
        $this->assign('search', $search);
        $config_status = Common::getConfig('adminConfig', 'Admin_division_config_status');
        $this->assign('configStatus', $config_status);

    }

    /*
     * 新增媒体分成定制配置
     */
    public function addCustomConfigAction() {
        $ids = $this->getGet('ids');
        $idsList = explode('|',$ids);
        $idsList = array_unique($idsList); // 对相同元素去重
        $this->assign('navTitle', '添加');
        if (!empty($app_list)) {
            $this->assign('navTitle', '重置');
        }
        $appMap = MobgiData_Service_BaseModel::getAppKeyMap();
        $this->assign('applist',$appMap);
        $this->assign('idslist',$idsList);
    }


    public function addCustomConfigPostAction() {
        $info = $this->getPost ( array (
            'app_key',
            'ads_division_rate',
            'own_division_rate',
            'notice'
        ) );
        if(empty($info['app_key'])){
            $this->output ( - 1, '应用列表为空' );
        }
        $info = $this->checkPostParam ( $info );
        $effect_date = date('Y-m-d');
        $operator = $this->userInfo['user_name'];
        $failed_arr = array();
        $appMap = MobgiData_Service_BaseModel::getAppKeyMap();
        foreach($info['app_key'] as $key => $app_key){
            MobgiData_Service_BalanceModel::updateDivisionCustomConfig(
                array('status'=>Common_Service_Const::STATUS_NOT_ACTIVE),array('status'=>Common_Service_Const::STATUS_ACTIVE,'app_key'=>$app_key)
            );
            $result = MobgiData_Service_BalanceModel::addDivisionCustomConfig(
                $app_key,$info['ads_division_rate'],$info['own_division_rate'],
                $effect_date,Common_Service_Const::STATUS_ACTIVE,time(),$operator,$info['notice']
            );
            if (! $result) {
                $failed_arr[] = $appMap[$app_key];
            }
        }
        if(empty($failed_arr)){
            $this->output ( 0, '操作成功');
        }else{
            $this->output ( - 1, '应用 '.implode(',',$failed_arr).' 配置失败' );
        }
    }

    private function getUserList(){
        $userParam['is_check'] = Admin_Service_UserModel::ISCHECK_PASS;
        $userParam['user_type'] = array('IN', array(Admin_Service_UserModel::DEVERLOPER_USER, Admin_Service_UserModel::OPERATOR_USER));
        $userList = Admin_Service_UserModel::getsBy($userParam);
        return $userList;
    }
}