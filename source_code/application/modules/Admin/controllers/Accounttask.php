<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-12 16:59:58
 * $Id: Accounttask.php 62100 2016-9-12 16:59:58Z hunter.fang $
 */
if (!defined('BASE_PATH')) exit('Access Denied!');

class AccounttaskController extends Admin_BaseController {
    public $actions = array(
        'manageUrl' => '/Admin/Accounttask/manage',
        'auditUrl' => '/Admin/Accounttask/audit',
        'detailUrl' => '/Admin/Accounttask/detail',
        'singleaddPostUrl' => '/Admin/Accounttask/singleaddPost',
        'batchaddPostUrl' => '/Admin/Accounttask/batchaddPost',
        'uploadUrl' => '/Admin/Accounttask/uploadbatchcsv',
	    'uploadPostUrl' => '/Admin/Accounttask/uploadbatchcsvPost',
        'auditPostUrl' => '/Admin/Accounttask/auditPost',
        'exportdetailUrl' => '/Admin/Accounttask/exportdetail',
	);
	
	public $perpage = 10;
    
    public function manageAction() {
        $page = intval($this->getInput('page'));
        $applyby = $this->getInput('applyby');
        $taskid = $this->getInput('taskid');
        $taskname = $this->getInput('taskname');
        $opertype = $this->getInput('opertype');
        $auditstate = $this->getInput('auditstate');
        $taskstate = $this->getInput('taskstate');
        $apply_sdate = $this->getInput('apply_sdate');
        $apply_edate = $this->getInput('apply_edate');
        $expire_sdate = $this->getInput('expire_sdate');
        $expire_edate = $this->getInput('expire_edate');
        $perpage = $this->perpage;
        
        $params = array();
        $urlparam = '';
        $params['del'] = 0;
        if($applyby){
            $params['applyby'] = array('like',$applyby);
            $urlparam.="applyby=".$content."&";
        }
        if($taskid){
            $params['taskid'] = array('like',$taskid);
            $urlparam.="taskid=".$taskid."&";
        }
        if($taskname){
            $params['taskname'] = array('like',$taskname);
            $urlparam.="taskname=".$taskname."&";
        }
        if($opertype){
            $params['opertype'] = $opertype;
            $urlparam.="opertype=".$opertype."&";
        }
        if($auditstate){
            $params['auditstate'] = $auditstate;
            $urlparam.="auditstate=".$auditstate."&";
        }
        if($taskstate){
            $params['taskstate'] = $taskstate;
            $urlparam.="taskstate=".$taskstate."&";
        }
        if($apply_sdate && empty($apply_edate)){
            $params['apply_time'] = array('>=', strtotime($apply_sdate));
            $urlparam.="apply_sdate=".$apply_sdate."&";
        }
        if(empty($apply_sdate) &&$apply_edate){
            $params['apply_time'] = array('<=', strtotime($apply_edate)+86400);
            $urlparam.="apply_edate=".$apply_edate."&";
        }
        if($apply_sdate && $apply_edate){
            $params['apply_time'] = array(array('>=', strtotime($apply_sdate)), array('<=', strtotime($apply_edate) + 86400));
            $urlparam.="apply_sdate=".$apply_sdate."&";
            $urlparam.="apply_edate=".$apply_edate."&";
        }
        if($expire_sdate && empty($expire_edate)){
            $params['expire_time'] = array('>=', strtotime($expire_sdate));
            $urlparam.="expire_sdate=".$expire_sdate."&";
        }
        if(empty($expire_sdate) &&$expire_edate){
            $params['expire_time'] = array('<=', strtotime($expire_edate)+86400);
            $urlparam.="expire_edate=".$expire_edate."&";
        }
        if($expire_sdate && $expire_edate){
            $params['expire_time'] = array(array('>=', strtotime($expire_sdate)), array('<=', strtotime($expire_edate) + 86400));
            $urlparam.="expire_sdate=".$expire_sdate."&";
            $urlparam.="expire_edate=".$expire_edate."&";
        }
        
        list($total, $list) = Advertiser_Service_AccountTaskModel::getList($page, $perpage, $params, array('apply_time'=>'desc'));
        
        $Admin_account_opertype_config =  Common::getConfig('adminConfig', 'Admin_account_opertype');
        $Admin_account_auditstate_config =  Common::getConfig('adminConfig', 'Admin_account_auditstate');
        $Admin_account_taskstate_config =  Common::getConfig('adminConfig', 'Admin_account_taskstate');
        $Admin_virtualaccount_type_config =  Common::getConfig('adminConfig', 'Admin_virtualaccount_type');
        
        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->assign('applyby', $applyby);
        $this->assign('taskid', $taskid);
        $this->assign('taskname', $taskname);
        $this->assign('opertype', $opertype);
        $this->assign('auditstate', $auditstate);
        $this->assign('taskstate', $taskstate);
        $this->assign('apply_sdate', $apply_sdate);
        $this->assign('apply_edate', $apply_edate);
        $this->assign('expire_sdate', $expire_sdate);
        $this->assign('expire_edate', $expire_edate);
        $this->assign('Admin_account_opertype_config', $Admin_account_opertype_config);
        $this->assign('Admin_account_auditstate_config', $Admin_account_auditstate_config);
        $this->assign('Admin_account_taskstate_config', $Admin_account_taskstate_config);
        $this->assign('Admin_virtualaccount_type_config', $Admin_virtualaccount_type_config);
        $this->assign('pager', Common::getPages($total, $page, $perpage, $this->actions['manageUrl'].'/?'.$urlparam));
        
    }
    
