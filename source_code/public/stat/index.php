<?php
    ini_set("display_errors", "On");
    error_reporting(E_ALL ^ E_NOTICE);
    define('BASE_PATH', realpath(dirname(__DIR__)) . '/../');
    define('APP_PATH', BASE_PATH . 'application/');
    $app = new Yaf_Application(BASE_PATH . 'configs/application.ini');
    define('ENV', $app->environ());
    define("DEFAULT_MODULE", 'Stat');
    $response = $app->bootstrap()->run();

