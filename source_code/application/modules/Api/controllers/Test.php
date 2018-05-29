<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class TestController extends Api_BaseController {
    
    public function phpinfoAction() {
        phpinfo();
        exit;
    }
    private function isBidInfoUseList($platform, $version){
    	if($platform == Common_Service_Const::IOS_PLATFORM && version_compare ( $version, '2.2.0', '<=' )){
    		return false;
    	}
    	if($platform == Common_Service_Const::ANDRIOD_PLATFORM){
    		//视频1.0.0以下，或者插页的3.0.0使用对象
    		if( ($this->mAdType == Common_Service_Const::VIDEO_AD_SUB_TYPE) && version_compare ( $version, '1.0.0', '<=' ) ){
    			return false;
    		}
    		if(($this->mAdType == Common_Service_Const::PIC_AD_SUB_TYPE) && version_compare ( $version, '3.0.0', '<=' ) ){
    			return false;
    		}
    	}
    	return true;
    }
    
    function shorturl($input) {

        $base32 = array (
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
            'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
            'y', 'z', '0', '1', '2', '3', '4', '5'
        );
    
        $hex = md5($input);
        $hexLen = strlen($hex);
        $subHexLen = $hexLen / 8;
        $output = array();
    
        for ($i = 0; $i < $subHexLen; $i++) {
            $subHex = substr ($hex, $i * 8, 8);
            $int = 0x3FFFFFFF & (1 * ('0x'.$subHex));
            $out = '';
    
            for ($j = 0; $j < 6; $j++) {
                $val = 0x0000001F & $int;
                $out .= $base32[$val];
                $int = $int >> 5;
            }
    
            $output[] = $out;
        }
    
        return $output;
    }
    public function test2Action() {
		$inputJson = file_get_contents ( 'php://input' );
		print_r($inputJson);
        exit();
		
    	$test = array(1,2,3,4,5,6);
    	foreach ($test as $val){
    		$temp[] = array(
    				   'a'=>'1',
    				   'b'=>'2'
    		);
    	
    	}
 die;
 
 
    	
    	
        var_dump($this->getInput(array('test1', 'test2')) );


        $algorithms = mcrypt_list_algorithms();
        
        print_r($algorithms);
        $modes = mcrypt_list_modes();
        
        print_r($modes);
    }
    
    public function get_rand($proArr) {
    	$result = '';
    	//概率数组的总概率精度
    	$proSum = array_sum($proArr);
    	//概率数组循环
    	foreach ($proArr as $key => $proCur) {
    		$randNum = mt_rand(1, $proSum);             //抽取随机数
    		if ($randNum <= $proCur) {
    			$result = $key;                         //得出结果
    			break;
    		} else {
    			$proSum -= $proCur;
    		}
    	}
    	unset ($proArr);
    	return $result;
    }
    
    function get_rand2($arr)
    {
    	$pro_sum=array_sum($arr);
    	$rand_num=mt_rand(1,$pro_sum);
    	$tmp_num=0;
    	foreach($arr as $key=>$val)
    	{
    		if($rand_num<=$val+$tmp_num)
    		{
    			$n=$key;
    			break;
    		}else
    		{
    			$tmp_num+=$val;
    		}
    	}
    	return $n;
    }
    
    public function test3Action() {
    	echo 'test3';
    	exit;
    }
    
    public function test4Action() {
    	//sleep(10);
    	usleep(190000);
    	echo 'test4';
    	exit;
    }
    
    public function test5Action() {
    	echo 'test5';
    	exit;
    }
    
    /**
     *
     * curl_multi_*简单运用
     *
     * @author: rudy
     * @date: 2016/07/12
     */
    
    /**
     * 根据url,postData获取curl请求对象,这个比较简单,可以看官方文档
     */
    function getCurlObject($url,$postData=array(),$header=array()){
    	$options = array();
    	$url = trim($url);
    	$options[CURLOPT_URL] = $url;
    	//$options[CURLOPT_TIMEOUT] = 1;
    	$options[CURLOPT_TIMEOUT_MS] = 200; //注意，毫秒超时一定要设置这个 超时时间200毫秒
    	$options[CURLOPT_NOSIGNAL] = true;
    	$options[CURLOPT_USERAGENT] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36';
    	$options[CURLOPT_RETURNTRANSFER] = true;
    	//    $options[CURLOPT_PROXY] = '127.0.0.1:8888';
    	foreach($header as $key=>$value){
    		$options[$key] =$value;
    	}
    	if(!empty($postData) && is_array($postData)){
    		$options[CURLOPT_POST] = true;
    		$options[CURLOPT_POSTFIELDS] = http_build_query($postData);
    	}
    	if(stripos($url,'https') === 0){
    		$options[CURLOPT_SSL_VERIFYPEER] = false;
    	}
    	$ch = curl_init();
    	curl_setopt_array($ch,$options);
    	return $ch;
    }




	function scerweima($url=''){
		Yaf_loader::import("Util/PHPQRcode/QRcode.php");

		$value = $url;                  //二维码内容
		$errorCorrectionLevel = 'L';    //容错级别
		$matrixPointSize = 5;           //生成图片大小
		//生成二维码图片
		$filename = '/tmp/'.microtime().'.png';
		QRcode::png($value,false , $errorCorrectionLevel, $matrixPointSize, 2);
		$QR = $filename;                //已经生成的原始二维码图片文件
/*
		$QR = imagecreatefromstring($QR);

		//输出图片
		imagepng($QR, 'qrcode.png');
		imagedestroy($QR);*/
		return '<img src="qrcode.png" alt="使用微信扫描支付">';
	}

	public function createQrCode($value){
		if(Util_Environment::isDevelop()){
			return '';
		}
		$errorCorrectionLevel = 'L';    //容错级别
		$matrixPointSize = 5;           //生成图片大小
		return common::generateQRfromLocal($value,'L',5);
		/*Yaf_loader::import("Util/PHPQRcode/QRcode.php");
		//二维码内容
		$errorCorrectionLevel = 'L';    //容错级别
		$matrixPointSize = 5;           //生成图片大小
		ob_start();
		//生成二维码图片
		 QRcode::png($value,true , $errorCorrectionLevel, $matrixPointSize, 2,false);
		$imageString = base64_encode(ob_get_contents());
		ob_end_clean();
		return $imageString;*/
	}

	public function testAction() {


		Yaf_loader::import("Util/IdWorker.php");
		$idWorker = new IdWorker(1,1);
		for ($i=0;$i<1000000;$i++){
			echo  $idWorker->generateID();
			echo "<br/>";
		}
		die;

		$this->createQrCode('http://www.baidu.com');

		die;
		Yaf_loader::import("Util/PHPQRcode/QRcode.php");
		$value = 'http://www.baidu.com';       //二维码内容
		$errorCorrectionLevel = 'L';    //容错级别
		$matrixPointSize = 5;           //生成图片大小
		ob_start();
		//生成二维码图片
		QRcode::png($value,true , $errorCorrectionLevel, $matrixPointSize, 2,false);
		$imageString = base64_encode(ob_get_contents());
		ob_end_clean();
		//生成二维码图片
		//$data = QRcode::png($value,false , $errorCorrectionLevel, $matrixPointSize, 2);
		var_dump($imageString);

		echo '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAkCAYAAABIdFAMAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAHhJREFUeNo8zjsOxCAMBFB/KEAUFFR0Cbng3nQPw68ArZdAlOZppPFIBhH5EAB8b+Tlt9MYQ6i1BuqFaq1CKSVcxZ2Acs6406KUgpt5/LCKuVgz5BDCSb13ZO99ZOdcZGvt4mJjzMVKqcha68iIePB86GAiOv8CDADlIUQBs7MD3wAAAABJRU5ErkJggg=="/>';

		die;

		echo $this->scerweima('http://www.baidu.com');die;



		yaf_Session::getInstance()->start();
		Util_Cookie::set('test','123456',true,86400);
	 var_dump(session_id(),$_COOKIE,Util_Cookie::get('test', true),$_SERVER);

	 die;

		Yaf_loader::import("Util/IdWorker.php");
		$idWorker = new IdWorker(1,1);
		for ($i=0;$i<1000000;$i++){
			echo  $idWorker->generateID();
			echo "<br/>";
		}


		echo
		//echo Particle::timeFromParticle($particle);//反向计算时间戳

die;
		$url = 'http://localhost.api.mobgi.com/api/test/test3';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'ddddddddddddddddddddddd');

		$options [CURLOPT_USERAGENT] =  $this->mPostData['device']['ua'];// 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36';
		$response = curl_exec($ch); // 已经获取到内容，没有输出到页面上。
		var_dump(curl_getinfo($ch));
		curl_close($ch);
		echo $response;

		echo $this->scerweima('http://www.baidu.com');

		exit;

    	for ($i=0;$i< 600;$i++){
			$str = strtoupper(dechex(crc32(md5(uniqid(time() . mt_rand(1,1000000))))));
			if(strlen($str) == 8){
				echo $str;
				echo "<br>";
			}
		}

    	;die;

		$input = 'http://test.localhost.com/index/detail?id=245&intersrc=ranklist_upRankI1_gamedetail245&t_bi=_219269652';
		var_dump ( $this->shorturl ( $input ) );

		die ();

		Yaf_loader::import("Util/Foo.php");

		$foo = new Foo();
		$foo->setBar(1);
		$foo->setBaz('two');
		$foo->appendSpam(3.0);
		$foo->appendSpam(4.0);
		$packed = $foo->serializeToString();

		var_dump($packed);

		$data = '{"name":"ljp","sex":1}';
		$url = 'http://localhost.api.ha.mobgi.com/api/test/test2';
		// 初始化
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $packed);
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
		$output = curl_exec ( $ch );
		curl_close ( $ch );

		var_dump ( $output );

		die;
		Yaf_loader::import("Util/Foo.php");

		$foo = new Foo();
		$foo->setBar(1);
		$foo->setBaz('two');
		$foo->appendSpam(3.0);
		$foo->appendSpam(4.0);
		$packed = $foo->serializeToString();

		$parsedFoo = new Foo();
		try {
			$parsedFoo->parseFromString($packed);
		} catch (Exception $ex) {
			die('Oops.. there is a bug in this example, ' . $ex->getMessage());
		}
		var_dump($foo->getBar(),$packed,$parsedFoo);

		die;
        
        Dedelivery_Service_OriginalityRelationModel::getOriginalityChargePriceByRequestId ( 'gfsfgsgf', 8 );
     
            var_dump($a['aaaaaaaaaaaaa']);
         die; 
        $params['card_id'] = array();
        var_dump($data);
        $ret = Admin_Service_UserModel::getBy($params);
        
       var_dump($ret); 
        die;
        $cache = Cache_Factory::getCache ();
        var_dump($cache);
        $cache->set ( 'test1', 111111111, 3600 );
        
        die;
        
        $string = array(1=>'ssss');
        var_dump( Common::is_json($string));
        die;
        
        
        $x = (int)'01FF';
        var_dump($x);die;
        $text = 'John';
        $text[10] = 'Doe';
        var_dump($text);die;
        $x = true and false;
        var_dump($x = true, true and false , $x);die;
        $a = '1';
        $b = &$a;
        $b = "2$b";
    var_dump($b,$a);
    unset($b);
    var_dump($b,$a);
        die;

        
        $queue = Common::getQueue ();
        $queue->push ( 'test', array (
                'name' => 'tttt',
                'age' => 23
        ) );
        var_dump($queue);
        die;
        $cache = Cache_Factory::getCache ();
  var_dump($cache);      
        $cache->set ( 'test1', 111111111, 3600 );
        
        die;
        

        
        var_dump(867348026517816%100);
        
        for($i=1;$i<10000;$i++){
            $j = mt_rand(1,10);
            $arr[$j] = $arr[$j] +1;
        }
