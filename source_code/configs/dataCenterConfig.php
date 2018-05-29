<?php
if (!defined('BASE_PATH')) exit('Access Denied!');


    $dataCenterConfig = array(
        'test' => array(
            'direct_url' =>'https://uts.uu.cc/usertag/ad',
        	'code'=>'15h9dkr8VBcCDGW7$kz#S7ZUvyq7hShM',
        	'version'=>1
        ),
        'product' => array(
            'direct_url' =>'https://uts.uu.cc/usertag/ad',
        	'code'=>'15h9dkr8VBcCDGW7$kz#S7ZUvyq7hShM',
        	'version'=>1
        ),
        'develop' => array(
            'direct_url'=>'https://uts.uu.cc/usertag/ad',
        	'code'=>'15h9dkr8VBcCDGW7$kz#S7ZUvyq7hShM',
        	'version'=>1
        	
        ),
    );


 return defined('ENV') ? $dataCenterConfig[ENV] : $dataCenterConfig['product'];;