    public function auditAction() {
        $page = intval($this->getInput('page'));
        $applyby = $this->getInput('applyby');
        $taskid = $this->getInput('taskid');
        $taskname = $this->getInput('taskname');
        $opertype = $this->getInput('opertype');
        $auditstate = $this->getInput('auditstate');
        $taskstate = $this->getInput('taskstate');
        $apply_sdate = $this->getInput('apply_sdate');
        $apply_edate = $this->getInput('apply_edate');
        $expire_sdate = $this->getInput('expire_sdate');
        $expire_edate = $this->getInput('expire_edate');
        $perpage = $this->perpage;
        
        $params = array();
        $urlparam = '';
        $params['del'] = 0;
        if($applyby){
            $params['applyby'] = array('like',$applyby);
            $urlparam.="applyby=".$content."&";
        }
        if($taskid){
            $params['taskid'] = array('like',$taskid);
            $urlparam.="taskid=".$taskid."&";
        }
        if($taskname){
            $params['taskname'] = array('like',$taskname);
            $urlparam.="taskname=".$taskname."&";
        }
        if($opertype){
            $params['opertype'] = $opertype;
            $urlparam.="opertype=".$opertype."&";
        }
        if($auditstate){
            $params['auditstate'] = $auditstate;
            $urlparam.="auditstate=".$auditstate."&";
        }
        if($taskstate){
            $params['taskstate'] = $taskstate;
            $urlparam.="taskstate=".$taskstate."&";
        }
        if($apply_sdate && empty($apply_edate)){
            $params['apply_time'] = array('>=', strtotime($apply_sdate));
            $urlparam.="apply_sdate=".$apply_sdate."&";
        }
        if(empty($apply_sdate) &&$apply_edate){
            $params['apply_time'] = array('<=', strtotime($apply_edate)+86400);
            $urlparam.="apply_edate=".$apply_edate."&";
        }
        if($apply_sdate && $apply_edate){
            $params['apply_time'] = array(array('>=', strtotime($apply_sdate)), array('<=', strtotime($apply_edate) + 86400));
            $urlparam.="apply_sdate=".$apply_sdate."&";
            $urlparam.="apply_edate=".$apply_edate."&";
        }
        if($expire_sdate && empty($expire_edate)){
            $params['expire_time'] = array('>=', strtotime($expire_sdate));
            $urlparam.="expire_sdate=".$expire_sdate."&";
        }
        if(empty($expire_sdate) &&$expire_edate){
            $params['expire_time'] = array('<=', strtotime($expire_edate)+86400);
            $urlparam.="expire_edate=".$expire_edate."&";
        }
        if($expire_sdate && $expire_edate){
            $params['expire_time'] = array(array('>=', strtotime($expire_sdate)), array('<=', strtotime($expire_edate) + 86400));
            $urlparam.="expire_sdate=".$expire_sdate."&";
            $urlparam.="expire_edate=".$expire_edate."&";
        }
        
        list($total, $list) = Advertiser_Service_AccountTaskModel::getList($page, $perpage, $params, array('apply_time'=>'desc'));
        
        $Admin_account_opertype_config =  Common::getConfig('adminConfig', 'Admin_account_opertype');
        $Admin_account_auditstate_config =  Common::getConfig('adminConfig', 'Admin_account_auditstate');
        $Admin_account_taskstate_config =  Common::getConfig('adminConfig', 'Admin_account_taskstate');
        $Admin_virtualaccount_type_config =  Common::getConfig('adminConfig', 'Admin_virtualaccount_type');
        
        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->assign('applyby', $applyby);
        $this->assign('taskid', $taskid);
        $this->assign('taskname', $taskname);
        $this->assign('opertype', $opertype);
        $this->assign('auditstate', $auditstate);
        $this->assign('taskstate', $taskstate);
        $this->assign('apply_sdate', $apply_sdate);
        $this->assign('apply_edate', $apply_edate);
        $this->assign('expire_sdate', $expire_sdate);
        $this->assign('expire_edate', $expire_edate);
        $this->assign('Admin_account_opertype_config', $Admin_account_opertype_config);
        $this->assign('Admin_account_auditstate_config', $Admin_account_auditstate_config);
        $this->assign('Admin_account_taskstate_config', $Admin_account_taskstate_config);
        $this->assign('Admin_virtualaccount_type_config', $Admin_virtualaccount_type_config);
        $this->assign('pager', Common::getPages($total, $page, $perpage, $this->actions['manageUrl'].'/?'.$urlparam));
        
    }
    
