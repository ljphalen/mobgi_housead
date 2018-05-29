<?php
if (! defined ( 'BASE_PATH' )) exit ( 'Access Denied!' );
/**
 *
 *
 * Enter description here ...
 * 
 * @author rock.luo
 *        
 */
class Util_Http {
	/**
	 * Enter description here ...
	 */
	static public function getClientIp() {
		return self::_getClientIp ();
	}
	
	/**
	 * Enter description here ...
	 */
	static private function _getClientIp() {
		$_clientIp = "0.0.0.0";
		if (($ip = self::getServer ( 'HTTP_CLIENT_IP' )) != null) {
			$_clientIp = $ip;
		} elseif (($_ip = self::getServer ( 'HTTP_X_FORWARDED_FOR' )) != null) {
			$ip = strtok ( $_ip, ',' );
			do {
				$ip = ip2long ( $ip );
				if (! (($ip == 0) || ($ip == 0xFFFFFFFF) || ($ip == 0x7F000001) || (($ip >= 0x0A000000) && ($ip <= 0x0AFFFFFF)) || (($ip >= 0xC0A8FFFF) && ($ip <= 0xC0A80000)) || (($ip >= 0xAC1FFFFF) && ($ip <= 0xAC100000)))) {
					$_clientIp = long2ip ( $ip );
					return;
				}
			} while ( ($ip = strtok ( ',' )) );
		} elseif (($ip = self::getServer ( 'HTTP_PROXY_USER' )) != null) {
			$_clientIp = $ip;
		} elseif (($ip = self::getServer ( 'REMOTE_ADDR' )) != null) {
			$_clientIp = $ip;
		} else {
			$_clientIp = "0.0.0.0";
		}
		return $_clientIp;
	}
	static public function getServer($name = null, $defaultValue = null) {
		if ($name === null)
			return $_SERVER;
		return (isset ( $_SERVER [$name] )) ? $_SERVER [$name] : $defaultValue;
	}
	
