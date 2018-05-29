<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

//监控系统
$monitorConfig["REPORTDATA_HOST"]="127.0.0.1";
$monitorConfig["REPORTDATA_PORT"]="6969"; 
//类型
$monitorConfig["REPORTDATA_PROJECT_TYPE"]=9;
// spm监控系统
$monitorConfig["REPORTDATA_SPM_PROJECT_TYPE"] = 50;
return $monitorConfig;