    public function auditPostAction(){
        $taskid = intval($this->getInput('taskid'));
        $auditstate = $this->getInput('auditstate');
        
        if(empty($taskid)){
            $this->output('1', '参数错误1.');
        }
        
        $Admin_account_auditstate_config =  Common::getConfig('adminConfig', 'Admin_account_auditstate');
        if(!isset($Admin_account_auditstate_config[$auditstate])){
            $this->output('2', '参数错误2.');
        }
        
        $taskinfo = Advertiser_Service_AccountTaskModel::getByTaskid($taskid);
        if(!$taskinfo){
            $this->output('3', '任务不存在.');
        }
        
        if($taskinfo['auditstate']!='not_check'){
            $this->output('4', '操作失败，不是未审核任务.');
        }
        
        $Admin_account_opertype_config =  Common::getConfig('adminConfig', 'Admin_account_opertype');
        if($auditstate == 'checked_success'){
            #到期时间已经过去了的任务不能审核通过
            if($taskinfo['opertype']=='recharge' && $taskinfo['expire_time']< time()){
                $this->output('1', '充值任务已经过期.');
            }
            
            //(1)把任务和任务详情状态标志成审核通过
            $adminInfo = $this->userInfo;
            if (!$adminInfo['uid']) $this->output(-1, '登录超时,请重新登录后操作');
            $info = array();
            $info['taskstate'] ='sending';
            $info['auditstate'] ='checked_success';
            $info['audit_time'] =time();
            $info['auditby'] =$adminInfo['username'];
            $info['auditmsg'] ='审核通过';
            $result = Advertiser_Service_AccountTaskModel::updateTask($info, $taskid);
            if (!$result) $this->output(-1, '操作任务失败');
            
            $detailinfo = array();
            $detailinfo['taskdetailstate'] ='sending';
            $detailinfo['auditstate'] ='checked_success';
            $detailinfo['audit_time'] =time();
            $detailinfo['auditby'] =$adminInfo['username'];
            $detailinfo['auditmsg'] ='审核通过';
            $params = array();
            $params['taskid'] = $taskid;
            $resultdetail = Advertiser_Service_AccountTaskDetailModel::updateTaskdetailBy($detailinfo, $params);
            if (!$resultdetail) $this->output(-1, '操作任务详情失败');
            //(2)处理子任务详情
            list($total, $list) = Advertiser_Service_AccountTaskDetailModel::getList(0, 999999, $params);
            $success = 0;
            foreach($list as $item){
                if($item['taskdetailstate']=='sended_success'){
                    continue;
                }
                $successFlag = false;
                $advertiseruserinfo = Admin_Service_UserModel::getBy(array('email'=>$item['email']));
                $advertiser_uid = $advertiseruserinfo['user_id'];
                $virtualaccountinfo = Advertiser_Service_AccountDetailModel::getBy(array('uid'=>$advertiser_uid, 'account_type'=>$item['virtual_account_type']));
                
                if($item['opertype']=='recharge' ){
                    if(empty($virtualaccountinfo)){
                        $addparam = array();
                        $addparam['uid'] = $advertiser_uid;
                        $addparam['account_type'] = $item['virtual_account_type'];
                        $addparam['balance'] = $item['money'];
                        $resultdetail = Advertiser_Service_AccountDetailModel::addAccountdetail($addparam);
                        $resultvirtualdetail = $this->saveAccountVirtualDetail($advertiser_uid, $item['virtual_account_type'], $item['money'], 'normal', $item['detailid'], $item['expire_time'], $adminInfo['username']);
                        if($resultdetail&&$resultvirtualdetail){
                            $success ++;
                            $successFlag = true;
                            $detailstate = Advertiser_Service_AccountTaskDetailModel::updateTaskdetail(array('taskdetailstate'=>'sended_success'), $item['detailid']);
                            $this->saveAccountlog($advertiser_uid, $item['virtual_account_type'], $item['opertype'], $item['money'], $info['auditmsg']);
							Advertiser_Service_AccountDetailModel::delTotalbalanceCache($advertiser_uid);
                        }else{
                            $detailstate = Advertiser_Service_AccountTaskDetailModel::updateTaskdetail(array('taskdetailstate'=>'sended_failed'), $item['detailid']);
                        }
                    }else{
                        $balance = $virtualaccountinfo['balance'] + $item['money'];
                        $resultdetail = Advertiser_Service_AccountTaskDetailModel::updateAccountdetailBy(array('balance'=>$balance), array('uid'=>$advertiser_uid, 'account_type'=>$item['virtual_account_type']));
                        $resultvirtualdetail = $this->saveAccountVirtualDetail($advertiser_uid, $item['virtual_account_type'], $item['money'], 'normal', $item['detailid'], $item['expire_time'], $adminInfo['username']);
                        if($resultdetail&&$resultvirtualdetail){
                            $success ++;
                            $successFlag = true;
							Advertiser_Service_AccountTaskDetailModel::updateTaskdetail(array('taskdetailstate'=>'sended_success'), $item['detailid']);
                            $this->saveAccountlog($advertiser_uid, $item['virtual_account_type'], $item['opertype'], $item['money'], $info['auditmsg']);
							Advertiser_Service_AccountDetailModel::delTotalbalanceCache($advertiser_uid);
                        }else{
							Advertiser_Service_AccountTaskDetailModel::updateTaskdetail(array('taskdetailstate'=>'sended_failed'), $item['detailid']);
                        }
                    }
                }else if($item['opertype']=='recovery'){
                    if(empty($virtualaccountinfo)){
                        $addparam = array();
                        $addparam['uid'] = $advertiser_uid;
                        $addparam['account_type'] = $item['virtual_account_type'];
                        $addparam['balance'] = 0;
                        $resultdetail = Advertiser_Service_AccountDetailModel::addAccountdetail($addparam);
						Advertiser_Service_AccountTaskDetailModel::updateTaskdetail(array('taskdetailstate'=>'sended_failed'), $item['detailid']);
                        $this->saveAccountlog($advertiser_uid, $item['virtual_account_type'], $item['opertype'], 0, $info['auditmsg']);
						Advertiser_Service_AccountDetailModel::delTotalbalanceCache($advertiser_uid);
                    }else{
                        $balance = $virtualaccountinfo['balance'] - $item['money'];
                        if($balance<0)$balance=0;
                        $resultdetail = Advertiser_Service_AccountDetailModel::updateAccountdetailBy(array('balance'=>$balance), array('uid'=>$advertiser_uid, 'account_type'=>$item['virtual_account_type']));
                        if($resultdetail){
                            $success ++;
                            $successFlag = true;
							Advertiser_Service_AccountTaskDetailModel::updateTaskdetail(array('taskdetailstate'=>'sended_success'), $item['detailid']);
                            $this->saveAccountlog($advertiser_uid, $item['virtual_account_type'], $item['opertype'], $item['money'], $info['auditmsg']);
							Advertiser_Service_AccountDetailModel::delTotalbalanceCache($advertiser_uid);
                        }else{
							Advertiser_Service_AccountTaskDetailModel::updateTaskdetail(array('taskdetailstate'=>'sended_failed'), $item['detailid']);
                        }
                    }
                }else{
                    $this->output('4', '操作失败，子任务操作类型错误.');
                }
                
                /*管理端操作日志start*/
                $logdata=array();
                $logdata['object']=$advertiseruserinfo['advertiser_uid'];
                $logdata['module'] = 'admin_account';
                $logdata['sub_module'] = 'audit_accounttask';
                $logdata['content'].='任务名：'.$taskinfo['taskname'].','.$Admin_account_opertype_config[$item['opertype']].',任务详情id：'.$item['detailid'].',交易总额：'.$item['money'].', 审核成功，';
                if($successFlag){
                    $logdata['content'].='发放成功';
                }else{
                    $logdata['content'].='发放失败';
                };
                $this->addAdminOperatelog($logdata);
                /*管理端操作日志end*/
                
            }
            
            //(3)全部操作成功或者部分操作成功，更新任务状态
            $processresult = array();
            if($total == 0){
                $processresult['taskstate'] ='sended_failed';
            }else{
                if($success == 0){
                    $processresult['taskstate'] ='sended_failed';
                }else if($success == $total){
                    $processresult['taskstate'] ='sended_success';
                }else{
                    $processresult['taskstate'] ='sended_partial_success';
                }
            }
            $result = Advertiser_Service_AccountTaskModel::updateTask($processresult, $taskid);
            
            $this->output(0, '操作成功');
            
        } else if ($auditstate == 'checked_failed'){
            $adminInfo = $this->userInfo;
            if (!$adminInfo['uid']) $this->output(-1, '登录超时,请重新登录后操作');
            $info = array();
            $info['taskstate'] ='checked_failed';
            $info['auditstate'] ='checked_failed';
            $info['audit_time'] =time();
            $info['auditby'] =$adminInfo['username'];
            $info['auditmsg'] ='审核不通过';
            $result = Advertiser_Service_AccountTaskModel::updateTask($info, $taskid);
            if (!$result) $this->output(-1, '操作任务失败');
            
            $detailinfo = array();
            $detailinfo['taskdetailstate'] ='checked_failed';
            $detailinfo['auditstate'] ='checked_failed';
            $detailinfo['audit_time'] =time();
            $detailinfo['auditby'] =$adminInfo['username'];
            $detailinfo['auditmsg'] ='审核不通过';
            $params = array();
            $params['taskid'] = $taskid;
            $resultdetail = Advertiser_Service_AccountTaskDetailModel::updateTaskdetailBy($detailinfo, $params);
            if (!$resultdetail) $this->output(-1, '操作任务详情失败');
            
            /*管理端操作日志start*/
            $applyInfo = Admin_Service_UserModel::getUserByName($taskinfo['applyby']);
            $logdata=array();
            $logdata['object']=$applyInfo['uid'];
            $logdata['module'] = 'admin_account';
            $logdata['sub_module'] = 'audit_accounttask';
            $logdata['content'].='任务名：'.$taskinfo['taskname'].','.'审核不通过';
            $this->addAdminOperatelog($logdata);
            /*管理端操作日志end*/
            
            $this->output(0, '操作成功');
        } else {
            $this->output('4', '参数错误.');
        }
    }
    