	/**
	 * 封装socket
	 * 
	 * @param string $out        	
	 * @param string $host        	
	 * @param int $port        	
	 * @param int $timeout        	
	 * @return boolean array(code, response_headers)
	 */
	private static function _sockopen($stream, $host, $port, $timeout = 10) {
		$response = new stdClass ();
		$response->state = - 1;
		$response->data = null;
		$response->headers = array ();
		
		if (function_exists ( 'fsockopen' )) {
			$_socket = @fsockopen ( $host, $port, $errno, $errstr, $timeout );
		} elseif (function_exists ( 'pfsockopen' )) {
			$_socket = @pfsockopen ( $host, $port, $errno, $errstr, $timeout );
		} else {
			list ( $errno, $errstr ) = array (
					- 1,
					'Socket function were forbidden' 
			);
			$_socket = null;
		}
		if (! is_resource ( $_socket )) {
			$response->errno = $errno;
			$response->errstr = $errstr;
			@fclose ( $_socket );
			return $response;
		}
		
		list ( $sec, $usec ) = explode ( '.', $timeout . '.' );
		stream_set_timeout ( $_socket, intval ( $sec ), intval ( $usec ) );
		
		@fwrite ( $_socket, $stream );
		
		$status = stream_get_meta_data ( $_socket );
		if ($status ['timed_out']) {
			list ( $response->errno, $response->errstr ) = array (
					- 1,
					'Connection timed out' 
			);
			@fclose ( $_socket );
			return $response;
		}
		
		$header = trim ( fgets ( $_socket ) );
		list ( $proto, $state, $message ) = explode ( ' ', $header, 3 );
		
		while ( ($header = trim ( fgets ( $_socket ) )) != '' ) {
			if (strpos ( $header, ':' ) === false)
				continue;
			list ( $key, $value ) = explode ( ':', $header, 2 );
			$response->headers [$key] = trim ( $value );
		}
		
		if (isset ( $response->headers ['Transfer-Encoding'] )) {
			$body = self::_getSocketBodyByTransferEncoding ( $_socket, $response->headers ['Transfer-Encoding'] );
		} else {
			$body = self::_getSocketBodyByNormal ( $_socket );
		}
		
		if (isset ( $response->headers ['Content-Encoding'] )) {
			self::_contentDecoding ( $body, $response->headers ['Content-Encoding'] );
		}
		
		@fclose ( $_socket );
		
		$response->proto = $proto;
		$response->state = intval ( $state );
		$response->message = $message;
		$response->data = $body;
		
		return $response;
	}
	private static function _getSocketBodyByTransferEncoding(&$_socket, $transferEncoding) {
		switch (strtolower ( $transferEncoding )) {
			case 'chunked' :
				return self::_getSocketBodyByChunked ( $_socket );
			default :
				return self::_getSocketBodyByNormal ( $_socket );
		}
	}
	private static function _contentDecoding(&$content, $contentEncoding) {
		switch (strtolower ( $contentEncoding )) {
			case 'gzip' :
				$content = self::_contentDecodingByGzip ( $content );
				break;
			default :
				break;
		}
	}
	private static function _contentDecodingByGzip(&$content) {
		if (function_exists ( 'gzdecode' ))
			return gzdecode ( $content );
		return file_get_contents ( 'compress.zlib://data:who/cares;base64,' . base64_encode ( $content ) );
	}
	private static function _getSocketBodyByChunked(&$_socket) {
		$body = '';
		while ( ! feof ( $_socket ) && ($chunkSize = ( int ) hexdec ( fgets ( $_socket ) )) ) {
			while ( $chunkSize > 0 ) {
				$temp = fread ( $_socket, $chunkSize );
				$body .= $temp;
				$chunkSize -= strlen ( $temp );
			}
			fread ( $_socket, 2 ); // skip \r\n
		}
		return $body;
	}
	private static function _getSocketBodyByNormal(&$_socket) {
		$stop = false;
		$limit = 0;
		$body = '';
		while ( ! feof ( $_socket ) && ! $stop ) {
			$data = fread ( $_socket, ($limit == 0 || $limit > 8192 ? 8192 : $limit) );
			$body .= $data;
			if ($limit) {
				$limit -= strlen ( $data );
				$stop = $limit <= 0;
			}
		}
		return $body;
	}
	private static function _addHeaders($headers = array()) {
		$defaultHeaders = array (
				'Connection' => 'close' 
		);
		
		$headers = is_array ( $headers ) ? array_merge ( $headers, $defaultHeaders ) : $defaultHeaders;
		$headerArray = array ();
		foreach ( $headers as $key => $value ) {
			$headerArray [] = sprintf ( "%s: %s", $key, $value );
		}
		return implode ( "\r\n", $headerArray );
	}
	public static function _get($host, $port, $query, $headers = array(), $timeout = 10, $type = 'GET') {
		$_host = str_replace ( 'ssl://', '', $host );
		$header = "${type} ${query} HTTP/1.1\r\n";
		$header .= "Host: ${_host}\r\n";
		$header .= self::_addHeaders ( $headers );
		$header .= "\r\n\r\n";
		
		return self::_sockopen ( $header, $host, $port, $timeout );
	}
	
	public static function _post($host, $port, $query, $body, $headers = array(), $timeout = 10, $type = 'POST') {
		$_host = str_replace ( 'ssl://', '', $host );
		$header = "${type} ${query} HTTP/1.1\r\n";
		$header .= "Host: ${_host}:${port}\r\n";
		$header .= "Content-Length: " . strlen ( $body ) . "\r\n";
		$header .= self::_addHeaders ( $headers );
		$header .= "\r\n\r\n";
		return self::_sockopen ( $header . $body, $host, $port, $timeout );
	}
	
	private static function _parse_url($url) {
		$parseUrl = parse_url ( $url );
		if (! $parseUrl || ! is_array ( $parseUrl ))
			return false;
		$parseUrl['port'] =  isset ( $parseUrl ['port'] ) ? $parseUrl ['port'] : (($parseUrl ['scheme'] === 'https') ? 443 : 80);
		$query = isset ( $parseUrl ['path'] ) ? $parseUrl ['path'] : '/';
		isset ( $parseUrl ['query'] ) && $query .= '?' . $parseUrl ['query'];
		isset ( $parseUrl ['fragment'] ) && $query .= '#' . $parseUrl ['fragment'];
		if ($parseUrl ['scheme'] === 'https') {
			$parseUrl ['host'] = 'ssl://' . $parseUrl ['host'];
		}
		return array (
				'host' => $parseUrl ['host'],
				'port' => $parseUrl ['port'],
				'query' => $query 
		);
	}
	
