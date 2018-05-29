<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/29
 * Time: 14:40
 */
class HomeController extends Admin_BaseController {

    public $actions = array(
        'logout' => '/Admin/Login/logout',
        'passwdUrl' => '/Admin/User/passwd',
    );

    public function indexAction(){
        $module = $this->getTopModule();
        $this->assign('module', $module);
        $this->assign('user_name', $this->userInfo['user_name']);
    }

    /**
     * 顶级模块列表 + 用户名
     */
    public function getModuleAction(){
        $data['module'] = $this->getTopModule();
        $data['user_name'] = $this->userInfo['user_name'];
        $this->outputCode(0, '获取成功', $data);
    }

}