    public function detailAction(){
        $taskid = $this->getInput('taskid');
        $applyby = $this->getInput('applyby');
        $email = $this->getInput('email');
        $virtual_account_type = $this->getInput('virtual_account_type');
        $opertype = $this->getInput('opertype');
        $auditstate = $this->getInput('auditstate');
        $taskdetailstate = $this->getInput('taskdetailstate');
        $apply_sdate = $this->getInput('apply_sdate');
        $apply_edate = $this->getInput('apply_edate');
        $expire_sdate = $this->getInput('expire_sdate');
        $expire_edate = $this->getInput('expire_edate');
        $page = intval($this->getInput('page'));
        $perpage = $this->perpage;
        
        $taskinfo = Advertiser_Service_AccountTaskModel::getByTaskid($taskid);
        if(!$taskinfo){
            $this->output('1', '任务不存在.');
        }
        
        $params = array();
        $urlparam='';
        if($taskid){
            $params['taskid'] = $taskid;
            $urlparam.="taskid=".$taskid."&";
        }
        
        if($applyby){
            $params['applyby'] = array('like',$applyby);
            $urlparam.="applyby=".$content."&";
        }
        if($email){
            $params['email'] = array('like',$email);
            $urlparam.="email=".$email."&";
        }
        if($virtual_account_type){
            $params['virtual_account_type'] = $virtual_account_type;
            $urlparam.="virtual_account_type=".$virtual_account_type."&";
        }
        if($opertype){
            $params['opertype'] = $opertype;
            $urlparam.="opertype=".$opertype."&";
        }
        if($auditstate){
            $params['auditstate'] = $auditstate;
            $urlparam.="auditstate=".$auditstate."&";
        }
        if($taskdetailstate){
            $params['taskdetailstate'] = $taskdetailstate;
            $urlparam.="taskdetailstate=".$taskdetailstate."&";
        }
        if($apply_sdate && empty($apply_edate)){
            $params['apply_time'] = array('>=', strtotime($apply_sdate));
            $urlparam.="apply_sdate=".$apply_sdate."&";
        }
        if(empty($apply_sdate) &&$apply_edate){
            $params['apply_time'] = array('<=', strtotime($apply_edate)+86400);
            $urlparam.="apply_edate=".$apply_edate."&";
        }
        if($apply_sdate && $apply_edate){
            $params['apply_time'] = array(array('>=', strtotime($apply_sdate)), array('<=', strtotime($apply_edate) + 86400));
            $urlparam.="apply_sdate=".$apply_sdate."&";
            $urlparam.="apply_edate=".$apply_edate."&";
        }
        if($expire_sdate && empty($expire_edate)){
            $params['expire_time'] = array('>=', strtotime($expire_sdate));
            $urlparam.="expire_sdate=".$expire_sdate."&";
        }
        if(empty($expire_sdate) &&$expire_edate){
            $params['expire_time'] = array('<=', strtotime($expire_edate)+86400);
            $urlparam.="expire_edate=".$expire_edate."&";
        }
        if($expire_sdate && $expire_edate){
            $params['expire_time'] = array(array('>=', strtotime($expire_sdate)), array('<=', strtotime($expire_edate) + 86400));
            $urlparam.="expire_sdate=".$expire_sdate."&";
            $urlparam.="expire_edate=".$expire_edate."&";
        }
        
        list($total, $list) = Advertiser_Service_AccountTaskDetailModel::getList($page, $perpage, $params);
        $Admin_account_opertype_config =  Common::getConfig('adminConfig', 'Admin_account_opertype');
        $Admin_account_auditstate_config =  Common::getConfig('adminConfig', 'Admin_account_auditstate');
        $Admin_virtualaccount_type_config =  Common::getConfig('adminConfig', 'Admin_virtualaccount_type');
        $Admin_account_taskdetailstate_config =  Common::getConfig('adminConfig', 'Admin_account_taskdetailstate');
        
//        if(!$list){
//            $this->output('1', '任务详情不存在.');
//        }
        
        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->assign('applyby', $applyby);
        $this->assign('taskid', $taskid);
        $this->assign('email', $email);
        $this->assign('virtual_account_type', $virtual_account_type);
        $this->assign('opertype', $opertype);
        $this->assign('auditstate', $auditstate);
        $this->assign('taskdetailstate', $taskdetailstate);
        $this->assign('apply_sdate', $apply_sdate);
        $this->assign('apply_edate', $apply_edate);
        $this->assign('expire_sdate', $expire_sdate);
        $this->assign('expire_edate', $expire_edate);
        $this->assign('Admin_account_opertype_config', $Admin_account_opertype_config);
        $this->assign('Admin_account_auditstate_config', $Admin_account_auditstate_config);
        $this->assign('Admin_virtualaccount_type_config', $Admin_virtualaccount_type_config);
        $this->assign('Admin_account_taskdetailstate_config', $Admin_account_taskdetailstate_config);
        $this->assign('pager', Common::getPages($total, $page, $perpage, $this->actions['detailUrl'].'/?'.$urlparam));
        
    }
    
