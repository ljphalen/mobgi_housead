<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * 
 * @author ROCK.LUO
 *
 */
class Dsp_Inmobi extends Dsp_Base{
    
    /**
     * smaato 支持原声广告
     * @param unknown $dspResponses
     * @return unknown|string
     */
   	public function formatResponses($dspResponse,$dspNo, $apiVersion = 'V1') {
        $dataArr = $dspResponse;
        if (empty ( $dataArr ['ads'] )) {
             return array();
        }
        $contents = json_decode ( base64_decode ( $dataArr ['ads'] [0] ['pubContent'] ), true );
        $targetUrl = $dataArr ['ads'] [0] ['landingPage'];
        $jumpType = 2;
        $reportDataClickUrl = array ();
        foreach ( $dataArr ['ads'] [0] ['eventTracking'] [8] ['urls'] as $val ) {
            $reportDataClickUrl [] = $val;
        }
        $reportDataShowUrl = array ();
        foreach ( $dataArr ['ads'] [0] ['eventTracking'] [18] ['urls'] as $val ) {
            $reportDataShowUrl [] = $val;
        }
        $outputData ['ret'] = 0;
        $outputData ['msg'] = 'ok';
        $outputData ['data'] ['outBidId'] = $dataArr ['requestId'];
        $outputData ['data'] ['bidInfo'] [0] ['adType'] = $this->mAdType;
        $outputData ['data'] ['bidInfo'] [0] ['jumpType'] = $jumpType;
        $outputData ['data'] ['bidInfo'] [0] ['targetUrl'] = $targetUrl;
        $outputData ['data'] ['bidInfo'] [0] ['iconUrl'] = $contents ['icon'] ['url'];
        $outputData ['data'] ['bidInfo'] [0] ['adName'] = $contents ['title'];
        $outputData ['data'] ['bidInfo'] [0] ['adDesc'] = $contents ['description'];
        $outputData ['data'] ['bidInfo'] [0] ['originalityDesc'] = $contents ['description'];
        $outputData ['data'] ['bidInfo'] [0] ['packageName'] = '';
        $outputData ['data'] ['bidInfo'] [0] ['actionText'] = $contents ['cta'];
        $outputData ['data'] ['bidInfo'] [0] ['reportDataClickUrl'] = $reportDataClickUrl;
        $outputData ['data'] ['bidInfo'] [0] ['reportDataShowUrl'] = $reportDataShowUrl;
        if ($this->mAdSubType == Common_Service_Const::SINGLE_ENBED_AD_SUB_TYPE) { // 单图
            $outputData ['data'] ['bidInfo'] [0] ['imgUrl'] = $contents ['screenshots'] ['url'];
            // 必须是三张图片
        } else if ($this->mAdSubType == Common_Service_Const::COMBINATION_ENBED_AD_SUB_TYPE) { // 组图
            $outputData ['data'] ['bidInfo'] [0] ['imgUrls'] [0] = $contents ['screenshots'] ['url'];
            $outputData ['data'] ['bidInfo'] [0] ['imgUrls'] [1] = $contents ['screenshots'] ['url'];
            $outputData ['data'] ['bidInfo'] [0] ['imgUrls'] [2] = $contents ['screenshots'] ['url'];
        }
        return $this->formatComonDspResponses($outputData,$dspNo, $apiVersion);
    }
    
    
    public function getRequestData($thirdAppkey, $thirdPosKey) {
        $postData ['app'] ['id'] = $thirdPosKey;
        $postData ['app'] ['bundle'] = $this->mPostData ['app'] ['bundle'];
        $postData ['imp'] ['native'] ['layout'] = 0;
        $postData ['imp'] ['secure'] = $this->isHttps () ? 1 : 0;
        $postData ['imp'] ['trackertype'] = 'url_ping';
        $postData ['imp'] ['ext'] ['ads'] = 1;
        $postData ['device'] ['ip'] = Common::getClientIP();
        if ($this->mPlatform == Common_Service_Const::IOS_PLATFORM) {
            $postData ['device'] ['ifa'] = $this->mPostData ['device'] ['deviceId'];
            $postData ['device'] ['ua'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko)Version/8.0 Mobile/12D436 Safari/600.1.4';
        } else {
            $postData ['device'] ['ua'] = 'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 5 Build/LMY48B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.93 Mobile Safari/537.36';
            $postData ['device'] ['md5_imei'] = md5 ( $this->mPostData ['device'] ['deviceId'] );
            $postData ['device'] ['sha1_imei'] = sha1 ( $this->mPostData ['device'] ['deviceId'] );
            $postData ['device'] ['o1'] = sha1 ( $this->mPostData ['device'] ['andriodId'] );
        }
        $postData ['ext'] ['responseformat'] = 'json';
        return $postData;
    }
    
    public function isHttps() {
        $serverPort =$_SERVER['SERVER_PORT'];
        if ($serverPort == '443') {
            return true;
        }
        return false;
    }
    
  
    
    
    
}
