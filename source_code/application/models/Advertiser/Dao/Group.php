<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Advertiser_Dao_GroupModel
 * @author rock.luo
 *
 */
class Advertiser_Dao_GroupModel extends Common_Dao_Base {
	protected $_name = 'advertiser_group';
	protected $_primary = 'groupid';
	

	 /**
     * 格式化广告主用户权限列表
     * @return type
     */
    public function getMainLevels() {
        $advertiser_group_permission_file = Common::getConfig("siteConfig", "advertiserGroupPermission");
        $file = BASE_PATH . $advertiser_group_permission_file;
		if (is_file($file)) {
			list ($config, $view, ,) = require $file;
			if (isset($config) && is_array($config)) {
				$this->formatMenu($config, $view, '', $mainmenu);
				unset($config);
			}
		}
        return $this->mainlevels;
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
            $this->mainview[$pkey] = $item;
            $isroot && $rootkey = $pkey;
            if (! is_array($value)) {
                if (isset($this->mainlevels[$rootkey]) && ! $this->mainlevels[$rootkey]) {
                    $this->mainlevels[$rootkey] = array();
                }
                $this->mainlevels[$rootkey]['name'] = $this->mainview[$rootkey]['name'];
                if (isset($this->mainlevels[$rootkey]) && ! array_key_exists('items', $this->mainlevels[$rootkey])) {
                    $this->mainlevels[$rootkey]['items'] = array();
                }
                array_push($this->mainlevels[$rootkey]['items'], $item);
            }
            ! $isroot && $pkey = count($base);
            $base[$pkey] = $item;
            if (is_array($value) && isset($value['items'])) {
                $this->formatMenu($value['items'], $view, $app, $base[$pkey]['items'], false, $rootkey);
            }
		}
	}
}
