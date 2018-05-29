<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Common_BaseController
 * @author rock.luo
 *
 */
abstract class Adx_BaseController extends Yaf_Controller_Abstract
{

	const PERPAGE = 10;
	protected $sTime = 0;
	protected $mDebugInfo = null;
//    protected $mDspLog = null;
	protected $OuterInnerRate = 100;    //外部投放单元与内部投放单元与单位换算,单位不同.内部单位:元,外部单位:分. 1元:100分
	protected $maxPageSize = 100;
	protected $perpage = 10;
	protected $isReportToMonitor = 0;
	protected $mReportCode = 0;
	protected $mReportMsg = '';
	protected $mReportData = null;
	protected $mAppKey = NULL;
	protected $mProviderId = 1;
	protected $mIsTest = 0;
	protected $mWhitelistConfig = 0;
	protected $mFlowId = 0;
	protected $mDevMode = null;

	/**
	 *
	 * Enter description here ...
	 */
	public function init()
	{
		$this->sTime = microtime(true);
		Yaf_Dispatcher::getInstance()->throwException(true);
		Yaf_Dispatcher::getInstance()->setErrorHandler(array($this, "myErrorHandler"));
		Yaf_Dispatcher::getInstance()->disableView();
	}

	public function myErrorHandler($errNo, $errStr, $errFile, $errLine)
	{
		switch ($errNo) {
			case YAF_ERR_NOTFOUND_CONTROLLER:
			case YAF_ERR_NOTFOUND_MODULE:
			case YAF_ERR_NOTFOUND_ACTION:
				header(" Not Found");
				break;
			//忽略没有没有下标
			case 8:
			case 2:
			case 4096:
				break;
			default:
				$errCode = $errNo;
				$errStr = str_replace(APP_PATH, '[PATH]', $errStr);
				$errFileName = str_replace(APP_PATH, '[PATH]', $errFile);
				$errFileLine = $errLine;
				Common::sendLogError('ads', 0, '', 5, $errCode, $errStr, $errFileName, $errFileLine);
				break;
		}
		return true;
	}


	/**
	 *
	 * 获取post参数
	 * @param string /array $var
	 */
	public function getPost($var)
	{
		if (is_string($var)) return Util_Filter::post($var);
		$return = array();
		if (is_array($var)) {
			foreach ($var as $key => $value) {
				if (is_array($value)) {
					$return[$value[0]] = Util_Filter::post($value[0], $value[1]);
				} else {
					$return[$value] = Util_Filter::post($value);;
				}
			}
			return $return;
		}
		return null;
	}

	/**
	 *
	 * 获取post参数
	 * @param string /array $var
	 */
	public function getGet($var)
	{
		if (is_string($var)) return Util_Filter::get($var);
		$return = array();
		if (is_array($var)) {
			foreach ($var as $key => $value) {
				if (is_array($value)) {
					$return[$value] = Util_Filter::get($value[0], $value[1]);
				} else {
					$return[$value] = Util_Filter::get($value);;
				}
			}
			return $return;
		}
		return null;
	}

	/*
	 * 是否post
	 */
	public function isPost()
	{
		return $this->getRequest()->isPost();
	}

	/*
	 * 是否get
	 */
	public function isGet()
	{
		return $this->getRequest()->isGet();
	}


	public function getServer($var = null)
	{
		if (is_null($var)) return $this->getRequest()->getServer();
		if (is_string($var)) return $this->getRequest()->getServer($var);
		if (is_array($var)) {
			$return = array();
			foreach ($var as $key => $value) {
				$return[$value] = $this->getRequest()->getServer($value);
			}
			return $return;
		}
	}

	/**
	 *
	 * 获取get参数
	 * @param string $var
	 */
	public function getInput($var)
	{
		if (is_string($var)) return $this->getVal($var);
		if (is_array($var)) {
			$return = array();
			foreach ($var as $key => $value) {
				$return[$value] = $this->getVal($value);
			}
			return $return;
		}
		return null;
	}

	/**
	 *
	 * @param unknown_type $var
	 * @return unknown|NULL
	 */
	private function getVal($var)
	{
		if ($this->isPost()) {
			$value = Util_Filter::post($var);
			if (!is_null($value)) return $value;
		}
		if ($this->isGet()) {
			$value = Util_Filter::get($var);
			if (!is_null($value)) return $value;
		}
		return null;
	}


	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $code
	 * @param unknown_type $msg
	 * @param unknown_type $data
	 */
	public function output($code, $msg = '', $data = array())
	{
		$this->mReportCode = $code;
		$this->mReportMsg = $msg;
		$this->mReportData = $this->mFlowId ? $this->mFlowId : '';
		header("Content-type:text/json");
		$return = array(
			'ret' => $code,
			'msg' => $msg,
			'data' => $data
		);
		//新增adx白名单devMode的下发
		if ($this->isDebugMode()) {
			$return['debugInfo'] = $this->mDebugInfo;
			$return['devMode'] = '';
			if ($this->mDevMode) {
				$return['devMode'] = 'YES';
			}
		}
		exit (json_encode($return));
	}


	/**
	 * 检测token
	 * @param type $advertiser_id
	 * @return boolean
	 */
	public function checkAdxToken()
	{

		$bearerTokenStr = $_SERVER['HTTP_AUTHORIZATION'];

		$token = str_replace('Bearer ', '', $bearerTokenStr);
		if (empty($token)) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'token does not exist ');
		}

		$string = base64_decode($token);
		list($providerId, $time_stamp, $sign) = explode(',', $string);
		$tokenExpireTime = Common::getConfig("adxConfig", "token_expire_time");
		if ((!in_array(intval($providerId), array(1, 2))) && (time() - $time_stamp > $tokenExpireTime)) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'token is expired');
		}

		//校验token
		$checkSign = sha1($providerId . $time_stamp);
		if (strtolower($checkSign) != strtolower($sign)) {
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'token is wrong');
		}
		return true;
	}

	public function checkSspToken($data){
		$sign = Util_Ssp::getSign($data);
		if($data['sign']!=$sign){
			$this->output(Util_ErrorCode::PARAMS_CHECK, 'sign is wrong');
		}

	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $code
	 * @param unknown_type $msg
	 * @param unknown_type $data
	 */
	public function adxOutput($code, $msg = '', $data = array())
	{
		header("Content-type:text/json");
		exit(json_encode(array(
			'ret' => $code,
			'msg' => $msg,
			'data' => $data
		)));
	}

	public function returnOutput($code, $msg = '', $data = array())
	{
		return array(
			'ret' => $code,
			'msg' => $msg,
			'data' => $data
		);
		exit();
	}

	protected function isHttps()
	{
		$serverPort = $this->_request->getServer('SERVER_PORT');
		if ($serverPort == '443') {
			return true;
		}
		return false;
	}


	public function isDebugMode()
	{
		if ($this->mIsTest) {
			return true;
		}
		if ($this->mWhitelistConfig) {
			return true;
		}
		return false;
	}

}
