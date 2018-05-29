<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * Class Dsp_Base
 */
abstract class Dsp_Base {
     protected  $mPostData = array();
     protected  $mAppKey  = NULL;
     protected  $mPlatform = null;
     protected  $mAdType = NULL;
     protected  $mAdSubType = 0;
     protected  $mAppInfo = array();
     protected  $mBlockInfo = array();
     protected  $mBidId = null;
     protected $mDebugInfo = null;
     protected $mDspNo = null;
     protected $mGlobalConfig = null;

     
    /**
     * 设置请求参数
     * @param array $request
     */
    public function setPostData($postData){
        $this->mPostData = $postData;
        $this->initPostParams();
    }
    
    public function initPostParams(){
        $this->mAdType = $this->mPostData['adType'];
        $this->mAppKey = $this->mPostData['app'] ['appKey'];
        $this->mPlatform = $this->mPostData['device']['platform'];
        
    }
    
    public function setAppInfo($appInfo){
        $this->mAppInfo = $appInfo;
    }
    
    public function setBlockInfo($blockInfo ){
        $this->mBlockInfo  = $blockInfo;
        $this->mAdSubType =  $this->mBlockInfo['ad_sub_type'];
        if($this->mAdSubType){
        	$this->mPostData['adSubType'] = $this->mAdSubType;
		}
        
    }
    
    public function setBidId($bidId ){
        $this->mBidId  = $bidId;
    }

    public function setDspId($dspNo){
		$this->mDspNo = $dspNo;
	}
    
    public function formatComonDspResponses($responseData, $dspId, $apiVersion = 'V1'){
        $data = array();
        if($apiVersion == 'V1'){
            $data = $this->formatComonDspResponsesV1($responseData, $dspId);
        }else if ($apiVersion == 'V2'){
            $data = $this->formatComonDspResponsesV2($responseData, $dspId);
        }
        return $data;
    }


