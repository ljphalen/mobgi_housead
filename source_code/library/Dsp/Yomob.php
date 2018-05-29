<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * 
 * @author ROCK.LUO
 *
 */
class Dsp_Yomob extends Dsp_Base{

	public  $mNetType = array(
		0 =>'6',
		1 =>'1',
		2 =>'2',
		3 =>'3',
		4 =>'4',
	);

    
    /**
     * opera 视频广告
     * @param unknown $dspResponses
     * @return unknown|string
     */
   	public function formatResponses($dspResponse,$dspNo,$apiVersion = 'V1') {

   	    if(!is_array($dspResponse)|| empty($dspResponse)){
   	        return array();
   	    }
        if($dspResponse['error']){
           return array();
        }
		if(!is_array($dspResponse['creatives'])|| empty($dspResponse['creatives'])){
			return array();
		}


        $targetUrl = $dspResponse['creatives'][0]['linear']['clickThrough'] ;
       /* if(!$targetUrl){
        	return array();
		}*/
		$jumpType = 2;
        $iconUrl = '';
        $adName = $dspResponse['adTitle'];
        $packageName = '';
		$reportDataClickUrl =  array();
		$reportDataShowUrl = array();
		$reportDataVideoEndUrl = array();
		$reportDataHtmlCloseUrl = array();

		if($dspResponse['creatives'][0]['companion']['clickTracking']){
			foreach ($dspResponse['creatives'][0]['linear']['clickTracking'] as $va){
				$reportDataClickUrl[] = $va;
			}
		}

		if(empty($reportDataClickUrl)){
			return array();
		}

		foreach ($dspResponse['creatives'][0]['linear']['trackingEvents'] as $key=> $val){
			if($key=='start'){
				foreach ($val as $va){
					$reportDataShowUrl[] = $va;
				}
			}
			if($key == 'complete'){
				foreach ($val as $va){
					$reportDataVideoEndUrl[] = $va;
				}
			}

		}
		foreach ($dspResponse['creatives'][0]['companion']['trackingEvents'] as $key=> $val){
			if($key == 'start'){
				foreach ($val as $va){
					$reportDataHtmlCloseUrl[] = $va;
				}
			}
		}
        $outputData ['ret'] = 0;
        $outputData ['msg'] = 'ok';
        $outputData ['data'] ['outBidId'] = $dspResponse['id'];
        $outputData ['data'] ['bidInfo'][0] ['adType'] = $this->mAdType;
        $outputData ['data'] ['bidInfo'] [0] ['jumpType'] =$jumpType;
        $outputData ['data'] ['bidInfo'] [0] ['targetUrl'] = $targetUrl;
        $outputData ['data'] ['bidInfo'] [0] ['iconUrl'] = $iconUrl;
        $outputData ['data'] ['bidInfo'] [0]['adName'] = $adName;
		$outputData ['data'] ['bidInfo'] [0] ['packageName'] = $packageName;
        $outputData ['data'] ['bidInfo'] [0] ['adDesc'] = $adName;
        $outputData ['data'] ['bidInfo'] [0] ['reportDataClickUrl'] = $reportDataClickUrl;
        $outputData ['data'] ['bidInfo'] [0] ['reportDataShowUrl'] = $reportDataShowUrl;

		$videoUrl = urldecode($dspResponse['creatives'][0]['linear']['mediaFile']);
		if(!$videoUrl){
			return array();
		}
		//$htmlUrl = htmlentities($dspResponse['creatives'][0]['companion']['StaticResource']);
		$htmlUrl = html_entity_decode($dspResponse['creatives'][0]['companion']['StaticResource'],ENT_NOQUOTES);
		if(!$htmlUrl){
			return array();
		}
		$outputData ['data'] ['bidInfo'] [0] ['videoUrl'] =$videoUrl;
		$outputData ['data'] ['bidInfo'] [0] ['htmlUrl'] = $htmlUrl;
		$outputData ['data'] ['bidInfo'] [0] ['reportDataHtmlCloseUrl'] = $reportDataHtmlCloseUrl;
		$outputData ['data'] ['bidInfo'] [0] ['reportDataVideoEndUrl'] = $reportDataVideoEndUrl;
        return $this->formatComonDspResponses($outputData,$dspNo,$apiVersion);
    }

