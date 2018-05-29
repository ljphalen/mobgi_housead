<?php
/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-3-17 11:14:29
 * $Id: Ad.php 62100 2017-3-17 11:14:29Z hunter.fang $
 */
if (!defined('BASE_PATH'))
	exit ('Access Denied!');

class Adx_V1_HouseadController extends Adx_Api_V1_BaseController
{

	/**
	 * 获取housead转成dsp的格式.
	 */
	public function getAdInfoAction()
	{

		$this->checkAdPostParam(false);
		$this->initIp();
		$infoArr = $this->getHouseadParamInfo();
		$spArr = $this->getHouseadParamSp();

		// 获取应用的信息
		$this->mAppInfo = MobgiApi_Service_AdAppModel::getAppInfoByAppKey($this->mAppKey);
		if (empty ($this->mAppInfo)) {
			$this->output(Util_ErrorCode::APP_STATE_CHECK, 'app state is close');
		}

		$this->mGlobalConfig = Advertiser_Service_AdAppkeyConfigModel::getStrategyConfig($this->mAppKey, $this->mPlatform);
		if (empty($this->mGlobalConfig)) {
			$this->output(Util_ErrorCode::DSP_STRATEGY_CONFIG_EMPTY, 'StrategyConfig is empty');
		}

		// 获取帐号列表
		$accountIdsListReturn = $this->getAccountList();
		if ($accountIdsListReturn ['code']) {
			$this->output($accountIdsListReturn ['code'], $accountIdsListReturn ['msg']);
		}

		// 处理自有广告信息数据
		$data = $this->processMyselfAdInfoData($infoArr, $spArr);
		// 输出数据
		if ($data ['code']) {
			$this->output($data ['code'], $data ['msg']);
		} else {
			$dspResponse = $this->farmatResponseData($data);
			$this->output(Util_ErrorCode::CONFIG_SUCCESS, 'ok', $dspResponse);
		}
	}

	private function formatMacroLink($link, $deepReplaceParam, $isReturnArray)
	{
		if (empty ($link)) {
			if ($isReturnArray) {
				return array();
			}
			return '';
		}
		$canReplaceMacro = $this->getClientCanUseMacro();
		if (!is_array($link)) {
			$link = $this->replaceMacroLink($canReplaceMacro, $deepReplaceParam, $link);
			if ($isReturnArray) {
				return array(
					$link
				);
			}
			return $link;
		}
		$tempLink = array();
		foreach ($link as $key => $item) {
			if ($item) {
				$tempLink [] = $this->replaceMacroLink($canReplaceMacro, $deepReplaceParam, $item);
			}
		}
		return $tempLink;
	}

	private function replaceMacroLink($canReplaceMacro, $deepReplaceParam, $replaceStr)
	{
		$replaceStr = str_ireplace(
			array(
				'{CPID}',
				'{CRID}',
				'{OS}',
				'{IP}',
				'{UA}',
				'{RequestID}',
				'{AppKey}',
				'{IDFA}',
				'{IMEI}',
				'{Callback}'
			),
			array(
				$deepReplaceParam ['adId'],
				$deepReplaceParam ['originalityId'],
				$this->getOsFromPlatform($this->mPlatform),
				$this->ip,
				$_SERVER ['HTTP_USER_AGENT'],
				$deepReplaceParam ['requestId'],
				$this->mAppKey,
				$this->mPostData ['device'] ['deviceId'],
				$this->mPostData ['device'] ['deviceId'],
				urlencode(common::getWebRoot() . '/api/conversion/postback/?originalityId=' . $deepReplaceParam ['originalityId'] . '&deviceId=' . $this->mPostData ['device'] ['deviceId'] . '&requestId=' . $deepReplaceParam ['requestId'])
			),
			$replaceStr);
		if ($canReplaceMacro) {
			$item = $replaceStr;
		} else {
			$item = str_replace(array(
				'{',
				'}'
			), array(
				'',
				''
			), $replaceStr);
		}
		$item = html_entity_decode($item, ENT_QUOTES);
		return $item;
	}

	/**
	 * platform转os
	 * platform int; recommended 设备操作系统， 例如 2“ios" 1"安卓"
	 * os 0-Android、1-IOS、2-WP、3-Others
	 *
	 * @param type $platform
	 * @return type
	 */
	private function getOsFromPlatform($platform)
	{
		$platform_os_config = array(
			1 => 0,
			2 => 1,
			3 => 2,
			4 => 3
		);
		return intval($platform_os_config [$platform]);
	}

	/**
	 * 判断客户端哪些版本能适应宏定义IOS 2.1.4及以上，android 2.3.0及以上
	 *
	 * @param type $clientVersion
	 * @return boolean
	 */
	private function getClientCanUseMacro()
	{
		$sdkVersion = $this->mPostData ['extra'] ['sdkVersion'];
		if ($this->mPlatform == Common_Service_Const::ANDRIOD_PLATFORM && version_compare($sdkVersion, '0.4.0', '>=')) {
			return true;
		} else if ($this->mPlatform == Common_Service_Const::IOS_PLATFORM && version_compare($sdkVersion, '0.3.0', '>=')) {
			return true;
		}
		return false;
	}

	private function dspOutput($code, $msg = '', $data = array())
	{
		header("Content-type:text/json");
		exit (json_encode(array(
			'ret' => $code,
			'msg' => $msg,
			'data' => $data
		)));
	}

	private function getHouseadParamInfo()
	{
		$info = array();
		$info ['blockId'] = $this->mPostData ['imp'] [0] ['blockId'];
		$info ['appKey'] = $this->mPostData ['app'] ['appKey'];
		$info ['adType'] = $this->mPostData ['adType'];
		return $info;
	}

	private function getHouseadParamSp()
	{
		$tmp = array();
		$tmp ['platform'] = is_null($this->mPostData ['device'] ['platform']) ? '' : $this->mPostData ['device'] ['platform'];
		$tmp ['screenDirection'] = is_null($this->mPostData ['device'] ['screenDirection']) ? '' : $this->mPostData ['device'] ['screenDirection'];
		$tmp ['brand'] = is_null($this->mPostData ['device'] ['brand']) ? '' : $this->mPostData ['device'] ['brand'];
		$tmp ['screenSize'] = is_null($this->mPostData ['device'] ['screenSize']) ? '' : $this->mPostData ['device'] ['screenSize'];
		$tmp ['model'] = is_null($this->mPostData ['device'] ['model']) ? '' : $this->mPostData ['device'] ['model'];
		$tmp ['clientVersion'] = is_null($this->mPostData ['device'] ['version']) ? '' : $this->mPostData ['device'] ['version'];
		$tmp ['systemVertion'] = is_null($this->mPostData ['app'] ['version']) ? '' : $this->mPostData ['app'] ['version'];
		$tmp ['resolution'] = is_null($this->mPostData ['device'] ['resolution']) ? '' : $this->mPostData ['device'] ['resolution'];
		$tmp ['netType'] = is_null($this->mPostData ['device'] ['net']) ? '' : $this->mPostData ['device'] ['net'];
		$tmp ['operator'] = is_null($this->mPostData ['device'] ['operator']) ? '' : $this->mPostData ['device'] ['operator'];
		$tmp ['uuid'] = is_null($this->mPostData ['device'] ['deviceId']) ? '' : $this->mPostData ['device'] ['deviceId'];
		return $tmp;
	}


	private function processMyselfAdInfoData($info, $sp)
	{
		// 可用的创意列表
		$originalityListReturn = $this->getAvailableOriginalityList($info, $sp);
		if ($originalityListReturn ['code']) {
			return $originalityListReturn;
		} else {
			$originalityList = $originalityListReturn ['data'];
		}
		// 可用的广告列表
		$adInfoListReturn = $this->getAvailableAdList($originalityList, $sp);
		if ($adInfoListReturn ['code']) {
			return $adInfoListReturn;
		} else {
			$adInfoList = $adInfoListReturn ['data'];
		}
		// 计算eEcpm的排名
		$eEcpmRankList = $this->calculateAdListEECPMRankList($adInfoList);
		$this->mRequestId = $this->createRequestId();
		// 格式化输出数据
		$data = $this->fillDataToOutData($sp, $adInfoList, $eEcpmRankList);
		return $data;
	}

