<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class ErrorController extends Yaf_Controller_Abstract {

	public function init() {
		Yaf_Dispatcher::getInstance()->disableView();
	}
	public function errorAction($exception) {
		/* error occurs */
		switch ($exception->getCode()) {
		case YAF_ERR_NOTFOUND_MODULE:
		case YAF_ERR_NOTFOUND_CONTROLLER:
		case YAF_ERR_NOTFOUND_ACTION:
		case YAF_ERR_NOTFOUND_VIEW:
			echo 404,':',$exception->getMessage();
			break;
	   default:
            	$errCode = $exception->getCode();
            	$errStr = $exception->getMessage();
            	$errFileName = $exception->getFile();
            	$errFileLine = $exception->getLine();
            	Common::sendLogError('ads', 0, '',  5, $errCode, $errStr, $errFileName ,$errFileLine);
                break;
		}
	}
}
