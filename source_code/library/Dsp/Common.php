<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * 
 * @author ROCK.LUO
 *
 */
class Dsp_Common extends Dsp_Base{
    
    public function getRequestData($thirdAppkey, $thirdPosKey= NULL) {
        $postData = $this->mPostData;
        $postData ['device'] ['ip'] = Common::getClientIP();
        $postData ['bidId'] = $this->mBidId;
        $postData ['app'] ['appKey'] = $thirdAppkey;
        if($thirdPosKey){
			$postData ['imp'][0]['blockId'] = $thirdPosKey;
			if($this->mAdType == Common_Service_Const::VIDEO_AD_SUB_TYPE){
				$postData ['imp'][0]['attr']['w'] = 1280;
				$postData ['imp'][0]['attr']['h'] = 720;
			}
			if($this->mAdType == Common_Service_Const::ENBED_AD_SUB_TYPE){
				if(!isset($postData ['imp'][0]['attr'])){
					$postData ['imp'][0]['attr']['w'] = 1280;
					$postData ['imp'][0]['attr']['h'] = 720;
				}

			}

		}

        return $postData;
    }
    
    
    public function formatResponses($dspResponses, $dspNo, $apiVersion = 'V1'){
        return $this->formatComonDspResponses($dspResponses, $dspNo, $apiVersion);
    }
  
    
    
    
}
