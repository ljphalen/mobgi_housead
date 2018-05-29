<?php
/**
 * User: atom.zhan
 * Date: 2016/9/18
 * Time: 15:03
 */
if (!defined('BASE_PATH')) exit('Access Denied!');


class DataController extends Stat_BaseController {
    private $data = '';//原始上报数据
    protected $mReportType = 0;
    protected $mReportMsg = '';
    protected $mAppKey = '';


    private function needCharge($type) {
        return in_array(intval($type), [Common_Service_Const::EVENT_TYPE_VIEW, Common_Service_Const::EVENT_TYPE_CLICK]);
    }

    //housead上报数据入口
    public function collectAction() {

        $data = $this->getPostData();
        //保存数据
        try {
            $data['ads_id'] = 'Housead_DSP';
            if ($this->needCharge($data['event_type'])) {
                $eventType = intval($data['event_type']);
                $requestData = $this->getRequestInfo($data['request_id'], $data['originality_id']);
                if (($requestData['charge_type'] == Common_Service_Const::CHARGE_TYPE_CPM and $eventType == Common_Service_Const::EVENT_TYPE_VIEW) or ($requestData['charge_type'] == Common_Service_Const::CHARGE_TYPE_CPC and $eventType == Common_Service_Const::EVENT_TYPE_CLICK)) {
                    $data['price'] = $requestData['price'];
                } else {
                    $data['price'] = 0;
                }
                $data['charge_type'] = $requestData['charge_type'];
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
        $cdata['dsp_id'] = $data['dsp_id'];
        $cdata['uuid'] = trim($data['uuid']);
        $cdata['charge_type'] = intval($data['charge_type']);
        $cdata['event_type'] = intval($data['event_type']);
        $cdata['price'] = floatval($data['price']);
        $cdata['created_time'] = $data['created_time'];
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
        $data['ad_id'] = intval($data['ad_id']);
        $data['request_id'] = $data['originality_id'];
        $data['originality_id'] = $data['originality_id'];
        $data['uuid'] = trim($data['uuid']);
        $data['event_type'] = intval($data['event_type']);
        $data['charge_type'] = intval($data['charge_type']);
        $data['price'] = floatval($data['price']);
        $data['ver'] = Common_Service_Const::STAT_HOUSEAD;
        $data['ad_sub_type'] = -1;
        $data['used_time'] = 0;
        $data['vh'] = 0;
        $data['point_x'] = -1;
        $data['point_y'] = -1;
        $write = $redis->push('RQ:housead_data_original', $data);
        if ($write <= 0) {
            $this->errlog('saveData');
        }
        return $write;
    }

    /**
     * 获取post参数
     * @param array $var
     * @return array
     */
    public function getPost($var) {
        $return = [];
        foreach ($var as $key => $value) {
            $return[$key] = Util_Filter::post($key);
        }
        return $return;
    }

//    检查签名
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
            'requestId' => [],
            'originalityId' => [],
            'adId' => [],
            'adUnitId' => [],
            'blockId' => [],
            'appKey' => ['*srange', '19:20'],
            'adType' => ['*irange', '1:12'],
            'blockId' => [],
            'brand' => [],
            'model' => [],
            'eventType' => ['*srange', '1:2'],
            'imei' => [],
            'imsi' => [],
            'netType' => ['*irange', '1:5'],
            'operator' => ['*irange', '0:4'],
            'platform' => ['*irange', '1:2'],
            'resolution' => ['*s>', 0],
            'uuid' => ['*s>', 0],
            'appVersion' => ['*s>', 0],
            'sdkVersion' => ['*s>', 0]
        ];
        $this->data = $this->getPost($fields);
        $debug = Util_Filter::post("debug");
        if (is_null($debug) and !$this->chkSign($this->data, Common::getConfig('siteConfig', 'secretKey'))) {
            $this->errlog('sign');
        }
        $return['created_time'] = time();
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


