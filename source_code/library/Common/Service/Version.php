<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class Common_Service_Version {
	
	const VER_1_5_5 = "1.5.5";
	const VER_1_5_6 = "1.5.6";
	const VER_1_5_7 = "1.5.7";
	const VER_1_5_8 = "1.5.8";
	const VER_1_5_9 = "1.5.9";
	
	public  static $mClientVersionConfig = array(
			1 => self::VER_1_5_5,
			2 => self::VER_1_5_6,
			3 => self::VER_1_5_7,
			4 => self::VER_1_5_8,
			5 => self::VER_1_5_9);
	
	/**
	 * 用于在管理平台上显示可获得版本列表
	 * @param string $startVersion
	 * @return multitype:string
	 */
	public static function getClientVersionConfig($startVersion = null){
		if (!$startVersion) {
			return self::$mClientVersionConfig;
		}
		
		$result = array();
		foreach (self::$mClientVersionConfig as $key=>$value) {
			if (Common::isAfterVersion($value, $startVersion)) {
				$result[$key] = $value;
			}
		}
		return $result;
	}
	
	/**
	 * 获取版本名称
	 */
	public static function getClientVersion($key) {
		return self::$mClientVersionConfig[$key];
	}
}