	/**
	 * @param $responseData
	 * @param $dspId
	 * @return array
	 */
    public function formatComonDspResponsesV1($responseData, $dspId) {
        if (empty ( $responseData ['data'] ) || empty ( $responseData ['data'] ['bidInfo'] )) {
            return array();
        }
        $fixPriceInfo = MobgiApi_Service_AdsListModel::getFixPriceByDspId ( $dspId );
        $data ['ret'] = 0;
        $data ['msg'] = $responseData ['msg'];
        $data ['data'] ['bidId'] = $this->mBidId;
        $data ['data'] ['outBidId'] = $responseData ['data'] ['outBidId'];
        $bidInfoList = null;
        // 玩转互联返回的不是list特殊处理一下
        if (Common_Service_Const::UNIPLAY_DSP_ID != $dspId) {
            $bidInfoList = $responseData ['data'] ['bidInfo'];
        } else {
            $bidInfoList [] = $responseData ['data'] ['bidInfo'];
        }
		$this->mGlobalConfig = Advertiser_Service_AdAppkeyConfigModel::getStrategyConfig($this->mAppKey, $this->mPlatform);
        $priceCacheData = array();
        foreach ( $bidInfoList as $bidInfoItemKey => $bidInfoItemValue ) {
            if (! is_array ( $bidInfoItemValue ) || empty ( $bidInfoItemValue )) {
                continue;
            }
			$chargeType = $this->getChargeType($dspId, $fixPriceInfo, $bidInfoItemValue);
            $adType = $this->mAdType;
            $originalityId = (Common_Service_Const::HOUSEAD_DSP_ID == $dspId) ? strval ( $bidInfoItemValue ['originalityId'] ) : '0';
            $tmpReportDataClickUrl = ! empty ( $bidInfoItemValue ['reportDataClickUrl'] ) ? $bidInfoItemValue ['reportDataClickUrl'] : array ();
            $tmpReportDataShowUrl = ! empty ( $bidInfoItemValue ['reportDataShowUrl'] ) ? $bidInfoItemValue ['reportDataShowUrl'] : array ();
			//是否实时竞价
			list($price, $bidPrice) = $this->getDspPrice($dspId, $fixPriceInfo, $bidInfoItemValue, $chargeType);
			$reportDataClickUrl = array ();
			foreach ( $tmpReportDataClickUrl as $val ) {
				$reportDataClickUrl [] = $val;
			}
			$reportDataShowUrl = array ();
			foreach ( $tmpReportDataShowUrl as $val ) {
				$reportDataShowUrl [] = $this->priceReplaceMico($val,$bidPrice,$dspId);;
			}
			$priceCacheData[$originalityId] = array(
				'price' => $price,
				'charge_type' => $chargeType
			);
			$jumpType =  intval ($this->parseJumpType( $bidInfoItemValue ['jumpType']));
			$packageName = $bidInfoItemValue ['packageName']?$bidInfoItemValue ['packageName']:'';
			if(in_array($jumpType,array(0,7)) && !$packageName){
				$packageName = 'com.test.11';
			}


            $tmp = array (
                    'chargeType' => intval ( $chargeType ),
                    'currency' => $bidInfoItemValue ['currency'] ? $bidInfoItemValue ['currency'] : 1,
                    'price' => $price,
                    'bidPrice'=>$bidPrice,
                    'width' => $bidInfoItemValue ['width'] ? $bidInfoItemValue ['width'] : 960,
                    'height' => $bidInfoItemValue ['height'] ? $bidInfoItemValue ['height'] : 480,
                    'adId' => (Common_Service_Const::HOUSEAD_DSP_ID == $dspId) ? strval ( $bidInfoItemValue ['adId'] ) : '',
                    'adUnitId' => (Common_Service_Const::HOUSEAD_DSP_ID == $dspId) ? strval ( $bidInfoItemValue ['adUnitId'] ) : '',
                    'originalityId' => $originalityId,
                    "targetUrl" => $bidInfoItemValue ['targetUrl'] ? $bidInfoItemValue ['targetUrl'] : '', // 推广地址
                    "versionCode" => $bidInfoItemValue ['versionCode'] ? $bidInfoItemValue ['versionCode'] : '', // 推广目标版本号
                    "adType" => $adType, // 广告类型 1视频,2插页,3自定义
                    "jumpType" => $jumpType , // 跳转类型，0表示静默下载(针对安卓)，1表示跳转市场应用(ios为Appstore,安卓为GooglePlay)，2表示跳转系统默认浏览器，3表示跳转自建浏览器，4表示打开列表广告，5表示自定义动作，6表示无动作，7表示通知栏下载(针对安卓），8表示商店内页打开（IOS）。目前仅0,1,2,3,7,8有价值
                    "packageName" => $packageName, // 包名（针对安卓）
                    "adName" => $bidInfoItemValue ['adName'] ? $bidInfoItemValue ['adName'] : '', // 广告名称（针对安卓）
                    "iconUrl" => $bidInfoItemValue ['iconUrl'] ? $bidInfoItemValue ['iconUrl'] : '', // 图标地址（针对安卓）
                    "adDesc" => $bidInfoItemValue ['adDesc'] ? $bidInfoItemValue ['adDesc'] : '',
                    "reportDataClickUrl" => $reportDataClickUrl, // 第三方数据上报地址（展示、点击）
                    "reportDataShowUrl" => $reportDataShowUrl,
                    'deepLink' => $bidInfoItemValue ['deepLink'] ? $bidInfoItemValue ['deepLink'] : '' 
            );

            if ($this->mAdType == Common_Service_Const::PIC_AD_SUB_TYPE) {
                $tmp ['imgUrl'] = $bidInfoItemValue ['imgUrl'] ? $bidInfoItemValue ['imgUrl'] : '';
                $tmp ['border'] = ($dspId  == Common_Service_Const::HOUSEAD_DSP_ID) ? $bidInfoItemValue ['border'] : '';
                $tmp ['closeButtonDelayShow'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $this->mAdType, 'close_button_delay_show');
                $tmp ['closeButtonDelayShowTimes'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $this->mAdType, 'close_button_delay_show_time');
            } else if ($this->mAdType == Common_Service_Const::VIDEO_AD_SUB_TYPE) {
                $tmpReportDataHtmlCloseUrl = ! empty ( $bidInfoItemValue ['reportDataHtmlCloseUrl'] ) ? $bidInfoItemValue ['reportDataHtmlCloseUrl'] : array ();
                $tmpReportDataVideoEndUrl = ! empty ( $bidInfoItemValue ['reportDataVideoEndUrl'] ) ? $bidInfoItemValue ['reportDataVideoEndUrl'] : array ();
                $reportDataHtmlCloseUrl = array ();
                foreach ( $tmpReportDataHtmlCloseUrl as $val ) {
                    $reportDataHtmlCloseUrl [] = $val;
                }
                $reportDataVideoEndUrl = array ();
                foreach ( $tmpReportDataVideoEndUrl as $val ) {
                    $reportDataVideoEndUrl [] = $val;
                }
                $tmp ['reportDataHtmlCloseUrl'] = $reportDataHtmlCloseUrl;
                $tmp ['reportDataVideoEndUrl'] = $reportDataVideoEndUrl;
                $tmp ['videoUrl'] = $bidInfoItemValue ['videoUrl'] ? $bidInfoItemValue ['videoUrl'] : '';
                $tmp ['htmlUrl'] = $bidInfoItemValue ['htmlUrl'] ? $bidInfoItemValue ['htmlUrl'] : '';
                $tmp ['muteButton'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $this->mAdType, 'show_mute_button');
                $tmp ['closeButton'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $this->mAdType, 'show_close_button');
                $tmp ['downloadButton'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $this->mAdType, 'show_download_button');
                $tmp ['progressButton'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $this->mAdType, 'show_progress_button');
            } else if ($this->mAdType == Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
                $tmp ['appName'] = $bidInfoItemValue ['appName'] ? $bidInfoItemValue ['appName'] : '';
                $tmp ['originalityTitle'] = $bidInfoItemValue ['originalityTitle'] ? $bidInfoItemValue ['originalityTitle'] : '';
                $tmp ['originalityDesc'] = $bidInfoItemValue ['originalityDesc'] ? $bidInfoItemValue ['originalityDesc'] : '';
                $tmp ['adSubType'] =$bidInfoItemValue['adSubType']?$bidInfoItemValue['adSubType']:$this->mPostData['adSubType'];
                $tmp ['imgUrl'] = $bidInfoItemValue ['imgUrl'] ? $bidInfoItemValue ['imgUrl'] : '';
                $tmp ['border'] = $bidInfoItemValue ['border'];
                $tmp ['boutiqueLabelUrl'] = $bidInfoItemValue ['boutiqueLabelUrl'] ? $bidInfoItemValue ['boutiqueLabelUrl'] : '';
                $tmp ['closeButtonDelayShow'] = $bidInfoItemValue ['closeButtonDelayShow'] ? $bidInfoItemValue ['closeButtonDelayShow'] : 0;
                $tmp ['closeButtonDelayShowTimes'] = $bidInfoItemValue ['closeButtonDelayShowTimes'] ? $bidInfoItemValue ['closeButtonDelayShowTimes'] : 0;
                $tmp ['closeButtonUrl'] = $bidInfoItemValue ['closeButtonUrl'] ? $bidInfoItemValue ['closeButtonUrl'] : 0;
                $tmp ['playInterval'] = $bidInfoItemValue ['playInterval'] ? $bidInfoItemValue ['playInterval'] : 0;
                $tmp ['animationEffect'] = $bidInfoItemValue ['animationEffect'] ? $bidInfoItemValue ['animationEffect'] : 0;
            } else if ($this->mAdType == Common_Service_Const::SPLASH_AD_SUB_TYPE) {
                $tmp ['imgUrl'] = $bidInfoItemValue ['imgUrl'] ? $bidInfoItemValue ['imgUrl'] : '';
                $tmp ['showSkipButton'] = $bidInfoItemValue ['showSkipButton'] ? $bidInfoItemValue ['showSkipButton'] : 0;
                $tmp ['showCountdown'] = $bidInfoItemValue ['showCountdown'] ? $bidInfoItemValue ['showCountdown'] : 0;
                $tmp ['waitTime'] = $bidInfoItemValue ['waitTime'] ? $bidInfoItemValue ['waitTime'] : 0;
                $tmp ['showTime'] = $bidInfoItemValue ['showTime'] ? $bidInfoItemValue ['showTime'] : 0;
            } else if ($this->mAdType == Common_Service_Const::ENBED_AD_SUB_TYPE) {
                $tmp ['originalityDesc'] = $bidInfoItemValue ['originalityDesc']?$bidInfoItemValue ['originalityDesc']:$bidInfoItemValue ['adDesc'];
                $tmp ['score'] = $bidInfoItemValue ['score'] ? $bidInfoItemValue ['score'] : 5;
                $tmp ['actionText'] = $bidInfoItemValue ['actionText'] ? $bidInfoItemValue ['actionText'] : '立即下载';
                $tmp ['adSubType'] = $this->mAdSubType;
                if ($this->mAdSubType == Common_Service_Const::SINGLE_ENBED_AD_SUB_TYPE) { // 单图
                    $tmp ['imgUrl'] = $bidInfoItemValue ['imgUrl'];
                } else if ($this->mAdSubType == Common_Service_Const::COMBINATION_ENBED_AD_SUB_TYPE) { // 组图
                    $imgUrls = array ();
                    foreach ( $bidInfoItemValue ['imgUrls'] as $val ) {
                        $imgUrls [] = $val;
                    }
                    $tmp ['imgUrls'] = $imgUrls;
                }
            }
            // 需根据android,ios版本判断才能决定bidInfo的层次。
            if (Common::isBidInfoList ( $this->mPostData ['device'] ['platform'], $this->mPostData ['extra'] ['sdkVersion'], $this->mAdType )) {
                $data['data'] ['bidInfo'] [] = $tmp;
                // ADX广告除交叉推广广告外，其他形式每次请求均只下发一条
                if (($this->mAdType != Common_Service_Const::CUSTOME_AD_SUB_TYPE)) {
                    break;
                }
            } else {
                $data ['data'] ['bidInfo'] = $tmp;
                break;
            }
        }
        if($priceCacheData){
            //保存扣费信息到缓存
            Dedelivery_Service_OriginalityRelationModel::saveOriginalityChargePriceKeyToCache ( $this->mBidId, $priceCacheData );
        }
        return $data;
    }

