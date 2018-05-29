<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * 
 * @author ROCK.LUO
 *
 */
class Dsp_Domob extends Dsp_Base{
    
    /**
     * Domob支持视频
     * @param unknown $dspResponses
     * @return unknown|string
     */
   	public function formatResponses($reponse, $dspNo, $apiVersion = 'V1') {
        if ($reponse ['response'] ['code'] != '1') {
           return array();
        }
        if (! $reponse ['response'] ['ad'] ['adm']) {
              return array();
        }
        $videoStracing = json_decode ( $reponse ['ad'] ['video_trackings'], true );
        if(empty($videoStracing)){
            return array();
        }
        $outputData ['ret'] = 0;
        $outputData ['msg'] = 'ok';
        $outputData ['data'] ['outBidId'] = $reponse ['response'] ['sid'];
        $outputData ['data'] ['bidInfo'] [0] ['adType'] = $this->mAdType;
        $outputData ['data'] ['bidInfo'] [0] ['targetUrl'] = $reponse ['ad'] ['pkg_url'];
        $outputData ['data'] ['bidInfo'] [0] ['reportDataClickUrl'] = $reponse ['ad'] ['clk_trackings'] ? $reponse ['ad'] ['clk_trackings'] : array ();
        $outputData ['data'] ['bidInfo'] [0] ['reportDataShowUrl'] = $videoStracing ['start'] ? $videoStracing ['start'] : array ();
        $outputData ['data'] ['bidInfo'] [0] ['reportDataVideoEndUrl'] = $videoStracing ['finish'] ? $videoStracing ['finish'] : array ();
        $outputData ['data'] ['bidInfo'] [0] ['videoUrl'] = $reponse ['ad'] ['adm'];
        $outputData ['data'] ['bidInfo'] [0] ['jumpType'] = ($this->mPostData ['device'] ['platform'] == Common_Service_Const::ANDRIOD_PLATFORM) ? 0 : 2;
        $outputData ['data'] ['bidInfo'] [0] ['packageName'] = $reponse ['ad'] ['pkg_name'];
        $outputData ['data'] ['bidInfo'] [0] ['iconUrl'] = $reponse ['ad'] ['logo'];
        $outputData ['data'] ['bidInfo'] [0] ['htmlUrl'] = $reponse ['ad'] ['lp'];
        return $this->formatComonDspResponses($outputData,$dspNo, $apiVersion);
    }
    
    /**
     *  smaato 支持视频
     * @param unknown $thirdAppkey
     * @param unknown $thirdPosKey
     */
    public function getRequestData($thirdAppkey, $thirdPosKey) {
        // 屏幕的方向
        $screenDirection = array (
                2 => 0,
                1 => 1 
        );
        $secretKey = 'NzkyMjZsZWRvdWFkc0BnbWFpbC5jb204NWE3Yjc5YzJkNWMzOWVm';
        $postData ['request'] ['ts'] = time ();
        $postData ['request'] ['pubid'] = $thirdAppkey;
        $token = md5 ( $postData ['request'] ['ts'] . strtolower ( $this->mPostData ['device'] ['deviceId'] ) . strtolower ( $thirdAppkey ) . strtolower ( $secretKey ) );
        $postData ['request'] ['package'] = $this->mPostData ['app'] ['bundle'];
        $postData ['request'] ['token'] = $token;
        $postData ['imp'] ['adtp'] = 0;
        $postData ['imp'] ['admp'] = 1;
        $postData ['device'] ['sw'] = 640;
        $postData ['device'] ['sh'] = 1136;
        $postData ['device'] ['ori'] = $screenDirection [$this->mPostData ['device'] ['screenDirection']];
        $postData ['device'] ['ua'] = $this->mPostData ['device'] ['ua'];
        $postData ['device'] ['dt'] = 0;
        $postData ['device'] ['os'] = $this->mPostData ['device'] ['platform'];
        $postData ['device'] ['ov'] = $this->mPostData ['device'] ['version'];
        $postData ['device'] ['dm'] = $this->mPostData ['device'] ['model'];
        // $brandList = Common::getConfig ( 'deliveryConfig', 'brandList' );
        // $postData ['device']['db'] = $brandList[$this->mPostData['device']['brand']];
        $postData ['device'] ['sn'] = $this->mPostData ['device'] ['deviceId'];
        if ($this->mPostData ['device'] ['platform'] == Common_Service_Const::IOS_PLATFORM) {
            $postData ['device'] ['idfa'] = $this->mPostData ['device'] ['deviceId'];
        } else {
            $postData ['device'] ['imei'] = $this->mPostData ['device'] ['deviceId'];
            $postData ['device'] ['anid'] = $this->mPostData ['device'] ['andriodId'];
        }
        $netWorkList = Common::getConfig ( 'deliveryConfig', 'netWorkList' );
        $postData ['device'] ['nw'] = $netWorkList [$this->mPostData ['device'] ['net']];
        return $postData;
    }
    
    
}
