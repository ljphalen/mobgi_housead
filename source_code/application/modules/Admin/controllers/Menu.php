<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class MenuController extends Admin_BaseController {
	
	public $actions = array(
		'listUrl' => '/Admin/Menu/index',
		'addUrl' => '/Admin/Menu/add',
		'addPostUrl' => '/Admin/Menu/addPost',
		'deleteUrl' => '/Admin/Menu/delete',
	    'viewUrl' => '/Admin/Menu/view',
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
	    $search= $this->getInput(array('menu_id'));
	    if ($search['menu_id']) {
	        $params['menu_id'] = array('LIKE', $search['menu_id']);
	    }
	  
	    list($total, $menuList) = Admin_Service_MenuConfigModel::getList($page, $this->perpage, $params);
	    $url = $this->actions['listUrl'].'/?' . http_build_query($search) . '&';
	    $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
	    
	    $this->assign('search', $search);
	    $this->assign('total', $total);
	    $this->assign('menuList', $menuList);
	    
	     $menuIds =  $this->getMenuIdList();
	     $this->assign('menuIds', $menuIds);
	
	}
	
	private function getMenuIdList(){
		$mainfile = Common::getConfig("siteConfig", "adminMainMenu");
		$file = BASE_PATH . $mainfile;
		list ($topModule, , $view, , ) = require $file;
		$list = array();
		foreach ($view as $key=>$val){
			$list[$key] = $val[0];
		}
		return $list;
	}
	

	
	/**
	 * 
	 * Enter description here ...
	 */
	public function addAction() {
	    $id = $this->getInput('id');
	    $this->assign('title', '添加');
	    $menuIds =  $this->getMenuIdList();
	    $this->assign('menuIds', $menuIds);
	    if($id){
	        $this->assign('title', '编辑');
	        $info = Admin_Service_MenuConfigModel::getByID($id);
	        $this->assign('info', $info);
	    }
	   
	}
	
	/**
	 * 
	 * Enter description here ...	 */
	public function addPostAction() {
		$info = $this->getPost(array('menu_id','module','controler','action','name','id'));
	     if(!trim($info['menu_id'])){
	         $this->output(-1, '菜单id不能为空');
	     }
	     if(!trim($info['module'])){
	         $this->output(-1, '模块不能为空');
	     }
	     if(!trim($info['controler'])){
	         $this->output(-1, '控制器不能为空');
	     }
	     if(!trim($info['action'])){
	         $this->output(-1, 'action不能为空');
	     }
	     if(!trim($info['name'])){
	         $this->output(-1, '名称不能为空');
	     }
	     if($info['id']){	        
	         $result = Admin_Service_MenuConfigModel::updateBy($info, array('id'=>intval($info['id'])));
	     }else{
	         $result = Admin_Service_MenuConfigModel::add($info);
	     }
		if (!$result) $this->output(-1, '操作失败');
		$this->output(0, '操作成功',array('menu_id'=>$info['']));
	}
	

	/**
	 * 
	 * Enter description here ...
	 */
	public function deleteAction() {
		$id = $this->getInput('id');
		$result = Admin_Service_MenuConfigModel::deleteById($id);
		if (!$result) $this->output(-1, '操作失败');
		$this->output(0, '操作成功');
	}
	
	/**
	 *
	 * Enter description here ...
	 */
	public function viewAction() {
	    $id = $this->getInput('id');
	    if($id){
	        $info = Admin_Service_MenuConfigModel::getByID($id);
	        $this->assign('info', $info);
	    }
	
	}
  
}
