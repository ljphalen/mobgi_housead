<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class LoginController extends Common_BaseController {
	
	public $actions = array(
		'loginUrl' => '/Admin/Login/login',
		'logoutUrl' => '/Admin/Login/logout',
		'indexUrl' => '/Admin/Index/index'
	);
	/**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {
		$this->assign('loginUrl', $this->actions['loginUrl']);
		$this->assign('logoutUrl', $this->actions['logoutUrl']);
		$this->assign('indexUrl', $this->actions['indexUrl']);
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function loginAction() {	   
		$login = $this->getRequest()->getPost();
		if (!isset($login['email']) || !isset($login['password'])) {
			return $this->showMsg(-1, '用户名或者密码不能为空.');
		}
		$ret = Admin_Service_UserModel::login($login['email'], $login['password']);
		if ($ret['code'] != 0) $this->showMsg(-1, $ret['msg']);
		$this->redirect('/Admin/Home/index');
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function logoutAction() {
		Admin_Service_UserModel::logout();
		$this->redirect("/Admin/Login/index");
	}
}
