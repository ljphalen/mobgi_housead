<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/9
 * Time: 20:41
 */
abstract class Spm_BaseController extends Yaf_Controller_Abstract {

    protected $sTime = 0;
    protected $mDebugInfo = null;
    protected $mReportType = 0;
    protected $mReportCode = 0;
    protected $mReportMsg = '';
    protected $mReportData = null;
    protected $jsonData = null;
    protected $status = 1;


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


    /**
     * format the data
     * @param $data
     * @param $formatKeys
     * @return array
     * @throws Exception
     */
    protected function format($data, $formatKeys) {
        foreach ($formatKeys as $key => $param) {
            if (isset($data[$param[0]])) {
                $val = $this->formatParam($data[$param[0]], $param[1]);
                if (is_null($val)) {
                    $this->error('filter fail:' . $param[0] . json_encode($param) . $data[$param[0]], Common_Expection_Spm::EXP_PARAM_ERROR);
                } else {
                    $data[$key] = $val;
                }
            } elseif (isset($param[2])) {
                $data[$key] = $param[2];
            } else {
                $this->error('miss field:' . $param[0], Common_Expection_Spm::EXP_PARAM_ERROR);
            }
        }
        return $data;
    }


    /**
     * format the params
     * @param $val
     * @param $param
     * @return float|int|null
     */
    protected function formatParam($val, $param) {
        switch ($param[0]) {
            case "int":
                $result = empty($val) ? 0 : (is_numeric($val) ? intval($val) : NULL);
                break;
            case "float":
                $result = is_numeric($val) ? floatval($val) : NULL;
                break;
            case "in":
                $result = (in_array($val, $param[1])) ? $val : NULL;
                break;
            case "len":
                $result = (strlen($val) == $param[1]) ? $val : NULL;
                break;
            case "maxLen":
                $result = (strlen($val) <= $param[1]) ? $val : substr($val, 0, $param[1]);;
                break;
            case "sRange":
                $result = ($param[1][0] <= strlen($val) && strlen($val) <= $param[1][1]) ? $val : NULL;
                break;
            case "iRange":
                $result = ($param[1][0] <= $val && $val <= $param[1][1]) ? intval($val) : NULL;
                break;
            case "toInt":
                $result = intval($val);
                break;
            case "toUpper":
                $result = strtoupper($val);
                break;
            case "toLower":
                $result = strtolower($val);
                break;
            case "normal":
                $result = $val;
                break;
            default:
                $result = NULL;
        }
        return $result;
    }

    /**
     * get the macros of request
     * @param $params
     * @param $type
     * @return array
     */
    protected function getMacros($params, $type){
        $macros = array();
        $marcosConfig = Common::getConfig('spmConfig', 'MACROS');
        foreach($marcosConfig[$type] as $item){
            if(isset($params[$item])){
                $macros[$item] = $params[$item];
            }
        }
        return $macros;
    }

    /**
     * replace the defined macros
     * @param $url
     * @param $macros
     * @return mixed
     */
    protected function replaceMacros($url, $macros){
        if(!empty($url)){
            foreach($macros as $key => $value){
                $url = str_replace("{".$key."}", $value, $url);
            }
        }
        return $url;
    }

    /**
     * get mober callback config
     * @param $mober
     * @return array
     */
    protected function getMoberCallback($mober){
        $moberConfig = Common::getConfig('spmConfig', 'MOBER');
        if(isset($moberConfig[$mober])){
            return $moberConfig[$mober];
        }else{
            return '';
        }
    }

    /**
     * get mober callback config
     * @param $mober
     * @return array
     */
    protected function getGdtHost($mober){
        $gdtHostConfig = Common::getConfig('spmConfig', 'GDTHOST');
        if(isset($gdtHostConfig[$mober])){
            return $gdtHostConfig[$mober];
        }else{
            return '';
        }
    }

