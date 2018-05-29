<?php

/**
 * Created by PhpStorm.
 * User: atom.zhan
 * Date: 2017/7/5
 * Time: 16:42
 */
class BaseController extends Stat_BaseController {
    private $data = '';//原始上报数据
    //monitor统计用
    protected $mReportType = 0;
    protected $mReportMsg = '';
    protected $mAppKey = '';


    const CHARGE_TYPE_CPM = 1;
    const CHARGE_TYPE_CPC = 2;

//    获取当前创意价格,类型
    private function getRequestInfo($requestId, $originalityId, $eventType) {
        $requestDate = Dedelivery_Service_OriginalityRelationModel::getOriginalityChargePriceByRequestId($requestId, $originalityId);
        if (empty($requestDate)) {
            $requestDate['price'] = 0;
            $requestDate['charge_type'] = 0;
            $this->errlog('getPrice', 0);
        } else {
            if (($requestDate['charge_type'] == Common_Service_Const::CHARGE_TYPE_CPM and $eventType != Common_Service_Const::EVENT_TYPE_VIEW) or ($requestDate['charge_type'] == Common_Service_Const::CHARGE_TYPE_CPC and $eventType != Common_Service_Const::EVENT_TYPE_CLICK)) {
                $data['price'] = 0;
            }
        }
        return $requestDate;
    }

    //上报数据入口
    public function collectAction() {
        $data = $this->getPostData();
        $data['ver'] = 1;
        //保存数据
        try {
            if (empty($data['bit_id']) and self::needCharge($data['event_type'])) {
                $requestDate = $this->getRequestInfo($data['bit_id'], $data['originality_id'], $data['event_type']);
                $data['price'] = $requestDate['price'];
                $data['charge_type'] = $requestDate['charge_type'];
                $this->saveChargeData($data);
            } else {
                $data['price'] = 0;
                $data['charge_type'] = 0;
            }
            $this->saveData($data);
            $this->mReportType = 'success';
            $this->mReportMsg = 'success';
            exit(json_encode([status => true, usedtime => $this->getUseTime('saveData'), msg => "ok", data => []]));
        } catch (Exception $e) {
            $this->errlog($e->getMessage());
        }
    }


//    保存扣费数据
    private function saveChargeData($data) {
        $cdata = array();
        $cdata['ad_id'] = intval($data['ad_id']);
        $cdata['request_id'] = $data['request_id'];
        $cdata['originality_id'] = $data['originality_id'];
        $cdata['ads_id'] = $data['dsp_id'];
        $cdata['uuid'] = trim($data['uuid']);
        $cdata['charge_type'] = intval($data['charge_type']);
        $cdata['event_type'] = intval($data['event_type']);
        $cdata['price'] = floatval($data['price']);
        $cdata['server_time'] = $data['server_time'];
        $redis = Common::getQueue('adx');
        $write = $redis->push('RQ:charge', $cdata);
        if ($write <= 0) {
            $this->errlog('saveChargeData');
        }
        return $write;
    }

//    保存上报数据
    private function saveData($data) {
        $redis = Common::getQueue('adx');
        $data['request_id'] = intval($data['re_id']);
        $data['originality_id'] = $data['originality_id'];
        $data['uuid'] = trim($data['uuid']);
        $data['event_type'] = intval($data['event_type']);
        $data['charge_type'] = intval($data['charge_type']);
        $data['price'] = floatval($data['price']);
        $data['server_id'] = intval(1000 + SERVER_ID);
        $data['ad_sub_type'] = -1;
        $data['used_time'] = 0;
        $data['vh'] = -1;
        $data['point_x'] = -1;
        $data['point_y'] = -1;
        $write = $redis->push('RQ:client', $data);
        if ($write <= 0) {
            $this->errlog('saveData');
        }
        return $write;
    }

    /**
     * 获取post参数
     * @param $fields
     * @return array
     */
    public function getPost($fields) {
        $return = [];
        foreach ($fields as $key => $value) {
            $return[$key] = Util_Filter::post($key);
        }
        return $return;
    }

