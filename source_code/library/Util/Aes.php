<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * AES加密解密算法
 * 配合 Java中AES使用AES/ECB/PKCS5Padding模式加密与解密使用
 */
class Util_Aes {
	private static $key = "GIONEE2012061900";
	
	private static function pkcs5Pad($text, $blocksize) {
		$pad = $blocksize - (strlen($text) % $blocksize); // in php, strlen returns the bytes of $text
		return $text . str_repeat(chr($pad), $pad);
	}
	
	public static function encryptText($str) {
		$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
		$input = self::pkcs5pad($str, $size);
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, self::$key, $iv);
		$data = mcrypt_generic($td, $input);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$data = base64_encode($data);
		$data = strtr($data, '+/', '-_');
		return $data;
	}
	
	public static function decryptText($str) {
		$str = strtr($str, '-_', '+/');
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
    	$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND); 
     	$decrypted= @mcrypt_decrypt(MCRYPT_RIJNDAEL_128, self::$key, base64_decode($str), MCRYPT_MODE_ECB, $iv);
    	$dec_s = strlen($decrypted);
    	$padding = ord($decrypted[$dec_s-1]);
    	$data = substr($decrypted, 0, -$padding);
    	return $data;
	}
}