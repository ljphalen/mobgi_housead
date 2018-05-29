<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
return array(
    'version' => '201805241000',
    'secretKey' => '92fe5927095eaac53cd1aa3408da8135',
    'adminMainMenu' => 'configs/adminMainMenu.php',
    'advertiserGroupPermission' => 'configs/advertiserGroupPermission.php',
    'rsaPemFile' => BASE_PATH . 'configs/rsa_private_key.pem',
    'rsaPubFile' => BASE_PATH . 'configs/rsa_public_key.pem',
    'attachPath' => BASE_PATH . 'attachs',
    'dataPath' => BASE_PATH . 'data/',
    'logPath' => BASE_PATH . 'data/logs/',
    'tmpPath' => '/data/log/housead/',
    'aaptPath' => BASE_PATH . 'data/aapt/aapt',
    'IpUrl' => 'http://ip.lua.uu.cc/',
    'sessionLifeTime' => 86400
);