    /**
     * 检查签名
     * @param $data
     * @param string $code
     * @return bool
     */
    private function chkSign($data, $code = "") {
        if (!empty($data['sign']) and $data['platform'] == 2 and version_compare($data['sdkVersion'], '0.2.0', '<=')) {
            return true;//iso sdkVersion<=0.2.0不做校验
        }

        if (!empty($data['sign'])) {
            return true;//开发阶段.先不做校验sign失败处理
            $sign = $data['sign'];
            unset($data['sign']);
            ksort($data);
            $signval = md5(html_entity_decode(implode('', $data) . $code));
            return $sign == $signval;
        }
        return false;
    }

//    获取上报数据
    private function getPostData() {
        $fields = [
            'sign' => ['*srange', '31:32'],
            'sspId' => ['*srange', '31:32'],
            'dspId' => ['*srange', '31:32'],
            'requestId' => [],
            'originalityId' => [],
            'appKey' => ['*srange', '19:20'],
            'posKey' => [],
            'adType' => ['*irange', '1:12'],
            'adSubType' => ['*irange', '1:12'],
            'brand' => [],
            'model' => [],
            'operator' => ['*irange', '1:4'],
            'netType' => ['*irange', '1:5'],
            'eventType' => ['*srange', '1:2'],
            'eventValue' => [],
            'imei' => [],
            'imsi' => [],
            'platform' => [],
            'uuid' => ['*s>', 0],
            'appVersion' => ['*s>', 0],
            'sdkVersion' => ['*s>', 0],
            'vh' => ['*s>', 0],
            'point_x' => ['*s>', 0],
            'point_y' => ['*s>', 0],
            'width' => ['*s>', 0],
            'height' => ['*s>', 0],
        ];
        $this->data = $this->getPost($fields);
//        $debug = Util_Filter::post("debug");
//        if (is_null($debug) and !$this->chkSign($this->data, Common::getConfig('siteConfig', 'secretKey'))) {
//            $this->errlog('sign');
//        }
//        if (is_null($debug) and !$this->checkAdxToken()) {
//            $this->errlog('sign');
//        }

        $return['server_time'] = time();
        $return['client_ip'] = Common::getClientIP();
        $return['price'] = 0;
        $return['charge_type'] = 0;

        foreach ($fields as $key => $field) {
            $newkey = Common::snakeCase($key);
            if (is_array($field)) {
                if (count($field) == 2) {
                    $return[$newkey] = Util_Filter::post($key, $field[0], $field[1]);
                } else {
                    $return[$newkey] = Util_Filter::post($key);
                }
            } else {
                $return[$newkey] = Util_Filter::post($key, null, null, $field);
            }
            if (is_null($return[$newkey])) {
                $this->errlog($key);
            }
        }
        $this->mAppKey = $return['appKey'];
        return $return;
    }


//    错误记录
    private function errlog($tag, $quit = 1) {
        $logContent = date('H:i:s') . "\t" . $tag . "\t" . json_encode($this->data, JSON_UNESCAPED_UNICODE) . "\n";
        $typeSendToFile = 3;
        $fileName = 'collect_err_' . date('md') . '.log';
        $filePath = Common::getConfig('siteConfig', 'logPath') . $fileName;
        error_log($logContent, $typeSendToFile, $filePath);
        $this->mReportType = 'error_' . $tag;
        $this->mReportMsg = $tag;
        if ($quit) {
            header("Content-type:text/json");
            exit(json_encode([status => false, msg => $tag, data => json_encode($this->data, JSON_UNESCAPED_UNICODE)]));
        }

    }

    public function __destruct() {
        $execTime = intval((microtime(true) - $this->sTime) * 1000);
        $module = $this->getRequest()->getModuleName();
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        $name = 'hosead_' . $module . '_' . $controller . '_' . $action . '_' . $this->mAppKey . '_' . $this->mReportType;
        Common::sendLogAccess(0, 'ads', $name, $this->mReportMsg, $execTime);
    }

}


