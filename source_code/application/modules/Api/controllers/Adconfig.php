<?php
if (!defined('BASE_PATH'))
	exit ('Access Denied!');

class AdConfigController extends Api_BaseController
{
	const LOG_TAG = 'getAdListAction';
	const LOG_FILE = 'getAdList.log';
	private $mPlatform = '';
	private $mRequestId = '';
	private $mAccountAmountList = null;
	private $mAccountTodayConsumeAmount = null;
	private $mAccountDayAmountLimitList = null;
	private $mUnitDayLimitAmountList = null;
	private $mUnitTodayCosumeAmountList = null;
	private $mThirtyDayCTR = null;
	private $mSevenDayCTR = null;
	private $mThreeDayCTR = null;
	private $mYesterdayCTR = null;
	private $todayCTR = null;
	private $mSevenAverageExposureRate = null;
	private $mEcpmRankCount = 0;
	private $mUuid = '';
	private $mUdid = '';
	// 广告类型
	private $mAdType = null;
	// 是否保存用户限制cache
	private $mAccountIdsList = array();
	private $mGlobalConfig = array();


	private function createRequestId()
	{
		$time = explode('.', microtime(true));
		$key = 'request_id::' . date('Ymd');
		$cache = Cache_Factory::getCache();
		$requestId = date('YmdHis') . str_pad($time [1], 4, "0", STR_PAD_LEFT) . str_pad($cache->increment($key), 14, "0", STR_PAD_LEFT);
		return $requestId;
	}

	private function testAction()
	{
		$info = $this->getInput(array(
			'gameType',
			'userId'
		));
		$data ['user'] = $info ['userId'];
		$data ['tags'] [] = array(
			'type' => 1,
			'paid' => 1,
			'paid7' => 3,
			'paid14' => 3,
			'paid30' => 3,
			'active7' => 3,
			'active14' => 3,
			'active30' => 3
		);
		$this->output(0, 'ok', $data);
	}

	private function getDirectDataCacheKey($gameType)
	{
		return Util_CacheKey::DATA_CENTER_USER_DIRECT_LABEL . $this->mUuid . '_' . $gameType . '_' . date('Y-m-d');
	}

	private function requestToDataCenter($gameType = '')
	{
		$dataCenterConfig = Common::getConfig('dataCenterConfig');
		$uid = 'D9394D91-043A-4F8D-A698-AF8206D1A41F';
		$gameType = '';
		$version = $dataCenterConfig ['version'];
		$code = $dataCenterConfig ['code'];
		$token = md5($uid . $gameType . $code . 'V' . $version . $code . $gameType . $uid);
		$url = $dataCenterConfig ['direct_url'] . '/?id=' . $uid . '&type=' . $gameType . '&ver=' . $version . '&token=' . $token;
		$curl = new Util_Http_Curl ($url, Common_Service_Const::TWO_SECONDS);
		// $curl->setData($data);
		$result = $curl->send('GET');
		$headInfo = $curl->getInfo();
		if ($headInfo ['http_code'] != Common_Service_Const::HTTP_SUCCESS_CODE) {
			// 重试
			$result = $curl->send('GET');
		}
		if (!$result) {
			return false;
		}
		return $result;
	}

	private function getDataCenterDirectDataFromCache($gameType)
	{
		$key = $this->getDirectDataCacheKey($gameType);
		$cache = Cache_Factory::getCache();
		$data = $cache->get($key);
		if ($data === false) {
			$data = $this->requestToDataCenter($gameType);
			if ($data) {
				$cache->set($key, $data, Common_Service_Const::ONE_DAY_FOR_SECONDS);
			}
		}
		return $data;
	}

	public function getAdListAction()
	{
		// header("Content-type:text/html;charset=utf-8");
		$info = $this->getInput(array(
			'blockId',
			'appKey',
			'sp',
			'adType',
			'adSubType'
		));
		// Util_Log::info(self::LOG_TAG, self::LOG_FILE, array('请求参数：', $info));
		$sp = Common::parseSp($info ['sp']);
		$this->checkAdListParams($info, $sp);
		$this->initIp();

		$this->mGlobalConfig = Advertiser_Service_AdAppkeyConfigModel::getStrategyConfig($this->mAppKey, $sp ['platform']);
		if (empty($this->mGlobalConfig)) {
			$this->output(Util_ErrorCode::DSP_STRATEGY_CONFIG_EMPTY, 'StrategyConfig is empty');
		}
		// 获取帐号列表
		$this->mAccountIdsList = $this->getAccountListFromDb();
		// 处理自有广告信息数据
		$data = $this->processMyselfAdInfoData($info, $sp);
		// 输出数据
		$this->output(Util_ErrorCode::CONFIG_SUCCESS, 'ok', $data);
	}

