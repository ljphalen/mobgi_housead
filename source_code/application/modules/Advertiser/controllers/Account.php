<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-8-31 11:39:39
 * $Id: Account.php 62100 2016-8-31 11:39:39Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');


class AccountController extends Admin_BaseController {
	
	public $actions = array(
		'setCosumeLimitUrl' => '/Advertiser/Account/setDayconsumptionLimit',
        'listUrl' => '/Advertiser/Account/log',
	);
	
	
    
    public $perpage = 10;
	
    
    public function init(){
    	parent::init();
    	if ($this->userInfo['user_type'] == 3){
    		$this->showMsg(-1,'此用户类型不能操作');
    	}
    }
	/**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {
		$this->assign('loginUrl', $this->actions['loginUrl']);
		$this->assign('logoutUrl', $this->actions['logoutUrl']);
		$this->assign('indexUrl', $this->actions['indexUrl']);
	}
    
    public function logAction(){ 
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        $operate_type = $this->getInput('operate_type');
        $account_type = $this->getInput('account_type');
        
        if(empty($sdate) && empty($edate)){
            $sdate = date("Y-m-d");
            $edate = date("Y-m-d");
        }
        
        $page = intval($this->getInput('page'));
		$perpage = $this->perpage;
        $urlparam= '';
		
        $params  = array();
        if($sdate){
            $params['create_time'] = array('>=', strtotime($sdate));
            $urlparam.="sdate=".$sdate."&";
        }
        if($edate){
            $params['create_time'] = array('<=', strtotime($edate) + 86400);
            $urlparam.="edate=".$edate."&";
        }
        if($operate_type){
            $params['operate_type'] = $operate_type;
            $urlparam.="operate_type=".$operate_type."&";
        }
        if($account_type){
            $params['account_type'] = $account_type;
            $urlparam.="account_type=".$account_type."&";
        }
        #获取当前用户的id
        $advertiser_uid = $this->userInfo['user_id'];
        $params['uid'] = $advertiser_uid;
        
        #获取各个帐户内的余额和今日已消耗
        $account_detail_arr = Advertiser_Service_AccountDetailModel::getList(0, 10 , array('uid'=>$advertiser_uid));
        $account_detail_arr = common::resetKey($account_detail_arr, 'account_type');
        foreach($account_detail_arr as $key=>$account_item){
            $account_detail_arr[$key]['account_type_str'] = $this->getAccountTypeStr($account_item['account_type']);
        }
        
        #获取各个帐户内的今日消耗
        $day_consumption_arr = Advertiser_Service_AccountDayConsumeModel::getsBy(array('uid'=>$advertiser_uid, 'date'=>date('Ymd')));
        $day_consumption_arr = common::resetKey($day_consumption_arr, 'account_type');
        $accountTodayConsumeAmount = 0;
        if($day_consumption_arr){
            foreach($day_consumption_arr as $key=>$item){
                $accountTodayConsumeAmount +=$item['consumption'];
            }
        }
        
        #计算周期内总充值与总扣费
        $recharge_total =0;
        $deduction_total=0;
        list($totalnum, $totalloglist) = Advertiser_Service_AccountlogModel::getList(0, 999999, $params);
        foreach($totalloglist as $key=>$logitem){
            if($logitem['operate_type'] == 'recharge'){
                $recharge_total += $logitem['trade_balance'];
            }else if($logitem['operate_type'] == 'deduction'){
                $deduction_total += $logitem['trade_balance'];
            }
        }
        #获取列表及分页
		list($total, $loglist) = Advertiser_Service_AccountlogModel::getList($page, $perpage, $params, array('create_time'=>'desc'));
        foreach($loglist as $key=>$log){
            $loglist[$key]['account_type_str'] = $this->getAccountTypeStr($log['account_type']);
            $loglist[$key]['operate_type_str'] = $this->getOperateTypeStr($log['operate_type']);
            $loglist[$key]['create_time_str'] = date("Y-m-d H:i:s", $log['create_time']);
        }
        
	    $accountDayAmountLimitList =  Advertiser_Service_AccountConsumptionLimitModel::getConsumptionlimit($advertiser_uid);
	    
	    $this->assign('accountDayAmountLimitList', intval($accountDayAmountLimitList));
        
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('operate_type', $operate_type);
        $this->assign('account_type', $account_type);
        $this->assign('account_detail_arr', $account_detail_arr);
        $this->assign('day_consumption_arr', $day_consumption_arr);
        $this->assign('accountTodayConsumeAmount', floatval($accountTodayConsumeAmount));
        $this->assign('recharge_total', $recharge_total);
        $this->assign('deduction_total', $deduction_total);
		$this->assign('loglist', $loglist);
		$this->assign('pager', Common::getPages($total, $page, $perpage, $this->actions['listUrl'].'/?'.$urlparam));
		
		
		//广告总量
		$adParams['account_id'] = $this->userInfo['user_id'];
		$adParams['status'] = array('IN', array(1, 2, 3, 4, 5));
		$adTotal = Dedelivery_Service_AdConfListModel::getCountBy($adParams);
		
		$adParams['status'] = array('IN', array(1, 4));
		$checkPassTotal = Dedelivery_Service_AdConfListModel::getCountBy($adParams);
		
		$adParams['status'] = 3;
		$checkNoPassTotal = Dedelivery_Service_AdConfListModel::getCountBy($adParams);
		
		$adParams['status'] = 1;
		$checkingTotal = Dedelivery_Service_AdConfListModel::getCountBy($adParams);
		
		$this->assign('adTotal', $adTotal);
		$this->assign('checkPassTotal', $checkPassTotal);
		$this->assign('checkNoPassTotal', $checkNoPassTotal);
		$this->assign('checkingTotal', $checkingTotal);
    }
    
    #帐户类型
    private function getAccountTypeStr($account_type){
        $Advertiser_account_type_config =  Common::getConfig('advertiserConfig', 'Advertiser_account_type');
        return $Advertiser_account_type_config[$account_type];
    }
    
    #操作类型
    private function getOperateTypeStr($operate_type){
        $Advertiser_operate_type_config =  Common::getConfig('advertiserConfig', 'Advertiser_operate_type');
        return $Advertiser_operate_type_config[$operate_type];
    }
    
    /**
     * 设置日限额
     */
    public function setDayconsumptionLimitAction(){
        $uId = $this->userInfo['user_id'];
        $limit = $this->getInput('limit');
        if(!is_numeric($limit)){
            $this->output(1, '请输入正确的日限额');
        }
        
        if($limit<0){
            $this->output(1, '日限额不能设置为负数');
        }
        
        $params= array();
        $params['operator']=$uId;
        $params['day_consumption_limit'] = floatval($limit);
        if(Advertiser_Service_AccountConsumptionLimitModel::getBy(array('uid'=>$uId))){
            $ret = Advertiser_Service_AccountConsumptionLimitModel::update($params, $uId);
        }else{
            $params['uid'] = $uId;
            $ret = Advertiser_Service_AccountConsumptionLimitModel::add($params);
        }
        if(!$ret)$this->output(1, '操作失败');
        $this->output(0, '操作成功');
    }
	
}
