<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class GroupController extends Advertiser_BaseController {
    
    public $actions = array(
        'listUrl' => '/Advertiser/Group/index',
        'addUrl' => '/Advertiser/Group/add',
    	'addPostUrl' => '/Advertiser/Group/add_post',
    	'editUrl' => '/Advertiser/Group/edit',
    	'editPostUrl' => '/Advertiser/Group/edit_post',
    	'delUrl' => '/Advertiser/Group/delete'
    );
    
    public $perpage = 20;
    
    /**
     * 
     * Enter description here ...
     */
    public function indexAction() {
		$page = intval($this->getInput('page'));
				
		list($total, $groups) = Advertiser_Service_GroupModel::getList($page, $this->perpage);
		$this->assign('groups', $groups);
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $this->actions['listUrl'] . '/?'));
    }
    
    /**
     * 
     * Enter description here ...
     */
    public function editAction() {
    	$groupid = $this->getInput('groupid');
    	$groupInfo = Advertiser_Service_GroupModel::getGroup($groupid);
    	$menuService = new Common_Service_Menu(Common::getConfig("siteConfig", "advertiser_mainMenu"), 0);
    	$level = $menuService->getMainLevels();
		$this->assign('level', $level);
    	$this->assign('groupInfo', $groupInfo);
    }
    
    /**
     * 
     * Enter description here ...
     */
    public function edit_postAction() {
    	$info = $this->getPost(array('name','descrip','rvalue','groupid'));
		$result = Advertiser_Service_GroupModel::updateGroup($info, $info['groupid']);
		if (!$result) $this->output(-1, '修改失败.');
		$this->output(0, '修改成功.');
    }
    
    /**
     * 
     * Enter description here ...
     */
    public function addAction() {
    	$menuService = new Common_Service_Menu(Common::getConfig("siteConfig", "advertiser_mainMenu"), 0);
    	$level = $menuService->getMainLevels();
		$this->assign('level', $level);
    }
    
    /**
     * 
     * Enter description here ...
     */
    public function add_postAction() {
    	$info = $this->getPost(array('name','descrip','rvalue'));
		if ($info['name'] == '') $this->output(-1, '用户名不得为空.');
		$result = Advertiser_Service_GroupModel::addGroup($info);
		if (!$result) $this->output(-1, '操作失败.');
		$this->output(0, '操作成功.');
    }
    
    /**
     * 
     * Enter description here ...
     */
    public function deleteAction() {
    	$groupid = $this->getInput('groupid');
    	$ret = Advertiser_Service_GroupModel::deleteGroup(intval($groupid));
    	if (!$ret) $this->output(-1, '操作失败.');
    	$this->output(0, '操作成功.');
    }
}
