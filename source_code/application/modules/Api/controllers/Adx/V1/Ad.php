<?php
/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-3-17 11:14:29
 * $Id: Ad.php 62100 2017-3-17 11:14:29Z hunter.fang $
 */
if (! defined ( 'BASE_PATH' ))
    exit ( 'Access Denied!' );

class Adx_V1_AdController extends Adx_Api_V1_BaseController {

    public function getTokenAction() {
        $this->mProviderId = $this->getGet ( 'providerId' );
        $timeStamp = Common::getTime ();
        $sign = sha1 ( $this->mProviderId . $timeStamp );
        $token = base64_encode ( $this->mProviderId . ',' . $timeStamp . ',' . $sign );
        echo $token;
    }

    /**
     * 1.请求adx广告
     * adx/list
     * method: post
     * curl 'http://rock.advertiser.housead.com/v1/adx/getAdList' -H 'Authorization: Bearer NDUxNSwxNDg5NzM1ODYyLDM4ZDQ0ZjNhYjM2YWNiMTVjNzdmYjllOThhOTExZTBkMDU4NjQ1MjY=' -d '{"providerId":1,"isTest":1,"sdkVersion":1,"imp":{"blockId":"xxs2143d","adType":1,"attr":{"w":11,"h":22,"pos":33},"bidfloor":1.02},"user":{"id":"234234asdf34eds"},"device":{"ua":"aaaaaa","ip":"1.2.3.4","brand":"apple","model":"iphone","platform":1,"version":"3.2.1","resolution":"600*800","operator":1,"net":1,"deviceId":"2134qad23r43e","screenDirection":1,"screenSize":2.4},"app":{"appKey":"xxxx23","name":"1111232","bundle":"com.abc.11","version":"1.2.3"}}'
     * {"code":0,"msg":"","data":{"list":[{"id":"50","ad_name":"adname_hunter05","ad_target_type":"1","ad_target":"http:\/\/baidu.com\/xx","package_name":"testpackagename","unit":"22","date_type":"0","date_range":"{\"start_date\":\"2016-12-13\",\"end_date\":\"2016-12-30\"}","time_type":"0","time_range":null,"time_series":"001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000001111111111111111110000000000000000000000000000","charge_type":"1","price":"1.0000","create_time":1481696845,"update_time":1481696847,"account_id":"4","status":"1","del":"0","direct_id":"3","outer_ad_id":"14","direct_config":null}],"page_info":{"total_num":"6","total_page":6,"page_size":1,"page":1}}}
     *
     * 测试housead转dsp
     * 只有housead
     * curl 'http://rock.advertiser.housead.com/adx/v1/getAdList' -H 'Authorization: Bearer NDUxNSwxNDg5NzM1ODYyLDM4ZDQ0ZjNhYjM2YWNiMTVjNzdmYjllOThhOTExZTBkMDU4NjQ1MjY=' -d '{"providerId":1,"isTest":1,"sdkVersion":1,"adType":1,"imp":{"blockId":"MC43OTI1MjcwMCAxNDYxNjU-ZTE5MDgx","attr":{"w":11,"h":22,"pos":33},"bidfloor":1.02},"user":{"id":"ffffffff-8059-8a17-66db-c1063de1ea70"},"device":{"ua":"aaaaaa","ip":"1.2.3.4","brand":"apple","model":"iphone","platform":1,"version":"3.2.1","resolution":"720\/1184","operator":1,"net":1,"deviceId":"ffffffff-8059-8a17-66db-c1063de1ea70","screenDirection":2,"screenSize":4.3304156844349},"app":{"appKey":"e19081b4527963d70c7a","name":"1111232","bundle":"com.abc.11","version":"0.1.0"}}'
     * 有housead和聚合数据
     * curl 'http://rock.advertiser.housead.com/adx/v1/getAdList' -H 'Authorization: Bearer NDUxNSwxNDg5NzM1ODYyLDM4ZDQ0ZjNhYjM2YWNiMTVjNzdmYjllOThhOTExZTBkMDU4NjQ1MjY=' -d '{"providerId":1,"isTest":1,"sdkVersion":1,"adType":1,"imp":{"blockId":"MC43OTI1MjcwMCAxNDYxNjU-ZTE5MDgx","attr":{"w":11,"h":22,"pos":33},"bidfloor":1.02},"user":{"id":"ffffffff-8059-8a17-66db-c1063de1ea70"},"device":{"ua":"aaaaaa","ip":"1.2.3.4","brand":"apple","model":"iphone","platform":1,"version":"3.2.1","resolution":"720\/1184","operator":1,"net":1,"deviceId":"ffffffff-8059-8a17-66db-c1063de1ea70","screenDirection":2,"screenSize":4.3304156844349},"app":{"appKey":"54C8BFBCBEAC92A48B3B","name":"1111232","bundle":"com.abc.11","version":"0.1.0"}}'
     * 测试环境调试
     * curl 'http://test-api-ha.mobgi.com/adx/v1/getAdList' -d '{"providerId":1,"adType":1,"isTest":1,"sdkVersion":1,"imp":{"blockId":"MC43OTI1MjcwMCAxNDYxNjU-ZTE5MDgx","attr":{"w":11,"h":22,"pos":33},"bidfloor":1.02},"user":{"id":"ffffffff-8059-8a17-66db-c1063de1ea70"},"device":{"ua":"aaaaaa","ip":"1.2.3.4","brand":"apple","model":"iphone","platform":1,"version":"3.2.1","resolution":"720\/1184","operator":1,"net":1,"deviceId":"ffffffff-8059-8a17-66db-c1063de1ea70","screenDirection":2,"screenSize":4.3304156844349},"app":{"appKey":"e19081b4527963d70c7a","name":"1111232","bundle":"com.abc.11","version":"0.1.0"}}'
     *
     * curl 'http://test-api-ha.mobgi.com/adx/v1/getAdList' -d '{"device":{"ua":"Mozilla\/5.0 (Linux; Android 5.1; m1 note Build\/LMY47D) AppleWebKit\/537.36 (KHTML, like Gecko) Version\/4.0 Chrome\/40.0.2214.127 Mobile Safari\/537.36","brand":"Meizu","model":"m1note","platform":1,"version":"5.1","resolution":"1080*1920","operator":0,"net":1,"deviceId":"867348026517826","screenDirection":2,"screenSize":0},"imp":[],"providerId":"1","app":{"appKey":"e19081b4527963d70c7a","name":"聚合广告视频Demo","bundle":"com.kiloo.subwaysurf","version":"0.13.0","channelId":"0.13.0"},"user":{"id":"59821_017531732689113os65315p26q7"},"adType":1}'
     */
    public function getAdListAction() {
        $this->checkAdxToken ();
        $this->checkAdPostParam ( true );
        $this->initIp ();
        $this->initAdType ();
        // 获取应用的信息
        $this->mAppInfo = MobgiApi_Service_AdAppModel::getAppInfoByAppKey ( $this->mAppKey );
        if (empty ( $this->mAppInfo )) {
            $this->output ( Util_ErrorCode::APP_STATE_CHECK, 'app state is close' );
        }
        // 获取广告位
        $this->mAppPositonList = $this->getAppPosInfo ( $this->mAppInfo );
        if (empty ( $this->mAppPositonList )) {
            $this->output ( Util_ErrorCode::POS_STATE_CHECK, 'app positon state is close' );
        }
        $this->mWhitelistConfig = $this->isWhitelist ();
        // 非白名单才走特殊过滤逻辑(白名单不走特殊过滤逻辑)
        // 获取聚合配置
        $intergrationData = $this->getIntergrationData ();
        // 创建竞价ID
        $this->mBidId = $this->createRequestId ();
        // 获取所有的dsp响应
        $dspResponses = $this->getAllDspResponses ();
        // 检查所有DSP返回的数据
        $dspResponses = $this->farmatAllDspResponses ( $dspResponses );
        // 竞价
        list ( $winDspNo, $winDspResponse ) = $this->getWinDspResponse ( $dspResponses );
        // 组装输出数据
        $outputData = $this->fillDataToOutputData ( $winDspNo, $winDspResponse, $intergrationData );
        $this->output ( Util_ErrorCode::CONFIG_SUCCESS, 'ok', $outputData );
    }

