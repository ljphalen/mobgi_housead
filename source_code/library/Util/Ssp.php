<?php
if (!defined('BASE_PATH')) exit('Access Denied!');


class Util_Ssp {
	//ssp的id
	const PROVIDER_ID_FOR_MOBGI = 1;
	//4399ssp_id
	const PROVIDER_ID_FOR_4399 = '201804030001';



	public static $secretList = array(
			self::PROVIDER_ID_FOR_4399=>'201804030001'
	);


	public static function getSign($data,$sspId=self::PROVIDER_ID_FOR_4399){
		if(!is_array($data)){
			return '';
		}
		unset($data['sign']);
		ksort($data,SORT_STRING);
		$str = implode('',$data);
		$sign = md5($str.self::$secretList[$sspId]);
		return $sign;

	}
	

}