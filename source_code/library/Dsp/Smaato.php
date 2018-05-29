<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * 
 * @author ROCK.LUO
 *
 */
class Dsp_Smaato extends Dsp_Base{
    
    /**
     * smaato 支持原声广告
     * @param unknown $dspResponses
     * @return unknown|string
     */
   	public function formatResponses($dspResponse,$dspNo, $apiVersion = 'V1') {
        /*
         * $reponseData ['ret'] = 0;
         * $reponseData ['msg'] = 'ok';
         * $reponseData ['data'] ['outBidId'] = 0;
         * $reponseData ['data'] ['bidInfo'] [0] ['adType'] = $this->mAdType;
         * $reponseData ['data'] ['bidInfo'] [0] ['targetUrl'] = 'https://www.smaato.com';
         * $reponseData ['data'] ['bidInfo'] [0] ['reportDataClickUrl'] = array (
         * "https://soma.smaato.net/clickTracking",
         * "https://soma.smaato.net/clickTracking2"
         * );
         * $reponseData ['data'] ['bidInfo'] [0] ['reportDataShowUrl'] = array (
         * "https://soma.smaato.net/videoStart"
         * );
         * $reponseData ['data'] ['bidInfo'] [0] ['reportDataVideoEndUrl'] = array (
         * "https://soma.smaato.net/complete",
         * "https://soma.smaato.net/complete2"
         * );
         * $reponseData ['data'] ['bidInfo'] [0] ['videoUrl'] = 'https://smt-demofiles.s3.amazonaws.com/creatives/assets/video/Outstream Demo Asset Mockup.mp4?test=true';
         * $reponseData ['data'] ['bidInfo'] [0] ['jumpType'] = 2;
         * $reponseData ['data'] ['bidInfo'] [0] ['packageName'] = '';
         * $reponseData ['data'] ['bidInfo'] [0] ['iconUrl'] = '';
         * $reponseData ['data'] ['bidInfo'] [0] ['htmlUrl'] = '<html><head><title>ok</title></head><body><a href="https://www.smaato.com/resources/adformats/#section1" target="_blank"><img src="https://smt-demofiles.s3.amazonaws.com/creatives/assets/images/600x500.png" alt="" /></a></body></html>';
         * return $this->formatComonDspResponses($reponseData,$dspNo);
         */
        $xmlData = $dspResponse;
        if (! $xmlData) {
            return array();
        }
        $dataArr = Util_XML2Array::createArray ( $xmlData );
        if (!isset ( $dataArr ['VAST'] ['Ad'] )) {
             return array();
        }
        $targetUrl = $dataArr ['VAST'] ['Ad'] ['InLine'] ['Creatives'] ['Creative'] [0] ['Linear'] ['VideoClicks'] ['ClickThrough'] ['@cdata'];
        if(isset($dataArr ['VAST'] ['Ad'] ['InLine'] ['Creatives'] ['Creative'] [1] ['CompanionAds'] ['Companion'] ['StaticResource'])){
            $htmlUrl = $dataArr ['VAST'] ['Ad'] ['InLine'] ['Creatives'] ['Creative'] [1] ['CompanionAds'] ['Companion'] ['StaticResource'] ['@cdata'];
        }
        if(isset($dataArr ['VAST'] ['Ad'] ['InLine'] ['Creatives'] ['Creative'] [1] ['CompanionAds'] ['Companion'] ['HTMLResource'])){
            $htmlUrl = $dataArr ['VAST'] ['Ad'] ['InLine'] ['Creatives'] ['Creative'] [1] ['CompanionAds'] ['Companion'] ['HTMLResource'] ['@cdata'];
        }
       if(isset($dataArr ['VAST'] ['Ad'] ['InLine'] ['Creatives'] ['Creative'] [0] ['Linear'] ['MediaFiles'] ['MediaFile'] ['@value'])){
           $videoUrl = $dataArr ['VAST'] ['Ad'] ['InLine'] ['Creatives'] ['Creative'] [0] ['Linear'] ['MediaFiles'] ['MediaFile'] ['@value'];
       }
       if(isset($dataArr ['VAST'] ['Ad'] ['InLine'] ['Creatives'] ['Creative'] [0] ['Linear'] ['MediaFiles'] ['MediaFile'] ['@cdata'])){
           $videoUrl = $dataArr ['VAST'] ['Ad'] ['InLine'] ['Creatives'] ['Creative'] [0] ['Linear'] ['MediaFiles'] ['MediaFile'] ['@cdata'];
       }
        
        if (empty ( $videoUrl ) || empty ( $htmlUrl )) {
            return array();
        }
        $tmp = explode ( '?', $videoUrl );
        if ($tmp) {
            $videoUrl = $tmp [0];
        }
        $ClickTracking = $dataArr ['VAST'] ['Ad'] ['InLine'] ['Creatives'] ['Creative'] [0] ['Linear'] ['VideoClicks'] ['ClickTracking'];
        $reportDataClickUrl = array ();
        if ($ClickTracking) {
            foreach ( $ClickTracking as $val ) {
                if(isset($val['@cdata'])){
                    $reportDataClickUrl [] = $val['@cdata'];
                }else{
                    $reportDataClickUrl [] = $val;
                }
            }
        }
        $impression = $dataArr ['VAST'] ['Ad'] ['InLine']['Impression'];
        $reportDataShowUrl = array ();
        if ($impression) {
            foreach ( $impression as $val ) {
                if(isset($val['@cdata'])){
                    $reportDataShowUrl [] = $val['@cdata'];
                }else{
                    $reportDataShowUrl [] = $val;
                }
            }
        }
        $reportDataVideoEndUrl = array ();
        if ($dataArr ['VAST'] ['Ad'] ['InLine'] ['Creatives'] ['Creative'] [1] ['CompanionAds'] ['Companion']['TrackingEvents']['Tracking'] ['@cdata']) {
            array_push ( $reportDataVideoEndUrl, $dataArr ['VAST'] ['Ad'] ['InLine'] ['Creatives'] ['Creative'] [1] ['CompanionAds'] ['Companion']['TrackingEvents']['Tracking'] ['@cdata'] );
        }
        if ($dataArr ['VAST'] ['Ad'] ['InLine'] ['Creatives'] ['Creative'] [1] ['CompanionAds'] ['Companion']['TrackingEvents']['Tracking'] ['@value']) {
            array_push ( $reportDataVideoEndUrl, $dataArr ['VAST'] ['Ad'] ['InLine'] ['Creatives'] ['Creative'] [1] ['CompanionAds'] ['Companion']['TrackingEvents']['Tracking'] ['@value'] );
        }
        $reponseData ['ret'] = 0;
        $reponseData ['msg'] = 'ok';
        $reponseData ['data'] ['outBidId'] = 0;
        $reponseData ['data'] ['bidInfo'] [0] ['adType'] = $this->mAdType;
        $reponseData ['data'] ['bidInfo'] [0] ['targetUrl'] = $targetUrl;
        $reponseData ['data'] ['bidInfo'] [0] ['reportDataClickUrl'] = $reportDataClickUrl;
        $reponseData ['data'] ['bidInfo'] [0] ['reportDataShowUrl'] = $reportDataShowUrl;
        $reponseData ['data'] ['bidInfo'] [0] ['reportDataVideoEndUrl'] = $reportDataVideoEndUrl;
        $reponseData ['data'] ['bidInfo'] [0] ['videoUrl'] = $videoUrl;
        $reponseData ['data'] ['bidInfo'] [0] ['jumpType'] = 2;
        $reponseData ['data'] ['bidInfo'] [0] ['packageName'] = '';
        $reponseData ['data'] ['bidInfo'] [0] ['iconUrl'] = '';
        $reponseData ['data'] ['bidInfo'] [0] ['htmlUrl'] = "<html><head><title>ok</title></head><body>" . $htmlUrl . "</body></html>";
        return $this->formatComonDspResponses($reponseData,$dspNo, $apiVersion);
    }
    