    public function exportdetailAction(){
        $taskid = $this->getInput('taskid');
        $applyby = $this->getInput('applyby');
        $email = $this->getInput('email');
        $virtual_account_type = $this->getInput('virtual_account_type');
        $opertype = $this->getInput('opertype');
        $auditstate = $this->getInput('auditstate');
        $taskdetailstate = $this->getInput('taskdetailstate');
        $apply_sdate = $this->getInput('apply_sdate');
        $apply_edate = $this->getInput('apply_edate');
        $expire_sdate = $this->getInput('expire_sdate');
        $expire_edate = $this->getInput('expire_edate');
        $page = intval($this->getInput('page'));
        $perpage = $this->perpage;
        
        $taskinfo = Advertiser_Service_AccountTaskModel::getByTaskid($taskid);
        if(!$taskinfo){
            $this->output('1', '任务不存在.');
        }
        
        $params = array();
        $urlparam='';
        if($taskid){
            $params['taskid'] = $taskid;
            $urlparam.="taskid=".$taskid."&";
        }
        
        if($applyby){
            $params['applyby'] = array('like',$applyby);
            $urlparam.="applyby=".$content."&";
        }
        if($email){
            $params['email'] = array('like',$email);
            $urlparam.="email=".$email."&";
        }
        if($virtual_account_type){
            $params['virtual_account_type'] = $virtual_account_type;
            $urlparam.="virtual_account_type=".$virtual_account_type."&";
        }
        if($opertype){
            $params['opertype'] = $opertype;
            $urlparam.="opertype=".$opertype."&";
        }
        if($auditstate){
            $params['auditstate'] = $auditstate;
            $urlparam.="auditstate=".$auditstate."&";
        }
        if($taskdetailstate){
            $params['taskdetailstate'] = $taskdetailstate;
            $urlparam.="taskdetailstate=".$taskdetailstate."&";
        }
        if($apply_sdate && empty($apply_edate)){
            $params['apply_time'] = array('>=', strtotime($apply_sdate));
            $urlparam.="apply_sdate=".$apply_sdate."&";
        }
        if(empty($apply_sdate) &&$apply_edate){
            $params['apply_time'] = array('<=', strtotime($apply_edate)+86400);
            $urlparam.="apply_edate=".$apply_edate."&";
        }
        if($apply_sdate && $apply_edate){
            $params['apply_time'] = array(array('>=', strtotime($apply_sdate)), array('<=', strtotime($apply_edate) + 86400));
            $urlparam.="apply_sdate=".$apply_sdate."&";
            $urlparam.="apply_edate=".$apply_edate."&";
        }
        if($expire_sdate && empty($expire_edate)){
            $params['expire_time'] = array('>=', strtotime($expire_sdate));
            $urlparam.="expire_sdate=".$expire_sdate."&";
        }
        if(empty($expire_sdate) &&$expire_edate){
            $params['expire_time'] = array('<=', strtotime($expire_edate)+86400);
            $urlparam.="expire_edate=".$expire_edate."&";
        }
        if($expire_sdate && $expire_edate){
            $params['expire_time'] = array(array('>=', strtotime($expire_sdate)), array('<=', strtotime($expire_edate) + 86400));
            $urlparam.="expire_sdate=".$expire_sdate."&";
            $urlparam.="expire_edate=".$expire_edate."&";
        }
        
        list($total, $list) = Advertiser_Service_AccountTaskDetailModel::getList($page, $perpage, $params);
        $Admin_account_opertype_config =  Common::getConfig('adminConfig', 'Admin_account_opertype');
        $Admin_account_auditstate_config =  Common::getConfig('adminConfig', 'Admin_account_auditstate');
        $Admin_virtualaccount_type_config =  Common::getConfig('adminConfig', 'Admin_virtualaccount_type');
        $Admin_account_taskdetailstate_config =  Common::getConfig('adminConfig', 'Admin_account_taskdetailstate');
        
        $str =iconv ( 'utf-8', 'gbk', "帐号ID,操作类型,金额,帐户类型,审核状态,详情状态,到期时间,申请人,任务申请时间\n");
        foreach($list as $item){
            $str .= iconv ( 'utf-8', 'gbk', $item['email'].','.$Admin_account_opertype_config[$item['opertype']].",".$item['money'].','.$Admin_virtualaccount_type_config[$item['virtual_account_type']].
                    ",".$Admin_account_auditstate_config[$item['auditstate']].','.$Admin_account_taskdetailstate_config[$item['taskdetailstate']].
                    ','.date('Y-m-d H:i:s', $item['expire_time']).','.$item['applyby'].','.date('Y-m-d H:i:s', $item['apply_time'])."\n");
        }
        
        $filename ='taskdetail_'.$taskid.'.csv'; //设置文件名
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;die;
    }
    
