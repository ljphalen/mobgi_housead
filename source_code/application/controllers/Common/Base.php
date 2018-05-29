<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Common_BaseController
 * @author rock.luo
 *
 */
abstract class Common_BaseController extends Yaf_Controller_Abstract {
    public $actions = array();
    public $filter = array();

    /**
     * Enter description here ...
     */
    public function init() {
        $this->setTempPathByModule();
        $webroot = Common::getWebRoot();
        $staticroot = Yaf_Application::app()->getConfig()->staticroot;
        $this->assign("webroot", $webroot);
        $this->assign("staticPath", $staticroot . '/static');
        $this->assign("commonPath", $staticroot . '/static/common');
        $this->assign("staticAdvertiserPath", $staticroot . '/static/advertiser');
        $this->assign("attachPath", Common::getAttachPath());
        $this->assign('token', Common::getToken());
        $this->assign('version', Common::getConfig('siteConfig', 'version'));
        $this->assign('titlepre', Common::getTitlePre());
        // init actions
        foreach ($this->actions as $key => $value) {
            $this->assign($key, $value);
        }
        if ($this->isAjax()) {
            Yaf_Dispatcher::getInstance()->disableView();
        }
    }


    /**
     * 根据模块来设置模板路径
     */
    protected function setTempPathByModule($theme = 'default') {
        $module = $this->getRequest()->getModuleName();

        //修改模板路径
        $temp_dir = APP_PATH . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
        $this->getView()->setScriptPath($temp_dir);
    }


    /**
     *
     * Enter description here ...
     * @param unknown_type $var
     * @param unknown_type $value
     */
    public function assign($var, $value) {
        $this->getView()->assign($var, $value);
    }


