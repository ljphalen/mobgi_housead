<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Common_Service_Menu
 * @author rock.luo
 *
 */
class Common_Service_Menu {
	
	private $mainmenu = array(); //系统树状菜单
	private $mainview = array(); //系统主视图
	private $levels = array(); //系统权限点集合
	

	private $usermenu = array(); //用户菜单
	private $userlevels = array(); //用户权限点集合
	private $usersite = array();
	
	private $mainfile = '';
	private $sitefile = '';
	
	private $approot = '';

	private $extends = array();
	
	private $noVerify = array();
	
	/**
	 * 构造函数
	 * 
	 * @param string $mainfile  
	 * @param srting $sitefile
	 * @return 
	 */
	public function __construct($mainfile, $sitefile) {
		$this->mainfile = $mainfile;
		$this->sitefile = $sitefile;
	}
	
	/**
	 * 获取用户菜单
	 * 
	 * @param Int $uid 用户id
	 * @param String $site 站点名称
	 * @return (Array)用户菜单列表 & (Array)系统视图列表 &　系统权限列表
	 */
	public function getUserMenu($groupInfo) {
		$this->getMainMenu();
		$this->usermenu = $this->mainmenu;
		$this->getLevelsByUid($groupInfo);
		return array($this->usermenu, $this->mainview, $this->usersite, $this->userlevels);
	}
	
	/**取系统权限key*/
	public function getUserLevels($groupInfo) {
		$this->getMainMenu();
		$this->usermenu = $this->mainmenu;
		$this->getLevelsByUid($groupInfo);
		foreach ($this->extends as $key => $level) {
		    if(! in_array('_' . $key, $this->userlevels)) {
		        continue;
		    }
    		foreach ($level as $menu) {
    		    $this->userlevels[] = '_' . $menu;
    		}
		}
		foreach ($this->noVerify as $menu) {
		    $this->userlevels[] = '_' . $menu;
		}
		return $this->userlevels;
	}
	
	/**
	 * 获取系统所有权限点
	 * 
	 * @return string
	 */
	public function getMainLevels() {
		$this->getMainMenu();
		return $this->levels;
	}
	
	/**
	 * 根据用户id生成用户菜单
	 * 
	 * @param Int $uid
	 */
	private function getLevelsByUid($groupInfo) {
		if ($groupInfo['group_id'] == 0) {
			$this->usersite = array_keys($this->mainmenu);
			$this->userlevels = array_keys($this->mainview);
		} else {
			list($this->usersite, $this->userlevels) = $this->formartUserLevels($groupInfo['menu_config']);
			$this->filterMenu($this->usermenu, true);
		}
	}
	
	/**
	 * 生成用户菜单
	 * 
	 * @param Array $usermenu 菜单根节点
	 * @param Bool $isroot 是否是根节点
	 * @return
	 */
	private function filterMenu(&$usermenu, $isroot) {
        if($usermenu){
            foreach ($usermenu as $key => $value) {
                if ($isroot && !in_array($key, $this->usersite)) {
                    unset($usermenu[$key]);
                    continue;
                }
                if (is_array($value['items'])) {
                    $this->filterMenu($usermenu[$key]['items'], false);
                } else {
                    if (!in_array($value['id'], $this->userlevels)) {
                        unset($usermenu[$key]);
//                        !$isroot && $usermenu = array_values($usermenu); //重建索引
                    }
                }
            }
        }
	}
	
	/**
	 * 格式化用户权限
	 * 
	 * @param Ａrray $levels
	 * @return 用户站点权限＆用户权限点
	 */
	private function formartUserLevels($levels) {
		$usersite = array_keys($levels);
		$userlevels = array();
		foreach ($levels as $key => $value) {
			$userlevels = array_merge($userlevels, array_keys($value));
		}
		return array($usersite, $userlevels);
	}
	
	/**
	 * 获取应用菜单
	 * 
	 * @param String $site 站点名称
	 * @return
	 */
	private function getSiteMenu($site) {
		$file = $this->sitefile;
		if (is_file($file)) {
			list ($topModule, $config, $view, $this->extends, $this->noVerify) = require $file;
			$this->formatMenu($config, $view, $site, $this->mainmenu);
			return;
		}
	}
	
