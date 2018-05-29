<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
class Util_Cdn {
	// cust_id,passwd,item_id,sourch_path,publish_path;
	private $userName;
	private $password;
	private $itemId;
	private $sourcePath;
	private $publishPath;
	private $publishXml;
	private $deleteXml;
	private $cdn_url;
	private $cdnPath;
	
	
	
	public function __construct($itemId,$sourcePath,$publishPath)//$item_id在publish时要传入相对UPLOAD_PATH的路径，因为会根据状态调用callback函数来进行文件删除。
	{
	    $cdnConfig = Common::getConfig('cdnConfig');
		$this->userName =$cdnConfig['cdn_username'];
		$this->password = $cdnConfig['cdn_pwd'];
		$this->publishXml = $cdnConfig['publish_xml'];
		$this->deleteXml = $cdnConfig['delete_xml'];
		$this->cdnPath = $cdnConfig['cdn_path'];
		$this->cdn_url = $cdnConfig['cdn_url'];
		$this->itemId = $itemId;
		$this->sourcePath = $sourcePath;
		$this->publishPath = $publishPath;
	}
	
	public function publish()
	{
		$xml = $this->publishXml;
		//CDN发布的xml格式  要先传入 cust_id,checkcode,report,item_id,sourch_path,publish_path,checkfile;
		$checkstr = $this->itemId.$this->userName."chinanetcenter".$this->password;
		$checkcode = md5($checkstr);
		$attachPath = Common::getConfig ( 'siteConfig', 'attachPath' );
		$localPath = $attachPath.$this->publishPath;
		$checkfile = md5_file($localPath);
		$reportUrl = Yaf_Application::app()->getConfig()->webroot."/Api/Callback/report";
		$new_xml =  sprintf($xml,$this->userName,$checkcode,$reportUrl,$this->itemId,$this->sourcePath,$this->publishPath, $checkfile);
		$url =$this->cdn_url.'?op=publish&context='.$new_xml;
		Util_Log::info('publicCdnFile', 'cdnPublic.log',array('请求url：', $url));
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_AUTOREFERER , TRUE);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch,CURLOPT_BINARYTRANSFER, TRUE);
		$output = curl_exec($ch);
		Util_Log::info('publicCdnFile', 'cdnPublic.log',array('返回结果：', $output));
		curl_close($ch);
		return $this->cdnPath.$this->publishPath;
	}
	
	public function delete()
	{
		$xml = $this->publishXml;
		$checkstr = $this->itemId.$this->userName."chinanetcenter".$this->password;
		$checkcode = md5($checkstr);
		$reportUrl = Yaf_Application::app()->getConfig()->webroot."/Api/Callback/report";
		$new_xml = sprintf($xml,$this->userName,$checkcode,$reportUrl,$this->itemId,$this->sourcePath,$this->publishPath);
		$url = $this->cdn_url.'?op=delete&context='.$new_xml;
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_AUTOREFERER , TRUE);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch,CURLOPT_BINARYTRANSFER, TRUE);
		$output = curl_exec($ch);
		curl_close($ch);
	}
	
	static public function report()
	{
		
		$context = isset($_REQUEST['context'])?$_REQUEST['context']:'';
		Util_Log::info('publicCdnFile', 'cdnPublic.log',array('回调参数：', $context));
		if(empty($context)){
			return false;
		}
		$result = '<?xml version="1.0" encoding="UTF-8" ?>'.
				'<ccsc>'.
				'<result>SUCCESS</result>'.
				'<detail>nothing</detail>'.
				'</ccsc>';
		echo $result;
		return false; //暂时不要删除服务器资源
		$xml = simplexml_load_string(stripslashes($context));
		$array = array();
		$xml = self::objectsIntoArray($xml);
		if(empty($xml)){
			return false;
		}
		$status = $xml['item_id']['op_status'];
		$op_name = $xml['item_id']['op_name'];
		$file_name = $xml['item_id']['@attributes']['value'];
		$attachPath = Common::getConfig('siteConfig', 'attachPath');
		$file = $attachPath.$file_name;
		if($op_name == "publish" && $status == "sync finish")
		{
			if(file_exists($file))
		 	unlink($file);;
		}
		$result = '<?xml version="1.0" encoding="UTF-8" ?>'.
                           '<ccsc>'.
                           '<result>SUCCESS</result>'.
                           '<detail>nothing</detail>'.
                           '</ccsc>';
		echo $result;
	}
	
	static public function objectsIntoArray($arrObjData, $arrSkipIndices = array())
	{
		$arrData = array();
		// if input is object, convert into array
		if (is_object($arrObjData)) {
			$arrObjData = get_object_vars($arrObjData);
		}
		if (is_array($arrObjData)) {
			foreach ($arrObjData as $index => $value) {
				if (is_object($value) || is_array($value)) {
					$value = self::objectsIntoArray($value, $arrSkipIndices); // recursive call
				}
				if (in_array($index, $arrSkipIndices)) {
					continue;
				}
				$arrData[$index] = $value;
			}
		}
		return $arrData;
	}
}