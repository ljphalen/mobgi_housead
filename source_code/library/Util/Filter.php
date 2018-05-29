<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Util_Filter 类
 *
 * 概述：过滤一切用户的输入操作
 *
 * 服务：
 * -  过滤GET、POST提交的数据；
 * -  转换数据内容： html剔除 js格式的数据 swf格式的数据 html转义
 * -  描述 过滤 和 验证 类型：*代表验证 #代表过滤
 *		  1.  *reg		正则表达式[验证]
 *		  2.  *callback   自定义回调函数[验证]
 *		  3.  *i<		 验证小于几
 *		  4.  *i>		 验证大于几
 * 		  3.  *f<		 验证小于几 （浮点）
 *		  4.  *f>		 验证大于几（浮点）
 *		  5.  *s<		 验证小于多少字符
 *		  6.  *s>		 验证大于多少字符
 *		  7.  *srange	 验证字符数量范围
 *		  8.  *irange	 验证数字范围
 *		  9.  *int		验证必须是 int 整数
 *		  10. *bool	   验证必须是 boolean 布尔型
 *		  11. *float	  验证必须是 float 浮点数
 *		  12. *url		验证url地址
 *		  13. *email	  验证email
 *		  14. *ip		 验证IP
 *		  15. *ipv4	   验证IPV4
 *		  16. *ipv6	   验证IPV6
 *		  17. *ippr	   验证 ip 非私有IP 范围
 *		  18. *iprr	   验证 ip 非保留的 IP 范围
 *		  19. *price 验证 价格
 *		  20. *mobile 验证手机号
 *
 *		  1.  #email	  过滤email
 *		  2.  #url		过滤url
 *		  3.  #s_z	   如果存在HTML标签，将作为文本输出，并且转换一些特殊字符成其他形式【浏览器可识别形式 &#60; 】 _conFilter
 *		  4.  #s_zb	   如果存在HTML标签，将作为文本输出，并且转换一些特殊字符成其他形式【浏览器可识别形式 &lt; 】, 并且把换行转换成<br>  _conFilter _nlSpaceSwitch
 *		  5.  #s_fh	   如果存在HTML标签，过滤掉有威胁的HTML标签【适用于所见即所得的BLOG等】   _htmlFilter
 *		  6.  #s_t	   如果存在HTML标签，剔除所有标签，保留其中文本，并且转换一些特殊字符成其他形式【浏览器可识别形式 &lt; 】 _htmlWeebFilter
 *		  7.  #callback   自定义回调函数[过滤]
 *
 *		  1 . nowords	 没有词语过滤，否则默认对字符串进行词语过滤
 *		  2 . fail		错误处理
 *
 * @example
 * 返回数组，并且第一个元素是null，则表明有没有通过的变量
 * 1 . get | post
 *
 *	  - Filter::[get|post]('name', '*int:*reg:*callback', '/^M(.*)/', 'disp');
 *	  - Filter::[get|post]('num', '*i<', 25);
 *	  - Filter::[get|post]('num', '*i>', 25);
 *	  - Filter::[get|post]('name', '*s<:#s_z', 25);
 *	  - Filter::[get|post]('name', '*s>:#s_z', 25);
 *	  - Filter::[get|post]('name', '*srange:#s_z', '25:500');
 *	  - Filter::[get|post]('num', '*irange', '25:500');
 *	  - Filter::[get|post]('price', '*price', '2:10000000');   //二位小数，金额不能大于10000000
 *	  - Filter::[get|post]('num', '*irange:fail:nowords', '25:500', function(){});
 * 2 . vars
 *	  - Filter::vars(array('a', 'v', array('r', 'm')), '*int:*reg:*callback', '/^M(.*)/ ', 'disp');
 *
 */

class Util_Filter {

	/**
	 * 特殊验证方式，须提供额外参数辅助
	 *
	 * @var array
	 */
	private $_specialValidate = array('*reg', '*callback', '#callback', '*i<', '*i>', '*f<', '*f>', '*s<', '*s>', '*srange', '*irange','*price', 'fail', 'nowords');

