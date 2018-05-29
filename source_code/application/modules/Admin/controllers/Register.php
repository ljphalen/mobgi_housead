<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class RegisterController extends Common_BaseController {
	
	public $actions = array(
		'loginUrl' => '/Admin/Login/login',
		'registerUrl' => '/Admin/Register/index' 
	);
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {
		$this->assign('loginUrl', $this->actions['loginUrl']);
		$this->assign('registerUrl', $this->actions['registerUrl']);
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function registerAction() {	   
		$login = $this->getRequest()->getPost();
		if (!isset($login['username']) || !isset($login['password'])) {
			return $this->showMsg(-1, '用户名或者密码不能为空.');
		}
		$ret = Admin_Service_UserModel::login($login['username'], $login['password']);
		if (!$ret) $this->showMsg(-1, '登录失败.');
		$this->redirect('/Admin/Index/index');
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