	/**
	 * 获取Admin菜单
	 * 
	 * @return
	 */
	private function getMainMenu() {
		$file = BASE_PATH . $this->mainfile;
		if (is_file($file)) {
			list ($topModule, $config, $view, $this->extends, $this->noVerify) = require $file;
			if (isset($config) && is_array($config)) {
				$this->formatMenu($config, $view, '', $this->mainmenu);
				unset($config);
			}
		}
	}
	

	/**
	 * 格式化菜单
	 * 
	 * @param Ａrray $config 来自的菜单配置
	 * @param Ａrray $view 来息应用的菜单配置
	 * @param Ａrray $app 应用名称
	 * @param Array $base 输出
	 * @return
	 */
	private function formatMenu($config, $view, $app, &$base, $isroot = true, $rootkey = null) {
		foreach ($config as $key => $value) {
            $pkey = ! is_array($value) ? ($app . '_' . $value) : ($app . '_' . $key);
            $name = ! is_array($value) ? ($view[$value][0]) : $value['name'];
            $item = array('id' => $pkey, 'name' => $name);
            ! is_array($value) && $item['url'] = $view[$value][1];
			$isroot && $item['parent'] = $value['parent'];
            $this->mainview[$pkey] = $item;
            $isroot && $rootkey = $pkey;
            if (! is_array($value)) {
                if (isset($this->levels[$rootkey]) && ! $this->levels[$rootkey]) {
                    $this->levels[$rootkey] = array();
                }
                $this->levels[$rootkey]['name'] = $this->mainview[$rootkey]['name'];
                if (isset($this->levels[$rootkey]) && ! array_key_exists('items', $this->levels[$rootkey])) {
                    $this->levels[$rootkey]['items'] = array();
                }
                array_push($this->levels[$rootkey]['items'], $item);
            }
            ! $isroot && $pkey = count($base);
            $base[$pkey] = $item;
            if (is_array($value) && isset($value['items'])) {
               $this->formatMenu($value['items'], $view, $app, $base[$pkey]['items'], false, $rootkey);
            }
		}
	}
	
	/**
	 * 获取系统所有权限点
	 *
	 * @return string
	 */
	public function getAllMainLevels() {
		$this->getAllMainMenu();
		return $this->levels;
	}
	
	
	private function getAllMainMenu(){
		$file = BASE_PATH . $this->mainfile;
		if (is_file($file)) {
			list ($topModule, $config, $view, $this->extends, $this->noVerify) = require $file;
			if (isset($config) && is_array($config)) {
				$this->formatAllMenu($config, $view, '', $this->mainmenu);
				unset($config);
			}
		}
	}
	
	private function formatAllMenu($config, $view, $app, &$base, $isroot = true, $rootkey = null, $parentkey = null) {
		foreach ($config as $key => $value) {
            $pkey = ! is_array($value) ? ($app . '_' . $value) : ($app . '_' . $key);
            $name = ! is_array($value) ? ($view[$value][0]) : $value['name'];
            $item = array('id' => $pkey, 'name' => $name);
            ! is_array($value) && $item['url'] = $view[$value][1];
			$isroot && $item['parent'] = $value['parent'];
            $this->mainview[$pkey] = $item;
            $isroot && $rootkey = $pkey;
            if (isset($this->levels[$rootkey]) && ! $this->levels[$rootkey]) {
            	$this->levels[$rootkey] = array();
            }
            $this->levels[$rootkey]['name'] = $this->mainview[$rootkey]['name'];
			$this->levels[$rootkey]['parent'] = $this->mainview[$rootkey]['parent'];
            if (isset($this->levels[$rootkey]) && ! array_key_exists('items', $this->levels[$rootkey])) {
            	$this->levels[$rootkey]['items'] = array();
            }else{  
            	if(is_array($value) && array_key_exists('items',$value)){
            		if(!is_array($this->levels[$rootkey]['items'][$item['id']])){
            			$this->levels[$rootkey]['items'][$item['id']]['items'] = array();
            		}
            		$parentkey = $item['id'];
            		$this->levels[$rootkey]['items'][$item['id']]['name'] = $item['name'];
            		$this->levels[$rootkey]['items'][$item['id']]['id'] = $item['id'];
            	}else{
            		array_push($this->levels[$rootkey]['items'][$parentkey]['items'], $item);
            	}
            }
            ! $isroot && $pkey = count($base);
            $base[$pkey] = $item;
            if (is_array($value) && isset($value['items'])) {
               $this->formatAllMenu($value['items'], $view, $app, $base[$pkey]['items'], false, $rootkey, $parentkey);
            }
		}
	}
	
}
