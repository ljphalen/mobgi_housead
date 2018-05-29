<?php
/*
* create on 2015-09-07
* Author: nobo.zhou
* 数据提取接口测试类
*/
class Util_DataExtract {
	//用户名
	public $user;
	//密码
	public $pwd;
	//DataExtract地址
	public $host = 'http://edata.idreamsky.com';
	//cookie缓存地址
	public $cookiePath = './';
	//请求过期时间
	public $timeOut = 0;
	private $tempFile;
	private $loginResponse;
	private $errorMsg;
	private $statusCode = 9999;
	/*
	* 构造函数里不建议抛出异常
	*/
	function __construct( $setting ){
		$this->user	= $setting['user'];
		$this->pwd	= $setting['pwd'];
		if( isset($setting['host']) ) {
			$this->host	= $setting['host'];
		}
		if( isset($setting['cookiePath']) ) {
			$this->cookiePath	= $setting['cookiePath'];
		}
		if( isset($setting['timeOut']) ) {
			$this->timeOut	= $setting['timeOut'];
		}
	}
	function __destruct(){
		unlink($this->tempFile);
	}
	function login(){
		$curl = curl_init();
		$this->tempFile = tempnam($this->cookiePath, 'cookie');
		curl_setopt($curl, CURLOPT_URL, $this->host.'/simpleLogin');
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array(
			'username' => $this->user,
			'password' => $this->pwd,
		), '', '&'));
		// 把返回来的cookie信息保存在文件中
		curl_setopt($curl, CURLOPT_COOKIEJAR, $this->tempFile);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//设定返回 的数据是否自动显示
		curl_setopt($curl, CURLOPT_HEADER, false);//设定是否显示头信 息
		curl_setopt($curl, CURLOPT_NOBODY, false);//设定是否输出页面 内容
		$data = curl_exec($curl);//返回结果
		 
		$state_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl); //关闭
		//只要状态不是200 都返回false
		if( $state_code != '200' ) {
			$this->loginResponse['status'] = $state_code;
			$this->statusCode = $state_code;
			$this->errorMsg = '登录失败:'.$state_code;
			$this->error($this->errorMsg);
		}
		
		//判断是否登录成功
		$response = json_decode($data, true);
		$this->loginResponse = $response;
		$this->statusCode = $response['status'];
		$this->errorMsg = isset($response['message']) ? $response['message'] : '';
	}
	protected function error($msg = '未知错误') {
		throw new Exception($msg);	
	}
	function reLogin() {
		$this->getToken();
	}
	function __toString(){
		return $this->loginResponse['status'] == 0 ? '0' : '-1';	
	}
	function getError(){
		return $this->errorMsg;	
	}
	function getStatusCoce(){
		return $this->statusCode;	
	}
	/*
	* 请求接口 提取平台接口都是返回json的数据格式，请求失败或错误都会返回false eg:
	* $content = $obj->request('/user/app/query', array( 'pageSize'=>10, pageNumber:1 ));
	* if( $content === false ) {  }
	*/
	function request( $url, $data = array(), $method='GET', $download = false ){
		if( !$download ) {
			$url = $method == 'GET' ? 
				empty($data) ? $this->host.$url : $this->host.$url.'?'.http_build_query($data) : 
				$this->host.$url;
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);//登陆后要从哪个页面获取信息
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeOut); 
		if( $method == 'POST' ) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
		}
		curl_setopt($curl, CURLOPT_COOKIEFILE, $this->tempFile);
		$content = curl_exec($curl);
		//检查状态吗
		$state_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$curl_errno = curl_errno($curl);
		//只要状态不是200 都返回false
		if( $state_code != '200' || $curl_errno > 0 ) {
			if( $curl_errno > 0 ) {
				$this->errorMsg = 'CURL请求错误:'.$curl_errno;
				$this->error($this->errorMsg);
			}
			$this->errorMsg = '请求失败:'.$state_code;
			$this->error($this->errorMsg);
		}
		
		if( $download ) {
			return $content;	
		}
		curl_close($curl); //关闭
		//如果接口正确都会返回一个json格式的数据
		$response = json_decode($content, true);
		//如果status不等于0 请求也算失败
		$this->statusCode = $response['status'];
		if( $response['status'] != 0 ) {
			$this->errorMsg =  isset($response['message']) ? $response['message'] : '';
			$this->error($this->errorMsg);
		}
		return $response['data'];
	}
	function get($url, $data = array()){
		return $this->request($url, $data, 'GET');
	}
	function post($url, $data = array()){
		return $this->request($url, $data, 'POST');
	}
	/*
	* 获取下载的内容
	*/
	function download($url, $data = array()){
		return $this->request($url, $data, 'GET', true);
	}
}
?>