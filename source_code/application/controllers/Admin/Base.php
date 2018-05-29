<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Admin_BaseController extends Common_BaseController {
    public $userInfo;
    public $actions = array();
    public $mUserRelAppList;
    public $mOperateData = '';


    /**
     * 获取用户
     * @return int
     */
    public function getUserId() {
        $userId = $this->userInfo['user_id'];
        if ($this->userInfo['is_admin']) {
            Yaf_Registry::set('is_admin', true);
        }
        return $userId;
    }

    public function getUserName() {
        $userName = $this->userInfo['user_name'];
        if ($this->userInfo['is_admin']) {
            Yaf_Registry::set('is_admin', true);
        }
        return $userName;
    }

    /**
     *
     * Enter description here ...
     */
    public function init() {
        parent::init();
        Yaf_Registry::set('backEnd', true);
        $frontroot = Yaf_Application::app()->getConfig()->webroot;
        $adminroot = Yaf_Application::app()->getConfig()->adminroot;
        $staticroot = Yaf_Application::app()->getConfig()->staticroot;
        $this->assign("frontroot", $frontroot);
        $this->assign("adminroot", $adminroot);
        $this->assign("staticPath", $staticroot . '/static');
        $this->assign('titlepre', Common::getTitlePre());
        $this->checkRight();
        $this->checkToken();
        $this->setUserAppRel();
    }

    public function setUserAppRel() {
        $this->mUserRelAppList = Admin_Service_UserAppRelModel::getsBy(array('user_id' => $this->userInfo['user_id']));
    }


    /**
     * 检查token
     */
    protected function checkToken() {
        if (!$this->getRequest()->isPost() || $this->isVueRequest()) return true;
        $token = $this->getRequest()->get('token');
        $result = Common::checkToken($token);
        if (Common::isError($result)) $this->output(-1, $result['msg']);
        return true;
    }

    public function updateAppCache() {
        $action = $this->getRequest()->getActionName();
        if ($this->appCacheName && in_array($action, array('add_post', 'edit_post', 'delete'))) {
            if (is_array($this->appCacheName)) {
                foreach ($this->appCacheName as $value) {
                    Game_Service_Config::setValue($value, Common::getTime());
                }
            } else {
                Game_Service_Config::setValue($this->appCacheName, Common::getTime());
            }
        }
    }

    /**
     * 单独执行更新AppCache操作
     * @param $appCacheKey
     */
    public function updateAppCacheKey($appCacheKey) {
        if (is_array($appCacheKey)) {
            foreach ($appCacheKey as $value) {
                Game_Service_Config::setValue($value, Common::getTime());
            }
        } else {
            Game_Service_Config::setValue($appCacheKey, Common::getTime());
        }
    }

    /**
     * updateAppCache
     */
    public function updateVersion() {
        $action = $this->getRequest()->getActionName();
        if ($this->versionName && in_array($action, array('add_post', 'edit_post', 'delete', 'batchUpdate'))) {
            if (is_array($this->versionName)) {
                foreach ($this->versionName as $value) {
                    Game_Service_Config::setValue($value, Common::getTime());
                }
            } else {
                Game_Service_Config::setValue($this->versionName, Common::getTime());
            }
        }
    }


    public function isCheckRight($noVerify) {
        $module = $this->getRequest()->getModuleName();
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        if (in_array($module . '_' . $controller, $noVerify)) {
            return false;
        }
        if ($action && strtolower(substr($action, 0, 3)) == 'get') {
            return false;
        }
        return true;

    }

    /**
     *
     * Enter description here ...
     */
    public function checkRight() {
        $this->userInfo = Admin_Service_UserModel::isLogin();
        if (!$this->userInfo && !$this->inLoginPage()) {
            if ($this->isVueRequest()) {
                $this->outputCode(Common_Expection_Admin::EXP_SESSION_EXPIRED, '会话过期，请重新登录', array('url' => '/Admin/Login/index'));
            } else {
                $this->redirect("/Admin/Login/index");
            }
        } else {
            $module = $this->getRequest()->getModuleName();
            $controller = $this->getRequest()->getControllerName();
            $action = $this->getRequest()->getActionName();
            $flag = false;
            $userlevels = $this->getUserLevels();
            $mc = "_" . $module . "_" . $controller;
            foreach ($userlevels as $key => $value) {
                if (stristr($value, $mc)) {
                    $flag = true;
                    break;
                }
            }
            if (!$flag) {
                $this->showMsg('-1', '没有菜单操作权限1');
                exit();
            }
            list(, , , , $noVerify) = Common::getConfig("adminMainMenu");
            if ($this->userInfo['group_id'] && $this->isCheckRight($noVerify)) {
                $groupInfo = Admin_Service_GroupModel::getGroup($this->userInfo['group_id']);
                if (empty($groupInfo)) {
                    $this->showMsg('-1', '没有菜单操作权限2');
                    exit();
                }
                $groupRightList = array();
                foreach ($groupInfo['menu_right'] as $value) {
                    foreach ($value as $key => $val) {
                        array_push($groupRightList, $key);
                    }
                }
                if (empty($groupRightList)) {
                    $this->showMsg('-1', '没有菜单操作权限3');
                    exit();
                }
                $params['id'] = array('IN', $groupRightList);
                $params['module'] = $module;
                $params['controler'] = $controller;
                $menuConfig = Admin_Service_MenuConfigModel::getsBy($params);
                if (empty($menuConfig)) {
                    $this->showMsg('-1', '没有菜单操作权限4');
                    exit();
                }

                $flag = false;
                foreach ($menuConfig as $key => $value) {
                    if (in_array($module . '_' . $controller, $noVerify)) {
                        $flag = true;
                        break;
                    }
                    if (strtolower($value['module']) == strtolower($module) && strtolower($value['controler']) == strtolower($controller) && stristr($value['action'], $action)) {
                        $flag = true;
                        break;
                    }
                }
                if (!$flag) {
                    $this->showMsg('-1', '没有菜单操作权限5');
                    exit();
                }
            }
        }
    }

    /**
     *
     * Enter description here ...
     */
    public function inLoginPage() {
        $module = $this->getRequest()->getModuleName();
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();

        if ($module == 'Admin' && $controller == 'Login' && ($action == 'index' || $action == 'login')) {
            return true;
        }
        return false;
    }

    /**
     *
     * Enter description here ...
     */
    public function getUserMenu() {
        $userInfo = Admin_Service_UserModel::getUser($this->userInfo['user_id']);
        $groupInfo = array();
        if ($userInfo['group_id'] == 0) {
            $groupInfo = array('group_id' => 0);
        } else {
            $groupInfo = Admin_Service_GroupModel::getGroup($userInfo['group_id']);
        }
        $menuService = new Common_Service_Menu(Common::getConfig("siteConfig", "adminMainMenu"), 0);
        list($usermenu, $mainview, $usersite, $userlevels) = $menuService->getUserMenu($groupInfo);
        //array_push($userlevels, "_Admin_Initiator", "_Admin_Index", '_Admin_Login');
        return array($usermenu, $mainview, $usersite, $userlevels);
    }

    /**取用户系统权限*/
    public function getUserLevels() {
        $userInfo = Admin_Service_UserModel::getUser($this->userInfo['user_id']);
        if ($userInfo['group_id'] == 0) {
            $groupInfo = array('group_id' => 0);
        } else {
            $groupInfo = Admin_Service_GroupModel::getGroup($userInfo['group_id']);
        }
        $menuService = new Common_Service_Menu(Common::getConfig("siteConfig", "adminMainMenu"), 0);
        $userlevels = $menuService->getUserLevels($groupInfo);
        return $userlevels;
    }

    public function cookieParams() {
        $module = $this->getRequest()->getModuleName();
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        $name = sprintf('%s_%s_%s', $module, $controller, $action);

        $tmp = array();
        $not = array('token', 's');
        foreach ($_REQUEST as $key => $value) {
            if (!in_array($key, $not)) $tmp[$key] = $this->getInput($key);
        }
        Util_Cookie::set($name, Common::encrypt(json_encode($tmp)), false, Common::getTime() + (5 * 3600));
    }

    /**
     * 获取顶级模块
     * @return mixed
     */
    public function getTopModule(){
        $file = BASE_PATH.Common::getConfig("siteConfig", "adminMainMenu");
        list ($topModule, , , ,) = require $file;
        if($this->userInfo['group_id'] != 0){
            $groupInfo = Admin_Service_GroupModel::getBy(array('group_id'=>$this->userInfo['group_id']));
            $topModuleConfig = json_decode($groupInfo['top_module_config'],true);
            foreach($topModule as $key => $value){
                if(!array_key_exists($key, $topModuleConfig)){
                    unset($topModule[$key]);
                }
            }
        }
        return $topModule;
    }

    /**
     *
     * @return boolean
     */
    public function checkCookieParams() {
        $s = $this->getInput('s');

        $module = $this->getRequest()->getModuleName();
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        $name = sprintf('%s_%s_%s', $module, $controller, $action);

        $params = json_decode(Common::encrypt(Util_Cookie::get($name), 'DECODE'), true);

        if (count($params) && $s) {
            $adminroot = Yaf_Application::app()->getConfig()->adminroot;
            $url = sprintf('%s/%s/%s/%s?%s', $adminroot, $module, $controller, $action, http_build_query($params));
            $this->redirect($url);
        }
    }

    /**
     * @param unknown_type $code
     * @param string $msg
     * @param array $data
     */
    public function output($code, $msg = '', $data = array()) {
        header("Content-type:text/json");
        exit(json_encode(array(
            'success' => $code == 0 ? true : false,
            'msg' => $msg,
            'data' => $data
        )));
    }

    /**
     * @param unknown_type $code
     * @param string $msg
     * @param array $data
     */
    public function outputCode($code, $msg = '', $data = array()) {
        header("Content-type:text/json");
        exit(json_encode(array(
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        )));
    }

    /**
     * @return bool
     */
    public function addOperateLog() {
        $noWriteLogAction = array('index', 'list');
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        /* 	if(in_array($action, $noWriteLogAction)){
                return fase;
            } */
        $data['user_id'] = $this->userInfo['user_id'];
        $data['object'] = 1;
        $data['module'] = $controller;
        $data['sub_module'] = $action;
        $data['content'] = $this->mOperateData;
        return Admin_Service_OperatelogModel::addOperateLog($data);
    }


    public function __destruct() {

    }

}