    /**
     *  smaato 支持视频
     * @param unknown $thirdAppkey
     * @param unknown $thirdPosKey
     */
    public function getRequestData($thirdAppkey, $thirdPosKey) {
        // http://soma.smaato.net/oapi/reqAd.jsp?adspace=130321855&pub=1100029957&devip=123.45.67.89&device=1&format=vast&response=xml&vastver=2
        //http://soma.smaato.net/oapi/reqAd.jsp?adspace=130321855&pub=1100029957&devip=183.14.28.210&device=1&format=video&response=xml&vastver=2&iosadid=1D76F5D1-1983-47C8-B18D-119D52E4597A&iosadtracking=true&videotype=rewarded&apiver=502&dimension=mma
        if ($this->mPostData ['device'] ['platform'] == Common_Service_Const::IOS_PLATFORM) {
            return array (
                    'adspace' => $thirdAppkey,
                    'pub' => '1100029957',
                    'devip' =>Common::getClientIP(), //'183.14.28.210',//
                    'device' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko)Version/8.0 Mobile/12D436 Safari/600.1.4',
                    'format' => 'video',
                    'response' => 'xml',
                    'vastver' => 2,
                    'iosadid'=> $this->mPostData ['device'] ['deviceId'],
                    'iosadtracking'=>true,
                    'videotype'=>'rewarded',
                    'apiver'=>'502',
                    'dimension'=>'mma'
            );
           
        } else {
            return array (
                    'adspace' => $thirdAppkey,
                    'pub' => '1100029957',
                    'devip' => Common::getClientIP(), //'183.14.28.210',
                    'device' => 'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 5 Build/LMY48B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.93 Mobile Safari/537.36',
                    'format' => 'video',
                    'response' => 'xml',
                    'vastver' => 2,
                    'googleadid'=> $this->mPostData ['device'] ['andriodId'],
                    'googlednt'=>true,
                    'videotype'=>'rewarded',
                    'apiver'=>'502',
                    'dimension'=>'mma'
            
            );
        }

    }

    
}
