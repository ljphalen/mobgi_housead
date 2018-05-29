<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * RSA加密
 * the last known user to change this file in the repository
 * @author rock.luoe<rock.luoe@gmail.com>
 * @copyright Copyright &copy; 2003-2011 phpwind.com
 * @license 
 */
/**
 * 产生对称密钥
 *$res = openssl_pkey_new();
 *openssl_pkey_export($res,$pri);
 *$d= openssl_pkey_get_details($res);
 *$pub = $d['key'];
 *var_dump($pri,$pub);
 */
class Util_Rsa {
	public static $outAlg = 'base64_encode'; //bin2hex方式无法用本类中的verify方法校验,专用于UUID校验(对方是nodejs)
	public static $signAlg = 'SHA1'; //默认是SHA1
	
	/**
	 * 验证签名
	 * @param array $param
	 * @param string $sign
	 * @param string $key_file
	 * @return
	 */
	public static function verifySign($params, $sign, $key_file) {
		$params = self::para_filter($params);
		$params = self::arg_sort($params);
		
		$prestr = self::create_valuestr($params);
		return self::verify($prestr, $sign, $key_file);
	}
	
	/**
	 * 生成签名结果
	 * @param array $params
	 * @param string $key_file
	 * @return string
	 */
	public function build_mysign($params, $key_file) {
		$params = self::para_filter($params);
		$params = self::arg_sort($params);
		
		$prestr = self::create_valuestr($params);
		//调用RSA签名方法
		$mysgin = self::sign($prestr, $key_file);
		return $mysgin;
	}
	
	/********************************************************************************/
	
	/**
	 * RSA签名
	 * @param string $data
	 * @param string $key_file
	 * @return boolean|string
	 */
	public function sign($data, $key_file) {
		//读取私钥文件
		if (!is_file($key_file)) return false;
		$priKey = file_get_contents($key_file);
		
		$res = openssl_get_privatekey($priKey);
		//调用openssl内置签名方法，生成签名$sign
		openssl_sign($data, $sign, $res, self::$signAlg);
		//释放资源
		openssl_free_key($res);
		//base64编码
		if (self::$outAlg == 'bin2hex') {
			$sign = bin2hex($sign);
		} else {
			$sign = base64_encode($sign);
		}
		return $sign;
	}
	
	/********************************************************************************/
	
	/**
	 * RSA验签
	 * @param String $string 验证内容
	 * @param String $sign 签名
	 * @param String $key_file 密钥
	 * @return boolean
	 */
	public function verify($string, $sign, $key_file) {
		if (!is_file($key_file)) return false;
		$pubKey = file_get_contents($key_file);
		
		//转换为openssl格式密钥
		$res = openssl_get_publickey($pubKey);
		
		//调用openssl内置方法验签，返回bool值
		$sign = base64_decode($sign);
		$result = (bool) openssl_verify($string, $sign, $res, self::$signAlg);
		
		//释放资源
		openssl_free_key($res);
		
		//返回资源是否成功
		return $result;
	}
	
	/********************************************************************************/
	
	/**
	 * 加密
	 * @param string $content 加密的内容
	 * @param string $key_file 加密的密钥
	 * @return string 返回加密后的内容
	 */
	public function encrypt($content, $key_file) {
		if (!is_file($key_file)) return false;
		$priKey = file_get_contents($key_file);
		$res = openssl_get_privatekey($priKey);
		
		$result = '';
		$s = 117;
		$len = ceil(strlen($content) / $s);
		
		for ($i = 0; $i < $len; $i++) {
			$data = substr($content, $i * $s, $s);
			openssl_private_encrypt($data, $decrypt, $res);
			$result .= $decrypt;
		}
		openssl_free_key($res);
		$result = base64_encode($result);
		//返回明文
		return $result;
	}
	
	/********************************************************************************/
	
	/**
	 * 解密
	 * @param string $content 解密的内容
	 * @param string $key_file 解密的密钥
	 * @return 返回解密后的明文
	 */
	public function decrypt($content, $key_file) {
		if (!is_file($key_file)) return false;
		$priKey = file_get_contents($key_file);
		$res = openssl_get_publickey($priKey);
		
		$content = base64_decode($content);
		
		$result = '';
		for ($i = 0; $i < strlen($content) / 128; $i++) {
			$data = substr($content, $i * 128, 128);
			openssl_public_decrypt($data, $decrypt, $res);
			$result .= $decrypt;
		}
		openssl_free_key($res);
		return $result;
	}
	
	/********************************************************************************/
	
	/**
	 * 
	 * @param array $array
	 * @return string
	 */
	public function create_linkstring($array) {
		return http_build_query($array);
	}
	
	/**
	 * 
	 * @param array $array
	 * @return string
	 */
	public function create_valuestr($array) {
		return implode('', array_values($array));
	}
	
	/********************************************************************************/
	
	/**除去数组中的空值和签名参数
	 * $parameter 签名参数组
	 * return 去掉空值与签名参数后的新签名参数组
	 */
	public function para_filter($parameter) {
		$para = array();
		while (list($key,$val) = each($parameter)) {
			if ($key == "sign" || $key == "sign_type" || $val == "" || $key == "vip_level" || $key == "dev_email")
				continue;
			else
				$para[$key] = $parameter[$key];
		}
		return $para;
	}
	
	/********************************************************************************/
	
	/**对数组排序
	 * $array 排序前的数组
	 * return 排序后的数组
	 */
	public function arg_sort($array) {
		ksort($array);
		reset($array);
		return $array;
	}

}
