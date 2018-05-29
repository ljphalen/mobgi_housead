<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
$config = array(
    'test' => array(
        //默认是housead主库为连接
        'default' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '10.50.10.12',
            'username' => 'eric',
            'password' => 'XqfX29pXso',
            'dbname' => 'mobgi_housead',
            'displayError' => 1
        ),
        'mobgi_admin' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '10.50.10.12',
            'username' => 'eric',
            'password' => 'XqfX29pXso',
            'dbname' => 'mobgi_admin',
            'displayError' => 1
        ),
        'mobgi_api' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '10.50.10.12',
            'username' => 'eric',
            'password' => 'XqfX29pXso',
            'dbname' => 'mobgi_api',
            'displayError' => 1
        ),
        'mobgi_data' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '10.50.10.12',
            'username' => 'eric',
            'password' => 'XqfX29pXso',
            'dbname' => 'mobgi_data_new',
            'displayError' => 1
        ),
        'housead_stat' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '10.50.10.12',
            'username' => 'eric',
            'password' => 'XqfX29pXso',
            'dbname' => 'mobgi_housead_stat',
            'displayError' => 1
        ),
        'mobgi_www' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '10.50.10.12',
            'username' => 'eric',
            'password' => 'XqfX29pXso',
            'dbname' => 'mobgi_www',
            'displayError' => 1
        ),
        'mobgi_charge' => array(
                'adapter' => 'PDO_MYSQL',
                'host' => '10.50.10.12',
                'username' => 'eric',
                'password' => 'XqfX29pXso',
                'dbname' => 'mobgi_charge',
                'displayError' => 1
        ),
        'bh_stat' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '127.0.0.1:5029',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'bh_adx_stats',
            'displayError' => 1
        ),
        'mobgi_spm' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '10.50.10.12',
            'username' => 'eric',
            'password' => 'XqfX29pXso',
            'dbname' => 'mobgi_spm',
            'displayError' => 1
        ),
        'mobgi_spm_abroad' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '10.50.10.12',
            'username' => 'eric',
            'password' => 'XqfX29pXso',
            'dbname' => 'mobgi_spm_abroad',
            'displayError' => 1
        ),
        'mobgi_spm_data' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '10.50.10.12',
            'username' => 'eric',
            'password' => 'XqfX29pXso',
            'dbname' => 'mobgi_spm_data',
            'displayError' => 1
        ),
        'bh_mobgi_spm' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '10.50.10.12',
            'username' => 'eric',
            'password' => 'XqfX29pXso',
            'dbname' => 'bh_mobgi_spm',
            'displayError' => 1
        ),
        'mobgi_market' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '10.50.10.12',
            'username' => 'eric',
            'password' => 'XqfX29pXso',
            'dbname' => 'mobgi_market',
            'displayError' => 1
        ),
    ),
    'product' => array(
        'default' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => 'db.ad.api.ildyx.com',
            'username' => 'ad_system',
            'password' => 'wY7DTW6aBXV9ljG_g4sE',
            'dbname' => 'mobgi_housead',
            'displayError' => 1
        ),
        'mobgi_admin' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => 'db.ad.api.ildyx.com',
            'username' => 'ad_system',
            'password' => 'wY7DTW6aBXV9ljG_g4sE',
            'dbname' => 'mobgi_admin',
            'displayError' => 1
        ),
        'mobgi_api' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => 'db.ad.api.ildyx.com',
            'username' => 'ad_system',
            'password' => 'wY7DTW6aBXV9ljG_g4sE',
            'dbname' => 'mobgi_api',
            'displayError' => 1
        ),
        'mobgi_data' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => 'db.ad.data.ildyx.com',
            'username' => 'ad_system',
            'password' => 'wY7DTW6aBXV9ljG_g4sE',
            'dbname' => 'mobgi_data_new',
            'displayError' => 1
        ),
        'mobgi_monitor' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => 'db.ad.data.ildyx.com',
            'username' => 'ad_system',
            'password' => 'wY7DTW6aBXV9ljG_g4sE',
            'dbname' => 'mobgi_monitor',
            'displayError' => 1
        ),
        'housead_stat' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => 'db.ad.data.ildyx.com',
            'username' => 'ad_system',
            'password' => 'wY7DTW6aBXV9ljG_g4sE',
            'dbname' => 'mobgi_housead_stat',
            'displayError' => 1
        ),
        'mobgi_www' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => 'db.ad.data.ildyx.com',
            'username' => 'ad_system',
            'password' => 'wY7DTW6aBXV9ljG_g4sE',
            'dbname' => 'mobgi_www',
            'displayError' => 1
        ),
        'mobgi_charge' => array(
                'adapter' => 'PDO_MYSQL',
                'host' => 'db.ad.data.ildyx.com',
                'username' => 'ad_system',
                'password' => 'wY7DTW6aBXV9ljG_g4sE',
                'dbname' => 'mobgi_charge',
                'displayError' => 1
        ),
        'bh_stat' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '10.30.85.91:5029',
            'username' => 'ad_system',
            'password' => 'wY7DTW6aBXV9ljG_g4sE',
            'dbname' => 'bh_ad_2018',
            'displayError' => 1
        ),
        'mobgi_spm' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => 'db.ad.spm.ildyx.com',
            'username' => 'mobgi_spm',
            'password' => 'vvfjogFd3N',
            'dbname' => 'mobgi_spm',
            'displayError' => 1
        ),
        'mobgi_spm_abroad' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => 'db.ad.spm.ildyx.com',
            'username' => 'mobgi_spm',
            'password' => 'vvfjogFd3N',
            'dbname' => 'mobgi_spm_abroad',
            'displayError' => 1
        ),
        'mobgi_spm_data' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => 'db.ad.spm.ildyx.com',
            'username' => 'mobgi_spm',
            'password' => 'vvfjogFd3N',
            'dbname' => 'mobgi_spm_data',
            'displayError' => 1
        ),
        'bh_mobgi_spm' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => 'ib.ad.spm.ildyx.com:5029',
            'username' => 'ad_system',
            'password' => 'wY7DTW6aBXV9ljG_g4sE',
            'dbname' => 'mobgi_spm',
            'displayError' => 1
        ),
        'mobgi_market' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => 'db.ad.spm.ildyx.com',
            'username' => 'mobgi_spm',
            'password' => 'vvfjogFd3N',
            'dbname' => 'mobgi_market',
            'displayError' => 1
        ),
    ),
    'develop' => array(
        'default' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '192.168.141.216',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'mobgi_housead',
            'displayError' => 1
        ),
        'mobgi_admin' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '192.168.141.216',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'mobgi_admin',
            'displayError' => 1
        ),
        'mobgi_api' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '192.168.141.216',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'mobgi_api',
            'displayError' => 1
        ),
        'mobgi_data' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '192.168.141.216',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'mobgi_data_new',
            'displayError' => 1
        ),
        'mobgi_monitor' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '192.168.141.216',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'mobgi_monitor',
            'displayError' => 1
        ),
        'housead_stat' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '192.168.141.216',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'mobgi_housead_stat',
            'displayError' => 1
        ),
        'mobgi_www' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '192.168.141.216',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'mobgi_www',
            'displayError' => 1
        ),
        'mobgi_charge' => array(
                'adapter' => 'PDO_MYSQL',
                'host' => '192.168.141.216',
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'mobgi_charge',
                'displayError' => 1
        ),
         'bh_stat' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '192.168.141.216:5029',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'bh_adx_stats',
            'displayError' => 1
        ),
        'mobgi_spm' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '192.168.141.216',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'mobgi_spm',
            'displayError' => 1
        ),
        'mobgi_spm_abroad' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '192.168.141.216',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'mobgi_spm_abroad',
            'displayError' => 1
        ),
        'mobgi_spm_data' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '192.168.141.216',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'mobgi_spm_data',
            'displayError' => 1
        ),
        'bh_mobgi_spm' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '192.168.141.216',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'bh_mobgi_spm',
            'displayError' => 1
        ),
        'mobgi_market' => array(
            'adapter' => 'PDO_MYSQL',
            'host' => '192.168.141.216',
            'username' => 'root',
            'password' => '123456',
            'dbname' => 'mobgi_market',
            'displayError' => 1
        ),
    )
);
return defined('ENV') ? $config[ENV] : $config['product'];
