<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-11-22 14:27:15
 * $Id: Adappkeyconfig.php 62100 2016-11-22 14:27:15Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_AdAppkeyConfigModel{
    
    const APPKEY_CONFIGID_FOR_ANDROID = 1;
    const APPKEY_CONFIGID_FOR_IOS = 2;
    //插页边框 纯色
    const BODER_TYPE_COLOR = 0;
    //插页边框 图片
    const BODER_TYPE_PIC = 1;


	public static function getStrategyConfig($appKey,$platform)
	{
		$configId = Advertiser_Service_AppkeyConfigModel::getAppkeyconfigidByappkey($appKey);
		// 新增默认配置.当后台没有配置指定的策略配置时,默认使用请求的平台的默认配置.
		if (empty ($configId)) {
			$configId = self::APPKEY_CONFIGID_FOR_ANDROID;
			if ($platform == Common_Service_Const::IOS_PLATFORM) {
				$configId = self::APPKEY_CONFIGID_FOR_IOS;
			}
		}
		$configRecord = Advertiser_Service_AdAppkeyConfigModel::getConfig($configId);
		if($configRecord){
			return $configRecord['config'];
		}
		return $configRecord;
	}

	public static function parseGlobalConfig($globalConfig,$adType,$field,$isString=false){
		if(!is_array($globalConfig)){
			if($isString){
				return '';
			}
			return 0;
		}
		if($isString){
			isset($globalConfig [$adType] [$field]) ? $globalConfig [$adType] [$field]:'';
		}
		return isset($globalConfig [$adType] [$field]) ? intval($globalConfig [$adType] [$field]):0;

	}

	public static function parseGlobalConfigBorder($globalConfig,$adType,$screenDirection,$attachPath){
		if(!is_array($globalConfig)){
			return '';
		}
		$border = '';
		if($adType == Common_Service_Const::PIC_AD_SUB_TYPE){
			if ($globalConfig [$adType] ['border_type'] == self::BODER_TYPE_COLOR) {
				$border = $globalConfig [$adType] ['border'];
			} else {
				$border = ($screenDirection == Common_Service_Const::SCREEN_CROSS) ? $attachPath . $globalConfig [$adType] ['border_cross_img'] : $attachPath . $globalConfig [$adType] ['border_vertical_img'];
			}
		}elseif($adType == Common_Service_Const::CUSTOME_AD_SUB_TYPE){
			$border = ($screenDirection == Common_Service_Const::SCREEN_CROSS) ? $attachPath . $globalConfig [$adType] ['custom_border_cross_img'] : $attachPath . $globalConfig [$adType] ['custom_border_vertical_img'];
		}

		return $border;
	}

	/**
	 * @param $configId
	 * @return bool|mixed
	 *
	 */
	public static function getConfig($configId) {
	    if(!$configId){
	        return false;
	    }
		$result = self::_getDao()->get(intval($configId));
		if($result){
		    $result['config'] = json_decode($result['config'], true);
		}
		return $result;
	}

	/**
	 * @param $data
	 * @return bool|int|string
	 */
	public static function addConfig($data) {
		if (!is_array($data)) return false;
		$data['create_time'] = Common::getTime();
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		$ret = self::_getDao()->insert($data);
	    if (!$ret) return $ret;
        return self::_getDao()->getLastInsertId();
	}

	/**
	 * @param $data
	 * @param $configid
	 * @return bool|int
	 */
	public static function updateConfig($data, $configid) {
		if (!is_array($data)) return false; 
        $data['update_time'] = Common::getTime();
		$data = self::_cookData($data);
		return self::_getDao()->update($data, intval($configid));
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public static function getAllConfig() {
		return  array(self::_getDao()->count(), self::_getDao()->getAll());
	}

	/**
	 * @param int $page
	 * @param int $limit
	 * @param array $params
	 * @return array
	 */
	public static function getList($page = 1, $limit = 20, $params = array()) {
		if ($page < 1) $page = 1;
		$start = ($page -1) * $limit;
        $params['del'] = 0;
		$ret = self::_getDao()->getList(intval($start), intval($limit), $params);
		$total = self::_getDao()->count($params);
		return array($total, $ret); 
	}

	/**
	 * @param $params
	 * @param array $orderBy
	 * @return array|bool
	 */
	public static function getsBy($params, $orderBy = array('id' => 'ASC')) {
		if (!is_array($params)) return false;
		return self::_getDao()->getsBy($params, $orderBy);
	}

	/**
	 * @param $configid
	 * @return bool|int
	 */
	public static function deleteConfig($configid) {
		return self::_getDao()->	delete(intval($configid));
	}

	/**
	 * @param $data
	 * @return array
	 */
	private static function _cookData($data) {
		$tmp = array();
		if(isset($data['name'])) $tmp['name'] = $data['name'];
		if(isset($data['config'])) $tmp['config'] = json_encode($data['config']);
        if(isset($data['del'])) $tmp['del'] = $data['del'];
        if(isset($data['operator'])) $tmp['operator'] = $data['operator'];
		if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}


	/**
	 * @param array $params
	 * @param array $orderBy
	 * @return bool|mixed
	 */
	public static function getBy($params = array(),$orderBy = array('id'=>'DESC')){
	    $ret = self::_getDao()->getBy($params, $orderBy);
	   if(!$ret) return false;
	    return $ret;
	
	}
	
	/**
	 * 
	 * @return Advertiser_Dao_AdAppkeyConfigModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_AdAppkeyConfigModel");
	}
}

