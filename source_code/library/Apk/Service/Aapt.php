<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * games api
 * @author lichanghua
 *
 */
class Apk_Service_Aapt extends Common_Service_Base{
	
	/**
	 * 获取apk包信息
	 * @param apk file full path $file
	 * @return boolean|array()
	 */
	public static function info($file) {
		if (!$file) return false;
		if (strpos($file, ".apk") === false) return false;
		if (!file_exists($file)) return false;
		$cmd = self::getCmd($file);
		exec($cmd, $output, $return);
		if ($return !=0) return false;
		$output = implode("\n", $output);
		
		$info = array();
		$pattern_name = "/application: label='(.*)'/isU";
		preg_match($pattern_name, $output, $m);
		$info['lable']=$m[1];
		
		#内部名称,软件唯一的
		$pattern_sys_name = "/package: name='(.*)'/isU";
		preg_match($pattern_sys_name, $output, $m);
		$info['package']=$m[1];

		#内部版本名称,用于检查升级
		$pattern_version_code = "/versionCode='(.*)'/isU";
		preg_match($pattern_version_code, $output, $m);
		$info['version_code']=$m[1];

		#对外显示的版本名称
		$pattern_version = "/versionName='(.*)'/isU";
		preg_match($pattern_version, $output, $m);
		$info['version']=$m[1];
		return $info;
	}
	
	/**
	 * get command 
	 * @param apk full path $file
	 */
	private static function getCmd($file) {
		//$cmdfile = Common::getConfig('siteConfig', 'aaptPath');
		$cmdfile = BASE_PATH . 'library/Apk/Cmd/aapt';
		return escapeshellcmd(sprintf("%s d badging %s", $cmdfile, realpath($file)));
	}
}