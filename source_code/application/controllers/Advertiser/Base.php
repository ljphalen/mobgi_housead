<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Advertiser_BaseController extends Common_BaseController {
    public $userInfo;
    public $actions = array();
    public $userlevels = array();

    /**
     *
     * Enter description here ...
     */
    public function init() {
        parent::init();
        Yaf_Registry::set('backEnd', true);
        $webRoot = Yaf_Application::app()->getConfig()->webroot;
        $staticroot = Yaf_Application::app()->getConfig()->staticroot;
        $this->assign("webRoot", $webRoot);
        $this->assign("staticPath", $staticroot . '/static/advertiser');
        $this->checkRight();
        $this->checkToken();
        $this->checkisGdtDirect();
        $this->checkIsreport();
        $this->assign('advertiser_name', $this->userInfo['advertiser_name']);
        $this->assign('titlepre', Common::getTitlePre());
        $this->assign('gdtconfig', $this->gdtconfig);
        //针对报表帐号.
        $this->assign('isreport', $this->userInfo['isreport']);
        $this->assign('related_advertiser_uid', $this->related_advertiser_uid);
        // $menuId = isset($this->getInput('menuId'))?$this->getInput('menuId'):'';
        // $this->assign('menuId', $menuId);
    }


    /**
     * 检查token
     */
    protected function checkToken() {
        if (!$this->getRequest()->isPost()) return true;
        $post = $this->getRequest()->getPost();
        $result = Common::checkToken($post['token']);
        if (Common::isError($result)) $this->output(-1, $result['msg']);
        return true;
    }


    /**
     *
     * Enter description here ...
     */
    public function checkRight() {
        $this->userInfo = Advertiser_Service_UserModel::isLogin();
        if($this->userInfo){
            if($this->userInfo['type'] ==Advertiser_Service_UserModel::ADVERTISER_REPORT ){
                $this->userInfo['isreport'] = 1;
            }else{
                $this->userInfo['isreport'] = 0;
            }
        }
        if (!$this->userInfo && !$this->inLoginPage()) {
            $this->redirect("/Advertiser/Login/index");
        } else {
            $this->userlevels = $this->getUserLevels();
            return true;
        }
    }

    /**
     *
     * Enter description here ...
     */
    public function checkisGdtDirect() {
        if($this->userInfo['type'] != Advertiser_Service_UserModel::ADVERTISER_GDT){
            $this->gdtconfig = false;
        }else{
        $this->gdtconfig = Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']);
    }
	}
    /**
     * 报表查看帐号返回其关联的帐号.
     * @return type
     */
    public function checkIsreport() {
        if ($this->userInfo['isreport']) {
            $reportUser = Admin_Service_ReportuserModel::getBy(array('advertiser_uid' => $this->userInfo['advertiser_uid']));
            if ($reportUser) {
                $this->related_advertiser_uid = $reportUser['related_advertiser_uid'];
            } else {
                $this->related_advertiser_uid = 0;
            }
        } else {
            $this->related_advertiser_uid = 0;
        }
        $this->userInfo['related_advertiser_uid'] = $this->related_advertiser_uid;
        return $this->related_advertiser_uid;
    }

    /**取用户系统权限*/
    public function getUserLevels() {
        $userInfo = Advertiser_Service_UserModel::getUser($this->userInfo['advertiser_uid']);
        $groupInfo = array();
        if ($userInfo['groupid'] == 0) {
            $groupInfo = array('groupid' => 0);
        } else {
            $groupInfo = Advertiser_Service_GroupModel::getGroup($userInfo['groupid']);
        }
        $menuService = new Common_Service_Menu(Common::getConfig("siteConfig", "advertiserGroupPermission"), 0);
        $userlevels = $menuService->getUserLevels($groupInfo);
        array_push($userlevels, "_Advertiser_Initiator", "_Advertiser_Index", '_Advertiser_Login');
        return $userlevels;
    }

    /**
     * 检测广告主用户权限
     * @param type $permissionFlag
     * @param type $app
     * @return boolean
     */
    public function hasAdvertiserPermission($permissionFlag, $app = '') {
        $truepermissionFlag = $app . '_' . $permissionFlag;
        if (in_array($truepermissionFlag, $this->userlevels)) {
            return true;
        } else {
            return false;
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
        if ($module == 'Advertiser' && $controller == 'Login' && ($action == 'index' || $action == 'login')) {
            return true;
        }
        return false;
    }


    /**
     *
     * Enter description here ...
     * @param unknown_type $code
     * @param unknown_type $msg
     * @param unknown_type $data
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
     * 获取广告关联账户
     * @return mixed
     */

    protected function getReportAccountId() {
        if ($this->userInfo['isreport']) {
            return $this->userInfo['related_advertiser_uid'];
        } else {
            return $this->userInfo['advertiser_uid'];
        }
    }
}
