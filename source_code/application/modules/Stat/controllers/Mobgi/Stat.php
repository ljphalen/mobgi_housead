<?php if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Mobgi_StatController
 * @author atom.zhan
 *
 */
class Mobgi_StatController extends Stat_BaseController {
    protected $ver = 1;
    protected $eventMap = [
        1 => [5, 1],
        2 => [7, 1],
        3 => [8, 1],
        4 => [6, 1],
        5 => [2, 1],
        6 => [3, 1],
        7 => [4, 1],
        8 => [5, 2],
        9 => [7, 2],
        10 => [6, 2],
        11 => [1, 2],
        12 => [2, 2],
        13 => [4, 2],
        14 => [3, 2],
        15 => [15, 0],
        16 => [14, 0],
        17 => [17, 0],
        31 => [1, 4],
        32 => [2, 4],
        33 => [3, 4],
        34 => [4, 4],
        35 => [5, 4],
        36 => [6, 4],
        37 => [7, 4],
        41 => [1, 5],
        42 => [2, 5],
        43 => [3, 5],
        44 => [4, 5],
        45 => [5, 5],
        46 => [6, 5],
    ];

    /**
     * 视频聚合统计接口 字段的顺序
     * 0 游戏或APP包体版本号
     * 1 聚合SDK版本号
     * 2 游戏或APP接入聚合SDK使用到的APPKEY
     * 3 聚合SDK使用的渠道号(IOS默认CURRENT00000)
     * 4 上报事件类型(1开始播放，2播放结束，3触发奖励，4点击)
     * 5 服务端时间（传空）
     * 6 UUID(IOS为idfv)
     * 7 第三方广告商SDK的版本号
     * 8 imei(IOS传空)
     * 9 clientid(传空)
     * 10 设备品牌
     * 11 设备型号
     * 12 运营商（1为China Mobile,2为China Unicom,3China Telecom,4没有运营商信息,5.WIFI）
     * 13 广告位ID（新版聚合SDK统一用自家的广告位ID）
     * 14 UDID(IOS为idfa)
     * 15 操作系统区分(IOS为1)
     * 16 android_id(IOS传空)
     * 17 聚合的广告商的名称标示(Vungle,Unity,Adcolony)
     * 18 聚合广告类型,0视频，1插页，2自定义，3开屏
     * @return array
     */
    public function statAction() {

        //$this->mReportType = 1;
        $this->jsonData = file_get_contents('php://input');
        //$data = "1.0.1|1.1.1|B4D3AF7C536AB8A2A88A|TEST0000000|1|145545455|asdfask11111|2.0.1|1111111111|127.0.0.1|device_brand|device_model|string1|string1|string2|0|string9|mober|1";
        //$data = '3.1.5|0.4.0|0f881ba4e517c6c28d88|NT0S0N00002|11||00000000-6708-6c7a-ffff-ffff9cc68e7a||863360023990231||Xiaomi|MI 3W|5|MC45OTQ5NjMwMCAxNDY1NzA-MGY4ODFi|674_6875448755188139qp3s9533q8835|1|924f8cd433d88008|GDT|1';
        if (empty($this->jsonData)) {
            $this->error('Missing required parameter', self::ERROR_PARAM);
        }
        if (strpos($this->jsonData, "\n")) {
            $this->error('Contain a newline', self::ERROR_PARAM);
        }
        if (strpos($this->jsonData, "\t")) {
            $this->error('Contain a tab', self::ERROR_PARAM);
        }
        $dataArr = explode('|', $this->jsonData);

        if (count($dataArr) < 18) {
            $this->error('Missing required parameter', self::ERROR_PARAM);
        }

        if (strlen($dataArr[0]) > 15) {
            $this->error('app_version > 15:' . $dataArr[0], self::ERROR_PARAM);
        }
        if (strlen($dataArr[1]) > 15) {
            $this->error('sdk_version > 15:' . $dataArr[1], self::ERROR_PARAM);
        }

        if (strlen($dataArr[2]) != 20) {
            $this->error('app_key !=20:' . $dataArr[2], self::ERROR_PARAM);
        }
        if (strlen($dataArr[3]) > 12) {
            $this->error('cid > 12:' . $dataArr[3], self::ERROR_PARAM);
        }
        //事件过滤
        if ($dataArr[4] > 9999) {
            $this->error('event_type out of range' . $dataArr[4], self::ERROR_PARAM);
        } else if ($dataArr[4] == 17) {
            if ($dataArr[5] >= 0) {
                $dataArr[4] = 1700 + intval($dataArr[5]);
            } else {
                $this->error('cache ready :' . $dataArr[5], self::ERROR_PARAM);
            }
        }
        $dataArr[5] = time();
        //uuid过滤
        if (empty($dataArr[6]) or strlen($dataArr[6]) > 64) {
            $dataArr[6] = substr($dataArr[6], 0, 64);
            // $this->error('uuid Error:' . $dataArr[6], self::ERROR_PARAM);
        }
        // [7]不校验
        if (strlen($dataArr[8]) > 64) {
            //imei
            $dataArr[8] = substr($dataArr[8], 0, 64);
        }
        $dataArr[9] = Common::getClientIP();
        if (strlen($dataArr[10]) > 32) {
            //brand
            $dataArr[10] = substr($dataArr[10], 0, 32);
        }
        if (strlen($dataArr[11]) > 32) {
            //model
            $dataArr[11] = substr($dataArr[11], 0, 32);
        }
        if (intval($dataArr[12]) > 100) {
            $this->error('operator out of range:' . $dataArr[12], self::ERROR_PARAM);
        }
        if (strlen($dataArr[13]) > 64) {
            $this->error('pos_key len more then 64:' . $dataArr[13], self::ERROR_PARAM);
        }
        // [14]不校验

        if ($dataArr[15] != '0' && $dataArr[15] != '1') {
            $this->error('os Error:' . $dataArr[15], self::ERROR_PARAM);
        } else {
            $dataArr[15] = $dataArr[15] + 1;//操作系统 +1
        }
        // [16]不校验
        if (empty($dataArr[17]) and in_array(intval($dataArr[4]), [1, 2, 3, 4, 6, 7, 8, 9, 10, 14, 13, 16, 35, 37, 36, 33, 34, 45, 46, 43, 44])) {
            $this->error('ads Error:' . $dataArr[4], self::ERROR_PARAM);
        }
        $dataArr[18] = isset($dataArr[18]) ? ($dataArr[18] + 1) : 1;//广告类型
        $dataArr = $this->initReportParams($dataArr);

        $ret = $this->saveAdxData($dataArr);
        if ($ret['ret'] == 0) {
            $this->output(1, 'ok');
        } else {
            $this->output(0, 'fail');
        }


    }