    public function singleaddPostAction(){
        $params= $this->getInput(array('single_taskname', 'single_opertype', 'single_email',  'single_virtualaccount',  'single_money',  'single_applymsg', 'single_expire_time'));
        $params = $this->checkPostSingleadd($params);
        
        $taskdata = array();
        $taskdata['taskname'] = $params['taskname'];
        $taskdata['tasktype'] = 'single';
        $taskdata['taskstate'] = 'not_check';
        $taskdata['opertype'] = $params['opertype'];
        $taskdata['applyby'] = $this->userInfo['username'];
        $taskdata['applymsg'] = $params['applymsg'];
        $taskdata['apply_time'] = time();
        $taskdata['expire_time'] = empty($params['expire_time'])?Common_Service_Const::ACCOUNTTASK_MAX_EXPIRE_TIME:  strtotime($params['expire_time']);
        $taskid = Advertiser_Service_AccountTaskModel::addTask($taskdata);
        if(!$taskid){
            $this->output('1', '添加任务失败.');
        }
        
        $taskdetaildata = array();
        $taskdetaildata['email'] = $params['email'];
        $taskdetaildata['taskid'] = $taskid;
        $taskdetaildata['opertype'] = $params['opertype'];
        $taskdetaildata['virtual_account_type'] = $params['virtualaccount'];
        $taskdetaildata['taskdetailstate'] = 'not_check';
        $taskdetaildata['money'] = $params['money'];
        $taskdetaildata['applyby'] = $this->userInfo['username'];
        $taskdetaildata['apply_time'] = time();
        $taskdetaildata['expire_time'] = empty($params['expire_time'])?Common_Service_Const::ACCOUNTTASK_MAX_EXPIRE_TIME:  strtotime($params['expire_time']);
        $ret = Advertiser_Service_AccountTaskDetailModel::addTaskdetail($taskdetaildata);
        if(!$ret)$this->output('2', '添加任务详情失败.');
        
        $Admin_account_opertype_config =  Common::getConfig('adminConfig', 'Admin_account_opertype');
        /*管理端操作日志start*/
        $advertiserInfo = Admin_Service_UserModel::getBy(array('email'=>$params['email']));
      	$this->mOperateData = '任务名：'.$params['taskname'].','.$Admin_account_opertype_config[$params['opertype']].',任务详情id：'.$ret.',交易总额：'.$params['money'];
        $this->addOperateLog();
        /*管理端操作日志end*/
        
        $this->output('0', '操作成功.');
        
    }
    
