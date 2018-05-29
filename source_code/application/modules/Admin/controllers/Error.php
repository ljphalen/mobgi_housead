<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class ErrorController extends Common_BaseController {
    /**
     *
     * Enter description here ...
     * @author rock.luo
     *
     */
	public function errorAction($exception) {
		Yaf_Dispatcher::getInstance()->disableView();
		$staticroot = Yaf_Application::app()->getConfig()->staticroot;
		$this->assign("staticPath", $staticroot . '/static');
		header("Content-type:text/html;charset=utf-8");
		switch ($exception->getCode()) {
		case YAF_ERR_NOTFOUND_MODULE:
		case YAF_ERR_NOTFOUND_CONTROLLER:
		case YAF_ERR_NOTFOUND_ACTION:
		case YAF_ERR_NOTFOUND_VIEW:
				echo 404,':',$exception->getMessage();
			break;
		case -1 : 
			echo $this->getView()->render('error/msg.phtml', array('msg'=>$exception->getMessage()));
			break;
		default :
//			    echo 0,':',$exception->getMessage();
                echo $this->getView()->render('error/msg.phtml', array('msg'=>$exception->getMessage()));
			break;
		}
	}
}