    public function getChannelId(){
		if($this->mPlatform == Common_Service_Const::ANDRIOD_PLATFORM){
			return '10000';
		}
		return '10006';
   	}
    
    
    public function getRequestData($thirdAppkey, $thirdPosKey) {

   		$time = common::getTime();
   		$channelId = $this->getChannelId();
		$token = md5($thirdAppkey.$channelId.$this->mPostData['app']['bundle'].$this->mAppInfo['app_name'].$this->mPostData['app']['version'].$time.$time);
        $postData ['type'] = 'json';
		$postData ['version'] = '1.0.4';
        $postData ['token'] = $token;
		$postData ['app']['appid'] = $thirdAppkey;
		$postData ['app']['channelid'] = $channelId;
		$postData ['app']['bundle'] = $this->mPostData['app']['bundle'];
		$postData ['app']['name'] = $this->mAppInfo['app_name'];
		$postData ['app']['appv'] = $this->mPostData['app']['version'];
		$postData ['device']['type'] = ($this->mPostData['device']['platform'] == Common_Service_Const::IOS_PLATFORM)?'1':'4';
		if($this->mPlatform == Common_Service_Const::IOS_PLATFORM){
			$postData ['device']['idfa']  = $this->mPostData['device']['deviceId'];
		}else{
			$postData ['device']['imei']  = $this->mPostData['device']['deviceId'];
			$postData ['device']['anid']  = $this->mPostData['device']['andriodId'];
			$postData ['device']['apilevel']  = 19;
		}
		$postData ['device']['imsi'] = $this->mPostData['device']['imsi']?$this->mPostData['device']['imsi']:'';
		$postData ['device']['mac'] = $this->mPostData['device']['mac']?strtolower(str_ireplace(':','',$this->mPostData['device']['mac'])):'';
		$postData ['device']['ip'] = Common::getClientIP();
		$postData ['device']['language'] = 'zh';
		$brandList = Common::getConfig ( 'deliveryConfig', 'brandList' );
		$postData ['device']['brand']  = is_numeric($this->mPostData['device']['brand'])?$brandList[$this->mPostData['device']['brand']]:$this->mPostData['device']['brand'];
		$postData ['device']['model'] = $this->mPostData['device']['model'];
		$postData ['device']['devicename'] = $this->mPostData['device']['model'];
		$postData ['device']['os'] = ($this->mPostData['device']['platform'] == Common_Service_Const::IOS_PLATFORM)?'0':'1';
		$postData ['device']['osversion'] = $this->mPostData['device']['version'] ;
		$postData ['device']['conntype'] = isset($this->mNetType[$this->mPostData['device']['net']])?$this->mNetType[$this->mPostData['device']['net']]:'6'; ;
		$screenArr = explode('*', $this->mPostData['device']['resolution']);
		$postData ['device']['screenwidth'] = intval($screenArr[0]);
		$postData ['device']['screenheight'] = intval($screenArr[1]);
		$postData ['device']['density'] = 0;
		$postData ['device']['orientation'] = ($this->mPostData['device']['screenDirection']==1)?2:1;
		$postData ['device']['jailbreak'] = 0;
		$postData ['device']['starttime'] = $time;
		$postData ['device']['time'] = $time;
		$postData ['device']['routemac'] = "";
		$postData ['device']['routessid'] = "";
		$postData ['device']['simstatus'] = 1;
		$postData ['user']['geography']['lat']= '';
		$postData ['user']['geography']['long']= '';
		$postData ['user']['geography']['countrycode']= 'CN';
		$postData ['user']['geography']['city']= '';
		$postData ['user']['geography']['timezone']= 'GMT+08:00';
        return $postData;
    }
    
  
    
    
    
}
