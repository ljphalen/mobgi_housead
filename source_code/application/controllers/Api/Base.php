<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Common_BaseController
 * @author rock.luo
 *
 */
abstract class Api_BaseController extends Yaf_Controller_Abstract {
    
    const PERPAGE = 10;
	protected $sTime = 0;
	protected $mDebugInfo = null;
    protected $OuterInnerRate = 100;    //外部投放单元与内部投放单元与单位换算,单位不同.内部单位:元,外部单位:分. 1元:100分
    protected $maxPageSize = 100;
    protected $perpage = 10;
	protected $mReportCode = 0;
	protected $mReportMsg = '';
	protected $mAppKey='';
	public  $isReportToMonitor = 0;
	
	 
    /**
     * 
     * Enter description here ...
     */
    public function init() {
        $this->sTime = microtime(true);
        Yaf_Dispatcher::getInstance()->throwException(true);
        Yaf_Dispatcher::getInstance()->setErrorHandler(array($this,"myErrorHandler"));	
	    Yaf_Dispatcher::getInstance()->disableView();
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

    
   
    /**
     * 
     * 获取post参数 
     * @param string/array $var
     */
    public function getPost($var) {
        if(is_string($var)) return Util_Filter::post($var);
        $return = array();
        if (is_array($var)) {
            foreach ($var as $key=>$value) {
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
     * @param string/array $var
     */
    public function getGet($var) {
        if(is_string($var)) return Util_Filter::get($var);
        $return = array();
        if (is_array($var)) {
            foreach ($var as $key=>$value) {
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
    public function isPost(){
    	return $this->getRequest()->isPost();
    }
    
    /*
     * 是否get
     */
    public function isGet(){
    	return $this->getRequest()->isGet();
    }
    

    public function getServer($var = null){
        if(is_null($var)) return $this->getRequest()->getServer();
        if(is_string($var)) return $this->getRequest()->getServer($var);
        if (is_array($var)) {
            $return = array();
            foreach ($var as $key=>$value) {
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
    public function getInput($var) {
    	if(is_string($var)) return $this->getVal($var);
    	if (is_array($var)) {
    		$return = array();
    		foreach ($var as $key=>$value) {
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
    private  function getVal($var) {
        if($this->isPost()){
        	$value = Util_Filter::post($var);
        	if(!is_null($value)) return $value;
        }
        if($this->isGet()){
        	$value = Util_Filter::get($var);
        	if(!is_null($value)) return $value;
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
    public function output($code, $msg = '', $data = array()) {
    	if($this->isReportToMonitor){
    		$this->mReportCode = $code;
    		$this->mReportMsg = $msg;
    	}
     	header("Content-type:text/json");
    	exit(json_encode(array(
    			'success' => $code == 0 ? true : false ,
    			'msg' => $msg,
    			'data' => $data
    	))); 
    }
    
    

    public function __destruct() {
    	if($this->isReportToMonitor){
    		$execTime = intval(( microtime(true) - $this->sTime)*1000);
    		$module = $this->getRequest()->getModuleName();
    		$controller = $this->getRequest()->getControllerName();
    		$action = $this->getRequest()->getActionName();
    		$name = 'hosead_'.$module.'_'.$controller.'_'.$action.'_'.$this->mAppKey.'_'.Util_ErrorCode::$mReportCodeDesc [$this->mReportCode];
    		Common::sendLogAccess(0, 'ads', $name, $this->mReportMsg,  $execTime);
    	}
    }
    
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $code
	 * @param unknown_type $msg
	 * @param unknown_type $data
	 */
	public function sonaOutput($code, $msg = '', $data = array()) {
		header("Content-type:text/json");
		exit(json_encode(array(
			'code' => $code,
			'msg' => $msg,
			'data' => $data
		)));
	}
    
    /**
     * 检测token
     * @param type $advertiser_id
     * @return boolean
     */
    public function checkSonaToken($advertiser_id){
        $useinfo = Advertiser_Service_UserModel::getUser($advertiser_id);
        if(empty($useinfo)){
            $this->sonaOutput(31010, 'object operated not exist ');
        }
        $bearerTokenStr = $_SERVER['HTTP_AUTHORIZATION'];
        $token = str_replace('Bearer ', '', $bearerTokenStr);
        if(empty($token)){
            $this->sonaOutput(30100, 'token is not exist ');
        }
        $appkey = $useinfo['appkey'];

        $string = base64_decode($token);
        list($advertiser_id, $time_stamp, $sign) = explode(',', $string);
        $tokenExpireTime = Common::getConfig("sonaConfig", "token_expire_time");
        if(time() - $time_stamp > $tokenExpireTime){
            $this->sonaOutput(30102, 'expired token ');
        }
        //校验token
        $checkSign = sha1($advertiser_id.$appkey.$time_stamp);
        if($checkSign != $sign){
            $this->sonaOutput(30101, 'wrong token');
        }
        return true;
    }
    
    /**
     * 
     * @param type $data
     * @return type
     */
    public function addSonaOperatelog($advertiser_id, $data){
        $data['uid']=$advertiser_id;
        $data['object']=$advertiser_id;
        $module =$data['module'];
        $sub_module=$data['sub_module'];
        $content = $data['content'];
        if(empty($module)|| empty($sub_module)){
            $this->sonaOutput(100001, 'param error! no module or no sub_module');
        }
        if(empty($content)){
            $this->sonaOutput(100002, 'param error! no content');
        }
        $Advertiser_operate_log_config =  Common::getConfig('advertiserConfig', 'Advertiser_operate_log');
        if(!isset($Advertiser_operate_log_config['advertiser'][$module])){
            $this->sonaOutput(100003, 'wrong module');
        }
        if(!in_array($sub_module, $Advertiser_operate_log_config['advertiser'][$module])){
            $this->sonaOutput(100004, 'wrong sub_module');
        }
        return Advertiser_Service_OperatelogModel::addOperateLog($data);
    }
    
    protected   function isHttps(){
    	$serverPort = $this->_request->getServer('SERVER_PORT');
    	if($serverPort == '443'){
    		return true;
    	}
    	return false;
    }

}
