<?php
ini_set("display_errors", "On");
error_reporting(E_ALL  ^ E_NOTICE);
// //session设置
/* ini_set("session.save_handler", "redis");
ini_set("session.save_path", "tcp://127.0.0.1:6379"); */
define('BASE_PATH', realpath(dirname(__DIR__)) . '/../');
define('APP_PATH', BASE_PATH . 'application/');
$app = new Yaf_Application(BASE_PATH . 'configs/application.ini');
define('ENV', $app->environ());
define("DEFAULT_MODULE", 'Api');
$response = $app->bootstrap()->run();

