<?php if (!defined('BASE_PATH')) exit('Access Denied!');

class StatController extends Adx_BaseController {
    private $data = '';//原始上报数据
    protected $mReportType = 0;
    protected $mReportMsg = '';
    protected $mAppKey = '';


    public function init() {
        header("Content-type:text/json");
    }

    /**
     * 上报数据入口
     */
    public function collectAction() {
        try {
            $this->checkAdxToken();
            $data = $this->getPostData();
            if (in_array(intval($data['event_type']), [
                    Common_Service_Const::EVENT_TYPE_VIEW,
                    Common_Service_Const::EVENT_TYPE_CLICK
                ]) and !empty($data['request_id'])
            ) {
                $cdata = array();
                $cdata['originality_id'] = ($data['dsp_id'] == 'Housead_DSP' or $data['dsp_id'] == 'Mobgi') ? intval($data['originality_id']) : 0;
                $cdata['ads_id'] = $data['dsp_id'];
                $cdata['uuid'] = trim($data['uuid']);
                $cdata['charge_type'] = intval($data['charge_type']);
                $cdata['event_type'] = intval($data['event_type']);
                $cdata['price'] = floatval($data['price']);
                $this->saveChargeData($cdata);
                $data['charge_type'] = $cdata['charge_type'];
                $data['currency'] = intval($cdata['currency']);
                $data['price'] = $cdata['price'];
            } else {
                $data['charge_type'] = 0;
                $data['currency'] = 0;
                $data['price'] = 0;
            }
            $this->saveData($data);
            $this->output(0, 'ok', []);
        } catch (Exception $e) {
            $this->errlog(50001, $e);
        }
    }

    /**
     * 保存上报数据
     * @param array $data
     * @return int
     */
    private function saveData($data) {
        $redis = Common::getQueue('adx');
        if (!empty($data['ssp_type']) && $data['ssp_type'] == 2) {
            $data['provider_id'] = 2;
        }
        $data['ad_id'] = intval($data['ad_id']);
        $data['uuid'] = trim($data['uuid']);
        $data['ver'] = Common_Service_Const::STAT_MOBGI;
        $write = $redis->push('RQ:stat', $data);
        //push到列表内
        if ($write <= 0) {
            $this->errlog(50002, 'save the data error');
        }
        return $write;
    }

    /**
     * 获取请求参数
     * @param $var
     * @return array
     */ #$jsonStr = '{"sign":"90c7001b538a79e9042ffb915bb7efb0","providerId":"1","bidId":"1","outBidid":"2","dspId":"1","adId":"4","originalityId":"8","adUnitId":"1","blockId":"SMTW2_GO_F2","appKey":"aaaaa11111ccccc33333","adType":"1","imei":"284011234567890","brand":"Huawei","model":"H60-L01","eventType":"05","imsi":"12345678","netType":"1","operator":"1","platform":"1","resolution":"768X1184","uuid":"ffffffff-bc51-99cb-1a4d-3b7b37a78b65","appVersion":"2.4.0","sdkVersion":"1.5.6"}';

    public function getPost($var) {
        $jsonStr = file_get_contents('php://input');
        if (!Common::is_json($jsonStr)) {
            $this->errlog(31005, 'invalid post input format', $jsonStr, True);
        }
        #$jsonStr = '{"providerId":"1","bidId":"1","outBidid":"2","dspId":"1","adId":"4","originalityId":"8","adUnitId":"1","blockId":"SMTW2_GO_F2","appKey":"aaaaa11111ccccc33333","adType":"1","imei":"284011234567890","brand":"Huawei","model":"H60-L01","eventType":"05","imsi":"12345678","netType":"1","operator":"1","platform":"1","resolution":"768X1184","uuid":"ffffffff-bc51-99cb-1a4d-3b7b37a78b65","appVersion":"2.4.0","sdkVersion":"1.5.6","Price":"0.03","currency":"RMB","chargeType":"1"}';
        $data = json_decode($jsonStr, true);
        $return = $this->filterParams($data, $var);
        return $return;
    }

    /**
     * 参数校验
     */
    private function filterParams($data, $var) {
        if (empty($data) && empty($val)) return NULL;
        $fitter_result = array();
        #ADX判断校验
        if ($data['sspType'] == 1 && in_array($data['eventType'], array(3, 4, 5, 6, 7, 8, 10, 11, 12, 13, 14, 41, 42, 43, 44))) {
            if ($data['bidId'] == '-1' || $data['outBidid'] == '-1') {
                $this->errlog(50007, 'sspType filter error', json_encode($data), true);
            }
        }
        #广告商过滤
        if ($data['dspId'] == -1 && in_array($data['eventType'], array(3, 4, 5, 6, 7, 8, 10, 11, 12, 13, 14, 41, 42, 43, 44))) {
            $this->errlog(50006, "dspID and eventType error!", $data['dspId'], true);
        }
        foreach ($data as $key => $val) {
            //驼峰过滤
            $changeKey = Common::snakeCase($key);
            if (isset($var[$key])) {
                $fitter_result[$changeKey] = $this->checkfilterParams($val, $var[$key]);
            } else {
                $fitter_result[$changeKey] = -1;
            }
            if ($fitter_result[$changeKey] === NULL) {
                $this->errlog(50003, 'filter error', "[" . json_encode($key . ":" . $val) . "]", true);
            }
        }
        $addKey = array_diff_key($var, $data);
        if (!empty($addKey)) {
            foreach ($addKey as $keys => $vals) {
                $fitter_result[Common::snakeCase($keys)] = -1;
            }
        }
        return $fitter_result;
    }