    private function initReportParams($dataArr) {
        $dataArr[0] = !empty($dataArr[0]) ? $dataArr[0] : '-1';
        $dataArr[1] = !empty($dataArr[1]) ? $dataArr[1] : '-1';
        $dataArr[3] = !empty($dataArr[3]) ? $dataArr[3] : '-1';
        $dataArr[7] = !empty($dataArr[7]) ? $dataArr[7] : '-1';
        $dataArr[8] = !empty($dataArr[8]) ? $dataArr[8] : '-1';
        $dataArr[10] = !empty($dataArr[10]) ? $dataArr[10] : '-1';
        $dataArr[11] = !empty($dataArr[11]) ? $dataArr[11] : '-1';
        $dataArr[12] = !empty($dataArr[12]) ? $dataArr[12] : '-1';
        $dataArr[13] = !empty($dataArr[13]) ? $dataArr[13] : '-1';
        $dataArr[14] = !empty($dataArr[14]) ? $dataArr[14] : '-1';
        $dataArr[16] = !empty($dataArr[16]) ? $dataArr[16] : '-1';
        $dataArr[17] = !empty($dataArr[17]) ? $dataArr[17] : '-1';
        return $dataArr;
    }

    /**
     * 保存上报数据
     * @param array $data
     * @return int
     */
    private function saveData($data) {
        $redis = Common::getQueue('mobgi');
        $ret = $redis->push('RQ:video_ads_stat', json_encode($data));
        return $ret;
    }


    /**
     * 保存Adx上报数据
     * @param array $data
     * @return int
     */
    private function saveAdxData($data) {
        $r_data['ads_id'] = $data[17];
        $r_data['app_key'] = $data[2];
        $r_data['pos_key'] = $data[13];
        $r_data['ad_type'] = intval($this->eventMap[$data[4]][1]) ?: intval($data[18]);
        $r_data['cid'] = $data[3];
        $r_data['brand'] = $data[10];
        $r_data['model'] = $data[11];
        $r_data['operator'] = intval($data[12]);
        $r_data['event_type'] = intval($this->eventMap[$data[4]][0]) ?: intval($data[4]);
        $r_data['imei'] = $data[8];
        $r_data['platform'] = intval($data[15]);
        $r_data['uuid'] = $data[6];
        $r_data['app_version'] = $data[0];
        $r_data['sdk_version'] = $data[1];
        $r_data['client_ip'] = $data[9];
        $r_data['server_time'] = $data[5];
        $r_data['ver'] = $this->ver;
        //聚合数据部分数据校正(因为IOS设备adx的imei索引存的是idfa，所以把idfa写入imei索引)
        if($r_data['platform'] == 2){
           $r_data['imei'] =  $data[14];
        }
        $redis = Common::getQueue('mobgi');
        return $redis->push('RQ:ad_client', $r_data);

    }

    //兼容旧版ISO返回头
    public function output($code, $msg = '', $data = array()) {
        $this->mReportCode = $code;
        $this->mReportMsg = $msg;
        $this->mReportData = $data;
        header("Content-type: text/html; charset=utf-8");
        exit (json_encode(array(
            'ret' => $code,
            'msg' => $msg,
            'data' => $data
        )));
    }

}