	/**
	 * 额外提取出来的参数
	 *
	 * @var array
	 */
	private $_extractArgs = array();

	/**
	 * 过滤模式 get post var
	 *
	 * @var int|boolean
	 */
	private $_mode = null;

	/**
	 * get post 参数名 或 变量的变量值
	 *
	 * @var string|array
	 */
	private $_argName = null;

	/**
	 * 过滤类型
	 *
	 * @var string
	 */
	private $_type = null;

	/**
	 * 是否词语过滤，默认不过滤
	 *
	 * @var array
	 */
	private $_isWords = false;

	/**
	 * 错误处理函数
	 *
	 * @var function
	 */
	private $_failCallBack = null;

	/**
	 * 针对性错误处理函数
	 *
	 * @var function
	 */
	private $_failCallBackPertinence = null;

	private function __construct() {

	}

	/**
	 * 禁止克隆
	 *
	 */
	private function __clone() {
		//empty!!
	}

	/**
	 * 转义特殊字符
	 *
	 * @param   string	$var  需要转义的内容
	 *
	 * @return  string
	 */
	private function _conFilter($var) {
		$trans = array(
			'<' => '&#60;',
			'>' => '&#62;',
			"'" => '&#39;',
			'"' => '&#34;',
			',' => '&#44;',
			'(' => '&#40;',
			')' => '&#41;',
			'?' => '&#63;',
			'\\' => '&#92;',
			'&' => '&amp;',
		);
		return strtr($var, $trans);
	}

	/**
	 * 转换 空格和换行和制表符
	 *
	 * @param   string	$var  需要转换的内容
	 *
	 * @return  string
	 */
	private function _nlSpaceSwitch ($var) {
		$trans = array(
			'\t' => '&nbsp;',
			' ' => '&nbsp;',
			'\\' => '&#92;',
		);
		return nl2br(strtr($var, $trans));
	}

	/**
	 * 过滤危险的HTML内容
	 *
	 * @param   string	$var  需要过滤的内容
	 * @return  string
	 */
	private function _unsafeHtmlFilter($var) {
		if (!defined("XML_HTMLSAX3")) define("XML_HTMLSAX3", "");
		require_once ("safehtml/safehtml.php");
		$safeHtml = new SafeHTML();
		return $safeHtml->parse($var);
	}
	
	/**
	 * 去除标签
	 *
	 * @param   string	$var  需要去除的内容
	 *
	 * @return  string
	 */
	private function _htmlWeebFilter($var) {
		return filter_var($var, FILTER_SANITIZE_STRING);
	}
	
	/**
	 * 正式过滤前的特殊内容过滤
	 *
	 * @param string $type  过滤类型
	 * @param string $var   过滤内容
	 *
	 * @return string
	 */
	private function _beforeFilter($type, $var) {
		switch ($type) {
			case '#s_z': // 如果存在HTML标签，将作为文本输出，并且转换一些特殊字符成其他形式【浏览器可识别形式 &#60; 】
				if (gettype($var) == 'string') {
					return $this->_conFilter($var);
				}
				break;
			case '#s_t': // 如果存在HTML标签，剔除所有标签，保留其中文本，并且转换一些特殊字符成其他形式【浏览器可识别形式 &lt; 】
				if (gettype($var) == 'string') {
					return $this->_htmlWeebFilter($var);
				}
				break;
			case '#s_zb': // 如果存在HTML标签，将作为文本输出，并且转换一些特殊字符成其他形式【浏览器可识别形式 &lt; 】, 并且把换行转换成<br>
				if (gettype($var) == 'string') {
					return $this->_nlSpaceSwitch(self::_conFilter($var));
				}
				break;
			case '#s_fh': // 如果存在HTML标签，过滤掉有威胁的HTML标签【适用于所见即所得的BLOG等】
				if (gettype($var) == 'string') {
					return $this->_unsafeHtmlFilter($var);
				}
				break;
			default:
				return $var;
				break;
		}
		
		return false;
	}
	
