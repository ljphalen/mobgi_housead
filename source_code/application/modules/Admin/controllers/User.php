<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class UserController extends Admin_BaseController {
	
	public $actions = array(
		'listUrl' => '/Admin/User/index',
		'viewUrl' => '/Admin/User/view',
		'editUrl' => '/Admin/User/edit',
		'editPostUrl' => '/Admin/User/editPost',
		'deleteUrl' => '/Admin/User/delete',
		'passwdUrl' => '/Admin/User/passwd',
		'passwdPostUrl' => '/Admin/User/passwdPost',
        'batchupdategroupUrl' => '/Admin/User/batchUpdateGroup',
		'checkUrl' => '/Admin/User/check',
		'checkPostUrl' => '/Admin/User/checkPost',
	);
	
	public $perpage = 20;
	/**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {
		$params = array();
		$page = intval($this->getInput('page'));
		if ($page < 1) $page = 1;
		$search= $this->getInput(array('user_name','group_id','is_check','user_type','email','type'));
		if ($search['user_name']) {
		    $params['user_name'] = array('LIKE', $search['user_name']);
		}
		if ($search['email']) {
			$params['email'] = array('LIKE', $search['email']);
		}
		if ($search['group_id']) {
		     $params['group_id'] = $search['group_id'];
		}
		if ($search['user_type']) {
			$params['user_type'] = $search['user_type'];
		}
		if($search['type']=='check_list'){
			if(in_array($search['is_check'], array(Admin_Service_UserModel::ISCHECKING,Admin_Service_UserModel::ISCHECK_NOT_PASS))){
				$params['is_check'] = $search['is_check'];
			}else{
				$params['is_check'] = array('IN', array(Admin_Service_UserModel::ISCHECKING,Admin_Service_UserModel::ISCHECK_NOT_PASS));
			}
			$orderBy= array('is_check'=>'DESC','update_time'=>'DESC');
		}else{
			$params['is_check'] = Admin_Service_UserModel::ISCHECK_PASS;
			$orderBy= array('update_time'=>'DESC');
		}
		list($total, $users) = Admin_Service_UserModel::getList($page, $this->perpage, $params,$orderBy);
		$url = $this->actions['listUrl'].'/?' . http_build_query($search) . '&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
		list(,$groups) = Admin_Service_GroupModel::getAllGroup();
		$groups = Common::resetKey($groups, 'group_id');
		$this->assign('groups', $groups);
		$this->assign('search', $search);
		$this->assign('total', $total);
		$this->assign('users', $users);
		$this->assign('userType', Admin_Service_UserModel::$mUserType);
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function editAction() {
		$uid = $this->getInput('user_id');
		$this->assign('navTitle', '添加');
		

		
		$userRelAppList = array();
		if($uid){
			$this->assign('navTitle', '编辑');
			$userInfo = Admin_Service_UserModel::getUser(intval($uid));
			$this->assign('userInfo', $userInfo);
			$userRelAppList = Admin_Service_UserAppRelModel::getsBy(array('user_id'=>$uid));
		}
		$this->assign('userType', Admin_Service_UserModel::$mUserType);
		list(,$groups) = Admin_Service_GroupModel::getAllGroup();
		$this->assign('groups', $groups);
		
		$params['is_check'] = MobgiApi_Service_AdAppModel::ISCHECK_PASS;
		$appInfo = MobgiApi_Service_AdAppModel::getsBy($params);
		$this->assign('appInfo', $appInfo);
		$this->assign('userRelAppList', Common::resetKey($userRelAppList, 'app_key'));
		
	}
	
	/**
	 *
	 * Enter description here ...
	 */
	public function checkAction() {
		$uid = $this->getInput('user_id');
		$this->assign('navTitle', '审核');
	
		$userRelAppList = array();
		if($uid){
			$userInfo = Admin_Service_UserModel::getUser(intval($uid));
			$this->assign('userInfo', $userInfo);
			$userRelAppList = Admin_Service_UserAppRelModel::getsBy(array('user_id'=>$uid));
		}
		$this->assign('userType', Admin_Service_UserModel::$mUserType);
		$this->assign('registerType', Admin_Service_UserModel::$mRegisterType);
	
	}
	public function checkPostAction() {
		$info = $this->getPost(array('user_id', 'is_check', 'check_msg'));
		if (!trim($info['check_msg'])) $this->output(-1, '审批意见不能为空.'); 
		$ret = Admin_Service_UserModel::updateBy($info, array('user_id'=>$info['user_id']));
		if (!$ret) $this->output(-1, '操作失败');
		$this->output(0, '操作成功.');
	}
	
	/**
	 *
	 * Enter description here ...
	 */
	public function viewAction() {
		$uid = $this->getInput('user_id');
		$this->assign('navTitle', '添加');
		$userRelAppList = array();
		if($uid){
			$this->assign('navTitle', '编辑');
			$userInfo = Admin_Service_UserModel::getUser(intval($uid));
			$this->assign('userInfo', $userInfo);
			$userRelAppList = Admin_Service_UserAppRelModel::getsBy(array('user_id'=>$uid));
		}
		$this->assign('userType', Admin_Service_UserModel::$mUserType);
		list(,$groups) = Admin_Service_GroupModel::getAllGroup();
		$this->assign('groups', $groups);
		
		$params['is_check'] = MobgiApi_Service_AdAppModel::ISCHECK_PASS;
		$appInfo = MobgiApi_Service_AdAppModel::getsBy($params);
		$this->assign('appInfo', $appInfo);
		$this->assign('userRelAppList', Common::resetKey($userRelAppList, 'app_key'));
		
	}
	
	private  function checkPostParam($info){	
		if (!$info['user_id'] && !strlen(trim($info['email']))) $this->output(-1, '用户EMAIL必填.'.strlen(trim($info['email'])));
		if (!$info['user_id'] && trim($info['password']) == '') $this->output(-1, '密码不能为空.');
		if ($info['password'] &&( strlen($info['password'])  < 6 || strlen($info['password'])  > 16 )  ){
			$this->output(-1, '用户密码长度６-16位之间');
		}
		if($info['user_id'] == '8888' && $this->userInfo['user_id'] != $info['user_id']){
			$this->output(-1, '不能修改管理员信息');
		}
		if($info['user_id']){	
			$params['user_id'] = array('<>', intval($info['user_id']) );
		}	
		$params['email'] = $info['email'];
		$ret = Admin_Service_UserModel::getBy($params);
		if ($ret){
			$this->output(-1, '邮件地址已经存在.');
		}
		unset($params['email']);	
		$params['user_name'] = $info['user_name'];
		$ret = Admin_Service_UserModel::getBy($params);
		if ($ret){
			$this->output(-1, '用户名已经存在.');
		}
		
	}
	/**
	 * 
	 * Enter description here ...
	 */
	public function editPostAction() {
		$info = $this->getPost(array('user_id', 'group_id', 'password','user_name','email','user_type','app_key','is_lock','is_admin'));
	    $this->checkPostParam($info);
		$info['is_check'] = 1;
		if($info['user_type'] == Admin_Service_UserModel::DEVERLOPER_USER){
			$info['group_id'] = 1;
		}
		if($info['user_id']){
			if(!$info['password']){
				unset($info['password']);
			}
			$ret = Admin_Service_UserModel::updateUser($info, intval($info['user_id']));
		}else {
			$ret = Admin_Service_UserModel::addUser($info);
			$info['user_id'] = $ret;
		}
		$this->updateUserAppRel($info);
		if (!$ret) $this->output(-1, '操作失败');
		$this->output(0, '操作成功.'); 		
	}
	
	private function updateUserAppRel($info){
		if($info['is_admin']){
			return ;
		}
		if($info['user_type'] == Admin_Service_UserModel::ADS_USER){
			return ;
		}
		$userAppRelParams['user_id'] = $info['user_id'];
		$userAppRelList = Admin_Service_UserAppRelModel::getsBy($userAppRelParams);
		if($userAppRelList){
			foreach ($userAppRelList as $value) {
				if(!in_array($value['app_key'], $info['app_key'])){
					$userAppRelParams['app_key'] = $value['app_key'];
					Admin_Service_UserAppRelModel::deleteBy($userAppRelParams);
				}
			}
		}
		$appKeys = array();
		if($userAppRelList){
			$appKeys = array_keys(Common::resetKey($userAppRelList, 'app_key'));
		}
		if($info['app_key']){
			foreach ($info['app_key'] as $value) {
				if(!in_array($value, $appKeys)){
					unset($data);
					$data['user_id'] = $info['user_id'];
					$data['app_key'] = $value;
					Admin_Service_UserAppRelModel::add($data);
				}
			}
		}
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function passwdAction() {
		if (!$this->userInfo) $this->redirect("/Admin/Login/index");
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function passwdPostAction() {
		$adminInfo = $this->userInfo;
		if (!$adminInfo['user_id']) $this->output(-1, '登录超时,请重新登录后操作');
		$info = $this->getPost(array('current_password','password','r_password'));
		$ret = Admin_Service_UserModel::checkUser($adminInfo['email'], $info['current_password']);
		if ($ret['code']) $this->output(-1, $ret['msg']);
		$info['user_id'] = $adminInfo['user_id'];
		if (strlen($info['password']) < 6 || strlen($info['password']) > 16) $this->output(-1, '用户密码长度6-16位之间');
		if ($info['password'] !== $info['r_password']) $this->output(-1, '两次密码输入不一致');
		$result = Admin_Service_UserModel::updateUser($info, intval($info['user_id']));
		if (!$result) $this->output(-1, '编辑失败');
		$this->output(0, '操作成功');
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function deleteAction() {
		$uid = $this->getGet('user_id');
		$info = Admin_Service_UserModel::getUser($uid);
		if ($info && $info['group_id'] == 0) $this->output(-1, '此用户无法删除');
		if ($uid < 1) $this->output(-1, '参数错误');
		$result = Admin_Service_UserModel::deleteUser($uid);
		if (!$result) $this->output(-1, '操作失败');
		$this->output(0, '操作成功');
	}
    /**
     * 更改权限
     */
    public function batchUpdateGroupAction() {
	    $info = $this->getPost(array('action', 'ids','content'));
	    if (!count($info['ids'])) $this->output(-1, '没有可操作的项.');
        $oldGroupObjs = array();
        foreach ($info['ids'] as $val){
            $oldGroupObjs[$val]= Admin_Service_UserModel::getBy(array('user_id'=>$val));
        }
        list(, $groups) = Admin_Service_GroupModel::getAllGroup();
        $groups = common::resetKey($groups, 'group_id');
	    if($info['action'] =='changeSystemGroup'){
            $data=array();
            $data['group_id'] = intval($info['content']);
            $params = array();
            $params['user_id'] = array('IN', $info['ids']);
            $ret = Admin_Service_UserModel::updateBy($data, $params);
	    }
	    if (!$ret) $this->output('-1', '操作失败.');
	    $this->output('0', '操作成功.');
	}
}
