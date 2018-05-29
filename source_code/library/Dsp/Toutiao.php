<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * 
 * @author ROCK.LUO
 *
 */
class Dsp_Toutiao extends Dsp_Base{
    
    /**
     * smaato 支持原声广告
     * @param unknown $dspResponses
     * @return unknown|string
     */
   	public function formatResponses($dspResponse,$dspNo,$apiVersion = 'V1') {
   	    if(!is_array($dspResponse)|| empty($dspResponse)){
   	        return array();
   	    }
        if($dspResponse['status_code'] != 20000 || empty($dspResponse['ads'])){
           return array();
        }
        $targetUrl = ($dspResponse['ads'][0]['creative']['interaction_type'] == 4)?$dspResponse['ads'][0]['creative']['download_url']:$dspResponse['ads'][0]['creative']['target_url'];
        //使用浏览器打开=2；应用内打开=3；下载应用=4
        $jumpType = 2;
        $iconUrl = $dspResponse['ads'][0]['creative']['icon'];
        $originalityDesc = $dspResponse['ads'][0]['creative']['description'];
        $adName = $dspResponse['ads'][0]['creative']['app_name'];
        $packageName = $dspResponse['ads'][0]['creative']['package_name'];
        $reportDataClickUrl =  array();
        foreach ($dspResponse['ads'][0]['creative']['click_url'] as $val){
            $reportDataClickUrl[] = $val;
        }
        $reportDataClickUrl[] = 'http://uri6.com/736bEj';
        $reportDataShowUrl = array();
        foreach ($dspResponse['ads'][0]['creative']['show_url'] as $val){
            $reportDataShowUrl[] = $val;
        }
        $actionText = $dspResponse['ads'][0]['creative']['button_text']?$dspResponse['ads'][0]['creative']['button_text']:'立即查看';
        $outputData ['ret'] = 0;
        $outputData ['msg'] = 'ok';
        $outputData ['data'] ['outBidId'] = $dspResponse['request_id'];
        $outputData ['data'] ['bidInfo'][0] ['adType'] = $this->mAdType;
        $outputData ['data'] ['bidInfo'] [0] ['jumpType'] =$jumpType;
        $outputData ['data'] ['bidInfo'] [0] ['targetUrl'] = $targetUrl;
        $outputData ['data'] ['bidInfo'] [0] ['iconUrl'] = $iconUrl;
        $outputData ['data'] ['bidInfo'] [0]['adName'] = $adName;
        $outputData ['data'] ['bidInfo'] [0] ['adDesc'] = $originalityDesc;
        $outputData ['data'] ['bidInfo'] [0] ['originalityDesc'] = $originalityDesc;
        $outputData ['data'] ['bidInfo'] [0] ['packageName'] = $packageName;
        $outputData ['data'] ['bidInfo'] [0] ['actionText'] = $actionText;
        $outputData ['data'] ['bidInfo'] [0] ['reportDataClickUrl'] = $reportDataClickUrl;
        $outputData ['data'] ['bidInfo'] [0] ['reportDataShowUrl'] = $reportDataShowUrl;
        if ($this->mAdSubType == Common_Service_Const::SINGLE_ENBED_AD_SUB_TYPE) { // 单图
            $outputData ['data'] ['bidInfo'] [0] ['imgUrl'] = $dspResponse['ads'][0]['creative']['image']['url'];
            //必须是三张图片
        } else if ($this->mAdSubType == Common_Service_Const::COMBINATION_ENBED_AD_SUB_TYPE) { // 组图
            $tmp = array();
            foreach ($dspResponse['ads'][0]['creative']['image_list'] as $val){
                $tmp[] = $val['url'];
            }
            if(count($tmp)>3){
                $tmp = array_slice($tmp, 0,3);
            }
            $outputData ['data'] ['bidInfo'] [0] ['imgUrls']=$tmp;
        }
        return $this->formatComonDspResponses($outputData,$dspNo,$apiVersion);
    }
    
    
    public function getRequestData($thirdAppkey, $thirdPosKey) {
        //屏幕的方向
        $postData ['request_id'] = $this->mBidId;
        $postData ['uid'] = '';
        $postData ['api_version'] = '1.4';
        $postData ['ip'] = Common::getClientIP();
        $postData ['ua'] = $this->mPostData['device']['ua'];
        $postData ['source_type'] = 'app';
        $postData ['app']['app_category'] = 10;
        $postData ['app']['appid'] = $thirdAppkey;
        $postData ['app']['package_name'] = $this->mPostData['app']['bundle'];
        $postData ['app']['name'] = $this->mAppInfo['app_name'];
        $postData ['app']['version'] = $this->mPostData['app']['version'];
         
        if($this->mPlatform == Common_Service_Const::IOS_PLATFORM){
            $postData ['device']['did']  = $this->mPostData['device']['deviceId'];
        }else{
            $postData ['device']['imei']  = $this->mPostData['device']['deviceId'];
            $postData ['device']['andriod_id']  = $this->mPostData['device']['andriodId'];
        }
        $postData ['device']['os'] = $this->mPostData['device']['platform'];
        $postData ['device']['os_version'] = $this->mPostData['device']['version'];
        $brandList = Common::getConfig ( 'deliveryConfig', 'brandList' );
        $postData ['device']['vendor']  = $brandList[$this->mPostData['device']['brand']];
        $postData ['device']['model'] = $this->mPostData['device']['model'];
        $postData ['device']['language'] = '中文';
        $postData ['device']['conn_type'] = intval($this->mPostData['device']['net']);
        $screenArr = explode('*', $this->mPostData['device']['resolution']);
        $postData ['device']['screen_width'] = intval($screenArr[0]);
        $postData ['device']['screen_height'] = intval($screenArr[1]);
        $postData ['adslots'][0]['id'] =$thirdPosKey;
        $postData ['adslots'][0]['adtype'] = 5;
        $postData ['adslots'][0]['pos'] = 4;
        $tmpSize = Common_Service_Const::$mEnbedSize[$this->mBlockInfo['size']];
        $tmpSizeArr = array();
        if($tmpSize){
            $tmpSizeArr = explode('*', $tmpSize);
        }
        $size[0]['width'] = $tmpSizeArr[0]?intval($tmpSizeArr[0]):1280;
        $size[0]['height'] =$tmpSizeArr[1]?intval($tmpSizeArr[1]):720;
        $postData ['adslots'][0]['accepted_size'] = $size;
        return $postData;
    }
    
  
    
    
    
}