	/**
	 * Prepare post body according to encoding type
	 * 
	 * @param array $formvars        	
	 * @param array $formfiles        	
	 * @param array $submitType        	
	 * @return string
	 */
	public static function preparePostBody(array $formvars, array $formfiles = array()) {
		if (count ( $formvars ) == 0 && count ( $formfiles ) == 0)
			return array (
					'data' => '',
					'headers' => array () 
			);
		
		$postdata = '';
		$headers = array ();
		
		if (count ( $formfiles ) > 0) {
			$contentType = "multipart/form-data";
		} else {
			$contentType = "application/x-www-form-urlencoded";
		}
		
		switch ($contentType) {
			case "application/x-www-form-urlencoded" :
				reset ( $formvars );
				while ( list ( $key, $val ) = each ( $formvars ) ) {
					if (is_array ( $val ) || is_object ( $val )) {
						while ( list ( $cur_key, $cur_val ) = each ( $val ) ) {
							$postdata .= urlencode ( $key ) . "[]=" . urlencode ( $cur_val ) . "&";
						}
					} else {
						$postdata .= urlencode ( $key ) . "=" . urlencode ( $val ) . "&";
					}
				}
				$headers ['Content-Type'] = $contentType;
				break;
			case "multipart/form-data" :
				$mime_boundary = "Boundary" . md5 ( uniqid ( microtime () ) );
				reset ( $formvars );
				while ( list ( $key, $val ) = each ( $formvars ) ) {
					if (is_array ( $val ) || is_object ( $val )) {
						while ( list ( $cur_key, $cur_val ) = each ( $val ) ) {
							$postdata .= "--" . $mime_boundary . "\r\n";
							$postdata .= "Content-Disposition: form-data; name=\"$key\[\]\"\r\n";
							$postdata .= "Content-Type: text/plain; charset=utf-8\r\n\r\n";
							$postdata .= "$cur_val\r\n";
						}
					} else {
						$postdata .= "--" . $mime_boundary . "\r\n";
						$postdata .= "Content-Disposition: form-data; name=\"$key\"\r\n";
						$postdata .= "Content-Type: text/plain; charset=utf-8\r\n\r\n";
						$postdata .= "$val\r\n";
					}
				}
				
				reset ( $formfiles );
				while ( list ( $field_name, $file_names ) = each ( $formfiles ) ) {
					settype ( $file_names, "array" );
					while ( list ( , $file_name ) = each ( $file_names ) ) {
						if (! is_readable ( $file_name ))
							continue;
						
						$file_content = file_get_contents ( $file_name );
						$pathinfo = pathinfo ( $file_name );
						$base_name = isset ( $pathinfo ["basename"] ) ? $pathinfo ["basename"] : basename ( $file_name );
						$ext = isset ( $pathinfo ["extension"] ) ? $pathinfo ["extension"] : substr ( strrchr ( $base_name, "." ), 1 );
						$postdata .= "--" . $mime_boundary . "\r\n";
						$postdata .= "Content-Disposition: form-data; name=\"$field_name\"; filename=\"$base_name\"\r\n";
						$mimeType = self::getMimeType ( $ext );
						$postdata .= "Content-Type: $mimeType; Content-Transfer-Encoding: binary\r\n\r\n";
						$postdata .= "$file_content\r\n";
					}
				}
				$postdata .= "--" . $mime_boundary . "--\r\n";
				
				$headers ['Content-Type'] = "$contentType; boundary=" . $mime_boundary;
				break;
		}
		
		return array (
				'data' => $postdata,
				'headers' => $headers 
		);
	}
	
