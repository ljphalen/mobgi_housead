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
			if (ENV == 'product') {
				exit('Access Denied!');
			} else {
				echo 404,':',$exception->getMessage();
			}
			break;
		default :
			if (ENV == 'product') {				
//				exit('Access Denied!');
                echo $this->getView()->render('error/msg.phtml', array('msg'=>$exception->getMessage()));
			} else {
//			    echo 0,':',$exception->getMessage();
                echo $this->getView()->render('error/msg.phtml', array('msg'=>$exception->getMessage()));
			}
			break;
		}
	}
}