	/**
	 * 获得过滤类型
	 *
	 * @param   string  $type   过滤类型
	 *
	 * @return int
	 */
	private function _getFilterMode($type) {
		switch ($type) {
			case '*callback': // 自定义回调函数[验证]
			case '#callback': // 自定义回调函数[过滤]
				$flag = array(FILTER_CALLBACK, '');
				break;
			case '*reg': // 正则表达式[验证]
				$flag = array(FILTER_VALIDATE_REGEXP, '');
				break;
			case '*int': // 验证必须是 int 整数
				$flag = array(FILTER_VALIDATE_INT, '必须是数字');
				break;
			case '*bool': // 验证必须是 true | false
				$flag = array(FILTER_VALIDATE_BOOLEAN, '必须是布尔型');
				break;
			case '*float': // 验证必须是 float 浮点数
				$flag = array(FILTER_VALIDATE_FLOAT, '必须是数字或小数');
				break;
			case '*url': // 验证url地址，RFC 兼容【比如：http://example】、包含主机名、URL在主机名后存在路径
				$flag = array(FILTER_VALIDATE_URL, 'url地址不符合规范');
				break;
			case '*email': // 验证 email 格式
				$flag = array(FILTER_VALIDATE_REGEXP, 'email 格式不符合规范');
				break;
			case '*ip': // 验证 ip 格式
				$flag = array(FILTER_VALIDATE_IP, 'IP 无效');
				break;
			case '*ipv4': // 验证 ipv4 格式
				$flag = array(FILTER_VALIDATE_IP . ', ' . FILTER_FLAG_IPV4, 'IP 无效 或 不符合规范');
				break;
			case '*ipv6': // 验证 ipv6 格式
				$flag = array(FILTER_VALIDATE_IP . ', ' . FILTER_FLAG_IPV6, 'IP 无效 或 不符合规范');
				break;
			case '*ippr': // 验证 ip 非私有IP 范围
				$flag = array(FILTER_VALIDATE_IP . ', ' . FILTER_FLAG_NO_PRIV_RANGE, 'IP 无效 或 属于私有 IP 范围内');
				break;
			case '*iprr': // 验证 ip 非保留的 IP 范围
				$flag = array(FILTER_VALIDATE_IP . ', ' . FILTER_FLAG_NO_RES_RANGE, 'IP 无效 或 属于保留 IP 范围内');
				break;
			case '#email': // 过滤email字符 允许所有的【字母、数字以及 $-_.+!*'{}|^~[]`#%/?@&=】
				$flag = array(FILTER_SANITIZE_EMAIL, 'email中含有非法字符');
				break;
			case '#url': // 过滤url字符 允许所有的【除了字母、数字以及 $-_.+!*'(), {}|\ \ ^~[]`<>#%"; /?:@&=】
				$flag = array(FILTER_SANITIZE_URL, 'url中含有非法字符');
				break;
			case '#s_z': // 保留html标签，去除 或 编码特殊字符，剔除ASCII 32以下字符。 _conFilter
			case '#s_zb': // 保留html标签，去除 或 编码特殊字符。剔除ASCII 32以下字符。 _conFilter _nlSpaceSwitch
			case '#s_fh': // 保留html标签，去除 或 编码特殊字符。剔除ASCII 32以下字符。 _htmlFilter
			case '#s_t': // 保留html标签，去除 或 编码特殊字符。剔除ASCII 32以下字符。 _htmlWeebFilter
				$flag = array(FILTER_UNSAFE_RAW . ', ' . FILTER_FLAG_STRIP_LOW, '含有不合法的危险字符');
				break;
		}
		return $flag;
	}
	