    private function checkPostSingleadd($info){
        if(!trim($info['single_taskname'])){
	        $this->output('1', '请输入任务名称.');
	    }
	    $info['taskname'] = trim($info['single_taskname']);
        
	    if(!$info['single_opertype']){
	        $this->output('1', '请选择操作类型.');
	    }
        $info['opertype'] = $info['single_opertype'];
        
        if(!$info['single_email']){
	        $this->output('1', '请输入对象帐号.');
	    }
        $info['email'] = $info['single_email'];
        
        if(!$info['single_virtualaccount']){
	        $this->output('1', '请选择帐户类型.');
	    }
        $info['virtualaccount'] = $info['single_virtualaccount'];
        
        if(!floatval($info['single_money'])){
	        $this->output('1', '请输入金额.');
	    }
        $info['money'] = floatval($info['single_money']);

        $info['applymsg'] = $info['single_applymsg'];
        $info['expire_time'] = $info['single_expire_time'];
        
        if($info['expire_time']){
            if($info['opertype']=='recharge' && strtotime($info['expire_time'])< time()){
                $this->output('1', '请使用正确的任务到期时间.');
            }
        }
        
	    $ret = Advertiser_Service_AccountTaskModel::getBy(array('taskname'=>$info['taskname']));
	    if($ret){
	        $this->output('1', '任务名称已经存在.');
	    }
        
        //判断对象帐号是否存在
        $ret = Admin_Service_UserModel::getBy(array('email'=>$info['email']));
        if(!$ret){
	        $this->output('1', '对象帐号不存在');
	    }
        
	    return $info;
    }
    
    public function batchaddPostAction(){
        $params= $this->getInput(array('batch_taskname', 'batch_opertype', 'batch_virtualaccount',  'batch_applymsg',  'batch_expire_time',  'batch_csv_file'));
        $params = $this->checkPostBatchadd($params);
        
        $taskdata = array();
        $taskdata['taskname'] = $params['taskname'];
        $taskdata['tasktype'] = 'batch';
        $taskdata['taskstate'] = 'not_check';
        $taskdata['opertype'] = $params['opertype'];
        $taskdata['applyby'] = $this->userInfo['username'];
        $taskdata['applymsg'] = $params['applymsg'];
        $taskdata['apply_time'] = time();
        $taskdata['expire_time'] = empty($params['expire_time'])?Common_Service_Const::ACCOUNTTASK_MAX_EXPIRE_TIME:  strtotime($params['expire_time']);
        $taskid = Advertiser_Service_AccountTaskModel::addTask($taskdata);
        if(!$taskid){
            $this->output('1', '添加任务失败.');
        }
        
        $Admin_account_opertype_config =  Common::getConfig('adminConfig', 'Admin_account_opertype');
        $list = $params['csv_list'];
        $total = count($list);
        $success =0;
        foreach($list as $item){
            $email = $item[0];
            $money = $item[1];
            
            $taskdetaildata = array();
            $taskdetaildata['email'] = $email;
            $taskdetaildata['taskid'] = $taskid;
            $taskdetaildata['opertype'] = $params['opertype'];
            $taskdetaildata['virtual_account_type'] = $params['virtualaccount'];
            $taskdetaildata['taskdetailstate'] = 'not_check';
            $taskdetaildata['money'] = $money;
            $taskdetaildata['applyby'] = $this->userInfo['username'];
            $taskdetaildata['apply_time'] = time();
            $taskdetaildata['expire_time'] = empty($params['expire_time'])?Common_Service_Const::ACCOUNTTASK_MAX_EXPIRE_TIME:  strtotime($params['expire_time']);
            $ret = Advertiser_Service_AccountTaskDetailModel::addTaskdetail($taskdetaildata);
            if($ret){
                $success ++;
                /*管理端操作日志start*/
                $advertiserInfo = Admin_Service_UserModel::getBy(array('email'=>$email));
                $this->mOperateData='任务名：'.$params['taskname'].','.$Admin_account_opertype_config[$params['opertype']].',任务详情id:'.$ret.',交易总额：'.$money;
                $this->addOperateLog();
                /*管理端操作日志end*/
            }
        }
        if($total == $success && $total){
            $this->output('0', '操作成功.');
        }else if($success==0){
            if(!$ret)$this->output('1', '添加任务详情失败.');
        }else{
            if(!$ret)$this->output('2', '添加任务详情部分成功，部分失败.');
        }
    }
    
