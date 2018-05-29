<?php if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Adx_V1_StatController
 * @author atom.zhan
 *
 */
class Adx_V1_StatController extends Stat_BaseController {
    protected $ver = 4;
    private $jsonStr = '';//原始上报数据
    private $filterKeys = [
        'provider_id' => ['providerId', ['toInt'], 1],
        'dsp_id' => ['dspId', ['subStr', 32], '-1'],
        'bid_id' => ['bidId', ['subStr', 32], '-1'],
        'out_bid_id' => ['outBidId', ['subStr', 64], '-1'],
        'ad_id' => ['adId', ['toInt'], 0],
        'originality_id' => ['originalityId', ['toInt'], 0],
        'app_key' => ['appKey', ['len', 20]],
        'block_id' => ['blockId', ['maxLen', 64], '-1'],
        'cid' => ['cid', ['maxLen', 12], '-1'],
        'ad_type' => ['adType', ['in', [0, 1, 2, 3, 4, 5]]],
        'ad_sub_type' => ['adSubType', ['iRange', [0, 99]], 0],
        'event_type' => ['eventType', ['iRange', [1, 9999]]],
        'event_sort' => ['eventSort', ['toInt'], 0],
        'event_time' => ['eventTime', ['toInt'], 0],
        'event_value' => ['eventValue', ['maxLen', 300], 0],
        'used_time' => ['usedTime', ['int'], 0],
        'brand' => ['brand', ['subStr', 32]],
        'model' => ['model', ['subStr', 32]],
        'imei' => ['imei', ['maxLen', 64]],
        'imsi' => ['imsi', ['maxLen', 15]],
        'uuid' => ['uuid', ['maxLen', 64]],
        'net_type' => ['netType', ['iRange', [0, 9]]],
        'operator' => ['operator', ['iRange', [0, 9]], 0],
        'platform' => ['platform', ['in', [1, 2]]],
        'app_version' => ['appVersion', ['maxLen', 15]],
        'sdk_version' => ['sdkVersion', ['maxLen', 15]],
        'resolution' => ['resolution', ['maxLen', 11]],
        'price' => ['price', ['float'], 0],
        'currency' => ['currency', ['iRange', [-1, 4]], 0],
        'charge_type' => ['chargeType', ['iRange', [-1, 9]], 0],
        'vh' => ['vh', ['in', [-1, 0, 1, 2]], 0],
        'point_x' => ['pointX', ['int'], -1],
        'point_y' => ['pointY', ['int'], -1],
        'session_id' => ['sessionId', ['len', 32], 0],
        'client_time' => ['clientTime', ['toInt'], 0],
        'user_type' => ['userType', ['toInt'], 0],
        'config_id' => ['configId', ['toInt'], 0],
    ];

    //    判断是否计费
    private function needCharge($type) {
        return in_array(intval($type), [
            Common_Service_Const::EVENT_TYPE_VIEW,
            Common_Service_Const::EVENT_TYPE_CLICK,
            Common_Service_Const::EVENT_TYPE_ACTIVE
        ]);
    }

    //    获取当前创意价格
    private function getRequestInfo($requestId, $originalityId) {
        $requestDate = Dedelivery_Service_OriginalityRelationModel::getOriginalityChargePriceByRequestId($requestId, $originalityId);
        if (empty($requestDate)) {
            $requestDate['price'] = 0;
            $requestDate['charge_type'] = 0;
            $this->error('getPrice error:' . $requestId . '' . $originalityId, self::ERROR_PARAM);
        }
        return $requestDate;
    }

    /**
     * 上报数据入口
     */
    public function collectAction() {

        $this->checkAdxToken();
        $data = $this->getPostData();
        if (intval($data['originality_id']) > 0 and $this->needCharge($data['event_type'])) {
            if (!(($data['charge_type'] == Common_Service_Const::CHARGE_TYPE_CPM and $data['event_type'] == Stat_Service_OriginalDataModel::EVENT_TYPE_VIEW) or ($data['charge_type'] == Common_Service_Const::CHARGE_TYPE_CPC and $data['event_type'] == Stat_Service_OriginalDataModel::EVENT_TYPE_CLICK))) {
                $data['price'] = 0;
            }
            $this->saveChargeData($data);
        } else {
            $data['price'] = 0;
        }
        //保存数据
        $ret = $this->saveAdxData($data);
        if ($ret['ret'] == 0) {
            $this->output(0, 'ok');
        } else {
            $this->output(0, 'fail');
        }
    }


    /**
     * 获取请求参数
     * @return array
     * @throws Exception
     */
    public function getJsonPost() {
        $this->jsonStr = file_get_contents('php://input');
        if (!Common::is_json($this->jsonStr)) {
            $this->error('input format error', self::ERROR_PARAM);
        }
        return json_decode($this->jsonStr, true);
    }

    /**
     * 获取上报数据
     * @return array|null
     * @throws Exception
     */
    private function getPostData() {
        $this->jsonData = $this->getJsonPost();
        if (is_null($this->jsonData['appKey']) and $this->jsonData['platform'] == 2 and $this->jsonData['appVersion'] == '3.7.0' and $this->jsonData['sdkVersion'] == '2.5.0') {
            $this->jsonData['appKey'] = '9372E882638D9933786F';
        }
        $data = $this->filter($this->jsonData, $this->filterKeys);
        #广告商事件校验
        if (empty($data['dsp_id']) && in_array($data['event_type'], array(3, 4, 5, 6, 7, 8, 10, 11, 12, 13, 14, 16, 41, 42, 43, 44))) {
            $this->error('event_type need ads#' . $data['event_type'], self::ERROR_PARAM);
        }

        if ($data['event_type'] > 999) {
            $this->output(0, 'skip');
        }
        $data['ad_unit_id'] = 0;
        $data['client_ip'] = Common::getClientIP();
        $data['server_time'] = time();
        return $data;
    }