	/**
	 * 执行过滤 和 验证
	 *
	 * @param mixed $var
	 * @param mixed $type
	 * @param mixed $callBack
	 *
	 * @return mix [array|mix]
	 */
	private function _exec($var, $type) {
		// 检查编码
		$var = self::chkEncode($var);
		for ($i = 0, $total = count($type); $i < $total; ++$i) {
			if (isset($this->_extractArgs[$type[$i]])) {
				$tmpVal = $this->_extractArgs[$type[$i]];
				switch ($type[$i]) {
					case '*reg':
						$filterMode = $this->_getFilterMode($type[$i]);
						$var = filter_var($var, $filterMode[0], array('options' => array("regexp" => $tmpVal)));
						if ($var === false) {
							return array(false, $type[$i], '必须符合 [' . $tmpVal . '] 规则');
						}
						break;
					case '*callback':
						/**
						 * @example
						 *
						 * function test($str)
						 * {
						 * 验证成功返回 true
						 * 验证失败返回 错误提示信息
						 * }
						 */
						
						$filterMode = $this->_getFilterMode($type[$i]);
						$back = filter_var($var, $filterMode[0], array('options' => $tmpVal));
						if ($back !== true) {
							return array(false, $type[$i], $back);
						}
						break;
					case '#callback':
						/**
						 * @example
						 *
						 * function test($str)
						 * {
						 * 返回过滤后的内容
						 * }
						 */
						
						$filterMode = $this->_getFilterMode($type[$i]);
						$var = filter_var($var, $filterMode[0], array('options' => $tmpVal));
						break;
					case '*i<':
						$filterMode = $this->_getFilterMode('*int');
						if (false === filter_var($var, $filterMode[0])) {
							return array(false, '*int', $filterMode[1]);
						}
						$var = intval($var);
						if ($var > intval($tmpVal)) {
							return array(false, $type[$i], '必须小于' . $tmpVal . '位数');
						}
						break;
					case '*i>':
						$filterMode = $this->_getFilterMode('*int');
						if (false === filter_var($var, $filterMode[0])) {
							return array(false, '*int', $filterMode[1]);
						}
						$var = intval($var);
						if ($var < intval($tmpVal)) {
							return array(false, $type[$i], '必须大于' . $tmpVal . '位数');
						}
						break;
					case '*f<':
						$filterMode = $this->_getFilterMode('*float');
						if (false === filter_var($var, $filterMode[0])) {
							return array(false, '*float', $filterMode[1]);
						}
						$var = (float) $var;
						if ($var > (float) $tmpVal) {
							return array(false, $type[$i], '必须小于' . $tmpVal);
						}
						break;
					case '*f>':
						$filterMode = $this->_getFilterMode('*float');
						if (false === filter_var($var, $filterMode[0])) {
							return array(false, '*float', $filterMode[1]);
						}
						$var = (float) $var;
						if ($var < (float) $tmpVal) {
							return array(false, $type[$i], '必须大于' . $tmpVal);
						}
						break;
					case '*s<':
						if (false === is_string($var)) {
							return array(false, '*string', '类型错误，必须是字符串');
						}
						if (isset($var[intval($tmpVal)])) {
							return array(false, $type[$i], '内容长度必须小于' . $tmpVal . '位数');
						
		//return array(false, $type[$i], '可以为空');
						}
						break;
					case '*s>':
						if (false === is_string($var)) {
							return array(false, '*string', '类型错误，必须是字符串');
						}
						if (!isset($var[intval($tmpVal)])) {
							return array(false, $type[$i], '内容长度必须大于' . $tmpVal . '位数');
						
		//return array(false, $type[$i], '不能为空');
						}
						break;
					case '*srange':
						if (false === is_string($var)) {
							return array(false, '*string', '类型错误，必须是字符串');
						}
						$tmpVal = explode(':', $tmpVal);
						if (!isset($var[intval($tmpVal[0])])) {
							return array(false, $type[$i], '长度必须在' . $tmpVal[0] . ' 与 ' . $tmpVal[1] . ' 范围内');
						}
						if (isset($var[intval($tmpVal[1])])) {
							return array(false, $type[$i], '长度必须在' . $tmpVal[0] . ' 与 ' . $tmpVal[1] . ' 范围内');
						}
						break;
					case '*irange':
						$tmpVal = explode(':', $tmpVal);
						$filterMode = $this->_getFilterMode('*int');
						if (false === filter_var($var, $filterMode[0])) {
							return array(false, '*int', $filterMode[1]);
						}
						$var = intval($var);
						if ($var < intval($tmpVal[0])) {
							return array(false, $type[$i], '必须在' . $tmpVal[0] . ' 与 ' . $tmpVal[1] . ' 范围内');
						}
						if ($var > intval($tmpVal[1])) {
							return array(false, $type[$i], '必须在' . $tmpVal[0] . ' 与 ' . $tmpVal[1] . ' 范围内');
						}
						break;
					case '*price':
						$tmpVal = explode(':', $tmpVal);
						$filterMode = $this->_getFilterMode('*float');
						if (false === filter_var($var, $filterMode[0])) {
							return array(false, $type[$i], $filterMode[1]);
						}
						$var = (float) $var;
						
						$dotLen = strlen(substr(strrchr($var, '.'), 1));
						if ($dotLen > intval($tmpVal[0]) || $var > (float) $tmpVal[1] || $var < 0) {
							return array(false, $type[$i], '价格只能精确到' . $tmpVal . '位小数');
						}
						break;
				}
			} else {
				$var = $this->_beforeFilter($type[$i], $var);
				if (false === $var) {
					return array(false, '*string', '类型错误，必须是字符串');
				}
				$filterMode = $this->_getFilterMode($type[$i]);
				if (strstr($filterMode[0], ',')) {
					$args = explode(', ', $filterMode[0]);
					$tmpStr = '$var = filter_var($var';
					foreach ($args as $val) {
						$tmpStr .= ',' . $val;
					}
					$tmpStr .= '); ';
					eval($tmpStr);
				} else {
					if ('*email' == $type[$i]) {
						$filterMode = $this->_getFilterMode($type[$i]);
						$var = filter_var($var, $filterMode[0], array('options' => array('regexp' => '/^(?:[\w \-\.]+)@(?:[\-\w]+)\.(?:[\-\w\.]+)$/i')));
						if ($var === false) {
							return array(false, $type[$i], $filterMode[1]);
						}
					} elseif('*mobile' == $type[$i]) {
						if(!preg_match('/^1[3458]\d{9}$/', $var)) return array(false, $type[$i], '手机号码格式不正确');
					} else {
						$var = filter_var($var, $filterMode[0]);
					}
					
				}
				if ($var === false) {
					return array(false, $type[$i], $filterMode[1]);
				}
				switch ($type[$i]) {
					case '*int':
						$var = intval($var);
						break;
					case '*float':
						$var = floatval($var);
						break;
				}
			}
		}
		
		if ($this->_isWords) { // 词语过滤
			if (in_array('#s_z', $type) || in_array('#s_zb', $type) || in_array('#s_fh', $type) || in_array('#s_t', $type)) {
				$var = self::words($var);
			}
		}
		
		return array(true, $var);
	}
	