	public static function get($url, $headers = array(), $timeout = 10, $type = 'GET') {
		if (! $parseUrl = self::_parse_url ( $url ))
			return false;
		$response = self::_get ( $parseUrl ['host'], $parseUrl ['port'], $parseUrl ['query'], $headers, $timeout, $type );
		if (isset ( $response->headers ['Location'] )) {
			return self::get ( $response->headers ['Location'], $headers, $timeout, $type );
		}
		return $response;
	}
	
	public static function post($url, $data, $headers = array(), $timeout = 10, $type = 'POST') {
		if (! $parseUrl = self::_parse_url ( $url ))
			return false;
		$response = self::_post ( $parseUrl ['host'], $parseUrl ['port'], $parseUrl ['query'], $data, $headers, $timeout, $type );
		
		if (isset ( $response->headers ['Location'] )) {
			return self::post ( $response->headers ['Location'], $data, $headers, $timeout, $type );
		}
		return $response;
	}

	/**
	 * 金立账号1.5.0自动登陆接口代码专用 版本兼容用
	 *
	 */
	public static function post2($url, $data, $headers = array(), $timeout = 10, $type = 'POST') {
		if (! $parseUrl = self::_parse_url ( $url ))
			return false;
		$response = self::_post2 ( $parseUrl ['host'], $parseUrl ['port'], $parseUrl ['query'], $data, $headers, $timeout, $type );
	
		if (isset ( $response->headers ['Location'] )) {
			return self::post2 ( $response->headers ['Location'], $data, $headers, $timeout, $type );
		}
		return $response;
	}
	

	/**
	 * 金立账号1.5.0自动登陆接口代码专用 版本兼容用
	 *
	 */
	public static function _post2($host, $port, $query, $body, $headers = array(), $timeout = 10, $type = 'POST') {
		$_host = str_replace ( 'ssl://', '', $host );
		$header = "${type} ${query} HTTP/1.1\r\n";
		$header .= "Host: ${_host}\r\n";
		$header .= "Content-Length: " . strlen ( $body ) . "\r\n";
		$header .= self::_addHeaders ( $headers );
		$header .= "\r\n\r\n";
		return self::_sockopen ( $header . $body, $host, $port, $timeout );
	}
	
