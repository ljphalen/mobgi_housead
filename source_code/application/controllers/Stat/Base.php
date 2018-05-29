<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Stat_BaseController
 * @author atom.zhan
 *
 */
abstract class Stat_BaseController extends Adx_BaseController {
    protected $jsonData = null;
    protected $ver = 0;
    protected $status = 1;

    const ERROR_PARAM = 40001;
    const ERROR_SAVE_DATA = 50001;

    /**
     * 过滤数据
     * @param $data
     * @param $filterKeys
     * @return array
     * @throws Exception
     */
    protected function filter($data, $filterKeys) {
        $result = [];
        foreach ($filterKeys as $key => $param) {
            if (isset($data[$param[0]])) {
                $val = $this->filterParam($data[$param[0]], $param[1]);
                if (is_null($val)) {
                    $this->error('filter fail:' . $param[0] . json_encode($param) . $data[$param[0]], self::ERROR_PARAM);
                } else {
                    $result[$key] = $val;
                }
            } elseif (isset($param[2])) {
                $result[$key] = $param[2];
            } else {
                $this->error('miss field:' . $param[0], self::ERROR_PARAM);
            }
        }
        return $result;
    }

    /**
     * 参数过滤
     * @param $val
     * @param $param
     * @return float|int|null
     */
    protected function filterParam($val, $param) {
        switch ($param[0]) {
            case "int":
                $result = empty($val) ? 0 : (is_numeric($val) ? intval($val) : NULL);
                break;
            case "float":
                $result = is_numeric($val) ? floatval($val) : NULL;
                break;
            case "in":
                $result = (in_array($val, $param[1])) ? $val : NULL;
                break;
            case "len":
                $result = (strlen($val) == $param[1]) ? $val : NULL;
                break;
            case "gt":
                $result = (strlen($val) > $param[1]) ? $val : NULL;
                break;
            case "maxLen":
                $result = (strlen($val) <= $param[1]) ? $val : NULL;
                break;
            case "sRange":
                $result = ($param[1][0] <= strlen($val) && strlen($val) <= $param[1][1]) ? $val : NULL;
                break;
            case "iRange":
                $result = ($param[1][0] <= $val && $val <= $param[1][1]) ? intval($val) : NULL;
                break;
            case "toInt":
                $result = intval($val);
                break;
            case "subStr":
                $result = substr($val, 0, $param[1]) ?: NULL;
                break;
            default:
                $result = NULL;
        }
        return $result;
    }

    /**
     * 错误处理
     * @param $errCode
     * @param string $msg
     * @param bool $quit
     */
    protected function error($msg = '', $errCode, $quit = true) {
        if (empty($msg)) {
            $msg = json_encode($this->jsonData);
        }
        if (rand(1, 100) < 10) {
            $logContent = date('H:i:s') . '|' . $errCode . '|' . $msg . '|' . json_encode($this->jsonData) . "\n";
            $typeSendToFile = 3;
            $fileName = 'stat_err_' . date('md') . '.log';
            $filePath = Common::getConfig('siteConfig', 'logPath') . $fileName;
            error_log($logContent, $typeSendToFile, $filePath);
        }
        $this->status = 0;
        if ($quit) {
            $this->output($errCode, $msg);
        }
    }

    public function __destruct() {
        $execTime = intval((microtime(true) - $this->sTime) * 1000);
        $status = $this->status ? 'ok' : 'fail';
        if ($this->ver) {
            $name = 'stat_v' . $this->ver . '_' . $status;
            Common::sendLogAccess(0, 'ads', $name, $status, $execTime);
        }
    }

}