	private function processMyselfAdInfoData($info, $sp)
	{
		// 可用的创意列表
		$originalityList = $this->getAvailableOriginalityList($info, $sp);
		// 可用的广告列表
		$adInfoList = $this->getAvailableAdList($originalityList, $sp);
		// 计算eEcpm的排名
		$eEcpmRankList = $this->calculateAdListEECPMRankList($adInfoList);
		// 保持扣费价格
		$this->saveAdChargePrice($eEcpmRankList, $adInfoList);
		// 格式化输出数据
		$data = $this->fillDataToOutData($sp, $adInfoList, $eEcpmRankList);
		return $data;
	}

	private function getAvailableAdList($originalityList, $sp)
	{
		// 获取可用广告列表
		$fiterAdListResult = $this->fiterAdList($originalityList, $sp);
		if (empty ($fiterAdListResult)) {
			$this->output(Util_ErrorCode::AD_ID_EMPTY, 'fiterAdListResult  is empty');
		}
		$adInfoList = $this->fillDataToAdInfoList($originalityList, $fiterAdListResult);
		if (empty ($adInfoList)) {
			$this->output(Util_ErrorCode::AD_ID_EMPTY, 'adInfoList  is empty');
		}
		return $adInfoList;
	}

	// 椰子api对接数据
	private function getCocoMediaAdList($sp)
	{
		if ($this->mAdType != Common_Service_Const::PIC_AD_SUB_TYPE) {
			return array();
		}
		// 只接入插页
		$screenDirection = $sp ['screenDirection'];
		$attachPath = $this->getAttachPath();
		if ($this->mGlobalConfig [$this->mAdType] ['border_type'] == Advertiser_Service_AdAppkeyConfigModel::BODER_TYPE_COLOR) {
			$border = $this->mGlobalConfig [$this->mAdType] ['border'];
		} else {
			$border = ($screenDirection == Common_Service_Const::SCREEN_CROSS) ? $attachPath . $this->mGlobalConfig [$this->mAdType] ['border_cross_img'] : $attachPath . $this->mGlobalConfig [$this->mAdType] ['border_vertical_img'];
		}
		$data = array(
			'border' => $border,
			'closeButtonDelayShow' => ($this->mGlobalConfig && $this->mGlobalConfig [$this->mAdType] ['close_button_delay_show']) ? 1 : 0,
			'closeButtonDelayShowTimes' => ($this->mGlobalConfig && $this->mGlobalConfig [$this->mAdType] ['close_button_delay_show']) ? intval($this->mGlobalConfig [$this->mAdType] ['close_button_delay_show_time']) : 0
		);
		$CocoMedia = new Util_ThirdApi_CocoMedia ();
		$mRequestId = $this->createRequestId();
		$result = $CocoMedia->getAdList($mRequestId, $sp, $this->mAppKey, $data);
		if ($result ['success']) {
			return $result ['data'];
		} else {
			// $this->output(1, $result['msg']);
			return array();
		}
	}

