<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Common {
    /**
     * @param $serviceName
     * @return object
     */
	static public function getService($serviceName) {
		return	Common_Service_Factory::getService($serviceName);
	}

    /**
     * @param $daoName
     * @return Common_Dao_Base
     */
	static public function getDao($daoName) {
		return Common_Dao_Factory::getDao($daoName);
	}

    /**
     * @param $fileName
     * @param string $key
     * @return mixed|string
     */
	static public function getConfig($fileName, $key = '') {
		static $config = array();
		$name = md5($fileName);
		if (!isset($config[$name]) || !$config[$name]) {
			$file = realpath(BASE_PATH . 'configs/' . $fileName . '.php');
			if (is_file($file)) $config[$name] = include $file;
		}
		if ($key) {
			return isset($config[$name][$key]) ? $config[$name][$key] : '';
		} else {
			return isset($config[$name]) ? $config[$name] : '';
		}
	}
	
	/**
	 * 字符串加密解密
	 * @param string $string	需要处理的字符串
	 * @param string $action	{ENCODE:加密,DECODE:解密}
	 * @return string
	 */
	static public function encrypt($string, $action = 'ENCODE') {
		if (!in_array($action, array('ENCODE', 'DECODE'))) $action = 'ENCODE';
		$encrypt = new Util_Encrypt(self::getConfig('siteConfig', 'secretKey'));
		if ($action == 'ENCODE') { //加密
			return $encrypt->desEncrypt($string);
		} else { //解密
			return $encrypt->desDecrypt($string);
		}
	}
	
	/**
	 * 获得token  表单的验证
	 * @return string
	 */
	static public function getToken() {
		if (!isset($_COOKIE['_securityCode']) || '' == $_COOKIE['_securityCode']) {
			/*用户登录的会话ID*/
			$key = substr(md5('TOKEN:' . time() . ':' . $_SERVER['HTTP_USER_AGENT']), mt_rand(1, 8), 8);
			setcookie('_securityCode', $key, null, '/'); //
			$_COOKIE['_securityCode'] = $key; //IEbug
		}
		return $_COOKIE['_securityCode'];
	}
	
	/**
	 * 验证token
	 * @param string $token
	 * @return mixed
	 */
	static public function checkToken($token) {
		if (!$_COOKIE['_securityCode']) return self::formatMsg(-1, '非法请求:token'); //没有token的非法请求
		if (!$token || ($token !== $_COOKIE['_securityCode'])) return self::formatMsg(-1, '非法请求,token:'. $token); //token错误非法请求
		return true;
	}
	
	/**
	 * 分页方法
	 * @param int $count
	 * @param int $page
	 * @param int $perPage
	 * @param string $url
	 * @param string $ajaxCallBack
	 * @param bool $flag
	 * @return string
	 */
	static public function getPages($count, $page, $perPage, $url, $ajaxCallBack = '') {
		$pageStr  = Util_Page::show_page($count, $page, $perPage, $url, '=', $ajaxCallBack );
		return $pageStr;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $code
	 * @param unknown_type $msg
	 * @param unknown_type $data
	 */
	static public function formatMsg($code, $msg = '', $data = array()) {
		return array(
			'code' => $code,
			'msg'  => $msg,
			'data' => $data
		);
	}
	
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $length
	 */
	static public function randStr($length) {
		$randstr = "";
		for ($i = 0; $i < (int) $length; $i++) {
			$randnum = mt_rand(0, 61);
			if ($randnum < 10) {
				$randstr .= chr($randnum + 48);
			} else if ($randnum < 36) {
				$randstr .= chr($randnum + 55);
			} else {
				$randstr .= chr($randnum + 61);
			}
		}
		return $randstr;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $msg
	 */
	static public function isError($msg) {
		if (!is_array($msg)) return false;
		$temp = array_keys($msg);
		return $temp == array('code', 'msg', 'data') ? true : false;
	}
	
	static public function getSession() {
		Yaf_Session::getInstance()->start();
		return Yaf_Session::getInstance();
	}
	
	/**
	 *
	 * queue对象
	 */
	static public function getQueue($instanceType='default') {
		$config = Common::getConfig('queueConfig');
		return Queue_Factory::getQueue($config, $instanceType);
	}
	
	/**
	 *
	 * @return Util_Lock
	 */
	static public function getLockHandle() {
		static $lock = null;
		if ($lock === null) {
			$lock = Util_Lock::getInstance();
		}
		return $lock;
	}
	
	/**
	 * @return Beanstalk
	 */
	static public function getBeanstalkHandle() {
		static $beanstalk = null;
		if ($beanstalk === null) {
			$beanstalk = new Util_Beanstalk();
			$config = Common::getConfig('beanstalkConfig', ENV);
			$beanstalk->config($config);
		}
		return $beanstalk;
	}
	
	/**
	 *
	 * @param unknown_type $name        	
	 * @param unknown_type $dir        	
	 * @return multitype:unknown_type 默认2M
	 * @param array $params
	 *        	array('maxSize' => 文件上传最大,'allowFileType' => 允许上传的文件类型,'resolution'=>分辨率列表)
	 */
	static public function upload($name, $dir, $uploadParams = array('maxSize'=>2048,'allowFileType'=>array('gif','jpeg','jpg','png','bmp','swf', 'txt','csv','apk','mp4','rar','zip','mp3')), $syncToCdn = true, $isRenameFile = false,$useTimeStamp = True) {
	    $img = $_FILES [$name];
		$attachPath = Common::getConfig ( 'siteConfig', 'attachPath' );
		if ($img ['error']) {
			$error= array(1=>'上传文件超过服务器上传限制',
					2=>'大小不能超过限制',
					3=>'只上传了一半文件',
					4=>'上传的临时目录出错');
			return Common::formatMsg(-1, '上传失败:' . $error[$img['error']]);
		}
		
		$params = array ();
		if (isset ( $uploadParams ['allowFileType'] ) && is_array ( $uploadParams ['allowFileType'] )) {
			$params ['allowFileType'] = $uploadParams ['allowFileType'];
		}
		if (isset ( $uploadParams ['maxSize'] ) && $uploadParams ['maxSize']) {
			$params ['maxSize'] = $uploadParams ['maxSize'];
		}
		if (isset ( $uploadParams ['resolution'] ) && is_array ( $uploadParams ['resolution'] )) {
		    $params ['resolution'] = $uploadParams ['resolution'];
		}
		if($useTimeStamp){
            $savePath = sprintf ( '%s/%s/%s', $attachPath, $dir, date ( 'Ym' ) );
        }else{
            $savePath = sprintf ( '%s/%s', $attachPath, $dir);
        }
		$uploader = new Util_Upload ( $params );
		if ($isRenameFile) {
			$oldFileName = substr ( $img ['name'], 0, strrpos ( $img ['name'], '.' ) );
			$ret = $uploader->upload ( $name, $oldFileName, $savePath );
		} else {
			$ret = $uploader->upload ( $name, uniqid (), $savePath );
		}
		if (is_string($ret)) {
			return Common::formatMsg ( - 1, '上传失败:' . $ret );
		}
        if($useTimeStamp){
            $filepath = sprintf ( '/%s/%s/%s', $dir, date ( 'Ym' ), $ret ['newName'] );
        }else{
            $filepath = sprintf ( '/%s/%s', $dir,$ret ['newName'] );
        }
		$ext = strtolower ( substr ( strrchr ( $img ['name'], '.' ), 1 ) );
		// if($ext != 'gif') image2webp($attachPath.$filepath, $attachPath.$filepath.".webp");
		if ($syncToCdn) {
			Common::syncToCdn ( $filepath );
		}
		return Common::formatMsg ( 0, '', $filepath );
	}
	
	static public function syncToCdn($filePath) {
		if (! $filePath) {
			return false;
		}
		if (! Util_Environment::isOnline ()) {
			return false;
		}
		$itemId = uniqid ();
		$sourcePath =Common::getAttachUrl()  . $filePath;
		$publishPath = $filePath;
		$CDN = new Util_Cdn ( $itemId, $sourcePath, $publishPath );
		$path = $CDN->publish ();
		return $path;
	}
	
	
	static public function downloadImg($imgurl, $dir, $withWebp = true) {
		if (!file_exists($dir)) mkdir($dir, 0777, true);
	
		//get remote file info
		$headerInfo = get_headers($imgurl, 1);
		$size = $headerInfo['Content-Length'];
		if (!$size) return false;
		$type = $headerInfo['Content-Type'];
		$mimetypes = array(
				'bmp' => 'image/bmp',
				'gif' => 'image/gif',
				'jpeg' => 'image/jpeg',
				'jpg' => 'image/jpeg',
				'png' => 'image/png',
		);
		if(!in_array($type,$mimetypes)) return false;
		$ext = end(explode("/", $type));
		$filename = md5($imgurl).".".$ext;
	
		$localFile = $dir."/".$filename;
		//if is exists
		if (file_exists($localFile)) return $filename;
	
		//download
		ob_start();
		readfile($imgurl);
		$imgData = ob_get_contents();
		ob_end_clean();
		$fd = fopen($localFile , 'a');
		if (!$fd) {
			fclose($fd);
			return false;
		}
		fwrite($fd, $imgData);
		fclose($fd);
		if ($withWebp) image2webp($localFile, $localFile.".webp");
		return $filename;
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	static public function getTime($fmt = 'Y-m-d H:i:s') {
		return strtotime(date($fmt));
	}


	/**
	 * @param $source
	 * @param $name
	 * @return array
	 */
	static public function resetKey($source, $name) {
		if (!is_array($source)) return array();
		$tmp = array();
		foreach ($source as $key=>$value) {
			if (isset($value[$name])) $tmp[$value[$name]] = $value;
		}
		return $tmp;
	}


	/**
	 * 重置数组键值对
	 * @param $source
	 * @param $keyName
	 * @param $valName
	 * @return array
     */
	public static function resetKeyValue($source, $keyName, $valName) {
		if (!is_array($source)) return array();
		$tmp = array();
		foreach ($source as $key=>$value) {
			if (isset($value[$keyName])) $tmp[$value[$keyName]] = $value[$valName];
		}
		return $tmp;
	}

	/**
	 * 金额转换
	 * @param float/int $num
	 * @return float
	 */
	static public function money($num) {
		if (function_exists("money_format")) {
			return money_format('%.2n', $num);
		} else {
			return number_format($num, 2, '.', '');
		}
	}
	
	/**
	 * error log
	 * @param string $error
	 * @param string $file
	 */
	static public function log($error, $file) {
	    $error = json_encode($error, JSON_UNESCAPED_UNICODE);
		error_log(date('Y-m-d H:i:s') .' ' . $error . "\n", 3, Common::getConfig('siteConfig', 'logPath') . $file);
	}

	
	public static function getAttachPath() {
		$attachroot = Yaf_Application::app()->getConfig()->attachroot;
		if(Util_Environment::isOnline()){
		    $attachroot = Common::getConfig('cdnConfig','cdn_path');
		    return $attachroot;
		}
		return $attachroot . '/attachs';
	}
    /**
     * 获取域名下面的附件地址(非CDN地址,慎用)
     * @return type
     */
    public static function getAttachUrl(){
        $attachroot = Yaf_Application::app()->getConfig()->attachroot;
        return $attachroot . '/attachs';
    }
    
    /**
     * 落获落地页域名
     * @return type
     */
    public static function getActUrl(){
        $attachroot = Yaf_Application::app()->getConfig()->actroot;
        return $attachroot;
    }
    
	public static function getWebRoot() {
      if (DEFAULT_MODULE == "Admin") {
			return Yaf_Application::app()->getConfig()->adminroot;
		}
		if(DEFAULT_MODULE == "Api"){
			return Yaf_Application::app()->getConfig()->apiroot;
		}
		if(DEFAULT_MODULE == "Coupon"){
			return Yaf_Application::app()->getConfig()->couponroot;
		}
		return Yaf_Application::app()->getConfig()->webroot;
	}
	
	public static function getIniConfig($name) {
		return Yaf_Application::app()->getConfig()->$name;
	}
	
	/**
	 * 判断请求是否为手机客户端来源discuz方法
	 */
	public static function checkMobileRequest(){
		if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false)) return true;
		if(isset($_SERVER['HTTP_X_WAP_PROFILE'])) return true;
		if(isset($_SERVER['HTTP_PROFILE'])) return true;
		$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
		if(!isset($ua)) return false;
		$mk = array('iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',
				'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung',
				'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',
				'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource',
				'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone',
				'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop',
				'benq', 'haier', '320x320', '240x320', '176x220', 'windows phone', 'cect', 'compal', 'ctl', 'lg',
				'nec', 'tcl', 'alcatel', 'ericsson', 'bird', 'daxian', 'dbtel', 'eastcom', 'pantech', 'dopod', 'philips', 'haier',
				'konka', 'kejian', 'lenovo', 'benq', 'mot', 'soutec', 'nokia', 'sagem', 'sgh',
				'sed', 'capitel', 'panasonic', 'sonyericsson', 'sharp', 'amoi', 'panda', 'zte');
	
		// 从HTTP_USER_AGENT中查找手机浏览器的关键字
		if((preg_match("/(".implode('|',$mk).")/i",$ua) || strpos($ua,'^lct') !== false)  && strpos($ua,'ipad') === false) {
			return true;
		}
		return false;
	}
		

	/**
	 * 获取用户的操作系统
	 * 
	 */
	static public function browserPlatform () {
		$agent = $_SERVER['HTTP_USER_AGENT'];
		
		$browser_platform=='';
		if (eregi('win', $agent) && strpos($agent, '95')) {
			$browser_platform=true;
		} elseif (eregi('win 9x', $agent) && strpos($agent, '4.90')) {
			$browserplatform=true;
		} elseif (eregi('win',$agent) && ereg('98', $agent)) {
			$browser_platform=true;
		} elseif (eregi('win', $agent) && eregi('nt 5.0', $agent)) {
			$browser_platform=true;
		} elseif (eregi('win', $agent) && eregi('nt 5.1', $agent)) {
			$browser_platform=true;
		} elseif (eregi('win',$agent) && eregi('nt 6.0',$agent)) {
			$browser_platform=true;
		} elseif (eregi('win', $agent) && eregi('nt 6.1', $agent)) {
			$browser_platform=true;
		} elseif (eregi('win', $agent) && ereg('32', $agent)) {
			$browser_platform=true;
		} elseif (eregi('win', $agent) && eregi('nt', $agent)) {
			$browser_platform=true;
		} elseif (eregi('Mac OS', $agent)) {
			$browser_platform=false;
		} elseif (eregi('linux', $agent)) {
			$browser_platform=false;
		} elseif (eregi('unix', $agent)) {
			$browser_platform=false;
		} elseif (eregi('sun', $agent) && eregi('os', $agent)) {
			$browser_platform=false;
		} elseif (eregi('ibm',$agent) && eregi('os', $agent)) {
			$browser_platform=false;
		} elseif (eregi('Mac', $agent) && eregi('PC', $agent)) {
			$browser_platform=false;
		} elseif (eregi('PowerPC', $agent)) {
			$browser_platform=false;
		} elseif (eregi('AIX', $agent)) {
			$browser_platform=false;
		} elseif (eregi('HPUX', $agent)) {
			$browser_platform=false;
		} elseif (eregi('NetBSD', $agent)) {
			$browser_platform=false;
		} elseif (eregi('BSD',$agent)) {
			$browser_platform=false;
		} elseif (ereg('OSF1', $agent)) {
			$browser_platform=false;
		} elseif (ereg('IRIX', $agent)) {
			$browser_platform=false;
		} elseif (eregi('FreeBSD', $agent)) {
			$browser_platform=false;
		}
		if ($browser_platform == '') {$browserplatform = false; }
		return $browser_platform;
	}
	
	/**
 	* google api 二维码生成【QRcode可以存储最多4296个字母数字类型的任意文本，具体可以查看二维码数据格式】
 	* @param string $chl 二维码包含的信息，可以是数字、字符、二进制信息、汉字。不能混合数据类型，数据必须经过UTF-8 URL-encoded.如果需要传递的信息超过2K个字节，请使用POST方式
 	* @param int $widhtHeight 生成二维码的尺寸设置
 	* @param string $EC_level 可选纠错级别，QR码支持四个等级纠错，用来恢复丢失的、读错的、模糊的、数据。
 	* 						   L-默认：可以识别已损失的7%的数据
 	* 						   M-可以识别已损失15%的数据
 	* 						   Q-可以识别已损失25%的数据
 	* 						   H-可以识别已损失30%的数据
 	* @param int $margin 生成的二维码离图片边框的距离
 	*/
	static public function  generateQRfromGoogle($chl,$widhtHeight ='100',$EC_level='H',$margin='0',$class='')
	{
		$chl = urlencode($chl);
		return  '<img src="http://chart.apis.google.com/chart?chs='.$widhtHeight.'x'.$widhtHeight.'&cht=qr&chld='.$EC_level.'|'.$margin.'&chl='.$chl.'" alt="QR code" width="'.$widhtHeight.'" Height="'.$widhtHeight.'" class="'.$class.'" />';
	}
	
	/**
	 * 二维码生成
	 * @param unknown_type $chl 二维码数据
	 * @param unknown_type $widhtHeight
	 * @param unknown_type $EC_level
	 * @param unknown_type $margin
	 * @param unknown_type $class
	 */
	static public function  generateQRfromLocal($chl,$errorCorrectionLevel,$matrixPointSize)
	{
		//include_once "Util/PHPQRcode/qrlib.php";
		Yaf_loader::import("Util/PHPQRcode/QRcode.php");
		$cacheKey = "qr-co-" . md5($chl);
		$cache = Cache_Factory::getCache ();
		$data = $cache->get($cacheKey );
		if (!$data) {
		$data =QRcode::png($chl,false , $errorCorrectionLevel, $matrixPointSize, 2);
			$cache->set ( $cacheKey, $data, 3*3600);
		}
		return $data;
	}
	
	/**
	 * 发送邮件
	 * @param  $title 标题
	 * @param  $body 主体内容
	 * @param  $to 发送的邮箱地址
	 * @param  $author 作者
	 * @param  $type 邮件类型，HTML或TXT
	 * @return 布尔类型
	 */
	static public function sendEmail ($title = '', $body = '', $to = '', $author = '', $type = 'HTML' ) {
		$smtp_config = Common::getConfig('smtpConfig');
		
		$smtp = new  Util_Smtp( $smtp_config['mailhost'], $smtp_config['mailport'], $smtp_config['mailauth'], $smtp_config['companymail'], $smtp_config['mailpasswd']);
		$author = ($author == '') ?$smtp_config['mailauthor']: $author ;
		$send = $smtp->sendmail($to,$smtp_config['companymail'], $author, $title, $body, $type);
		
		return $send;
	}	

	
	/**
	 * 去掉html标签
	 * @param unknown_type $document
	 */
	static public function replaceHtmlAndJs( $document ){
		$search = array ("'<script[^>]*?>.*?</script>'si",  // 去掉 javascript
				"'<[\/\!]*?[^<>]*?>'si",           // 去掉 HTML 标记
				"'([\r\n])[\s]+'",                 // 去掉空白字符
				"'&(quot|#34);'i",                 // 替换 HTML 实体
				"'&(amp|#38);'i",
				"'&(lt|#60);'i",
				"'&(gt|#62);'i",
				"'&(nbsp|#160);'i",
				"'&(iexcl|#161);'i",
				"'&(cent|#162);'i",
				"'&(pound|#163);'i",
				"'&(copy|#169);'i",
				"'&#(\d+);'e");                    
	
		$replace = array ("","","\\1","\"","&","<",">"," ",chr(161),chr(162),chr(163),chr(169),"chr(\\1)");	
		return @preg_replace($search,$replace,$document);
	}
	
	
	
	
	/**
	 * 引入SEO信息
	 */
	static public function addSEO(&$seo_object, $title='', $keyworks='', $description='') {
		if ( $title != '') {
			$seo_object->assign('title',$title);
		}
		if ($keyworks != '') {
			$seo_object->assign('keyworks',$keyworks);
		}
		if ($description != '') {
			$seo_object->assign('description',$description);
		}
	}

	
	/**
	 * 文件大小大于1000,由M转换为G
	 * @param 　float $numbers
	 * @return float
	 */
	static public  function numConvert($numbers){
		$numbers = $numbers.'M';
		if($numbers >= 1000){
			$numbers = sprintf("%.2f", $numbers /(1024*1024)).'G';;
		}
		return $numbers;
	}
	
    /**
     * 写入日志文件
     * @param unknown_type $path
     * @param unknown_type $file_name
     * @param unknown_type $data
     * @param unknown_type $method
     */
	static public  function WriteLogFile($path, $file_name, $data, $method = 'ab'){
		//日志开关
		$log_status = Game_Service_Config::getValue('log_status');
		//if(!$log_status) return false;
		if(!$path || !$file_name) return false;
		//创建目录
		if(!Util_Folder::isDir($path)){
			Util_Folder::mkRecur($path);
		}
		return Util_File::logFile($path.$file_name, $data, $method);
		
	}
	
	/**
	 * 获取客户段访问IP地址,成功返回客户段IP,失败返回空
	 */
	static public  function  getClientIP() {
		if (isset($_SERVER['HTTP_CLIENT_IP']) and !empty($_SERVER['HTTP_CLIENT_IP'])){
			return $_SERVER['HTTP_CLIENT_IP'];
		}
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and !empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			return strtok($_SERVER['HTTP_X_FORWARDED_FOR'], ',');
		}
		if (isset($_SERVER['HTTP_PROXY_USER']) and !empty($_SERVER['HTTP_PROXY_USER'])){
			return $_SERVER['HTTP_PROXY_USER'];
		}
		if (isset($_SERVER['REMOTE_ADDR']) and !empty($_SERVER['REMOTE_ADDR'])){
			return $_SERVER['REMOTE_ADDR'];
		} else {
			return "0.0.0.0";
		}
	}
	
	
	
	/**
	 * 两个日期相差的天数,小时，分，秒
	 * @param string $start_time
	 * @param string $end_time
	 * @param string $unit d h i s
	 * @param boolean $isFlag 是否未自然日计算
	 * @return number|boolean
	 */
	static public function diffDate($startDate, $endDate, $unit = "d") { //时间比较函数，返回两个日期相差几秒、几分钟、几小时或几天
		switch ($unit) {
			case 's':
				$dividend = 1;
				break;
			case 'i':
				$dividend = 60; 
				break;
			case 'h':
				$dividend = 3600;
				break;
			case 'd':
				$dividend = 86400;
				break; 
			default:
				$dividend = 86400;
		}
		
		$startTime = strtotime($startDate);
		$endTime = strtotime($endDate);
		if ($startTime && $endTime){
			if($dividend == 86400){
				$startDay = date('Y-m-d 00:00:01', strtotime($startDate));
				$endDay = date('Y-m-d 00:00:01', strtotime($endDate));
				$startTime = strtotime($startDay);
				$endTime = strtotime($endDay);
				return round(($endTime - $startTime) / $dividend);
			}else{
				return round(($endTime - $startTime) / $dividend);
			}
			
		}
		return false;
	}

	
	/**
	 * 返回区间的开始日期与结束日期
	 * @param unknown_type $time
	 * @param unknown_type $section_start
	 * @param unknown_type $section_end
	 */
	static public function getSectionTime($time, $section_start = 1, $section_end = 1){
		
		//
		$time_arr = array();
		if($section_start == 1){
			$time_arr['start_time'] = $time;
		}else{
			$tmp = date('Y-m-d 00:00:00', $time);
			$time_arr['start_time'] = strtotime( $tmp." + ".($section_start-1)." day" );
		}
		
		if($section_end == 1){
			$tmp = date('Y-m-d 23:59:59', $time);
			$time_arr['end_time'] = strtotime($tmp);
		}else{
			$tmp = date('Y-m-d 23:59:59', $time);
			$time_arr['end_time'] = strtotime( $tmp." + ".($section_end-1)." day" );
		}
		
		return $time_arr;
		
	}
	
    static public function isValidImei($imei) {
        if (!$imei) {
            return false;
        }

        return ($imei != 'FD34645D0CF3A18C9FC4E2C49F11C510') ? true : false;
    }
    
   static  public  function arrayToObject($array) {
    
    	if (is_array($array)) {
    		$obj = new StdClass();
    		 
    		foreach ($array as $key => $val){
    			$obj->$key = $val;
    		}
    	}
    	else { $obj = $array; }
    
    	return $obj;
    }
    
    static public  function objectToArray($object) {
    	if (is_object($object)) {
    		foreach ($object as $key => $value) {
    			$array[$key] = $value;
    		}
    	}
    	else {
    		$array = $object;
    	}
    	return $array;
    }
    
    static public function getSeasonTimeRange(){
    	$season = ceil((date('n'))/3);//当月是第几季度
    	$startTime =  date('Y-m-d H:i:s', mktime(0, 0, 0, $season*3-3+1,1,date('Y')));
    	$endTime =    date('Y-m-d H:i:s', mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y')));
        return array('startTime'=>$startTime, 'endTime'=>$endTime);
    }

    static   public  function isAfterVersion($version, $afterVersion) {
        return self::compareVersion($version, $afterVersion) >= 0;
    }
    
    static  public  function isBeforeVersion($version, $beforeVersion) {
        return self::compareVersion($version, $beforeVersion) <=0;
    }
        
        
    static public function myErrorHandler($errno, $errstr, $errfile, $errline){
        switch ($errno) {
        case YAF_ERR_NOTFOUND_CONTROLLER:
        case YAF_ERR_NOTFOUND_MODULE:
        case YAF_ERR_NOTFOUND_ACTION:
             header("Not Found");
        break;
    
        default:
            echo "Unknown error type: [$errno] $errstr<br />\n";
            break;
        }
    
        return true;
    }
    

    

    /**
     * sp 参数分析
      *平台(1：安卓，2：ios)_手机横竖屏(1横屏，2竖屏)_手机品牌_机型_客户端版本_系统版本_分辨率（320*480)_网络类型(1：wifi,2：4G,3:3G, 4：2G)_ 运营商(1联通、2电信、3移动或4其
        他)_uuid(公司生成的uuid)_设备标识(ios idfa,安卓:imei)
     * @param string $sp
     * @param string $key
     */
    static public function parseSp($sp, $key = ''){
        if(!isset($sp)) return false;
        $tmp = array();
        $data = explode('_',$sp);
        $tmp['sp'] = $sp ? $sp : '';
        $tmp['platform'] = is_null($data[0]) ? '' : $data[0];
        $tmp['screenDirection'] = is_null($data[1]) ? '' : $data[1];
        $tmp['brand'] = is_null($data[2]) ? '' : $data[2];
        $tmp['screenSize'] = is_null($data[3]) ? '' : $data[3];
        $tmp['model'] = is_null($data[4]) ? '' : $data[4];
        $tmp['clientVersion'] = is_null($data[5]) ? '' : $data[5];
        $tmp['systemVertion'] = is_null($data[6]) ? '' : $data[6];
        $tmp['resolution'] = is_null($data[7]) ? '' : $data[7];
        $tmp['netType'] = is_null($data[8]) ? '' : $data[8];
        $tmp['operator'] = is_null($data[9]) ? '' : $data[9];
        $tmp['uuid'] = is_null($data[10]) ? '' : $data[10];
        $tmp['udid'] = is_null($data[11]) ? '' : $data[11];
        //修复当uuid值为XXXX_ios的异常情况
        if(strtolower($tmp['udid'])=='ios'){
            $tmp['udid'] = is_null($data[12]) ? '' : $data[12];
        }
        return ($key) ? $tmp[$key] : $tmp;
    }
    
    //驼峰转下划线命名
    static public function snakeCase($str) {
        return is_string($str) ? strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', $str)) : $str;
    }
    
    /**
       * PHP获取字符串中英文混合长度
      * @param $str string 字符串
       * @param $$charset string 编码
       * @return 返回长度，1中文=1位，1英文=1位
       */
    static public function   strLength($str,$charset='utf-8'){
        if($charset=='utf-8'){
            $str = iconv('utf-8','gb2312',$str);
        } 
        $num = strlen($str);
        $cnNum = 0;
        for($i=0;$i<$num;$i++){
        if(ord(substr($str,$i+1,1))>127){
                 $cnNum++;
                 $i++;
            }
        }
        $enNum = $num-($cnNum*2);
        $number = $enNum+$cnNum;
        return ceil($number);
    }
    
    /**
     * 根据邮箱后缀跳转到不同的页面登录邮箱
     * @param type $email
     * @return type
     */
    public static function return_email_web_url($email){
        $emailArr = explode("@", $email);
        $email_web_url= Common::getConfig("mailConfig", "email_web_url");
        if(isset($email_web_url["@".$emailArr[1]])){
            return "http://".$email_web_url["@".$emailArr[1]];
        }else{
            return 'http://mail.' . $emailArr['1'];
        }
    }
    
    /**
     * title前缀
     * @return type
     */
    static public function getTitlePre(){
        $titlePreConfig = array('develop'=>'开发环境__', 'test'=>'测试环境__', 'product'=>'');
        return defined('ENV') ? $titlePreConfig[ENV] : $titlePreConfig['product'];
    }
    
    /**
    * 获取一天内的时间串
    * @param type $start_hour
    * @param int $end_hour
    * @return boolean
    */
    static public function get_day_time_series($start_hour, $end_hour){
        $series_arr = array();
        if($start_hour>$end_hour || $start_hour<0 || $end_hour >24){
            return false;
        }
        if($end_hour==0){
            $end_hour = 24;
        }
        for($i=1; $i<=24; $i++){
            if($i>$start_hour && $i<=$end_hour){
                $series_arr[$i]='11';
            }else{
                $series_arr[$i]='00';
            }
        }
        $series_str = implode('', $series_arr);
        return $series_str;
    }
    /**
    * 获取一周的时间系列串
    * @param type $start_hour
    * @param int $end_hour
    * @return boolean
    */
    static public function get_week_time_series($start_hour, $end_hour){
        $week_series_str = '';
        if($start_hour>$end_hour || $start_hour<0 || $end_hour >24){
            return false;
        }
        if($end_hour==0){
            $end_hour = 24;
        }
        $day_time_series_str = self::get_day_time_series($start_hour, $end_hour);
        for($i=0; $i<7; $i++){
            $week_series_str.= $day_time_series_str;
        }
        return $week_series_str;
    }
    /**
    * 根据一周的时间系列串获取设置开始时间和结束时间
    * @param type $week_time_series
    * @return boolean
    */
   static public function get_hours_from_series($week_time_series){
       if(strlen($week_time_series) != 336){
           return false;
       }
       $day_time_series = substr($week_time_series, 0, 48);
       $start_hour = strpos($day_time_series, '1');
       $end_hour = strrpos($day_time_series, '1');
       $result=array();
       $result['start_hour'] = floor($start_hour/2.0);
       $result['end_hour'] = ceil($end_hour/2.0);
       return $result;
   }
   /**
    * 星期一到星期日为顺序，每半小时一上索引，一天48个时段，获取当前时间属于这个timeseries的索引。
    * @return type
    */
   static public function get_cur_timeseries_index(){
       $week = date('w') == 0 ? 7 : date('w');
        $hourindex = ($week-1) * 48 -1;
        $hour = date('H');
        $minute = date('i');
        $index = $hourindex + $hour * 2 + ceil($minute/30) ;
        return $index;
   }
	/**
	 * 时间区段不满336个，自动填充0
	 * @param type $week_time_series
	 * @return boolean
	 */
	static public function update_time_series_add_zero($time_series){
		$len = strlen($time_series);
		$dif_len = 336 - $len;
		for($i = 0;$i < $dif_len;$i ++){
			$time_series .= "0";
		}
		return $time_series;
	}
   /**
    * 根据上传的H5配置生成zip文件包
    * @param type $macros
    * @param type $dir
    * @return type
    */
   static public function createZip($macros) {
	    $attachPath = Common::getConfig('siteConfig', 'attachPath');
        $tmpname = uniqid();
	    $dstdir = sprintf('%s/%s/%s/%s', $attachPath, 'h5templates/zips', date('Ym'), $tmpname);
        $zipfile = sprintf('%s/%s/%s/%s%s', $attachPath, 'h5templates/zips', date('Ym'), $tmpname, '.zip');
        $filepath = sprintf('/%s/%s/%s%s', 'h5templates/zips', date('Ym'), $tmpname,'.zip');
        $filedir = sprintf('/%s/%s/%s', 'h5templates/zips', date('Ym'), $tmpname);
        if($macros['h5template'] == 1){//轮播图
            $sourcedir =  sprintf('%s/%s', $attachPath, 'h5templates/movie_Carousel');
            $destindexfile = $dstdir . '/movie/index.html';
            $filedir .='/movie';
        }else if($macros['h5template'] == 2){//单图
            $sourcedir =  sprintf('%s/%s', $attachPath, 'h5templates/movie_single');
            $destindexfile = $dstdir . '/index.html';
        }
        Util_Upload::mkRecur($dstdir);
        self::recurse_copy($sourcedir, $dstdir);
        $indexContent = file_get_contents($destindexfile);
        foreach($macros as $macro_key=>$macro_value){
            $indexContent = str_replace("{{".$macro_key."}}", $macro_value, $indexContent);
        }
        Util_File::del($destindexfile);
        Util_File::write($destindexfile, $indexContent);
        $zipobj = new Util_PHPZip();
        $zipobj->Zip($dstdir, $zipfile);
        Common::syncToCdn($filepath);
        $attachUrl = self::getAttachUrl();
        $previewUrl = $attachUrl . $filedir;
        $result = array();
        $result['h5'] = $filepath;
        $result['previewUrl'] = $previewUrl;
        return $result;
	}
    /**
     * 复制文件夹
     * @param type $src
     * @param type $dst
     */
    static public function recurse_copy($src,$dst) {  // 原目录，复制到的目录
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    
    public static function sendLogAccess($playerid = 0,  $module='ads' , $oper,  $msg='', $execTime){
    	if(!Util_Environment::isOnline()){//如果没开启上报则直接返回
    		return false;
    	}
        $monitorConfig = Common::getConfig('monitorConfig');
        if(empty($monitorConfig)){
        	return false;
        }
		if($module=='spm'){
			$monitorConfig['REPORTDATA_PROJECT_TYPE'] = $monitorConfig['REPORTDATA_SPM_PROJECT_TYPE'];
		}
    	Util_ReportData::loginit($monitorConfig['REPORTDATA_HOST'], $monitorConfig['REPORTDATA_PORT']);
    	$userAgent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
    	Util_ReportData::SEND_LOG_ACCESS($playerid, $module, $oper, 200, $msg, $monitorConfig['REPORTDATA_PROJECT_TYPE'], $execTime, $userAgent);
    }
    
    public static function sendLogError($module='ads', $playerid=0, $cmd='', $errLevel = 5, $errCode, $errStr, $errFileName='',$errFileLine=''){
    	if(!Util_Environment::isOnline()){//如果没开启上报则直接返回
    	    Common::outputErrorData($errCode, $errStr, $errFileName, $errFileLine);
    		return false;
    	}
    	$monitorConfig = Common::getConfig('monitorConfig');
    	if(empty($monitorConfig)){
    		return false;
    	}
		if($module=='spm'){
			$monitorConfig['REPORTDATA_PROJECT_TYPE'] = $monitorConfig['REPORTDATA_SPM_PROJECT_TYPE'];
		}
    	Util_ReportData::loginit($monitorConfig['REPORTDATA_HOST'], $monitorConfig['REPORTDATA_PORT']);
    	$userAgent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
    	$execTime = '';
    	Util_ReportData::SEND_LOG_ERROR($module, $playerid, $cmd, $errLevel, $errCode, $errStr, $monitorConfig['REPORTDATA_PROJECT_TYPE'], $execTime, $userAgent, $errFileName, $errFileLine);
    }
    
	public static function outputErrorData($errCode ,$errStr, $errFileName, $errFileLine){
	    if(Util_Environment::isOnline()){
	        return false;
	    }
	    echo 'errno: '.$errCode.'<br>';
	    echo 'errstr: '.str_replace(APP_PATH, '[PATH]', $errStr).'<br>';
	    echo 'errfile: '.str_replace(APP_PATH, '[PATH]', $errFileName).'<br>';
	    echo 'errline: '.$errFileLine.'<br>';
	}
    
    /**
     * 对比两个数组指定键的值是否相同
     * @param type $left_arr
     * @param type $right_arr
     * @param type $compare_key_arr
     * @return string
     */
    static public function compare_different($left_arr, $right_arr, $compare_key_arr){
        $return = array();
        $return['left'] = '';
        $return['right'] = '';
        if(empty($compare_key_arr)){
            return $return;
        }
        foreach($compare_key_arr as $key){
            if($left_arr[$key] != $right_arr[$key]){
                $return['left'] .= $key.":".$left_arr[$key]. '  ';
                $return['right'] .= $key.":".$right_arr[$key]. '  ';
            }
        }
        return $return;
    }
    
    /**
     * 判断字符串是否是json串
     * @param type $str
     * @return type true是json串, false不是json串
     */
    static public function is_json($string) { 
        if(is_string($string)){
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }else{
            return false;
        }
    }
    
    /**
     * 获取
     * @param type $file
     * @return type
     */
    static public function video_info($file) {   
        ob_start();
        $cmd = sprintf('/usr/local/ffmpeg/bin/ffmpeg -i "%s" 2>&1', $file);
        passthru($cmd);
        $info = ob_get_contents();
        ob_end_clean(); // 通过使用输出缓冲，获取到ffmpeg所有输出的内容。
        $ret = array();
        // Duration: 01:24:12.73, start: 0.000000, bitrate: 456 kb/s
        if (preg_match("/Duration: (.*?), start: (.*?), bitrate: (\\d*) kb\/s/", $info, $match)){
            $ret['duration'] = $match[1]; // 提取出播放时间
            $da = explode(':', $match[1]); $ret['seconds'] = $da[0] * 3600 + $da[1] * 60 + $da[2]; // 转换为秒
            $ret['start'] = $match[2]; // 开始时间
            $ret['bitrate'] = $match[3]; // bitrate 码率 单位 kb 、
        } // Stream #0.1: Video: rv40, yuv420p, 512x384, 355 kb/s, 12.05 fps, 12 tbr, 1k tbn, 12 tbc
        if (preg_match("/Video: (.*?), (.*?), (.*?), (.*?), (.*?), (.*?), (.*?)[,\s]/", $info, $match)) {
                $ret['vcodec'] = $match[1]; // 编码格式
                $ret['vformat'] = $match[2]; // 视频格式
                $ret['resolution'] = $match[3]; // 分辨率
                $a = explode('x', $match[3]);
                $ret['width'] = intval($a[0]);
                $ret['height'] = intval($a[1]);
                $ret['kb'] = $match[4];
                $frames = explode(' ', $match[5]);
                $ret['frames'] = $frames[0];
        } // Stream #0.0: Audio: cook, 44100 Hz, stereo, s16, 96 kb/s
        if (preg_match("/Audio: (\w*), (\d*) Hz/", $info, $match)) {
                $ret['acodec'] = $match[1]; // 音频编码
                $ret['asamplerate'] = $match[2]; // 音频采样频率
        }
        if (isset($ret['seconds']) && isset($ret['start'])) {
                $ret['play_time'] = $ret['seconds'] + $ret['start']; // 实际播放时间
        }
        $ret['size'] = filesize($file); // 文件大小
        return array($ret,$info);
    }
    /**
     * 断点续传上传
     * @param type $name
     * @param type $fileName
     * @param type $tmpDir
     * @param type $desDir
     * @param type $uploadParams
     * @param type $synctocdn
     */
    static public function breakpointupload($name, $fileName, $tmpDir, $desDir, $uploadParams=array('maxSize'=>2048,'allowFileType'=>array('gif','jpeg','jpg','png','bmp','swf', 'txt','csv','apk','mp4','rar','zip','mp3')),  $synctocdn = true) {
        $totalSize = $uploadParams['totalSize'];
        $isLastChunk = $uploadParams['isLastChunk'];
        $isFirstUpload = $uploadParams['isFirstUpload'];
        $ext = strtolower(substr(strrchr($fileName, '.'), 1));
        if(empty($ext) || !in_array($ext, $uploadParams['allowFileType'])){
            return self::formatMsg(1, 'error: extension error, '. $ext. " not allowed!");
        }
        $attachPath = Common::getConfig('siteConfig', 'attachPath');
        $breakpointTmpPath = sprintf('%s/%s/', $attachPath, $tmpDir);
        Util_Upload::mkRecur($breakpointTmpPath); 
        $relativePath = sprintf('/%s/%s/', $desDir, date('Ym'));
        $filepath = sprintf('/%s/%s/%s', $dir, date('Ym'), $ret['newName']);
        $savePath = sprintf('%s/%s/%s', $attachPath, $desDir, date('Ym'));
        Util_Upload::mkRecur($savePath); 
        $newFile = '';
        $msg = '';
        $completeUploadFlag = false;
        $tmpFilename = $breakpointTmpPath . $fileName;
        if ($_FILES[$name]['error'] > 0) {
            $status = 500;
            $msg = 'error: status 500';
        } else {
            // 如果第一次上传的时候，该文件已经存在，则删除文件重新上传
            if ($isFirstUpload == '1' && file_exists($tmpFilename) && filesize($tmpFilename) == $totalSize) {
                unlink($tmpFilename);
            }
            // 否则继续追加文件数据
            if (!file_put_contents($tmpFilename, file_get_contents($_FILES[$name]['tmp_name']), FILE_APPEND)) {
                $status = 501;
                $msg = 'error: status 501';
            } else {
                // 在上传的最后片段时，检测文件是否完整（大小是否一致）
                if ($isLastChunk === '1') {
                    $tmpFileSize = filesize($tmpFilename);
                    if ($tmpFileSize == $totalSize) {
                        $newFileName = uniqid() . '.' . $ext;
                        $newFile = $savePath . '/' . $newFileName;
                        rename($tmpFilename, $newFile); //上传成功的文件转移到新地址, 对于一个40M的文件,copy+unlink方式需要7.6249899864197秒,而rename方式,只需要0.024738788604736,快300倍.因此,谨慎使用copy+unlink方式
                        $md5file = md5_file($newFile);
                        $completeUploadFlag = true;
                        $status = 200;
                    } else {
                        $status = 502;
                        $data = array();
                        $data['status'] = $status;
                        $data['totalSize'] = $tmpFileSize;
                        $data['isLastChunk'] = $isLastChunk;
                        return self::formatMsg(0, 'error: upload interrupted! Rename upload file and reupload!', $data);
                    }
                } else {
                    //如果第一次上传就把整个文件上传完了,则返回成功标识
                    if ($isFirstUpload == '1' && $totalSize == $_FILES[$name]['size']) {
                        $newFileName = uniqid() . '.' . $ext;
                        $newFile = $savePath . '/' . $newFileName;
                        rename($tmpFilename, $newFile);
                        $md5file = md5_file($newFile);
                        $completeUploadFlag = true;
                    }
                    $status = 200;
                }
            }
        }
        $curTotalSize = $newFile?filesize($newFile):  filesize($tmpFilename);
        $data = array();
        $data['status'] = $status;
        $data['totalSize'] = $curTotalSize;
        $data['isLastChunk'] = $isLastChunk;
        if($completeUploadFlag){
            $filepath = $relativePath. $newFileName;
            if($synctocdn){
                Common::syncToCdn($filepath);
            }
            $data['filepath'] = $filepath;
            $data['md5file'] = $md5file;
        }
        return self::formatMsg(0, $msg, $data);
	}
	
	
	/**
	 * ios 2.2.0以上，android 1.0.0以上则下发的bidInfo为list，否则为对象。
	 *
	 * @param type $platform
	 * @param type $version
	 * @return boolean
	 */
	static public function isBidInfoList($platform, $version, $adType){
	    if($platform == Common_Service_Const::IOS_PLATFORM && version_compare ( $version, '2.2.0', '<=' )){
	        return false;
	    }
	    if($platform == Common_Service_Const::ANDRIOD_PLATFORM){
	        //视频1.0.0以下，或者插页的3.0.0使用对象 ,后面插页版本重新订了1.0.0
	        if( ($adType == Common_Service_Const::VIDEO_AD_SUB_TYPE) && version_compare ( $version, '1.0.0', '<=' ) ){
	            return false;
	        }
	        //
	        if(($adType == Common_Service_Const::PIC_AD_SUB_TYPE) && version_compare ( $version, '3.0.0', '=' ) ){
	            return false;
	        }
	
	    }
	    return true;
	}
	/**
	 * 获取设备 userAgent
	 * @return string
     */
	static public function getUserAgent(){
		$userAgent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
		return $userAgent;
	}
	
static public	function combination($arr, $size = 1) {
	    $len = count($arr);
	    $max = pow(2,$len) - pow(2,$len-$size);
	    $min = pow(2,$size)-1;
	    $returnArr = array();
	    for ($i=$min; $i<=$max; $i++){
	        $count = 0;
	        $tempArr = array();
	        for ($j=0; $j<$len; $j++){
	            $a = pow(2, $j);
	            $t = $i&$a;
	            if($t == $a){
	                $tempArr[] = $arr[$j];
	                $count++;
	            }
	        }
	        if($count == $size){
	            $returnArr[] = $tempArr;
	        }
	    }
	    return $returnArr;
	}
	
	static public	function validthirdPartyInput($content) {
	   if(preg_match('/^[0-9a-zA-Z\x{4e00}-\x{9fa5}%,_\#\-\.\+\/\@\\\~]*$/u', $content)) {
	       return true;
	   }
	   return false;
	}

	/**
	 * 把十进制的数字转成其它进制的（长url地址转成短url）
	 * @param type $dec
	 * @param int $toRadix
	 * @return string
	 */
	static public function dec2Any($dec, $toRadix) {
		$MIN_RADIX = 2;
		$MAX_RADIX = 62;
		$num62 = 'RSzdFWJf8Zibw91eC04lGHcxjPgY2ap57rEqIXK3VUoQ6hkuMmyTtLODNnvsBA';
		if ($toRadix < $MIN_RADIX || $toRadix > $MAX_RADIX) {
			$toRadix = 2;
		}
		if ($toRadix == 10) {
			return $dec;
		}
		// -Long.MIN_VALUE 转换为 2 进制时长度为65
		$buf = array();
		$charPos = 64;
		$isNegative = $dec < 0; //(bccomp($dec, 0) < 0);
		if (!$isNegative) {
			$dec = -$dec; // bcsub(0, $dec);
		}

		while (bccomp($dec, -$toRadix) <= 0) {
			$buf[$charPos--] = $num62[-bcmod($dec, $toRadix)];
			$dec = bcdiv($dec, $toRadix);
		}
		$buf[$charPos] = $num62[-$dec];
		if ($isNegative) {
			$buf[--$charPos] = '-';
		}
		$_any = '';
		for ($i = $charPos; $i < 65; $i++) {
			$_any .= $buf[$i];
		}
		return $_any;
	}
    
    
    /**
     * 遍历所有文件,同步attachs目录下的所有文件到CDN服务器
     * @param type $dir 真实地址：/vagrant/mobgi_housead/branches/mobgi_housead_200170629/source_code/public/../attachs/landingpage/cqb/20180201/t5uc
     * @param type $relativeDir 相对地址：/landingpage/cqb/20180201/t5uc
     * 上传后CDN地址为：https://dl2.gxpan.cn/ad/landingpage/ddd/20180201/t5uc/css/base.css
     * 注意CDN地址的前缀为 https://dl2.gxpan.cn/ad/
     * @return type
     */
    static public function syncToCdnDir($dir, $relativeDir) {
        $files = array();
        if(@$handle = opendir($dir)) { //注意这里要加一个@，不然会有warning错误提示：）
            while(($file = readdir($handle)) !== false) {
                if($file != ".." && $file != ".") { //排除根目录；
                    if(is_dir($dir."/".$file)) { //如果是子文件夹，就进行递归
                        $files[$file] = self::syncToCdnDir($dir."/".$file, $relativeDir."/".$file);
                    } else { //不然就将文件的名字存入数组；
                        $files[] = $file;
                        Common::syncToCdn($relativeDir."/".$file);
                    }
                }
            }
            closedir($handle);
            return $files;
        }
    }

	/**
	 * 获取不重复的随机值
	 * @return string
     */
	static public function getNonce(){
		$nonce = time() . rand(1, 1000000000);
		return md5($nonce);
	}

	static public function checkUrl($url){
		if(!preg_match('/http[s]?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',$url)){
			return false;
		}
		return true;
	}
}
