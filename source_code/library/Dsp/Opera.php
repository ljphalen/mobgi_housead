<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * 
 * @author ROCK.LUO
 *
 */
class Dsp_Opera extends Dsp_Base{

	public  $mNetType = array(
		1 =>'WIFI',
		2 =>'CELL_2G',
		3 =>'CELL_3G',
		4 =>'CELL_4G',


	);
    
    /**
     * opera 支持原声广告  视频广告
     * @param unknown $dspResponses
     * @return unknown|string
     */
   	public function formatResponses($dspResponse,$dspNo,$apiVersion = 'V1') {
   	    if(!is_array($dspResponse)|| empty($dspResponse)){
   	        return array();
   	    }
        if($dspResponse['error_code'] != 0 || empty($dspResponse['ads'])){
           return array();
        }
        $targetUrl = $dspResponse['ads'][0]['meta_group'][0]['clk_url'] ;
        if(!$targetUrl){
        	return array();
		}
		$jumpType = 2;
        if($dspResponse['ads'][0]['meta_group'][0]['interaction_type'] == 'DOWNLOAD'){
        	if($this->mPlatform == Common_Service_Const::ANDRIOD_PLATFORM){
				$jumpType = 7;
			}else{
				$jumpType = 1;
			}

		}
        $iconUrl = $dspResponse['ads'][0]['meta_group'][0]['icon'];
        if(!$iconUrl){
			$iconUrl= $dspResponse['ads'][0]['meta_group'][0]['media'];
		}
        $originalityDesc = $dspResponse['ads'][0]['meta_group'][0]['title'];
        $adName = $dspResponse['ads'][0]['meta_group'][0]['title'];
        $packageName = $dspResponse['ads'][0]['meta_group'][0]['app_package_name'];
        $actionText = $dspResponse['ads'][0]['meta_group'][0]['advertisement']?$dspResponse['ads'][0]['meta_group'][0]['advertisement']:'立即查看';
		$reportDataClickUrl =  array();
		$reportDataShowUrl = array();
		$reportDataVideoEndUrl = array();
		$reportDataHtmlCloseUrl = array();
		foreach ($dspResponse['ads'][0]['ad_tracking'] as $val){
			if($val['tracking_event'] == 'AD_CLICK'){
				foreach ($val['tracking_url'] as $va){
					$reportDataClickUrl[] = $va;
				}
			}
			if($val['tracking_event'] == 'AD_IMPRESSION'){
				foreach ($val['tracking_url'] as $va){
					$reportDataShowUrl[] = $va;
				}
			}
			if($val['tracking_event'] == 'VIDEO_AD_END'){
				foreach ($val['tracking_url'] as $va){
					$reportDataVideoEndUrl[] = $va;
				}
			}
			if($val['tracking_event'] == 'VIDEO_LDP_CLOSE'){
				foreach ($val['tracking_url'] as $va){
					$reportDataHtmlCloseUrl[] = $va;
				}
			}

		}
		if(empty($reportDataShowUrl)){
			return array();
		}


        $outputData ['ret'] = 0;
        $outputData ['msg'] = 'ok';
        $outputData ['data'] ['outBidId'] = $dspResponse['request_id'];
        $outputData ['data'] ['bidInfo'][0] ['adType'] = $this->mAdType;
        $outputData ['data'] ['bidInfo'] [0] ['jumpType'] =$jumpType;
        $outputData ['data'] ['bidInfo'] [0] ['targetUrl'] = $targetUrl;
        $outputData ['data'] ['bidInfo'] [0] ['iconUrl'] = $iconUrl;
        $outputData ['data'] ['bidInfo'] [0]['adName'] = $adName;
		$outputData ['data'] ['bidInfo'] [0] ['packageName'] = $packageName;
        $outputData ['data'] ['bidInfo'] [0] ['adDesc'] = $originalityDesc;
        $outputData ['data'] ['bidInfo'] [0] ['reportDataClickUrl'] = $reportDataClickUrl;
        $outputData ['data'] ['bidInfo'] [0] ['reportDataShowUrl'] = $reportDataShowUrl;
        if($this->mAdType == Common_Service_Const::ENBED_AD_SUB_TYPE){
			$outputData ['data'] ['bidInfo'] [0] ['originalityDesc'] = $originalityDesc;
			$outputData ['data'] ['bidInfo'] [0] ['actionText'] = $actionText;
			if ($this->mAdSubType == Common_Service_Const::SINGLE_ENBED_AD_SUB_TYPE) { // 单图
				$outputData ['data'] ['bidInfo'] [0] ['imgUrl'] = $dspResponse['ads'][0]['meta_group'][0]['media'];
				//必须是三张图片
			} else if ($this->mAdSubType == Common_Service_Const::COMBINATION_ENBED_AD_SUB_TYPE) { // 组图
				$tmp = array();
				foreach ($dspResponse['ads'][0]['meta_group'][0]['images'] as $val){
					$tmp[] = $val['url'];
				}
				if(count($tmp)>3){
					$tmp = array_slice($tmp, 0,3);
				}
				$outputData ['data'] ['bidInfo'] [0] ['imgUrls']=$tmp;
			}
		}elseif ($this->mAdType == Common_Service_Const::VIDEO_AD_SUB_TYPE){
        	$videoUrl = $dspResponse['ads'][0]['meta_group'][0]['video'];
        	if(!$videoUrl){
        		return array();
			}
			$htmlUrl = $dspResponse['ads'][0]['meta_group'][0]['video_ldpg_html'];
			if(!$htmlUrl){
				return array();
			}
			$outputData ['data'] ['bidInfo'] [0] ['videoUrl'] =$videoUrl;
			$outputData ['data'] ['bidInfo'] [0] ['htmlUrl'] = $htmlUrl;
			$outputData ['data'] ['bidInfo'] [0] ['reportDataHtmlCloseUrl'] = $reportDataHtmlCloseUrl;
			$outputData ['data'] ['bidInfo'] [0] ['reportDataVideoEndUrl'] = $reportDataVideoEndUrl;

		}

        return $this->formatComonDspResponses($outputData,$dspNo,$apiVersion);
    }
    
    
    public function getRequestData($thirdAppkey, $thirdPosKey) {
        //屏幕的方向
        $postData ['request_id'] = $this->mBidId;
        $postData ['api_version']['major'] = 3;
		$postData ['api_version']['minor'] = 1;
		$postData ['app']['app_package_name'] = $this->mPostData['app']['bundle'];
		//$postData ['app']['name'] = $this->mAppInfo['app_name'];
		//$postData ['app']['version'] = $this->mPostData['app']['version'];

		$postData ['device']['os'] = ($this->mPostData['device']['platform'] == Common_Service_Const::IOS_PLATFORM)?'IOS':'ANDROID';
		$postData ['device']['os_version']['major'] = intval($this->mPostData['device']['version']);
		$postData ['device']['os_version']['minor'] = 0;
		if($this->mPlatform == Common_Service_Const::IOS_PLATFORM){
			$postData ['device']['idfa']  = $this->mPostData['device']['deviceId'];
		}else{
			$postData ['device']['imei']  = $this->mPostData['device']['deviceId'];
			$postData ['device']['android_id']  = $this->mPostData['device']['andriodId'];
		}
		$postData ['device']['ip'] = Common::getClientIP();
		$postData ['device']['ua'] = $this->mPostData['device']['ua']?$this->mPostData['device']['ua']:$_SERVER['HTTP_USER_AGENT'];

		$brandList = Common::getConfig ( 'deliveryConfig', 'brandList' );
		$postData ['device']['vendor']  = is_numeric($this->mPostData['device']['brand'])?$brandList[$this->mPostData['device']['brand']]:$this->mPostData['device']['brand'];
		$postData ['device']['model'] = $this->mPostData['device']['model'];
		$screenArr = explode('*', $this->mPostData['device']['resolution']);
		$postData ['device']['screen_size']['width'] = intval($screenArr[0]);
		$postData ['device']['screen_size']['height'] = intval($screenArr[1]);
		$postData ['device']['connection_type'] = isset($this->mNetType[$this->mPostData['device']['net']])?$this->mNetType[$this->mPostData['device']['net']]:'UNKNOWN';

        $postData ['adslots'][0]['id'] =$thirdPosKey;
        $tmpSize = Common_Service_Const::$mEnbedSize[$this->mBlockInfo['size']];
        $tmpSizeArr = array();
        if($tmpSize){
            $tmpSizeArr = explode('*', $tmpSize);
        }
        $postData ['adslots'][0]['size']['width'] = $tmpSizeArr[0]?intval($tmpSizeArr[0]):1280;
		$postData ['adslots'][0]['size']['height'] = $tmpSizeArr[1]?intval($tmpSizeArr[1]):720;
		$postData ['adslots'][0]['count'] = 1;
        return $postData;
    }
    
  
    
    
    
}
