<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-8-30 21:40:41
 * $Id: smtpConfig.php 62100 2016-8-30 21:40:41Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

$config = array(
    'test' => array(
        'mailhost'=>'smtp.idreamsky.com',
        'mailport'=>25,
        'companymail'=>'admonitor@idreamsky.com',
        'mailpasswd'=>"#7XjFwSb6Rdx",
    ),
    'product' => array(
        'mailhost'=>'smtp.idreamsky.com',
        'mailport'=>25,
        'companymail'=>'admonitor@idreamsky.com',
        'mailpasswd'=>"#7XjFwSb6Rdx",
    ),
    'develop' => array(
         'mailhost'=>'smtp.idreamsky.com',
        'mailport'=>25,
        'companymail'=>'noreply@idreamsky.com',
        'mailpasswd'=>"npnw3x46GCal",
    ),
);

return defined('ENV') ? $config[ENV] : $config['product'];