    //  保存扣费数据
    private function saveChargeData($data) {
        $r_data = [];
        $r_data['ad_id'] = $data['ad_id'];
        $r_data['bid_id'] = $data['bid_id'];
        $r_data['orig_id'] = $data['originality_id'];
        $r_data['ads_id'] = $data['dsp_id'];
        $r_data['uuid'] = $data['uuid'];
        $r_data['imei'] = $data['imei'];
        $r_data['app_key'] = $data['app_key'];
        $r_data['pos_key'] = $data['block_id'];
        $r_data['ad_type'] = $data['ad_type'];
        $r_data['event_type'] = $data['event_type'];
        $r_data['client_ip'] = $data['client_ip'];
        $r_data['server_time'] = $data['server_time'];
        $r_data['charge_type'] = $data['charge_type'];
        $r_data['price'] = $data['price'];
        $r_data['ver'] = $this->ver;
        $redis = Common::getQueue('adx');
        return $redis->push('RQ:ad_charge', $r_data);

    }

    //  保存变现数据
    private function saveOriginalData($data) {
        $r_data['ad_unit_id'] = $data['ad_unit_id'];
        $r_data['ad_id'] = $data['ad_id'];
        $r_data['originality_id'] = $data['originality_id'];
        $r_data['block_id'] = $data['block_id'];
        $r_data['app_key'] = $data['app_key'];
        $r_data['ad_type'] = $data['ad_type'];
        $r_data['ad_sub_type'] = $data['ad_sub_type'];
        $r_data['brand'] = $data['brand'];
        $r_data['model'] = $data['model'];
        $r_data['event_type'] = $data['event_type'];
        $r_data['net_type'] = $data['net_type'];
        $r_data['charge_type'] = $data['charge_type'];
        $r_data['price'] = $data['price'];
        $r_data['imei'] = $data['imei'];
        $r_data['imsi'] = $data['imsi'];
        $r_data['operator'] = $data['operator'];
        $r_data['platform'] = $data['platform'];
        $r_data['resolution'] = $data['resolution'];
        $r_data['uuid'] = $data['uuid'];
        $r_data['app_version'] = $data['app_version'];
        $r_data['sdk_version'] = $data['sdk_version'];
        $r_data['client_ip'] = $data['client_ip'];
        $r_data['created_time'] = $data['server_time'];
        $r_data['vh'] = $data['vh'];
        $r_data['point_x'] = $data['point_x'];
        $r_data['point_y'] = $data['point_y'];
        $r_data['used_time'] = $data['used_time'];
        $redis = Common::getQueue('adx');
        return $redis->push('RQ:housead_data_original', $r_data);
    }


    //  保存上报数据
    private function saveData($r_data) {
        $r_data['out_bid_id'] = '-1';
        $r_data['dsp_version'] = '-1';
        $redis = Common::getQueue('adx');
        return $redis->push('RQ:adx_data', $r_data);
    }

    //  保存数据->Adx
    private function saveAdxData($data) {
        $r_data['bid_id'] = $data['bid_id'];
        $r_data['orig_id'] = $data['originality_id'];
        $r_data['ads_id'] = $data['dsp_id'];
        $r_data['ssp_id'] = $data['provider_id'];
        $r_data['app_key'] = $data['app_key'];
        $r_data['pos_key'] = $data['block_id'];
        $r_data['ad_type'] = $data['ad_type'];
        $r_data['ad_sub_type'] = $data['ad_sub_type'];
        $r_data['cid'] = $data['cid'];
        $r_data['brand'] = $data['brand'];
        $r_data['model'] = $data['model'];
        $r_data['operator'] = $data['operator'];
        $r_data['event_type'] = $data['event_type'];
        $r_data['event_sort'] = $data['event_sort'];
        $r_data['event_time'] = $data['event_time'];
        $r_data['event_value'] = $data['event_value'];
        $r_data['used_time'] = $data['used_time'];
        $r_data['price'] = $data['price'];
        $r_data['currency'] = $data['currency'];
        $r_data['charge_type'] = $data['charge_type'];

        $r_data['user_type'] = $data['user_type'];
        $r_data['config_id'] = $data['config_id'];


        $r_data['net_type'] = $data['net_type'];
        $r_data['imei'] = $data['imei'];
        $r_data['imsi'] = $data['imsi'];
        $r_data['platform'] = $data['platform'];
        $r_data['uuid'] = $data['uuid'];
        $r_data['app_version'] = $data['app_version'];
        $r_data['sdk_version'] = $data['sdk_version'];
        $r_data['client_ip'] = $data['client_ip'];
        $r_data['server_time'] = $data['server_time'];
        $r_data['client_time'] = $data['client_time'];
        $r_data['vh'] = $data['vh'];
        $r_data['point_x'] = $data['point_x'];
        $r_data['point_y'] = $data['point_y'];
        $r_data['session_id'] = $data['session_id'];
        if (isset($r_data['resolution']) and strpos('*', $r_data['resolution']) > 0) {
            list($r_data['width'], $r_data['height']) = explode('*', $data['resolution']);
        }
        $r_data['ver'] = $this->ver;
        $redis = Common::getQueue('mobgi');
        return $redis->push('RQ:ad_client', $r_data);
    }


}