    /**
     *
     * @param type $dspResponses            
     * @return array
     */
    private function farmatAllDspResponses($dspResponses) {
        $outData = array ();
        if(isset($dspResponses['ret']) && $dspResponses['ret']){
            return $outData;
        }
        $dspList = array_keys ( $dspResponses );
        if (empty ( $dspList )) {
            return $outData;
        }
        foreach ( $dspList as $dspNo ) {
            $tmp = $this->dspInstances [$dspNo]->formatResponses ( $dspResponses [$dspNo], $dspNo, 'V1' );
            if (! empty ( $tmp )) {
                $outData [$dspNo] = $tmp;
            }
        }
        if ($this->isDebugMode ()) {
            $this->mDebugInfo ['formatDspResponses'] = $outData;
        }
        
        return $outData;
    }

    private function fillDataToOutputData($winDspNo, $winDspResponse, $intergrationData) {
        // 交叉推广下发
        if ($this->mAdType == Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
            $output = array ();
            // 高于底价则竞价成功,下发竞价成功的adx响应,低于底价,下发聚合数据
            if ($winDspNo && $winDspResponse) {
                $output ['configType'] = 2;
                $configList = $winDspResponse ['data'];
                $configList ['backupList'] = array ();
                if (! $intergrationData ['ret']) {
                    $configList ['backupList'] = $intergrationData ['data'];
                }
                $output ['configList'] = $configList;
                $output ['configList'] ['appBlockList'] = $this->parseAppBlockList ();
            } else {
                $output ['configType'] = 2;
                $output ['configList'] = array ();
            }
        } else {
            $output = array ();
            // 高于底价则竞价成功,下发竞价成功的adx响应,低于底价,下发聚合数据
            if ($winDspNo && $winDspResponse) {
                $output ['configType'] = 2;
                $configList = $winDspResponse ['data'];
                $configList ['backupList'] = array ();
                if (! $intergrationData ['ret']) {
                    $configList ['backupList'] = $intergrationData ['data'];
                }
                $output ['configList'] = $configList;
                $output ['configList'] ['appBlockList'] = $this->parseAppBlockList ();
            } else {
                $output ['configType'] = 1;
                $output ['configList'] = $intergrationData ['data'];
                if ($intergrationData ['ret']) {
                    $this->output ( $intergrationData ['ret'], $intergrationData ['msg'] );
                }
                $output ['configList'] ['appBlockList'] = $this->parseAppBlockList ();
            }
        }
        return $output;
    }