	/**
	 * 执行 过滤 或 验证 数组
	 *
	 * @param string	$var			过滤内容
	 * @param string	 $filterType		过滤模式
	 * @param mixed		$key
	 * @param boolean	 $errMode		错误模式
	 * @param mixed	 $options		其他使用
	 *
	 * @return mixed
	 */
	private function _ary($var, $type) {
		foreach ($var as $k => $v) {
			if (is_array($v)) {
				$var[$k] = $this->_ary($v, $type);
				if (false === $var[$k][0]) {
					return array(false, $var[$k][1], $var[$k][2]);
				}
				$var[$k] = $var[$k][1];
			} else {
				$var[$k] = $this->_exec($v, $type);
				if (false === $var[$k][0]) {
					return array(false, $var[$k][1], $var[$k][2]);
				}
				$var[$k] = $var[$k][1];
			}
		}
		return array(true, $var);
	}
	
	/**
	 * 执行过滤 预处理
	 *
	 * @return mixed
	 */
	private function _pretreatment() {
		// !false 说明要过滤的是 get|post 参数内容
		if (false !== $this->_mode) {
			
			if (INPUT_GET || INPUT_POST) {
				if (!isset($_GET[$this->_argName]) && !isset($_POST[$this->_argName])) {
					return array(false, '*arg', '参数丢失');
				}
			}
			/*
			// 检查 get|post 中是否真的存在 argName里的内容
			if (false === filter_has_var($this->_mode, $this->_argName)) {
				return array(false, '*arg', '参数丢失');
			}
			*/
		}
		
		$isAry = false;
		
		if (false !== $this->_mode) {
			if (INPUT_GET === $this->_mode) {
				$var = $_GET[$this->_argName];
				if (is_array($var)) {
					$isAry = true;
				}
			} else {
				$var = $_POST[$this->_argName];
				if (is_array($var)) {
					$isAry = true;
				}
			}
		} else {
			$var = $this->_argName;
			if (is_array($var)) {
				$isAry = true;
			}
		}
		
		$type = explode(':', $this->_type);
		
		if ($isAry) {
			if (count($var) > 0) {
				return $this->_ary($var, $type);
			}
		} else {
			return $this->_exec($var, $type);
		}
	}
	
