<?php

class Mobgi_VideoAdsController extends Mobgi_BaseController {
	// 付费用户
	const PAY_USER = 1;
	// 活跃用户
	const ACTIVE_USER = 2;
	protected $ip = null;
	protected $mUserAreaInfo = null;
	protected $mAdType = null;
	protected $mAdTypeDesc = null;
	protected $mFlowAdTypeRel = null;
	protected $mAdsPosRelState = null;
	protected $mPriorityAdsList = null;
	protected $mAppPositonList = NULL;
	protected $mConditionRelFuntion = array (
	        'channel_conf_type' => 'checkChannelExistInConf',
	        'area_conf_type' => 'checkAreaExistInConf',
	        'game_conf_type' => 'checkGameVersionExistInConf',
	        'user_conf_type' => 'checkUserExistInConf'
	);
	protected $mAbConfRelFun = array(
           'channel_conf_type' => 'checkChannelExistInConf',
           'area_conf_type' => 'checkAreaExistInConf',
           'game_conf_type' => 'checkGameVersionExistInConf',
	        'sys_conf_type'=>'checkSysVersionExistInConf',
   );
   //用户对象
   protected  $mUserObject = 0;
   //测试配置
   protected $mAbTestConf = NULL;
   protected $mAbTestFlowId = 0;
   protected $mAbtestConRelId = 0;


    private function initParams(){
        $integrationTypeArr = array(0=>'1',1=>'2',2=>'3',3=>'4',4=>'5');
        $integrationTypeDescArr = array(0=>'video',1=>'pic',2=>'custome',3=>'splash',4=>'enbed');
        $this->mAppKey = $this->getInput('appKey');
        $this->mAdType = $integrationTypeArr[$this->getInput('adIntegrationType')];
        $this->mAdTypeDesc = $integrationTypeDescArr[$this->getInput('adIntegrationType')];
        $this->isReportToMonitor= 1;
    }

