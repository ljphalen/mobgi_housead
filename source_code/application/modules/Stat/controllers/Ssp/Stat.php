<?php if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Ssp_StatController
 * @author atom.zhan
 *
 */
class Ssp_StatController extends Stat_BaseController {
    protected $ver = 11;
    private $filterKeys = [
        'ssp_id' => ['providerId', ['gt', 1]],
        'ads_id' => ['dspId', ['maxLen', 32], 0],
        'bid_id' => ['bidId', ['maxLen', 32]],
        'out_bid_id' => ['outBidId', ['subStr', 64], '-1'],
        'ad_id' => ['adId', ['toInt'], 0],
        'originality_id' => ['originalityId', ['toInt'], 0],
        'app_key' => ['appKey', ['len', 20]],
        'pos_key' => ['blockId', ['maxLen', 64], '-1'],
        'cid' => ['cid', ['maxLen', 12], ''],
        'ad_type' => ['adType', ['in', [0, 1, 2, 3, 4, 5]]],
        'ad_sub_type' => ['adSubType', ['iRange', [0, 99]], 0],
        'event_type' => ['eventType', ['iRange', [1, 9999]]],
        'brand' => ['brand', ['subStr', 32]],
        'model' => ['model', ['subStr', 32]],
        'imei' => ['imei', ['maxLen', 64]],
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
        'client_time' => ['clientTime', ['toInt'], 0],
    ];

    //    判断是否计费
    private function needCharge($type) {
        return in_array(intval($type), [
            Common_Service_Const::EVENT_TYPE_VIEW,
            Common_Service_Const::EVENT_TYPE_CLICK,
            Common_Service_Const::EVENT_TYPE_ACTIVE
        ]);
    }

    /**
     * 上报数据入口
     */
    public function collectAction() {
        $data = $this->getData();
        if (intval($data['originality_id']) > 0 and $this->needCharge($data['event_type'])) {
            if (!(($data['charge_type'] == Common_Service_Const::CHARGE_TYPE_CPM and $data['event_type'] == Stat_Service_OriginalDataModel::EVENT_TYPE_VIEW) or ($data['charge_type'] == Common_Service_Const::CHARGE_TYPE_CPC and $data['event_type'] == Stat_Service_OriginalDataModel::EVENT_TYPE_CLICK))) {
                $data['price'] = 0;
            }
            $this->saveChargeData($data);
        } else {
            $data['price'] = 0;
        }
        //保存数据
        $ret = $this->saveSspData($data);
        if ($ret['ret'] == 0) {
            $this->output(0, 'ok');
        } else {
            $this->output(self::ERROR_SAVE_DATA, 'fail');
        }
    }

    /**
     * 校验数据
     * @return array
     * @throws Exception
     */
    public function checkToken() {
//        return json_decode($this->jsonStr, true);
    }

    /**
     * 获取请求参数
     * @return array
     * @throws Exception
     */
    public function getInputData() {
        $inputKey = [];
        foreach ($this->filterKeys as $item) {
            $inputKey[] = $item[0];
        }
        $inputKey[] = 'sign';
        $data = $this->getGet($inputKey);

        $this->checkSspToken($data);
        return $data;
    }

    /**
     * 获取上报数据
     * @return array
     */
    private function getData() {
        $this->jsonData = $this->getInputData();
        $data = $this->filter($this->jsonData, $this->filterKeys);
        if ($data['event_type'] > 999) {
            $this->output(self::ERROR_PARAM, 'skip');
        }
        if (isset($data['resolution']) and strpos('*', $data['resolution']) > 0) {
            list($data['width'], $data['height']) = explode('*', $data['resolution']);
            unset($data['resolution']);
        }
        $data['ad_unit_id'] = 0;
        $data['client_ip'] = Common::getClientIP();
        $data['server_time'] = time();
        $data['ver'] = $this->ver;
        return $data;
    }

    //  保存扣费数据
    private function saveChargeData($data) {
        $r_data = [];
        $r_data['ad_id'] = $data['ad_id'];
        $r_data['bid_id'] = $data['bid_id'];
        $r_data['orig_id'] = $data['originality_id'];
        $r_data['ads_id'] = $data['ads_id'];
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

    //  保存数据->ssp
    private function saveSspData($data) {
        $redis = Common::getQueue('mobgi');
        return $redis->push('RQ:ad_client', $data);
    }


}