    private function checkPostBatchadd($info){
        if(!trim($info['batch_taskname'])){
	        $this->output('1', '请输入任务名称.');
	    }
	    $info['taskname'] = trim($info['batch_taskname']);
        
	    if(!$info['batch_opertype']){
	        $this->output('1', '请选择操作类型.');
	    }
        $info['opertype'] = $info['batch_opertype'];
        
        
        if(!$info['batch_virtualaccount']){
	        $this->output('1', '请选择帐户类型.');
	    }
        $info['virtualaccount'] = $info['batch_virtualaccount'];
        
        $info['applymsg'] = $info['batch_applymsg'];
        $info['expire_time'] = $info['batch_expire_time'];
        
        if($info['expire_time']){
            if($info['opertype']=='recharge' && strtotime($info['expire_time'])< time()){
                $this->output('1', '请使用正确的任务到期时间.');
            }
        }
        
        $info['csvfile'] = $info['batch_csv_file'];
        if(!$info['csvfile']){
            $this->output('1', '请上传csv文件.');
        }
	    $ret = Advertiser_Service_AccountTaskModel::getBy(array('taskname'=>$info['taskname']));
	    if($ret){
	        $this->output('1', '任务名称已经存在.');
	    }
        
        $attachPath = Common::getConfig('siteConfig', 'attachPath');
        $file = $attachPath . $info['csvfile'];
        $list = $this->getdatafromcsv($file);
        $info['csv_list'] = $list;
        
        if(empty($list)){
            $this->output('1', '解析文件失败.');
        }
        $email_num_arr = array();
        $line = 1;
        foreach($list as $item){
            $email = $item[0];
            $money = $item[1];
            if(isset($email_num_arr[$email])){
                $this->output('1',  '第'.$line.'行,帐号不能得复:'. $email);
            }else{
                $email_num_arr[$email] = 1;
            }
            
            if(!is_numeric($money)){
                $this->output('1',  '第'.$line.'行,金额格式不正确:'. $money);
            }
            
            //判断对象帐号是否存在
            $result = Admin_Service_UserModel::getBy(array('email'=>$email));
            if(!$result){
                $this->output('1',  '第'.$line.'行,对象帐号不存在:'. $email);
            }
            $line ++;
        }
        
	    return $info;
    }
    
    
    
    public function uploadbatchcsvAction() {
	    $imgId = $this->getInput('imgId');
	    $this->assign('imgId', $imgId);
	    $this->getView()->display('common/uploadBatchAddCsv.phtml');
	    exit;
	}
    
	public function uploadbatchcsvPostAction() {
	    $ret = Common::upload('img', 'batchadd_upload_csv', array('maxSize'=>512000,'allowFileType'=>array('csv')));
        $ret = $this->checkparsecsvfile($ret);
	    $imgId = $this->getInput('imgId');
	    $this->assign('code' , $ret['code']);
	    $this->assign('msg' , $ret['msg']);
	    $this->assign('data', $ret['data']);
	    $this->assign('imgId', $imgId);
	    $this->getView()->display('common/uploadBatchAddCsv.phtml');
	    exit;
	}
    
    /**
     * 
     * @param type $ret
     * @return string
     */
    private function checkparsecsvfile($ret){
        $attachPath = Common::getConfig('siteConfig', 'attachPath');
        $file = $attachPath . $ret['data'];
        $list = $this->getdatafromcsv($file);
        
        if(empty($list)){
            $ret['code'] = -1;
            $ret['msg'] = '解析文件失败';
        }
        $email_num_arr = array();
        $line = 1;
        foreach($list as $item){
            $email = $item[0];
            $money = $item[1];
            if(isset($email_num_arr[$email])){
                $ret['code'] = -1;
                $ret['msg'] =  '第'.$line.'行帐号不能重复:'. $email;
                break;
            }else{
                $email_num_arr[$email] = 1;
            }
            
            if(!is_numeric($money)){
                $ret['code'] = -1;
                $ret['msg'] = '第'.$line.'行金额格式不正确:'. $money;
                break;
            }
            
            //判断对象帐号是否存在
            $result = Admin_Service_UserModel::getBy(array('email'=>$email));
            if(!$result){
                $ret['code'] = -1;
                $ret['msg'] = '第'.$line.'行对象帐号不存在:'. $email;
                break;
            }
            $line ++;
        }
        return $ret;
    }
    
    private function getdatafromcsv($file){
        $filehandle = fopen($file,'r'); 
        $list = array();
        while ($data = fgetcsv($filehandle)) { 
            $list[] = $data;
        }
        fclose($filehandle);
        return $list;
    }
    
    /**
     * 保存帐号流水日志
     * @param type $advertiser_uid
     * @param type $account_type
     * @param type $operate_type
     * @param type $trade_balance
     * @param type $description
     * @return type
     */
    private function saveAccountlog($advertiser_uid, $account_type, $operate_type, $trade_balance, $description){
        $accountlogdata = array();
        $accountlogdata['uid'] = $advertiser_uid;
        $accountlogdata['account_type'] = $account_type;
        $accountlogdata['operate_type'] = $operate_type;
        $accountlogdata['trade_balance'] = $trade_balance;
        $accountlogdata['description'] = $description;
        return Advertiser_Service_AccountlogModel::add($accountlogdata);
    }
    
    /**
     * 保存虚拟金详情
     * @param type $advertiser_uid
     * @param type $account_type
     * @param type $trade_balance
     * @param type $status
     * @param type $taskdetailid
     * @param type $expire_time
     * @param type $operator
     * @return type
     */
    private function saveAccountVirtualDetail($advertiser_uid, $account_type,  $trade_balance, $status, $taskdetailid, $expire_time, $operator){
        $virtualdetaildata = array();
        $virtualdetaildata['uid'] = $advertiser_uid;
        $virtualdetaildata['account_type'] = $account_type;
        $virtualdetaildata['balance'] = $trade_balance;
        $virtualdetaildata['status'] = $status;
        $virtualdetaildata['taskdetailid'] = $taskdetailid;
        $virtualdetaildata['expire_time'] = $expire_time;
        $virtualdetaildata['operator'] = $operator;
        return Advertiser_Service_AccountVirtualDetailModel::add($virtualdetaildata);
    }
    
}