    /*
     * 参数过滤，如果不成功返回NULL
     */

    private function checkfilterParams($val, $fitter) {
        //检测fitter参数有多少
        for ($i = 0; $i <= count($fitter) - 1; $i += 2) {
            $val = $this->filter($val, $fitter[$i], $fitter[$i + 1]);
        }
        return $val;
    }

    /*
     * 参数过滤
     */
    private function filter($val, $type, $param) {
        switch ($type) {
            case "slength":
                $result = ($param == strlen($val)) ? $val : NULL;
                break;
            case "srange":
                $result = ($param[0] <= strlen($val) && strlen($val) <= $param[1]) ? $val : NULL;
                break;
            case "irange":
                $result = ($param[0] <= $val && $val <= $param[1]) ? $val : NULL;
                break;
            case "num":
                $fitter = empty($param) ? '/^\d*$/' : $param;
                $result = preg_match($fitter, $val) ? $val : NULL;
                break;
            case "letter":
                $fitter = empty($param) ? "^[A-Za-z]+$" : $param;
                $result = preg_match($fitter, $val) ? $val : NULL;
                break;
            case "num&letter":
                $fitter = empty($param) ? "/^(([a-z]+[0-9]+)|([0-9]+[a-z]+))[a-z0-9]*$/i" : $param;
                $result = preg_match($fitter, $val) ? $val : NULL;
                break;
            case "fitter":
                $result = preg_match($param, $val) ? $val : NULL;
                break;
            default:
                $result = NULL;
        }
        return $result;
    }

    /**
     * 获取上报数据
     * @return array mixed
     */
    private function getPostData() {
        #过滤支持多重过滤格式['过滤的类型','参数']
        $fields = [
            'sspId' => ['irange', [1, 2]],
            'dspId' => [],
            'requestId' => [],
            'originalityId' => [],
            'posKey' => [],
            'appKey' => ['slength', 20],
            'adType' => ['irange', array_keys(Common_Service_Config::AD_TYPE)],
            'adSubType' => [],
            'cid' => [],
            'brand' => [],
            'model' => [],
            'operator' => ['irange', [0, 8]],
            'netType' => ['irange', [0, 5]],
            'eventType' => ['irange', [1, 99]],
            'eventValue' => [],
            'imei' => [],
            'imsi' => [],
            'platform' => ['irange', array_keys(Common_Service_Config::PLATFORM)],
            'uuid' => [],
            'appVersion' => [],
            'sdkVersion' => [],
            'vh' => [],
            'pointX' => [],
            'pointY' => [],
            'width' => [],
            'height' => [],
        ];

        $this->data = $this->getPost($fields);
        /*$debug = NULL;
        if (is_null($debug) and !$this->checkAdxToken()) {
            $this->errlog('sign');
        }*/
        $return = $this->data;
        $return['server_time'] = time();
        $return['client_ip'] = Common::getClientIP();


        $this->mAppKey = $return['appKey'];
        return $return;
    }


    /**
     * 错误记录啊
     * @param string $tag
     * @param int $quit
     */
    private function errlog($errNum, $tag = '', $msg = '', $quit = false) {
        if (empty($msg)) {
            $logContent = date('H:i:s') . "|" . $errNum . "|" . $tag . "|" . json_encode($this->data, JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            $logContent = date('H:i:s') . "|" . $errNum . "|" . $tag . '|' . $msg . "\n";
        }
        $typeSendToFile = 3;
        $fileName = 'collect_err_' . date('md') . '.log';
        $filePath = Common::getConfig('siteConfig', 'logPath') . $fileName;
        error_log($logContent, $typeSendToFile, $filePath);
        if ($quit) {
            if (empty($msg)) {
                $this->output($errNum, $tag, json_encode($this->data, JSON_UNESCAPED_UNICODE));
            } else {
                $this->output($errNum, $tag, $msg);
            }
        }
    }


    //    获取当前创意价格
    private function getRequestInfo($requestId, $originalityId) {
        $requestDate = Dedelivery_Service_OriginalityRelationModel::getOriginalityChargePriceByRequestId($requestId, $originalityId);
        if (empty($requestDate)) {
            $requestDate['price'] = 0;
            $requestDate['charge_type'] = 0;
            $this->errlog(50004, 'getPrice error', '', True);
        }
        return $requestDate;
    }


    //    保存扣费数据
    private function saveChargeData($data) {
        $data['created_time'] = time();
        $redis = Common::getQueue('adx');
        $write = $redis->push('RQ:adx_charge', $data);
        if ($write <= 0) {
            $this->errlog(50005, 'save the saveChargeData error', '', Ture);
        }
        return $write;
    }

    //    保存housead统计数据
    private function saveOriginalData($data) {
        $redis = Common::getQueue('adx');
        $data['ad_id'] = intval($data['ad_id']);
        $data['originality_id'] = $data['originality_id'];
        $data['uuid'] = trim($data['uuid']);
        $data['event_type'] = intval($data['event_type']);
        $data['charge_type'] = intval($data['charge_type']);
        $data['price'] = floatval($data['price']);
        $data['created_time'] = time();
        $write = $redis->push('RQ:housead_data_original', $data);
        if ($write <= 0) {
            $this->errlog('saveData');
        }
        return $write;
    }
}
