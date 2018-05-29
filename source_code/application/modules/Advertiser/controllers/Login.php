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
		'loginUrl' => '/Advertiser/Login/login',
		'logoutUrl' => '/Advertiser/Login/logout',
        'registerUrl' => '/Advertiser/Register/index',
        'pwdeditemailUrl'=>'/Advertiser/Register/pwdeditemail',
		'indexUrl' => '/Advertiser/Index/index' 
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
//		$login = $this->getRequest()->getPost();
        $login = $this->getPost(array('email', 'password'));
		if (!isset($login['email']) || !isset($login['password'])) {
			return $this->showMsg(-1, '邮箱帐号或者密码不能为空.');
		}
		$ret = Advertiser_Service_UserModel::login($login['email'], $login['password']);
		if (!$ret) $this->showMsg(-1, '登录失败:邮箱帐号或者密码错误.');
		$this->redirect('/Advertiser/Index/index');
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function logoutAction() {
		Advertiser_Service_UserModel::logout();
		$this->redirect("/Advertiser/Login/index");
	}
}