    /**
     * 获取post参数
     * @param $var
     * @return array|mixed|null
     */
    public function getPost($var) {
        if (is_string($var)) return Util_Filter::post($var);
        $return = array();
        if (is_array($var)) {
            foreach ($var as $key => $value) {
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
     * 获取get参数
     * @param $var
     * @return array|mixed|null
     */
    public function getGet($var) {
        if (is_string($var)) return Util_Filter::get($var);
        $return = array();
        if (is_array($var)) {
            foreach ($var as $key => $value) {
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
     * 是否post
     */
    public function isPost() {
        return $this->getRequest()->isPost();
    }

    /*
     * 是否get
     */
    public function isGet() {
        return $this->getRequest()->isGet();
    }


    public function getServer($var = null) {
        if (is_null($var)) return $this->getRequest()->getServer();
        if (is_string($var)) return $this->getRequest()->getServer($var);
        if (is_array($var)) {
            $return = array();
            foreach ($var as $key => $value) {
                $return[$value] = $this->getRequest()->getServer($value);
            }
            return $return;
        }
    }

    /**
     * 获取get参数
     * @param $var
     * @param string $type
     * @return array|mixed|null
     */
    public function getInput($var, $type = '#s_z') {
        if (is_string($var)) return $this->getVal($var, $type);
        if (is_array($var)) {
            $return = array();
            foreach ($var as $key => $value) {
                $return[$value] = $this->getVal($value, $type);
            }
            return $return;
        }
        return null;
    }

    /**
     * @param $var
     * @param string $type
     * @return mixed|null
     */
    private function getVal($var, $type = '#s_z') {
        if ($this->isPost()) {
            $value = Util_Filter::post($var, $type);
            if (!is_null($value)) return $value;
        }
        if ($this->isGet()) {
            $value = Util_Filter::get($var);
            if (!is_null($value)) return $value;
        }
        return null;
    }

    /**
     * 获取请求参数
     * @return mixed|null
     */
    public function getJsonPost() {
        $json = file_get_contents('php://input');
        return Common::is_json($json) ? json_decode($json, true) : [];


    }

    /**
     *
     * 请求是否是ajax
     */
    public function isAjax() {
        return $this->getRequest()->isXmlHttpRequest() || $this->getInput("callback");
    }

    /**
     *
     * 请求是否是vue的请求
     */
    public function isVueRequest() {
        if (isset($_SERVER['HTTP_VUE_SESSID'])) {
            $json_post = $this->getJsonPost();
            if (!empty($json_post)) {
                $_POST = array_merge($_POST, $json_post);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $code
     * @param string $msg
     * @throws Yaf_Exception
     */
    public function showMsg($code, $msg = '') {
        if ($this->isVueRequest()) {
            $this->output($code, $msg);
        } else {
            throw new Yaf_Exception($msg, $code);
        }
    }


    /**
     * @param $code
     * @param string $msg
     * @param array $data
     */
    public function output($code, $msg = '', $data = array()) {
        header("Content-type:text/json");
        exit(json_encode(array(
            'success' => $code == 0 ? true : false,
            'msg' => $msg,
            'data' => $data
        )));
    }


    /**
     * 校验并过滤数据 op val msg rep
     * @param $params
     * @param $list
     * @return mixed
     */
    public function checkParams($params, $list) {
        foreach ($list as $field => $value) {
            list($format, $check, $children) = $value;
            foreach ($format as $type) {
                $params[$field] = $this->format($params[$field], $type);
            }
            foreach ($check as $v) {
                list($op, $val, $msg, $rep) = $v;
                if (!$this->check($op, $params[$field], $val)) {
                    if (!empty($rep)) { // 替换参数 如 (%s) -> (activity_name)
                        $msg = str_replace('%s', $params[$rep], $msg);
                    }
                    $this->output(-1, $msg);
                }
            }
            if (!empty($children)) {
                $params = $this->checkParams($params, $children);
            }
        }
        return $params;
    }


    /**
     * check 条件匹配
     * @param $op
     * @param $value
     * @param $checkValue
     * @return bool
     */
    private function check($op, $value, $checkValue = '') {
        switch ($op) {
            case 'date':
                $flag = strtotime($value) ? false : true;
                break;
            case 'empty':
                $flag = empty($value) ? false : true;
                break;
            case '0':
                $flag = ($value == 0) ? false : true;
                break;
            case 'isset':
                $flag = isset($value) ? true : false;
                break;
            case '=':
                $flag = ($value == $checkValue) ? true : false;
                break;
            case '>':
                $flag = ($value > $checkValue) ? true : false;
                break;
            case '<':
                $flag = ($value < $checkValue) ? true : false;
                break;
            case '>=':
                $flag = ($value >= $checkValue) ? true : false;
                break;
            case '<=':
                $flag = ($value <= $checkValue) ? true : false;
                break;
            case "in":
                $flag = in_array($value, $checkValue) ? true : false;
        }
        return $flag;
    }

    /**
     * format the params
     * @param $val
     * @param $type
     * @return float|int|null
     */
    private function format($val, $type) {
        switch ($type) {
            case 'int':
                $result = empty($val) ? 0 : (is_numeric($val) ? intval($val) : NULL);
                break;
            case 'float':
                $result = is_numeric($val) ? floatval($val) : NULL;
                break;
            case 'toInt':
                $result = intval($val);
                break;
            case 'toUpper':
                $result = strtoupper($val);
                break;
            case 'toLower':
                $result = strtolower($val);
                break;
            case 'trim':
                $result = trim($val);
                break;
            case 'request':
                $result = $val;
                break;
            default:
                $result = NULL;
        }
        return $result;
    }


    /**
     * get input
     * @param $list
     * @param array $myParams
     * @param bool $isOr
     * @return array
     */

    public function getParams($list, $myParams = [], $isOr = false) {
        $params = [];
        if ($this->isVueRequest()) {
            $myParams = $myParams ?: $this->getJsonPost();
        }
        foreach ($list as $field => $conditions) {
            $val = $myParams[$field] ?: $this->getVal($field);
            $status = true;
            foreach ($conditions as $condition) {
                if (is_string($condition)) {
                    $type = $condition;
                    list($status, $newVal, $msg) = $this->validate($val, $type);
                } elseif (is_array($condition)) {
                    //                    $condition =>[$type,$val,$msg,$sub,$flag]
                    $type = $condition[0];
                    list($status, $newVal, $msg) = $this->validate($val, $type, $condition[1], $condition[2] ?: '');
                    //是否有子校验
                    if ($status and isset($condition[3]) and !empty($condition[3])) {
                        $this->getParams($condition[3], array_merge($params, [$field => $val]));
                    }
                    if (isset($condition[4])) {
                        $isOr = boolval($condition[4]);
                    }
                } elseif ($condition instanceof Closure) {
                    list($status, $newVal, $msg) = $condition($val);
                }
                if (!$status and !$isOr) {
                    $this->output(-1, $field . ':' . $msg);
                } elseif (!is_null($newVal)) {
                    $val = $newVal;
                }
            }
            if (!is_null($val)) {
                $params[$field] = $val;
            }
        }
        return $params;
    }

    private function validate($val, $type, $condition, $msg = null, $def = null) {
        switch ($type) {
            case 'null':
                $status = is_null($val);
                $result = [$status ?: $val, $msg ?: '为空'];
                break;

            case 'isset':
                $status = !is_null($val);
                $result = [$status, $val, $msg ?: '不能为空'];
                break;

            case 'trim':
                $result = [true, is_string($val) ? trim($val) : $val, $msg];
                break;

            case 'max_len':
                $len = strlen($val);
                $status = $len <= intval($condition);
                $result = [$status, $val, $msg ?: '长度必须小于' . $condition];
                break;


            case 'int':
                $status = is_numeric($val);
                $result = [$status, $status ? intval($val) : $val, $msg ?: '必须是数字'];
                break;

            case '=':
                $status = $val == $condition;
                $result = [$status, $status ? $val : null, $msg ?: '不等于' . $condition];
                break;

            case 'in':
                $status = in_array($val, $condition);
                $result = [$status, $status ? $val : null, $msg ?: '不在指定范围' . $condition];
                break;

            case 'notin':
                $status = !in_array($val, $condition);
                $result = [$status, $status ? $val : null, $msg ?: $val . '在指定范围' . $condition];
                break;

            case 'between':
                if (is_array($condition) and count($condition) == 2) {
                    $status = $val >= $condition[0] && $val <= $condition[1];
                    $result = [$status, $status ? $val : null, $msg ?: $val . ':不在指定区间' . $condition];
                } else {
                    $result = [false, null, $msg ?: $val . ':条件定义有误:' . $condition];
                }
                break;

            case 'default':
                $result = [true, is_null($val) ? $condition : $val, $msg];
                break;

            case 'array':
                $val = is_null($val) ?: json_decode($val);
                $status = is_array($val);
                $result = [$status, $val, $msg];
                break;

            case 'count_range':
                if (is_array($val)) {
                    if (is_array($condition) and count($condition) == 2) {
                        $status = count($val) >= $condition[0] && count($val) <= $condition[1];
                        $result = [$status, $status ? $val : null, $msg ?: $val . ':不在指定区间' . $condition];
                    } else {
                        $result = [false, null, $msg ?: $val . ':条件定义有误:' . $condition];
                    }
                } else {
                    $result = [false, null, $msg ?: $val . ':必须是数组'];
                }
                break;
            default:
                $result = NULL;
        }
        return $result;
    }


}
