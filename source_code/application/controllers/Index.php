<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * yaf默认控制器，用来做模块跳转
 * @author ljp
 *
 */
class IndexController  extends Yaf_Controller_Abstract{
	/**
	 * 用来跳转多模块，根据域名来跳转
	 */
    public function indexAction() {
		$hostName = ($this->isHttps ()?'https://' : 'http://') . $this->_request->getServer ( 'HTTP_HOST' );
		switch ($hostName) {
            case Yaf_Application::app ()->getConfig ()->adminroot :
				$url = '/Admin/Index/index';
				break;
			case Yaf_Application::app ()->getConfig ()->apiroot :
				$url = '/Api/Index/index';
				break;
			case Yaf_Application::app ()->getConfig ()->statroot :
					$url = '/Stat/Index/index';
					break;
			case Yaf_Application::app ()->getConfig ()->spmroot :
					$url = '/track/index';
					break;
			case Yaf_Application::app ()->getConfig ()->couponroot :
				$url = '/Coupon/Index/index';
				break;
			default :
				$url = '/Admin/Login/index';
				break;
		}
		// Header("HTTP/1.1 303 See Other"); //这条语句可以不写
		Header ( "Location: " . $hostName.$url );
		exit ();
	}
	
	private  function isHttps(){
		$serverPort = $this->_request->getServer('SERVER_PORT');
		if($serverPort == '443'){
			return true;
		}
		return false;
		
	}
}