    /**
     * 竞价获取赢得竞价的dsp响应
     *
     * @param type $dspResponses            
     * @return type
     */
    private function getWinDspResponse($dspResponses) {
        $winDspNo = '';
        $winDspResponse = array ();
        $bidPrice = $this->mBasePrice;
        if (! $dspResponses) {
            return array (
                    $winDspNo,
                    $winDspResponse 
            );
        }
        if ($this->mAdType == Common_Service_Const::CUSTOME_AD_SUB_TYPE && isset ( $dspResponses [Common_Service_Const::HOUSEAD_DSP_ID] )) {
            $winDspNo = Common_Service_Const::HOUSEAD_DSP_ID;
            $winDspResponse = $dspResponses [Common_Service_Const::HOUSEAD_DSP_ID];
            $winDspResponse ['data'] ['dspId'] = $winDspNo;
        } else {
			$bidPriceDspArr = array();
			foreach ( $dspResponses as $dspNo => $dspResponse ) {
				// 根据客户端版本判断bidInfo是否为list再确定
				if ($this->mIsBidInfoList) {
					if($dspResponse ['data'] ['bidInfo'] [0] ['price']){
						$bidPriceDspArr[$dspNo]= $dspResponse ['data'] ['bidInfo'] [0] ['price'];
					}
				}else{
					if($dspResponse ['data'] ['bidInfo'] ['price']){
						$bidPriceDspArr[$dspNo]= $dspResponse ['data'] ['bidInfo'] ['price'];
					}
				}
			}
			$winDspNo = $this->getWinBidPriceDspNo($bidPriceDspArr);
			if($winDspNo){
				$winDspResponse =$dspResponses[$winDspNo];
			}

        }

        if ($winDspNo && $winDspResponse) {
            $this->sendDspEvent ( $winDspNo, 'win' );
			$this->sendDspEvent ( $winDspNo, 'notice',$winDspResponse['data']['outBidId'] );

        }
        if ($this->isDebugMode ()) {
			$this->mDebugInfo ['bidPirceDspList'] = $bidPriceDspArr;
            $this->mDebugInfo ['winDspNo'] = $winDspNo;
        }
        return array (
                $winDspNo,
                $winDspResponse 
        );
    }

