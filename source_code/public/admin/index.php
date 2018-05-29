<?php
ini_set("display_errors", "On");
error_reporting(E_ALL^ E_NOTICE^ E_WARNING);
define('BASE_PATH', realpath(dirname(__DIR__)) . '/../');
define('APP_PATH', BASE_PATH . 'application/');

$appfile = 'configs/application.ini';
if(get_cfg_var('YAF_ENV')=='develop' ){
	$appfile = 'configs/application.develop.ini';
	if(file_exists(BASE_PATH.$appfile) ){
		$appfile = 'configs/application.develop.ini';
	}
}
$app = new Yaf_Application(BASE_PATH . $appfile);
define('ENV', $app->environ());
define("DEFAULT_MODULE", 'Admin');
$response = $app->bootstrap()->run();