    /**
     * 获取聚合插图和聚合视频广告
     */
    public function getAdListAction(){
        $this->checkPostParams ();
        $this->initParams ();
        // 获取应用的信息
        $appInfo = $this->getAppInfo ();
        // 获取广告位信息
        $this->mAppPositonList = $this->getAppPosInfo ( $appInfo );
        if (empty ( $this->mAppPositonList )) {
            $this->output ( Util_ErrorCode::POS_STATE_CHECK, 'app Positon state is close' );
        }
        $this->mWhitelistConfig = $this->isWhitelist ();
        $this->initIp ();
        // 非白名单才走特殊过滤逻辑(白名单不走特殊过滤逻辑)
        if (empty ( $this->mWhitelistConfig )) {
            // 安卓版本0.4.1加入过滤条件
            if ($this->checkSdkVersion ()) {
                // 一些特殊平台走特殊的定向配置 jinli baidu
                if ($this->isAndriodPlatform () && $this->getInput ( 'adsList' )) {
                    $this->getDirectConfig ();
                }
            }
        }
        // 获取聚合广告位的参数
        $adsPosRelConf = $this->getAdsPosRel ();
        if (empty ( $adsPosRelConf )) {
            $this->output ( Util_ErrorCode::BASEINFO_CONFIG_EMPTY, 'baseInfo pos mapping is empty ' );
        }
        // 获取聚合广告位的参数
        $this->mAppPositonList = $this->parseAdsPosRelInfo ( $adsPosRelConf );
        if (empty ( $this->mAppPositonList )) {
            $this->output ( Util_ErrorCode::BASEINFO_CONFIG_EMPTY, 'parse baseInfo pos mapping is empty' );
        }
        $this->mAdsPosRelState = $this->getAdsPosRelState ( $adsPosRelConf );
        // 广告商参数设置信息
        $adsAppRelList = $this->getAdsAppRel ();
        if (empty ( $adsAppRelList )) {
            $this->output ( Util_ErrorCode::BASEINFO_CONFIG_EMPTY, 'baseInfo app mapping is empty' );
        }
        // 走白名单流程
        if ($this->mWhitelistConfig) {
            $flowConf = $this->getWhitelistPolicyConfList ();
            if (empty ( $flowConf )) {
                $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'whitelist flow config is empty' );
            }
            $adsWeightList = $this->parseWeightList ( $flowConf );
            if (empty ( $adsWeightList )) {
                $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'whitelist weight is empty ' );
            }
        } else {
            $adTestConf = $this->getHitAbTestConf ();
            if ($this->mIsTest) {
                $this->mDebugInfo ['adTestConf'] = $adTestConf;
            }
            // 符合测试条件
            if ($adTestConf) {
                $cacheData = $this->getUserFlowConfInfoCache();    
                $hkey = $this->getUserFlowConfHkey();
                 $cache = self::getAbTestCache();
                if ($cacheData && array_key_exists($cache->hGet($hkey, 'abTestConfId'), $adTestConf)) {
                    $adsWeightList =json_decode($cacheData['adsWeightList'], true);
                    $flowConf =json_decode($cacheData['flowConf'], true);
                    $this->mFlowAdTypeRel = json_decode($cacheData['mFlowAdTypeRel'],true);
                    $this->mFlowId = $cacheData['mFlowId'];
                    $data['mAbtestConRelId'] = $this->mAbtestConRelId;
                    if ($this->mIsTest) {
                         $this->mDebugInfo ['mAbtestConRelId'] = $cacheData['mAbtestConRelId'];
                        $this->mDebugInfo ['mAbTestFlowId'] = $cacheData['mAbTestFlowId'];
                        $this->mDebugInfo ['abTestConfId'] = $cacheData['abTestConfId'];
                        $this->mDebugInfo ['flowConf'] = $flowConf;
                        $this->mDebugInfo ['adsWeightList'] = $adsWeightList;
                        $this->mDebugInfo ['mFlowId'] =$this->mFlowId;
                        $this->mDebugInfo ['mFlowAdTypeRel'] =$this->mFlowAdTypeRel;
                    }
                }else{
                    $abTestConfId = $this->rateAbTestConfId ( $adTestConf );
                    if ($this->mIsTest) {
                        $this->mDebugInfo ['abTestConfId'] = $abTestConfId;
                    }       
                    // 抽样到配置
                    if($abTestConfId){
                        $flowConf = $this->getAbTestFlowConfList ( $abTestConfId );
                        if (empty ( $flowConf )) {
                            $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'abTest flow config is empty' );
                        }
                        $adsWeightList = $this->parseWeightList ( $flowConf );
                        if (empty ( $adsWeightList )) {
                            $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'abTest weight is empty' );
                        }
                        $this->saveUserFlowConfInfoToCache($flowConf,$adsWeightList,$abTestConfId);
                        $this->savaAbtestUserToCache($abTestConfId);
                    }else{
                         list($adsWeightList, $flowConf)  = $this->getCommonWeightList();
                    }
                }
            } else {
                 list($adsWeightList, $flowConf)  = $this->getCommonWeightList();
            }
        } 
        // 优先广告上配置
        $this->mPriorityAdsList = $this->fillPriorityAdsConfList ( $flowConf ['priority_ads_conf'] );
        if ($this->mIsTest) {
            $this->mDebugInfo ['prioritAdsListConfig'] = $this->mPriorityAdsList;
        }
        // 组装列表数据
        $data = $this->fillOutputData ( $adsAppRelList, $adsWeightList );
        // 0.3.1加入组装全局配置
        $globalConfigData = $this->fillGlobalConfigOutputData ();
        if (empty ( $data )) {
            $this->output ( Util_ErrorCode::CONFIG_EMPTY, 'get list fail' );
        }
        $this->localFormatOutput ( Util_ErrorCode::CONFIG_SUCCESS, 'ok', $data, $globalConfigData );
    }
    
    public function getCommonWeightList(){
        //删除abtest的保留的缓存
        $this->delAbtestUserCache();
        // 获取流量区配置
        $this->mFlowId = $this->getFlowConfIdByUser ();
        if (empty ( $this->mFlowId )) {
            return $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'flow config  is empty' );
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
            return $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'flow config  is empty' );
        }
        // 流量配置的广告商权重列表
        $adsWeightList = $this->getFlowConfWeightList ( $flowConf );
        if (empty ( $adsWeightList )) {
            $this->output ( Util_ErrorCode::FLOW_CONFIG_EMPTY, 'flow weight is empty' );
        }
        return array($adsWeightList, $flowConf);;
    }
    
    public function delAbtestUserCache(){
        $cache = self::getAbTestCache();
        $userId = $this->getInput('userId') ;
        $hkey ='abTest_'.$userId;
        if($cache->exists($hkey)){
            $cache->delete($hkey);
        }
    }
    
    public function saveUserFlowConfInfoToCache($flowConf,$adsWeightList,$abTestConfId){
        $expire = strtotime(date('Y-m-d 23:59:59',strtotime($this->mAbTestConf[$abTestConfId]['end_time'])))- strtotime(date('Y-m-d H:i:s'));
        $hkey = $this->getUserFlowConfHkey();
        $cache = self::getAbTestCache();
        $data['flowConf'] = json_encode($flowConf);
        $data['adsWeightList'] = json_encode($adsWeightList);
        $data['mFlowAdTypeRel'] = json_encode($this->mFlowAdTypeRel);
        $data['mFlowId'] =  $this->mFlowId;
        $data['abTestConfId'] = $abTestConfId;
        $data['mAbTestFlowId'] = $this->mAbTestFlowId;
        $data['mAbtestConRelId'] = $this->mAbtestConRelId;
        $cache->hMset($hkey, $data, $expire);
    }
    
    public function savaAbtestUserToCache($abTestConfId){
        $expire = strtotime(date('Y-m-d 23:59:59',strtotime($this->mAbTestConf[$abTestConfId]['end_time'])))- strtotime(date('Y-m-d H:i:s'));
        $cache = self::getAbTestCache();
        $userId = $this->getInput('userId') ;
        $data['config_id'] =  $this->mAbtestConRelId;
        $data['user_type'] =  MobgiApi_Service_AbConfModel::ABTEST_CONF_TYPE;
        $hkey ='abTest_'.$userId;
        $cache->hMset($hkey, $data, $expire);
        
    }
    
    public function getUserFlowConfInfoCache(){
        $hkey = $this->getUserFlowConfHkey();
        $cache = self::getAbTestCache();
        return $cache->hGetAll($hkey);
    }
    
    public function getUserFlowConfHkey(){
        $userId = $this->getInput('userId') ;
        $key = 'user::conf::'.$userId.'::'.$this->mAppKey.'::'.$this->mAdType;
        return $key;
    }
    
    public function getAbTestCache(){
        $resource = 'ab_info';
        $cache = Cache_Factory::getCache (Cache_Factory::ID_REMOTE_REDIS,$resource);
        return $cache;
    }
    
    public function getAbTestFlowConfList($abTestConfId){
        $abFlowId = $this->getAbTestFlowIdByConfId ( $abTestConfId );
        if ($this->mIsTest) {
            $this->mDebugInfo ['abFlowId'] = $abFlowId;
        }
        if(!$abFlowId){
            return false;
        }
        $this->mAbTestFlowId = $abFlowId;
        $flowAdTypeRel = MobgiApi_Service_AbFlowAdTypeRelModel::getBy ( array (
                'flow_id' => $abFlowId,
                'ad_type' => $this->mAdType
        ) );
        if ($this->imIsTest) {
            $this->mDebugInfo ['abTestStatus'] = $flowAdTypeRel['status'];
        }
        if(!$flowAdTypeRel['status']){
            return false;
        }
        if($flowAdTypeRel['is_default']){
            $parms ['app_key'] = $this->mAppKey;
            $parms ['conf_type'] = MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE;
            $defaultFlowResult = MobgiApi_Service_FlowConfModel::getBy ( $parms );
            if ($this->mIsTest) {
                $this->mDebugInfo ['abTestRelDeaultFlowId'] = $defaultFlowResult ['id'];
            }
            if (empty ( $defaultFlowResult )) {
                return false;
            }
            $defaultflowAdTypeRel = MobgiApi_Service_FlowAdTypeRelModel::getBy ( array (
                    'flow_id' => $defaultFlowResult ['id'],
                    'ad_type' => $this->mAdType
            ) );
            $this->mFlowAdTypeRel = $defaultflowAdTypeRel;
            if (! $defaultflowAdTypeRel ['status']) {
                return false;
            }
            if ($this->mIsTest) {
                $this->mDebugInfo ['abTestRelDeaultStatus'] =  $defaultflowAdTypeRel['status'];
            }
            $generalAdsConf = $this->getAdsConf ( $defaultFlowResult ['id'] , MobgiApi_Service_FlowAdsRelModel::GERNERAL_ADS );
            if (empty ( $generalAdsConf )) {
                return false;
            }
            $flowConf ['general_ads_conf'] = $generalAdsConf;
            $flowConf ['priority_ads_conf'] = array();
            if($defaultflowAdTypeRel['is_priority']){
                $flowConf ['priority_ads_conf'] = $this->getAdsConf ( $defaultFlowResult ['id'], MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS );
            }
            $flowConf ['dsp_ads_conf'] = array();
            if($defaultflowAdTypeRel['is_use_dsp']){
                $flowConf ['dsp_ads_conf'] = $this->getAdsConf($defaultFlowResult ['id'], MobgiApi_Service_FlowAdsRelModel::DSP_ADS);
                $flowConf ['price'] = $defaultflowAdTypeRel['price'];
            }
            $flowConf ['flow_id'] = $defaultFlowResult ['id'];
            $this->mFlowId = $defaultFlowResult ['id'];
        }else{
            $generalAdsConf = $this->getAbTestAdsConf( $flowAdTypeRel ['flow_id'] , MobgiApi_Service_FlowAdsRelModel::GERNERAL_ADS );
            if (empty ( $generalAdsConf )) {
                return false;
            }
            $flowConf ['general_ads_conf'] = $generalAdsConf;
            $flowConf ['priority_ads_conf'] = array();
            if($flowAdTypeRel['is_priority']){
                $flowConf ['priority_ads_conf'] = $this->getAbTestAdsConf ( $flowAdTypeRel ['flow_id'], MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS );
            }
            $flowConf ['dsp_ads_conf'] = array();
            if($flowAdTypeRel['is_use_dsp']){
                $flowConf ['dsp_ads_conf'] = $this->getAbTestAdsConf($flowAdTypeRel ['flow_id'], MobgiApi_Service_FlowAdsRelModel::DSP_ADS);
                $flowConf ['price'] = $flowAdTypeRel['price'];
            }
            $flowConf ['flow_id'] = $flowAdTypeRel ['id'];
        }
        $this->mUserObject = MobgiApi_Service_AbConfModel::ABTEST_CONF_TYPE;
        return $flowConf;
    }
	

	
	private function checkSdkVersion(){
		$sdkVersion = $this->getInput('sdkVersion');
		if(in_array($this->mAdType, array(1,2))){
			if(version_compare ( $sdkVersion, '0.4.1' ) >= 0){
				return true;
			}
			return false;
		}
		return true;
	}
  
 
    
    private function  getAdsAppRel(){
        // 广告商参数设置信息
        $params ['app_key'] = $this->mAppKey;
        $params ['ad_sub_type'] = $this->mAdType;
        $adsAppRel = MobgiApi_Service_AdsAppRelModel::getsBy ( $params );
        $adsAppRel = Common::resetKey($adsAppRel, 'ads_id');
        //若是白名单方式，则如有配置则直接返回白名单配置的值
        if($this->mWhitelistConfig){
            $adsAppRelWhitelist = MobgiApi_Service_AdsAppRelWhitelistModel::getsBy ( $params );
            if($adsAppRelWhitelist){
                foreach ($adsAppRelWhitelist as $val){
                    $adsAppRel[$val['ads_id']]= $val;
                }
            }
        }
        return $adsAppRel;
    }
    
    /**
     * 整理出广告位广告商的开关状态
     * @param type $adsPosRelInfo
     * @return type
     */
    private function getAdsPosRelState($adsPosRelInfo){
        $adsPosRelState = array();
        if($adsPosRelInfo){
            foreach($adsPosRelInfo as $posKey=>$val){
                foreach($val as $adsId=>$item){
                    $adsPosRelState[$posKey][$adsId] = $item['state'];
                }
            }
        }
        return $adsPosRelState;
    }
    
    private function parseAdsPosRelInfo($adsPosRelInfo){
    	$returnData = array ();
    	if (empty ( $adsPosRelInfo )) {
    	    return $returnData;
    	}
    	foreach ( $adsPosRelInfo as $posKey => $blockInfo ) {
    	    if (array_key_exists ( $posKey, $this->mAppPositonList )) {
    	        $returnData [$posKey] = array (
    	                'rate' => $this->mAppPositonList [$posKey] ['rate'],
    	                'show_limit' => $this->mAppPositonList [$posKey] ['show_limit'],
    	                'pos_name' => $this->mAppPositonList [$posKey] ['pos_name'],
    	                'other_block_id_list' => $blockInfo
    	        );
    	    }
    	}
    	if (empty ( $returnData )) {
    	    return $returnData;
    	}
    	return $returnData;
    	
    }

    private function getAdsPosRel() {
    	// 聚合广告位ID的配置
    	$params ['app_key'] = $this->mAppKey;
    	$params ['ad_sub_type'] = $this->mAdType;
    	$resturnData = array();
    	$adsPosRelResult = MobgiApi_Service_AdsPosRelModel::getsBy ( $params );
    	if($adsPosRelResult){
    	    $retrunData = array();
    	    foreach ($adsPosRelResult as $val){
    	        $retrunData[$val['pos_key']][$val['ads_id']] = array(
    	                'third_party_block_id'=>$val['third_party_block_id'],
    	                'third_party_report_id'=>$val['third_party_report_id'],
    	                'state'=>$val['state']
    	        );
    	    }
    	}
        //若是白名单方式，则如有配置则直接返回白名单配置的值
        if($this->mWhitelistConfig){
            $adsPosRelWhitelistResult = MobgiApi_Service_AdsPosRelWhitelistModel::getsBy ( $params );
            $tmpWhilelist = array();
            if($adsPosRelWhitelistResult){
                foreach ($adsPosRelWhitelistResult as $val){
                    $retrunData[$val['pos_key']][$val['ads_id']]['third_party_block_id'] = $val['third_party_block_id'];
                    $retrunData[$val['pos_key']][$val['ads_id']]['third_party_report_id'] = $val['third_party_report_id'];
                    $retrunData[$val['pos_key']][$val['ads_id']]['state'] = $val['state'];
                }
            }
        }  
    	return $retrunData;
    }

    
    
