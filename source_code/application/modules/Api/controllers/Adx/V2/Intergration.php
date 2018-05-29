<?php
/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-10-30 15:46:09
 * $Id: Intergration.php 62100 2017-10-30 15:46:09Z hunter.fang $
 */
if (! defined ( 'BASE_PATH' ))
    exit ( 'Access Denied!' );

class Adx_V2_IntergrationController extends Adx_Api_V2_BaseController {

    public function getTokenAction() {
        $this->mProviderId = $this->getGet ( 'providerId' );
        $timeStamp = Common::getTime ();
        $sign = sha1 ( $this->mProviderId . $timeStamp );
        $token = base64_encode ( $this->mProviderId . ',' . $timeStamp . ',' . $sign );
        echo $token;
    }
    
   
    

    public function configAction() {
        $this->checkAdxToken ();
        $this->checkIntergrationPostParam ( true );
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
        $this->getIntergrationData ();
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
            $this->output ( Util_ErrorCode::BASEINFO_CONFIG_EMPTY, 'baseInfo pos mapping is empty' );
        }
        // 获取聚合广告位的参数
        $this->mAppPositonList = $this->parseAdsPosRelInfo ( $adsPosRelConf );
        $this->mAdsPosRelState = $this->getAdsPosRelState ( $adsPosRelConf );
        if (empty ( $this->mAppPositonList )) {
            $this->output ( Util_ErrorCode::BASEINFO_CONFIG_EMPTY, 'parse baseInfo pos mapping is empty' );
        }
        // 广告商参数设置信息
        $adsAppRelList = $this->getAdsAppRel ();
        if (empty ( $adsAppRelList )) {
            $this->output ( Util_ErrorCode::BASEINFO_CONFIG_EMPTY, 'baseInfo app mapping is empty' );
        }
        if ($this->mWhitelistConfig) {
            // 获取白名单流量区配置
            $flowConf = $this->getWhitelistPolicyConfList ();
            if (empty ( $flowConf )) {
                $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'whitelist flow config is empty' );
            }
            // 交叉推广不走流量判断计算广告商权重列表
            if ($this->mAdType != Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
                $adsWeightList = $this->parseWeightList ( $flowConf );
                if (empty ( $adsWeightList )) {
                    $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'whitelist weight is empty ' );
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
                    $this->mAbtestConRelId = $cacheData['mAbtestConRelId'];
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
                            $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'abTest flow config is empty' );
                        }
                        $adsWeightList = $this->parseWeightList ( $flowConf );
                        if (empty ( $adsWeightList )) {
                            $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'abTest weight is empty ' );
                        }
                        $this->saveUserFlowConfInfoToCache ( $flowConf, $adsWeightList, $abTestConfId );
                        $this->savaAbtestUserToCache ( $abTestConfId );
                    } else {
                        list($adsWeightList, $flowConf) = $this->getCommonWeightList ();
                    }
                }
            } else {
                  list($adsWeightList, $flowConf) = $this->getCommonWeightList ();
            }
        }
        
        // 优先广告配置格式化
        $this->mPriorityAdsList = $this->fillPriorityAdsConfList ( $flowConf ['priority_ads_conf'] );
        // 组装列表数据
        $data = $this->fillIntergrationData ( $adsAppRelList, $adsWeightList );
        if (empty ( $data )) {
            $this->output ( Util_ErrorCode::CONFIG_EMPTY, 'get list fail ' );
        }
        $this->output ( 0, 'ok', $data );
    }
    
    public function getCommonWeightList(){
        //删除abtest的保留的缓存
        $this->delAbtestUserCache();
        // 获取流量区配置
        $this->mFlowId = $this->getFlowConfIdByUser ();
        if (empty ( $this->mFlowId )) {
            $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'flow config is empty' );
        }
        $this->mFlowAdTypeRel = $this->getFlowAdTypeRelByFlowId ( $this->mFlowId );
        if (! $this->mFlowAdTypeRel) {
            $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'flow config adType is empty' );
        }
        if (! $this->mFlowAdTypeRel ['status']) {
            $this->output ( Util_ErrorCode::FITER_CONFIG, 'flow config state is close，close id:' . $this->mFlowId );
        }
        $flowConf = $this->getFlowConfListById ();
        if (empty ( $flowConf )) {
            $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'flow config is empty' );
        }
        // 交叉推广不走流量判断计算广告商权重列表
        if ($this->mAdType != Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
            // 流量配置的广告商权重列表
            $adsWeightList = $this->getFlowConfWeightList ( $flowConf );
            if (empty ( $adsWeightList )) {
                $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'flow weight is empty' );
            }
        }
        
        return array($adsWeightList, $flowConf);
    }
    
  

    /**
     * 填充数据，格式化
     */
    private function fillIntergrationData($adsAppRelList, $adsWeightList) {
        $data = array ();
        // 特殊处理交叉推广下发聚合配置
        if ($this->mAdType == Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
            $data ['thirdBlockList'] = array ();
            $data ['thirdPartyAppInfo'] = array ();
            $data ['globalConfig'] = $this->fillGlobalConfigOutputData ( );
            $data ['appBlockIdList'] = $this->parseAppBlockList ();
            $data['serverInfo'] = $this->fillServerInfoToData();
        } else {
            // 组装数据
            $thirdBlockList = $this->fillThirdPartyBlockList ( $adsWeightList );
            if (empty ( $thirdBlockList )) {
                return $data;
            }
            $appBlockIdList = $this->parseAppBlockList ();
            if (empty ( $appBlockIdList )) {
                return $data;
            }
            $data ['appBlockIdList'] = $appBlockIdList;
            $data ['thirdBlockList'] = $thirdBlockList;
            $data ['thirdPartyAppInfo'] = $this->fillThirdPartyAppInfo ( $adsWeightList, $adsAppRelList );
            $data ['globalConfig'] = $this->fillGlobalConfigOutputData (  );
            $data['serverInfo'] = $this->fillServerInfoToData();
        }
        return $data;
    }

    private function fillThirdPartyAppInfo($adsWeightList, $adsAppRelList) {
        if (empty ( $adsWeightList )) {
            return array ();
        }
        $adsList = array_keys ( $adsWeightList );
        $flowAppRel = $this->getFlowAppRel ();
        if ($this->mPriorityAdsList) {
            $priorityAdsList = array_keys ( $this->mPriorityAdsList );
            $adsList = array_merge ( $adsList, $priorityAdsList );
            $adsList = array_unique ( $adsList );
        }
        $appInfo = array ();
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
            $appInfo [] = array (
                    'thirdPartyAppkey' => $thirdPartyAppkey,
                    'thirdPartyAppsecret' => $thirdPartyAppsecret,
                    'thirdPartyName' => $this->getThirdPartAdsName($adsId) 
            );
        }
        return $appInfo;
    }

    private function fillThirdPartyBlockList($adsWeightList) {
        $blockList = array ();
        // 广告商列表
        $adsList = MobgiApi_Service_AdsListModel::getAdsListBySubType ( $this->mAdType );
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
            $generalConfigs = $this->fillAdPositonRate ( $adsWeightList, $adsList, $posInfo, $posKey, $flowPosRel );
            $prioritConfig = $this->fillPrioritAdsConfig ( $adsList, $posInfo, $posKey, $flowPosRel, $flowPosRel );
            if (empty ( $generalConfigs ) && empty ( $prioritConfig )) {
                continue;
            }
            $blockList [] = array (
                    'blockIdName' => $posInfo ['pos_name'],
                    'blockId' => $posKey,
                    'generalConfigs' => $generalConfigs,
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
                    'index' => intval($val ['index']),
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
    
    public function parseAppBlockList() {
        if (empty ( $this->mAppPositonList )) {
            return array ();
        }
        $posList = array ();
        $flowPosPolicy = $this->getFlowPosPolicy ();
        foreach ( $this->mAppPositonList as $posKey => $posInfo ) {
            if (isset ( $flowPosPolicy [$posKey] ['status'] ) && ! $flowPosPolicy [$posKey] ['status']) {
                continue;
            }
            $posList [] = array (
                    'blockIdName' => $posInfo ['pos_name'],
                    'blockId' => $posKey,
                    'rate' => isset ( $flowPosPolicy [$posKey] ['rate'] ) ? strval(floatval($flowPosPolicy [$posKey] ['rate'])) : strval(floatval($posInfo ['rate'])),
                    'showLimit' => isset ( $flowPosPolicy [$posKey] ['show_limit'] ) ? intval($flowPosPolicy [$posKey] ['show_limit']) : intval($posInfo ['show_limit'])
            );
        }
        return $posList;
    }


}