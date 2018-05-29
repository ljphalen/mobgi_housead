<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Bootstrap extends Yaf_Bootstrap_Abstract {

    /**
     *
     * init session
     * @param Yaf_Dispatcher $dispatcher
     */
    /* 	public function _initSession(Yaf_Dispatcher $dispatcher) {
            //$lifeTime = Common::getConfig('siteConfig', 'sessionLifeTime');
            //ini_set('session.gc_maxlifetime', $lifeTime); //设置时间
            //Yaf_Session::getInstance()->start();
        } */
    /**
     *
     * init config
     */
    public function _initConfig(Yaf_Dispatcher $dispatcher) {
        $config = Yaf_Application::app()->getConfig();
        set_include_path(get_include_path() . PATH_SEPARATOR . $config->application->library);
        Yaf_Registry::set("config", $config);
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        if (!Util_Environment::isOnline()) {
            $dispatcher->registerPlugin(new UserPlugin());
        }
    }


    /**
     *
     * init route
     * @param Yaf_Dispatcher $dispatcher
     */
    public function _initRoute(Yaf_Dispatcher $dispatcher) {
        // 默认 的路由器(默认路由器是:Yaf_Router;默认路由协议是:Yaf_Rout_Static)
        $router = Yaf_Dispatcher::getInstance()->getRouter();
        if (defined('DEFAULT_MODULE') and (DEFAULT_MODULE == 'Stat' or DEFAULT_MODULE == 'Spm')) {
            $routerConfig = Common::getConfig('router' . DEFAULT_MODULE);
        } else {
            $routerConfig = Common::getConfig('router');
        }
        if ($routerConfig) {
            foreach ($routerConfig as $routekey => $route) {
                $router->addRoute($routekey, $route);
            }
        }
    }

    /**
     *
     * init default adapter
     */
    public function _initDefaultAdapter(Yaf_Dispatcher $dispatcher) {

        $config = Common::getConfig('dbConfig', 'default');
        Db_Adapter_Pdo::setDefaultAdapter($config);

        $mobgiAdminConfig = Common::getConfig('dbConfig', 'mobgi_admin');
        Db_Adapter_Pdo::registryAdapter('mobgiAdmin', $mobgiAdminConfig);

        $mobgiApiConfig = Common::getConfig('dbConfig', 'mobgi_api');
        Db_Adapter_Pdo::registryAdapter('mobgiApi', $mobgiApiConfig);

        $reportDataConfig = Common::getConfig('dbConfig', 'mobgi_data');
        Db_Adapter_Pdo::registryAdapter('reportData', $reportDataConfig);

        $houseadStatConfig = Common::getConfig('dbConfig', 'housead_stat');
        Db_Adapter_Pdo::registryAdapter('houseadStat', $houseadStatConfig);

        $mobgiWwwConfig = Common::getConfig('dbConfig', 'mobgi_www');
        Db_Adapter_Pdo::registryAdapter('mobgiWww', $mobgiWwwConfig);

        $mobgiMonitorConfig = Common::getConfig('dbConfig', 'mobgi_monitor');
        Db_Adapter_Pdo::registryAdapter('mobgiMonitor', $mobgiMonitorConfig);

        $mobgiChargeConfig = Common::getConfig('dbConfig', 'mobgi_charge');
        Db_Adapter_Pdo::registryAdapter('mobgiCharge', $mobgiChargeConfig);

        $bhStatConfig = Common::getConfig('dbConfig', 'bh_stat');
        Db_Adapter_Pdo::registryAdapter('bhStat', $bhStatConfig);

        $mobgiSpmConfig = Common::getConfig('dbConfig', 'mobgi_spm');
        Db_Adapter_Pdo::registryAdapter('mobgiSpm', $mobgiSpmConfig);

        $mobgiSpmAbroadConfig = Common::getConfig('dbConfig', 'mobgi_spm_abroad');
        Db_Adapter_Pdo::registryAdapter('mobgiSpmAbroad', $mobgiSpmAbroadConfig);

        $mobgiSpmDataConfig = Common::getConfig('dbConfig', 'mobgi_spm_data');
        Db_Adapter_Pdo::registryAdapter('mobgiSpmData', $mobgiSpmDataConfig);

        $bhMobgiSpmConfig = Common::getConfig('dbConfig', 'bh_mobgi_spm');
        Db_Adapter_Pdo::registryAdapter('bhMobgiSpm', $bhMobgiSpmConfig);

        $mobgiMarketConfig = Common::getConfig('dbConfig', 'mobgi_market');
        Db_Adapter_Pdo::registryAdapter('mobgiMarket', $mobgiMarketConfig);

    }
}
