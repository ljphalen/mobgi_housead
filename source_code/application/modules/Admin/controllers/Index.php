<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class IndexController extends Admin_BaseController {
	
	public $actions = array(
		'editpasswd' => '/Admin/User/edit',
		'logout' => '/Admin/Login/logout',
		'default' => '/Admin/Index/default',
		'getdesc' => '/Admin/Index/getdesc',
		'search' => '/Admin/Index/search',
		'passwdUrl' => '/Admin/User/passwd',
	);

	/**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {
        header ( "Content-type:text/html;charset=utf-8" );
        list ( $usermenu, $mainview, $usersite, $userlevels ) = $this->getUserMenu ();
        // 删除非Admin页面菜单
		foreach($usermenu as $key => $val){
			if($val['parent'] != 'Admin_Top_Module'){
				unset($usermenu[$key]);
			}
		}
		$module = $this->getTopModule();
		$this->assign('module', $module);
        $this->assign ( 'jsonmenu', json_encode ( $usermenu ) );
        $this->assign ( 'mainmenu', $usermenu );
        $this->assign ( 'mainview', json_encode ( array_values ( $mainview ) ) );
        $this->assign ( 'user_name', $this->userInfo ['user_name'] );
    }
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function defaultAction() {
		$this->assign('user_id', $this->userInfo['user_id']);
		$this->assign('user_name', $this->userInfo['user_name']);
		$this->assign('email', $this->userInfo['email']);
	}

	public function getRouterAction(){
		$moduleName = $this->getInput('module');
		$file = BASE_PATH.Common::getConfig("siteConfig", "adminMainMenu");
		list ($topModule, $module, $menuNameList, ,) = require $file;
		$routerConfig = array();
		if(isset($module[$moduleName])){
			foreach($module[$moduleName]['items'] as $sonModuleKey => $sonModuleVal){
				$parentId = $moduleName . '_' .$sonModuleKey;
				$routerConfig[$parentId] = array(
						'id' => $parentId,
						'name' => $sonModuleVal['name'],
						'parent_id' => '',
						'route' => $sonModuleVal['url']
				);
				foreach($sonModuleVal['items'] as $viewVal){
					if(isset($menuNameList[$viewVal]) && !empty($menuNameList[$viewVal][1])){
						$tempViewVal = '_' . $viewVal;
						$routerConfig[$tempViewVal] = array(
								'id' => $tempViewVal,
								'name' => $menuNameList[$viewVal][0],
								'parent_id' =>$parentId,
								'route' => $menuNameList[$viewVal][1]
						);
					}
				}
			}
		}
		if($this->userInfo['group_id'] != 0){
			$groupInfo = Admin_Service_GroupModel::getBy(array('group_id'=>$this->userInfo['group_id']));
			$menuConfig = json_decode($groupInfo['menu_config'],true);
			$moduleName = '_' . $moduleName;
			if(isset($menuConfig[$moduleName])){
				$tempRouterConfig = array();
				foreach($routerConfig as $routerKey => $routerVal){
					if(array_key_exists($routerKey, $menuConfig[$moduleName])){
						$tempRouterConfig[$routerKey] = $routerConfig[$routerKey];
						if(isset($routerConfig[$routerVal['parent_id']])){
							$tempRouterConfig[$routerVal['parent_id']] = $routerConfig[$routerVal['parent_id']];
						}
					}
				}
				$routerConfig = $tempRouterConfig;
			}else{
				$routerConfig = array();
			}
		}
		$routerConfig = array_values($routerConfig);
		$this->output(0, '获取成功', $routerConfig);
	}
}
