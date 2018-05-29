<?php if (!defined('BASE_PATH')) exit('Access Denied!');

class Adx_V1_TestController extends Adx_BaseController {
    private $data = '';//原始上报数据
    protected $mReportType = 0;
    protected $mReportMsg = '';
    protected $mAppKey = '';

    const CHARGE_TYPE_CPM = 1;
    const CHARGE_TYPE_CPC = 2;

    const EVENT_TYPE_VIEW = 5;
    const EVENT_TYPE_CLICK = 6;

    public function init() {
        header("Content-type:text/json");
    }

    public function dspAction() {
        $jsonStr = file_get_contents("php://input");
        $data = json_decode($jsonStr, true);
        $result = array();
        foreach ($data as $key => $value) {
            $newkey = Common::snakeCase($key);
            $result[$newkey] = $value;
        }
        $result['server_time'] = time();
        $redis = Common::getQueue('adx');
        $write = $redis->push('RQ:adx_dsp_event', $result);
        if ($write <= 0) {
            $this->errlog('saveData');
        } else {
            $this->output(0, 'ok', $result);
        }
    }

    /**
     * 上报数据入口
     */
    public function collectAction() {
        try {
            $data = $this->getPostData();
            if ($data['dsp_id'] == "housead" && in_array(intval($data['event_type']), [self::EVENT_TYPE_VIEW, self::EVENT_TYPE_CLICK])) {
                $eventType = intval($data['event_type']);
                $requestDate = $this->getRequestInfo($data['bid_id'], $data['originality_id']);//原来request_id改为bid_id
                if (($requestDate['charge_type'] == self::CHARGE_TYPE_CPM and $eventType == Stat_Service_OriginalDataModel::EVENT_TYPE_VIEW) or ($requestDate['charge_type'] == self::CHARGE_TYPE_CPC and $eventType == Stat_Service_OriginalDataModel::EVENT_TYPE_CLICK)) {
                    $data['price'] = $requestDate['price'];
                } else {
                    $data['price'] = 0;
                }
                $data['charge_type'] = $requestDate['charge_type'];
                $this->saveChargeData($data);
            } else {
                $data['price'] = 0;
                $data['charge_type'] = 0;
            }
            $this->saveData($data);
            $this->output(0, 'ok', []);
        } catch (Exception $e) {
            $this->errlog('collect');
        }
    }

    /**
     * 保存上报数据
     * @param array $data
     * @return int
     */
    private function saveData($data) {
        $redis = Common::getQueue('adx');
        $data['ad_id'] = intval($data['ad_id']);
        $data['uuid'] = trim($data['uuid']);
        $data['event_type'] = intval($data['event_type']);
        $data['charge_type'] = intval($data['charge_type']);
        $write = $redis->push('RQ:adx_data', $data);//push到列表内
        if ($write <= 0) {
            $this->errlog('saveData');
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
        #$jsonStr = '{"providerId":"1","bidId":"1","outBidid":"2","dspId":"1","adId":"4","originalityId":"8","adUnitId":"1","blockId":"SMTW2_GO_F2","appKey":"aaaaa11111ccccc33333","adType":"1","imei":"284011234567890","brand":"Huawei","model":"H60-L01","eventType":"05","imsi":"12345678","netType":"1","operator":"1","platform":"1","resolution":"768X1184","uuid":"ffffffff-bc51-99cb-1a4d-3b7b37a78b65","appVersion":"2.4.0","sdkVersion":"1.5.6","Price":"0.03","currency":"RMB","chargeType":"1"}';
        $data = json_decode($jsonStr);
        $return = $this->filterParams($data, $var);
        return $return;
    }

    /**
     * 参数校验
     */
    private function filterParams($data, $var) {
        if (empty($data) && empty($val)) return NULL;
        $fitter_result = array();
        foreach ($data as $key => $val) {
            //驼峰过滤
            $key = Common::snakeCase($key);
            if (!empty($var[$key])) {
                $fitter_result[$key] = $this->checkfilterParams($val, $var[$key]);
            } else {
                $fitter_result[$key] = '-1';
            }
            if ($fitter_result[$key] == NULL) {
                $this->errlog('filter');
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
            case "irange":
                $result = ($param[0] <= $val && $val <= $param[1]) ? $val : NULL;
                break;
            case "num":
                $fitter = empty($param) ? "/^\d*$/" : $param;
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
            'providerId' => [],//sspId
            'dspId' => [],
            'originality_id' => [],
            'bidId' => ["slength", 32],
            'outBidid' => [],
            'adUnitId' => [],
            'adId' => [],
            'blockId' => [],
            'appKey' => ['slength', 20],
            'adType' => ['irange', [1, 12]],
            'eventType' => ['slength', 2],
            'brand' => [],
            'model' => [],
            'imei' => [],
            'imsi' => [],
            'netType' => ['irange', [0, 5]],
            'operator' => ['irange', [1, 4]],
            'platform' => ['irange', [1, 2]],
            'resolution' => [],
            'appVersion' => [],
            'sdkVersion' => [],
            'uuid' => [],
            'price' => [],
            'currency' => [],
            'chargeType' => [],
        ];
        $this->data = $this->getPost($fields);

        $debug = NULL;
        if (is_null($debug) and !$this->checkAdxToken()) {
            $this->errlog('sign');
        }
        $return = $this->data;
        $return['created_time'] = time();
        $return['client_ip'] = Common::getClientIP();
        $this->mAppKey = $return['appKey'];
        return $return;
    }


    /**
     * 错误记录
     * @param string $tag
     * @param int $quit
     */
    private function errlog($tag = '', $quit = false) {
        $logContent = date('H:i:s') . "\t" . $tag . "\t" . json_encode($this->data, JSON_UNESCAPED_UNICODE) . "\n";
        $typeSendToFile = 3;
        $fileName = 'collect_err_' . date('md') . '.log';
        $filePath = Common::getConfig('siteConfig', 'logPath') . $fileName;
        error_log($logContent, $typeSendToFile, $filePath);
        $this->mReportType = 'error_' . $tag;
        $this->mReportMsg = $tag;
        if ($quit) {
            $this->output(0, $tag, json_encode($this->data, JSON_UNESCAPED_UNICODE));
        }
    }


//    获取当前创意价格
    private function getRequestInfo($requestId, $originalityId) {
        $requestDate = Dedelivery_Service_OriginalityRelationModel::getOriginalityChargePriceByRequestId($requestId, $originalityId);
        if (empty($requestDate)) {
            $requestDate['price'] = 0;
            $requestDate['charge_type'] = 0;
            $this->errlog('getPrice', 0);
        }
        return $requestDate;
    }


    //    保存扣费数据
    private function saveChargeData($data) {
        $cdata = array();
        $cdata['ad_id'] = intval($data['ad_id']);
        $cdata['request_id'] = $data['request_id'];
        $cdata['originality_id'] = $data['originality_id'];
        $cdata['uuid'] = trim($data['uuid']);
        $cdata['charge_type'] = intval($data['charge_type']);
        $cdata['event_type'] = intval($data['event_type']);
        $cdata['price'] = floatval($data['price']);
        $cdata['created_time'] = $data['created_time'];
        $redis = Common::getQueue('stat');
        $write = $redis->push('RQ:housead_data_charge', $cdata);
        if ($write <= 0) {
            $this->errlog('saveChargeData');
        }
        return $write;
    }

}