	/**
	 * 设置额外参数 并与 过滤类型对应 存放到数组中
	 *
	 * @param string $type	   过滤类型
	 * @param array  $argList	额外参数数组
	 */
	private function _setArgs($type, $argList) {
		$this->_extractArgs = array();
		$typeAry = explode(':', $type);
		$needType = array_values(array_intersect($typeAry, $this->_specialValidate));
		$this->_failCallBackPertinence = null;
		$this->_isWords = false;
		for ($j = 0, $i = 2, $total = count($argList); $i < $total; ++$i, ++$j) {
			if ('fail' == $needType[$j]) {
				$this->_failCallBackPertinence = $argList[$i];
			} else if ('nowords' == $needType[$j]) {
				$this->_isWords = false;
			} else {
				$this->_extractArgs[$needType[$j]] = $argList[$i];
			}
		}
	}
	
	/**
	 * 创建实例对象
	 *
	 * @return object
	 */
	private static function _instance() {
		static $instance;
		if (null === $instance) {
			$instance = new Util_Filter();
		}
		return $instance;
	}
	
	//------------------------------ 对外使用接口 ------------------------------
	

	/**
	 * 设置错误处理函数
	 *
	 * @param string|function $callBack
	 *
	 * @return void
	 */
	public static function failCallBack($callBack) {
		$obj = self::_instance();
		$obj->_failCallBack = $callBack;
	}
	
	/**
	 * 过滤 和 验证 GET参数
	 *
	 * @param string	$mode	   过滤模式[GET|POST|VARS]
	 * @param string	$type	   过滤类型
	 * @param string	$argName	需过滤的参数名称
	 * @param array	 $addedArgs  额外参数数组
	 *
	 * @return mix [array|mix]
	 */
	public function exec($mode, $type, $argName, $addedArgs) {
		$this->_setArgs($type, $addedArgs);
		$this->_mode = $mode;
		$this->_argName = $argName;
		$this->_type = $type;
		$back = $this->_pretreatment();
		if ($back[0] == false) {
			if (!is_null($this->_failCallBackPertinence)) {
				return $this->_failCallBackPertinence($back[1], $back[2]);
			} else if (!is_null($this->_failCallBack)) {
				return $this->_failCallBack($back[1], $back[2]);
			}
// 			return array(null, $back[2]);
			return null;
		}
		return $back[1];
	}
	
	/**
	 * 过滤 和 验证 GET参数
	 *
	 * @param string	$argName   需过滤的参数名称
	 * @param string	$type	  过滤类型
	 *
	 * @return mixed
	 */
	public static function get($argName, $type = '#s_t') {
		$args = func_get_args();
		return self::_instance()->exec(INPUT_GET, $type, $argName, $args);
	}
	
