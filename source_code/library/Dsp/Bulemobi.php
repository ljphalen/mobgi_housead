<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * 
 * @author ROCK.LUO
 *
 */
class Dsp_Bulemobi extends Dsp_Base{


    /**
     * opera 视频广告
     * @param unknown $dspResponses
     * @return unknown|string
     */
   	public function formatResponses($dspResponse,$dspNo,$apiVersion = 'V1') {
   	    if(!is_array($dspResponse)|| empty($dspResponse)){
   	        return array();
   	    }
        if(empty($dspResponse['ad'])||$dspResponse['ret_code']){
           return array();
        }
        $targetUrl = $dspResponse['ad'][0]['apk_download_url'] ;

        if(!$targetUrl){
        	return array();
		}
		//广告类型：1下载类 2链接类 3宣传类video_type
		if($dspResponse['ad'][0]['video_type']==1){
			$jumpType = 7;
		}else{
			$jumpType = 2;
		}
        $iconUrl = $dspResponse['ad'][0]['apk_ico_url'];
        $adName = $dspResponse['ad'][0]['apk_descript'];
        $packageName = $dspResponse['ad'][0]['apk_pkg_name'];
		$reportDataClickUrl =  array();
		$reportDataShowUrl = array();
		$reportDataVideoEndUrl = array();
		$reportDataHtmlCloseUrl = array();
		foreach ($dspResponse['ad'][0]['tracker'] as $key=> $val){
			if($key=='page_click_trackers'||$key=='page_down_trackers'){
				foreach ($val as $va){
					$reportDataClickUrl[] = $va;
				}
			}
			if($key=='play_start_trackers'){
				foreach ($val as $va){
					$reportDataShowUrl[] = $va;
				}
			}
			if($key == 'play_end_trackers'){
				foreach ($val as $va){
					$reportDataVideoEndUrl[] = $va;
				}
			}

		}
		if(empty($reportDataClickUrl)){
			return array();
		}


        $outputData ['ret'] = 0;
        $outputData ['msg'] = 'ok';
        $outputData ['data'] ['outBidId'] = '0';
        $outputData ['data'] ['bidInfo'][0] ['adType'] = $this->mAdType;
        $outputData ['data'] ['bidInfo'] [0] ['jumpType'] =$jumpType;
        $outputData ['data'] ['bidInfo'] [0] ['targetUrl'] = $targetUrl;
        $outputData ['data'] ['bidInfo'] [0] ['iconUrl'] = $iconUrl;
        $outputData ['data'] ['bidInfo'] [0]['adName'] = $adName;
		$outputData ['data'] ['bidInfo'] [0] ['packageName'] = $packageName;
        $outputData ['data'] ['bidInfo'] [0] ['adDesc'] = $adName;
        $outputData ['data'] ['bidInfo'] [0] ['reportDataClickUrl'] = $reportDataClickUrl;
        $outputData ['data'] ['bidInfo'] [0] ['reportDataShowUrl'] = $reportDataShowUrl;
		$videoUrl = urldecode($dspResponse['ad'][0]['video_download_url']);

		if(!$videoUrl){
			return array();
		}
		//$htmlUrl = htmlentities($dspResponse['creatives'][0]['companion']['StaticResource']);
		$htmlUrl = base64_decode($dspResponse['ad'][0]['video_page']);
		if(!$htmlUrl){
			return array();
		}
		$outputData ['data'] ['bidInfo'] [0] ['videoUrl'] =$videoUrl;
		$outputData ['data'] ['bidInfo'] [0] ['htmlUrl'] = $htmlUrl;
		$outputData ['data'] ['bidInfo'] [0] ['reportDataHtmlCloseUrl'] = $reportDataHtmlCloseUrl;
		$outputData ['data'] ['bidInfo'] [0] ['reportDataVideoEndUrl'] = $reportDataVideoEndUrl;
		$this->setSortNum($dspResponse['ad'][0]['sortnum']);

        return $this->formatComonDspResponses($outputData,$dspNo,$apiVersion);
    }