var_dump($arr);
        die;
        $arr = array('test1','test2','test2','test3');
        var_dump(Common::combination($arr,2));
        
        $content = 'dafdad1231231%中文 ';
        var_dump(preg_match('/^[0-9a-zA-Z\x{4e00}-\x{9fa5}%,_\-\.\+\/\@]*$/u', $content));
        die;
        
        $extra = $this->getInput('extra');
      echo $extra;die;
        header('HTTP/1.1 404 Not Found');
        exit();
        header('HTTP/1.1 401 Unauthorized');  
        header('WWW-Authenticate: Basic realm="Top Secret"');  
        print 'Text that will be displayed if the user hits cancel or ';  
        print 'enters wrong login data';  
        exit();
      	$queue = Common::getQueue ();
    	$queue->push ( 'test', array (
    			'name' => 'tttt',
    			'age' => 23
    	) );
    	$queue->push ( 'test', array (
    			'name' => 'tttt',
    			'age' => 23
    	) );
    	$queue->push ( 'test', array (
    			'name' => 'tttt',
    			'age' => 23
    	) );
    	$queue->push ( 'test', array (
    			'name' => 'tttt',
    			'age' => 23
    	) );
    	$queue->push ( 'test', array (
    			'name' => 'tttt',
    			'age' => 23
    	) );
    	// var_dump( $queue->pop('test'));
    	var_dump ( $queue );
    	DIE ();
    	die;
    	try {
    		$a = int(1);
    	} catch (Exception $e) {
    		throw new Exception();
    	}
     
    	die;
    	$queue = Common::getQueue ();
    	$queue->push ( 'test', array (
    			'name' => 'tttt',
    			'age' => 23
    	) );
    	$queue->push ( 'test', array (
    			'name' => 'tttt',
    			'age' => 23
    	) );
    	$queue->push ( 'test', array (
    			'name' => 'tttt',
    			'age' => 23
    	) );
    	$queue->push ( 'test', array (
    			'name' => 'tttt',
    			'age' => 23
    	) );
    	$queue->push ( 'test', array (
    			'name' => 'tttt',
    			'age' => 23
    	) );
    	// var_dump( $queue->pop('test'));
    	var_dump ( $queue );
    	DIE ();
    	
    	Util_Log::info(__CLASS__, 'test.log', 'dddddddddddddddddddddddddd');die;
    	
    	Admin_Service_UserModel::getAllUser();die;
    	
    	var_dump($this->getRequest()->getQuery());die;
    	
    	$a = 'test';
    	$test = 'ddddddddddddd';  
    	
