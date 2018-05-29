<?php if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * HouseAd_StatController
 * @author atom.zhan
 *
 */
class Housead_StatController extends Stat_BaseController {
    protected $ver = 2;
    private $jsonStr = '';//原始上报数据
    private $filterKeys = [
        //        'sign' => ['sign', ['len', 32]],
        'request_id' => ['requestId', ['maxLen', 32]],
        'originality_id' => ['originalityId', ['int'], 0],
        'ad_id' => ['adId', ['int']],
        'unit_id' => ['adUnitId', ['int']],
        'block_id' => ['blockId', ['maxLen', 64]],
        'app_key' => ['appKey', ['len', 20]],
        'ad_type' => ['adType', ['in', [1, 2, 3, 4, 5]]],
        'brand' => ['brand', ['maxLen', 32]],
        'model' => ['model', ['maxLen', 32]],
        'event_type' => ['eventType', ['iRange', [1, 99]]],
        'brand' => ['brand', ['maxLen', 32]],
        'model' => ['model', ['maxLen', 32]],
        'net_type' => ['netType', ['in', [1, 2, 3, 4, 5]]],
        'operator' => ['operator', ['in', [1, 2, 3, 4, 5]]],
        'platform' => ['platform', ['in', [1, 2]]],
        'resolution' => ['resolution', ['maxLen', 12]],
        'uuid' => ['uuid', ['maxLen', 64]],
        'imei' => ['imei', ['maxLen', 64]],
        'imsi' => ['imsi', ['maxLen', 15]],
        'app_version' => ['appVersion', ['maxLen', 15]],
        'sdk_version' => ['sdkVersion', ['maxLen', 15]],

    ];

    //    判断是否计费
    private function needCharge($type) {
        return in_array(intval($type), [Common_Service_Const::EVENT_TYPE_VIEW, Common_Service_Const::EVENT_TYPE_CLICK]);
    }

    //    获取当前创意价格
    private function getRequestInfo($requestId, $originalityId) {
        $requestDate = Dedelivery_Service_OriginalityRelationModel::getOriginalityChargePriceByRequestId($requestId, $originalityId);
        if (empty($requestDate)) {
            $requestDate['price'] = 0;
            $requestDate['charge_type'] = 0;
            //            throw new Exception('getPrice error', self::ERROR_PARAM);
        }
        return $requestDate;
    }

    /**
     * 上报数据入口
     */
    public function collectAction() {

        $data = $this->getPostData();
        if (intval($data['originality_id']) > 0 and $this->needCharge($data['event_type'])) {
            $eventType = $data['event_type'];
            $requestDate = $this->getRequestInfo($data['request_id'], $data['originality_id']);
            if (($requestDate['charge_type'] == Common_Service_Const::CHARGE_TYPE_CPM and $eventType == Stat_Service_OriginalDataModel::EVENT_TYPE_VIEW) or ($requestDate['charge_type'] == Common_Service_Const::CHARGE_TYPE_CPC and $eventType == Stat_Service_OriginalDataModel::EVENT_TYPE_CLICK)) {
                $data['price'] = $requestDate['price'];
                $data['charge_type'] = intval($requestDate['charge_type']);
            }
            $this->saveChargeData($data);
        }

        //保存数据
        $ret = $this->saveAdxData($data);
        if ($ret['ret'] == 0) {
            $this->output(1, 'ok');
        } else {
            $this->output(0, 'fail');
        }

    }

    /**
     * 获取上报数据
     * @return array
     */
    private function getPostData() {
        $fields = [
            'sign',
            'requestId',
            'originalityId',
            'adId',
            'adUnitId',
            'blockId',
            'appKey',
            'adType',
            'blockId',
            'brand',
            'model',
            'eventType',
            'imei',
            'imsi',
            'netType',
            'operator',
            'platform',
            'resolution',
            'uuid',
            'appVersion',
            'sdkVersion'
        ];
        $jsonData = $this->getPost($fields);
        $data = $this->filter($jsonData, $this->filterKeys);
        $data['client_ip'] = Common::getClientIP();
        $data['server_time'] = time();
        $data['price'] = 0;
        $data['charge_type'] = 0;
        return $data;
    }


    //    保存扣费数据
    private function saveChargeData($data) {
        $r_data = [];

        $r_data['ads_id'] = 'Mobgi';
        $r_data['ad_id'] = $data['ad_id'];
        $r_data['bid_id'] = $data['request_id'];
        $r_data['orig_id'] = $data['originality_id'];
        $r_data['dsp_id'] = $data['dsp_id'];
        $r_data['uuid'] = $data['uuid'];
        $r_data['imei'] = $data['imei'];
        $r_data['app_key'] = $data['app_key'];
        $r_data['pos_key'] = $data['block_id'];
        $r_data['ad_type'] = $data['ad_type'];
        $r_data['charge_type'] = $data['charge_type'];
        $r_data['event_type'] = $data['event_type'];
        $r_data['price'] = $data['price'];
        $r_data['server_time'] = $data['server_time'];
        $r_data['client_ip'] = $data['client_ip'];
        $r_data['ver'] = $this->ver;
        $redis = Common::getQueue('adx');
        return $redis->push('RQ:ad_charge', $r_data);
    }

    //  保存上报数据
    private function saveOriginalData($data) {
        $r_data = array();
        $r_data['ad_unit_id'] = $data['ad_unit_id'];
        $r_data['ad_id'] = $data['ad_id'];
        $r_data['originality_id'] = $data['originality_id'];
        $r_data['block_id'] = $data['block_id'];
        $r_data['app_key'] = $data['app_key'];
        $r_data['ad_type'] = $data['ad_type'];
        $r_data['brand'] = $data['brand'];
        $r_data['model'] = $data['model'];
        $r_data['event_type'] = $data['event_type'];
        $r_data['net_type'] = $data['event_type'];
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

        $r_data['ad_sub_type'] = -1;
        $r_data['used_time'] = 0;
        $r_data['vh'] = 0;
        $r_data['point_x'] = -1;
        $r_data['point_y'] = -1;
        $redis = Common::getQueue('adx');
        return $redis->push('RQ:housead_data_original', $r_data);

    }

    //  保存数据->Adx
    private function saveAdxData($data) {
        $r_data['ads_id'] = 'Mobgi';
        $r_data['bid_id'] = $data['request_id'];
        $r_data['orig_id'] = $data['originality_id'];
        $r_data['app_key'] = $data['app_key'];
        $r_data['pos_key'] = $data['block_id'];
        $r_data['ad_type'] = $data['ad_type'];
        $r_data['net_type'] = $data['net_type'];
        $r_data['cid'] = $data['cid'];
        $r_data['brand'] = $data['brand'];
        $r_data['model'] = $data['model'];
        $r_data['operator'] = $data['operator'];
        $r_data['event_type'] = $data['event_type'];
        $r_data['imei'] = $data['imei'];
        $r_data['imsi'] = $data['imsi'];
        $r_data['platform'] = $data['platform'];
        $r_data['uuid'] = $data['uuid'];
        $r_data['app_version'] = $data['app_version'];
        $r_data['sdk_version'] = $data['sdk_version'];
        $r_data['client_ip'] = $data['client_ip'];
        $r_data['server_time'] = $data['server_time'];
        $r_data['charge_type'] = $data['charge_type'];
        $r_data['currency'] = $data['currency'];
        $r_data['price'] = $data['price'];
        $r_data['ver'] = $this->ver;
        $redis = Common::getQueue('mobgi');
        return $redis->push('RQ:ad_client', $r_data);
    }


}
