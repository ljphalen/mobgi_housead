<?php
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/16
 * Time: 18:45
 */
ini_set("display_errors", "On");
error_reporting(E_ALL  ^ E_NOTICE);
// //session设置
define('BASE_PATH', realpath(dirname(__DIR__)) . '/../');
define('APP_PATH', BASE_PATH . 'application/');
$app = new Yaf_Application(BASE_PATH . 'configs/application.ini');
define('ENV', $app->environ());
define("DEFAULT_MODULE", 'Spm');
$bootstrap = $app->bootstrap();
$bootstrap->getDispatcher()->getRequest()->setRequestUri( '/Spm/shorturl/common');
$response = $bootstrap->run();