    public function testAction() {
        $data = $this->getIntergrationData ();
        if ($data ['ret']) {
            $this->output ( $data ['ret'], $data ['msg'] );
        }
        $output ['configType'] = 1;
        $output ['configList'] = $data ['data'];
        $this->output ( 0, 'ok', $output );
    }

    private function getIntergrationData() {
        if ($this->checkSdkVersion ()) {
            // 一些特殊平台走特殊的定向配置 jinli baidu
            if ($this->isAndriodPlatform () && isset ( $this->mPostData ['extra'] ['adList'] )) {
                $this->getDirectConfig ();
            }
        }
        // 获取聚合广告位的参数
        $adsPosRelConf = $this->getAdsPosRel ();
        if (empty ( $adsPosRelConf )) {
            return $this->returnOutput ( Util_ErrorCode::BASEINFO_CONFIG_EMPTY, 'baseInfo pos mapping is empty' );
        }
        // 获取聚合广告位的参数
        $this->mAppPositonList = $this->parseAdsPosRelInfo ( $adsPosRelConf );
        $this->mAdsPosRelState = $this->getAdsPosRelState ( $adsPosRelConf );
        if (empty ( $this->mAppPositonList )) {
            return $this->returnOutput ( Util_ErrorCode::BASEINFO_CONFIG_EMPTY, 'parse baseInfo pos mapping is empty' );
        }
        // 广告商参数设置信息
        $adsAppRelList = $this->getAdsAppRel ();
        if (empty ( $adsAppRelList )) {
            return $this->returnOutput ( Util_ErrorCode::BASEINFO_CONFIG_EMPTY, 'baseInfo app mapping is empty' );
        }
        if ($this->mWhitelistConfig) {
            // 获取白名单流量区配置
            $flowConf = $this->getWhitelistPolicyConfList ();
            if (empty ( $flowConf )) {
                return $this->returnOutput ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'whitelist flow config is empty' );
            }
            // 交叉推广不走流量判断计算广告商权重列表
            if ($this->mAdType != Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
                $adsWeightList = $this->parseWeightList ( $flowConf );
                if (empty ( $adsWeightList )) {
                    return $this->returnOutput ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'whitelist weight is empty' );
                }
            }
        } else {
            $adTestConf = $this->getHitAbTestConf ();
            if ($this->isDebugMode ()) {
                $this->mDebugInfo ['adTestConf'] = $adTestConf;
            }
            // 有测试配置
            if ($adTestConf) {
                $cacheData = $this->getUserFlowConfInfoCache ();
                $hkey = $this->getUserFlowConfHkey();
                $cache = self::getAbTestCache();
                if ($cacheData && array_key_exists($cache->hGet($hkey, 'abTestConfId'), $adTestConf)) {
                    $adsWeightList = json_decode ( $cacheData ['adsWeightList'], true );
                    $flowConf = json_decode ( $cacheData ['flowConf'], true );
                    $this->mFlowAdTypeRel = json_decode ( $cacheData ['mFlowAdTypeRel'], true );
                    $this->mFlowId = $cacheData ['mFlowId'];
                    $this->mUserObject = MobgiApi_Service_AbConfModel::ABTEST_CONF_TYPE;
                    $this->mAbtestConRelId = $cacheData ['mAbtestConRelId'];
                    if ($this->isDebugMode ()) {
                        $this->mDebugInfo ['mAbtestConRelId'] = $this->mAbtestConRelId ;
                        $this->mDebugInfo ['mAbTestFlowId'] = $cacheData['mAbTestFlowId'];
                        $this->mDebugInfo ['abTestConfId'] = $this->mAbtestConRelId;
                        $this->mDebugInfo ['flowConf'] = $flowConf;
                        $this->mDebugInfo ['adsWeightList'] = $adsWeightList;
                        $this->mDebugInfo ['mFlowId'] =$this->mFlowId;
                        $this->mDebugInfo ['mFlowAdTypeRel'] =$this->mFlowAdTypeRel;
                    }
                } else {
                    $abTestConfId = $this->rateAbTestConfId ( $adTestConf );
                    if ($this->isDebugMode ()) {
                        $this->mDebugInfo ['abTestConfId'] = $abTestConfId;
                    }
                    // 符合测试条件
                    if ($abTestConfId) {
                        $flowConf = $this->getAbTestFlowConfList ( $abTestConfId );
                        if (empty ( $flowConf )) {
                          return  $this->returnOutput ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'abTest flow config is empty' );
                        }
                        $adsWeightList = $this->parseWeightList ( $flowConf );
                        if (empty ( $adsWeightList )) {
                          return  $this->returnOutput ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'abTest weight is empty' );
                        }
                        $this->saveUserFlowConfInfoToCache ( $flowConf, $adsWeightList, $abTestConfId );
                        $this->savaAbtestUserToCache ( $abTestConfId );
                    } else {
                        list($adsWeightList, $flowConf) = $this->getCommonWeightList ();
                        if($adsWeightList['ret']){
                            return $this->returnOutput ( $adsWeightList['ret'], $adsWeightList['msg'],$adsWeightList['data'] );
                        }
                    }
                }
            } else {
                list($adsWeightList, $flowConf) = $this->getCommonWeightList ();
                if($adsWeightList['ret']){
                    return $this->returnOutput ( $adsWeightList['ret'], $adsWeightList['msg'],$adsWeightList['data'] );
                }
            }
        }
        
        // 优先广告配置格式化
        $this->mPriorityAdsList = $this->fillPriorityAdsConfList ( $flowConf ['priority_ads_conf'] );
        // 组装列表数据
        $data = $this->fillIntergrationData ( $adsAppRelList, $adsWeightList );
        if (empty ( $data )) {
            return $this->returnOutput ( Util_ErrorCode::CONFIG_EMPTY, 'get list fail' );
        }
        return $this->returnOutput ( 0, 'ok', $data );
    }
    
    private function getCommonWeightList(){
        //删除abtest的保留的缓存
        $this->delAbtestUserCache();
        // 获取流量区配置
        $this->mFlowId = $this->getFlowConfIdByUser();
        if (empty (   $this->mFlowId )) {
            return $this->returnOutput ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'flow config is empty' );
        }
        $this->mFlowAdTypeRel = $this->getFlowAdTypeRelByFlowId ( $this->mFlowId );
        if (! $this->mFlowAdTypeRel) {
            return $this->returnOutput ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'flow config  ad_type is empty' );
        }
        if (! $this->mFlowAdTypeRel ['status']) {
           return  $this->returnOutput ( Util_ErrorCode::FITER_CONFIG, 'flow config state is close，close id:' . $this->mFlowId);
        }
        $flowConf = $this->getFlowConfListById ();
        if (empty ( $flowConf )) {
            return $this->returnOutput ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'flow config is empty' );
        }
        // 交叉推广不走流量判断计算广告商权重列表
        if ($this->mAdType != Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
            // 流量配置的广告商权重列表
            $adsWeightList = $this->getFlowConfWeightList ( $flowConf );
            if (empty ( $adsWeightList )) {
                return $this->returnOutput ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'flow weight is empty' );
            }
        }
     return array($adsWeightList, $flowConf);
    }

    /**
     * 填充数据，格式化
     */
    private function fillIntergrationData($adsAppRelList, $adsWeightList) {
        $data = array ();
        // 组装数据
        $thirdBlockList = $this->fillThirdPartyBlockList ( $adsWeightList );
        if (empty ( $thirdBlockList )) {
            return $data;
        }
        $data ['thirdBlockList'] = $thirdBlockList;
        $data ['thirdPartyAppInfo'] = $this->fillThirdPartyAppInfo ( $adsWeightList, $adsAppRelList );
        $data ['globalConfig'] = $this->fillGlobalConfigOutputData (  );
        $data ['prioritAdsListConfig'] = ( object ) array ();
        return $data;
    }

    private function fillThirdPartyAppInfo($adsWeightList, $adsAppRelList) {
        if (empty ( $adsWeightList )) {
            return array ();
        }
        $adsList = array_keys ( $adsWeightList );
        if ($this->mPriorityAdsList) {
            $priorityAdsList = array_keys ( $this->mPriorityAdsList );
            $adsList = array_merge ( $adsList, $priorityAdsList );
            $adsList = array_unique ( $adsList );
        }
        $flowAppRel = $this->getFlowAppRel ();
        $thirdAppInfo = array ();
        foreach ( $adsList as $key => $adsId ) {
            $thirdPartyAppkey = isset ( $adsAppRelList [$adsId] ['third_party_app_key'] ) ? $adsAppRelList [$adsId] ['third_party_app_key'] : '';
            if (! empty ( $flowAppRel )) {
                if (isset ( $flowAppRel [$adsId] ['third_party_app_key'] ) && $flowAppRel [$adsId] ['third_party_app_key']) {
                    $thirdPartyAppkey = $flowAppRel [$adsId] ['third_party_app_key'];
                }
            }
            $thirdPartyAppsecret = isset ( $adsAppRelList [$adsId] ['third_party_secret'] ) ? $adsAppRelList [$adsId] ['third_party_secret'] : '';
            if (! empty ( $flowAppRel )) {
                if (isset ( $flowAppRel [$adsId] ['third_party_secret'] ) && $flowAppRel [$adsId] ['third_party_secret']) {
                    $thirdPartyAppsecret = $flowAppRel [$adsId] ['third_party_secret'];
                }
            }
            $thirdAppInfo [] = array (
                    'thirdPartyAppkey' => $thirdPartyAppkey,
                    'thirdPartyAppsecret' => $thirdPartyAppsecret,
                    'thirdPartyName' => $this->getThirdPartAdsName($adsId) 
            );
        }
        return $thirdAppInfo;
    }

    private function fillThirdPartyBlockList($adsWeightList) {
        // 广告商列表
        $adsList = MobgiApi_Service_AdsListModel::getAdsListBySubType ( $this->mAdType );
        $blockList = array ();
        if ($this->isDebugMode ()) {
            $this->mDebugInfo ['adsWeightList'] = $adsWeightList;
            $this->mDebugInfo ['adsList'] = $adsList;
            $this->mDebugInfo ['mAdsPosRelState'] = $this->mAdsPosRelState;
        }
        $flowPosPolicy = $this->getFlowPosPolicy ();
        $flowPosRel = $this->getFlowPosRel ();
     
        foreach ( $this->mAppPositonList as $posKey => $posInfo ) {
            if (isset ( $flowPosPolicy [$posKey] ['status'] ) && ! $flowPosPolicy [$posKey] ['status']) {
                continue;
            }
            $generalConfig = $this->fillAdPositonRate ( $adsWeightList, $adsList, $posInfo, $posKey, $flowPosRel );
            $prioritConfig = $this->fillPrioritAdsConfig ( $adsList, $posInfo, $posKey, $flowPosRel, $flowPosRel );
            if (empty ( $generalConfig ) && empty ( $prioritConfig )) {
                continue;
            }
            $blockList [] = array (
                    'blockIdName' => $posInfo ['pos_name'],
                    'blockId' => $posKey,
                    'configs' => $generalConfig,
                    'prioritConfig' => $prioritConfig 
            );
        }
        return $blockList;
    }

    private function fillPrioritAdsConfig($adsList, $posInfo, $posKey, $flowPosRel) {
        $tmp = array ();
        if (empty ( $this->mPriorityAdsList )) {
            return $tmp;
        }
        foreach ( $this->mPriorityAdsList as $adsId => $val ) {
            if (! array_key_exists ( $adsId, $adsList )) {
                continue;
            }
            //白名单不走判断广告位是否开启状态的校验
            if($this->mWhitelistConfig){
                
            }else{
                if ($this->mAdsPosRelState [$posKey] [$adsId] != MobgiApi_Service_AdsPosRelModel::OPEN_STATE) {
                    continue;
                }
            }
            $thirdPartyBlockId = isset ( $posInfo ['other_block_id_list'] [$adsId]['third_party_block_id'] ) ? $posInfo ['other_block_id_list'] [$adsId]['third_party_block_id'] : '';
            if (! empty ( $flowPosRel )) {
                if (isset ( $flowPosRel [$posKey] [$adsId] ) && $flowPosRel [$posKey] [$adsId]) {
                    $thirdPartyBlockId = $flowPosRel [$posKey] [$adsId];
                }
            }
            $extraInfos = $this->getExtraInfos ( $adsId );
            $tmp [] = array (
                    'thirdPartyBlockId' => $thirdPartyBlockId,
                    'index' => $val ['index'],
                    'thirdPartyName' => $this->getThirdPartAdsName($adsId),
                    'showNumber' => isset ( $val ['limit_num'] ) ? intval ( $val ['limit_num'] ) : 0,
                    'extraInfos' => $extraInfos 
            );
        }
        return $tmp;
    }

    private function fillAdPositonRate($adsWeightList, $adsList, $posInfo, $posKey, $flowPosRel) {
        $tmp = array ();
        foreach ( $adsWeightList as $adsId => $weightList ) {
            if (! array_key_exists ( $adsId, $adsList ) || ! $weightList ['weight']) {
                continue;
            }
            //白名单不走判断广告位是否开启状态的校验
            if($this->mWhitelistConfig){
                
            }else{
                if ($this->mAdsPosRelState [$posKey] [$adsId] != MobgiApi_Service_AdsPosRelModel::OPEN_STATE) {
                    continue;
                }
            }
            $thirdPartyBlockId = isset ( $posInfo ['other_block_id_list'] [$adsId]['third_party_block_id'] ) ? $posInfo ['other_block_id_list'] [$adsId]['third_party_block_id'] : '';
            if (! empty ( $flowPosRel )) {
                if (isset ( $flowPosRel [$posKey] [$adsId] ) && $flowPosRel [$posKey] [$adsId]) {
                    $thirdPartyBlockId = $flowPosRel [$posKey] [$adsId];
                }
            }
            $extraInfos = $this->getExtraInfos ( $adsId );
            $tmp [] = array (
                    'thirdPartyBlockId' => $thirdPartyBlockId,
                    'rate' => $weightList ['weight'],
                    'thirdPartyName' => $this->getThirdPartAdsName($adsId),
                    'showNumber' => isset ( $weightList ['limit_num'] ) ? intval ( $weightList ['limit_num'] ) : 0,
                    'extraInfos' => $extraInfos 
            );
        }
        return $tmp;
    }
    
    /**
     * 获取广告商的额外配置
     *旧版本特殊处理
     * @param type $adsId
     * @param type $extra_config
     * @return type
     */
    public function getExtraInfos($adsId) {
        $extraInfos = array ();
        if ($adsId == 'Changxian') {
            $extraInfos ['limit_minimum_speed'] = 0;
            $extraInfos ['minimum_speed'] = 0;
            $extraInfos ['lazy_loading'] = intval ( $this->mFlowAdTypeRel ['is_delay'] );
            $extraInfos ['lazy_loading_time'] = intval ( $this->mFlowAdTypeRel ['time'] );
        }
        if (empty ( $extraInfos )) {
            $extraInfos =  array ();
        }
        return $extraInfos;
    }


}