	// 椰子api对接数据
	public function getAdListyzAction()
	{
		// 只接入插页
		header("Content-type:text/html;charset=utf-8");
		$info = $this->getInput(array(
			'blockId',
			'appKey',
			'sp',
			'adType',
			'adSubType'
		));
		$sp = Common::parseSp($info ['sp']);
		$this->checkAdListParams($info, $sp);
		$CocoMedia = new Util_ThirdApi_CocoMedia ();
		$mRequestId = $this->createRequestId();
		$this->mGlobalConfig = $this->getStrategyConfig();
		$screenDirection = $sp ['screenDirection'];
		$attachPath = $this->getAttachPath();
		if ($this->mGlobalConfig [2] ['border_type'] == Advertiser_Service_AdAppkeyConfigModel::BODER_TYPE_COLOR) {
			$border = $this->mGlobalConfig [2] ['border'];
		} else {
			$border = ($screenDirection == Common_Service_Const::SCREEN_CROSS) ? $attachPath . $this->mGlobalConfig [2] ['border_cross_img'] : $attachPath . $this->mGlobalConfig [2] ['border_vertical_img'];
		}
		$data = array(
			'border' => $border,
			'closeButtonDelayShow' => ($this->mGlobalConfig && $this->mGlobalConfig [2] ['close_button_delay_show']) ? 1 : 0,
			'closeButtonDelayShowTimes' => ($this->mGlobalConfig && $this->mGlobalConfig [2] ['close_button_delay_show']) ? intval($this->mGlobalConfig [2] ['close_button_delay_show_time']) : 0
		);
		$result = $CocoMedia->getAdList($mRequestId, $sp, $this->mAppKey, $data);
		if ($result ['success']) {
			$this->output(0, 'ok', $result ['data']);
		} else {
			$this->output(1, $result ['msg']);
		}
	}

