<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class GroupController extends Admin_BaseController {
    
    public $actions = array(
        'listUrl' => '/Admin/Group/index',
    	'viewUrl' => '/Admin/Group/view',
        'addUrl' => '/Admin/Group/add',
    	'addPostUrl' => '/Admin/Group/addPost',
    	'editUrl' => '/Admin/Group/edit',
    	'editPostUrl' => '/Admin/Group/editPost',
    	'delUrl' => '/Admin/Group/delete'
    );
    
    public $perpage = 20;
    
    /**
     * 
     * Enter description here ...
     */
    public function indexAction() {
        $name = $this->getInput('name');
		$page = intval($this->getInput('page'));
        $params = array();
        if($name){
            $params['name']= array('like', $name);
        }
        $params['del'] = 0;
		list($total, $groups) = Admin_Service_GroupModel::getList($page, $this->perpage, $params);
        $groups = Common::resetKey($groups, 'group_id');
        if($groups){
            foreach($groups as $key=>$group){
                $groups[$key]['user_num'] = Admin_Service_UserModel::getusernumsByGroup($group['group_id']);
                $userinfo = Admin_Service_UserModel::getUser($group['operator']);
                $groups[$key]['operator_name'] = $userinfo['username'];
            }
        }
		$this->assign('groups', $groups);
        $this->assign('total', $total);
        $this->assign('name', $name);
        $this->assign('params', $params);
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $this->actions['listUrl'] . '/?'));
    }
    
    /**
     * 
     * Enter description here ...
     */
    public function editAction() {
    	$groupId = $this->getInput('group_id');
    	$groupInfo = Admin_Service_GroupModel::getGroup($groupId);
    	$menuService = new Common_Service_Menu(Common::getConfig("siteConfig", "adminMainMenu"), 0);
    	$level = $menuService->getAllMainLevels();
    	//$level = $menuService->getMainLevels();
		$this->assign('level', $level);
    	$this->assign('groupInfo', $groupInfo);
        $file = BASE_PATH.Common::getConfig("siteConfig", "adminMainMenu");
        list ($topModule, , $menuNameList, ,) = require $file;
        list (, $menuList) = Admin_Service_MenuConfigModel::getAll();
        $temp = array();
        foreach ($menuList as $key => $val) {
            $temp['_'.$val['menu_id']][] = array('id'=>$val['id'],   
                'name' => $val['name']
            );
        }
        foreach($level as $key => $val){
            $topModule[$val['parent']]['items'][$key] = $val;
        }
        $this->assign('levels', $topModule);
        $this->assign('menuList', $temp);
    	
    }
    
    public function viewAction() {
    	$groupId = $this->getInput('group_id');
    	$groupInfo = Admin_Service_GroupModel::getGroup($groupId);
    	$menuService = new Common_Service_Menu(Common::getConfig("siteConfig", "adminMainMenu"), 0);
    	$level = $menuService->getAllMainLevels();
    	//$level = $menuService->getMainLevels();
		$this->assign('level', $level);
    	$this->assign('groupInfo', $groupInfo);

        $file = BASE_PATH.Common::getConfig("siteConfig", "adminMainMenu");
        list ($topModule, , $menuNameList, ,) = require $file;
        list (, $menuList) = Admin_Service_MenuConfigModel::getAll();
        $temp = array();
        foreach ($menuList as $key => $val) {
            $temp['_'.$val['menu_id']][] = array('id'=>$val['id'],   
                'name' => $val['name']
            );
        }
        $this->assign('menuList', $temp);
    	
    }
    
    /**
     * 
     * Enter description here ...
     */
    public function editPostAction() {
    	$info = $this->getPost(array('name','descrip','menu_config','group_id','menu_right'));
        $groupInfo = Admin_Service_GroupModel::getBy(array('group_id'=>$info['group_id']));
        if(empty($groupInfo)){
            $this->sonaOutput(-1, '权限套餐不存在');
        }
        $info['operator'] = $this->userInfo['user_id'];
        $data = $this->filldata($info);
		$result = Admin_Service_GroupModel::updateGroup($data, $info['group_id']);
		if (!$result) $this->output(-1, '修改失败.');
		$this->output(0, '修改成功.');
    }
    
    private function filldata($info){
        $tmp = array();
        if($info['menu_right']){
            foreach ($info['menu_right'] as $val){
                if($val){
                    foreach ($val as $key=>$va){
                        $tmp[$key] = $va;
                    }
                }
            }
        }
        $info['menu_right'] = $tmp;
        # 顶级模块
        $file = BASE_PATH.Common::getConfig("siteConfig", "adminMainMenu");
        list ($topModule, $config, , ,) = require $file;
        $moduleArr = array_keys($info['menu_config']);
        $topModuleConfig = array();
        foreach($moduleArr as $moduleVal){
            $moduleKey = substr($moduleVal, strpos($moduleVal,'/')+1);
            if(isset($config[$moduleKey])){
                $topModuleConfig[$config[$moduleKey]['parent']] = 1;
            }
        }
        $info['top_module_config'] = $topModuleConfig;
        return $info;
    }
    
    /**
     * 
     * Enter description here ...
     */
    public function addAction() {
    	$menuService = new Common_Service_Menu(Common::getConfig("siteConfig", "adminMainMenu"), 0);
    	//$level = $menuService->getMainLevels();
    	$level = $menuService->getAllMainLevels();
		$this->assign('level', $level);
 		$file = BASE_PATH.Common::getConfig("siteConfig", "adminMainMenu");
        list ($topModule, , $menuNameList, ,) = require $file;
        list (, $menuList) = Admin_Service_MenuConfigModel::getAll();
        $temp = array();
        foreach ($menuList as $key => $val) {
            $temp['_'.$val['menu_id']][] = array('id'=>$val['id'],   
                'name' => $val['name']
            );
        }
        foreach($level as $key => $val){
            $topModule[$val['parent']]['items'][$key] = $val;
        }
        $this->assign('levels', $topModule);
        $this->assign('menuList', $temp);
    }
    
    /**
     * 
     * Enter description here ...
     */
    public function addPostAction() {
    	$info = $this->getPost(array('name','descrip','menu_config','menu_right'));
		if ($info['name'] == '') $this->output(-1, '用户名不得为空.');
        $info['operator'] = $this->userInfo['user_id'];
        $params = array();
	    $params['name'] = $info['name'];
	    $params['del'] = Common_Service_Const::NOT_DELETE_FLAG;
	    $ret = Admin_Service_GroupModel::getBy($params);
	    if($ret){
	        $this->output(-1, '权限组名称不允许重名');
	    }
	    $info = $this->filldata($info);
		$groupId = Admin_Service_GroupModel::addGroup($info);
		if (!$groupId) $this->output(-1, '操作失败.');
		$this->output(0, '操作成功.');
    }
    
    /**
     * 
     * Enter description here ...
     */
    public function deleteAction() {
        $groupId = intval($this->getInput('group_id'));
        if(Admin_Service_UserModel::getusernumsByGroup($groupId) != 0){
            $this->output(-1, '该组有关联的帐号,不可删除.');
        }
        $groupInfo=Admin_Service_GroupModel::getGroup($groupId);
        $info = array();
        $info['del'] = 1;
        $info['operator'] = $this->userInfo['uid'];
        $ret = Admin_Service_GroupModel::updateGroup($info, $groupId);
    	if (!$ret) $this->output(-1, '操作失败.');
    	$this->output(0, '操作成功.');
    }
}