private function getAppPosInfo($appInfo) {
		$params ['app_id'] = $appInfo ['app_id'];
		$params ['del'] = MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG;
		$appPosInfo = MobgiApi_Service_AdDeverPosModel::getsBy ( $params );
		if (empty ( $appPosInfo )) {
			return false;
		}
		$posTypeDesc = Common_Service_Const::$mAdPosType;
		$returnData = array ();
		foreach ( $appPosInfo as $posInfo ) {
			if ($posInfo ['state'] != MobgiApi_Service_AdDeverPosModel::OPEN_STATUS) {
				continue;
			}
			if ($posInfo ['pos_key_type'] == $posTypeDesc [$this->mAdType]) {
				$returnData [$posInfo ['dever_pos_key']] = array (
						'rate' => $posInfo ['rate'],
						'show_limit' => $posInfo ['limit_num'],
						'pos_name' => $posInfo ['dever_pos_name'] 
				);
			}
		}
		if (empty ( $returnData )) {
			return false;
		}
		return $returnData;
	}

 
	private function getAppInfo() {
		$params ['app_key'] = $this->getInput('appKey');
		$params ['state'] = MobgiApi_Service_AdAppModel::OPEN_STATUS;
		$params ['is_check'] = MobgiApi_Service_AdAppModel::ISCHECK_PASS;
		$appInfo = MobgiApi_Service_AdAppModel::getBy ( $params );
		if (empty ( $appInfo )) {
			$this->output ( Util_ErrorCode::APP_STATE_CHECK, 'app state is close ');
		}
		return $appInfo;
	}
	
	private function getDefaultFlowConfId($flowConfList){
	    if(!is_array($flowConfList)){
	        return false;
	    }
	    $defaultFlowConfId = 0;
	    foreach ($flowConfList as $confInfo){
	        if ($confInfo['conf_type'] == MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE){
	            $defaultFlowConfId = $confInfo['id'];
	            break;
	        }
	    }
	    return $defaultFlowConfId;
	}
	


	
	private function sortArr($arr){
	    $len = count($arr);
	    for ($i = 1; $i < $len; $i++) {
	        $flag = false;    //本趟排序开始前，交换标志应为假
	        for ($k = 0; $k < $len - $i; $k++) {
	            if (!in_array('channel_conf_type', $arr[$k])) {
	                $tmp = $arr[$k + 1];
	                $arr[$k + 1] = $arr[$k];
	                $arr[$k] = $tmp;
	                $flag = true;
	            }
	            if (!in_array('area_conf_type', $arr[$k]) && !in_array('channel_conf_type', $arr[$k])) {
	                $tmp = $arr[$k + 1];
	                $arr[$k + 1] = $arr[$k];
	                $arr[$k] = $tmp;
	                $flag = true;
	            }
	            if (!in_array('game_conf_type', $arr[$k]) && !in_array('channel_conf_type', $arr[$k])&& !in_array('area_conf_type', $arr[$k])) {
	                $tmp = $arr[$k + 1];
	                $arr[$k + 1] = $arr[$k];
	                $arr[$k] = $tmp;
	                $flag = true;
	            }
	     
	         
	        }
	    }
	    return $arr;
	}
	
	private function getFlowIdByUserCondition($flowConfList, $conditionArr,$num){
        if (! is_array ( $flowConfList )) {
            return 0;
        }
        foreach ( $conditionArr as $condition ) {
           if (count ( $condition ) == $num) {
                foreach ($flowConfList as $flowConf){
                    if ($flowConf['conf_num'] == $num) {
                        $flag = 0;
                        foreach ( $condition as $conditionType ) {
                           if($flowConf[$conditionType] && call_user_func_array ( array ( $this, $this->mConditionRelFuntion [$conditionType] ), array ( $flowConf  ) )){
                               $flag = 1;
                           }else{
                               $flag = 0;
                               break;
                           }
                        }
                        if ($flag) {
                            return $flowConf ['id'];
                        }
                    }
                }
            }
        }
        return 0;
    }
	

	
	private function getCustomeFlowConfId($flowConfList){
	    if(!is_array($flowConfList)){
	        return false;
	    }
	    $flowId = 0;
        $len = count($this->mConditionRelFuntion);
        for($i = $len; $i>=1;$i--){
            $conditionArr = Common::combination ( array_keys ( $this->mConditionRelFuntion ), $i );
            $conditionArr = $this->sortArr ( $conditionArr );
            $flowId = $this->getFlowIdByUserCondition ( $flowConfList, $conditionArr, $i );
            if ($flowId) {
                break;
            }
        }
	    return $flowId;
	}
	
	private function getFlowAdTypeRelByFlowId($flowId){
	    $parms['flow_id'] = $flowId;
	    $parms['app_key'] = $this->mAppKey;
	    $parms['ad_type'] = $this->mAdType;
	    $flowAdTypeRel = MobgiApi_Service_FlowAdTypeRelModel::getBy($parms);
	    return $flowAdTypeRel;
	}
	
	private function getAdsConf($flowId, $confType ){
	    $parms['flow_id'] = $flowId;
	    $parms['conf_type'] = $confType;
	    $parms['app_key'] = $this->mAppKey;
	    $parms['ad_type'] = $this->mAdType;
	    $flowAdsRel = MobgiApi_Service_FlowAdsRelModel::getsBy($parms);
	    if($flowAdsRel){
	        $temp = array();
	        foreach ($flowAdsRel as $val){
	            $temp[] = array('current_ads_id'=>$val['ads_id'],
                    	                    'weight'=>strval(floatval($val['weight'])),
                    	                    'position'=>$val['position'],
                    	                    'limit_num'=>$val['limit_num'],
	            );
	        }
	        return $temp;
	    }
	    return array();
	}
	
	public function getAbTestAdsConf($flowId, $confType) {
	    $parms ['flow_id'] = $flowId;
	    $parms ['conf_type'] = $confType;
	    $parms ['ad_type'] = $this->mAdType;
	    $flowAdsRel = MobgiApi_Service_AbFlowAdsRelModel::getsBy ( $parms );
	    if ($flowAdsRel) {
	        $temp = array ();
            foreach ( $flowAdsRel as $val ) {
                $temp [] = array (
                        'current_ads_id' => $val ['ads_id'],
                        'weight' => strval ( floatval ( $val ['weight'] ) ),
                        'position' => $val ['position'],
                        'limit_num' => $val ['limit_num']
                );
            }
	        return $temp;
	    }
	    return array ();
	}

     
	private function getFlowConfIdByUser(){
	    // 获取流量配置列表
	    $params ['app_key'] = $this->mAppKey;
	    $flowConfList = MobgiApi_Service_FlowConfModel::getsBy ( $params );
	    if (empty ( $flowConfList )) {
	        return  0;
	    }
	    $flowId = $this->getCustomeFlowConfId ( $flowConfList );
	    if($this->mIsTest){
	        $this->mDebugInfo['customeFlowConfId'] = $flowId;
	    }
	    if ($flowId) {
	        $flowAdTypeRel = $this->getFlowAdTypeRelByFlowId ( $flowId );
	        if ($flowAdTypeRel && $flowAdTypeRel ['status'] && $flowAdTypeRel ['is_default']) {
	            $flowId = 0;
	        }
	        if ($this->mIsTest) {
	            $this->mDebugInfo ['customeFlowConfIdStaus'] = $flowAdTypeRel ['status'];
	            $this->mDebugInfo ['customeFlowConfIdIsDefault'] = $flowAdTypeRel ['is_default'];
	        }
	    }
	    if (! $flowId) {
	        $flowId = $this->getDefaultFlowConfId ( $flowConfList );
	        if($this->mIsTest){
	            $this->mDebugInfo['defaultFlowConfId'] = $flowId;
	        }
	    }
	    if($flowId){
	        return $flowId;
	    }
	    return  0;
	}
	
	private function getFlowConfListById() {
	    $generalAdsConf = $this->getAdsConf ( $this->mFlowId , MobgiApi_Service_FlowAdsRelModel::GERNERAL_ADS );
	    if(empty($generalAdsConf)){
	        return array();
	    }
	    $flowConf ['general_ads_conf'] = $generalAdsConf;
	    $flowConf ['priority_ads_conf'] = array();
	    if($this->mFlowAdTypeRel['is_priority']){
	        $flowConf ['priority_ads_conf'] = $this->getAdsConf ( $this->mFlowId , MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS );
	    }
	    $flowConf ['flow_id'] = $this->mFlowId ;
	    return $flowConf;
	}	

    
    private function isAndriodPlatform(){
        return $this->getInput('platform') == Common_Service_Const::ANDRIOD_PLATFORM;
    }

    
    
    private function checkPostParams() {
	 	if (empty ( $this->getInput('appKey') ) || strlen ( $this->getInput('appKey') ) < 10) {
			$this->output ( Util_ErrorCode::PARAMS_CHECK, 'appKey is error');
		} 
		if ( !in_array($this->getInput ('adIntegrationType'), array(0,1,2,3,4))) {
			$this->output ( Util_ErrorCode::PARAMS_CHECK, 'adType is error');
		} 
		$this->mIsTest =  $this->getInput('isTest');
	}
 
    private  function fillPriorityAdsConfList($adConf){
		$data = array ();
		if (empty ( $adConf )) {
			return $data;
		}
		foreach ( $adConf as $key => $val ) {
			$data  [$val ['current_ads_id']] = array (
					'current_ads_id' => $val ['current_ads_id'],
                    'index' => $val ['position'],
					'limit_num' => $val ['limit_num'],
			);
		}
		return $data;
	}
    
    private function initIp(){
        $this->ip = $this->getInput('ip')?$this->getInput('ip'):Common::getClientIP();
        if($this->mIsTest){
            $this->mDebugInfo['ip'] = $this->ip;
        }
		$this->mUserAreaInfo = $this->getAreaCacheDataByIp ( $this->ip );
		if($this->mIsTest){
			$this->mDebugInfo['areaInfo'] = $this->mUserAreaInfo;
		}
        return $this->ip;
    }
    
    //安卓的定向配置
    private function getDirectConfig(){
		$label = '';
		if (stristr ( $this->getInput('adsList'), 'BaiduChannel' )) {
			$label = 'BaiduChannel';
		} elseif (stristr ( $this->getInput('adsList'), 'JinliChannel' )) {
			$label = 'JinliChannel';
		} elseif (stristr ( $this->getInput('adsList'), 'MeizuChannel' )) {
			$label = 'MeizuChannel';
		}
		if (! $label) {
			return false;
		}
		$params ['app_key'] = $this->mAppKey;
		$params ['name'] = $label;
		$info = MobgiApi_Service_PolymericAdsModel::getBy ( $params );
		$data = array ();
		if (! empty ( $info )) {
			$adsList [0] = array (
					'rate' => 1,
					'thirdPartyName' => $label,
					'thirdPartyAppkey' => $info ['third_party_appkey'],
					'thirdPartyAppsecret' => $info ['secret_key'],
					'extraInfos' => array () 
			);
			$positionConfig = json_decode ( $info ['position_conf'], true );
			if ($positionConfig ['status']) {
				foreach ( $positionConfig ['status'] as $key => $val ) {
					$adsList [0] ['thirdPartyBlockId'] = $positionConfig ['other_block_id'] [$key];
					if ($val && $positionConfig ['rate'] [$key] && $adsList [0] ['thirdPartyBlockId']) {
						$data [] = array (
								'blockIdName' => $positionConfig ['pos_name'] [$key],
								'blockId' => $positionConfig ['pos_key'] [$key],
								'rate' => $positionConfig ['rate'] [$key],
								'showLimit' => '0',
								'configs' => $adsList 
						);
					}
				}
			}
		}
		$globalConfigData ['supportNetworkType'] = 1;
		$globalConfigData ['lifeCycle'] = 1800000;
		if (empty ( $data )) {
			$this->output ( Util_ErrorCode::CONFIG_EMPTY, 'get DirectConfig list fail' );
		}
		$this->localFormatOutput ( Util_ErrorCode::CONFIG_SUCCESS, 'DirectConfig ok ', $data, $globalConfigData );
	}
    
 
   
    /**
     * 根据游戏ID和用户id获取缓存库的ID
     * @param unknown $gameId
     * @param unknown $userId
     */
    private  function  getCacheByParams($gameId, $userId){
		$hashKey = md5 ( $gameId . '_' . md5 ( $userId ) );
		$cacheResourceType = hexdec ( substr ( $hashKey, 0, 2 ) ) % 3;
		$cacheResource = 'AD_USER_CACHE_REDIS_SERVER' . $cacheResourceType;
		$cacheRedis = Cache_Factory::getCache ( Cache_Factory::ID_REMOTE_REDIS, $cacheResource );
		return $cacheRedis;
	}
    
   
	private function checkUserExistInConf($confInfo){
	   if (empty ( $confInfo ) ) {
			return false;
		}
		$userConf = json_decode($confInfo['user_conf'], true);
		if (empty ( $userConf )) {
		    return false;
		}
		$appInfo = $this->getAppInfo();
	    $gameId = $appInfo ['out_game_id'];
	    $isNewUser = $this->getInput ('isNewUser');
	    $userId = $this->getInput('userId') ;
	    if($gameId && $this->isAndriodPlatform()){
    	    // 过滤30日付费用户
    	    $payUserKey = $gameId . '_' . md5 ( $userId ) . '_payUser';
    	    if (in_array ( self::PAY_USER, $userConf )) {
    	        $redis = $this->getCacheByParams ( $gameId, $userId );
    	        if ($redis->get ( $payUserKey )) {
    	            return true;
    	        }
    	    }
	    }
	    // 新增用户过滤
	    if (in_array ( self::ACTIVE_USER, $userConf )) {
	        if ($isNewUser) {
	            return true;
	        }
	    }
	    return false;
	
	}
	
	/**
	 * 检查系统版本
	 *
	 * @param unknown $fiterConf
	 */
	private function checkSysVersionExistInConf($confInfo,$isJson = true) {
	    return false;
	}
    
    /**
     * 检查渠道
     * @param unknown $fiterConf
     */
    private function checkGameVersionExistInConf($confInfo,$isJson = true){
        if (empty ( $confInfo )) {
            return false;
        }
        if ($isJson) {
            $gameConf = json_decode ( $confInfo ['game_conf'], true );
        } else {
            $gameConf = $confInfo ['game_conf'];
        }
        if (empty ( $gameConf )) {
            return false;
        }
        $gameVersion = $this->getInput ( 'gameVersion' );
        if (in_array ( $gameVersion, $gameConf )) {
            return true;
        }
        return false;
    }
    
    /**
     * 检查渠道
     * @param unknown $fiterConf
     */
    private function checkChannelExistInConf($confInfo,$isJson = true){
		if (empty ( $confInfo )) {
			return false;
		}
          if($isJson){
            $channelConf = json_decode ( $confInfo ['channel_conf'], true );
        }else{
            $channelConf = $confInfo ['channel_conf'];
        }
    	if (empty ( $channelConf )) {
			return false;
		}
		$channelId = $this->getInput('channelId');
		if (in_array ( $channelId, $channelConf )) {
			return true;
		}
		return false;
	}
    
    /**
     * 检查区域
     * @param unknown $fiterConf
     * $type 1 
     */
    private function checkAreaExistInConf($confInfo,$isJson = true){
		if (empty ( $confInfo )) {
			return false;
		}
         if($isJson){
            $areaConfIds = json_decode ( $confInfo ['area_conf'], true );
        }else{
            $areaConfIds = $confInfo ['area_conf'];
        }
		if (empty ( $areaConfIds )) {
		    return false;
		}
		if (empty ( $this->mUserAreaInfo )) {
			return false;
		}
		$findAreaFlag = 0;
		$provinceList = common::getConfig ( 'areaConfig', 'provinceList' );
		foreach ($areaConfIds as $val){
		    if(is_numeric($val)){
		       if (mb_strstr ( $this->mUserAreaInfo  ['province'], $provinceList [$val] ) !== false) {
		            $findAreaFlag = 1;
		            break;
		        }
		    }
		}
		if(!$findAreaFlag){
		    if(in_array($this->mUserAreaInfo['country_code'], $areaConfIds)){
		        $findAreaFlag = 1;
		    }
		}
        if($findAreaFlag){
            return true;
        }
		return false;
	}

	

	private function getAreaCacheDataByIp($ip) {
		if (! $ip) {
			return array ();
		}
		$ipLong = sprintf('%u',ip2long($ip));
		$resource = 'ip_info_'.(($ipLong % 2)+1);
		$cache = Cache_Factory::getCache (Cache_Factory::ID_REMOTE_REDIS,$resource);
		$key = 'ip_' . md5 ( $ip .'_ipinfo' );
		$ipInfo = $cache->get ( $key );
		if ($ipInfo === false) {
		    $ipInfo = Util_IpToCityApi::getIpDetailInfo($ip);
			if ($ipInfo) {
				$cache->set ( $key, $ipInfo, Util_CacheKey::CACHE_KEY_EXPRIE_ONE_DAY );
			}
		}
		return $ipInfo;
	}
	
	private function getFlowPosPolicy(){
	    if(empty($this->mFlowAdTypeRel) || !$this->mFlowAdTypeRel['is_block_policy']){
	        return array();
	    }
	    $parms['flow_id'] =$this->mFlowId;
	    $parms['ad_type'] =$this->mAdType;
	    $result = MobgiApi_Service_FlowPosPolicyRelModel::getsBy($parms);
	    if($result){
	       $tmp = array();
	       foreach ($result as $val){
	           $tmp[$val['pos_key']] = array('rate'=>$val['rate'],
	                   'status'=>$val['status'],
	                   'show_limit'=>$val['limit_num']
	           );
	       }
	       return $tmp;
	    }
	    return array();
	}
	
	private function getFlowAppRel(){
	    if(empty($this->mFlowAdTypeRel) || !$this->mFlowAdTypeRel['is_app_rel']){
	        return array();
	    }
	
	    $parms['flow_id'] =$this->mFlowId;
	    $parms['ad_type'] =$this->mAdType;
	    $result = MobgiApi_Service_FlowAppRelModel::getsBy($parms);
	    if($result){
	        $tmp = array();
	        foreach ($result as $val){
	            $tmp[$val['ads_id']] = array('third_party_app_key'=>$val['third_party_app_key'],
	                    'third_party_secret'=>$val['third_party_secret'],   
	            );
	        }
	        return $tmp;
	    }
	    return array();
	}
	
	private function getFlowPosRel(){
	    if(empty($this->mFlowAdTypeRel) || !$this->mFlowAdTypeRel['is_app_rel']){
	        return array();
	    }
	    $parms['flow_id'] =$this->mFlowId;
	    $parms['ad_type'] =$this->mAdType;
	    $result = MobgiApi_Service_FlowPosRelModel::getsBy($parms);
	
	    if($result){
	        $tmp = array();
	        foreach ($result as $val){
	            $tmp[$val['pos_key']][$val['ads_id']] =$val['third_party_block_id'];
	        }
	        return $tmp;
	    }
	    return array();
	}
	
    /**
     * 填充数据，格式化
     */
    private function fillOutputData($adsAppRelList, $adsWeightList){
		// 广告商列表
		$adsList = $this->getAdsListBySubType ( $this->mAdType );
		$flowPosPolicy = $this->getFlowPosPolicy();
		$flowAppRel = $this->getFlowAppRel();
		$flowPosRel = $this->getFlowPosRel();
		if($this->mIsTest){
		    $this->mDebugInfo['adsList'] = $adsList;
		    $this->mDebugInfo['posAdsList'] = $this->mAdsPosRelState;
		}
		// 组装数据
		foreach ( $this->mAppPositonList as $posKey => $posInfo ) {
		    if(isset($flowPosPolicy[$posKey]['status']) && !$flowPosPolicy[$posKey]['status']){
		        continue;
		    }
			$genernalConfig = $this->formatOutputAdsParams ( $adsWeightList, $adsList, $posInfo, $adsAppRelList, $posKey,$flowAppRel,$flowPosRel );
			$prioritConfig = $this->formatOutputPriorityAdsParams ($adsList, $posInfo, $adsAppRelList, $posKey,$flowAppRel,$flowPosRel  );
            if(empty($genernalConfig) && empty($prioritConfig)){
                continue;
            }
			$data [] = array (
					'blockIdName' => $posInfo ['pos_name'],
					'blockId' => $posKey,
					'rate' =>  isset($flowPosPolicy[$posKey]['rate'])?$flowPosPolicy[$posKey]['rate']:$posInfo ['rate'],
					'showLimit' => isset($flowPosPolicy[$posKey]['show_limit'])?strval($flowPosPolicy[$posKey]['show_limit']):strval($posInfo ['show_limit']),
					'configs' => $genernalConfig,
                    'prioritConfig' => $prioritConfig 
			);
		}
		return $data;
	}
    
    private function formatOutputAdsParams($adsWeightList, $adsList, $posInfo, $adsAppRelList, $posKey, $flowAppRel,$flowPosRel ){
        $tmp = array ();
		foreach ( $adsWeightList as $adsId => $weightList ) {
			if (! array_key_exists ( $adsId, $adsList ) || ! $weightList ['weight']) {
				continue;
			}
			if(!$this->mWhitelistConfig){
			    if($this->mAdsPosRelState[$posKey][$adsId] != MobgiApi_Service_AdsPosRelModel::OPEN_STATE){
			        continue;
			    }
			}
			$thirdPartyBlockId = isset($posInfo ['other_block_id_list'] [$adsId]['third_party_block_id']) ?$posInfo ['other_block_id_list'] [$adsId]['third_party_block_id'] : '';
			if(!empty($flowPosRel)){
			    if(isset($flowPosRel[$posKey] [$adsId]) && $flowPosRel[$posKey] [$adsId]){
			        $thirdPartyBlockId = $flowPosRel[$posKey] [$adsId];
			    }
			}
			$thirdPartyAppkey = isset($adsAppRelList [$adsId]['third_party_app_key']) ? $adsAppRelList [$adsId]['third_party_app_key'] : '';
			if(!empty($flowAppRel)){
			   if(isset($flowAppRel [$adsId]['third_party_app_key']) && $flowAppRel [$adsId]['third_party_app_key']){
			       $thirdPartyAppkey = $flowAppRel [$adsId]['third_party_app_key'];
			   }
			}
			$thirdPartyAppsecret = isset($adsAppRelList [$adsId]['third_party_secret'] )? $adsAppRelList [$adsId]['third_party_secret']  : '';
			if(!empty($flowAppRel)){
			    if(isset($flowAppRel [$adsId]['third_party_secret']) && $flowAppRel [$adsId]['third_party_secret']){
			        $thirdPartyAppsecret = $flowAppRel [$adsId]['third_party_secret'];
			    }
			}
            $extraInfos = $this->getExtraInfos($adsId);
			$tmp [] = array (
					'thirdPartyBlockId' => $thirdPartyBlockId,
					'rate' => $weightList ['weight'],
                    'showNumber'=>intval($weightList ['limit_num']),
					'thirdPartyName' => $adsId,
					'thirdPartyAppkey' => $thirdPartyAppkey,
					'thirdPartyAppsecret' => $thirdPartyAppsecret,
					'extraInfos' => $extraInfos 
			);
		}
		return $tmp;
	}
    
    /**
     * 获取广告商的额外配置
     * @param type $adsId
     * @param type $extra_config
     * @return type
     */
    private function getExtraInfos($adsId){
        $extraInfos = array();
        if($adsId == 'Changxian'){
            $extraInfos['limit_minimum_speed'] = 0;
            $extraInfos['minimum_speed'] = 0;
            $extraInfos['lazy_loading'] = intval($this->mFlowAdTypeRel['is_delay']);
            $extraInfos['lazy_loading_time'] = intval($this->mFlowAdTypeRel['time']);
        }
        if(empty($extraInfos)){
            $extraInfos =  array();
        }
        return $extraInfos;
    }
    
    /**
     * 格式化优先广告商数据
     * @param type $prioritAdsListConfig
     * @param type $adsList
     * @param type $posInfo
     * @param type $adsParamsList
     * @param type $posKey
     * @return type
     */
    private function formatOutputPriorityAdsParams($adsList, $posInfo, $adsAppRelList, $posKey,$flowAppRel,$flowPosRel){
		$tmp = array ();
		if(empty($this->mPriorityAdsList)){
		    return $tmp;
		}
		foreach ( $this->mPriorityAdsList as $adsId => $item ) {
			if (! array_key_exists ( $adsId, $adsList ) || ! $item ['index']) {
				continue;
			}
			if(!$this->mWhitelistConfig){
			    if($this->mAdsPosRelState[$posKey][$adsId] != MobgiApi_Service_AdsPosRelModel::OPEN_STATE){
			        continue;
			    }
			}
			$thirdPartyBlockId = isset($posInfo ['other_block_id_list'] [$adsId]['third_party_block_id'])  ? $posInfo ['other_block_id_list'] [$adsId]['third_party_block_id'] : '';
			if(!empty($flowPosRel)){
			    if(isset($flowPosRel[$posKey] [$adsId]) && $flowPosRel[$posKey] [$adsId]){
			        $thirdPartyBlockId = $flowPosRel[$posKey] [$adsId];
			    }
			}
			$thirdPartyAppkey = isset($adsAppRelList [$adsId]['third_party_app_key']) ? $adsAppRelList [$adsId]['third_party_app_key'] : '';
			if(!empty($flowAppRel)){
			    if(isset($flowAppRel [$adsId]['third_party_app_key']) && $flowAppRel [$adsId]['third_party_app_key']){
			        $thirdPartyAppkey = $flowAppRel [$adsId]['third_party_app_key'];
			    }
			}
			$thirdPartyAppsecret = isset($adsAppRelList [$adsId]['third_party_secret'] )? $adsAppRelList [$adsId]['third_party_secret']  : '';
			if(!empty($flowAppRel)){
			    if(isset($flowAppRel [$adsId]['third_party_secret']) && $flowAppRel [$adsId]['third_party_secret']){
			        $thirdPartyAppsecret = $flowAppRel [$adsId]['third_party_secret'];
			    }
			}
            $extraInfos = $this->getExtraInfos($adsId);
			$tmp [] = array (
					'thirdPartyBlockId' => $thirdPartyBlockId,
					'index' => $item ['index'],
                    'showNumber'=>intval($item ['limit_num']),
					'thirdPartyName' => $adsId,
					'thirdPartyAppkey' => $thirdPartyAppkey,
					'thirdPartyAppsecret' => $thirdPartyAppsecret,
					'extraInfos' => $extraInfos
			);
		}
		return $tmp;
	}

    /**
     * 获取流量配置的权限列表
     * @param unknown $policyConf
     */
    private function getFlowConfWeightList($flowConf){
		// 流量配置的广告商列表
		$flowConfAdsIds = $flowConf ['general_ads_conf'];
		$flowConfAdsIds = $this->getAvailableAdsList($flowConfAdsIds);
		if($this->mIsTest){
		    $this->mDebugInfo['flowConfAdsIds'] = $flowConfAdsIds;
		}
		if (empty ( $flowConfAdsIds )) {
			return false;
		}
		// 获取流量位置列表
		$adsPostionList = $this->getAdsPositonListFromCache ( $flowConfAdsIds, $flowConf );
		$adsPostionList = Common::resetKey($adsPostionList, 'current_ads_id');
		if (empty ( $adsPostionList )) {
			return false;
		}
		return $adsPostionList;
	}


   
    /**
     * 取得广告的位置列表
     */
    private function getAdsPositonListFromCache($flowConfAdsIds, $flowConf){
		
		$cache = Cache_Factory::getCache ();
		$key = 'intergration_postion_list_' . $this->mAppKey . '_' . $this->mAdType . '_' . $flowConf ['flow_id'];
		$cacheData = $cache->get ( $key );
		if (empty ( $cacheData ) || $this->checkArrDiff ( $cacheData, $flowConfAdsIds )) {
			// 当前广告商的位置
			$exprie = 3600;
			$cache->set ( $key, $flowConfAdsIds, $exprie );
			// 保存广告商的位置的日志
			$this->saveAdsPostionLog ( $flowConfAdsIds );
			return $flowConfAdsIds;
		}
		return $cacheData;
	}
	
	private function checkArrDiff($origal, $des) {
		if (empty ( $origal )) {
			return true;
		}
		$origal = Common::resetKey($origal, 'current_ads_id');
		$des = Common::resetKey($des, 'current_ads_id');
		foreach ($des as $key=>$val){
			if(!isset($origal[$key])){
				return true;
			}
			if( ($val['weight'] != $origal[$key]['weight'])||($val['limit_num'] != $origal[$key]['limit_num']) ){
				return true;
			}
		}
		return false;
	}
	private function saveAdsPostionLog($cacheData) {
		if (empty ( $cacheData )) {
			return false;
		}
		$cacheData = Common::resetKey($cacheData, 'current_ads_id');
		$dataArr ['appKey'] = $this->mAppKey;
		$dataArr ['intergrationType'] = $this->mAdType;
		$dataArr ['effectTime'] = date ( 'Y-m-d H:i:s' );
		$dataArr ['adsPositonList'] = json_encode ( $cacheData );
		$cache = Common::getQueue ( 'intergration_position_list' );
		$cache->push ( 'RQ:intergration_position_list', json_encode ( $dataArr ) );
	}
  

     /**
      * 获取配置可用的广告商列表
      * @param unknown $intergrationSubType
      */
    private function getAvailableAdsList($policyConfAdsIds){
		if (is_array ( $policyConfAdsIds )) {
			// 清除掉已经设置位置的广告商
			foreach ( $policyConfAdsIds as $key=>$val ) {
				if (!$val['weight']) {
					unset ( $policyConfAdsIds [$key] );
				}
			}
		}
		return $policyConfAdsIds;
	}

   /**
    * 获取配置的广告商列表
    * @param unknown $intergrationSubType
    */
    private function getAdsListBySubType($adSubType){
		$params ['ad_type'] = 1;
		$params ['del'] = Common_Service_Const::NOT_DELETE_FLAG;
		$result = MobgiApi_Service_AdsListModel::getsBy ( $params );
		$adsList = array ();
		foreach ( $result as $val ) {
			$arr = json_decode ( $val ['ad_sub_type'], true );
			if (in_array ( $adSubType, $arr )) {
				$adsList [$val ['ads_id']] = $val ['name'];
			}
		}
		return $adsList;
	}
    
    
    /**
     * 格式化全局配置信息
     * @param unknown $adsConfList
     */
    private function fillGlobalConfigOutputData(){
        $globalConfig = $this->getGlobalConfig ();
        $data ['supportNetworkType'] = intval ( $globalConfig ['play_network'] );
        $data ['lifeCycle'] = intval ( $globalConfig ['life_cycle'] ) * 1000;
        if ($this->mAdType == Common_Service_Const::SPLASH_AD_SUB_TYPE) {
            $data ['isShowView'] = isset ( $globalConfig ['is_show_view'] ) ? intval ( $globalConfig ['is_show_view'] ) : 0;
            $data ['viewDelay'] = isset ( $globalConfig ['show_view_time'] ) ? intval ( $globalConfig ['show_view_time'] ) * 1000 : 0;
        }
        return $data;
    }
	
   private function getGlobalConfig() {
         // 广告商参数设置信息
        $params ['app_key'] = $this->mAppKey;
        $params ['ad_sub_type'] = $this->mAdType;
        // 若是白名单方式，则如有配置则直接返回白名单配置的值
        if ($this->mWhitelistConfig) {
            $adsAppRelWhitelist = MobgiApi_Service_AdsAppRelWhitelistModel::getBy ( $params );
            if ($adsAppRelWhitelist) {
                return $adsAppRelWhitelist;
            }
        }
        $adsAppRel = MobgiApi_Service_AdsAppRelModel::getBy ( $params );
        if (empty ( $adsAppRel )) {
            return false;
        }
        return $adsAppRel;
    } 

    /**
     * 是否走白名单
     * @return type　值不为空则走白名单
     */
    private function isWhitelist(){
        $deviceId =  $this->getInput('userId');
        $parms['conf_type'] = MobgiApi_Service_AbConfModel::WHILELIST_CONF_TYPE;
        $parms['status'] = MobgiApi_Service_AbConfModel::STATUS_OPEN;
        $parms['start_time'] = array('<=', date('Y-m-d'));
        $parms['end_time'] = array('>=', date('Y-m-d'));
        $relust = MobgiApi_Service_AbConfModel::getsBy($parms);
        $returnData = 0;
        if(!$relust){
            return $returnData;
        }
        $deviceList = array();
        foreach ( $relust as $val ) {
            $content = json_decode ( $val ['content'] );
            foreach ( $content as $device ) {
                $deviceList [$device] = $val ['conf_id'];
                if($device == $deviceId){
                    $this->mDevMode = $val['dev_mode'];
                }
            }
        }
        if(isset($deviceList[$deviceId])){
            $returnData = $deviceList[$deviceId];
            if ($this->mIsTest) {
                $this->mDebugInfo ['whileList'] = $returnData;
            }
        }
        return $returnData;
        
        
    }
    
    /**
     * 随机
     * @param unknown $weightList
     */
    public function  rateWeight($weightList){
        $result = array();
        // 概率数组的总概率精度
        $proSum = array_sum($weightList);
        // 概率数组循环
        foreach ($weightList as $key => $proCur) {
            $randNum = mt_rand(1, $proSum); // 抽取随机数
            if ($randNum <= $proCur) {
                $result = $key; // 得出结果
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset($proArr);
        return $result;
    }
    
    public function getAbTestFlowIdByConfId($confId){
        if(!$confId){
            return false;
        }
        $confRelResult = MobgiApi_Service_AbConfRelModel::getsBy(array('conf_id'=>$confId));
        if(!$confRelResult){
            return false;
        }
        $confRelResult = Common::resetKey($confRelResult, 'flow_id');
        $weightList = array();
        foreach ($confRelResult as $val){
            $weightList[$val['flow_id']] = $val['weight'] *100;
        }
        if(empty($weightList)){
            return false;
        }
        $flowId = $this->rateWeight($weightList);
        $this->mAbtestConRelId = $confRelResult[$flowId]['id'];
        return $flowId;
    }
    /**
     * 获取白名单详细配置
     * @return type
     */
    private function getWhitelistPolicyConfList(){
        if(empty($this->mWhitelistConfig)){
            return false;
        }
        $flowId = $this->getAbTestFlowIdByConfId($this->mWhitelistConfig);
        if ($this->mIsTest) {
            $this->mDebugInfo ['whilelistFlowId'] = $flowId;
        }
        if(!$flowId){
            return false;
        }
        $flowAdTypeRel =MobgiApi_Service_AbFlowAdTypeRelModel::getBy(array('flow_id'=>$flowId,'ad_type'=>$this->mAdType));
        if ($this->mIsTest) {
            $this->mDebugInfo ['whilelistStatus'] = $flowAdTypeRel['status'];
        }
        if(!$flowAdTypeRel['status']){
            return false;
        }
        if($flowAdTypeRel['is_default']){
            $parms ['app_key'] = $this->mAppKey;
            $parms ['conf_type'] = MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE;
            $defaultFlowResult = MobgiApi_Service_FlowConfModel::getBy ( $parms );
            if ($this->mIsTest) {
                $this->mDebugInfo ['whilelistRelDeaultFlowId'] = $defaultFlowResult ['id'];
            }
            if (empty ( $defaultFlowResult )) {
                return false;
            }
            $defaultflowAdTypeRel = MobgiApi_Service_FlowAdTypeRelModel::getBy ( array (
                    'flow_id' => $defaultFlowResult ['id'],
                    'ad_type' => $this->mAdType 
            ) );
            if (! $defaultflowAdTypeRel ['status']) {
                return false;
            }
            if ($this->mIsTest) {
                $this->mDebugInfo ['whilelistRelDeaultStatus'] =  $defaultflowAdTypeRel['status'];
            }
            $generalAdsConf = $this->getAdsConf ( $defaultFlowResult ['id'] , MobgiApi_Service_FlowAdsRelModel::GERNERAL_ADS );
            if (empty ( $generalAdsConf )) {
                return false;
            }
            $flowConf ['general_ads_conf'] = $generalAdsConf;
            $flowConf ['priority_ads_conf'] = array();
            if($defaultflowAdTypeRel['is_priority']){
                $flowConf ['priority_ads_conf'] = $this->getAdsConf ( $defaultFlowResult ['id'], MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS );
            }
            $flowConf ['flow_id'] = $defaultFlowResult ['id'];
            $this->mFlowId = $defaultFlowResult ['id'];
        }else {
            $generalAdsConf = $this->getAbTestAdsConf( $flowAdTypeRel ['flow_id'] , MobgiApi_Service_FlowAdsRelModel::GERNERAL_ADS );
            if (empty ( $generalAdsConf )) {
                return false;
            }
            $flowConf ['general_ads_conf'] = $generalAdsConf;
            $flowConf ['priority_ads_conf'] = array();
            if($flowAdTypeRel['is_priority']){
                $flowConf ['priority_ads_conf'] = $this->getAbTestAdsConf ( $flowAdTypeRel ['flow_id'], MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS );
            }
            $flowConf ['flow_id'] = $flowAdTypeRel ['id'];
        }
        $this->mUserObject = MobgiApi_Service_AbConfModel::WHILELIST_CONF_TYPE;
        return $flowConf;
    }
    
    /**
     * 填充广告商的广告位
     */
    private function parseWeightList($flowConf){
        if(empty($flowConf)){
            return false;
        }
        $gernernalConf = $flowConf['general_ads_conf'];
       if(empty($gernernalConf)){
            return false;
        }
        return Common::resetKey($gernernalConf, 'current_ads_id');
    }
    
    public function getAbTestConf(){
        $params['app_key'] = $this->mAppKey;
        $params['conf_type'] = MobgiApi_Service_AbConfModel::ABTEST_CONF_TYPE;
        $params['status'] = MobgiApi_Service_AbConfModel::STATUS_OPEN;
        $params['start_time'] = array('<=',date('Y-m-d'));
        $params['end_time'] = array('>=',date('Y-m-d'));
        $abTestConf = MobgiApi_Service_AbConfModel::getsBy($params);
        if(empty($abTestConf)){
            return false;
        }
        return Common::resetKey($abTestConf, 'conf_id');
    }
    
    public function getHitAbTestConf(){
        $abTestConf = $this->getAbTestConf ();
        $hitAbConf = array ();
        if(!$abTestConf){
            return $hitAbConf;
        }
        foreach ( $abTestConf as $val ) {
            $content = json_decode ( $val ['content'], true );
            if (empty ( $content )) {
                continue;
            }
            $flag = 0;
            foreach ( $this->mAbConfRelFun as $type => $fun ) {
                if ($content [$type] && call_user_func_array ( array ( $this, $fun), array ($content, false) )) {
                    $flag = 1;
                } elseif ($content [$type] == 0) {
                    $flag = 1;
                } else {
                    $flag = 0;
                    break;
                }
            }
            if ($flag) {
                $hitAbConf [$val ['conf_id']] = $val ['rate'];
                $this->mAbTestConf[$val ['conf_id']] = $val;
            }
        }
        return $hitAbConf;
    }
    
    public function rateAbTestConfId($adTestConf){
        if (empty ( $adTestConf )) {
            return 0;
        }
        // 随机取出一条配置
        $tmpConf = $adTestConf;
        for($i = 1; $i <= 3; $i ++) {
            if (empty ( $tmpConf )) {
                return 0;
            }
            $confIds = array_rand ( $tmpConf );
            unset ( $tmpConf [$confIds] );
            $weightList = array (
                    $confIds => $adTestConf [$confIds],
                    0 => 100 - $adTestConf [$confIds] 
            );
            $rateConfId = $this->rateWeight ( $weightList );
            if ($rateConfId) {
                return $rateConfId;
            }
        }
    }
	
	public function listsAction(){
		if ( empty ( $this->getInput('app_key'))) {
			exit( json_encode(array('error'=>-1, 'msg'=>'get fail')));
		}
		$appKey = $this->getInput ('app_key');
		$dbInfo = MobgiApi_Service_VideoAdsModel::getBy(array (
				'app_key' => $appKey
		) );
		if (! $dbInfo) {
				exit( json_encode(array('error'=>-1, 'msg'=>'get fail')));
		}
		$vacca = json_decode ( $dbInfo ['video_ads_com_conf'], true );
		$ret = array ();
		foreach ( $vacca as $key => $value ) {
			if($value){
				$ret [] = array (
						'platform' => $key,
						'rate' => ( float ) $value
				);
			}
		
		}
		$ret = json_encode ( $ret );
		echo $ret;
		exit();
	}
	
}
