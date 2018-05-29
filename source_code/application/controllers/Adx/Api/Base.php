<?php
if (!defined('BASE_PATH'))
	exit ('Access Denied!');

class Adx_Api_BaseController extends Adx_BaseController
{
	const LOG_TAG = 'getAdListAction';
	const LOG_FILE = 'getAdList.log';
	// 付费用户
	const PAY_USER = 1;
	// 活跃用户
	const ACTIVE_USER = 2;
	protected $mPostData = null;
	protected $mDspEventParam = NULL;
	protected $ip = null;
	protected $mUserAreaInfo = null;
	protected $mPriorityAdsList = array();
	protected $mBasePrice = 0.00;
	protected $mAdType = null;
	protected $mAdSubType = null;
	protected $mAppInfo = array();
	protected $mBidId = null;
	protected $mPlatform = null;
	protected $mUuid = NULL;
	protected $mGlobalConfig = null;
	protected $mAppPositonList = NULL;
	protected $mFixPriceDspList = array();
	protected $mRequestId = null;
	protected $mIsBidInfoList = true;
	protected $mBlockId = null;
	protected $blockInfo = array();
	protected $mAdTypeDesc = null;
	protected $mFlowAdTypeRel = null;
	protected $mUseDirectAppkey = array(
		'e19081b4527963d70c7a', // '地铁跑酷(安桌)'
		'8E69498B356D95CCB579' // '神庙逃亡2(安桌)'
	);
	protected $dspInstances = array();
	protected $mConditionRelFuntion = array(
		'channel_conf_type' => 'checkChannelExistInConf',
		'area_conf_type' => 'checkAreaExistInConf',
		'game_conf_type' => 'checkGameVersionExistInConf',
		'user_conf_type' => 'checkUserExistInConf',
		'sys_conf_type' => 'checkSysVersionExistInConf',
	);
	protected $mAbConfRelFun = array(
		'channel_conf_type' => 'checkChannelExistInConf',
		'area_conf_type' => 'checkAreaExistInConf',
		'game_conf_type' => 'checkGameVersionExistInConf',
		'sys_conf_type' => 'checkSysVersionExistInConf',
	);
	//用户对象
	protected $mUserObject = 0;
	//测试配置
	protected $mAbTestConf = NULL;
	protected $mAbTestFlowId = 0;
	protected $mAbtestConRelId = 0;


	public function init()
	{
		parent::init();
	}

