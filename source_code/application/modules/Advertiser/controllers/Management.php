<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-1 15:08:06
 * $Id: Management.php 62100 2016-9-1 15:08:06Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');


class ManagementController extends Advertiser_BaseController {
	
	public $actions = array(
        'operatelogUrl' => '/Advertiser/Management/operatelog',
	);
    
    public $perpage = 10;
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {
//		$this->assign('loginUrl', $this->actions['loginUrl']);
//		$this->assign('logoutUrl', $this->actions['logoutUrl']);
//		$this->assign('indexUrl', $this->actions['indexUrl']);
	}
    
    public function operatelogAction(){
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_Operatelog_View')){
            $this->showMsg(100001, 'permission denied!');
        }
        /*权限校验end*/
        
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        $operator = $this->getInput('operator');
        $module = $this->getInput('module');
        $sub_module = $this->getInput('sub_module');
        $page = intval($this->getInput('page'));
        
        if(empty($sdate) && empty($edate)){
            $sdate = date("Y-m-d");
            $edate = date("Y-m-d");
        }
        $Advertiser_operate_log_config =  Common::getConfig('advertiserConfig', 'Advertiser_operate_log');
        $Advertiser_operate_log_name_config =  Common::getConfig('advertiserConfig', 'Advertiser_operate_log_name');
		$perpage = $this->perpage;
        
        $urlparam= '';
        $params  = array();
        if($sdate && empty($edate)){
            $params['create_time'] = array('>=', strtotime($sdate));
            $urlparam.="sdate=".$sdate."&";
        }
        if(empty($sdate) &&$edate){
            $params['create_time'] = array('<=', strtotime($edate)+86400);
            $urlparam.="edate=".$edate."&";
        }
        if($sdate && $edate){
            $params['create_time'] = array(array('>=', strtotime($sdate)), array('<=', strtotime($edate) + 86400));
            $urlparam.="sdate=".$sdate."&";
            $urlparam.="edate=".$edate."&";
        }
        
        $advertiser_uid = $this->userInfo['advertiser_uid'];
        if($operator){
            if($operator == 'advertiser'){
                $params['uid'] = $advertiser_uid;
                $params['object'] = $advertiser_uid;
            }else if($operator == 'system_manager'){
                $params['uid'] = 1;
                $params['object'] = $advertiser_uid;
            }else{
                $this->showMsg(100001, 'param error! wrong operator!');
            }
            $urlparam.="operator=".$operator."&";
        }else{
            $params['object'] = $advertiser_uid;
            $urlparam.="operator=&";
        }
        if($module){
            $params['module'] = $module;
            $urlparam.="module=".$module."&";
        }
        if($sub_module){
            $params['sub_module'] = $sub_module;
            $urlparam.="sub_module=".$sub_module."&";
        }
        
        #获取列表及分页
		list($total, $loglist) = Advertiser_Service_OperatelogModel::getList($page, $perpage, $params, array('id'=>'desc'));
        $advertiser_namearr = array();
        $admin_namearr = array();
        foreach($loglist as $key=>$log){
            //系统管理员的操作使用系统管理员的用户名信息表，广告主自身的操作使用广告主用户信息表．
            if(isset($Advertiser_operate_log_config['system_manager'][$log['module']])){
                $uid_name='';
                if(isset($admin_namearr[$log['uid']])){
                    $uid_name=$admin_namearr[$log['uid']];
                }else{
                    $userinfo = Admin_Service_UserModel::getUser($log['uid']);
                    $admin_namearr[$log['uid']] = $userinfo['username'];
                    $uid_name=$userinfo['username'];
                }
                $loglist[$key]['create_time_str'] = date("Y-m-d H:i:s", $log['create_time']);
                $loglist[$key]['uid_name'] = $uid_name;
            }else{
                $uid_name='';
                if(isset($advertiser_namearr[$log['uid']])){
                    $uid_name=$advertiser_namearr[$log['uid']];
                }else{
                    $userinfo = Advertiser_Service_UserModel::getUser($log['uid']);
                    $advertiser_namearr[$log['uid']] = $userinfo['advertiser_name'];
                    $uid_name=$userinfo['advertiser_name'];
                }
                $loglist[$key]['create_time_str'] = date("Y-m-d H:i:s", $log['create_time']);
                $loglist[$key]['uid_name'] = $uid_name;
            }
        }
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('operator', $operator);
        $this->assign('module', $module);
        $this->assign('sub_module', $sub_module);
        $this->assign('Advertiser_operate_log_config', $Advertiser_operate_log_config);
        $this->assign('Advertiser_operate_log_name_config', $Advertiser_operate_log_name_config);
        $this->assign('loglist', $loglist);
		$this->assign('pager', Common::getPages($total, $page, $perpage, $this->actions['operatelogUrl'].'/?'.$urlparam));
    }
    
}

