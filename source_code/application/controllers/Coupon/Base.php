<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class Coupon_BaseController extends Common_BaseController {


	public function init() {
		parent::init();
		$this->sTime = microtime(true);
		Yaf_Dispatcher::getInstance()->throwException(true);
		Yaf_Dispatcher::getInstance()->setErrorHandler(array($this,"myErrorHandler"));
		$this->checkToken();

	}

	/**
	 * 检查token
	 */
	protected function checkToken() {
		if (!$this->getRequest()->isPost()) return true;
		$token = $this->getRequest()->get('token');
		$result = Common::checkToken($token);
		if (Common::isError($result)) $this->output(-1, $result['msg']);
		return true;
	}

	public  function myErrorHandler($errno, $errstr, $errfile, $errline){
		switch ($errno) {
			case YAF_ERR_NOTFOUND_CONTROLLER:
			case YAF_ERR_NOTFOUND_MODULE:
			case YAF_ERR_NOTFOUND_ACTION:
				header(" Not Found");
				break;
			//忽略没有没有下标
			case 8:
			case 4096:
				break;
			default:
				$errCode = $errno;
				$errStr = str_replace(APP_PATH, '[PATH]', $errstr);
				$errFileName = str_replace(APP_PATH, '[PATH]', $errfile);
				$errFileLine = $errline;
				Common::sendLogError('ads', 0, '',  5, $errCode, $errStr, $errFileName ,$errFileLine);
				break;
		}
		return true;
	}

    public function __destruct() {

    }

}