    public function getSysVersion(){
   		$versionList = array(
   			'8.0'=>26,
   			'7.1'=>25,
   			'7.0'=>24,
			'6.0'=>23,
			'5.1'=>22,
			'5.0'=>22,
			'4.4w'=>20,
			'4.4'=>19,
			'4.3'=>18,
			'4.2'=>17,
			'4.1'=>16,
			'4.0.3'=>15,
			'4.0.4'=>15,
			'4.0'=>14,
			'3.2'=>13,
			'3.1'=>12,
			'3.0'=>11,
			'2.3.3'=>10,
			'2.3.0'=>9,
			'2.2'=>8,
			'2.1'=>7,
			'2.0.1'=>6,
			'2.0'=>5
		);
		$version = explode('.',$this->mPostData['device']['version']);
		if(in_array($version[0],array(8,6))){
			return $versionList[$version[0].'.0'];
		}elseif(in_array($version[0],array(7,5,3))){
			if(isset($versionList[$version[0].'.'.$version[1]])){
				return $versionList[$version[0].'.'.$version[1]];
			}
			return $versionList[$version[0].'.0'];
		}elseif(in_array($version[0],array(4,2))) {
			if (isset($version[2]) && isset($versionList[$version[0] . '.' . $version[1] . '.' . $version[2]])) {
				return $versionList[$version[0] . '.' . $version[1] . '.' . $version[2]];
			}
			if (isset($versionList[$version[0] . '.' . $version[1]])) {
				return $versionList[$version[0] . '.' . $version[1]];
			}
			return $versionList[$version[0] . '.0'];
		}else{
			return 27;
		}
   	}
    
    
    public function getRequestData($thirdAppkey, $thirdPosKey) {

   		$sysVersionApi = $this->getSysVersion();
		$sign = strtoupper(md5($thirdAppkey.$sysVersionApi.$this->mPostData['app']['bundle']));
        $postData ['jmediakey'] = $thirdAppkey;
		$postData ['imsi']  = $this->mPostData['device']['imsi']? $this->mPostData['device']['imsi']:'';
		$postData ['imei']  = $this->mPostData['device']['deviceId']? $this->mPostData['device']['deviceId']:'';
		$postData ['android_id']  = $this->mPostData['device']['andriodId'];
		$postData ['sys'] = $this->mPostData['device']['version'] ;
		$postData ['sdk'] = $sysVersionApi ;
		$brandList = Common::getConfig ( 'deliveryConfig', 'brandList' );
		$postData['brand']  = is_numeric($this->mPostData['device']['brand'])?$brandList[$this->mPostData['device']['brand']]:$this->mPostData['device']['brand'];
		$postData['model'] = $this->mPostData['device']['model'];
		$postData['package'] = $this->mPostData['app']['bundle'];
		$postData['channel'] = '';
		$postData['memory'] = 0;
		$postData['cpu'] = '';
		$postData['ratio'] =  $this->mPostData['device']['resolution'];
		$postData['screen_orientation'] =  ($this->mPostData['device']['screenDirection']==1)?1:0;;
		$postData['appname'] =  $this->mAppInfo['app_name'];
		$postData['sortnum'] =  $this->getSortNum();
		$postData ['ip'] = Common::getClientIP();
		$ipInfo = Util_IpToCityApi::getIpDetailInfo($postData ['ip']);
		$postData ['addr'] =  $ipInfo['province'].$ipInfo['city'];
		$postData['sign'] =  $sign;
        return array('param'=>json_encode($postData));
    }

    public function getSortNum(){
		$cache = $this->getCache();
		$key = $this->getsortNumCacheKey();
		$data = $cache->get($key);
		if($data){
			return $data;
		}
		return 0;
	}


	public function getSortNumCacheKey(){
		return 'thirdPartyMapping_sortNum_'.$this->mDspNo;
	}


	public function setSortNum($sortNum){
		$cache = $this->getCache();
		$key = $this->getsortNumCacheKey();
		$cache->set($key,intval($sortNum));
	}
    
  
    
    
    
}
