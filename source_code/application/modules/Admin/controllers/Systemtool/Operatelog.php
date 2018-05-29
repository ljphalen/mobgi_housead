<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-9 14:24:56
 * $Id: Management.php 62100 2016-9-9 14:24:56Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');
class Systemtool_OperatelogController extends Admin_BaseController {
	
	public $actions = array(
        'listUrl' => '/Admin/Systemtool_Operatelog/index',
	);
    
    public $perpage = 20;
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {

		$sdate = $this->getInput('sdate');
		$edate = $this->getInput('edate');

		 $page = intval($this->getInput('page'));
	    if ($page < 1) $page = 1;
	    
	    $search= $this->getInput(array('sdate','edate'));
		if(empty($search['sdate']) && empty($search['edate'])){
			$search['sdate'] = date("Y-m-d");
			$search['edate']  = date("Y-m-d");
		}

		$params  = array();
		if($search['sdate'] && empty($search['edate'])){
			$params['create_time'] = array('>=', strtotime($search['sdate'] ));
		}
		if(empty($search['sdate']) && $search['edate']){
			$params['create_time'] = array('<=', strtotime($search['edate'])+86400);

		}
		if($search['sdate'] && $search['edate']){
			$params['create_time'] = array(array('>=', strtotime($search['sdate'] )), array('<=', strtotime($search['edate']) + 86400));
		}
		
		list($total, $logList) =Admin_Service_OperatelogModel::getList($page, $this->perpage, $params,array('create_time'=>'DESC'));
		$url = $this->actions['listUrl'].'/?' . http_build_query($search) . '&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
		$this->assign('search', $search);
		$this->assign('total', $total);

		if($logList){
			$userIds = array_keys(Common::resetKey($logList, 'user_id'));
			$userInfo = Admin_Service_UserModel::getsBy(array('user_id'=>array('IN', $userIds)));
			$userInfo = Common::resetKey($userInfo, 'user_id');
			$this->assign('userInfo', $userInfo);
		}
		$this->assign('loglist', $logList);

    }
    
}

