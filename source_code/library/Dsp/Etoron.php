<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * 
 * @author ROCK.LUO
 *
 */
class Dsp_Etoron  extends Dsp_Base{
    
	protected $mEtoronAdFormat = array (
			2 => 'banner',
			1 => 'video' 
	);
    
    
    /**
     * Domob支持视频
     * @param unknown $dspResponses
     * @return unknown|string
     */
   	public function formatResponses($reponse, $dspNo, $apiVersion = 'V1') {
 
        if (strtolower ( $reponse['status'] ) != 'ok') {
          return  array();
        }
        if (strtolower ( $reponse  ['ad_type'] ) != $this->mEtoronAdFormat [$this->mAdType]) {
           return  array();
        }
        $reponse = $reponse [Common_Service_Const::ETORON_DSP_ID];
        if (! $reponse [$this->mEtoronAdFormat [$this->mAdType]] ['url']) {
           return  array();
        }
        $outputData ['ret'] = 0;
        $outputData ['msg'] = 'ok';
        $outputData ['data'] ['outBidId'] = 0;
        $outputData ['data'] ['bidInfo'] [0] ['adType'] = $this->mAdType;
        $outputData ['data'] ['bidInfo'] [0] ['targetUrl'] = $reponse ['click_url'];
        $outputData ['data'] ['bidInfo'] [0] ['jumpType'] = (strtolower ( $reponse ['click_url_type'] ) == 'click') ? 2 : ($this->mPostData ['device'] ['platform'] == Common_Service_Const::ANDRIOD_PLATFORM) ? 0 : 2;
        $outputData ['data'] ['bidInfo'] [0] ['packageName'] = '';
        $outputData ['data'] ['bidInfo'] [0] ['reportDataShowUrl'] = $reponse ['impressions'] ? $reponse ['impressions'] : array ();
        if ($this->mAdType == Common_Service_Const::PIC_AD_SUB_TYPE) {
            $outputData ['data'] ['bidInfo'] [0] ['imgUrl'] = $reponse [$this->mEtoronAdFormat [$this->mAdType]] ['url'];
        } else {
            $outputData ['data'] ['bidInfo'] [0] ['videoUrl'] = $reponse [$this->mEtoronAdFormat [$this->mAdType]] ['url'];
        }
        return $this->formatComonDspResponses($outputData,$dspNo, $apiVersion);
    }
    
    /**
     *  smaato 支持视频
     * @param unknown $thirdAppkey
     * @param unknown $thirdPosKey
     */
    public function getRequestData($thirdAppkey, $thirdPosKey) {
        $referer = Common::getWebRoot ();
        if ($this->mPostData ['device'] ['platform'] == Common_Service_Const::ANDRIOD_PLATFORM) {
            return array (
                    'p' => $thirdAppkey,
                    'response_type' => 'json',
                    'ad_format' => $this->mEtoronAdFormat [$this->mAdType],
                    'api_ver' => 20,
                    'ip' => Common::getClientIP(),
                    'ua' => $this->mPostData ['device'] ['ua'],
                    'andriod_ifa' => '',
                    'dnt' => 1,
                    'andriod_id' => $this->mPostData ['device'] ['andriodId'],
                    'dev_mac' => $this->mPostData ['device'] ['mac'],
                    'dev_imei' => $this->mPostData ['device'] ['deviceId'],
                    'coppa' => 1,
                    'ref' => $referer,
                    'bundle' => $this->mPostData ['app'] ['bundle'] 
            );
        } else {
            return array (
                    'p' => $thirdAppkey,
                    'response_type' => 'json',
                    'ad_format' => $this->mEtoronAdFormat [$this->mAdType],
                    'api_ver' => 20,
                    'ip' => Common::getClientIP(),
                    'ua' => $this->mPostData ['device'] ['ua'],
                    'ios_ifa' => $this->mPostData ['device'] ['deviceId'],
                    'dnt' => 1,
                    'dev_mac' => $this->mPostData ['device'] ['mac'],
                    'coppa' => 1,
                    'ref' => $referer,
                    'bundle' => $this->mPostData ['app'] ['bundle'] 
            );
        }
    }
    
    
}