/*     	$str = "Bill &amp; &#039;Steve&#039;";
    	echo html_entity_decode($str, ENT_COMPAT); // 只转换双引号
    	echo "<br>";
    	echo html_entity_decode($str, ENT_QUOTES); // 转换双引号和单引号
    	echo "<br>";
    	echo html_entity_decode($str, ENT_NOQUOTES); // 不转换任何引号
    	die;
    	 */
    	 $s1='{\x22extra\x22:{\x22adList\x22:\x22Dianview,Yumi,Yezi,Vungle,Domob,CentrixLink,Changxian,Uniplay,Unity,Oneway,Adview,YouDao,\x22,\x22sdkVersion\x22:\x221.0.0\x22,\x22isNewUser\x22:0},\x22device\x22:{\x22brand\x22:13,\x22model\x22:\x22Nexus6P\x22,\x22platform\x22:1,\x22version\x22:\x227.1.2\x22,\x22resolution\x22:\x221440*2392\x22,\x22operator\x22:1,\x22net\x22:1,\x22screenDirection\x22:2,\x22screenSize\x22:4.985714285714286,\x22deviceId\x22:\x22352584063075847\x22},\x22providerId\x22:\x221\x22,\x22isTest\x22:0,\x22imp\x22:[],\x22app\x22:{\x22appKey\x22:\x22e19081b4527963d70c7a\x22,\x22name\x22:\x22\xE7\xA3\xA8\xE5\x9F\xBA\xE5\xB9\xBF\xE5\x91\x8ADemo\x22,\x22bundle\x22:\x22com.kiloo.subwaysurf\x22,\x22version\x22:\x221.0\x22,\x22channelId\x22:\x22TEST0000000\x22},\x22user\x22:{\x22id\x22:\x2281s50r60r_344735675634213q09880p4\x22},\x22adType\x22:1}';
    	echo $s1;
    	 echo html_entity_decode($s1, ENT_NOQUOTES);die;
    	 
    	$result = Util_PHPMailer_SendMail::postEmail ( '369775049@qq.com', '标题', '内容ssssssssssssssssssssssssss' );
    	var_dump ( $result );
    	die ();
    	
    	$winDspNo = 'HouseAd';
    	var_dump($winDspNo, stripos($winDspNo, 'housead'), stripos($winDspNo, 'housead') === false);die;
			
			// 运行 file_exists 10000 次
		$time = microtime ();
		$time = explode ( ' ', $time );
		$begintime = $time [1] + $time [0];
		for($i = 0; $i < 100000; $i ++){
			file_exists (' /data/www/mobgi_housead/trunck/mobgi_housead/source_code/application/modules/Api/controllers/Test.php' ); // 文件不存在
		}
			
		$time = microtime ();
		$time = explode ( " ", $time );
		$endtime = $time [1] + $time [0];
		$totaltime = ($endtime - $begintime);
		echo '运行file_exists 10000 次所花时间： ' . $totaltime . ' 秒' . PHP_EOL;
		
		// 运行 is_file 10000 次
		$time = microtime ();
		$time = explode ( " ", $time );
		$begintime = $time [1] + $time [0];
		for($i = 0; $i < 100000; $i ++){
			is_file ( '/data/www/mobgi_housead/trunck/mobgi_housead/source_code/application/modules/Api/controllers/Test.php' );
		}
			
		$time = microtime ();
		$time = explode ( " ", $time );
		$endtime = $time [1] + $time [0];
		$totaltime = ($endtime - $begintime);
		echo '运行 is_file 10000 次所花时间： ' . $totaltime . ' 秒.' . PHP_EOL;
    			
    die;
    	
    	$path = new LimitIterator( new DirectoryIterator("/etc/"), 0,10);
    		foreach ( $path as $file ){
    			echo $file ."<br />";
    		}
    		die;
    	
      echo base64_encode('1,1491819105,AAA1BA84295843230353DCD4B71D47572D4F6C17');
      echo"<br />";
      echo sha1('11491819105');
      die;

    	$bearerTokenStr = $_SERVER['HTTP_AUTHORIZATION'];
    	$token = str_replace('Bearer ', '', $bearerTokenStr);
    	
    	if(empty($token)){
    		$this->output(30100, 'token is not exist ');
    	}
    	
    	$string = base64_decode($token);
    	list($providerId, $time_stamp, $sign) = explode(',', $string);
    	$tokenExpireTime = Common::getConfig("adxConfig", "token_expire_time");
    	if( ($providerId != 1) && (time() - $time_stamp > $tokenExpireTime)){
    		$this->output(30102, 'expired token ');
    	}
    	//校验token
    	$checkSign = sha1($providerId.$time_stamp);
    	if(strtolower($checkSign) != strtolower($sign)){
    		$this->output(30101, 'wrong token');
    	}
    
    	

    	
		$urlList = array (
				'http://localhost.api.ha.mobgi.com/api/test/test5',
				'http://localhost.api.ha.mobgi.com/api/test/test4',
				'http://localhost.api.ha.mobgi.com/api/test/test3' 
		);
		
		$handles = $contents = array ();
		// 初始化curl multi对象
		$mh = curl_multi_init ();
		// 添加curl 批处理会话
		foreach ( $urlList as $key => $url ) {
			$handles [$key] = $this->getCurlObject ( $url );
			curl_multi_add_handle ( $mh, $handles [$key] );
		}
		
	
		$active = null;
		do {
			$mrc = curl_multi_exec ( $mh, $active );
		} while ( $mrc == CURLM_CALL_MULTI_PERFORM );
		
		while ( $active &&  $mrc == CURLM_OK ) {
			if (curl_multi_select ( $mh ) === - 1) {
				usleep ( 100 );
			}
			do {
				$mrc = curl_multi_exec ( $mh, $active );
			} while ( $mrc == CURLM_CALL_MULTI_PERFORM );
		}
		// 获取批处理内容
		foreach ( $handles as $i => $ch ) {
			$content = curl_multi_getcontent ( $ch );
			$contents [$i] = curl_errno ( $ch ) == 0 ? $content : 'null';
		}
		// 移除批处理句柄
		foreach ( $handles as $ch ) {
			curl_multi_remove_handle ( $mh, $ch );
		}
		// 关闭批处理句柄
		curl_multi_close ( $mh );
		var_dump ( $contents );
    	die;
    	
    	
    	// 创建三个待请求的url对象
    	$chList = array();
    	$chList[] = $this->getCurlObject('http://localhost.api.ha.mobgi.com/api/test/test5');
    	$chList[] = $this->getCurlObject('http://localhost.api.ha.mobgi.com/api/test/test4');
    	$chList[] = $this->getCurlObject('http://localhost.api.ha.mobgi.com/api/test/test3');
    	
    	// 创建多请求执行对象
    	$downloader = curl_multi_init();
    	
    	// 将三个待请求对象放入下载器中
    	foreach ($chList as $ch){
    		curl_multi_add_handle($downloader,$ch);
    	}
    	
    	// 轮询
    	do {
    		while (($execrun = curl_multi_exec($downloader, $running)) == CURLM_CALL_MULTI_PERFORM) ;
    		if ($execrun != CURLM_OK) {
    			break;
    		}
    	
    		// 一旦有一个请求完成，找出来，处理,因为curl底层是select，所以最大受限于1024
    		while ($done = curl_multi_info_read($downloader))
    		{
    			// 从请求中获取信息、内容、错误
    			$info = curl_getinfo($done['handle']);
    			$output = curl_multi_getcontent($done['handle']);
    			$error = curl_error($done['handle']);
    	
    			// 将请求结果保存,我这里是打印出来
    			var_dump($done, $running);
    			//        print "一个请求下载完成!\n";
    	
    			// 把请求已经完成了得 curl handle 删除
    			curl_multi_remove_handle($downloader, $done['handle']);
    		}
    	
    		// 当没有数据的时候进行堵塞，把 CPU 使用权交出来，避免上面 do 死循环空跑数据导致 CPU 100%
    		if ($running) {
    			$rel = curl_multi_select($downloader, 1);
    			if($rel == -1){
    				usleep(1000);
    			}
    		}
    	
    		if( $running == false){
    			break;
    		}
    	} while (true);
    	
    
		
		die;
		
    	
    	$clientId = 'test';
    	$timeStamp = '1488267410';
    	$sign =md5('123');
    	$str = $clientId.','. $timeStamp .','.$sign;
    	$token = base64_encode($str);
    	$token2 = base64_decode($token);
    	
    	var_dump($sign,$token,$token2);die;
    	
		$prize_arr = array (
				'a' => 0.1,
				'b' => 0.1,
				'c' => 0.3,
				'e' => 0.5,
				'd' => 0 
		);
		foreach ( $prize_arr as $key => $weight ) {
			$adWeightList [$key] = $weight * 20000;
		}
		$randTotal = array_sum ( $adWeightList );
		for($i = 0; $i < $randTotal; $i ++) {
			$tmp [] = $this->get_rand ( $adWeightList );
		}
		$values = array_count_values ( $tmp );
		foreach ( $values as $key => $val ) {
			echo PHP_EOL . $key . ':' . (($val / $randTotal) * 100) . '%';
		}
		die ();
		
		echo ini_get ( 'session.gc_maxlifetime' );
		echo ini_get ( 'session.gc_maxlifetime' );
		die ();
		
		Yaf_loader::import ( "Util/FFMpeg/FFMpeg.php" );
		// $test = new \FFMpeg();
		$ffmpeg = Sharapov\FFMpegExtensions\FFMpeg::create ( array (
				'ffmpeg.binaries' => '/usr/local/ffmpeg/bin/ffmpeg', // Path to FFMpeg
				'ffprobe.binaries' => '/usr/local/ffmpeg/bin/ffprobe', // Path to FFProbe
				'timeout' => 3600, // The timeout for the underlying process
				'ffmpeg.threads' => 12 
		) ) // The number of threads that FFMpeg should use
