<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-3-17 15:13:32
 * $Id: adxConfig.php 62100 2017-3-17 15:13:32Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

$config = array(
    
    'develop' => array(
        'token_expire_time'=>36000,
        
    ),
    
    'test' => array(
        'token_expire_time'=>600,
        
    ),
    
    'product' => array(
        'token_expire_time'=>1200,

    ),
    
);

return defined('ENV') ? $config[ENV] : $config['product'];

