<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class IaadController extends Coupon_BaseController
{

	public $mAppInfo = null;
	public $mPosInfo = null;
	public $mAdConfRel = null;
	public $mAdConf = null;
	public $mPlatform = 0;

	const RUQUEST_EVENT_TYPE = 1;
	const FORWORD_EVENT_TYPE = 2;

	const INNTER_URL_TYPE = 2;
	const OUTER_URL_TYPE = 1;

	public $actions = array(
		'cdnUrl' => 'http://dl2.gxpan.cn/landingpage/iaad',
		'getUserUrl' => '/iaad/getUser',
		'prizeUrl' => '/iaad/prize',
		'activityUrl' => '/iaad/activity',
		'drawUrl' => '/iaad/draw',
		'goodsUrl' => '/iaad/goods',
		'verifyGoodsUrl' => '/iaad/verifyGoods',
		'postVerifyGoodsUrl' => '/iaad/postVerifyGoods',
		'qrCodeUrl' => '/iaad/qrCode',
		'uuidUrl' => '/iaad/getUuid'
	);
	//核销的密码
	const PASSWORD = 'idreamsky2018';

	public function init()
	{
		parent::init();
		if (Util_Environment::isOnline()) {
			$staticroot = $this->actions['cdnPath'];
		} else {
			$staticroot = Yaf_Application::app()->getConfig()->staticroot . '/static/iaad';
		}
		$this->assign("staticPath", $staticroot);

	}

	public function getUuidAction()
	{
		Yaf_loader::import("Util/IdWorker.php");
		$idWorker = new IdWorker(1, 1);
		$data['user_id'] = $idWorker->generateID();
		$this->output(0, 'ok', $data);
	}

	public function activityAction()
	{
		$info = $this->getInput(array('activity_id'));
		$activityInfo = $this->getActivityInfoById($info['activity_id'], false);
		if ($activityInfo) {
			$goodsList = MobgiApi_Service_InteractiveAdActivityRelModel::getsBy(['activity_id' => $info['activity_id']], array('id' => 'ASC'));
			$goodsData = array();
			if ($goodsList && (count($goodsList) == MobgiApi_Service_InteractiveAdActivityModel::CONFIG_GOODS_COUNT)) {
				$goodsIds = array_keys(common::resetKey($goodsList, 'goods_id'));
				$goodsInfo = MobgiApi_Service_InteractiveAdGoodsModel::getsBy(['id' => ['IN', $goodsIds], 'status' => MobgiApi_Service_InteractiveAdGoodsModel::OPEN_STATUS]);
				if ($goodsInfo && count($goodsIds) == count($goodsInfo)) {
					$goodsInfo = common::resetKey($goodsInfo, 'id');
					foreach ($goodsList as $val) {
						$goodsData[] = array('id' => $val['id'],
							'icon' => Common::getAttachPath() . $goodsInfo[$val['goods_id']]['icon'],
							'title' => $goodsInfo[$val['goods_id']]['title'],
							'big_img' => Common::getAttachPath() . $goodsInfo[$val['goods_id']]['big_img'],
							'desc' => html_entity_decode($goodsInfo[$val['goods_id']]['desc'])
						);
					}
				}
			}
			$reportData['activity_id'] = $info['activity_id'];
			$reportData['goods_id'] = 0;
			$this->saveActivityEventTypeToCache(Util_EventType::ACTIVITY_IMPRESSION_EVENT_TYPE, $reportData);
		}
		$this->assign("activityInfo", $activityInfo);
		$this->assign("goodsData", json_encode($goodsData));
		$user_id = Util_Cookie::get('user_id');
		$this->assign("user_id", ($user_id && $user_id！ = 'undefined') ? $user_id : '');
		$this->assign("activity_id", $info['activity_id']);

	}


	public function saveActivityEventTypeToCache($eventType, $data)
	{
		if (!is_array($data)) {
			return false;
		}
		$data['uuid'] = Util_Cookie::get('user_id');
		$data['ip'] = common::getClientIP();
		$data['server_time'] = time();
		$data['ua'] = str_ireplace(array('\t', '\n'), array('', ''), $this->getServer('HTTP_USER_AGENT'));
		//1展示，2抽奖 3中奖 4兑奖
		$data['event_type'] = $eventType;
		$redis = Common::getQueue('interative_ad_list');
		$redis->push(Util_CacheKey::INAD_ACTIVITY_QUEUE_KEY, $data);

	}


	public function getUserAction()
	{
		$info = $this->getInput(array('user_id', 'activity_id'));
		if (!$info['user_id'] || !$info['activity_id']) {
			$this->output(-1, '参数缺少');
		}
		if (!Util_Cookie::get('user_id')) {
			if ($info['user_id'] || ($info['user_id'] == 'undefined')) {
				//保存用户id的cookie
				Util_Cookie::set('user_id', $info['user_id'], false, time() + 3600 * 24 * 365);
				//保存用户
				if (!MobgiApi_Service_InteractiveAdUserModel::getBy(['user_id' => $info['user_id']])) {
					$data['user_id'] = $info['user_id'];
					MobgiApi_Service_InteractiveAdUserModel::add($data);
				}
			}
		}
		if (Util_Cookie::get('user_id') == 'undefined') {
			Util_Cookie::delete('user_id');
		}
		//获取用户的抽奖次数
		$activeInfo = $this->getActivityInfoById($info['activity_id'], true);
		$userTimes = $this->getUserDayDrawTimes($info, $activeInfo);
		$this->output(0, 'ok', ['times' => $userTimes]);
	}


	public function drawAction()
	{
		$info = $this->getInput(array('user_id', 'activity_id'));
		if (!$info['user_id'] || !$info['activity_id']) {
			$this->output(-1, '参数缺少');
		}
		if (Util_Cookie::get('user_id') != $info['user_id']) {
			$this->output(-1, '用户非法' . $info['user_id'] . ':' . Util_Cookie::get('user_id'));
		}
		$activeInfo = $this->getActivityInfoById($info['activity_id'], true);
		$userTimes = $this->getUserDayDrawTimes($info, $activeInfo);
		if (!$userTimes) {
			$this->output(-1, '抽奖次数已用完');
		}
		$goodsList = MobgiApi_Service_InteractiveAdActivityRelModel::getsBy(['activity_id' => $info['activity_id']], array('position' => 'ASC'));
		if (!$goodsList || count($goodsList) != MobgiApi_Service_InteractiveAdActivityModel::CONFIG_GOODS_COUNT) {
			$this->output(-1, '活动不存在');
		}
		$reportData['activity_id'] = $info['activity_id'];
		$reportData['goods_id'] = 0;
		$this->saveActivityEventTypeToCache(Util_EventType::ACTIVITY_DRAW_EVENT_TYPE, $reportData);


		$rateList = array();
		foreach ($goodsList as $val) {
			$rateList[$val['id']] = $val['rate'] * 100;
		}
		$id = $this->getRandId($rateList);
		if (!$id) {
			$this->output(-1, '抽奖异常');
		}
		$goodsList = common::resetKey($goodsList, 'id');
		$goodsInfo = MobgiApi_Service_InteractiveAdGoodsModel::getBy(['id' => $goodsList[$id]['goods_id'], 'status' => MobgiApi_Service_InteractiveAdGoodsModel::OPEN_STATUS]);
		if (!$goodsInfo) {
			$this->output(-1, '商品已下架');
		}
		if ($goodsInfo['type'] != MobgiApi_Service_InteractiveAdGoodsModel::DEAFAULT_GOODS_TYPE) {
			if ($goodsInfo['stock'] <= $goodsInfo['used_num']) {
				$this->output(-1, '商品库存不足');
			}
			$codeInfo = MobgiApi_Service_InteractiveAdGoodsCodeModel::getBy(['goods_id' => $goodsInfo['id'], 'status' => MobgiApi_Service_InteractiveAdGoodsCodeModel::CODE_DEFAULT_STATUS]);
			if (!$codeInfo) {
				$this->output(-1, '兑换码不足');
			}
			if (MobgiApi_Service_InteractiveAdGoodsExchangeLogModel::getBy(['code_id' => $codeInfo['id']])) {
				$this->output(-1, '兑换码已领取');
			}
			Common_Service_Base::beginTransaction('mobgiApi');
			$goodRestult = MobgiApi_Service_InteractiveAdGoodsModel::updateByID(['used_num' => $goodsInfo['used_num'] + 1], $goodsInfo['id']);
			$codeResult = MobgiApi_Service_InteractiveAdGoodsCodeModel::updateBy(['status' => MobgiApi_Service_InteractiveAdGoodsCodeModel::CODE_DRAW_STATUS], ['code' => $codeInfo['code']]);
			$exchangeData['user_id'] = $info['user_id'];
			$exchangeData['activity_id'] = $info['activity_id'];
			$exchangeData['goods_id'] = $goodsInfo['id'];
			$exchangeData['code_id'] = $codeInfo['id'];
			$exchangeData['code'] = $codeInfo['code'];
			$exchangeData['status'] = MobgiApi_Service_InteractiveAdGoodsCodeModel::CODE_DRAW_STATUS;
			$logResult = MobgiApi_Service_InteractiveAdGoodsExchangeLogModel::add($exchangeData);
			if (!$goodRestult || !$codeResult || !$logResult) {
				Common_Service_Base::rollBack();
				$this->output(-1, '数据异常');
			}
			Common_Service_Base::commit();
		}
		//修改每天抽奖次数
		unset($params);
		$params['user_id'] = $info['user_id'];
		$params['activity_id'] = $info['activity_id'];
		$params['day'] = date('Y-m-d');
		$userDrawTimes = MobgiApi_Service_InteractiveAdUserDayDrawTimesModel::getBy($params);
		if ($userDrawTimes) {
			$dayLog = MobgiApi_Service_InteractiveAdUserDayDrawTimesModel::updateBy(['times' => $userDrawTimes['times'] + 1], $params);
		} else {
			$dayData['user_id'] = $info['user_id'];
			$dayData['activity_id'] = $info['activity_id'];
			$dayData['day'] = date('Y-m-d');
			$dayData['times'] = 1;
			$dayLog = MobgiApi_Service_InteractiveAdUserDayDrawTimesModel::add($dayData);
		}
		if (!$dayLog) {
			$this->output(-1, '数据异常');
		}
		$outputData = array(
			'isPrize' => ($goodsInfo['type'] == MobgiApi_Service_InteractiveAdGoodsModel::DEAFAULT_GOODS_TYPE) ? 0 : 1,
			'times' => $userTimes - 1,
			'id' => $id,
			'icon' => Common::getAttachPath() . $goodsInfo['icon'],
			'title' => $goodsInfo['title'],
			'big_img' => Common::getAttachPath() . $goodsInfo['big_img'],
			'desc' => html_entity_decode($goodsInfo['desc']),
			'goodsUrl' => Common::getWebRoot() . $this->actions['goodsUrl'] . '?user_id=' . $info['user_id'] . '&id=' . $logResult . '&activity_id=' . $info['activity_id'],
		);
		$reportData['activity_id'] = $info['activity_id'];
		$reportData['goods_id'] = $goodsInfo['id'];
		$this->saveActivityEventTypeToCache(Util_EventType::ACTIVITY_PRIZE_EVENT_TYPE, $reportData);
		$this->output(0, 'ok', $outputData);
	}


	public function getActivityInfoById($id, $isOutput = true)
	{
		//获取用户的抽奖次数
		$activeInfo = MobgiApi_Service_InteractiveAdActivityModel::getByID($id);
		if (!$activeInfo) {
			if ($isOutput) {
				$this->output(-1, '活动未开始');
			} else {
				return false;
			}
		}
		$currentTime = strtotime(date('Y-m-d'));
		if ((strtotime($activeInfo['start_time']) > $currentTime) || ($currentTime > strtotime($activeInfo['end_time']))) {
			if ($isOutput) {
				$this->output(-1, '活动未开始');
			} else {
				return false;
			}
			$this->output(-1, '活动未开始');
		}
		if ($activeInfo['status'] == MobgiApi_Service_InteractiveAdActivityModel::CLOSE_STATUS) {
			if ($isOutput) {
				$this->output(-1, '活动已关闭');
			} else {
				return false;
			}
		}
		return $activeInfo;
	}


	public function getUserDayDrawTimes($info, $activeInfo)
	{
		if (!$activeInfo) {
			return 0;
		}
		$limitType = $activeInfo['limit_type'];
		$limitTimes = $activeInfo['limit_num'];
		$params['user_id'] = $info['user_id'];
		$params['activity_id'] = $info['activity_id'];
		if ($limitType == MobgiApi_Service_InteractiveAdActivityModel::DEFAULT_LIMIT_TYPE) {
			$params['day'][0] = array('>=', $activeInfo['start_time']);
			$params['day'][1] = array('<=', $activeInfo['end_time']);
		} else {
			$params['day'] = date('Y-m-d');
		}
		$userInfo = MobgiApi_Service_InteractiveAdUserDayDrawTimesModel::getsBy($params);
		$finishTimes = 0;
		if ($userInfo) {
			foreach ($userInfo as $val) {
				$finishTimes += $val['times'];
			}
		}
		$userTimes = $limitTimes - $finishTimes;
		if ($finishTimes > $limitTimes) {
			$userTimes = 0;
		}
		return $userTimes;
	}


	public function goodsAction()
	{
		$info = $this->getInput(array('user_id', 'id', 'activity_id'));
		if (!$info['user_id'] || !$info['id']) {
			$this->output(-1, '参数缺少');
		}

		$result = MobgiApi_Service_InteractiveAdGoodsExchangeLogModel::getByID($info['id']);
		if ($result['activity_id'] != $info['activity_id']) {
			$this->output(-1, '非法参数');
		}
		if (Util_Cookie::get('user_id') != $info['user_id']) {
			$this->output(-1, '非法用户');
		}
		if ($result) {
			$goodsInfo = MobgiApi_Service_InteractiveAdGoodsModel::getByID($result['goods_id']);
			$reportData['activity_id'] = $info['activity_id'];
			$reportData['goods_id'] = $goodsInfo['id'];
			$this->saveActivityEventTypeToCache(Util_EventType::ACTIVITY_IMPRESSION_EVENT_TYPE, $reportData);
		}
		$content = common::getWebRoot() . $this->actions['verifyGoodsUrl'] . '?code=' . $result['code'] . '&user_id=' . $info['user_id'] . '&activity_id=' . $result['activity_id'];
		//$qrcode = $this->createQrCode($content);
		$this->assign("goodsInfo", $goodsInfo);
		$this->assign("code", $result['code']);
		//$this->assign("qrcode",$qrcode);
		$this->assign("content", $content);
	}

	public function getQrCodeAction()
	{
		$url = $this->getInput('url');
		if (!$url) {
			return false;
		}
		$this->createQrCode(urldecode($url));
	}

	public function createQrCode($value)
	{
		if (Util_Environment::isDevelop()) {
			return '';
		}
		$errorCorrectionLevel = 'L';    //容错级别
		$matrixPointSize = 5;           //生成图片大小
		return common::generateQRfromLocal($value, $errorCorrectionLevel, $matrixPointSize);
		/*Yaf_loader::import("Util/PHPQRcode/QRcode.php");
		//二维码内容
		$errorCorrectionLevel = 'L';    //容错级别
		$matrixPointSize = 5;           //生成图片大小
		ob_start();
		//生成二维码图片
		 QRcode::png($value,true , $errorCorrectionLevel, $matrixPointSize, 2,false);
		$imageString = base64_encode(ob_get_contents());
		ob_end_clean();
		return $imageString;*/
	}


	public function verifyGoodsAction()
	{
		$info = $this->getInput(array('user_id', 'code', 'activity_id'));
		if (!$info['user_id'] || !$info['code']) {
			$this->output(-1, '参数缺少');
		}
		$codeInfo = MobgiApi_Service_InteractiveAdGoodsCodeModel::getBy(['code' => $info['code']]);
		if ($codeInfo) {
			$goodsInfo = MobgiApi_Service_InteractiveAdGoodsModel::getByID($codeInfo['goods_id']);
		}
		$this->assign("goodsInfo", $goodsInfo);
		$this->assign("codeInfo", $codeInfo);
		$this->assign("userId", $info['user_id']);
		$this->assign("activityId", $info['activity_id']);
	}

	public function postVerifyGoodsAction()
	{
		$info = $this->getInput(array('user_id', 'code', 'password', 'activity_id'));
		if (!$info['user_id'] || !$info['code']) {
			$this->output(-1, '参数缺少');
		}
		if (!trim($info['password'])) {
			$this->output(-1, '密码为空');
		}
		$passwordKey = 'iaad';
		$cache = Cache_Factory::getCache();
		$password = $cache->get($passwordKey);
		if (!$password) {
			$cache->set($passwordKey, self::PASSWORD);
		}
		if ($password != trim($info['password'])) {
			$this->output(-1, '密码不正确');
		}
		$codeInfo = MobgiApi_Service_InteractiveAdGoodsCodeModel::getBy(['code' => $info['code']]);
		if (!$codeInfo) {
			$this->output(-1, '核销码不存在');
		}
		if ($codeInfo['status'] == MobgiApi_Service_InteractiveAdGoodsCodeModel::CODE_USEED_STATUS) {
			$this->output(-1, '此商品已核销过');
		}
		$params['user_id'] = $info['user_id'];
		$params['code_id'] = $codeInfo['id'];
		$logReult = MobgiApi_Service_InteractiveAdGoodsExchangeLogModel::getBy($params);
		if (!$logReult) {
			$this->output(-1, '核销码不存在');
		}
		if ($codeInfo) {
			$goodsInfo = MobgiApi_Service_InteractiveAdGoodsModel::getByID($codeInfo['goods_id']);
		}
		$reportData['activity_id'] = $info['activity_id'];
		$reportData['goods_id'] = $goodsInfo['id'];
		$this->saveActivityEventTypeToCache(Util_EventType::ACITVITY_EXCHANGE_EVENT_TYPE, $reportData);
		MobgiApi_Service_InteractiveAdGoodsExchangeLogModel::updateBy(['status' => MobgiApi_Service_InteractiveAdGoodsCodeModel::CODE_USEED_STATUS], $params);
		MobgiApi_Service_InteractiveAdGoodsCodeModel::updateByID(['status' => MobgiApi_Service_InteractiveAdGoodsCodeModel::CODE_USEED_STATUS], $codeInfo['id']);
		$this->output(0, '核销成功');
	}

	/**
	 * 获取我的奖品
	 */
	public function prizeAction()
	{
		$info = $this->getInput(array('activity_id'));
		if (!$info['activity_id']) {
			$this->output(-1, '参数缺少');
		}
		$user_id = Util_Cookie::get('user_id');
		$userLog = array();
		if ($user_id) {
			$userLog = MobgiApi_Service_InteractiveAdGoodsExchangeLogModel::getsBy(['activity_id' => $info['activity_id'], 'user_id' => $user_id, 'status' => MobgiApi_Service_InteractiveAdGoodsCodeModel::CODE_DRAW_STATUS], ['create_time' => 'DESC']);
			if ($userLog) {
				$goodsIds = array_keys(common::resetKey($userLog, 'goods_id'));
				if ($goodsIds) {
					$goodsList = MobgiApi_Service_InteractiveAdGoodsModel::getsBy(['id' => ['IN', $goodsIds]]);
					$goodsList = common::resetKey($goodsList, 'id');
				}
			}
		}
		$this->assign("userLog", $userLog);
		$this->assign("goodsList", $goodsList);
		$info = $this->getInput(array('activity_id'));
		$this->assign("activity_id", $info['activity_id']);
		$this->assign("user_id", $user_id);
	}


	public function configAction()
	{
		Yaf_Dispatcher::getInstance()->disableView();
		$info = $this->getInput(array('app_key', 'pos_key'));
		$data = $this->checkParam($info);


		$hitAdConf = $this->getHitAdConf();
		$adConfId = $this->getRandId($hitAdConf);
		$this->saveDataToCache(self::RUQUEST_EVENT_TYPE, $data);
		$data['ads_id'] = $this->mAdConf[$adConfId]['ads_id'];
		$data['url_id'] = $adConfId;
		$data['url_type'] = self::OUTER_URL_TYPE;
		$this->saveDataToCache(self::FORWORD_EVENT_TYPE, $data);
		$this->redirect($this->mAdConf[$adConfId]['url']);


	}

	public function saveDataToCache($eventType, $data)
	{

		$data['event_type'] = $eventType;
		IF ($eventType == self::RUQUEST_EVENT_TYPE) {
			$data['url_id'] = '';
			$data['url_type'] = '';
			$data['ads_id'] = '';
		}
		$redis = Common::getQueue('interative_ad_list');
		$redis->push('RQ:interative_list', $data);

	}

	public function getRandId($proArr)
	{
		$result = '';
		//概率数组的总概率精度
		$proSum = array_sum($proArr);
		//概率数组循环
		foreach ($proArr as $key => $proCur) {
			$randNum = mt_rand(1, $proSum);             //抽取随机数
			if ($randNum <= $proCur) {
				$result = $key;                         //得出结果
				break;
			} else {
				$proSum -= $proCur;
			}
		}
		unset ($proArr);
		return $result;
	}

	function parseOS($ua)
	{

		if (strpos($ua, 'Android') !== false) {
			preg_match("/(?<=Android )[\d\.]{1,}/", $ua, $result);
			if ($result) {
				$this->mPlatform = Common_Service_Const::ANDRIOD_PLATFORM;
			}

		} elseif (strpos($ua, 'iPhone') !== false) {
			preg_match("/(?<=CPU iPhone OS )[\d\_]{1,}/", $ua, $result);
			if ($result) {
				$this->mPlatform = Common_Service_Const::IOS_PLATFORM;
			}
		}
	}

	/**
	 * @param $data
	 */
	public function checkParam($data)
	{
		if (!$data['app_key']) {
			$this->output(-1, 'app_key is null');
		}
		if (!$data['pos_key']) {
			$this->output(-1, 'pos_key is null');
		}
		$data['ua'] = str_ireplace(array('\t', '\n'), array('', ''), $this->getServer('HTTP_USER_AGENT'));
		$this->mAppInfo = MobgiApi_Service_AdAppModel::getBy(array('app_key' => $data['app_key']));
		if (!$this->mAppInfo) {
			$this->output(-1, 'app_key is not exist');
		}
		$this->mPosInfo = MobgiApi_Service_AdDeverPosModel::getBy(array('dever_pos_key' => $data['pos_key']));
		if (!$this->mPosInfo) {
			$this->output(-1, 'app_key is not exist');
		}
		$this->mAdConfRel = MobgiApi_Service_InteractiveAdConfRelModel::getBy(array('app_key' => $data['app_key'], 'pos_key' => $data['pos_key']));
		if (!$this->mAdConfRel) {
			$this->output(-1, 'no active');
		}
		$this->mAdConf = MobgiApi_Service_InteractiveAdConfModel::getsBy(array('conf_rel_id' => $this->mAdConfRel['id']));
		if (!$this->mAdConf) {
			$this->output(-1, 'no active');
		}
		$this->mAdConf = common::resetKey($this->mAdConf, 'id');
		$this->parseOS($data['ua']);
		$data['server_time'] = common::getTime();
		$data['ip'] = common::getClientIP();
		if (!$this->mAppInfo['state'] || !$this->mPosInfo['state']) {
			$templateId = 0;
			if ($this->mPlatform) {
				foreach ($this->mAdConf as $val) {
					if ($val['conf_type'] == $this->mPlatform && $val['status']) {
						$templateId = $val['template_id'];
						break;
					}
				}
			}
			if (!$templateId) {
				foreach ($this->mAdConf as $val) {
					if ($val['conf_type'] == MobgiApi_Service_InteractiveAdConfModel::DEAFAULT_CONF_TYPE && $val['status']) {
						$templateId = $val['template_id'];
						break;
					}
				}
			}
			if ($templateId) {
				$url = $this->getTemplateUrlById($templateId);
				$data['ads_id'] = 'mobgi';
				$data['url_id'] = $templateId;
				$data['url_type'] = self::INNTER_URL_TYPE;
				$this->saveDataToCache(self::FORWORD_EVENT_TYPE, $data);
				$this->redirect($url);
				exit;
			}
			$this->output(-1, 'no config');
		}
		return $data;
	}

	public function getTemplateUrlById($id)
	{
		$templateInfo = MobgiApi_Service_InteractiveAdTemplateModel::getBy(array('id' => $id));
		if ($templateInfo) {
			return $templateInfo['url'];
		}
		return 0;
	}

	/**
	 * @return array
	 */
	public function getHitAdConf(): array
	{
		$flag = 0;
		$hitAdConf = array();
		if ($this->mPlatform) {
			foreach ($this->mAdConf as $val) {
				if ($val['conf_type'] == $this->mPlatform && $val['status']) {
					$flag = 1;
					$hitAdConf[$val['id']] = $val['weight'] * 100;
				}
			}
		}
		if (!$flag) {
			foreach ($this->mAdConf as $val) {
				if ($val['conf_type'] == MobgiApi_Service_InteractiveAdConfModel::DEAFAULT_CONF_TYPE && $val['status']) {
					$hitAdConf[$val['id']] = $val['weight'] * 100;
				}
			}
		}
		return $hitAdConf;
	}


}