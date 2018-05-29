<?php
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/10
 * Time: 16:39
 */
ini_set("display_errors", "On");
error_reporting(E_ALL  ^ E_NOTICE);
// //session设置
define('BASE_PATH', realpath(dirname(__DIR__)) . '/../');
define('APP_PATH', BASE_PATH . 'application/');
$app = new Yaf_Application(BASE_PATH . 'configs/application.ini');
define('ENV', $app->environ());
define("DEFAULT_MODULE", 'Spm');
$response = $app->bootstrap() ->run();