	private function getAvailableOriginalityList($info, $sp)
	{
		// 查询没有关闭的投放单元
		$unintIds = $this->getUnitConfId();
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['unintIds'] = $unintIds;
		}
		if (empty ($unintIds)) {
			return array(
				'code' => Util_ErrorCode::UNIT_CONFIG_EMPTY,
				'msg' => 'unitConfIds is  empty'
			);
		}
		// 获取创意列表
		$originalityList = $this->getOriginalityList($unintIds);
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['originalityList'] = array_keys($originalityList);
		}
		$originalityList = $this->fiterAppKey($info ['appKey'], $originalityList);
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['fiterAppKeyoriginalityList'] = array_keys($originalityList);
		}
		if (empty ($originalityList)) {
			return array(
				'code' => Util_ErrorCode::ORIGINALITY_LIST_EMPTY,
				'msg' => 'originalityList is by appkey fiter'
			);
		}

		$originalityList = $this->fiterEnbedSize($originalityList);
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['fiterEndbedSizeOriginalityList'] = $originalityList;
		}
		if (empty ($originalityList)) {
			return array(
				'code' => Util_ErrorCode::ORIGINALITY_LIST_EMPTY,
				'msg' => 'no originalityList:fiterBy enbedImageSize'
			);
		}
		return array(
			'code' => 0,
			'msg' => '',
			'data' => $originalityList
		);
	}

	private function fiterEnbedSize($originalityList)
	{

		// 原生广告需求提取上传的图片尺寸与广告位尺寸一致的创意
		if ($this->mAdType == Common_Service_Const::ENBED_AD_SUB_TYPE) {
			foreach ($originalityList as $key => $originality) {
				$upload_content = json_decode($originality ['upload_content'], true);
				$size = $upload_content ['enbed_image_size'];
				if ($size != $this->blockInfo ['size']) {
					unset ($originalityList [$key]);
				}
			}

		}
		return $originalityList;
	}


	private function getUnitConfId()
	{
		$params ['status'] = Dedelivery_Service_UnitConfModel::OPEN_STATUS;
		$params ['del'] = Common_Service_Const::NOT_DELETE_FLAG;
		$params ['account_id'] = array(
			'IN',
			$this->mAccountIdsList
		);
		$result = Dedelivery_Service_UnitConfModel::getsBy($params);
		if ($result) {
			$result = Common::resetKey($result, 'id');
			return array_keys($result);
		}
		return false;
	}

	/**
	 * 获取帐号列表通过广告商
	 *
	 * @param unknown $adsId
	 */
	private function getAccountList()
	{
		$params ['user_type'] = array(
			'NOT IN',
			array(
				Admin_Service_UserModel::DEVERLOPER_USER
			)
		);
		$params ['is_lock'] = Admin_Service_UserModel::NO_LOCKED;
		$restult = Admin_Service_UserModel::getsBy($params);
		if (empty ($restult)) {
			return array(
				'code' => Util_ErrorCode::DSP_ACCOUNT_EMPTY,
				'msg' => 'no accountId'
			);
		}
		$advertiserUid = array_keys(Common::resetKey($restult, 'user_id'));
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['advertiserUid'] = $advertiserUid;
		}
		if (empty ($advertiserUid)) {
			return array(
				'code' => Util_ErrorCode::DSP_ACCOUNT_EMPTY,
				'msg' => 'no accountId'
			);
		}
		$this->mAccountIdsList = $advertiserUid;
		return array(
			'code' => 0,
			'msg' => '',
			'data' => $this->mAccountIdsList
		);
	}

	private function getAvailableAdList($originalityList, $sp)
	{
		// 获取可用广告列表
		$fiterAdListResult = $this->fiterAdList($originalityList, $sp);
		if ($fiterAdListResult ['code']) {
			return array(
				'code' => $fiterAdListResult ['code'],
				'msg' => $fiterAdListResult ['msg']
			);
		} else {
			$availableAdList = $fiterAdListResult ['data'];
		}
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['availableAdList'] = array_keys($availableAdList);
		}
		$adInfoList = $this->fillDataToAdInfoList($originalityList, $availableAdList);
		if (empty ($adInfoList)) {
			return array(
				'code' => Util_ErrorCode::DSP_AD_INFO_EMPTY,
				'msg' => 'no adInfoList:fillDataToAdInfoList'
			);
		}

		return array(
			'code' => 0,
			'msg' => '',
			'data' => $adInfoList
		);
	}

	private function getOriginalityList($unintIds)
	{
		$params ['status'] = Dedelivery_Service_OriginalityRelationModel::OPEN_STATUS;
		$params ['del'] = Common_Service_Const::NOT_DELETE_FLAG;
		$params ['unit_id'] = array(
			'IN',
			$unintIds
		);
		$params ['originality_type'] = $this->mAdType;
		$params ['account_id'] = array(
			'IN',
			$this->mAccountIdsList
		);
		$originalityList = Dedelivery_Service_OriginalityRelationModel::getsBy($params);
		if (empty ($originalityList)) {
			return false;
		}
		if ($originalityList) {
			$originalityList = Common::resetKey($originalityList, 'id');
		}
		return $originalityList;
	}

	private function fiterAppKey($appKey, $originalityList)
	{
		if (!is_array($originalityList)) {
			return false;
		}
		foreach ($originalityList as $key => $val) {
			if ($val ['filter_app_conf']) {
				if (stripos(html_entity_decode($val ['filter_app_conf']), $appKey) !== false) {
					unset ($originalityList [$key]);
				}
			}
		}
		return $originalityList;
	}

	private function fiterAdList($originalityList, $sp)
	{
		if (empty ($originalityList)) {
			return array(
				'code' => Util_ErrorCode::ORIGINALITY_LIST_EMPTY,
				'msg' => 'originalityList is empty'
			);
		}
		// 获取创意关联的广告id
		$adIds = $this->getAdIdsByoriginalityList($originalityList);
		if (empty ($adIds)) {
			return array(
				'code' => Util_ErrorCode::AD_ID_EMPTY,
				'msg' => 'adIds is empty by orgignalityId'
			);
		}
		// 获取关联的广告列表
		$adList = $this->getAdListByAdIds($adIds);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::AD_ID_EMPTY,
				'msg' => 'adList is empty by adIds'
			);
		}
		// app行为定向过滤
		$adList = $this->fiterAppBehavior($adList);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_FITER_CONFIG,
				'msg' => 'no adList:fiterAppBehavior'
			);
		}
		// 游戏兴趣定向
		$adList = $this->fiterInteresting($adList);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_FITER_CONFIG,
				'msg' => 'no adList:fiterInteresting'
			);
		}
		// 付费能力定向
		$adList = $this->fiterPayability($adList);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_FITER_CONFIG,
				'msg' => 'no adList:fiterPayability'
			);
		}
		// 游戏频率定向
		$adList = $this->fiterGamefrequency($adList);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_FITER_CONFIG,
				'msg' => 'no adList:fiterGamefrequency'
			);
		}
		$adList = $this->fiterDateRange($adList);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_DATE_FITER,
				'msg' => 'no adList:fiterDateRange'
			);
		}
		$adList = $this->fiterIp($adList);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_FITER_CONFIG,
				'msg' => 'no adList:fiterIp'
			);
		}
		$adList = $this->fiterPlatform($adList, $sp ['platform']);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_FITER_CONFIG,
				'msg' => 'no adList:fiterPlatform'
			);
		}
		$adList = $this->fiterOperator($adList, $sp ['operator']);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_FITER_CONFIG,
				'msg' => 'no adList:fiterOperator'
			);
		}
		$adList = $this->fiterNetType($adList, $sp ['netType']);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_FITER_CONFIG,
				'msg' => 'no adList:fiterNetType'
			);
		}
		$adList = $this->fiterBrand($adList, $sp ['brand']);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_FITER_CONFIG,
				'msg' => 'no adList:fiterBrand'
			);
		}
		$adList = $this->fiterScreenSize($adList, $sp ['screenSize']);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_FITER_CONFIG,
				'msg' => 'no adList:fiterScreenSize'
			);
		}
		$adList = $this->fiterAccountAmount($adList);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_ACCOUNT_LIMIT,
				'msg' => 'no adList:fiterAccountAmount'
			);
		}
		$adList = $this->fiterUnitAmountLimit($adList);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_UNIT_LIMIT,
				'msg' => 'no adList:fiterUnitAmountLimit'
			);
		}
		$adList = $this->fiterAdInfoAmountLimit($adList);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_ADINFO_AMOUNT_LIMIT,
				'msg' => 'no adList:fiterAdInfoAmountLimit'
			);
		}

		$adList = $this->fiterEnbedSubType($adList); // （预留）
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_FITER_CONFIG,
				'msg' => 'no adList:fiterEnbedSubType'
			);
		}
		// 服务端频次控制
		$adList = $this->fiterFrequency($adList);
		if (empty ($adList)) {
			return array(
				'code' => Util_ErrorCode::DSP_FREQUENCY_LIMIT,
				'msg' => 'no adInfoList:fiterFrequency'
			);
		}
		return array(
			'code' => 0,
			'msg' => '',
			'data' => $adList
		);
	}

	/**
	 * 只针对原生广告进行广告子类型的过滤(预留)
	 *
	 * @param type $adList
	 * @return boolean
	 */
	private function fiterEnbedSubType($adList)
	{
		if (empty ($adList)) {
			return false;
		}
		if ($this->mAdType == Common_Service_Const::ENBED_AD_SUB_TYPE) {
			foreach ($adList as $key => $val) {
				if ($val ['ad_sub_type'] != $this->mAdSubType) {
					unset ($adList [$key]);
				}
			}
		}
		return $adList;
	}

	/**
	 * 广告频次控制
	 *
	 * @param type $adList
	 * @return boolean
	 */
	private function fiterFrequency($adInfoList)
	{
		if (empty ($adInfoList)) {
			return false;
		}
		$today = date("Ymd");
		foreach ($adInfoList as $key => $adInfo) {
			$cache = Cache_Factory::getCache();
			if ($adInfo ['frequency_type'] && $adInfo ['frequency']) {
				if (empty ($this->mUuid)) {
					continue;
				}
				if ($adInfo ['frequency_type'] == 'ad') {
					// 格式：fiterfrequency_ad_日期_广告id_设备ID(imei/idfa) ex. fiterfrequency_ad_20170714_147_867348026517826
					$frequencyKey = "fiterfrequency_" . $adInfo ['frequency_type'] . "_" . $today . "_" . $adInfo ['ad_id'] . "_" . $this->mUuid;
					$cacheFrequency = $cache->get($frequencyKey);
					if (intval($cacheFrequency) >= $adInfo ['frequency']) {
						unset ($adInfoList [$key]);
					}
				} else if ($adInfo ['frequency_type'] == 'originality') {
					// 格式：fiterfrequency_originality_日期_创意id_设备ID(imei/idfa) ex. fiterfrequency_originality_20170714_231_867348026517826
					$frequencyKey = "fiterfrequency_" . $adInfo ['frequency_type'] . "_" . $today . "_" . $adInfo ['id'] . "_" . $this->mUuid;
					$cacheFrequency = $cache->get($frequencyKey);
					if (intval($cacheFrequency) >= $adInfo ['frequency']) {
						unset ($adInfoList [$key]);
					}
				}
			}
		}
		return $adInfoList;
	}

	/**
	 * app行为定向过滤
	 *
	 * @param type $adList
	 * @return boolean
	 */
	private function fiterAppBehavior($adList)
	{
		if (empty ($adList)) {
			return false;
		}
		if (in_array($this->mAdType, array(
			Common_Service_Const::VIDEO_AD_SUB_TYPE,
			Common_Service_Const::PIC_AD_SUB_TYPE,
		))) {
			$fiterKey = $this->mAppKey;
		} else {
			$fiterKey = $this->mPostData ['imp'] [0] ['blockId'];
		}
		foreach ($adList as $key => $val) {
			if (empty ($val ['direct_config'])) {
				continue;
			}
			$directConfig = json_decode($val ['direct_config'], true);
			if ($directConfig ['app_behavior_type'] && is_array($directConfig ['app_behavior_range'])) {
				if (isset ($directConfig ['app_behavior_range']) && !in_array($fiterKey, $directConfig ['app_behavior_range'])) {
					unset ($adList [$key]);
				}
			}
		}
		return $adList;
	}

	/**
	 * 游戏兴趣定向
	 *
	 * @param type $adList
	 * @return boolean
	 */
	private function fiterInteresting($adList)
	{
		if (empty ($adList)) {
			return false;
		}
		// todo
		// 特殊处理，地铁跑库和神庙逃亡2需要处理定向
		if (in_array($this->mAppKey, $this->mUseDirectAppkey)) {
			foreach ($adList as $key => $val) {
				if (empty ($val ['direct_config'])) {
					continue;
				}
				$directConfig = json_decode($val ['direct_config'], true);
				if ($directConfig ['interest_direct_type']) {
					$directDeviceInfo = Dedelivery_Service_DeviceDirectModel::getBy(array(
						'imei' => $this->mUuid,
						'appkey' => $this->mAppKey
					));
					if (empty ($directDeviceInfo)) {
						unset ($adList [$key]);
						continue;
					}
					// 若用户兴趣与广告的定向没有交集，则过滤掉这则广告
					$appInterest = explode(',', $directDeviceInfo ['app_interest']);
					if (!array_intersect($appInterest, $directConfig ['interest_direct_range'])) {
						unset ($adList [$key]);
						continue;
					}
				}
			}
		}
		return $adList;
	}

	/**
	 * 付费能力定向
	 *
	 * @param type $adList
	 * @return boolean
	 */
	private function fiterPayability($adList)
	{
		if (empty ($adList)) {
			return false;
		}
		// 特殊处理，地铁跑库和神庙逃亡2需要处理定向
		if (in_array($this->mAppKey, $this->mUseDirectAppkey)) {
			foreach ($adList as $key => $val) {
				if (empty ($val ['direct_config'])) {
					continue;
				}
				$directConfig = json_decode($val ['direct_config'], true);
				if ($directConfig ['pay_ability_type']) {
					$directDeviceInfo = Dedelivery_Service_DeviceDirectModel::getBy(array(
						'imei' => $this->mUuid,
						'appkey' => $this->mAppKey
					));
					if (empty ($directDeviceInfo)) {
						unset ($adList [$key]);
						continue;
					}
					// 若用户兴趣与广告的定向没有交集，则过滤掉这则广告
					if (!in_array($directDeviceInfo ['pay_ability'], $directConfig ['pay_ability_range'])) {
						unset ($adList [$key]);
						continue;
					}
				}
			}
		}
		return $adList;
	}

	/**
	 * 游戏频率定向
	 *
	 * @param type $adList
	 * @return boolean
	 */
	private function fiterGamefrequency($adList)
	{
		if (empty ($adList)) {
			return false;
		}
		// 特殊处理，地铁跑库和神庙逃亡2需要处理定向
		if (in_array($this->mAppKey, $this->mUseDirectAppkey)) {
			foreach ($adList as $key => $val) {
				if (empty ($val ['direct_config'])) {
					continue;
				}
				$directConfig = json_decode($val ['direct_config'], true);
				if ($directConfig ['game_frequency_type']) {
					$directDeviceInfo = Dedelivery_Service_DeviceDirectModel::getBy(array(
						'imei' => $this->mUuid,
						'appkey' => $this->mAppKey
					));
					if (empty ($directDeviceInfo)) {
						unset ($adList [$key]);
						continue;
					}
					// 若用户兴趣与广告的定向没有交集，则过滤掉这则广告
					if (!in_array($directDeviceInfo ['game_frequency'], $directConfig ['game_frequency_range'])) {
						unset ($adList [$key]);
						continue;
					}
				}
			}
		}
		return $adList;
	}

	private function getAdIdsByoriginalityList($originalityList)
	{
		$adIds = array();
		foreach ($originalityList as $val) {
			if (!in_array($val ['ad_id'], $adIds)) {
				$adIds [] = $val ['ad_id'];
			}
		}
		return $adIds;
	}

	private function getAdListByAdIds($adIds)
	{
		$params ['del'] = Common_Service_Const::NOT_DELETE_FLAG;
		$params ['status'] = Dedelivery_Service_AdConfListModel::OPEN_STATUS;
		$params ['id'] = array(
			'IN',
			$adIds
		);
		$params ['account_id'] = array(
			'IN',
			$this->mAccountIdsList
		);
		$params ['ad_target_type'] = array(
			'IN',
			array(
				$this->mPlatform,
				3
			)
		);
		$adList = Dedelivery_Service_AdConfListModel::getsBy($params);
		if ($adList) {
			$adList = Common::resetKey($adList, 'id');
		}
		return $adList;
	}

	private function fiterDateRange($adList)
	{
		if (empty ($adList)) {
			return false;
		}
		$currentDate = strtotime(date('Y-m-d'));
		$currentTime = date('H:i');
		foreach ($adList as $key => $val) {
			$dateRange = json_decode($val ['date_range'], true);
			$startDate = strtotime($dateRange ['start_date']);
			$endDate = strtotime($dateRange ['end_date']);
			// 比较日期
			if (!(($currentDate >= $startDate) && ($currentDate <= $endDate))) {
				unset ($adList [$key]);
			}
			// 比较时间段
			if ($val ['time_type']) {
				$timeRange = json_decode($val ['time_range'], true);
				// 0指定时间段快捷设置
				if (empty ($val ['hour_set_type'])) {
					$startTime = $timeRange ['start_time'];
					$endTime = $timeRange ['end_time'];
					if (!(strcmp($currentTime, $startTime) >= 0 && strcmp($currentTime, $endTime) <= 0)) {
						unset ($adList [$key]);
					}
				}                 // 1指定时间段高级设置
				else {
					$index = common::get_cur_timeseries_index();
					if (empty ($val ['time_series'] [$index])) {
						unset ($adList [$key]);
					}
				}
			}
		}
		return $adList;
	}

	private function fiterPlatform($adList, $platform)
	{
		if (empty ($adList)) {
			return false;
		}
		foreach ($adList as $key => $val) {
			if (empty ($val ['direct_config'])) {
				continue;
			}
			$directConfig = json_decode($val ['direct_config'], true);
			if ($directConfig ['os_direct_type'] && $platform != $directConfig ['os_direct_type']) {
				unset ($adList [$key]);
			}
		}
		return $adList;
	}

	private function fiterOperator($adList, $operator)
	{
		if (empty ($adList)) {
			return false;
		}
		foreach ($adList as $key => $val) {
			if (empty ($val ['direct_config'])) {
				continue;
			}
			$directConfig = json_decode($val ['direct_config'], true);
			if ($directConfig ['operator_direct_type'] && !in_array($operator, $directConfig ['operator_direct_range'])) {
				unset ($adList [$key]);
			}
		}
		return $adList;
	}

	private function fiterNetType($adList, $netType)
	{
		if (empty ($adList)) {
			return false;
		}
		foreach ($adList as $key => $val) {
			if (empty ($val ['direct_config'])) {
				continue;
			}
			$directConfig = json_decode($val ['direct_config'], true);
			if ($directConfig ['network_direct_type'] && !in_array($netType, $directConfig ['network_direct_range'])) {
				unset ($adList [$key]);
			}
		}
		return $adList;
	}

	private function fiterBrand($adList, $brand)
	{
		if (empty ($adList)) {
			return false;
		}
		// 查找客户端的上报的品牌找到对应的索引
		$brandList = Common::getConfig('deliveryConfig', 'brandList');
		$findIndex = -1;
		foreach ($brandList as $key => $val) {
			if ((stripos($val, $brand) !== false) || (stripos($brand, $val) !== false)) {
				$findIndex = $key;
				break;
			}
		}
		foreach ($adList as $key => $val) {
			if (empty ($val ['direct_config'])) {
				continue;
			}
			$directConfig = json_decode($val ['direct_config'], true);
			if ($directConfig ['brand_direct_type'] && !in_array($findIndex, $directConfig ['brand_direct_range'])) {
				unset ($adList [$key]);
			}
		}
		return $adList;
	}

	private function fiterScreenSize($adList, $screenSize)
	{
		if (empty ($adList)) {
			return false;
		}
		$screenSize = $this->transformScreensize($screenSize);
		foreach ($adList as $key => $val) {
			if (empty ($val ['direct_config'])) {
				continue;
			}
			$directConfig = json_decode($val ['direct_config'], true);
			if ($directConfig ['screen_direct_type'] && !in_array($screenSize, $directConfig ['screen_direct_range'])) {
				unset ($adList [$key]);
			}
		}
		return $adList;
	}

	private function transformScreensize($screenSize)
	{
		// 'screenList'=>array(1=>'微屏',2=>'小屏',3=>'中屏',4=>'大屏','5'=>'其它'),
		// 微（2.4英寸以下）、小（2.4~3.2英寸）、中（3.0~4.0英寸）、大屏（4.0英寸+）or未识别
		if ($screenSize == '-1') {
			$screen = 5;
		} elseif (strcmp('2.4', $screenSize) <= 0) {
			$screen = 1;
		} elseif (strcmp('2.4', $screenSize) > 0 && strcmp('3.2', $screenSize) <= 0) {
			$screen = 2;
		} elseif (strcmp('3.2', $screenSize) > 0 && strcmp('4.0', $screenSize) <= 0) {
			$screen = 3;
		} elseif (strcmp('4.0', $screenSize) > 0) {
			$screen = 4;
		}
		return $screen;
	}

	private function fiterAccountAmount($adList)
	{
		if (empty ($adList)) {
			return false;
		}
		$accountIds = array_keys(Common::resetKey($adList, 'account_id'));
		$this->mAccountAmountList = Advertiser_Service_AccountDetailModel::getAccountAmountList($accountIds);
		$this->mAccountTodayConsumeAmount = Advertiser_Service_AccountDayConsumeModel::getAccountTodayConsumeTotalAmountList($accountIds);
		$this->mAccountDayAmountLimitList = Advertiser_Service_AccountConsumptionLimitModel::getAccountDayAmountLimitList($accountIds);
		foreach ($adList as $key => $val) {
			if ($this->mAccountAmountList [$val ['account_id']] ['totalBalance'] <= 0) {
				unset ($adList [$key]);
			}
			if ($this->mAccountDayAmountLimitList [$val ['account_id']] ['consumeLimit'] < $val ['price']) {
				unset ($adList [$key]);
			}
			$limitAmount = $this->mAccountDayAmountLimitList [$val ['account_id']] ['consumeLimit'] - $this->mAccountTodayConsumeAmount [$val ['account_id']] ['consumeAmount'];
			if ($limitAmount <= 0) {
				unset ($adList [$key]);
				$this->saveAccountLimitToCache($key, $this->mAccountDayAmountLimitList [$val ['account_id']] ['consumeLimit'], $this->mAccountTodayConsumeAmount [$val ['account_id']] ['consumeAmount']);
			}
		}
		return $adList;
	}

	private function saveAccountLimitToCache($adId, $consumeLimit, $consumeAmount)
	{
		$cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS, 'adx_default');
		$key = 'account_limit_' . date('Y-m-d') . '_' . $adId;
		if ($cache->exists($key)) {
			return false;
		}
		$data['consumeLimit'] = $consumeLimit;
		$data['consumeAmount'] = $consumeAmount;
		$data['dateTime'] = date('Y-m-d H:i:s');
		$cache->set($key, $data, 259200);
	}

	private function fiterAdInfoAmountLimit($adList)
	{
		if (empty ($adList)) {
			return false;
		}

		$adIds = array_keys($adList);
		// 广告活动日限额,广告活动今日消耗
		$this->mAdInfoDayLimitAmountList = Dedelivery_Service_AdConfListModel::getAdInfoDayLimitAmountList($adList);
		$this->mAdInfoTodayCosumeAmountList = Advertiser_Service_UnitDayConsumeModel::getAdInfoTodayConsumeAmountList($adIds);

		foreach ($adList as $key => $val) {
			if ($this->mAdInfoDayLimitAmountList [$val ['id']] <= 0) {
				unset ($adList [$key]);
			}
			if ($this->mAdInfoDayLimitAmountList [$val ['id']] < $val ['price']) {
				unset ($adList [$key]);
			}
			$limitAmount = $this->mAdInfoDayLimitAmountList [$val ['id']] * 0.8 - $this->mAdInfoTodayCosumeAmountList [$val ['id']] ['amount'];
			if ($limitAmount <= 0) {
				unset ($adList [$key]);
				$this->saveAdLimitToCache($val ['id'], $this->mAdInfoDayLimitAmountList [$val ['id']], $this->mAdInfoTodayCosumeAmountList [$val ['id']] ['amount']);
			}
		}
		return $adList;
	}


	private function fiterUnitAmountLimit($adList)
	{
		if (empty ($adList)) {
			return false;
		}

		// 投放单元日限额,投放单元今日消耗
		$unitIds = $this->getfieldIdsByAdInfoList($adList, 'unit_id', array(
			Common_Service_Const::CHARGE_TYPE_CPM,
			Common_Service_Const::CHARGE_TYPE_CPC
		));
		$this->mUnitDayLimitAmountList = Dedelivery_Service_UnitConfModel::getUnitDayLimitAmountList($unitIds);
		$this->mUnitTodayCosumeAmountList = Advertiser_Service_UnitDayConsumeModel::getUnitTodayConsumeAmountList($unitIds);

		foreach ($adList as $key => $val) {
			if ($this->mUnitDayLimitAmountList [$val ['unit_id']] <= 0) {
				unset ($adList [$key]);
			}
			if ($this->mUnitDayLimitAmountList [$val ['unit_id']] < $val ['price']) {
				unset ($adList [$key]);
			}
			$limitAmount = $this->mUnitDayLimitAmountList [$val ['unit_id']] * 0.8 - $this->mUnitTodayCosumeAmountList [$val ['unit_id']] ['amount'];
			if ($limitAmount <= 0) {
				unset ($adList [$key]);
				$this->saveUnitLimitToCache($val ['unit_id'], $this->mUnitDayLimitAmountList [$val ['unit_id']], $this->mUnitTodayCosumeAmountList [$val ['unit_id']] ['amount']);
			}
		}
		return $adList;
	}

	private function saveUnitLimitToCache($unitId, $consumeLimit, $consumeAmount)
	{
		$cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS, 'adx_default');
		$key = 'unit_limit_' . date('Y-m-d') . '_' . $unitId;
		if ($cache->exists($key)) {
			return false;
		}
		$data['consumeLimit'] = $consumeLimit;
		$data['consumeAmount'] = $consumeAmount;
		$data['dateTime'] = date('Y-m-d H:i:s');
		$cache->set($key, $data, 259200);
	}

	private function saveAdLimitToCache($adId, $consumeLimit, $consumeAmount)
	{
		$cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS, 'adx_default');
		$key = 'unit_limit_' . date('Y-m-d') . '_' . $adId;
		if ($cache->exists($key)) {
			return false;
		}
		$data['consumeLimit'] = $consumeLimit;
		$data['consumeAmount'] = $consumeAmount;
		$data['dateTime'] = date('Y-m-d H:i:s');
		$cache->set($key, $data, 259200);
	}

	private function getfieldIdsByAdInfoList($adInfoList, $field, $chargeType)
	{
		$returnResult = array();
		foreach ($adInfoList as $key => $val) {
			if (!is_array($chargeType)) {
				if ($val ['charge_type'] == $chargeType) {
					$returnResult [$val [$field]] = $val [$field];
				}
			} else {
				if (in_array($val ['charge_type'], $chargeType)) {
					$returnResult [$val [$field]] = $val [$field];
				}
			}
		}
		return $returnResult;
	}


	private function fillDataToAdInfoList($originalityList, $availableAdList)
	{
		if (empty ($originalityList) || empty ($availableAdList)) {
			return false;
		}
		// 帅选出广告信息列表
		$adIds = array_keys($availableAdList);
		$adInfoList = array();
		foreach ($originalityList as $key => $val) {
			if (in_array($val ['ad_id'], $adIds)) {
				$adInfoList [$key] = $val;
				$adInfoList [$key] ['ad_target_type'] = $availableAdList [$val ['ad_id']] ['ad_target_type'];
				$adInfoList [$key] ['ad_target'] = $availableAdList [$val ['ad_id']] ['ad_target'];
				$adInfoList [$key] ['package_name'] = $availableAdList [$val ['ad_id']] ['package_name'];
				$adInfoList [$key] ['ad_name'] = $availableAdList [$val ['ad_id']] ['ad_name'];
				$adInfoList [$key] ['unit_id'] = $availableAdList [$val ['ad_id']] ['unit_id'];
				$adInfoList [$key] ['price'] = $availableAdList [$val ['ad_id']] ['price'];
				$adInfoList [$key] ['charge_type'] = $availableAdList [$val ['ad_id']] ['charge_type'];
				$adInfoList [$key] ['jump_type'] = $availableAdList [$val ['ad_id']] ['jump_type'];
				$adInfoList [$key] ['imp_trackers'] = json_decode($availableAdList [$val ['ad_id']] ['imp_trackers']);
				$adInfoList [$key] ['click_trackers'] = json_decode($availableAdList [$val ['ad_id']] ['click_trackers']);
				$adInfoList [$key] ['app_name'] = $availableAdList [$val ['ad_id']] ['app_name'];
				$adInfoList [$key] ['originality_title'] = $val ['title'];
				$adInfoList [$key] ['originality_desc'] = $val ['desc'];
				$adInfoList [$key] ['deeplink'] = $availableAdList [$val ['ad_id']] ['deeplink'];
				$adInfoList [$key] ['frequency_type'] = $availableAdList [$val ['ad_id']] ['frequency_type'];
				$adInfoList [$key] ['frequency'] = $availableAdList [$val ['ad_id']] ['frequency'];
			}
		}
		return $adInfoList;
	}

	/**
	 * 1）若创意计费类型=CPM，则eCPM=CPM*1/(1+e^(-0.1*x))*创意权重，x=1000*min（账户总余额，账户日限额-账户今日消耗，投放单元日限额-投放单元今日消耗）/CPM
	 * 2) 若创意计费类型=CPC，则eCPM= 1000*CPC*x1*1/(1+e^(-0.1*x2))*1/(1+0.04*x3)*创意权重
	 * X1= CTR（近30天）*0.03+CTR（近7天）*0.07+CTR（近3天）*0.1+CTR（昨天）*0.4+CTR（当日实时）*0.4。若无历史点击率（新创意），视频创意的x1用0.5%代替，插页创意的x1用20%代替
	 * X2=min（账户总余额，账户日限额-账户今日消耗，投放单元日限额-投放单元今日消耗）/CPC
	 * X3=（创意的人均曝光次数 总曝光次数/总人数，过去七天）
	 */
	private function calculateAdListEECPMRankList($adInfoList)
	{
		if (empty ($adInfoList))
			return false;
		$eEpcmList = array();
		$this->calculateAdInfoData($adInfoList);
		foreach ($adInfoList as $key => $val) {
			$accoutTotalBalance = $this->mAccountAmountList [$val ['account_id']] ['totalBalance'];
			$acountLimitAmount = $this->mAccountDayAmountLimitList [$val ['account_id']] ['consumeLimit'] - $this->mAccountTodayConsumeAmount [$val ['account_id']] ['consumeAmount'];
			$unitAmount = $this->mUnitDayLimitAmountList [$val ['unit_id']] - $this->mUnitTodayCosumeAmountList [$val ['unit_id']] ['amount'];
			$minAmount = min($accoutTotalBalance, $acountLimitAmount, $unitAmount);
			if (intval($minAmount) <= 0) {
				$minAmount = 0;
			}
			if ($val ['charge_type'] == Common_Service_Const::CHARGE_TYPE_CPM) {
				// x=1000*min（账户总余额，账户日限额-账户今日消耗，投放单元日限额-投放单元今日消耗）/CPM eCPM=CPM*1/(1+e^(-0.1*x))*创意权重
				$params1 = -(1000 * $minAmount) / $val ['price'] * 0.1;
				$eCPM = $val ['price'] * 1 / (1 + exp($params1)) * $val ['weight'];
				$eEpcmList [$key] = $eCPM;
			} elseif ($val ['charge_type'] == Common_Service_Const::CHARGE_TYPE_CPC) {
				// / 2) 若创意计费类型=CPC，则eCPM= 1000*CPC*x1*1/(1+e^(-0.1*x2))*1/(1+0.04*x3)*创意权重
				// / X1= CTR（近30天）*0.03+CTR（近7天）*0.07+CTR（近3天）*0.1+CTR（昨天）*0.4+CTR（当日实时）*0.4。若无历史点击率（新创意），视频创意的x1用0.05代替，插页创意的x1用0.08代替
				// / X2=min（账户总余额，账户日限额-账户今日消耗，投放单元日限额-投放单元今日	消耗）/CPC
				// / X3=（创意的人均曝光次数 总曝光次数/总人数，过去七天）

				$params1 = $this->mThirtyDayCTR [$val ['id']] ['CTR'] * 0.03 + $this->mSevenDayCTR [$val ['id']] ['CTR'] * 0.07 + $this->mThreeDayCTR [$val ['id']] ['CTR'] * 0.1 + $this->mYesterdayCTR [$val ['id']] ['CTR'] * 0.4 + $this->todayCTR [$val ['id']] ['CTR'] * 0.4;
				$params2 = -($minAmount) / $val ['price'] * 0.1;
				$params3 = $this->mSevenAverageExposureRate [$val ['id']] ['AER'] ? $this->mSevenAverageExposureRate [$val ['id']] ['AER'] : 0;
				$eCPM = 1000 * $val ['price'] * $params1 * 1 / (1 + exp($params2)) * 1 / (1 + 0.04 * $params3) * $val ['weight'];
				$eEpcmList [$key] = $eCPM;
			}
		}
		// 下发广告个数
		$this->mEcpmRankCount = count($eEpcmList);
		// 当下发的广告只有一个直接下发
		if ($this->mEcpmRankCount == 1) {
			if ($this->isDebugMode()) {
				$this->mDebugInfo ['epcmList'] = $eEpcmList;
			}
			return $eEpcmList;
		}
		// 保存一些调试信息
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['beforeRankEcmpList'] = $eEpcmList;
		}
		// 排序
		arsort($eEpcmList);

		if ($this->isDebugMode()) {
			$this->mDebugInfo ['afterRankEcmpList'] = $eEpcmList;
		}
		// 排名之后的id
		$epcmIds = array_keys($eEpcmList);
		$positon = 0;
		foreach ($eEpcmList as $key => $val) {
			$positon++;
			// 最后一个位置的，则取当前ecpm
			if ($positon == $this->mEcpmRankCount) {
				$returnData [$key] = sprintf("%.4f", $eEpcmList [$epcmIds [$positon - 1]]);
			} else {
				$returnData [$key] = sprintf("%.4f", $eEpcmList [$epcmIds [$positon]]);
			}
		}
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['epcmList'] = $returnData;
		}

		// 处理ecpm排名数据
		$randEcpmRank = $this->randEcpmRank($returnData);

		// 输出一些调试信息
		if ($this->isDebugMode()) {

			$this->mDebugInfo ['randEcpmRank'] = $randEcpmRank;
		}
		// 交叉推广取6条广告下发
		if ($this->mAdType == Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
			$data = array_slice($randEcpmRank, 0, 6, true);
		} else {
			$data = array_slice($randEcpmRank, 0, 2, true);
		}
		return $data;
	}

	private function calculateAdInfoData($adInfoList)
	{
		// 要计算的创意id
		$originalityIds = $this->getfieldIdsByAdInfoList($adInfoList, 'id', Common_Service_Const::CHARGE_TYPE_CPC);
		// 获取30天的CTR
		$thirtyDay = MobgiCharge_Service_AdxChargeDayModel::getFormatDate(Common_Service_Const::THIRTY_DAY);
		$this->mThirtyDayCTR = MobgiCharge_Service_AdxChargeDayModel::getSomedayCTR($adInfoList, $originalityIds, $thirtyDay);
		// 七天
		$sevenDay = MobgiCharge_Service_AdxChargeDayModel::getFormatDate(Common_Service_Const::SEVEN_DAY);
		$this->mSevenDayCTR = MobgiCharge_Service_AdxChargeDayModel::getSomedayCTR($adInfoList, $originalityIds, $sevenDay);
		// 三天
		$threeDay = MobgiCharge_Service_AdxChargeDayModel::getFormatDate(Common_Service_Const::THREE_DAY);
		$this->mThreeDayCTR = MobgiCharge_Service_AdxChargeDayModel::getSomedayCTR($adInfoList, $originalityIds, $threeDay);
		// 昨天
		$oneDay = MobgiCharge_Service_AdxChargeDayModel::getFormatDate(Common_Service_Const::ONE_DAY);
		$this->mYesterdayCTR = MobgiCharge_Service_AdxChargeDayModel::getSomedayCTR($adInfoList, $originalityIds, $oneDay);
		// 今天实时的
		$this->todayCTR = MobgiCharge_Service_AdxChargeDayModel::getTodayCTR($adInfoList, $originalityIds);
		// 七天的人均曝光
		$this->mSevenAverageExposureRate = MobgiCharge_Service_AdxChargeDayModel::getSomedayAverageExposureRate($adInfoList, $originalityIds, $sevenDay);
	}

	/**
	 * 随机相同价格的创意
	 *
	 * @param unknown $epcmList
	 */
	private function randEcpmRank($epcmList)
	{
		// 交叉推广需要6条(6条暂不处理ecpm的相同价格的随机排序)
		if ($this->mAdType == Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
			arsort($epcmList);
			return $epcmList;
		} else {
			$epcmListKeys = array_keys($epcmList);
			// 价格相同随机展示其创意
			if ($epcmList [$epcmListKeys [0]] == $epcmList [$epcmListKeys [1]]) {
				// 计算每个值相等的个数
				$countValues = array_count_values($epcmList);
				// 相同keys的个数
				$sameKeyNum = $countValues [$epcmList [$epcmListKeys [0]]];
				// 截取相同的要随机的数组
				$sliceArr = array_slice($epcmList, 0, $sameKeyNum, true);
				$firstRandKey = array_rand($sliceArr);
				unset ($sliceArr [$firstRandKey]);
				$secondRandKey = array_rand($sliceArr);
				$data [$firstRandKey] = $epcmList [$epcmListKeys [0]];
				$data [$secondRandKey] = $epcmList [$epcmListKeys [0]];
				return $data;
			}
			return $epcmList;
		}
	}

	private function fillDataToOutData($sp, $adInfoList, $eEcpmRankList)
	{
		$screenDirection = $sp ['screenDirection'];
		$clientVersion = $sp ['clientVersion'];
		$data = array();
		$attachPath = $this->getAttachPath();
		$index = 0;
		foreach ($eEcpmRankList as $key => $val) {
			$adTarget = html_entity_decode($adInfoList [$key] ['ad_target'], ENT_QUOTES);
			if ($this->mPlatform == Common_Service_Const::ANDRIOD_PLATFORM && Util_Environment::isOnline()) {
				if ($this->isHttps()) {
					$adTarget = str_replace('http', 'https', $adTarget);
				} else {
					$adTarget = str_replace('https', 'http', $adTarget);
				}
			}
			$imgPath = json_decode($adInfoList [$key] ['upload_content'], true);
			$originalityType = $adInfoList [$key] ['originality_type'];
			$originalityId = $adInfoList [$key] ['id'];
			$jumpType = $this->parseJumpType($clientVersion, $adInfoList [$key] ['jump_type']);
			$price = ($adInfoList [$key] ['charge_type'] == Common_Service_Const::CHARGE_TYPE_CPM) ? $adInfoList [$key]['price'] / 1000 : $adInfoList [$key]['price'];
			$data [$index] = array(
				'chargeType' => intval($adInfoList [$key] ['charge_type']),
				'price' => sprintf("%.4f", $price),
				'bidPrice' => $val,
				'requestId' => $this->mRequestId,
				'adUnitId' => $adInfoList [$key] ['unit_id'],
				'adUnitId' => strval($adInfoList [$key] ['unit_id']),
				'adId' => $adInfoList [$key] ['ad_id'],
				'adName' => $adInfoList [$key] ['app_name'] ? $adInfoList [$key] ['app_name'] : $adInfoList [$key] ['ad_name'],
				'originalityId' => $originalityId,
				'adType' => $originalityType,
				'targetUrl' => html_entity_decode($adTarget),
				'reportDataShowUrl' => $adInfoList [$key] ['imp_trackers'],
				'reportDataClickUrl' => $adInfoList [$key] ['click_trackers'],
				'appName' => $adInfoList [$key] ['app_name'],
				'deepLink' => $adInfoList [$key] ['deeplink'],
				'packageName' => $adInfoList [$key] ['package_name'],
				'jumpType' => $jumpType
			);
			if ($originalityType == Common_Service_Const::PIC_AD_SUB_TYPE) {
				$data [$index] ['iconUrl'] = $imgPath ['icon'] ? $attachPath . $imgPath ['icon'] : '';
				$data [$index] ['imgUrl'] = ($screenDirection == Common_Service_Const::SCREEN_CROSS) ? $attachPath . $imgPath ['cross_img'] : $attachPath . $imgPath ['vertical_img'];
				$data [$index] ['border'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfigBorder($this->mGlobalConfig, $originalityType, $screenDirection, $attachPath);
				$data [$index] ['closeButtonDelayShow'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $originalityType, 'close_button_delay_show');
				$data [$index] ['closeButtonDelayShowTimes'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $originalityType, 'close_button_delay_show_time');
			} elseif ($originalityType == Common_Service_Const::VIDEO_AD_SUB_TYPE) {
				$data [$index] ['iconUrl'] = $imgPath ['icon'] ? $attachPath . html_entity_decode($imgPath ['icon']) : '';
				$data [$index] ['videoUrl'] = $imgPath ['video'] ? $attachPath . html_entity_decode($imgPath ['video']) : '';
				$data [$index] ['htmlUrl'] = $imgPath ['h5'] ? $attachPath . html_entity_decode($imgPath ['h5']) : '';
				$data [$index] ['muteButton'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $originalityType, 'show_mute_button');
				$data [$index] ['closeButton'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $originalityType, 'show_close_button');
				$data [$index] ['downloadButton'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $originalityType, 'show_download_button');
				$data [$index] ['progressButton'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $originalityType, 'show_progress_button');
			} elseif ($originalityType == Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
				$data [$index] ['originalityTitle'] = $adInfoList [$key] ['originality_title'];
				$data [$index] ['originalityDesc'] = $adInfoList [$key] ['originality_desc'];
				$data [$index] ['adSubType'] = $this->mAdSubType;
				$data [$index] ['iconUrl'] = $imgPath ['icon'] ? $attachPath . $imgPath ['icon'] : '';
				$data [$index] ['imgUrl'] = ($screenDirection == Common_Service_Const::SCREEN_CROSS) ? $attachPath . $imgPath ['cross_img'] : $attachPath . $imgPath ['vertical_img'];
				$data [$index] ['border'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfigBorder($this->mGlobalConfig, $originalityType, $screenDirection, $attachPath);
				$data [$index] ['closeButtonDelayShow'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $originalityType, 'close_button_delay_show');
				$data [$index] ['closeButtonDelayShowTimes'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $originalityType, 'close_button_delay_show_time');
				$data [$index] ['closeButtonUrl'] = $attachPath . $this->mGlobalConfig [$originalityType] ['custom_close_button_url'];
				$data [$index] ['boutiqueLabelUrl'] = $this->mGlobalConfig [$originalityType] ['custom_boutique_label_url'] ? $attachPath . $this->mGlobalConfig [$originalityType] ['custom_boutique_label_url'] : '';
				$data [$index] ['playInterval'] = intval($this->mGlobalConfig [$originalityType] ['custom_play_interval']);
				$data [$index] ['animationEffect'] = intval($this->mGlobalConfig [$originalityType] ['custom_animation_effect']);
			} elseif ($originalityType == Common_Service_Const::SPLASH_AD_SUB_TYPE) {
				$data [$index] ['imgUrl'] = ($screenDirection == Common_Service_Const::SCREEN_CROSS) ? $attachPath . $imgPath ['cross_img'] : $attachPath . $imgPath ['vertical_img'];
				$data [$index] ['showSkipButton'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $originalityType, 'show_skip_button');
				$data [$index] ['showCountdown'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $originalityType, 'show_countdown');
				$data [$index] ['waitTime'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $originalityType, 'dsp_waiting_time');
				$data [$index] ['showTime'] = Advertiser_Service_AdAppkeyConfigModel::parseGlobalConfig($this->mGlobalConfig, $originalityType, 'display_time');
			} elseif ($originalityType == Common_Service_Const::ENBED_AD_SUB_TYPE) {
				$data [$index] ['originalityDesc'] = $adInfoList [$key] ['originality_desc'];
				$data [$index] ['iconUrl'] = $imgPath ['icon'] ? $attachPath . $imgPath ['icon'] : '';
				$data [$index] ['score'] = intval($imgPath ['score']);
				$data [$index] ['actionText'] = $imgPath ['action_text'];
				$data [$index] ['adSubType'] = $this->mAdSubType;
				if ($this->mAdSubType == Common_Service_Const::SINGLE_ENBED_AD_SUB_TYPE) { // 单图
					$data [$index] ['imgUrl'] = $imgPath ['single_img'] ? $attachPath . $imgPath ['single_img'] : '';
				} else if ($this->mAdSubType == Common_Service_Const::COMBINATION_ENBED_AD_SUB_TYPE) { // 组图
					$imgurls = array();
					if ($imgPath ['combination_img1']) {
						$imgurls [] = $attachPath . html_entity_decode($imgPath ['combination_img1']);
					}
					if ($imgPath ['combination_img2']) {
						$imgurls [] = $attachPath . html_entity_decode($imgPath ['combination_img2']);
					}
					if ($imgPath ['combination_img3']) {
						$imgurls [] = $attachPath . html_entity_decode($imgPath ['combination_img3']);
					}
					$data [$index] ['imgUrls'] = $imgurls;
				}
			}
			$index++;
		}
		$returnData ['list'] = $data;
		return $returnData;
	}

	/**
	 * 转成dsp返回
	 *
	 * @param type $houseadResponse
	 * @return array
	 */
	private function farmatResponseData($houseadResponse)
	{
		// 组织数据返回
		$dspResponse = array();
		if ($houseadResponse ['list']) {
			$dspResponse ['bidId'] = $this->mPostData ['bidId'];
			$dspResponse ['outBidId'] = $houseadResponse ['list'] [0] ['requestId'];
			foreach ($houseadResponse ['list'] as $item) {
				$tmpItem = array(
					"reportDataClickUrl" => $this->formatMacroLink($item ['reportDataClickUrl'], array(
						'adId' => $item ['adId'],
						'originalityId' => $item ['originalityId'],
						'requestId' => $this->mPostData ['bidId']
					), true), // 第三方数据上报地址（展示、点击）
					"reportDataShowUrl" => $this->formatMacroLink($item ['reportDataShowUrl'], array(
						'adId' => $item ['adId'],
						'originalityId' => $item ['originalityId'],
						'requestId' => $this->mPostData ['bidId']
					), true),
					"width" => 123, // 广告宽度
					"height" => 456, // 广告高度
					"adDesc" => $item['adName'], // 广告描述
					"price" => $item ['price'], // 价格
					"bidPrice" => $item ['bidPrice'], // 竞价价格
					"chargeType" => $item ['chargeType'],
					"currency" => 1, // 币种1CNY
					"adUnitId" => intval($item ['adUnitId']), // 广告单元ID
					"adUnitId" => $item ['adUnitId'], // 广告单元ID
					"adId" => $item ['adId'], // 广告的ID
					"originalityId" => $item ['originalityId'], // 创意ID
					"targetUrl" => $this->formatMacroLink($item ['targetUrl'], array(
						'adId' => $item ['adId'],
						'originalityId' => $item ['originalityId'],
						'requestId' => $this->mPostData ['bidId']
					), false), // 推广地址
					"versionCode" => '1', // 推广目标版本号
					"adType" => intval($item ['adType']), // 广告类型 1视频,2插页,3自定义
					"jumpType" => intval($item ['jumpType']), // 跳转类型，0表示静默下载(针对安卓)，1表示跳转市场应用(ios为Appstore,安卓为GooglePlay)，2表示跳转系统默认浏览器，3表示跳转自建浏览器，4表示打开列表广告，5表示自定义动作，6表示无动作，7表示通知栏下载(针对安卓），8表示商店内页打开（IOS）。目前仅0,1,2,3,7,8有价值
					"packageName" => $item ['packageName'], // 包名（针对安卓）
					"adName" => $item ['adName'], // 广告名称（针对安卓）
					"iconUrl" => $item ['iconUrl'], // 图标地址（针对安卓）
					"deepLink" => $item ['deepLink']
				);
				if ($item ['adType'] == Common_Service_Const::PIC_AD_SUB_TYPE) {
					$tmpItem ['reportDataVideoStartUrl'] = array();
					$tmpItem ['reportDataVideoEndUrl'] = array();
					$tmpItem ['imgUrl'] = $item ['imgUrl'];
					$tmpItem ['border'] = $item ['border'];
					$tmpItem ['closeButtonDelayShow'] = intval($item ['closeButtonDelayShow']);
					$tmpItem ['closeButtonDelayShowTimes'] = intval($item ['closeButtonDelayShowTimes']);
				} else if ($item ['adType'] == Common_Service_Const::VIDEO_AD_SUB_TYPE) {
					$tmpItem ['videoUrl'] = $item ['videoUrl'];
					$tmpItem ['htmlUrl'] = $item ['htmlUrl'];
					$tmpItem ['muteButton'] = intval($item ['muteButton']);
					$tmpItem ['closeButton'] = intval($item ['closeButton']);
					$tmpItem ['downloadButton'] = intval($item ['downloadButton']);
					$tmpItem ['progressButton'] = intval($item ['progressButton']);
				} else if ($item ['adType'] == Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
					$tmpItem ['appName'] = $item ['appName'];
					$tmpItem ['originalityTitle'] = $item ['originalityTitle'];
					$tmpItem ['originalityDesc'] = $item ['originalityDesc'];
					$tmpItem ['adSubType'] = $item ['adSubType'];
					$tmpItem ['reportDataVideoStartUrl'] = array();
					$tmpItem ['reportDataVideoEndUrl'] = array();
					$tmpItem ['imgUrl'] = $item ['imgUrl'];
					$tmpItem ['border'] = $item ['border'];
					$tmpItem ['boutiqueLabelUrl'] = $item ['boutiqueLabelUrl'];
					$tmpItem ['closeButtonDelayShow'] = intval($item ['closeButtonDelayShow']);
					$tmpItem ['closeButtonDelayShowTimes'] = intval($item ['closeButtonDelayShowTimes']);
					$tmpItem ['closeButtonUrl'] = $item ['closeButtonUrl'];
					$tmpItem ['playInterval'] = $item ['playInterval'];
					$tmpItem ['animationEffect'] = $item ['animationEffect'];
				} else if ($item ['adType'] == Common_Service_Const::SPLASH_AD_SUB_TYPE) {
					$tmpItem ['imgUrl'] = $item ['imgUrl'];
					$tmpItem ['showSkipButton'] = intval($item ['showSkipButton']);
					$tmpItem ['showCountdown'] = intval($item ['showCountdown']);
					$tmpItem ['waitTime'] = $item ['waitTime'];
					$tmpItem ['showTime'] = $item ['showTime'];
				} else if ($item ['adType'] == Common_Service_Const::ENBED_AD_SUB_TYPE) {
					$tmpItem ['originalityDesc'] = $item ['originalityDesc'];
					$tmpItem ['iconUrl'] = $item ['iconUrl'];
					$tmpItem ['score'] = intval($item ['score']);
					$tmpItem ['actionText'] = $item ['actionText'];
					$tmpItem ['adSubType'] = $item ['adSubType'];
					if ($this->mAdSubType == Common_Service_Const::SINGLE_ENBED_AD_SUB_TYPE) { // 单图
						$tmpItem ['imgUrl'] = $item ['imgUrl'];
					} else if ($this->mAdSubType == Common_Service_Const::COMBINATION_ENBED_AD_SUB_TYPE) { // 组图
						$tmpItem ['imgUrls'] = $item ['imgUrls'];
					}
				}
				// 根据客户端版本是否返回list
				if ($this->mAdType == Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
					$dspResponse ['bidInfo'] [] = $tmpItem;
				} else {
					$dspResponse ['bidInfo'] [] = $tmpItem;
					break;
				}
			}
		}
		return $dspResponse;
	}

	private function fiterIp($adList)
	{
		if (empty ($adList)) {
			return false;
		}
		//$this->ip = '218.70.247.163';
		$ipInfo = $this->getParseAreaCacheDataByIp($this->ip);
		$cityList = Common::getConfig('areaConfig', 'cityList');
		$tmpCityList = array();
		foreach ($cityList as $key => $val) {
			foreach ($val as $k => $v) {
				$tmpCityList[$k] = $v;
			}
		}

		foreach ($adList as $key => $val) {
			if (empty ($val ['direct_config']) || empty($ipInfo['city'])) {
				continue;
			}
			$directConfig = json_decode($val ['direct_config'], true);
			if ($directConfig ['area_type']) {
				$areaIds = $directConfig ['area_range'];
				$findFlag = 0;
				foreach ($areaIds as $cityId) {
					if (mb_strpos($tmpCityList[$cityId], $ipInfo['city']) !== false || mb_strpos($ipInfo['city'], $tmpCityList[$cityId]) !== false) {
						$findFlag = 1;
					}
				}
				if (!$findFlag) {
					unset($adList[$key]);
				}
			}
		}
		return $adList;
	}
}