	private function getAccountListFromDb()
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
			$this->output(Util_ErrorCode::DSP_ACCOUNT_EMPTY, 'no accountId');
		}
		$advertiserUid = array_keys(Common::resetKey($restult, 'user_id'));
		if (empty ($advertiserUid)) {
			$this->output(Util_ErrorCode::DSP_ACCOUNT_EMPTY, 'no accountId');
		}
		return $advertiserUid;
	}

	private function getStrategyConfig($sp = array())
	{
		$appKeyConfigId = Advertiser_Service_AppkeyConfigModel::getAppkeyconfigidByappkey($this->mAppKey);
		// 新增默认配置.当后台没有配置指定的策略配置时,默认使用请求的平台的默认配置.
		if (empty ($appKeyConfigId)) {
			if ($sp && isset ($sp ['platform'])) {
				$platform = $sp ['platform'];
				if ($platform == 1) {
					$appKeyConfigId = Advertiser_Service_AdAppkeyConfigModel::APPKEY_CONFIGID_FOR_ANDROID;
				} else if ($platform == 2) {
					$appKeyConfigId = Advertiser_Service_AdAppkeyConfigModel::APPKEY_CONFIGID_FOR_IOS;
				} else {
					$appKeyConfigId = Advertiser_Service_AdAppkeyConfigModel::APPKEY_CONFIGID_FOR_ANDROID;
				}
			} else {
				$appKeyConfigId = Advertiser_Service_AdAppkeyConfigModel::APPKEY_CONFIGID_FOR_ANDROID;
			}
		}
		$configRecord = Advertiser_Service_AdAppkeyConfigModel::getConfig($appKeyConfigId);
		if (empty ($configRecord)) {
			$this->output(Util_ErrorCode::DSP_STRATEGY_CONFIG_EMPTY, 'StrategyConfig is empty');
		}
		return $configRecord ['config'];
	}

	private function getAttachPath()
	{
		$attachPath = Common::getAttachPath();
		if (Util_Environment::isTest() && $this->mPlatform == Common_Service_Const::IOS_PLATFORM) {
			$webRoot = Common::getWebRoot();
			$attachPath = str_replace('http', 'https', $webRoot) . '/attachs';
		}
		if ($this->mPlatform == Common_Service_Const::ANDRIOD_PLATFORM && Util_Environment::isOnline()) {
			$attachPath = str_replace('https', 'http', $attachPath);
		}
		return $attachPath;
	}

	private function fillDataToOutData($sp, $adInfoList, $eEcpmRankList)
	{
		$screenDirection = $sp ['screenDirection'];
		$clientVersion = $sp ['clientVersion'];
		$data = array();
		$attachPath = $this->getAttachPath();
		$index = 0;
		foreach ($eEcpmRankList as $key => $val) {
			$originalityType = $adInfoList [$key] ['originality_type'];
			$imgPath = json_decode($adInfoList [$key] ['upload_content'], true);
			$originalityId = $adInfoList [$key] ['id'];
			$adTarget = $this->formatMacroLink(html_entity_decode($adInfoList [$key] ['ad_target'], ENT_QUOTES), array(
				'adId' => $adInfoList [$key] ['ad_id'],
				'originalityId' => $originalityId,
				'requestId' => $this->mRequestId
			), $clientVersion, false);
			if ($this->mPlatform == Common_Service_Const::ANDRIOD_PLATFORM && Util_Environment::isOnline()) {
				$adTarget = str_replace('https', 'http', $adTarget);
			}
			$jumpType = intval($adInfoList [$key] ['jump_type']);
			$jumpType = $this->getJumpTypeByVerion($clientVersion, $jumpType);
			// 针对安桌投放应用的通知栏下载，需要把ad_name改成appName
			if ($adInfoList [$key] ['ad_target_type'] == '1' && $jumpType == 7) {
				$adName = $adInfoList [$key] ['app_name'];
			} else {
				$adName = $adInfoList [$key] ['ad_name'];
			}
			$data [$index] = array(
				'requestId' => $this->mRequestId,
				'adUnitId' => $adInfoList [$key] ['unit_id'],
				'adId' => $adInfoList [$key] ['ad_id'],
				'adName' => $adName,
				'originalityId' => $originalityId,
				'adType' => $originalityType,
				'targetUrl' => $adTarget,
				'reportDataShowUrl' => $this->formatMacroLink($adInfoList [$key] ['imp_trackers'], array(
					'adId' => $adInfoList [$key] ['ad_id'],
					'originalityId' => $originalityId,
					'requestId' => $this->mRequestId
				), $clientVersion, true),
				'reportDataClickUrl' => $this->formatMacroLink($adInfoList [$key] ['click_trackers'], array(
					'adId' => $adInfoList [$key] ['ad_id'],
					'originalityId' => $originalityId,
					'requestId' => $this->mRequestId
				), $clientVersion, true),
				'appName' => $adInfoList [$key] ['app_name'],
				'deepLink' => $adInfoList [$key] ['deeplink'],
				'packageName' => $adInfoList [$key] ['package_name'],
				'jumpType' => $jumpType,
				'iconUrl' => $imgPath ['icon'] ? $attachPath . $imgPath ['icon'] : ''
			);
			if ($originalityType == Common_Service_Const::PIC_AD_SUB_TYPE) {
				if ($this->mGlobalConfig [$originalityType] ['border_type'] == Advertiser_Service_AdAppkeyConfigModel::BODER_TYPE_COLOR) {
					$border = $this->mGlobalConfig [$originalityType] ['border'];
				} else {
					$border = ($screenDirection == Common_Service_Const::SCREEN_CROSS) ? $attachPath . $this->mGlobalConfig [$originalityType] ['border_cross_img'] : $attachPath . $this->mGlobalConfig [$originalityType] ['border_vertical_img'];
				}
				$data [$index] ['imgUrl'] = ($screenDirection == Common_Service_Const::SCREEN_CROSS) ? $attachPath . $imgPath ['cross_img'] : $attachPath . $imgPath ['vertical_img'];
				$data [$index] ['border'] = $border;
				$data [$index] ['closeButtonDelayShow'] = ($this->mGlobalConfig && $this->mGlobalConfig [$originalityType] ['close_button_delay_show']) ? 1 : 0;
				$data [$index] ['closeButtonDelayShowTimes'] = ($this->mGlobalConfig && $this->mGlobalConfig [$originalityType] ['close_button_delay_show']) ? intval($this->mGlobalConfig [$originalityType] ['close_button_delay_show_time']) : 0;
			} elseif ($originalityType == Common_Service_Const::VIDEO_AD_SUB_TYPE) {
				$data [$index] ['videoUrl'] = $imgPath ['video'] ? $attachPath . $imgPath ['video'] : '';
				$data [$index] ['htmlUrl'] = $imgPath ['h5'] ? $attachPath . $imgPath ['h5'] : '';
				$data [$index] ['muteButton'] = ($this->mGlobalConfig && $this->mGlobalConfig [$originalityType] ['show_mute_button']) ? 0 : 1;
				$data [$index] ['closeButton'] = ($this->mGlobalConfig && $this->mGlobalConfig [$originalityType] ['show_close_button']) ? 0 : 1;
				$data [$index] ['downloadButton'] = ($this->mGlobalConfig && $this->mGlobalConfig [$originalityType] ['show_download_button']) ? 0 : 1;
				$data [$index] ['progressButton'] = ($this->mGlobalConfig && $this->mGlobalConfig [$originalityType] ['show_progress_button']) ? 0 : 1;
			} elseif ($originalityType == Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
				$border = ($screenDirection == Common_Service_Const::SCREEN_CROSS) ? $attachPath . $this->mGlobalConfig [$originalityType] ['custom_border_cross_img'] : $attachPath . $this->mGlobalConfig [$originalityType] ['custom_border_vertical_img'];
				$data [$index] ['imgUrl'] = ($screenDirection == Common_Service_Const::SCREEN_CROSS) ? $attachPath . $imgPath ['cross_img'] : $attachPath . $imgPath ['vertical_img'];
				$data [$index] ['border'] = $border;
				$data [$index] ['closeButtonDelayShow'] = ($this->mGlobalConfig && $this->mGlobalConfig [$originalityType] ['close_button_delay_show']) ? 1 : 0;
				$data [$index] ['closeButtonDelayShowTimes'] = ($this->mGlobalConfig && $this->mGlobalConfig [$originalityType] ['close_button_delay_show']) ? intval($this->mGlobalConfig [$originalityType] ['close_button_delay_show_time']) : 0;
				$data [$index] ['closeButtonUrl'] = $this->mGlobalConfig [$originalityType] ['custom_close_button_url'];
				$data [$index] ['playInterval'] = intval($this->mGlobalConfig [$originalityType] ['custom_play_interval']);
				$data [$index] ['animationEffect'] = intval($this->mGlobalConfig [$originalityType] ['custom_animation_effect']);
			}
			$index++;
		}
		$returnData ['list'] = $data;
		return $returnData;
	}

	/**
	 * 保持扣费价格
	 * 若计费类型为CPC，应扣费用=eCPM（第二名）/(1000*f（x）*创意权重)+0.01，f(x)= x1*1/(1+e^(-0.1x2))*1/(1+0.1x3)，详情见上
	 * 若计费类型为CPM，应扣费用=eCPM（第二名）/(1000*f（x）*创意权重)，f（x）=1/(1+e^(-0.1x))，详情见上
	 */
	private function saveAdChargePrice($eEcpmRankList, $adInfoList)
	{
		$data = array();
		$position = 0;
		foreach ($eEcpmRankList as $key => $val) {
			$position++;
			$chargeType = $adInfoList [$key] ['charge_type'];
			// 当只有一条的时候，直接用广告出价
			if (($this->mEcpmRankCount == 1) || (($this->mEcpmRankCount == 2) && $position == 2)) {
				$chargePrice = $adInfoList [$key] ['price'];
				if ($chargeType == Common_Service_Const::CHARGE_TYPE_CPM) {
					$chargePrice = $chargePrice / 1000;
				}
				$data [$key] = array(
					'price' => sprintf("%.4f", $chargePrice),
					'charge_type' => $chargeType
				);
				break;
			}
			$chargePrice = $adInfoList [$key] ['price'];
			if ($chargeType == Common_Service_Const::CHARGE_TYPE_CPM) {
				$chargePrice = $chargePrice / 1000;
			}
			$data [$key] = array(
				'price' => sprintf("%.4f", $chargePrice),
				'charge_type' => $chargeType
			);
		}
		$this->mRequestId = $this->createRequestId();
		// 保存调试信息
		if (!Util_Environment::isOnline()) {
			$this->mDebugInfo ['savePriceList'] = $data;
			$this->mDebugInfo ['RequestId'] = $this->mRequestId;
		}
		Dedelivery_Service_OriginalityRelationModel::saveOriginalityChargePriceKeyToCache($this->mRequestId, $data);
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
				// / X1= CTR（近30天）*0.03+CTR（近7天）*0.07+CTR（近3天）*0.1+CTR（昨天）*0.4+CTR（当日实时）*0.4。若无历史点击率（新创意），视频创意的x1用0.5%代替，插页创意的x1用20%代替
				// / X2=min（账户总余额，账户日限额-账户今日消耗，投放单元日限额-投放单元今日消耗）/CPC
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
			if (!Util_Environment::isOnline()) {
				$this->mDebugInfo ['epcmList'] = $eEpcmList;
			}
			return $eEpcmList;
		}
		// 保存一些调试信息
		if (!Util_Environment::isOnline()) {
			$this->mDebugInfo ['beforeRankEcmpList'] = $eEpcmList;
		}
		// 排序
		arsort($eEpcmList);
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
		// 处理ecpm排名数据
		$randEcpmRank = $this->randEcpmRank($returnData);
		// 输出一些调试信息
		if (!Util_Environment::isOnline()) {
			$this->mDebugInfo ['afterRankEcmpList'] = $eEpcmList;
			$this->mDebugInfo ['epcmList'] = $returnData;
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


	private function getAvailableOriginalityList($info, $sp)
	{
		// 查询没有关闭的投放单元
		$unintIds = $this->getUnitConfId();
		if (empty ($unintIds)) {
			$this->output(Util_ErrorCode::UNIT_CONFIG_EMPTY, 'unitConfIds is  empty');
		}
		// 获取创意列表
		$originalityList = $this->getOriginalityList($unintIds);
		$originalityList = $this->fiterAppKey($info ['appKey'], $originalityList);
		if (empty ($originalityList)) {
			$this->output(Util_ErrorCode::ORIGINALITY_LIST_EMPTY, 'originalityList  is empty', array());
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
				$adInfoList [$key] ['deeplink'] = $availableAdList [$val ['ad_id']] ['deeplink'];
				$adInfoList [$key] ['frequency_type'] = $availableAdList [$val ['ad_id']] ['frequency_type'];
				$adInfoList [$key] ['frequency'] = $availableAdList [$val ['ad_id']] ['frequency'];
			}
		}
		return $adInfoList;
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
				if ($adInfo ['frequency_type'] == 'ad') {
					// 格式：fiterfrequency_ad_日期_广告id_设备ID(imei/idfa) ex. fiterfrequency_ad_20170714_147_867348026517826
					$frequencyKey = "fiterfrequency_" . $adInfo ['frequency_type'] . "_" . $today . "_" . $adInfo ['ad_id'] . "_" . $this->mUdid;
					$cacheFrequency = $cache->get($frequencyKey);
					if (intval($cacheFrequency) >= $adInfo ['frequency']) {
						unset ($adInfoList [$key]);
					}
				} else if ($adInfo ['frequency_type'] == 'originality') {
					// 格式：fiterfrequency_originality_日期_创意id_设备ID(imei/idfa) ex. fiterfrequency_originality_20170714_231_867348026517826
					$frequencyKey = "fiterfrequency_" . $adInfo ['frequency_type'] . "_" . $today . "_" . $adInfo ['id'] . "_" . $this->mUdid;
					$cacheFrequency = $cache->get($frequencyKey);
					if (intval($cacheFrequency) >= $adInfo ['frequency']) {
						unset ($adInfoList [$key]);
					}
				}
			}
		}
		return $adInfoList;
	}

	private function fiterAdList($originalityList, $sp)
	{
		if (empty ($originalityList)) {
			return false;
		}
		// 获取创意关联的广告id
		$adIds = $this->getAdIdsByoriginalityList($originalityList);
		if (empty ($adIds)) {
			$this->output(Util_ErrorCode::DSP_AD_INFO_EMPTY, 'adIds  is empty by originalityList');
		}
		// 获取关联的广告列表 //修改这里的问题.广告目标修改了之后,这里也需要修改
		$adList = $this->getAdListByAdIds($adIds);
		if (empty ($adList)) {
			$this->output(Util_ErrorCode::DSP_AD_INFO_EMPTY, 'get adIds is  empty');
		}
		// app行为定向过滤
		$adList = $this->fiterAppBehavior($adList);
		if (empty ($adList)) {
			$this->output(Util_ErrorCode::DSP_FITER_CONFIG, 'get adList  by fiterAppBehavior');
		}
		if (empty ($adList)) {
			$this->output(Util_ErrorCode::DSP_FITER_CONFIG, 'get adList  by adIds');
		}
		$adList = $this->fiterDateRange($adList);
		if (empty ($adList)) {
			$this->output(Util_ErrorCode::DSP_DATE_FITER, 'get adList  by fiterDateRange');
		}
		$adList = $this->fiterIp($adList);
		if (empty ($adList)) {
			$this->output(Util_ErrorCode::DSP_FITER_CONFIG, 'get adList  by fiterIp');
		}
		$adList = $this->fiterPlatform($adList, $sp ['platform']);
		if (empty ($adList)) {
			$this->output(Util_ErrorCode::DSP_FITER_CONFIG, 'get adList  by fiterPlatform');
		}
		$adList = $this->fiterOperator($adList, $sp ['operator']);
		if (empty ($adList)) {
			$this->output(Util_ErrorCode::DSP_FITER_CONFIG, 'get adList  by fiterOperator');
		}
		$adList = $this->fiterNetType($adList, $sp ['netType']);
		if (empty ($adList)) {
			$this->output(Util_ErrorCode::DSP_FITER_CONFIG, 'get adList  by fiterNetType');
		}
		$adList = $this->fiterBrand($adList, $sp ['brand']);
		if (empty ($adList)) {
			$this->output(Util_ErrorCode::DSP_FITER_CONFIG, 'get adList  by fiterBrand');
		}
		$adList = $this->fiterScreenSize($adList, $sp ['screenSize']);
		if (empty ($adList)) {
			$this->output(Util_ErrorCode::DSP_FITER_CONFIG, 'get adList  by fiterScreenSize');
		}
		$adList = $this->fiterAccountAmount($adList);
		if (empty ($adList)) {
			$this->output(Util_ErrorCode::DSP_ACCOUNT_LIMIT, 'get adList  by fiterAccountAmountLimit');
		}
		$adList = $this->fiterUnitAmountLimit($adList);
		if (empty ($adList)) {
			$this->output(Util_ErrorCode::DSP_UNIT_LIMIT, 'get adList  by fiterUnitAmountLimit');
		}

		$adList = $this->fiterAdInfoAmountLimit($adList);
		if (empty ($adList)) {
			$this->output(Util_ErrorCode::DSP_ADINFO_AMOUNT_LIMIT, 'get adList  by fiterAdInfoAmountLimit');
		}


		// 服务端频次控制
		$adList = $this->fiterFrequency($adList);
		if (empty ($adList)) {
			$this->output(Util_ErrorCode::DSP_FREQUENCY_LIMIT, 'no adInfoList:fiterFrequency');
		}


		return $adList;
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
			$fiterKey = $this->getInput('blockId');
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
			}
		}
		return $adList;
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

	/**
	 *
	 * @param unknown $ip
	 * @param unknown $type
	 *            1世界区域 0中国区域
	 */
	private function getParseAreaCacheDataByIp($ip)
	{
		if (!$ip) {
			return array();
		}
		$ipLong = sprintf('%u', ip2long($ip));
		$resource = 'ip_info_' . (($ipLong % 2) + 1);
		$cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS, $resource);
		$key = 'ip_' . md5($ip . '_ipinfo');
		$ipInfo = $cache->get($key);
		if ($ipInfo === false) {
			$ipInfo = Util_IpToCityApi::getIpDetailInfo($ip);
			if ($ipInfo) {
				$cache->set($key, $ipInfo, Util_CacheKey::CACHE_KEY_EXPRIE_ONE_DAY);
			}
		}
		return $ipInfo;
	}

	private function fiterIp($adList)
	{
		if (empty ($adList)) {
			return false;
		}
		//$ip = '218.18.232.228';
		$ipInfo = $this->getParseAreaCacheDataByIp($this->ip);
		if (!Util_Environment::isOnline()) {
			$this->mDebugInfo ['ip'] = $this->ip;
			$this->mDebugInfo ['areaInfo'] = $ipInfo;
		}
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

	private function fiterDateRange($adList)
	{
		if (empty ($adList)) {
			return false;
		}
		$currentDate = strtotime(date('Y-m-d'));
		$currentTime = date('H-i');
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

	// todo modify
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

	private function checkAdListParams($info, $sp)
	{
		if (!in_array($info ['adType'], array(
			Common_Service_Const::PIC_AD_SUB_TYPE,
			Common_Service_Const::VIDEO_AD_SUB_TYPE,
			Common_Service_Const::CUSTOME_AD_SUB_TYPE
		))) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'adType is error', array());
		}
		if ($info ['adType'] == Common_Service_Const::PIC_AD_SUB_TYPE && !$info ['blockId']) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'blockId is error');
		}
		if ($info ['adType'] == Common_Service_Const::CUSTOME_AD_SUB_TYPE && !$info ['blockId']) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'blockId is error');
		}
		$this->mAdType = intval($info ['adType']);
		$this->mAdSubType = intval($info ['adSubType']);
		if ($this->mAdType == Common_Service_Const::CUSTOME_AD_SUB_TYPE && empty ($this->mAdSubType)) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'adSubType is error');
		}
		if (!$info ['appKey']) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'appKey is error');
		}
		$this->mAppKey = $info ['appKey'];
		if (count(explode('_', $sp ['sp'])) < 11) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'sp is error');
		}
		if ($sp ['platform'] != Common_Service_Const::ANDRIOD_PLATFORM && $sp ['platform'] != Common_Service_Const::IOS_PLATFORM) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'platform is error');
		}
		$this->mPlatform = $sp ['platform'];
		if (intval($sp ['screenDirection']) != Common_Service_Const::SCREEN_CROSS && intval($sp ['screenDirection']) != Common_Service_Const::SCREEN_VERTICAL) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'screenDirection is error');
		}
		if (!$sp ['brand']) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'brand is error');
		}
		if (!$sp ['screenSize']) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'screenSize is error');
		}
		if (!$sp ['uuid']) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'uuid is error');
		}
		$this->mUuid = $sp ['uuid'];
		$this->mUdid = $sp ['udid'];
		$this->isReportToMonitor = 1;
	}

	/**
	 * 功能:获取兼容新旧版本SDK的跳转类型
	 * jumpType: 跳转类型，0表示静默下载(针对安卓)，1表示跳转市场应用(ios为Appstore,安卓为GooglePlay)，2表示跳转系统默认浏览器，3表示跳转自建浏览器，4表示打开列表广告，5表示自定义动作，6表示无动作，7表示通知栏下载(针对安卓），8表示商店内页打开（IOS）。目前仅0,1,2,3,7,8有价值
	 * 当客户端版本≤0.1.0时，若配置的jumptype=7，实际下发0；若配置的jumptype=8，实际下发1
	 * 当客户端版本＞0.1.0时，按实际配置下发
	 *
	 * @param type $clientVersion
	 *            请求的SDK的版本号
	 * @param type $jumpType
	 */
	private function getJumpTypeByVerion($clientVersion, $jumpType)
	{
		if ($clientVersion && version_compare($clientVersion, '0.1.0', '<=')) {
			$compatible_jumptype_config = array(
				7 => 0,
				8 => 1
			); // key为新版本SDK的下发值,value为旧版本SDK需要的下发值
			if (isset ($compatible_jumptype_config [$jumpType])) {
				$jumpType = $compatible_jumptype_config [$jumpType];
			}
		}
		return $jumpType;
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

	private function formatMacroLink($link, $deepReplaceParam, $clientVersion, $isReturnArray)
	{
		if (empty ($link)) {
			if ($isReturnArray) {
				return array();
			}
			return '';
		}
		$canReplaceMacro = $this->getClientCanUseMacro($clientVersion);
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
				$this->mUdid,
				$this->mUdid,
				urlencode(common::getWebRoot() . '/api/conversion/postback/?originalityId=' . $deepReplaceParam ['originalityId'] . '&deviceId=' . $this->mUdid . '&requestId=' . $deepReplaceParam ['requestId'])
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
	 * 判断客户端哪些版本能适应宏定义IOS 2.1.4及以上，android 2.3.0及以上
	 *
	 * @param type $clientVersion
	 * @return boolean
	 */
	private function getClientCanUseMacro($clientVersion)
	{
		if ($this->mPlatform == Common_Service_Const::ANDRIOD_PLATFORM && version_compare($clientVersion, '0.4.0', '>=')) {
			return true;
		} else if ($this->mPlatform == Common_Service_Const::IOS_PLATFORM && version_compare($clientVersion, '0.3.0', '>=')) {
			return true;
		}
		return false;
	}

	private function initIp()
	{
		$this->ip = Common::getClientIP(); // '173.82.151.163' ;
		return $this->ip;
	}
}