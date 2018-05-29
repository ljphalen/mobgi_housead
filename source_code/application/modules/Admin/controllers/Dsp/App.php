<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-5-25 20:06:58
 * $Id: App.php 62100 2017-5-25 20:06:58Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Dsp_AppController extends Admin_BaseController {
	
	public $actions = array('positionListUrl'=>'/Admin/Dsp_App/index',
	                                            'appListUrl'=>'/Admin/Dsp_App/appList',          
                            					'updAppkeyConfigUrl'=>'/Admin/Dsp_App/updAppkeyConfig',
												'updatePolicyConfigUrl'=>'/Admin/Dsp_App/updatePolicyConfig',
	);
	
    public  $status = array(2=>'待审核',3=>'审核未通过', -1=>'审核通过');
    public 	$perpage = 10;
    public $posState = array(1=>'开启',0=>'关闭');
 
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {
	    $params = array();
	    $page = intval($this->getInput('page'));
	    if ($page < 1) $page = 1;
        
        $search= $this->getInput(array('app_name','app_key', 'pos_key_type', 'dever_pos_key'));
        if ($search['app_name']) {
            $params['app_name'] = trim($search['app_name']);
        }
        if ($search['app_key']) {
            $params['app_key'] = $search['app_key'];
        }
        if ($search['pos_key_type']) {
            $params['pos_key_type'] = $search['pos_key_type'];
        }
        if ($search['dever_pos_key']) {
            $params['dever_pos_key'] = $search['dever_pos_key'];
        }

//        dever_pos_key
//        var_dump($search);
        list($total, $positoinList) = MobgiApi_Service_AdAppModel::getAdPos($params, ($page-1)*$this->perpage, $this->perpage);
        
        $url = $this->actions['positionListUrl'].'/?' . http_build_query($search) . '&';
	    $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
	    $this->assign('adType', Common_Service_Const::$mAdPosTypeName);
        $this->assign('posState', $this->posState);
	    $this->assign('search', $search);
	    $this->assign('total', $total);
	    $this->assign('positoinList', $positoinList);
	}
	
	
	
	public function appListAction(){
        $params = array();
        $page = intval($this->getInput('page'));
        if ($page < 1) $page = 1;
         
        $search= $this->getInput(array('app_name','app_key','appkey_config_id','policy_config_id'));
        if ($search['app_name']) {
            $params['app_name'] = trim($search['app_name']);
        }
        if ($search['app_key']) {
            $params['app_key'] = $search['app_key'];
        }

        if ($search['appkey_config_id']) {
          $params['appkey_config_id'] = $search['appkey_config_id'];
        }
        list($total, $appList) = MobgiApi_Service_AdAppModel::getAdApp($params, ($page-1)*$this->perpage, $this->perpage);
        
        $url = $this->actions['appListUrl'].'/?' . http_build_query($search) . '&';
	    $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
	    foreach ($appList as $key=>$val){
            $appConfig = Advertiser_Service_AppkeyConfigModel::getBy(array('app_key'=>$val['app_key']));
            if($appConfig){
                $appList[$key]['appkey_config_id'] = $appConfig['appkey_config_id'];
            }else{
                $appList[$key]['appkey_config_id'] = '';
            }
	    }
        //获取appkey对应的下发配置
       list(,$appkeyConfigs)= Advertiser_Service_AdAppkeyConfigModel::getAllConfig();
		$appkeyConfigs = Common::resetKey($appkeyConfigs, 'id');
		
		list(,$policyConfig) = Advertiser_Service_PolicyConfigModel::getAll();
		$policyConfig = Common::resetKey($policyConfig, 'id');

	    $this->assign('search', $search);
	    $this->assign('total', $total);
	    $this->assign('appList', $appList);
        $this->assign('appkey_configs', $appkeyConfigs);
	}

    public function updAppkeyConfigAction(){
        $appKey = $this->getInput('appKey');
        $configId = intval($this->getInput('configId'));
        if(empty($appKey) || empty($configId)){
            $this->output('1', '参数错误.');
        }
        $ret = Advertiser_Service_AdAppkeyConfigModel::getBy(array('id'=>$configId));
        if(!$ret){
            $this->output(1, '配置不存在');
        }
        
        $config = Advertiser_Service_AppkeyConfigModel::getBy(array('app_key'=>$appKey));
        if(empty($config)){
            $adAppInfo = MobgiApi_Service_AdAppModel::getBy(array('app_key'=>$appKey));
            $data = array();
            $data['appkey_config_id'] = $configId;
            $data['app_key'] = $appKey;
            $data['app_name'] = $adAppInfo['app_name'];
            $data['del'] = Common_Service_Const::NOT_DELETE_FLAG;
            $result = Advertiser_Service_AppkeyConfigModel::add($data);
        }else{
            $data = array();
            $data['appkey_config_id'] = $configId;
            $params = array();
            $params['app_key'] = $appKey;
            $result = Advertiser_Service_AppkeyConfigModel::updateBy($data, $params);
        }
        if(!$result){
            $this->output(1, '操作失败');
        }
        $this->output(0, '操作成功');
    }
    
    public function updatePolicyConfigAction(){
    	$appKey = $this->getInput('appKey');
    	$configId = intval($this->getInput('configId'));
    	if(empty($appKey) || empty($configId)){
    		$this->output('1', '参数错误.');
    	}
    	$ret = Advertiser_Service_PolicyConfigModel::getBy(array('id'=>$configId));
    	if(!$ret){
    		$this->output(1, '配置不存在');
    	}
        
        $config = Advertiser_Service_AppkeyConfigModel::getBy(array('app_key'=>$appKey));
        if(empty($config)){
            $adAppInfo = MobgiApi_Service_AdAppModel::getBy(array('appkey'=>$appKey));
            $data = array();
            $data['policy_config_id'] = $configId;
            $data['app_key'] = $appKey;
            $data['app_name'] = $adAppInfo['app_name'];
            $data['del'] = Common_Service_Const::NOT_DELETE_FLAG;
            $result = Advertiser_Service_AppkeyConfigModel::add($data);
        }else{
            $data = array();
            $data['policy_config_id'] = $configId;
            $params = array();
            $params['app_key'] = $appKey;
            $result = Advertiser_Service_AppkeyConfigModel::updateBy($data, $params);
        }
        
    	if(!$result){
    		$this->output(1, '操作失败');
    	}
    	$this->output(0, '操作成功');
    }
	
}