;
		$video = $ffmpeg->open ( '/home/ljp/Downloads/test.mp4' );
		
		var_dump ( $video );
		die ();
		
		$prize_arr = array (
				'a' => 10,
				'b' => 10,
				'c' => 80 
		);
		for($i = 0; $i < 500; $i ++) {
			var_dump ( $this->get_rand ( $prize_arr ) );
		}
		die ();
		
		// Client
		$ch = curl_init ( 'http://localhost/test/test_timeout.php' );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_NOSIGNAL, 1 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT_MS, 100 );
		$data = curl_exec ( $ch );
		$curl_errno = curl_errno ( $ch );
		$curl_error = curl_error ( $ch );
		curl_close ( $ch );
		if ($curl_errno > 0) {
			echo "cURL Error ($curl_errno): $curl_error\n";
		} else {
			echo "Data received: $data\n";
		}
		
		exit ();
		
		$a = 2;
		$b = 1;
		$b = &$a;
		// $b = "2$b";
		
		unset ( $b );
		
		var_dump ( $a, $b );
		die ();
		
		$url = 'http://test-api-ha.mobgi.com/api/adconfig/getAdList?blockId=MC43ODAwODQwMCAxNDYxNjc-NTExMGJl&sp=1_1_1_2.4_model_1.0_4.0_320*480_1_3_uuid&appKey=5110be2586e884a9bc61&adType=1';
		/*
		 * $ch = curl_init ();
		 * curl_setopt ( $ch, CURLOPT_URL, $url );
		 * curl_setopt ( $ch, CURLOPT_HEADER, 0); // 设置显示返回的http头
		 * curl_setopt ( $ch, CURLOPT_TIMEOUT, 30 );
		 * curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		 * $output = curl_exec($ch);
		 * // curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1); //是否抓取跳转后的页面
		 * //$result = self::curlRedirExec ( $ch );
		 * curl_close ( $ch ); // close cURL handler
		 * var_dump($output);die;
		 */
		
		$result = Util_Http::get ( $url, array (
				'ddd' => 'dddddd' 
		) );
		$rs_list = json_decode ( $result->data, true );
		var_dump ( $result->data );
		exit ();
		
		var_dump ( $this->getInput ( 'test' ) );
		
		phpinfo ();
		die ();
		
		$cache = Cache_Factory::getCache ();
		
		$cache->set ( 'test1', 111111111, 3600 );
		var_dump ( $cache->get ( 'test1' ) );
		
		die ();
		
		header ( "Content-type: image/png" );
		imagepng ( $image );
		
		var_dump ( 'dddddddddddddddd' );
		
		var_dump ( Dedelivery_Service_UnitConfModel::getAll () );
		
		exit ();
		// build the individual requests as above, but do not execute them
		$ch_1 = curl_init ( 'http://www.baidu.com/' );
		$ch_2 = curl_init ( 'http://www.baidu.com/' );
		$ch_3 = curl_init ( 'http://www.baidu.com/' );
		$ch_4 = curl_init ( 'http://www.baidu.com/' );
		
		curl_setopt ( $ch_1, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch_2, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch_3, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch_4, CURLOPT_RETURNTRANSFER, true );
		
		// build the multi-curl handle, adding both $ch
		$mh = curl_multi_init ();
		curl_multi_add_handle ( $mh, $ch_1 );
		curl_multi_add_handle ( $mh, $ch_2 );
		
		// execute all queries simultaneously, and continue when all are complete
		$running = null;
		do {
			curl_multi_exec ( $mh, $running );
			$ch = curl_multi_select ( $mh );
			var_dump ( $ch );
			if ($ch !== 0) {
				$info = curl_multi_info_read ( $mh );
				if ($info) {
					var_dump ( $info );
					$response_1 = curl_multi_getcontent ( $info ['handle'] );
					echo "$response_1 \n";
					break;
				}
			}
		} while ( $running > 0 );
		
		// close the handles
		curl_multi_remove_handle ( $mh, $ch_1 );
		curl_multi_remove_handle ( $mh, $ch_2 );
		curl_multi_close ( $mh );
		die ();
		
		$queue = Common::getQueue ();
		$queue->push ( 'test', array (
				'name' => 'tttt',
				'age' => 23 
		) );
		// var_dump( $queue->pop('test'));
		var_dump ( $queue );
		DIE ();
		
		$file = '/data/www/mobgi_backend/branches/mobgi_backend_rock/source_code/app/misc/ledou/upload/apk/147677918234.apk';
		Apk_Service_Aapt::info ( $file );
		die ();
		
		$result = Util_PHPMailer_SendMail::postEmail ( '369775049@qq.com', '标题', '内容' );
		var_dump ( $result );
		die ();
		
		// item_id,sourch_path,publish_path;
		$filepath = '/delivery/201610/5800401d342c5.jpg';
		
		$ret = Common::syncToCdn ( $filepath );
		
		var_dump ( $ret );
		
		die ();
		
		$config = Common::getConfig ( 'smtpConfig' );
		
		var_dump ( $config );
		
		var_dump ( Common::getAttachPath () );
		die ();
		
		// item_id,sourch_path,publish_path;
		$filepath = '/delivery/201610/57f8d33fb5ded.png';
		$item_id = $filepath;
		$source_path = Common::getAttachPath () . $filepath;
		$publish_path = $filepath;
		
		$CDN = new Util_Cdn ( $item_id, $source_path, $publish_path );
		$path = $CDN->publish ();
		
		var_dump ( $path );
		
		die ();
		$test = Common::getConfig ( 'cdnConfig' );
		
		var_dump ( $test );
		
		die ();
		if (strstr ( $HTTP_SERVER_VARS [HTTP_USER_AGENT], "Mozilla/5.0" )) // 支持特殊字符"/"和中文字符
{
			echo 'strstr';
		}
		if (strpos ( $HTTP_SERVER_VARS [HTTP_USER_AGENT], "Mozilla/5.0" )) // 对"/"和中文字符不支持
{
			
			echo 'strpos';
		}
		die ();
		$ret = Dedelivery_Service_OriginalityRelationModel::getOriginalityChargePriceByRequestId ( 'gfsfgsgf', 8 );
		var_dump ( $ret );
		die ();
		$len = Common::strLength ( '中文测试中文测试中中文测试中文测试中文测试中文测试中文测试中文测试文测试中文测试中文测试中文测试中文' );
		
		var_dump ( $len );
		die ();
		var_dump ( exp ( - 50 ) );
		die ();
		
		$data = file_get_contents ( 'php://input' );
		$this->output ( 0, 'report ok' );
		
		$tmp [0] = array (
				'blockId' => 'blockId',
				'appKey' => 'dddddddddd' 
		);
		$tmp [1] = array (
				'blockId' => 'blockId',
				'appKey' => 'dddddddddd' 
		);
		$data ['list'] = $tmp;
		$this->output ( 0, 'ok', $data );
		
		$queue = Common::getQueue ( 'default' );
		$queue->push ( 'test', 'tttttttttttttttttt' );
		die ();
		
		$result = Dedelivery_Service_UnitConfModel::getBy ( array (
				'id' => array (
						'>',
						1 
				) 
		) );
		
		var_dump ( $result );
		die ();
		
		$res = openssl_pkey_new ();
		openssl_pkey_export ( $res, $pri );
		$d = openssl_pkey_get_details ( $res );
		$pub = $d ['key'];
		
		echo $pri;
		echo $pub;
		var_dump ( $pri, $pub );
		die ();
		
		$this->output ( 0 );
		
		foreach ( $arr as $val ) {
			echo $val;
		}
		die ();
		header ( "Content-type:text/html;charset=utf-8" );
		$file = "/tmp/中文名.tar.gz";
		
		$filename = basename ( $file );
		
		var_dump ( $filename );
		die ();
		
		header ( "Content-type: application/octet-stream" );
		
		// 处理中文文件名
		$ua = $_SERVER ["HTTP_USER_AGENT"];
		var_dump ( $ua );
		die ();
		$encoded_filename = rawurlencode ( $filename );
		if (preg_match ( "/MSIE/", $ua )) {
			header ( 'Content-Disposition: attachment; filename="' . $encoded_filename . '"' );
		} else if (preg_match ( "/Firefox/", $ua )) {
			header ( "Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"' );
		} else {
			header ( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		}
		
		header ( "Content-Length: " . filesize ( $file ) );
		readfile ( $file );
		
		exit ();
		header ( "Content-type:text/html;charset=utf-8" );
		
		var_dump ( memory_get_usage (), memory_get_peak_usage () );
		die ();
		
		/*
		 * $queue = Common::getQueue();
		 * $queue->push('tet', 111111111);die;
		 */
		
		$ret = Account_Service_UserModel::getsByUser ( array (
				'id' => 6 
		) );
		// $ret = Common::getService('Account_Service_User_Model')->getsByUser(array('id'=>6));
		var_dump ( $ret );
		$cache = Cache_Factory::getCache ( Cache_Factory::ID_LOCAL_APCU );
		// $cache->set('ttttttttt', 'ssssss', 3600);die;
		$cache->get ( 'test1' );
		die ();
		for($i = 10000; $i <= 20000; $i ++) {
			// $cache->set('test'.$i, array('key1'=>11, 'key2'=>11, 'key3'=>11,'key4'=>11), 3600);
			var_dump ( $cache->get ( 'test' . $i ), $cache->get ( 'test' . $i ) );
		}
		
		// $cache->set('test3', 'ssssssssdddddddd', 3600);
		// var_dump($cache->get('test1') , $cache->get('test2'), $cache->get('test3'), $cache->get('test4'),$cache->get('test5'));
		
		die ();
		// var_dump($this->getInput('test'));
		$module = $this->getRequest ()->getModuleName ();
		$controller = $this->getRequest ()->getControllerName ();
		$action_name = $this->getRequest ()->getActionName ();
		var_dump ( $this->getInput ( array (
				'test1',
				'test2' 
		) ) );
		$ret = Account_Service_UserModel::getsByUser ( array (
				'id' => 6 
		) );
		// $ret = Common::getService('Account_Service_User_Model')->getsByUser(array('id'=>6));
		var_dump ( $ret );
		
		// $this->set('test', 2222222222222222222);
		$session = Yaf_Session::getInstance ();
		$session->test4 = 'tttttttttttttttttt';
		var_dump ( $session->key () );
		die ();
		
		// Yaf_Loader::setLibraryPath('/data/www/test/application/models');
		// var_dump($_SERVER['DOCUMENT_ROOT'], strrpos($_SERVER['DOCUMENT_ROOT'],'/')+1, strtolower(substr($_SERVER['DOCUMENT_ROOT'], strrpos($_SERVER['DOCUMENT_ROOT'],'/')+1)));
		// die;
		
		$ret = Account_Service_UserModel::getsByUser ( array (
				'id' => 6 
		) );
		// $ret = Common::getService('Account_Service_User_Model')->getsByUser(array('id'=>6));
		var_dump ( $ret );
		
		die ();
		$yes = strtotime ( '-1 day' );
		$test = gmdate ( "Y-m-dTH:i:s", $yes );
		var_dump ( $test );
		$url = 'http://gameads-admin.applifier.com/stats/monetization-api?apikey=1c12d204f9cbe5feceb9b4ef99c980eed8a38fadbce869d854ad27333a955fcc&splitBy=source&start=2016-03-14GMT00:00:00.000Z&end=2016-03-15GMT23:59:59.000Z&scale=hour';
		// $url = 'https://ssl.vungle.com/api/applications/5632dd452b2c276d23000020?key=ace296d6c40853ea17857939cbf85f68&date=2016-03-10';
		// 初始化
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 0 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		// curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		// curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec ( $ch );
		curl_close ( $ch );
		
		var_dump ( $output );
		
		/*
		 * $yes = strtotime('-1 day');
		 * $test = gmdate("Y-m-dTH:i:s", $yes);
		 * var_dump($test);
		 */
		$url = 'http://gameads-admin.applifier.com/stats/monetization-api?apikey=1c12d204f9cbe5feceb9b4ef99c980eed8a38fadbce869d854ad27333a955fcc&splitBy=source&start=2016-03-14GMT00:00:00.000Z&end=2016-03-15GMT23:59:59.000Z&scale=hour';
		// $url = 'https://ssl.vungle.com/api/applications/5632dd452b2c276d23000020?key=ace296d6c40853ea17857939cbf85f68&date=2016-03-10';
		// $data['key'] = '568a3445260ad9a223000011';
		// $json_data = json_encode($data);
		
		// $result = Util_Http::post($url,$data, array('Content-Type' => 'application/json'));
		// post到支付服务器
		$result = Util_Http::get ( $url );
		$rs_list = json_decode ( $result->data, true );
		var_dump ( $result->data, $rs_list );
		exit ();
		
		$cache = Cache_Factory::getCache ();
		$cache->set ( 'test', 'ddddddddddd' );
		var_dump ( $cache->get ( 'test' ) );
		die ();
		
		$ret = Account_Service_User::getsByUser ( array (
				'id' => 6 
		) );
		var_dump ( $ret );
		die ();
		
		$input = 'http://test.localhost.com/index/detail?id=245&intersrc=ranklist_upRankI1_gamedetail245&t_bi=_219269652';
		var_dump ( $this->shorturl ( $input ) );
		
		die ();
		
		list ( $ret, $info ) = $this->video_info ( '/home/ljp/Downloads/test.mp4' );
		
		print_r ( $info );
		var_dump ( $ret );
		die ();
		
		// var_dump( Yaf_Registry::get("config"), Yaf_Registry::get('BIAdapter'));
		// $ret = Account_Service_User::getsByUser(array('id'=>6));
		
		// $time = strtotime('-1 Day');
		
		$seasonEndDay = array (
				'04-01',
				'07-01',
				'10-01',
				'01-01' 
		);
		$currentDay = date ( 'm-d' );
		if (in_array ( $currentDay, $seasonEndDay )) {
			echo $currentDay;
		}
		// $currentTime = date('m-d');
		// var_dump($currentTime);
		die ();
		
		$seasonTimeRange = Common::getSeasonTimeRange ();
		
		// $seasonTimeRange['endTime'] = '2015-05-06';
		
		// 每个季度的最后一天
		$seasonEndDay = date ( 'Y-m-d', strtotime ( $seasonTimeRange ['endTime'] ) );
		
		var_dump ( $currentTime, $seasonEndDay );
		// var_dump($this->loginDate(3));
	}
    
   public function video_info($file) {   
       
       $redis = $this->getCache();
       $test = $redis->get('test');
       var_dump($test);die;
       
       
       $ret = Account_Service_User::getsByUser(array('id'=>6));
       var_dump($ret);
       
       die;
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
       if (preg_match("/Video: (.*?), (.*?), (.*?)[,\s]/", $info, $match)) {
                $ret['vcodec'] = $match[1]; // 编码格式
                $ret['vformat'] = $match[2]; // 视频格式
                $ret['resolution'] = $match[3]; // 分辨率
                $a = explode('x', $match[3]);
                $ret['width'] = $a[0];
                $ret['height'] = $a[1];
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
     * 算出用户的登录日期显示
     */
    static public function loginDate($days = 1 ,$cycle = 7 ){
    	$tmp = array();
    	$z = 0;
    	for ($i=1; $i <$days+1; $i++){
    		$tmp[$i] = date('Y-m-d',strtotime($z.' day'));
    		$z--;
    	}
    	$j = 0;
    	for($i = $days+1; $i <= $cycle; $i++ ){
    		$j++;
    		$tmp[$i] = date('Y-m-d',strtotime('+'.$j.' day'));
    
    	}
    	sort($tmp);
    	return $tmp ;
    }
    
    function input_csv($handle) {

    	$out = array ();
    	$n = 0;
    	while ($data = fgetcsv($handle, 10000)) {
    		$num = count($data);
    		for ($i = 0; $i < $num; $i++) {
    			$out[$n][$i] = $data[$i];
    		}
    		$n++;
    	}
    	return $out;
    }
    
    public function getClientIDAction() {
    
    	$file = fopen("/home/ljp/www/test.csv","r");
    	$data = $this->input_csv($file);
    	fclose($file);
    
    	header("Content-type:text/html;charset=utf-8");
    
    	foreach ($data as $key=>$val){
    		$uname = trim($val[0]);
    		$imei  = trim($val[1]);
    		$uuid  = trim($val[2]);
    		$clientVersion = '1.6.0.a';
    			
    		$keyParam = array(
    				'apiName' => strtoupper('grab'),
    				'imei' => $imei,
    				'uname' => $uname,
    		);
    		$ivParam = $uuid;
    		$serverIdParam = array(
    				'clientVersion' => $clientVersion,
    				'imei' => $imei,
    				'uname' => $uname,
    		);
    			
    		$imeiDecrypt = Util_Imei::decryptImei(trim($val[1]));
    		$clientID = Common::encryptClientData(trim($val[2]), trim($val[0]));
    		$serverID = strtoupper($this->decryptServerId($keyParam, $ivParam, $clientVersion));
    		$sp = "E6mini_1.6.0.a_4.2.2_Android4.2.1_720*1280_I01000_wifi_".$imei;
    		echo  'uname='.$uname.',puuid='.$uuid.',imei='.$imei.',clientID='.strtoupper(md5($clientID)).',serverID='.$serverID.',sp='.$sp."<br />";
    	}
    
    
    }
    
    
    private function decryptServerId($keyParam, $ivParam, $clientVersion) {
    	$apiName = strtoupper($keyParam['apiName']);
    	$imei = $keyParam['imei'];
    	$uname = $keyParam['uname'];
    
    	$key = md5($apiName . $imei . $uname);
    	$key = substr($key, 0, 16);
    
    	$iv = md5($ivParam);
    	$iv = substr($iv, 0, 16);
    
    	$serverId = $clientVersion . '_' . $imei . '_' . $uname;
    
    	$cryptAES = new Util_CryptAES();
    	$cryptAES->setIv($iv);
    	$cryptAES->setKey($key);
    	return $cryptAES->encrypt($serverId);
    }
    
    
    
  
}