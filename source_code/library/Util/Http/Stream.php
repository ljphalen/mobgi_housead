<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * @author rock.luo
 *
 */
final class Util_Http_Stream extends Util_Http_Base {
	/**
	 * @var string 字节流对象
	 */
	private $context = null;
	/**
	 * @var string 通信协议
	 */
	private $wrapper = 'http';
	private $host = '';
	private $port = 80;
	private $path = '';
	private $query = '';

	/**
	 * 设置通信协议
	 * @param string $wrapper
	 */
	public function setWrapper($wrapper = 'http') {
		$this->wrapper = $wrapper;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::createHttpHandler()
	 */
	protected function createHttpHandler() {
		$url = parse_url($this->url);
		isset($url['scheme']) && $this->wrapper = $url['scheme'];
		isset($url['host']) && $this->host = $url['host'];
		isset($url['path']) && $this->path = $url['path'];
		isset($url['query']) && $this->query = $url['query'];
		$this->context = stream_context_create();
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::request()
	 */
	public function request($name, $value = null) {
		return stream_context_set_option($this->context, $this->wrapper, $name, $value);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::response()
	 */
	public function response() {
		$response = '';
		while (!feof($this->getHttpHandler())) {
			$response .= fgets($this->getHttpHandler());
		}
		return $response;
	}

	/**
	 * 释放资源
	 */
	public function close() {
		if ($this->httpHandler) {
			fclose($this->httpHandler);
			$this->httpHandler = null;
			$this->context = null;
		}
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::getError()
	 */
	public function getError() {
		return $this->err ? $this->eno . ':' . $this->err : '';
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::send()
	 */
	public function send($method = 'GET', $options = array()) {
		$method = strtoupper($method);
		if ($this->data) {
			switch ($method) {
				case 'GET':
					$_url = Util_Url::argsToUrl($this->data);
					$this->url .= ($this->query ? '&' : '?') . $_url;
					break;
				case 'POST':
					$data = Util_Url::argsToUrl($this->data, false);
					$this->setHeader('application/x-www-form-urlencoded', 'Content-Type');
					$this->setHeader(strlen($data), 'Content-Length');
					$this->request('content', $data);
					break;
				default:
					break;
			}
		}
		$this->httpHandler = fopen($this->url, 'r', false, $this->context);
		
		$this->setHeader($this->host, "Host");
		$this->setHeader('Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)', 'User-Agent');
		$this->setHeader('Close', 'Connection');
		if ($options) $this->setHeader($options);
		if ($this->cookie) {
			$_cookie = Util_Url::argsToUrl($this->cookie, false, ';=');
			$this->setHeader($_cookie, "Cookie");
		}
		$_header = '';
		foreach ($this->header as $key => $value) {
			$_header .= $key . ': ' . $value . "\r\n";
		}
		$_header && $this->request('header', $_header);
		$this->request('method', $method);
		$this->request('timeout', $this->timeout);
		return $this->response();
	}
}