<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Common_BaseController
 * @author rock.luo
 *
 */
abstract class Mobgi_BaseController extends Yaf_Controller_Abstract {
	public $isReportToMonitor = 0;
	public $mWhitelistConfig = null;
	protected $mReportCode = 0;
	protected $mReportMsg = '';
	protected $mReportData = null;
	protected $mAppKey='';
	protected $mIsTest=0;
	protected $mDebugInfo = null;
	protected $mFlowId = 0;
	protected $mDevMode = null;
	protected $sTime = 0;
	
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
    		$this->mReportData = $this->mFlowId?$this->mFlowId:$data;
    	}
    	array(
    	     $config = array(   'success' => $code == 0 ? true : false ,
    	        'msg' => $msg,
    	        'data' => $data
    	));
    	if( $this->mIsTest){   
    	    $config['debugInfo'] = $this->mDebugInfo;
    	}
     	header("Content-type:text/json");
    	exit(json_encode ( $config)); 
    }
    
    

    public function __destruct() {
    	if($this->isReportToMonitor){
    		$execTime = intval(( microtime(true) - $this->sTime)*1000);
    		$module = $this->getRequest()->getModuleName();
    		$controller = $this->getRequest()->getControllerName();
    		$action = $this->getRequest()->getActionName();
    		$name =$controller.'_'.$action.'_'.$this->mAppKey.'_'.Util_ErrorCode::$mReportCodeDesc [$this->mReportCode];
    		if($this->mReportCode == Util_ErrorCode::FITER_CONFIG){
    			$name =$controller.'_'.$action.'_'.$this->mAppKey.'_'.$this->mReportData.'_'.Util_ErrorCode::$mReportCodeDesc [$this->mReportCode];
    		}
    		Common::sendLogAccess(0, 'ads', $name, $this->mReportMsg,  $execTime);
    	}
    }
    
    
    
    public  function localFormatOutput($code = 0, $msg = '', $data = array(), $globalConfig = array()) {   	
    	if($this->isReportToMonitor){
    		$this->mReportCode = $code;
    		$this->mReportMsg = $msg;
    	}
    	$config = array (
    			'success' => $code == 0 ? true : false,
    			'msg' => $msg,
    			'globalConfig' => $globalConfig,
    			'prioritAdsListConfig' => (object)array(),//废弃，兼容之前的版本
    			'data' => $data
    	);
    	if($this->mWhitelistConfig){
    	    $config['devMode'] = '';
    	    if($this->mDevMode){
    	        $config['devMode']   = 'YES';
    	    }
    	}
    	if($this->mIsTest){
    	    $config['debugInfo'] = $this->mDebugInfo;
    	}
    	header("Content-type:text/json");
    	exit ( json_encode ( $config) );
    }
    

   

}
