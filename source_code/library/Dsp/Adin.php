<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * @author ROCK.LUO
 *
 */
class Dsp_Adin extends Dsp_Base
{

	public $mNetType = array(
		1 => 'WIFI',
		2 => 'CELL_2G',
		3 => 'CELL_3G',
		4 => 'CELL_4G',


	);

	/**
	 * opera 支持原声广告
	 * @param unknown $dspResponses
	 * @return unknown|string
	 */
	public function formatResponses($dspResponse, $dspNo, $apiVersion = 'V1')
	{

		if (!is_array($dspResponse) || empty($dspResponse)) {
			return array();
		}
		if ($dspResponse['err_code'] == -1 || empty($dspResponse['ad'])) {
			return array();
		}
		$targetUrl = $dspResponse['ad'][0]['url'];
		if(empty($targetUrl)){
			return array();
		}
		$jumpType = 2;
		if ($dspResponse['ad'][0]['action'] == 0) {
			if ($this->mPlatform == Common_Service_Const::ANDRIOD_PLATFORM) {
				$jumpType = 7;
			} else {
				$jumpType = 1;
			}
		}
		$iconUrl = $dspResponse['ad'][0]['app']['icon_url'];
		if ($iconUrl) {
			$iconUrl =  $dspResponse['ad'][0]['app']['icon_url'];
		}
		$originalityDesc = $dspResponse['ad'][0]['desc'];
		$adName = $dspResponse['ad'][0]['title'];
		$packageName = $dspResponse['ad'][0]['app']['package_name'];
		if($this->mAdType == Common_Service_Const::ENBED_AD_SUB_TYPE){
			$actionText = '立即查看';

		}
		$reportDataClickUrl = array();
		$reportDataShowUrl = array();
		$deepLink = '';
		if($dspResponse['ad'][0]['clk']){
			foreach ($dspResponse['ad'][0]['clk'] as $val) {
				$reportDataClickUrl[] = $val;

			}
		}
		if($dspResponse['ad'][0]['imp']){
			foreach ($dspResponse['ad'][0]['imp'] as $val) {
				if(is_array($val)){
					foreach($val as $va){
						$reportDataShowUrl[] = $va;
					}
				}else{
					$reportDataShowUrl[] = $val;
				}

			}
		}
		if(empty($reportDataShowUrl)){
			return array();
		}
		if(isset($dspResponse['ad'][0]['dp_url'])){
			$deepLink = $dspResponse['ad'][0]['dp_url'];
		}

		$outputData ['ret'] = 0;
		$outputData ['msg'] = 'ok';
		$outputData ['data'] ['outBidId'] = 0;
		$outputData ['data'] ['bidInfo'][0] ['adType'] = $this->mAdType;
		$outputData ['data'] ['bidInfo'] [0] ['jumpType'] = $jumpType;
		$outputData ['data'] ['bidInfo'] [0] ['targetUrl'] = $targetUrl;
		$outputData ['data'] ['bidInfo'] [0] ['iconUrl'] = $iconUrl;
		$outputData ['data'] ['bidInfo'] [0]['adName'] = $adName;
		$outputData ['data'] ['bidInfo'] [0] ['adDesc'] = $originalityDesc;
		$outputData ['data'] ['bidInfo'] [0] ['packageName'] = $packageName;
		if($this->mAdType == Common_Service_Const::ENBED_AD_SUB_TYPE){
			$outputData ['data'] ['bidInfo'] [0] ['originalityDesc'] = $originalityDesc;
			$outputData ['data'] ['bidInfo'] [0] ['actionText'] = $actionText;
		}
		$outputData ['data'] ['bidInfo'] [0] ['reportDataClickUrl'] = $reportDataClickUrl;
		$outputData ['data'] ['bidInfo'] [0] ['reportDataShowUrl'] = $reportDataShowUrl;
		$outputData ['data'] ['bidInfo'] [0] ['imgUrl'] = $dspResponse['ad'][0]['src'];
		$outputData ['data'] ['bidInfo'] [0] ['deepLink'] = $deepLink;
		return $this->formatComonDspResponses($outputData, $dspNo, $apiVersion);
	}


	public function getRequestData($thirdAppkey, $thirdPosKey)
	{

		$requestData['ip'] = Common::getClientIP();
		$requestData ['ua'] = $this->mPostData['device']['ua']?$this->mPostData['device']['ua']:$_SERVER['HTTP_USER_AGENT'];
		$requestData ['si'] = $thirdPosKey;
		$requestData ['reqid'] = $this->mBidId;

		if ($this->mPlatform == Common_Service_Const::IOS_PLATFORM) {
			$device['udid'] = $this->mPostData['device']['deviceId'];
			$device['identify_type'] = 'idfa';
		} else {
			$device ['udid'] = $this->mPostData['device']['deviceId'];
			$device['identify_type'] = 'imei';
			$device['android_id'] = $this->mPostData['device']['andriodId'];
		}
		//$device['mac'] = $this->mPostData['device']['mac'];
		$brandList = Common::getConfig('deliveryConfig', 'brandList');
		$device['vendor'] =  is_numeric($this->mPostData['device']['brand'])?$brandList[$this->mPostData['device']['	brand']]:$this->mPostData['device']['brand'];
		$device['model'] =$this->mPostData['device']['model'];
		$device['os'] = $this->mPlatform;
		$device['os_version'] = $this->mPostData['device']['version'];
		$device['network'] = $this->mPostData['device']['net'];
		$device['operator'] = $this->mPostData['device']['operator'];
		$screenArr = explode('*', $this->mPostData['device']['resolution']);
		$device['width'] = intval($screenArr[0]);
		$device['height'] = intval($screenArr[1]);
		$requestData ['device'] = json_encode($device);
		$requestData['app_version']= $this->mPostData['app']['version'];
		$requestData ['v'] = '1.3.6';
		//$this->setAdtypeToCache(Common_Service_Const::ADIN_DSP_ID,$thirdPosKey);
		return $requestData;
	}




}