	/**
	 * @param $responseData
	 * @param $dspId
	 * @return mixed
	 */
	public function formatComonDspResponsesV2($responseData, $dspId) {
        if (empty ( $responseData ['data'] ) || empty ( $responseData ['data'] ['bidInfo'] )) {
            if(Common_Service_Const::HOUSEAD_DSP_ID == $dspId){
                return array('ret'=>$responseData['ret'],'msg'=>$responseData['msg'], 'data'=>array());
            }else{
                return array('ret'=>Util_ErrorCode::DSP_RETURN_DATA_EMPTY,'msg'=>$responseData['msg'], 'data'=>array());
            }
        }


        $fixPriceInfo = MobgiApi_Service_AdsListModel::getFixPriceByDspId ( $dspId );
        $data ['ret'] = 0;
        $data ['msg'] = $responseData ['msg'];
        $data ['data'] ['dspId'] = $dspId;
        $data ['data'] ['bidId'] = $this->mBidId;
        $data ['data'] ['outBidId'] = ($this->mPostData['providerId']!=1 )?($this->mPostData['outBidId']?$this->mPostData['outBidId']:'0'	):$responseData ['data'] ['outBidId'];
        $data ['data'] ['adType'] = $this->mAdType;
        $bidInfoList = null;
        // 玩转互联返回的不是list特殊处理一下
        if (Common_Service_Const::UNIPLAY_DSP_ID != $dspId) {
            $bidInfoList = $responseData ['data'] ['bidInfo'];
        } else {
            $bidInfoList [] = $responseData ['data'] ['bidInfo'];
        }

		$this->mGlobalConfig = Advertiser_Service_AdAppkeyConfigModel::getStrategyConfig($this->mAppKey, $this->mPlatform);
        $priceCacheData = array();
        foreach ( $bidInfoList as $bidInfoItemKey => $bidInfoItemValue ) {
            if (! is_array ( $bidInfoItemValue ) || empty ( $bidInfoItemValue )) {
                continue;
            }
			$chargeType = $this->getChargeType($dspId, $fixPriceInfo, $bidInfoItemValue);
            $originalityId = (Common_Service_Const::HOUSEAD_DSP_ID == $dspId) ? strval ( $bidInfoItemValue ['originalityId'] ) : '0';
            $tmpReportDataClickUrl = ! empty ( $bidInfoItemValue ['reportDataClickUrl'] ) ? $bidInfoItemValue ['reportDataClickUrl'] : array ();
            $tmpReportDataShowUrl = ! empty ( $bidInfoItemValue ['reportDataShowUrl'] ) ? $bidInfoItemValue ['reportDataShowUrl'] : array ();

			//是否实时竞价
			list($price, $bidPrice) = $this->getDspPrice($dspId, $fixPriceInfo, $bidInfoItemValue, $chargeType);
			$priceCacheData[$originalityId] = array(
				'price' => $price,
				'charge_type' => $chargeType
			);
			$reportDataClickUrl = array ();
			foreach ( $tmpReportDataClickUrl as $val ) {
				$reportDataClickUrl [] = $this->priceReplaceMico($val,$bidPrice,$dspId);
			}

			$reportDataShowUrl = array ();
			foreach ( $tmpReportDataShowUrl as $val ) {
				$reportDataShowUrl [] = $this->priceReplaceMico($val,$bidPrice,$dspId);
			}

			$jumpType = intval ( $this->parseJumpType( $bidInfoItemValue ['jumpType']));
			$packageName = $bidInfoItemValue ['packageName']?$bidInfoItemValue ['packageName']:'';
			if(in_array($jumpType,array(0,7)) && !$packageName){
				$packageName = 'com.test.11';
			}
			$reportDataList['dspId'] = $dspId;
			$reportDataList['bidId'] = $this->mBidId;
			$reportDataList['outBidId'] = $data ['data'] ['outBidId'];
			$reportDataList['adId'] = (Common_Service_Const::HOUSEAD_DSP_ID == $dspId) ? strval ( $bidInfoItemValue ['adId'] ) : '';
			$reportDataList['originalityId'] = $originalityId;
			$reportDataList['price'] = $price;
			$reportDataList['chargeType'] = $chargeType;
			$reportDataList['currency'] = $bidInfoItemValue ['currency'] ? $bidInfoItemValue ['currency'] : 1;


			$basicInfo = array(
                "chargeType"=>intval ( $chargeType ),
                "currency"=>$bidInfoItemValue ['currency'] ? $bidInfoItemValue ['currency'] : 1,
                "price"=>$price,
                'bidPrice'=>$bidPrice,
                "adId"=>(Common_Service_Const::HOUSEAD_DSP_ID == $dspId) ? strval ( $bidInfoItemValue ['adId'] ) : '',
                "adUnitId"=>(Common_Service_Const::HOUSEAD_DSP_ID == $dspId) ? strval ( $bidInfoItemValue ['adUnitId'] ) : '',
                "originalityId"=>$originalityId,
                "adName"=>$bidInfoItemValue ['adName'] ? $bidInfoItemValue ['adName'] : '', // 广告名称（针对安卓）
                "adDesc"=>$bidInfoItemValue ['adDesc'] ? $bidInfoItemValue ['adDesc'] : ($bidInfoItemValue ['adName']?$bidInfoItemValue ['adName']:''),
                "targetUrl"=>$bidInfoItemValue ['targetUrl'] ? $bidInfoItemValue ['targetUrl'] : '', // 推广地址
                "jumpType"=>$jumpType , // 跳转类型，0表示静默下载(针对安卓)，1表示跳转市场应用(ios为Appstore,安卓为GooglePlay)，2表示跳转系统默认浏览器，3表示跳转自建浏览器，4表示打开列表广告，5表示自定义动作，6表示无动作，7表示通知栏下载(针对安卓），8表示商店内页打开（IOS）。目前仅0,1,2,3,7,8有价值
                "deepLink"=>$bidInfoItemValue ['deepLink'] ? $bidInfoItemValue ['deepLink'] : '',
                "packageName"=>$packageName, // 包名（针对安卓）
                "iconUrl"=>$bidInfoItemValue ['iconUrl'] ? $bidInfoItemValue ['iconUrl'] : '', // 图标地址（针对安卓）
            );
   
            $eventTraking = array(
                "reportDataClickUrl"=>$this->addReportUrl(Util_EventType::CLICE_EVENT_TYPE,$reportDataClickUrl,$reportDataList), // 第三方数据上报地址（展示、点击）
                "reportDataShowUrl" =>$this->addReportUrl(Util_EventType::IMPRESSIONS_EVENT_TYPE,$reportDataShowUrl,$reportDataList) ,
            );
            
            if ($this->mAdType == Common_Service_Const::VIDEO_AD_SUB_TYPE) {
                $tmpReportDataHtmlCloseUrl = ! empty ( $bidInfoItemValue ['reportDataHtmlCloseUrl'] ) ? $bidInfoItemValue ['reportDataHtmlCloseUrl'] : array ();
                $tmpReportDataVideoEndUrl = ! empty ( $bidInfoItemValue ['reportDataVideoEndUrl'] ) ? $bidInfoItemValue ['reportDataVideoEndUrl'] : array ();
                $reportDataHtmlCloseUrl = array ();
                foreach ( $tmpReportDataHtmlCloseUrl as $val ) {
                    $reportDataHtmlCloseUrl [] = $val;
                }
                $reportDataVideoEndUrl = array ();
                foreach ( $tmpReportDataVideoEndUrl as $val ) {
                    $reportDataVideoEndUrl [] = $val;
                }
                $eventTraking['reportDataHtmlCloseUrl'] = $this->addReportUrl(Util_EventType::VIDEO_PAGE_CLOSE_EVENT_TYPE,$reportDataHtmlCloseUrl,$reportDataList);
                $eventTraking['reportDataVideoEndUrl'] = $this->addReportUrl(Util_EventType::VIDEO_FINISH_EVENT_TYPE,$reportDataVideoEndUrl,$reportDataList);
                
                $extraInfo = array(
                    "videoUrl"=>$bidInfoItemValue ['videoUrl'] ? $bidInfoItemValue ['videoUrl'] : '',
                    "htmlUrl"=>$bidInfoItemValue ['htmlUrl'] ? $bidInfoItemValue ['htmlUrl'] : '',
                    "isShowMuteButton"=>  boolval(Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $this->mAdType, 'show_mute_button')),
                    "isShowCloseButton"=>  boolval(Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $this->mAdType, 'show_close_button')),
                    "isShowDownloadButton"=>  boolval(Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $this->mAdType, 'show_download_button')),
                    "isShowProgressButton"=>  boolval(Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $this->mAdType, 'show_progress_button'))
                );
            } else if ($this->mAdType == Common_Service_Const::PIC_AD_SUB_TYPE) {
                $imgUrl = $bidInfoItemValue ['imgUrl'] ? $bidInfoItemValue ['imgUrl'] : '';
                $imgUrls = array($imgUrl);
                $extraInfo= array(
                    "imgUrls"=>$imgUrls,
                    "border"=>($dspId == Common_Service_Const::HOUSEAD_DSP_ID) ? $bidInfoItemValue ['border'] : '',
                    "isCloseButtonDelayShow"=>  boolval(Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $this->mAdType, 'close_button_delay_show')),
                    "closeButtonDelayShowTimes"=>Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $this->mAdType, 'close_button_delay_show_time')
                );
            } else if ($this->mAdType == Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
                $imgUrl = $bidInfoItemValue ['imgUrl'] ? $bidInfoItemValue ['imgUrl'] : '';
                $imgUrls = array($imgUrl);
                $extraInfo = array(
                    "imgUrls"=>$imgUrls,
                    "originalityTitle"=>$bidInfoItemValue ['originalityTitle'] ? $bidInfoItemValue ['originalityTitle'] : '',
                    "originalityDesc"=>$bidInfoItemValue ['originalityDesc'] ? $bidInfoItemValue ['originalityDesc'] : '',
                    "adSubType"=>$bidInfoItemValue['adSubType']?$bidInfoItemValue['adSubType']:$this->mPostData['adSubType'],
                    "appName"=>$bidInfoItemValue ['appName'] ? $bidInfoItemValue ['appName'] : '',     
                    "border"=>$bidInfoItemValue ['border'],
                    "boutiqueLabelUrl"=>$bidInfoItemValue ['boutiqueLabelUrl'] ? $bidInfoItemValue ['boutiqueLabelUrl'] : '',
                    "closeButtonUrl"=>$bidInfoItemValue ['closeButtonUrl'] ? $bidInfoItemValue ['closeButtonUrl'] : 0,
                    "playInterval"=>$bidInfoItemValue ['playInterval'] ? $bidInfoItemValue ['playInterval'] : 0,
                    "animationEffect"=>$bidInfoItemValue ['animationEffect'] ? $bidInfoItemValue ['animationEffect'] : 0
                );
            } else if ($this->mAdType == Common_Service_Const::SPLASH_AD_SUB_TYPE) {
                $imgUrl = $bidInfoItemValue ['imgUrl'] ? $bidInfoItemValue ['imgUrl'] : '';
                $imgUrls = array($imgUrl);
                $extraInfo = array(
                    "imgUrls"=>$imgUrls,
                    "isShowSkipButton"=>  boolval($bidInfoItemValue ['showSkipButton'] ? $bidInfoItemValue ['showSkipButton'] : 0),
                    "isShowCountdown"=>  boolval($bidInfoItemValue ['showCountdown'] ? $bidInfoItemValue ['showCountdown'] : 0),
                    "waitTime"=>$bidInfoItemValue ['waitTime'] ? $bidInfoItemValue ['waitTime'] : 0,
                    "showTime"=>$bidInfoItemValue ['showTime'] ? $bidInfoItemValue ['showTime'] : 0
                );
            } else if ($this->mAdType == Common_Service_Const::ENBED_AD_SUB_TYPE) {
                if ($this->mAdSubType == Common_Service_Const::SINGLE_ENBED_AD_SUB_TYPE) { // 单图
                    $imgUrl = $bidInfoItemValue ['imgUrl'];
                    $imgUrls = array($imgUrl);
                } else if ($this->mAdSubType == Common_Service_Const::COMBINATION_ENBED_AD_SUB_TYPE) { // 组图
                    $imgUrls = array ();
                    foreach ( $bidInfoItemValue ['imgUrls'] as $val ) {
                        $imgUrls [] = $val;
                    }

                }
                
                $extraInfo = array(
                    "imgUrls"=>$imgUrls,
                    "originalityDesc"=>$bidInfoItemValue ['originalityDesc']?$bidInfoItemValue ['originalityDesc']:$bidInfoItemValue ['adDesc'],
                    "adSubType"=>$this->mAdSubType,
                    "actionText"=>$bidInfoItemValue ['actionText'] ? $bidInfoItemValue ['actionText'] : '立即下载',
                    "score"=>$bidInfoItemValue ['score'] ? $bidInfoItemValue ['score'] : 5
                );
            }
            
            $tmp = array (
                'basicInfo'=>$basicInfo,
                'eventTraking'=>$eventTraking,
                'extraInfo'=>$extraInfo
            ); 
            $data['data'] ['adInfo'] [] = $tmp;
            // ADX广告除交叉推广广告外，其他形式每次请求均只下发一条
            if (($this->mAdType != Common_Service_Const::CUSTOME_AD_SUB_TYPE)) {
                break;
            }
        }
        if($priceCacheData){
            //保存扣费信息到缓存
            Dedelivery_Service_OriginalityRelationModel::saveOriginalityChargePriceKeyToCache ( $this->mBidId, $priceCacheData );
        }
        return $data;
    }

    public function parseJumpType($jumpType){
		if(!isset($jumpType)){
			$jumpType = 2;
			return$jumpType;
		}

		if( $this->mPlatform == Common_Service_Const::IOS_PLATFORM && in_array($jumpType,array(0,7))){
			$jumpType = 2;
		}elseif($this->mPlatform == Common_Service_Const::ANDRIOD_PLATFORM && in_array($jumpType,array(8))){
			$jumpType = 2;
		}else{
			$jumpType  = $jumpType;
		}
		return $jumpType;
	}
    

    
    public function isDebugMode(){
        if($this->mPostData['isTest']){
            return true;
        }
        return false;
    }

	/**
	 * @param $dspId
	 * @param $fixPriceInfo
	 * @param $bidInfoItemValue
	 * @param $chargeType
	 * @return array
	 */
	public function getDspPrice($dspId, $fixPriceInfo, $bidInfoItemValue, $chargeType){
		$bidPrice = '0';
		$price = '0';
		if ($fixPriceInfo['is_bid'] == 0) {
			$chargePrice = $fixPriceInfo['settlement_price'];
			if (Common_Service_Const::HOUSEAD_DSP_ID == $dspId) {
				$price = sprintf("%.4f", $bidInfoItemValue ['price']);
				$bidPrice = sprintf("%.4f", $chargePrice);
			} else {
				if ($chargeType == Common_Service_Const::CHARGE_TYPE_CPM) {
					$price = sprintf("%.4f", $chargePrice / 1000);
					$bidPrice = sprintf("%.4f", $chargePrice);
				} else {
					$price = sprintf("%.4f", $chargePrice);
					$bidPrice = sprintf("%.4f", $chargePrice * 1000);
				}
			}
		} else {
			if (Common_Service_Const::HOUSEAD_DSP_ID == $dspId) {
				$price = sprintf("%.4f", $bidInfoItemValue ['price']);
				$bidPrice = sprintf("%.4f", $bidInfoItemValue ['bidPrice']);
			} else {
				$price = sprintf("%.4f", $bidInfoItemValue ['price'] / 1000);
				$bidPrice = sprintf("%.4f", $bidInfoItemValue ['price']);
			}
		}
		return array($price, $bidPrice);
	}

	public function getCache(){
		return Cache_Factory::getCache();
	}


	public function getAdTypeByBlockIdFromCache($dspNo, $blockId){
		$cache = $this->getCache();
		$key = $this->getAdTypeCacheKey($dspNo,$blockId);
		$data = $cache->get($key);
		return $data;

	}

	public function getAdTypeCacheKey($dspNo, $blockId){
		return 'thirdPartyMapping_'.$this->mAppKey.'_'.$dspNo.'_'.$blockId;


	}

	public function setAdtypeToCache($dspNo, $blockId){
		$cache = $this->getCache();
		$data['adType'] = $this->mAdType;
		$data['adSubType'] = $this->mAdSubType;
		$key = $this->getAdTypeCacheKey($dspNo,$blockId);
		if($cache->exists($key)){
			return false;
		}
		$cache->set($key,$data);

	}


	public function priceReplaceMico($url,$price, $dspId){
		if(!$url){
			return '';
		}
		if(Common_Service_Const::ZHIZIYUN_DSP_ID != $dspId){
			return $url;
		}
		return str_replace('{price}',$price,$url);

	}

	/**
	 * @param $dspId
	 * @param $fixPriceInfo
	 * @param $bidInfoItemValue
	 * @return int|string
	 */
	public function getChargeType($dspId, $fixPriceInfo, $bidInfoItemValue)
	{
		$chargeType = Common_Service_Const::CHARGE_TYPE_CPM;
		if ($fixPriceInfo['is_bid']) {
			$chargeType = $bidInfoItemValue ['chargeType'] ? $bidInfoItemValue ['chargeType'] : MobgiApi_Service_AdsListModel::getDspChargeType($dspId);
		} else {
			if (Common_Service_Const::HOUSEAD_DSP_ID == $dspId) {
				$chargeType = $bidInfoItemValue ['chargeType'];
			} else {
				$chargeType = MobgiApi_Service_AdsListModel::getDspChargeType($dspId);
			}
		}
		return $chargeType;
	}



	public function addReportUrl($eventType,$reportUrl,$reportDataList){
		if($this->mPostData['providerId'] != Util_Ssp::PROVIDER_ID_FOR_4399){
			return $reportUrl;
		}
		$statUrl = Yaf_Application::app()->getConfig()->statroot.'/ssp/?';
		$data = $this->getReportData($reportDataList,$eventType);
		$sign = Util_Ssp::getSign($data);
		$data['sign'] = $sign;
		$requestStr = http_build_query($data);
		$reportUrl[]=$statUrl.$requestStr;
		return $reportUrl;
	}

	public function getReportData($reportDataList,$eventType){
		$data['providerId'] = $this->mPostData['providerId'];
		$data['dspId'] = $reportDataList['dspId'];
		$data['bidId'] = $reportDataList['bidId'];
		$data['outBidId'] =  $reportDataList['outBidId'];
		$data['adId'] = $reportDataList['adId'];
		$data['originalityId'] = $reportDataList['originalityId'];
		$data['price'] = $reportDataList['price'];
		$data['chargeType'] = $reportDataList['chargeType'];
		$data['currency'] = $reportDataList['currency'];
		$data['appKey'] = $this->mPostData['app']['appKey'];
		$data['blockId'] = $this->mPostData['imp'][0]['blockId'];
		$data['adType'] = $this->mAdType;
		$data['eventType'] = $eventType;
		$data['brand'] =  $this->mPostData['device']['brand'];
		$data['model'] =  $this->mPostData['device']['model'];
		$data['imei'] =  $this->mPostData['device']['deviceId'];
		$data['uuid'] =  $this->mPostData['device']['deviceId'];
		$data['netType'] =  $this->mPostData['device']['net'];
		$data['operator'] =  $this->mPostData['device']['operator'];
		$data['platform'] =  $this->mPostData['device']['platform'];
		$data['resolution'] =  $this->mPostData['device']['resolution'];
		$data['appVersion'] =  $this->mPostData['app']['version'];
		$data['sdkVersion'] =  -1;
		$data['clientTime'] =  common::getTime();
		return $data;
	}





}