	/**
	 * 带有页面重定向的url页面
	 * 
	 * @param string $url        	
	 * @return
	 *
	 */
	public static function redirect($url) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_HEADER, 1 ); // 设置显示返回的http头
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 0 );
		// curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1); //是否抓取跳转后的页面
		$result = self::curlRedirExec ( $ch );
		curl_close ( $ch ); // close cURL handler
		return $result;
		;
	}
	public static function curlRedirExec($ch) {
		static $curl_loops = 0;
		static $curl_max_loops = 20;
		
		if ($curl_loops ++ >= $curl_max_loops) {
			$curl_loops = 0;
			return FALSE;
		}
		curl_setopt ( $ch, CURLOPT_HEADER, true );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$data = curl_exec ( $ch );
		$debbbb = $data;
		list ( $header, $data ) = explode ( "\n\n", $data, 2 );
		$http_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		
		if ($http_code == 301 || $http_code == 302) {
			$matches = array ();
			preg_match ( '/Location:(.*?)\n/', $header, $matches );
			$url = @parse_url ( trim ( array_pop ( $matches ) ) );
			if (! $url) {
				$curl_loops = 0;
				return $data;
			}
			$last_url = parse_url ( curl_getinfo ( $ch, CURLINFO_EFFECTIVE_URL ) );
			$new_url = $url ['scheme'] . '://' . $url ['host'] . $url ['path'] . ($url ['query'] ? '?' . $url ['query'] : '');
			curl_setopt ( $ch, CURLOPT_URL, $new_url );
			return self::curlRedirExec ( $ch );
		} else {
			$curl_loops = 0;
			return $debbbb;
		}
	}
	
	/**
	 * 根据后缀返回mime type类型
	 * 
	 * @param string $ext        	
	 */
	public static function getMimeType($ext) {
		switch (strtolower ( $ext )) {
			case 'html' :
			case 'htm' :
			case 'shtml' :
				return 'text/html';
			case 'css' :
				return 'text/css';
			case 'xml' :
				return 'text/xml';
			case 'gif' :
				return 'image/gif';
			case 'jpeg' :
			case 'jpg' :
				return 'image/jpeg';
			case 'js' :
				return 'application/x-javascript';
			case 'atom' :
				return 'application/atom+xml';
			case 'rss' :
				return 'application/rss+xml';
			case 'mml' :
				return 'text/mathml';
			case 'txt' :
				return 'text/plain';
			case 'jad' :
				return 'text/vnd.sun.j2me.app-descriptor';
			case 'wml' :
				return 'text/vnd.wap.wml';
			case 'htc' :
				return 'text/x-component';
			case 'png' :
				return 'image/png';
			case 'tif' :
			case 'tiff' :
				return 'image/tiff';
			case 'wbmp' :
				return 'image/vnd.wap.wbmp';
			case 'ico' :
				return 'image/x-icon';
			case 'jng' :
				return 'image/x-jng';
			case 'bmp' :
				return 'image/x-ms-bmp';
			case 'svg' :
				return 'image/svg+xml';
			case 'jar' :
			case 'war' :
			case 'ear' :
				return 'application/java-archive';
			case 'hqx' :
				return 'application/mac-binhex40';
			case 'doc' :
				return 'application/msword';
			case 'pdf' :
				return 'application/pdf';
			case 'ps' :
			case 'eps' :
			case 'ai' :
				return 'application/postscript';
			case 'rtf' :
				return 'application/rtf';
			case 'xls' :
				return 'application/vnd.ms-excel';
			case 'ppt' :
				return 'application/vnd.ms-powerpoint';
			case 'wmlc' :
				return 'application/vnd.wap.wmlc';
			case 'kml' :
				return 'application/vnd.google-earth.kml+xml';
			case 'kmz' :
				return 'application/vnd.google-earth.kmz';
			case '7z' :
				return 'application/x-7z-compressed';
			case 'cco' :
				return 'application/x-cocoa';
			case 'jardiff' :
				return 'application/x-java-archive-diff';
			case 'jnlp' :
				return 'application/x-java-jnlp-file';
			case 'run' :
				return 'application/x-makeself';
			case 'pl' :
			case 'pm' :
				return 'application/x-perl';
			case 'prc' :
			case 'pdb' :
				return 'application/x-pilot';
			case 'rar' :
				return 'application/x-rar-compressed';
			case 'rpm' :
				return 'application/x-redhat-package-manager';
			case 'sea' :
				return 'application/x-sea';
			case 'swf' :
				return 'application/x-shockwave-flash';
			case 'sit' :
				return 'application/x-stuffit';
			case 'tcl' :
			case 'tk' :
				return 'application/x-tcl';
			case 'der' :
			case 'pem' :
			case 'crt' :
				return 'application/x-x509-ca-cert';
			case 'xpi' :
				return 'application/x-xpinstall';
			case 'xhtml' :
				return 'application/xhtml+xml';
			case 'zip' :
				return 'application/zip';
			case 'bin' :
			case 'exe' :
			case 'dll' :
			case 'deb' :
			case 'dmg' :
			case 'eot' :
			case 'iso' :
			case 'img' :
			case 'msi' :
			case 'msp' :
			case 'msm' :
				return 'application/octet-stream';
			case 'mid' :
			case 'midi' :
			case 'kar' :
				return 'audio/midi';
			case 'mp3' :
				return 'audio/mpeg';
			case 'ogg' :
				return 'audio/ogg';
			case 'ra' :
				return 'audio/x-realaudio';
			case '3gpp' :
			case '3gp' :
				return 'video/3gpp';
			case 'mpeg' :
			case 'mpg' :
				return 'video/mpeg';
			case 'mov' :
				return 'video/quicktime';
			case 'flv' :
				return 'video/x-flv';
			case 'mng' :
				return 'video/x-mng';
			case 'asx' :
			case 'asf' :
				return 'video/x-ms-asf';
			case 'wmv' :
				return 'video/x-ms-wmv';
			case 'avi' :
				return 'video/x-msvideo';
			default :
				return 'text/plain';
		}
	}
}