    /**
     * get ip + ua hash
     * @param $ip
     * @param $ua
     * @return string
     */
    protected function getIpua($ip, $ua)
    {
        // IOS UA参数获取
        $start = stripos($ua,'CPU');
        $end = stripos($ua,' like');
        $cpu = substr($ua,$start,$end -$start);

        $startMobile = stripos($ua,'Mobile/');
        $modeltemp = substr($ua, $startMobile);
        $modeltemparr = explode(" ", $modeltemp);
        $model = $modeltemparr[0];

        // Android UA参数获取
        $ua_android = strtoupper($ua);
        $linux = stripos($ua_android,'LINUX');
        $android = stripos($ua_android,'Android');
        $str_android = substr($ua_android,$android);
        $comma_android = stripos($str_android,';');
        $versiontemp = substr($str_android,0,$comma_android);
        $version_android = str_replace(array('ANDROID', ' '), array('', ''), $versiontemp);

        $startBuild = stripos($ua_android,'BUILD/');
        $endBuild = stripos($ua_android, ')', $startBuild);#匹配BUILD/后面的第一个)
        $buildlength = $endBuild -$startBuild;
        $buildtemp = substr($ua_android, $startBuild, $buildlength);
        $build = str_replace(array(' ', 'BUILD/', ';WV', ')'), array('', '', '', ''), $buildtemp);

        #兼容android的ipua匹配
        #(1)苹果设备可以匹配到CPU  like Mobile/
        #(2)安桌设备应该可以匹配到
        #(3)其他设备的浏览器比较随机匹配不到,所以案桌直接使用Ip+整个ua
        if($start !==false && $end !== false && $startMobile !== false){
            # 替换掉特殊符号
            $model = str_replace(',', '', $model);
            return md5($ip.$cpu.$model);
        }else if($linux !==false && $android !== false && $startBuild !== false){
            return md5($ip.$version_android.$build);
        }else{
            return md5($ip.$ua);
        }
    }

    /**
     * record the error
     * @param $errCode
     * @param string $msg
     * @param bool $quit
     */
    protected function error($msg = '', $errCode, $quit = true) {
        if (empty($msg)) {
            $msg = json_encode($this->jsonData);
        }
        if (rand(1, 100) < 10) {
            $logContent = date('H:i:s') . '|' . $errCode . '|' . $msg . "\n";
            $typeSendToFile = 3;
            $fileName = 'spm_err_' . date('Ymd') . '.log';
            $filePath = Common::getConfig('siteConfig', 'logPath') . $fileName;
            error_log($logContent, $typeSendToFile, $filePath);
        }
        $this->status = 0;
        if ($quit) {
            $this->output($errCode, $msg);
        }
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
     * get POST params
     * @param string /array $var
     * @return array|mixed|null
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
     * get GET params
     * @param string /array $var
     * @return array|mixed|null
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
     * judge request type POST
     */
    public function isPost(){
        return $this->getRequest()->isPost();
    }

    /*
     * judge request type GET
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
     * get REQUEST params
     * @param string $var
     * @return array|null
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
     * result output
     * @param $code
     * @param string $msg
     * @param array $data
     */
    public function output($code, $msg = '', $data = array()) {
        $this->mReportCode = $code;
        $this->mReportMsg = $msg;
        $this->mReportData = $data;
        header("Content-type:text/json");
        $return = array(
            'ret' => $code,
            'msg' => $msg,
            'data' => $data
        );
        exit (json_encode($return));
    }

    public function __destruct() {
        $execTime = intval(( microtime(true) - $this->sTime)*1000);
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        $codeDesc = Common_Expection_Spm::getCodeDesc($this->mReportCode);
        $name = $controller.'_'.$action.'_'.$codeDesc;
        Common::sendLogAccess(0, 'spm', $name, $this->mReportMsg,  $execTime);
    }

    protected  function isHttps(){
        $serverPort = $this->_request->getServer('SERVER_PORT');
        if($serverPort == '443'){
            return true;
        }
        return false;
    }
}