	/**
	 * 过滤 和 验证 POST参数
	 *
	 * @param string	$argName   需过滤的参数名称
	 * @param string	$type	  过滤类型
	 *
	 * @return mixed
	 */
	public static function post($argName, $type = '#s_z') {
		$args = func_get_args();
		return self::_instance()->exec(INPUT_POST, $type, $argName, $args);
	}
	
	/**
	 * 过滤 和 验证 内容
	 *
	 * @param string	$var	   需过滤的内容
	 * @param string	$type	  过滤类型
	 *
	 * @return mixed
	 */
	public static function vars($var, $type = '#s_z') {
		$args = func_get_args();
		return self::_instance()->exec(false, $type, $var, $args);
	}
	
	/**
	 * 词语过滤
	 *
	 * @param	string $var	需要过滤得内容
	 * @return   string
	 */
	public static function words($str) {
		static $words;
		
		// 获得需过滤的词语
		if (!isset($words)) {
			$where = 'deleted = 0';
			$words = CmsFilterKeywordsModule::getWords($where);
		}
		// 验证禁用的词语
		if (isset($words['banned']) && '' != $words['banned']) {
			$pattern = '/' . $words['banned'] . '/i';
			if (preg_match($pattern, $str)) {
				return array(false, '中不能含有【' . implode(',', $matches) . '】字符');
			}
		}
		// 过滤词语
		if (isset($words['filter']['find']) && is_array($words['filter']['find'])) {
			return @preg_replace($words['filter']['find'], $words['filter']['replace'], $str);
		}
	}

	/**
	 * 输出过滤
	 *
	 * @example
	 *
	 * 多个【用:隔开】按顺序执行
	 * Filter::output('js:swf', $a); 或 Filter::output('js', $a);
	 *
	 * @param string $type   过滤类型[js|swf|text]
	 * @param string $var	需要过滤的内容
	 *
	 * @return $var
	 */
	public static function output($type, $var) {
		switch ($type) {
			case 'swf':
				$trans = array(
					'&#60;' => '<',
					'&#62;' => '>',
					'&#39;' => "'",
					'&#34;' => '"',
					'&#44;' => ',',
					'&#40;' => '(',
					'&#41;' => ')',
					'&#63;' => '?',
					'&#92;' => '\\',
				);
				$var = urlencode(strtr($var, $trans));
				break;
			case 'flash1':
				$trans = array(
					'&#60;' => '<',
					'&#62;' => '>',
					'&#39;' => "\'",
					'&#34;' => '\"',
					'&#44;' => '\,',
					'&#40;' => '(',
					'&#41;' => ')',
					'&#63;' => '?',
					'&#92;' => '\\',
				);
				$var = urlencode(strtr($var, $trans));
				break;
			case 'text':
				$trans = array(
					'&#60;' => '\ < ',
					'&#62;' => '\ > ',
					'&#39;' => "\'",
					'&#34;' => '\"',
					'&#44;' => '\, ',
					'&#40;' => '\(',
					'&#41;' => '\)',
					'&#63;' => '?',
					'&#92;' => '\\',
					'<br />' => '\n',
				);
				$var = strtr($var, $trans);
				break;
			case 'html':
				$trans = array(
					'&#60;' => '<',
					'&#62;' => '>',
					'&#39;' => "'",
					'&#34;' => '"',
					'&#44;' => ',',
					'&#40;' => '(',
					'&#41;' => ')',
					'&#63;' => '?',
					'&#92;' => '\\',
				);
				$var = strtr($var, $trans);
				break;
		}
		return $var;
	}

	/**
	 * 检查编码, 并转换成UTF8
	 *
	 * @param   string $var
	 *
	 * @return  string
	 */
	public static function chkEncode($var) {
		switch (mb_detect_encoding($var, 'UTF-8, GB2312, GBK')) {
			case 'EUC-CN':
				return mb_convert_encoding($var, 'UTF-8', 'GB2312');
				break;
			case 'CP936':
				return mb_convert_encoding($var, 'UTF-8', 'GBK');
				break;
		}
		return $var;
	}
}