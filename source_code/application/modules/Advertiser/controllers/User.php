<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class UserController extends Advertiser_BaseController {
	
	public $actions = array(
		'listUrl' => '/Advertiser/User/index',
		'addUrl' => '/Advertiser/User/add',
		'addPostUrl' => '/Advertiser/User/add_post',
		'editUrl' => '/Advertiser/User/edit',
		'editPostUrl' => '/Advertiser/User/edit_post',
		'deleteUrl' => '/Advertiser/User/delete',
		'passwdUrl' => '/Advertiser/User/passwd',
		'passwdPostUrl' => '/Advertiser/User/passwd_post',
        'uploadUrl' => '/Advertiser/User/uploadUserimg',
	    'uploadPostUrl' => '/Advertiser/User/uploadUserimgPost',
	);
	
	public $perpage = 20;
	/**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {
		$page = intval($this->getInput('page'));
		$perpage = $this->perpage;
		
		list($total, $users) = Advertiser_Service_UserModel::getList($page, $perpage);
		list(,$groups) = Advertiser_Service_GroupModel::getAllGroup();
		$groups = Common::resetKey($groups, 'groupid');
		
		$this->assign('users', $users);
		$this->assign('groups', $groups);
		$this->assign('pager', Common::getPages($total, $page, $perpage, $this->actions['listUrl'].'/?'));
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function editAction() {
		$uid = $this->getInput('uid');
		$userInfo = Advertiser_Service_UserModel::getUser(intval($uid));
		list(,$groups) = Advertiser_Service_GroupModel::getAllGroup();
		$this->assign('userInfo', $userInfo);
		$this->assign('groups', $groups);
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function addAction() {
		list(,$groups) = Advertiser_Service_GroupModel::getAllGroup();
		$this->assign('groups', $groups);
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function add_postAction() {
		$info = $this->getPost(array('username','password','r_password','email','groupid'));
		if (strlen($info['username']) < 5 || strlen($info['username']) > 16) $this->output(-1, '用户名长度5-16位之间');
		if (strlen($info['password']) < 5 || strlen($info['password']) > 16) $this->output(-1, '用户密码长度5-16位之间.');
		if ($info['password'] !== $info['r_password']) $this->output(-1, '两次密码输入不一致.');
		if ($info['email'] == '') $this->output(-1, '用户EMAIL必填.');
		if (Advertiser_Service_UserModel::getUserByName($info['username'])) $this->output(-1, '用户名已经存在.');
		if (Advertiser_Service_UserModel::getUserByEmail($info['email'])) $this->output(-1, '邮件地址已经存在.');
		$info['registerip'] = Util_Http::getClientIp();
		$result = Advertiser_Service_UserModel::addUser($info);
		if (!$result) $this->output(-1, '操作失败');
		$this->output(0, '操作成功');
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function edit_postAction() {
		$info = $this->getPost(array('uid', 'groupid', 'password', 'r_password'));
		if ($info['password'] == '') $this->output(-1, '密码不能为空.'); 
		if (strlen($info['password']) < 5 || strlen($info['password']) > 16) $this->output(-1, '用户密码长度5-16位之间');
		if ($info['password'] !== $info['r_password']) $this->output(-1, '两次密码输入不一致');
		$ret = Advertiser_Service_UserModel::updateUser($info, intval($info['uid']));
		if (!$ret) $this->output(-1, '更新用户失败');
		$this->output(0, '更新用户成功.'); 		
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function passwdAction() {
		if (!$this->userInfo) $this->redirect("/Advertiser/Login/index");
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function passwd_postAction() {
		$adminInfo = $this->userInfo;
		if (!$adminInfo['advertiser_uid']) $this->output(-1, '登录超时,请重新登录后操作');
		$info = $this->getPost(array('current_password','password','r_password'));
		$ret = Advertiser_Service_UserModel::checkUser($adminInfo['email'], $info['current_password']);
		if (Common::isError($ret)) $this->output(-1, $ret['msg']);
		$info['advertiser_uid'] = $adminInfo['advertiser_uid'];
		if (strlen($info['password']) < 5 || strlen($info['password']) > 16) $this->output(-1, '用户密码长度5-16位之间');
		if ($info['password'] !== $info['r_password']) $this->output(-1, '两次密码输入不一致');
		$result = Advertiser_Service_UserModel::updateUser($info, intval($info['advertiser_uid']));
		if (!$result) $this->output(-1, '编辑失败');
		$this->output(0, '操作成功');
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function deleteAction() {
		$uid = $this->getInput('uid');
		$info = Advertiser_Service_UserModel::getUser($uid);
		if ($info && $info['groupid'] == 0) $this->output(-1, '此用户无法删除');
		if ($uid < 1) $this->output(-1, '参数错误');
		$result = Advertiser_Service_UserModel::deleteUser($uid);
		if (!$result) $this->output(-1, '操作失败');
		$this->output(0, '操作成功');
	}
    
    
    public function managementAction(){
        $adminInfo = $this->userInfo;
        $advertiser_uid = $adminInfo['advertiser_uid'];
		$userInfo = Advertiser_Service_UserModel::getUser(intval($advertiser_uid));
		$this->assign('userInfo', $userInfo);
    }
        
    /**
     * 
     */
    public function baseinfo_postAction() {
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_User_Edit')){
            $this->output(1, 'permission denied!');
        }
        /*权限校验end*/
        
		$adminInfo = $this->userInfo;
		if (!$adminInfo['advertiser_uid']) $this->output(-1, '登录超时,请重新登录后操作');
        $oldinfo = Advertiser_Service_UserModel::getBy(array('advertiser_uid'=>intval($adminInfo['advertiser_uid'])));
		$info = $this->getPost(array('advertiser_name','address'));
		$info['advertiser_uid'] = $adminInfo['advertiser_uid'];
		if (empty($info['advertiser_name'])) $this->output(-1, '帐户名称不能为空');
		$result = Advertiser_Service_UserModel::updateUser($info, intval($info['advertiser_uid']));
		if (!$result) $this->output(-1, '编辑失败');
        
        /*操作日志start*/
        $logdata=array();
        $logdata['module'] = 'adver_account';
        $logdata['sub_module'] = 'edit_account';
        $logdata['content']='';
        foreach($info as $key=>$item){
            if(empty($item))continue;
            if($item != $oldinfo[$key]){
                $different.=$key.":".$oldinfo[$key].','.$key.":".$item.',';
            }
        }
        if(empty($different)){
            $logdata['content'].='未更新;';
        }else{
            $logdata['content'].=$different.';';
        }
        
        $this->addOperatelog($logdata);
        /*操作日志end*/
        
		$this->output(0, '操作成功');
    }
    
    public function uploadUserimgAction() {
	    $imgId = $this->getInput('imgId');
	    $this->assign('imgId', $imgId);
	    $this->getView()->display('common/upload.phtml');
	    exit;
	}
	public function uploadUserimgPostAction() {
	    $ret = Common::upload('img', 'advertiser',  array('allowFileType'=>array('gif','jpeg','jpg','png','bmp')));
	    $imgId = $this->getInput('imgId');
	    $this->assign('code' , $ret['data']);
	    $this->assign('msg' , $ret['msg']);
	    $this->assign('data', $ret['data']);
	    $this->assign('imgId', $imgId);
	    $this->getView()->display('common/upload.phtml');
	    exit;
	}
    public function advertiserinfo_postAction() {
        /*权限校验start*/
        if(!$this->hasAdvertiserPermission('Advertiser_User_Edit')){
            $this->output(1, 'permission denied!');
        }
        /*权限校验end*/
        
		$adminInfo = $this->userInfo;
		if (!$adminInfo['advertiser_uid']) $this->output(-1, '登录超时,请重新登录后操作');
        $oldinfo = Advertiser_Service_UserModel::getBy(array('advertiser_uid'=>intval($adminInfo['advertiser_uid'])));
		$info = $this->getPost(array('company_name','business_license','ad_qualification'));
		$info['advertiser_uid'] = $adminInfo['advertiser_uid'];
        $info['status']='notchecked';#已激活未审核的帐号当有更新广告主信息时需要把状态改成未审核
		if (empty($info['company_name'])) $this->output(-1, '帐户名称不能为空');
		$result = Advertiser_Service_UserModel::updateUser($info, intval($info['advertiser_uid']));
		if (!$result) $this->output(-1, '编辑失败');
        
        /*操作日志start*/
        $logdata=array();
        $logdata['module'] = 'adver_account';
        $logdata['sub_module'] = 'edit_account';
        $logdata['content']='';
        foreach($info as $key=>$item){
            if(empty($item))continue;
            if($item != $oldinfo[$key]){
                $different.=$key.":".$oldinfo[$key].','.$key.":".$item.',';
            }
        }
        if(empty($different)){
            $logdata['content'].='未更新;';
        }else{
            $logdata['content'].=$different.';';
        }
        
        $this->addOperatelog($logdata);
        /*操作日志end*/
        
		$this->output(0, '操作成功');
	}
}