	public function checkIntergrationPostParam()
	{
		$this->isReportToMonitor = 1;
		$inputJson = file_get_contents('php://input');
		if (!Common::is_json($inputJson)) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'invalid post input format');
		}
		$this->mPostData = json_decode($inputJson, TRUE);

		$this->mAdType = intval($this->mPostData ['adType']);
		$this->mAdSubType = intval($this->mPostData ['adSubType']);
		if (!array_key_exists($this->mAdType, Common_Service_Const::$mAdSubType)) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'adType is error');
		}

		$this->mAppKey = $this->mPostData ['app'] ['appKey'];
		if (!$this->mAppKey) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'appKey is error');
		}
		$this->mPlatform = $this->mPostData ['device'] ['platform'];
		if ($this->mPlatform != Common_Service_Const::ANDRIOD_PLATFORM && $this->mPlatform != Common_Service_Const::IOS_PLATFORM) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'platform is error');
		}
		//补全设备号
		if (!$this->mPostData ['device'] ['deviceId']) {
			$this->mPostData ['device'] ['deviceId'] = '88888888';
		}
		$this->mUuid = $this->mPostData ['device'] ['deviceId'];
		if (!$this->mUuid) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'deviceId is error');
		}
		$this->mUserId = $this->mPostData ['user'] ['id'];
		$this->mIsTest = $this->mPostData ['isTest'];
	}


	public function checkAdPostParam($isCheck = true)
	{
		$this->isReportToMonitor = 1;
		$inputJson = file_get_contents('php://input');
		if (!Common::is_json($inputJson)) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'invalid post input format');
		}

		$this->mPostData = json_decode($inputJson, TRUE);
		$this->mProviderId = $this->mPostData ['providerId'];
		if (empty ($this->mProviderId)) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'providerId is empty');
		}
		$this->mAdType = intval($this->mPostData ['adType']);
		$this->mAdSubType = intval($this->mPostData ['adSubType']);
		if (!array_key_exists($this->mAdType, Common_Service_Const::$mAdSubType)) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'adType is error');
		}
		$this->mAppKey = $this->mPostData ['app'] ['appKey'];
		if (!$this->mAppKey) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'appKey is error');
		}
		// 原生广告必须传blockid
		if ($this->mAdType == Common_Service_Const::ENBED_AD_SUB_TYPE) {
			$this->mBlockId = $this->mPostData ['imp'] [0] ['blockId'];
			if (empty ($this->mBlockId)) {
				$this->output(Util_ErrorCode::PARAMS_CHECK, 'enbed blockId is error');
			}
			// 检测广告位是否打开
			$params ['dever_pos_key'] = $this->mBlockId;
			$params ['del'] = MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG;
			$this->blockInfo = MobgiApi_Service_AdDeverPosModel::getBy($params);
			if (empty ($this->blockInfo)) {
				$this->output(Util_ErrorCode::PARAMS_CHECK, 'enbed blockInfo is not exist');
			}
			if ($this->blockInfo ['state'] == 0) {
				$this->output(Util_ErrorCode::POS_STATE_CHECK, 'enbed blockInfo is not open');
			}
			$this->mAdSubType = $this->blockInfo ['ad_sub_type'];
			if (!array_key_exists($this->mAdSubType, Common_Service_Const::$mEnbedSubType)) {
				$this->output(Util_ErrorCode::PARAMS_CHECK, 'enbed  adSubType is error');
			}
		}

		if (!stripos($this->mPostData ['device'] ['resolution'], '*')) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'resolution is error 如600*800');
		}
		$this->mPlatform = $this->mPostData ['device'] ['platform'];
		if ($this->mPlatform != Common_Service_Const::ANDRIOD_PLATFORM && $this->mPlatform != Common_Service_Const::IOS_PLATFORM) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'platform is error');
		}
		if (intval($this->mPostData ['device'] ['screenDirection']) != Common_Service_Const::SCREEN_CROSS && intval($this->mPostData ['device'] ['screenDirection']) != Common_Service_Const::SCREEN_VERTICAL) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'screenDirection is error');
		}
		if (!isset ($this->mPostData ['device'] ['brand'])) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'brand is error');
		}
		if (!$this->mPostData ['device'] ['screenSize']) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'screenSize is error');
		}
		//补全设备号
		if (!$this->mPostData ['device'] ['deviceId']) {
			$this->mPostData ['device'] ['deviceId'] = '88888888';
		}
		$this->mUuid = $this->mPostData ['device'] ['deviceId'];
		if (!$this->mUuid) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'deviceId is error');
		}
		if(!$this->mPostData ['device'] ['net']){
			$this->mPostData ['device'] ['net'] =1;
		}
		$this->mUserId = $this->mPostData ['user'] ['id'];
		$this->mIsTest = $this->mPostData ['isTest'];
		// 下发的bidinfo是否是list
		$this->mIsBidInfoList = Common::isBidInfoList($this->mPostData ['device'] ['platform'], $this->mPostData ['extra'] ['sdkVersion'], $this->mAdType);
	}

	/**
	 * 创建唯一的竞价id
	 *
	 * @return type
	 */
	public function createRequestId()
	{
		$time = explode('.', microtime(true));
		$key = 'request_id::' . date('Ymd');
		$cache = Cache_Factory::getCache();
		$requestId = date('YmdHis') . str_pad($time [1], 4, "0", STR_PAD_LEFT) . str_pad($cache->increment($key), 14, "0", STR_PAD_LEFT);
		return $requestId;
	}

	public function getAttachPath()
	{
		$attachPath = Common::getAttachPath();
		if (Util_Environment::isTest()) {
			if ($this->isHttps()) {
				$webRoot = Common::getWebRoot();
				$attachPath = str_replace('http', 'https', $webRoot) . '/attachs';
			}
		}
		if ($this->mPlatform == Common_Service_Const::ANDRIOD_PLATFORM && Util_Environment::isOnline()) {
			if (!$this->isHttps()) {
				$attachPath = str_replace('https', 'http', $attachPath);
			}
		}
		return $attachPath;
	}


	public function initIp()
	{
		//$this->ip = '183.14.30.99';
		//$this->ip = '210.21.221.18';//Common::getClientIP (); // '173.82.151.163'
		$this->ip = $this->mPostData['device']['ip'] ? $this->mPostData['device']['ip'] : Common::getClientIP(); // '173.82.151.163'
		if ($this->isDebugMode()) {
			$this->mDebugInfo['ip'] = $this->ip;
		}
		$this->mUserAreaInfo = $this->getAreaCacheDataByIp($this->ip);
		if ($this->isDebugMode()) {
			$this->mDebugInfo['areaInfo'] = $this->mUserAreaInfo;
		}
		return $this->ip;
	}


	public function getDefaultFlowConfId($flowConfList)
	{
		if (!is_array($flowConfList)) {
			return false;
		}
		$defaultFlowConfId = 0;
		foreach ($flowConfList as $confInfo) {
			if ($confInfo ['conf_type'] == MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE) {
				$defaultFlowConfId = $confInfo ['id'];
				break;
			}
		}
		return $defaultFlowConfId;
	}

	public function getCustomeFlowConfId($flowConfList)
	{
		if (!is_array($flowConfList)) {
			return false;
		}
		$flowId = 0;
		$len = count($this->mConditionRelFuntion);
		for ($i = $len; $i >= 1; $i--) {
			$conditionArr = Common::combination(array_keys($this->mConditionRelFuntion), $i);
			$conditionArr = $this->sortArr($conditionArr);
			$flowId = $this->getFlowIdByUserCondition($flowConfList, $conditionArr, $i);
			if ($flowId) {
				break;
			}
		}
		return $flowId;
	}

	public function sortArr($arr)
	{
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
				if (!in_array('game_conf_type', $arr[$k]) && !in_array('channel_conf_type', $arr[$k]) && !in_array('area_conf_type', $arr[$k])) {
					$tmp = $arr[$k + 1];
					$arr[$k + 1] = $arr[$k];
					$arr[$k] = $tmp;
					$flag = true;
				}
				if (!in_array('user_conf_type', $arr[$k]) && !in_array('game_conf_type', $arr[$k]) && !in_array('channel_conf_type', $arr[$k]) && !in_array('area_conf_type', $arr[$k])) {
					$tmp = $arr[$k + 1];
					$arr[$k + 1] = $arr[$k];
					$arr[$k] = $tmp;
					$flag = true;
				}
			}
		}
		return $arr;
	}

	public function getFlowIdByUserCondition($flowConfList, $conditionArr, $num)
	{
		if (!is_array($flowConfList)) {
			return 0;
		}

		foreach ($conditionArr as $condition) {
			if (count($condition) == $num) {
				foreach ($flowConfList as $flowConf) {
					if ($flowConf['conf_num'] == $num) {
						$flag = 0;
						foreach ($condition as $conditionType) {
							if ($flowConf[$conditionType] && call_user_func_array(array($this, $this->mConditionRelFuntion [$conditionType]), array($flowConf))) {
								$flag = 1;
							} else {
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

	public function checkChannelExistInConf($confInfo, $isJson = true)
	{
		if (empty ($confInfo)) {
			return false;
		}
		if ($isJson) {
			$channelConf = json_decode($confInfo ['channel_conf'], true);
		} else {
			$channelConf = $confInfo ['channel_conf'];
		}
		if (empty ($channelConf)) {
			return false;
		}

		$channelId = $this->mPostData ['app'] ['channelId'];

		if (in_array($channelId, $channelConf)) {
			return true;
		}
		return false;
	}


	/**
	 * 检查区域
	 *
	 * @param unknown $fiterConf
	 *            $type 1
	 */
	public function checkAreaExistInConf($confInfo, $isJson = true)
	{
		if (empty ($confInfo)) {
			return false;
		}
		if ($isJson) {
			$areaConfIds = json_decode($confInfo ['area_conf'], true);
		} else {
			$areaConfIds = $confInfo ['area_conf'];
		}
		if (empty ($areaConfIds)) {
			return false;
		}

		if (empty ($this->mUserAreaInfo)) {
			return false;
		}

		$findAreaFlag = 0;
		$provinceList = common::getConfig('areaConfig', 'provinceList');
		foreach ($areaConfIds as $val) {
			if (is_numeric($val)) {
				if (mb_strstr($this->mUserAreaInfo ['province'], $provinceList [$val]) !== false) {
					$findAreaFlag = 1;
					break;
				}
			}
		}
		if (!$findAreaFlag) {
			if (in_array($this->mUserAreaInfo ['country_code'], $areaConfIds)) {
				$findAreaFlag = 1;
			}
		}
		if ($findAreaFlag) {
			return true;
		}
		return false;
	}

	/**
	 * 检查渠道
	 *
	 * @param unknown $fiterConf
	 */
	public function checkSysVersionExistInConf($confInfo, $isJson = true)
	{
		$operator = array(1 => '>', 2 => '<', 3 => '=');
		if (empty ($confInfo)) {
			return false;
		}
		if ($isJson) {
			$gameConf = json_decode($confInfo ['sys_conf'], true);
		} else {
			$gameConf = $confInfo ['sys_conf'];
		}
		if (empty ($gameConf)) {
			return false;
		}
		$userSysVersion = $this->mPostData ['device'] ['version'];
		$pattern = '/\d+\.\d/';
		if (!preg_match($pattern, $userSysVersion, $match)) {
			return false;
		}
		$version = $match[0];
		if ($gameConf['sys_conf_content']) {
			foreach ($gameConf['sys_conf_content'] as $sysConf) {

				if (version_compare($version, $sysConf, $operator[$gameConf['sys_conf_condition']])) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * 检查渠道
	 *
	 * @param unknown $fiterConf
	 */
	public function checkGameVersionExistInConf($confInfo, $isJson = true)
	{
		if (empty ($confInfo)) {
			return false;
		}
		if ($isJson) {
			$gameConf = json_decode($confInfo ['game_conf'], true);
		} else {
			$gameConf = $confInfo ['game_conf'];
		}
		if (empty ($gameConf)) {
			return false;
		}
		$gameVersion = $this->mPostData ['app'] ['version'];
		if (in_array($gameVersion, $gameConf)) {
			return true;
		}
		return false;
	}

	public function checkUserExistInConf($confInfo)
	{
		if (empty ($confInfo)) {
			return false;
		}
		$userConf = json_decode($confInfo ['user_conf'], true);
		if (empty ($userConf)) {
			return false;
		}
		$gameId = $this->mAppInfo ['out_game_id'];
		$userId = $this->mPostData ['device'] ['deviceId'];
		$isNewUser = $this->mPostData ['extra'] ['isNewUser'];
		if ($gameId && $this->isAndriodPlatform()) {
			// 过滤30日付费用户
			$payUserKey = $gameId . '_' . md5($userId) . '_payUser';
			if (in_array(self::PAY_USER, $userConf)) {
				$redis = $this->getCacheByParams($gameId, $userId);
				if ($redis->get($payUserKey)) {
					return true;
				}
			}
		}
		// 新增用户过滤
		if (in_array(self::ACTIVE_USER, $userConf)) {
			if ($isNewUser) {
				return true;
			}
		}
		return false;
	}

	public function isAndriodPlatform()
	{
		return $this->mPostData ['device'] ['platform'] == Common_Service_Const::ANDRIOD_PLATFORM;
	}

	public function getAreaCacheDataByIp($ip)
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

	/**
	 * 根据游戏ID和用户id获取缓存库的ID
	 *
	 * @param unknown $gameId
	 * @param unknown $userId
	 */
	public function getCacheByParams($gameId, $userId)
	{
		$hashKey = md5($gameId . '_' . md5($userId));
		$cacheResourceType = hexdec(substr($hashKey, 0, 2)) % 3;
		$cacheResource = 'AD_USER_CACHE_REDIS_SERVER' . $cacheResourceType;
		$cacheRedis = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS, $cacheResource);
		return $cacheRedis;
	}

	public function checkSdkVersion()
	{
		$sdkVersion = $this->mPostData ['extra'] ['sdkVersion'];
		if (in_array($this->mAdType, array(
			1,
			2
		))) {
			if (version_compare($sdkVersion, '0.4.1') >= 0) {
				return true;
			}
			return false;
		}
		return true;
	}

	public function getFlowAdTypeRelByFlowId($flowId)
	{
		$parms ['flow_id'] = $flowId;
		$parms ['app_key'] = $this->mAppKey;
		$parms ['ad_type'] = $this->mAdType;
		$flowAdTypeRel = MobgiApi_Service_FlowAdTypeRelModel::getBy($parms);
		return $flowAdTypeRel;
	}

	public function getAbTestAdsConf($flowId, $confType)
	{
		$parms ['flow_id'] = $flowId;
		$parms ['conf_type'] = $confType;
		$parms ['ad_type'] = $this->mAdType;
		$flowAdsRel = MobgiApi_Service_AbFlowAdsRelModel::getsBy($parms);
		if ($flowAdsRel) {
			$temp = array();
			if ($confType == MobgiApi_Service_AbFlowAdsRelModel::DSP_ADS) {
				foreach ($flowAdsRel as $val) {
					$temp [] = array(
						'dsp_ads_id' => $val ['ads_id']
					);
				}
			} else {
				foreach ($flowAdsRel as $val) {
					$temp [] = array(
						'current_ads_id' => $val ['ads_id'],
						'weight' => strval(floatval($val ['weight'])),
						'position' => $val ['position'],
						'limit_num' => $val ['limit_num']
					);
				}
			}
			return $temp;
		}
		return array();
	}

	public function getAdsConf($flowId, $confType)
	{
		$flowAdsRel = $this->getFlowAdsRelByFlowId($flowId, $confType);
		if ($flowAdsRel) {
			$temp = array();
			if ($confType == MobgiApi_Service_FlowAdsRelModel::DSP_ADS) {
				foreach ($flowAdsRel as $val) {
					$temp [] = array(
						'dsp_ads_id' => $val ['ads_id']
					);
				}
			} else {
				foreach ($flowAdsRel as $val) {
					$temp [] = array(
						'current_ads_id' => $val ['ads_id'],
						'weight' => strval(floatval($val ['weight'])),
						'position' => $val ['position'],
						'limit_num' => $val ['limit_num']
					);
				}
			}
			return $temp;
		}
		return array();
	}

	/**
	 * 获取流量配置的权限列表
	 *
	 * @param unknown $policyConf
	 */
	public function getFlowConfWeightList($flowConf)
	{
		// 流量配置的广告商列表
		$flowConfAdsIds = $flowConf ['general_ads_conf'];
		$flowConfAdsIds = $this->getAvailableAdsList($flowConfAdsIds);
		if (empty ($flowConfAdsIds)) {
			return false;
		}
		// 获取流量位置列表
		$adsPostionList = $this->getAdsPositonListFromCache($flowConfAdsIds, $flowConf);
		$adsPostionList = Common::resetKey($adsPostionList, 'current_ads_id');
		if (empty ($adsPostionList)) {
			return false;
		}
		return $adsPostionList;
	}

	/**
	 * 获取配置可用的广告商列表
	 *
	 * @param unknown $intergrationSubType
	 */
	public function getAvailableAdsList($flowConfAdsIds)
	{
		if (is_array($flowConfAdsIds)) {
			// 清除掉已经设置位置的广告商
			foreach ($flowConfAdsIds as $key => $val) {
				if (!$val ['weight']) {
					unset ($flowConfAdsIds [$key]);
				}
			}
		}
		return $flowConfAdsIds;
	}

	/**
	 * 取得广告的位置列表
	 */
	public function getAdsPositonListFromCache($flowConfAdsIds, $flowConf)
	{
		$cache = Cache_Factory::getCache();
		$key = 'intergration_postion_list_' . $this->mAppKey . '_' . $this->mAdType . '_' . $flowConf ['flow_id'];
		$cacheData = $cache->get($key);
		if (empty ($cacheData) || $this->checkArrDiff($cacheData, $flowConfAdsIds)) {
			// 当前广告商的位置
			$exprie = 3600;
			$cache->set($key, $flowConfAdsIds, $exprie);
			// 保存广告商的位置的日志
			$this->saveAdsPostionLog($flowConfAdsIds);
			return $flowConfAdsIds;
		}
		return $cacheData;
	}

	public function checkArrDiff($origal, $des)
	{
		if (empty ($origal)) {
			return true;
		}
		$origal = Common::resetKey($origal, 'current_ads_id');
		$des = Common::resetKey($des, 'current_ads_id');
		foreach ($des as $key => $val) {
			if (!isset ($origal [$key])) {
				return true;
			}
			if (($val ['weight'] != $origal [$key] ['weight']) || ($val ['limit_num'] != $origal [$key] ['limit_num'])) {
				return true;
			}
		}
		return false;
	}

	public function saveAdsPostionLog($cacheData)
	{
		if (empty ($cacheData)) {
			return false;
		}
		$cacheData = Common::resetKey($cacheData, 'current_ads_id');
		$dataArr ['appKey'] = $this->mAppKey;
		$dataArr ['intergrationType'] = $this->mAdType;
		$dataArr ['effectTime'] = date('Y-m-d H:i:s');
		$dataArr ['adsPositonList'] = json_encode($cacheData);
		$cache = Common::getQueue('intergration_position_list');
		$cache->push('RQ:intergration_position_list', json_encode($dataArr));
	}

	public function fillPriorityAdsConfList($adsConf)
	{
		$data = array();
		if (empty ($adsConf)) {
			return $data;
		}
		foreach ($adsConf as $key => $val) {
			$data [$val ['current_ads_id']] = array(
				'current_ads_id' => $val ['current_ads_id'],
				'index' => $val ['position'],
				'limit_num' => $val ['limit_num']
			);
		}
		return $data;
	}

	public function getFlowAppRel()
	{
		if (empty ($this->mFlowAdTypeRel) || !$this->mFlowAdTypeRel ['is_app_rel']) {
			return array();
		}
		$parms ['flow_id'] = $this->mFlowId;
		$parms ['ad_type'] = $this->mAdType;
		$result = MobgiApi_Service_FlowAppRelModel::getsBy($parms);
		if ($result) {
			$tmp = array();
			foreach ($result as $val) {
				$tmp [$val ['ads_id']] = array(
					'third_party_app_key' => $val ['third_party_app_key'],
					'third_party_secret' => $val ['third_party_secret']
				);
			}
			return $tmp;
		}
		return array();
	}

	public function getFlowPosPolicy()
	{
		if (empty ($this->mFlowAdTypeRel) || !$this->mFlowAdTypeRel ['is_block_policy']) {
			return array();
		}
		$parms ['flow_id'] = $this->mFlowId;
		$parms ['ad_type'] = $this->mAdType;
		$result = MobgiApi_Service_FlowPosPolicyRelModel::getsBy($parms);
		if ($result) {
			$tmp = array();
			foreach ($result as $val) {
				$tmp [$val ['pos_key']] = array(
					'rate' => $val ['rate'],
					'status' => $val ['status'],
					'show_limit' => $val ['limit_num']
				);
			}
			return $tmp;
		}
		return array();
	}

	public function getFlowPosRel()
	{
		if (empty ($this->mFlowAdTypeRel) || !$this->mFlowAdTypeRel ['is_app_rel']) {
			return array();
		}
		$parms ['flow_id'] = $this->mFlowId;
		$parms ['ad_type'] = $this->mAdType;
		$result = MobgiApi_Service_FlowPosRelModel::getsBy($parms);
		if ($result) {
			$tmp = array();
			foreach ($result as $val) {
				$tmp [$val ['pos_key']] [$val ['ads_id']] = $val ['third_party_block_id'];
			}
			return $tmp;
		}
		return array();
	}

	public function initAdType()
	{
		$adTypeDescArr = array(
			1 => 'video',
			2 => 'pic',
			3 => 'custome',
			4 => 'splash',
			5 => 'enbed'
		);
		$this->mAdTypeDesc = $adTypeDescArr [$this->mAdType];
	}

	public function getAppPosInfo($appInfo)
	{
		$params ['app_id'] = $appInfo ['app_id'];
		$params ['del'] = MobgiApi_Service_AdDeverPosModel::NOT_DEL_FLAG;
		$appPosInfo = MobgiApi_Service_AdDeverPosModel::getsBy($params);
		if (empty ($appPosInfo)) {
			return false;
		}
		$posTypeDesc = Common_Service_Const::$mAdPosType;
		$returnData = array();
		foreach ($appPosInfo as $posInfo) {
			if ($posInfo ['state'] != MobgiApi_Service_AdDeverPosModel::OPEN_STATUS) {
				continue;
			}
			if ($posInfo ['pos_key_type'] == $posTypeDesc [$this->mAdType]) {
				$returnData [$posInfo ['dever_pos_key']] = array(
					'rate' => $posInfo ['rate'],
					'show_limit' => $posInfo ['limit_num'],
					'pos_name' => $posInfo ['dever_pos_name']
				);
			}
		}
		if (empty ($returnData)) {
			return false;
		}
		return $returnData;
	}

	public function getAdsAppRel()
	{
		// 广告商参数设置信息
		$params ['app_key'] = $this->mAppKey;
		$params ['ad_sub_type'] = $this->mAdType;
		$adsAppRel = MobgiApi_Service_AdsAppRelModel::getsBy($params);
		$adsAppRel = Common::resetKey($adsAppRel, 'ads_id');
		//若是白名单方式，则如有配置则直接返回白名单配置的值
		if ($this->mWhitelistConfig) {
			$adsAppRelWhitelist = MobgiApi_Service_AdsAppRelWhitelistModel::getsBy($params);
			if ($adsAppRelWhitelist) {
				foreach ($adsAppRelWhitelist as $val) {
					$adsAppRel[$val['ads_id']] = $val;
				}
			}
		}
		return $adsAppRel;
	}

	/**
	 * 获取广告商的额外配置
	 *
	 * @param type $adsId
	 * @param type $extra_config
	 * @return type
	 */
	public function getExtraInfos($adsId)
	{
		$extraInfos = array();
		if ($adsId == 'Changxian') {
			$extraInfos ['limit_minimum_speed'] = 0;
			$extraInfos ['minimum_speed'] = 0;
			$extraInfos ['lazy_loading'] = intval($this->mFlowAdTypeRel ['is_delay']);
			$extraInfos ['lazy_loading_time'] = intval($this->mFlowAdTypeRel ['time']);
		}
		if (empty ($extraInfos)) {
			$extraInfos = ( object )array();
		}
		return $extraInfos;
	}

	/**
	 *
	 * @param unknown $adsConfList
	 */
	public function fillGlobalConfigOutputData()
	{
		$globalConfig = $this->getGlobalConfig();
		$data ['supportNetworkType'] = intval($globalConfig ['play_network']);
		$data ['lifeCycle'] = intval($globalConfig ['life_cycle']) * 1000;
		if ($this->mAdType == Common_Service_Const::SPLASH_AD_SUB_TYPE) {
			$data ['isShowView'] = isset ($globalConfig ['is_show_view']) ? intval($globalConfig ['is_show_view']) : 0;
			$data ['viewDelay'] = isset ($globalConfig ['show_view_time']) ? intval($globalConfig ['show_view_time']) * 1000 : 0;
		}
		$data ['isUseTemplate'] = boolval(0);
		$data ['templateShowTime'] = 0;
		$data ['templateUrl'] = '';
		if (in_array($this->mAdType, array(Common_Service_Const::VIDEO_AD_SUB_TYPE, Common_Service_Const::PIC_AD_SUB_TYPE, Common_Service_Const::SPLASH_AD_SUB_TYPE))) {
			$data ['isUseTemplate'] = boolval($globalConfig ['is_use_template']);
			$url = '';
			if ($globalConfig ['is_use_template']) {
				$result = MobgiApi_Service_TemplateModel::getByID($globalConfig ['template_id']);
				if ($result) {
					$attach = Common::getAttachPath();
					$url = $attach . $result ['url'];
				}
				if (in_array($this->mAdType, array(Common_Service_Const::VIDEO_AD_SUB_TYPE, Common_Service_Const::SPLASH_AD_SUB_TYPE))) {
					$data ['templateShowTime'] = isset ($globalConfig ['template_show_time']) ? intval($globalConfig ['template_show_time']) * 1000 : 0;
				}
				$data ['templateUrl'] = $url;
			}
		}
		return $data;
	}

	public function getGlobalConfig()
	{
		// 广告商参数设置信息
		$params ['app_key'] = $this->mAppKey;
		$params ['ad_sub_type'] = $this->mAdType;
		// 若是白名单方式，则如有配置则直接返回白名单配置的值
		if ($this->mWhitelistConfig) {
			$adsAppRelWhitelist = MobgiApi_Service_AdsAppRelWhitelistModel::getBy($params);
			if ($adsAppRelWhitelist) {
				return $adsAppRelWhitelist;
			}
		}
		$adsAppRel = MobgiApi_Service_AdsAppRelModel::getBy($params);
		if (empty ($adsAppRel)) {
			return false;
		}
		return $adsAppRel;
	}


	public function getAdsPosRel()
	{
		// 聚合广告位ID的配置
		$params ['app_key'] = $this->mAppKey;
		$params ['ad_sub_type'] = $this->mAdType;
		$retrunData = array();
		$adsPosRelResult = MobgiApi_Service_AdsPosRelModel::getsBy($params);
		if ($adsPosRelResult) {
			foreach ($adsPosRelResult as $val) {
				$retrunData[$val['pos_key']][$val['ads_id']] = array(
					'third_party_block_id' => $val['third_party_block_id'],
					'third_party_report_id' => $val['third_party_report_id'],
					'state' => $val['state']
				);
			}
		}
		//若是白名单方式，则如有配置则直接返回白名单配置的值
		if ($this->mWhitelistConfig) {
			$adsPosRelWhitelistResult = MobgiApi_Service_AdsPosRelWhitelistModel::getsBy($params);
			if ($adsPosRelWhitelistResult) {
				foreach ($adsPosRelWhitelistResult as $val) {
					$retrunData[$val['pos_key']][$val['ads_id']]['third_party_block_id'] = $val['third_party_block_id'];
					$retrunData[$val['pos_key']][$val['ads_id']]['third_party_report_id'] = $val['third_party_report_id'];
					$retrunData[$val['pos_key']][$val['ads_id']]['state'] = $val['state'];
				}
			}
		}
		return $retrunData;
	}


	/**
	 * 获取配置的DSP列表
	 */
	public function getAllDspListFromDb()
	{
		$dspList = array();
		if ($this->mWhitelistConfig) {
			$policyConf = $this->getWhitelistPolicyConfList();
			if (empty ($policyConf)) {
				return $dspList;
			}
			$dspArr = $policyConf ['dsp_ads_conf'];
			if (empty ($dspArr)) {
				return $dspList;
			}
			// 初始化低价
			$this->mBasePrice = $policyConf ['price'];
		} else {
			$flowId = $this->getFlowConfIdByUser();
			if (!$flowId) {
				return $dspList;
			}
			$flowAdTypeRel = $this->getFlowAdTypeRelByFlowId($flowId);
			if (empty ($flowAdTypeRel) || !$flowAdTypeRel ['status'] || !$flowAdTypeRel ['is_use_dsp']) {
				return $dspList;
			}
			// 初始化低价
			$this->mBasePrice = $flowAdTypeRel ['price'];
			$dspArr = $this->getAdsConf($flowId, MobgiApi_Service_FlowAdsRelModel::DSP_ADS);

		}
		foreach ($dspArr as $dspItem) {
			if ($dspItem ['dsp_ads_id']) {
				$dspList [$dspItem ['dsp_ads_id']] = $dspItem ['dsp_ads_id'];
			}
		}
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['beforefiterDspList'] = $dspList;
		}
		if (empty ($dspList)) {
			return $dspList;
		}
		// 原生广告类型需要判断是否广告位是否已经开启
		if ($this->mAdType == Common_Service_Const::ENBED_AD_SUB_TYPE) {
			$relParams = array();
			$relParams ['app_key'] = $this->mAppKey;
			$relParams ['ads_id'] = array(
				'IN',
				array_keys($dspList)
			);
			$relParams ['pos_key'] = $this->mBlockId;
			$adsPosRel = MobgiApi_Service_AdsPosRelModel::getsBy($relParams);
			if (empty ($adsPosRel)) {
				return array();
			}
			$adsPosRel = Common::resetKey($adsPosRel, 'ads_id');
			foreach ($dspList as $dspKey => $dspItem) {
				if ($adsPosRel [$dspKey] ['state'] != MobgiApi_Service_AdsPosRelModel::OPEN_STATE) {
					unset ($dspList [$dspKey]);
				}
			}
		}
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['afterfiterDspList'] = $dspList;
		}
		return $dspList;
	}

	public function getFlowAdsRelByFlowId($flowId, $confType)
	{
		$flowAdsRelparms ['flow_id'] = $flowId;
		$flowAdsRelparms ['conf_type'] = $confType;
		$flowAdsRelparms ['app_key'] = $this->mAppKey;
		$flowAdsRelparms ['ad_type'] = $this->mAdType;
		$flowAdsRel = MobgiApi_Service_FlowAdsRelModel::getsBy($flowAdsRelparms);
		if (empty ($flowAdsRel)) {
			return array();
		}
		return $flowAdsRel;
	}


	/**
	 * 随机
	 * @param unknown $weightList
	 */
	public function rateWeight($weightList)
	{
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


	/**
	 * 是否走白名单
	 *
	 * @return type
	 */
	public function isWhitelist()
	{
		$deviceId = $this->mPostData ['device'] ['deviceId'];
		$parms['conf_type'] = MobgiApi_Service_AbConfModel::WHILELIST_CONF_TYPE;
		$parms['status'] = MobgiApi_Service_AbConfModel::STATUS_OPEN;
		$parms['start_time'] = array('<=', date('Y-m-d'));
		$parms['end_time'] = array('>=', date('Y-m-d'));
		$relust = MobgiApi_Service_AbConfModel::getsBy($parms);
		$returnData = 0;
		if (!$relust) {
			return $returnData;
		}
		$deviceList = array();
		foreach ($relust as $val) {
			$content = json_decode($val ['content']);
			foreach ($content as $device) {
				$deviceList [$device] = $val ['conf_id'];
				if ($device == $deviceId) {
					$this->mDevMode = $val['dev_mode'];
				}
			}
		}
		if (isset($deviceList[$deviceId])) {
			$returnData = $deviceList[$deviceId];
			if ($this->isDebugMode()) {
				$this->mDebugInfo ['whileList'] = $returnData;
			}
		}
		return $returnData;
	}

	/**
	 *
	 * @param unknown $url
	 * @param array $data
	 * @param string $method
	 * @param array $header
	 */
	public function getCurlObject($url, $data = array(), $method = 'POST', $header = array(CURLOPT_HTTPHEADER => array('Content-Type: application/json')), $isJson = true)
	{

		$options = array();
		switch (strtoupper($method)) {
			case 'GET' :
				$params = http_build_query($data);
				$parseUrl = parse_url($url);
				$url .= (isset ($parseUrl ['query']) ? '&' : '?') . $params;
				$options [CURLOPT_URL] = $url;
				break;
			case 'POST' :
				$options [CURLOPT_URL] = trim($url);
				$options [CURLOPT_POST] = true;
				$options [CURLOPT_POSTFIELDS] = $isJson ? json_encode($data) : $data;
				break;
			default :
				break;
		}
		// $options[CURLOPT_TIMEOUT] = 1;
		$options [CURLOPT_TIMEOUT_MS] = Util_Environment::isOnline() ? 500 : 10000; // 注意，毫秒超时一定要设置这个 超时时间200毫秒
		$options [CURLOPT_NOSIGNAL] = true;
		$options [CURLOPT_USERAGENT] = $this->getServer('HTTP_USER_AGENT');
		$options [CURLOPT_RETURNTRANSFER] = true;
		// $options[CURLOPT_PROXY] = '127.0.0.1:8888';
		foreach ($header as $key => $value) {
			$options [$key] = $value;
		}
		if (stripos($url, 'https') === 0) {
			$options [CURLOPT_SSL_VERIFYPEER] = false;
		}
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		return $ch;
	}

	/**
	 * 获取普通接入的dsp响应
	 *
	 * @param type $dspNo
	 * @param type $inputArr
	 * @return array
	 */
	public function getDspResponse($dspUrlList, $adsAppRelList)
	{

		// 广告位参数设置
		$adsPosRelResult = $this->getAdsPosRel();
		$adsPosRelList = $this->parseThirdPartyByBlockId($adsPosRelResult);

		$handles = $contents = array();
		// 初始化curl multi对象
		$mh = curl_multi_init();
		// 添加curl 批处理会话
		foreach ($dspUrlList as $dspId => $url) {
			$thirdAppkey = $this->getThirdPartyAppKey($adsAppRelList, $dspId);
			$thirdPosKey = $this->getThirdPartyPosKey($adsPosRelList, $dspId);
			$dspReqeustData = $this->getDspReqeustData($dspId, $thirdAppkey, $thirdPosKey);

			if ($this->isDebugMode()) {
				$this->mDebugInfo ['thirdPartyAppKey:' . $dspId] = $thirdAppkey;
				$this->mDebugInfo ['thirdPosKey:' . $dspId] = $thirdPosKey;
				$this->mDebugInfo ['dspReqeustData:' . $dspId] = $dspReqeustData;
			}
			if (in_array(strtolower($dspId), array(
				strtolower(Common_Service_Const::ETORON_DSP_ID),
				strtolower(Common_Service_Const::SMAATO_DSP_ID),
				strtolower(Common_Service_Const::ADIN_DSP_ID),
				strtolower(Common_Service_Const::BULEMOBI_DSP_ID)
			))) {
				$method = 'GET';
			} else {
				$method = 'POST';
				$isJson = true;
				if (strtolower($dspId) == strtolower(Common_Service_Const::OPERA_DSP_ID)) {
					$header = array(CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Accept: application/json'));
				} elseif (strtolower($dspId) == strtolower(Common_Service_Const::YOMOB_DSP_ID)) {
					$isJson = true;
					$ua = $this->mPostData['ua'] ? $this->mPostData['ua'] : $this->getServer('HTTP_USER_AGENT');
					$header = array(CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Accept: application/json', 'UserAgent: ' . $ua));
				} else {
					$header = array(CURLOPT_HTTPHEADER => array('Content-Type: application/json'));
				}
			}
			$this->sendDspEvent($dspId, 'request');
			$handles [$dspId] = $this->getCurlObject($url, $dspReqeustData, $method, $header, $isJson);
			curl_multi_add_handle($mh, $handles [$dspId]);
		}
		$active = null;
		do {
			$mrc = curl_multi_exec($mh, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		while ($active && $mrc == CURLM_OK) {
			if (curl_multi_select($mh) === -1) {
				usleep(100);
			}
			do {
				$mrc = curl_multi_exec($mh, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}
		// 获取批处理内容
		$contents = array();
		foreach ($handles as $dspId => $handle) {
			$content = curl_multi_getcontent($handle);
			$contents [$dspId] = curl_errno($handle) == 0 ? (in_array(strtolower($dspId), array(
				strtolower(Common_Service_Const::SMAATO_DSP_ID)
			)) ? $content : json_decode($content, true)) : '';
			if (curl_errno($handle) == 0) {
				$this->sendDspEvent($dspId, 'response');
			}
		}
		// 移除批处理句柄
		foreach ($handles as $handle) {
			curl_multi_remove_handle($mh, $handle);
		}
		// 关闭批处理句柄
		curl_multi_close($mh);
		return $contents;
	}

	public function parseThirdPartyByBlockId($adsPosRelResult)
	{
		$posList = array();
		if (empty ($adsPosRelResult)) {
			return false;
		}
		foreach ($adsPosRelResult as $posKey => $val) {
			foreach ($val as $adsId => $va) {
				if (in_array($this->mAdType, array(Common_Service_Const::VIDEO_AD_SUB_TYPE, Common_Service_Const::PIC_AD_SUB_TYPE))) {
					if ($va ['third_party_block_id']) {
						$posList [$adsId] = $va ['third_party_block_id'];
					}
				} else {
					$posList [$posKey] [$adsId] = $va ['third_party_block_id'];
				}
			}
		}
		if (in_array($this->mAdType, array(Common_Service_Const::VIDEO_AD_SUB_TYPE, Common_Service_Const::PIC_AD_SUB_TYPE))) {
			return $posList;
		}
		return $posList [$this->mBlockId];
	}


	public function getThirdPartyPosKey($adsPosRelList, $dspId)
	{
		return $adsPosRelList [$dspId];
	}

	public function getThirdPartyAppKey($adsAppRelList, $dspId)
	{
		// 交叉推广的appkey直接使用上报上来的appkey
		if ($this->mAdType == Common_Service_Const::CUSTOME_AD_SUB_TYPE) {
			return $this->mAppKey;
		} else {
			return $adsAppRelList [$dspId] ['third_party_app_key'];
		}
	}


	/**
	 *
	 * @param unknown $dspNo
	 * @param unknown $thirdAppkey
	 * @return string[]|number[]|unknown[]|NULL[]|mixed[]
	 */
	private function getDspReqeustData($dspNo, $thirdAppkey, $thirdPosKey)
	{
		$dspObj = Dsp_Factory::getDspInstances($dspNo);
		unset($this->mPostData['isTest']);
		$dspObj->setPostData($this->mPostData);
		$dspObj->setAppInfo($this->mAppInfo);
		$dspObj->setBlockInfo($this->blockInfo);
		$dspObj->setBidId($this->mBidId);
		$dspObj->setDspId($dspNo);
		$this->dspInstances [$dspNo] = $dspObj;
		return $dspObj->getRequestData($thirdAppkey, $thirdPosKey);
	}

	/**
	 * 推送dsp事件.
	 *
	 * @param type $dspId
	 * @param type $eventFlag
	 * @return type
	 */
	public function sendDspEvent($dspId, $eventFlag, $outBidId = 0)
	{
		$dspEventParam ['provider_id'] = $this->mProviderId;
		$dspEventParam ['bid_id'] = $this->mBidId;
		$dspEventParam ['out_bid_id'] = $outBidId ? $outBidId : 0;
		$dspEventParam ['app_key'] = $this->mPostData ['app'] ['appKey'];
		$dspEventParam ['block_id'] = empty ($this->mPostData ['imp'] [0] ['blockId']) ? '-1' : $this->mPostData ['imp'] [0] ['blockId'];
		$dspEventParam ['platform'] = $this->mPostData ['device'] ['platform'];
		$dspEventParam ['ad_type'] = $this->mPostData ['adType'];
		$dspEventParam ['dsp_id'] = $dspId;
		$dspEventParam ['event_type'] = $this->getDspEventType($eventFlag);
		$dspEventParam ['server_time'] = time();
		$redis = Common::getQueue('adx');
		$write = $redis->push('RQ:adx_dsp_event', $dspEventParam);
		return $write;
	}

	/**
	 * 获取dsp事件类型
	 *
	 * @param type $eventFlag
	 *            51: "dsp_request", # dsp请求
	 *            52: "dsp_response", # dsp响应
	 *            53: "dsp_win", # dsp竞价成功
	 *            54: "dsp_notice" # "dsp通知"
	 * @return int
	 */
	public function getDspEventType($eventFlag)
	{
		$eventConfig = array(
			'request' => 51,
			'response' => 52,
			'win' => 53,
			'notice' => 54
		);
		if (!isset ($eventConfig [$eventFlag])) {
			return -1;
		} else {
			return $eventConfig [$eventFlag];
		}
	}

	public function saveNoticeQueue($dspResponse)
	{
		if (!$dspResponse ['data'] ['bidInfo'] ['nurl']) {
			$this->sendDspEvent($dspResponse ['data'] ['dspId'], 'notice', $dspResponse ['data'] ['outBidId']);
			return false;
		}
		$data ['outBidId'] = $dspResponse ['data'] ['outBidId'];
		$data ['nurl'] = $dspResponse ['data'] ['bidInfo'] ['nurl'];
		$redis = Common::getQueue('adx');
		$write = $redis->push('RQ:adx_dsp_notice', $data);
	}

	/**
	 * 获取所有的dsp响应
	 *
	 * @param type $inputArr
	 * @return type
	 */
	public function getAllDspResponses()
	{
		$dspResponses = array();
		$dspList = $this->getAllDspListFromDb();
		if (empty ($dspList)) {
			$dspResponses['ret'] = Util_ErrorCode::DSP_FLOW_CONFIG_EMPTY;
			$dspResponses['msg'] = 'flow config list is not config DSP';
			$dspResponses['data'] = array();
			return $dspResponses;
		}
		$dspUrlList = MobgiApi_Service_AdsListModel::getDspInterFaceUrl($dspList);
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['dspUrlList'] = $dspUrlList;
		}
		if (empty ($dspUrlList)) {
			$dspResponses['ret'] = Util_ErrorCode::DSP_FLOW_CONFIG_EMPTY;
			$dspResponses['msg'] = 'dsp interFaceUrl is not config';
			$dspResponses['data'] = array();
			return $dspResponses;
		}


		// 广告商参数设置信息
		$adsAppRelList = $this->getAdsAppRel();
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['adsAppRelList'] = array_keys($adsAppRelList);
		}
		if (empty ($adsAppRelList)) {
			$dspResponses['ret'] = Util_ErrorCode::DSP_FLOW_CONFIG_EMPTY;
			$dspResponses['msg'] = 'baseInfo ads params is not config';
			$dspResponses['data'] = array();
			return $dspResponses;
		}
		// 检查第三方appkey是否设置
		foreach ($dspUrlList as $dspId => $url) {
			if (!array_key_exists($dspId, $adsAppRelList)) {
				unset ($dspUrlList [$dspId]);
			}
		}
		if (empty ($dspUrlList)) {
			$dspResponses['ret'] = Util_ErrorCode::DSP_FLOW_CONFIG_EMPTY;
			$dspResponses['msg'] = 'baseInfo ads params is not config';
			$dspResponses['data'] = array();
			return $dspResponses;
		}

		$dspResponses = $this->getDspResponse($dspUrlList,$adsAppRelList);
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['dspResponses'] = $dspResponses;
		}
		return $dspResponses;
	}

	/**
	 * 整理出广告位广告商的开关状态
	 *
	 * @param type $adsPosRelInfo
	 * @return type
	 */
	public function getAdsPosRelState($adsPosRelInfo)
	{
		$adsPosRelState = array();
		if ($adsPosRelInfo) {
			foreach ($adsPosRelInfo as $posKey => $val) {
				foreach ($val as $adsId => $item) {
					$adsPosRelState[$posKey][$adsId] = $item['state'];
				}
			}
		}
		return $adsPosRelState;
	}


	public function parseAdsPosRelInfo($adsPosRelInfo)
	{
		$returnData = array();
		if (empty ($adsPosRelInfo)) {
			return $returnData;
		}
		foreach ($adsPosRelInfo as $posKey => $blockInfo) {
			if (array_key_exists($posKey, $this->mAppPositonList)) {
				$returnData [$posKey] = array(
					'rate' => $this->mAppPositonList [$posKey] ['rate'],
					'show_limit' => $this->mAppPositonList [$posKey] ['show_limit'],
					'pos_name' => $this->mAppPositonList [$posKey] ['pos_name'],
					'other_block_id_list' => $blockInfo
				);
			}
		}
		if (empty ($returnData)) {
			return $returnData;
		}
		return $returnData;

	}

	// 安卓的定向配置
	public function getDirectConfig()
	{
		$label = '';
		if (stristr($this->mPostData ['extra'] ['adList'], 'BaiduChannel')) {
			$label = 'BaiduChannel';
		} elseif (stristr($this->mPostData ['extra'] ['adList'], 'JinliChannel')) {
			$label = 'JinliChannel';
		} elseif (stristr($this->mPostData ['extra'] ['adList'], 'MeizuChannel')) {
			$label = 'MeizuChannel';
		}
		if (!$label) {
			return false;
		}
		$params ['app_key'] = $this->mAppKey;
		$params ['name'] = $label;
		$info = MobgiApi_Service_PolymericAdsModel::getBy($params);
		$data = array();
		if (!empty ($info)) {
			$adsList [0] = array(
				'rate' => 1,
				'thirdPartyName' => $label,
				'extraInfos' => array()
			);
			$data = array();
			$positionConfig = json_decode($info ['position_conf'], true);
			if ($positionConfig ['status']) {
				foreach ($positionConfig ['status'] as $key => $val) {
					$adsList [0] ['thirdPartyBlockId'] = $positionConfig ['other_block_id'] [$key];
					if ($val && $positionConfig ['rate'] [$key] && $adsList [0] ['thirdPartyBlockId']) {
						$appBlockList [] = array(
							'blockIdName' => $positionConfig ['pos_name'] [$key],
							'blockId' => $positionConfig ['pos_key'] [$key],
							'rate' => $positionConfig ['rate'] [$key],
							'showLimit' => 0
						);
						$thirdBlockList [] = array(
							'blockIdName' => $positionConfig ['pos_name'] [$key],
							'blockId' => $positionConfig ['pos_key'] [$key],
							'configs' => $adsList
						);
					}
				}
			}
		}
		if (empty ($appBlockList)) {
			$this->output(Util_ErrorCode::DIRECT_CONFIT_EMPTY, 'get DirectConfig list fail');
		}
		$data ['configType'] = 1;
		$data ['configList'] ['supportNetworkType'] = 1;
		$data ['configList'] ['lifeCycle'] = 1800000;
		$data ['configList'] ['thirdPartyAppInfo'] [0] ['thirdPartyAppkey'] = $info ['third_party_appkey'];
		$data ['configList'] ['thirdPartyAppInfo'] [0] ['thirdPartyAppsecret'] = $info ['secret_key'];
		$data ['configList'] ['thirdPartyAppInfo'] [0] ['thirdPartyName'] = $label;
		$data ['configList'] ['appBlockList'] = $appBlockList;
		$data ['configList'] ['thirdBlockList'] = $thirdBlockList;
		$this->output(0, 'DirectConfig ok ', $data);
	}

	public function getAbTestFlowIdByConfId($confId)
	{
		if (!$confId) {
			return false;
		}
		$confRelResult = MobgiApi_Service_AbConfRelModel::getsBy(array('conf_id' => $confId));
		if (!$confRelResult) {
			return false;
		}
		$confRelResult = Common::resetKey($confRelResult, 'flow_id');
		$weightList = array();
		foreach ($confRelResult as $val) {
			$weightList[$val['flow_id']] = $val['weight'] * 100;
		}
		if (empty($weightList)) {
			return false;
		}
		$flowId = $this->rateWeight($weightList);
		$this->mAbtestConRelId = $confRelResult[$flowId]['id'];
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['mAbtestConRelId'] = $this->mAbtestConRelId;
		}
		return $flowId;
	}

	public function getAbTestFlowConfList($abTestConfId)
	{
		$abFlowId = $this->getAbTestFlowIdByConfId($abTestConfId);
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['abFlowId'] = $abFlowId;
		}
		if (!$abFlowId) {
			return false;
		}
		$this->mAbTestFlowId = $abFlowId;
		$flowAdTypeRel = MobgiApi_Service_AbFlowAdTypeRelModel::getBy(array(
			'flow_id' => $abFlowId,
			'ad_type' => $this->mAdType
		));
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['abTestStatus'] = $flowAdTypeRel['status'];
		}
		if (!$flowAdTypeRel['status']) {
			return false;
		}
		if ($flowAdTypeRel['is_default']) {
			$parms ['app_key'] = $this->mAppKey;
			$parms ['conf_type'] = MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE;
			$defaultFlowResult = MobgiApi_Service_FlowConfModel::getBy($parms);
			if ($this->isDebugMode()) {
				$this->mDebugInfo ['abTestRelDeaultFlowId'] = $defaultFlowResult ['id'];
			}
			if (empty ($defaultFlowResult)) {
				return false;
			}
			$defaultflowAdTypeRel = MobgiApi_Service_FlowAdTypeRelModel::getBy(array(
				'flow_id' => $defaultFlowResult ['id'],
				'ad_type' => $this->mAdType
			));
			$this->mFlowAdTypeRel = $defaultflowAdTypeRel;
			if (!$defaultflowAdTypeRel ['status']) {
				return false;
			}
			if ($this->isDebugMode()) {
				$this->mDebugInfo ['abTestRelDeaultStatus'] = $defaultflowAdTypeRel['status'];
			}
			$generalAdsConf = $this->getAdsConf($defaultFlowResult ['id'], MobgiApi_Service_FlowAdsRelModel::GERNERAL_ADS);
			if (empty ($generalAdsConf)) {
				return false;
			}
			$flowConf ['general_ads_conf'] = $generalAdsConf;
			$flowConf ['priority_ads_conf'] = array();
			if ($defaultflowAdTypeRel['is_priority']) {
				$flowConf ['priority_ads_conf'] = $this->getAdsConf($defaultFlowResult ['id'], MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS);
			}
			$flowConf ['dsp_ads_conf'] = array();
			if ($defaultflowAdTypeRel['is_use_dsp']) {
				$flowConf ['dsp_ads_conf'] = $this->getAdsConf($defaultFlowResult ['id'], MobgiApi_Service_FlowAdsRelModel::DSP_ADS);
				$flowConf ['price'] = $defaultflowAdTypeRel['price'];
			}
			$flowConf ['flow_id'] = $defaultFlowResult ['id'];
			$this->mFlowId = $defaultFlowResult ['id'];
		} else {
			$generalAdsConf = $this->getAbTestAdsConf($flowAdTypeRel ['flow_id'], MobgiApi_Service_FlowAdsRelModel::GERNERAL_ADS);
			if (empty ($generalAdsConf)) {
				return false;
			}
			$flowConf ['general_ads_conf'] = $generalAdsConf;
			$flowConf ['priority_ads_conf'] = array();
			if ($flowAdTypeRel['is_priority']) {
				$flowConf ['priority_ads_conf'] = $this->getAbTestAdsConf($flowAdTypeRel ['flow_id'], MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS);
			}
			$flowConf ['dsp_ads_conf'] = array();
			if ($flowAdTypeRel['is_use_dsp']) {
				$flowConf ['dsp_ads_conf'] = $this->getAbTestAdsConf($flowAdTypeRel ['flow_id'], MobgiApi_Service_FlowAdsRelModel::DSP_ADS);
				$flowConf ['price'] = $flowAdTypeRel['price'];
			}
			$flowConf ['flow_id'] = $flowAdTypeRel ['id'];
		}
		$this->mUserObject = MobgiApi_Service_AbConfModel::ABTEST_CONF_TYPE;
		return $flowConf;
	}

	/**
	 * 获取白名单详细配置
	 *
	 * @return type
	 */
	public function getWhitelistPolicyConfList()
	{
		if (empty($this->mWhitelistConfig)) {
			return false;
		}
		$flowId = $this->getAbTestFlowIdByConfId($this->mWhitelistConfig);
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['whilelistFlowId'] = $flowId;
		}
		if (!$flowId) {
			return false;
		}
		$flowAdTypeRel = MobgiApi_Service_AbFlowAdTypeRelModel::getBy(array('flow_id' => $flowId, 'ad_type' => $this->mAdType));
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['whilelistStatus'] = $flowAdTypeRel['status'];
		}
		if (!$flowAdTypeRel['status']) {
			return false;
		}
		if ($flowAdTypeRel['is_default']) {
			$parms ['app_key'] = $this->mAppKey;
			$parms ['conf_type'] = MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE;
			$defaultFlowResult = MobgiApi_Service_FlowConfModel::getBy($parms);
			if ($this->isDebugMode()) {
				$this->mDebugInfo ['whilelistRelDeaultFlowId'] = $defaultFlowResult ['id'];
			}
			if (empty ($defaultFlowResult)) {
				return false;
			}
			$defaultflowAdTypeRel = MobgiApi_Service_FlowAdTypeRelModel::getBy(array(
				'flow_id' => $defaultFlowResult ['id'],
				'ad_type' => $this->mAdType
			));
			$this->mFlowAdTypeRel = $defaultflowAdTypeRel;
			if (!$defaultflowAdTypeRel ['status']) {
				return false;
			}
			if ($this->isDebugMode()) {
				$this->mDebugInfo ['whilelistRelDeaultStatus'] = $defaultflowAdTypeRel['status'];
			}
			$generalAdsConf = $this->getAdsConf($defaultFlowResult ['id'], MobgiApi_Service_FlowAdsRelModel::GERNERAL_ADS);
			if (empty ($generalAdsConf)) {
				return false;
			}
			$flowConf ['general_ads_conf'] = $generalAdsConf;
			$flowConf ['priority_ads_conf'] = array();
			if ($defaultflowAdTypeRel['is_priority']) {
				$flowConf ['priority_ads_conf'] = $this->getAdsConf($defaultFlowResult ['id'], MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS);
			}
			$flowConf ['dsp_ads_conf'] = array();
			if ($defaultflowAdTypeRel['is_use_dsp']) {
				$flowConf ['dsp_ads_conf'] = $this->getAdsConf($defaultFlowResult ['id'], MobgiApi_Service_FlowAdsRelModel::DSP_ADS);
				$flowConf ['price'] = $defaultflowAdTypeRel['price'];
			}
			$flowConf ['flow_id'] = $defaultFlowResult ['id'];
			$this->mFlowId = $defaultFlowResult ['id'];
		} else {
			$generalAdsConf = $this->getAbTestAdsConf($flowAdTypeRel ['flow_id'], MobgiApi_Service_FlowAdsRelModel::GERNERAL_ADS);
			if (empty ($generalAdsConf)) {
				return false;
			}
			$flowConf ['general_ads_conf'] = $generalAdsConf;
			$flowConf ['priority_ads_conf'] = array();
			if ($flowAdTypeRel['is_priority']) {
				$flowConf ['priority_ads_conf'] = $this->getAbTestAdsConf($flowAdTypeRel ['flow_id'], MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS);
			}
			$flowConf ['dsp_ads_conf'] = array();
			if ($flowAdTypeRel['is_use_dsp']) {
				$flowConf ['dsp_ads_conf'] = $this->getAbTestAdsConf($flowAdTypeRel ['flow_id'], MobgiApi_Service_FlowAdsRelModel::DSP_ADS);
				$flowConf ['price'] = $flowAdTypeRel['price'];
			}
			$flowConf ['flow_id'] = $flowAdTypeRel ['id'];
		}
		$this->mUserObject = MobgiApi_Service_AbConfModel::WHILELIST_CONF_TYPE;
		return $flowConf;
	}


	public function parseAppBlockList()
	{
		if (empty ($this->mAppPositonList)) {
			return array();
		}
		$posList = array();
		$flowPosPolicy = $this->getFlowPosPolicy();
		foreach ($this->mAppPositonList as $posKey => $posInfo) {
			if (isset ($flowPosPolicy [$posKey] ['status']) && !$flowPosPolicy [$posKey] ['status']) {
				continue;
			}
			$posList [] = array(
				'blockIdName' => $posInfo ['pos_name'],
				'blockId' => $posKey,
				'rate' => isset ($flowPosPolicy [$posKey] ['rate']) ? strval(floatval($flowPosPolicy [$posKey] ['rate'])) : strval(floatval($posInfo ['rate'])),
				'showLimit' => isset ($flowPosPolicy [$posKey] ['show_limit']) ? strval($flowPosPolicy [$posKey] ['show_limit']) : strval($posInfo ['show_limit'])
			);
		}
		return $posList;
	}

	public function getFlowConfIdByUser()
	{
		// 获取流量配置列表
		$params ['app_key'] = $this->mAppKey;
		$flowConfList = MobgiApi_Service_FlowConfModel::getsBy($params);
		if (empty ($flowConfList)) {
			return 0;
		}
		$flowId = $this->getCustomeFlowConfId($flowConfList);
		if ($this->isDebugMode()) {
			$this->mDebugInfo ['customeFlowConfId'] = $flowId;
		}
		if ($flowId) {
			$flowAdTypeRel = $this->getFlowAdTypeRelByFlowId($flowId);
			if ($flowAdTypeRel && $flowAdTypeRel ['status'] && $flowAdTypeRel ['is_default']) {
				$flowId = 0;
			}
			if ($this->isDebugMode()) {
				$this->mDebugInfo ['customeFlowConfIdStaus'] = $flowAdTypeRel ['status'];
				$this->mDebugInfo ['customeFlowConfIdIsDefault'] = $flowAdTypeRel ['is_default'];
			}
		}
		if (!$flowId) {
			$flowId = $this->getDefaultFlowConfId($flowConfList);
			if ($this->isDebugMode()) {
				$this->mDebugInfo ['defaultFlowConfId'] = $flowId;
			}
		}
		if ($flowId) {
			return $flowId;
		}
		return 0;
	}

	public function getFlowConfListById()
	{
		$generalAdsConf = $this->getAdsConf($this->mFlowId, MobgiApi_Service_FlowAdsRelModel::GERNERAL_ADS);
		if (empty($generalAdsConf)) {
			return array();
		}
		$flowConf ['general_ads_conf'] = $generalAdsConf;
		$flowConf ['priority_ads_conf'] = array();
		if ($this->mFlowAdTypeRel['is_priority']) {
			$flowConf ['priority_ads_conf'] = $this->getAdsConf($this->mFlowId, MobgiApi_Service_FlowAdsRelModel::PRIORITY_ADS);
		}
		$flowConf ['flow_id'] = $this->mFlowId;
		return $flowConf;
	}

	/**
	 * 特殊的版本对联想的处理
	 * @param unknown $adsId
	 */
	public function getThirdPartAdsName($adsId)
	{
		if ($this->mAdType == Common_Service_Const::VIDEO_AD_SUB_TYPE && $adsId == 'LenovoAd') {
			$version = $this->mPostData['extra']['sdkVersion'];
			if (version_compare($version, '3.1.0', '>=') && version_compare($version, '3.3.1', '<=')) {
				return 'Lenovo';
			}
		}
		return $adsId;
	}

	public function getAbTestConf()
	{
		$params['app_key'] = $this->mAppKey;
		$params['conf_type'] = MobgiApi_Service_AbConfModel::ABTEST_CONF_TYPE;
		$params['status'] = MobgiApi_Service_AbConfModel::STATUS_OPEN;
		$params['start_time'] = array('<=', date('Y-m-d'));
		$params['end_time'] = array('>=', date('Y-m-d'));
		$abTestConf = MobgiApi_Service_AbConfModel::getsBy($params);
		if (empty($abTestConf)) {
			return false;
		}
		return Common::resetKey($abTestConf, 'conf_id');
	}

	public function getHitAbTestConf()
	{
		$adTestConf = $this->getAbTestConf();
		$hitAbConf = array();
		if (!$adTestConf) {
			return $hitAbConf;
		}
		foreach ($adTestConf as $val) {
			$content = json_decode($val ['content'], true);
			if (empty ($content)) {
				continue;
			}
			$flag = 0;
			foreach ($this->mAbConfRelFun as $type => $fun) {
				if ($content [$type] && call_user_func_array(array(
						$this,
						$fun
					), array(
						$content,
						false
					))) {
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

	public function rateAbTestConfId($adTestConf)
	{
		if (empty($adTestConf)) {
			return 0;
		}
		//随机取出一条配置
		$tmpConf = $adTestConf;
		for ($i = 1; $i <= 3; $i++) {
			if (empty($tmpConf)) {
				return 0;
			}
			$confIds = array_rand($tmpConf);
			unset($tmpConf[$confIds]);
			$weightList = array($confIds => $adTestConf[$confIds], 0 => 100 - $adTestConf[$confIds]);
			$rateConfId = $this->rateWeight($weightList);
			if ($rateConfId) {
				return $rateConfId;
			}
		}
	}


	/**
	 * 填充广告商的广告位
	 */
	public function parseWeightList($flowConf)
	{
		if (empty($flowConf)) {
			return false;
		}
		$gernernalConf = $flowConf['general_ads_conf'];
		if (empty($gernernalConf)) {
			return false;
		}
		return Common::resetKey($gernernalConf, 'current_ads_id');
	}

	public function delAbtestUserCache()
	{
		$cache = self::getAbTestCache();
		$userId = $this->mPostData ['device'] ['deviceId'];
		$hkey = 'abTest_' . $userId;
		if ($cache->exists($hkey)) {
			$cache->delete($hkey);
		}
	}

	public function saveUserFlowConfInfoToCache($flowConf, $adsWeightList, $abTestConfId)
	{
		$expire = strtotime(date('Y-m-d 23:59:59', strtotime($this->mAbTestConf[$abTestConfId]['end_time']))) - strtotime(date('Y-m-d H:i:s'));
		$hkey = $this->getUserFlowConfHkey();
		$cache = self::getAbTestCache();
		$data['flowConf'] = json_encode($flowConf);
		$data['adsWeightList'] = json_encode($adsWeightList);
		$data['mFlowAdTypeRel'] = json_encode($this->mFlowAdTypeRel);
		$data['mFlowId'] = $this->mFlowId;
		$data['abTestConfId'] = $abTestConfId;
		$data['mAbTestFlowId'] = $this->mAbTestFlowId;
		$data['mAbtestConRelId'] = $this->mAbtestConRelId;
		$cache->hMset($hkey, $data, $expire);
	}

	public function savaAbtestUserToCache($abTestConfId)
	{
		$expire = strtotime(date('Y-m-d 23:59:59', strtotime($this->mAbTestConf[$abTestConfId]['end_time']))) - strtotime(date('Y-m-d H:i:s')) + 7200;
		$cache = self::getAbTestCache();
		$userId = $this->mPostData ['device'] ['deviceId'];
		$data['config_id'] = $this->mAbtestConRelId;
		$data['user_type'] = MobgiApi_Service_AbConfModel::ABTEST_CONF_TYPE;
		$hkey = 'abTest_' . $userId;
		$cache->hMset($hkey, $data, $expire);
	}

	public function getUserFlowConfInfoCache()
	{
		$hkey = $this->getUserFlowConfHkey();
		$cache = self::getAbTestCache();
		return $cache->hGetAll($hkey);
	}

	public function getAbTestCache()
	{
		$resource = 'ab_info';
		$cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS, $resource);
		return $cache;
	}

	public function getUserFlowConfHkey()
	{
		$userId = $this->mPostData ['device'] ['deviceId'];
		$key = 'user::conf::' . $userId . '::' . $this->mAppKey . '::' . $this->mAdType;
		return $key;
	}

	public function fillServerInfoToData()
	{
		$data['bidId'] = strval($this->createRequestId());
		$data['userType'] = $this->mUserObject;
		$data['configId'] = strval(($this->mUserObject) ? $this->mAbtestConRelId : $this->mFlowId);
		return $data;
	}

	public function getWinBidPriceDspNo($bidPriceDspArr)
	{
		if (empty($bidPriceDspArr)) {
			return '';
		}
		if (count($bidPriceDspArr) == 1 || count(array_unique($bidPriceDspArr)) == 1) {
			$dspNoArr = array_keys($bidPriceDspArr);
			$key = array_rand($dspNoArr);
			return $dspNoArr[$key];
		}
		arsort($bidPriceDspArr);
		$dspNoArr = array_keys($bidPriceDspArr);
		$basePrice = 0;
		$maxDspNo = '';
		foreach ($bidPriceDspArr as $dspNo => $price) {
			if ($price > $basePrice) {
				$basePrice = $price;
				$maxDspNo = $dspNo;
			}
		}
		return $maxDspNo;
	}


}