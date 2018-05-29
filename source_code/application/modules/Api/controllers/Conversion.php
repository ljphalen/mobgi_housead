<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-7-26 10:13:46
 * $Id: active.php 62100 2017-7-26 10:13:46Z hunter.fang $
 */
//接收回调用
if (!defined('BASE_PATH'))
    exit('Access Denied!');


//http://rock.advertiser.housead.com/api/conversion/postback?originalityId=1&deviceId=1acfd33122112113ef3&requestId=1313qewa3111121
//http://test-api-ha.mobgi.com/api/conversion/postback?originalityId=1&deviceId=1acfd33122112113ef3&requestId=1313qewa3111121
//http://test-api-ha.mobgi.com/api/conversion/postback?originalityId=125&deviceId=1ffaffffffdxfffdff3cFdffdf3d&requestId=313ddff2ffffffffffffxfffddF


class ConversionController extends Api_BaseController {

    /**
     * 接收回调的接口
     */
    public function postbackAction() {
        $info = $this->getInput(array('originalityId', 'deviceId', 'requestId'));
        $this->data = $info;
        if (empty($info['originalityId'])) {
            $this->output(1, 'param error:originalityId', array());
        }

        if (empty($info['deviceId'])) {
            $this->output(1, 'param error:deviceId', array());
        }

        if (empty($info['requestId'])) {
            $this->output(1, 'param error:requestId', array());
        }

        $originalityInfo = Dedelivery_Service_OriginalityRelationModel::getBy(array('id' => $info['originalityId']));
        if (empty($originalityInfo)) {
            $this->output(1, 'originalityInfo is empty', array());
        }
        
        $activeInfo = MobgiCharge_Service_ActiveModel::getBy(array('request_id' => $info['requestId']));
        if ($activeInfo) {
            $this->output(0, 'request_id recoreded', array());
        }

        $activeInfo = MobgiCharge_Service_ActiveModel::getBy(array('originality_id' => $info['originalityId'], 'device_id' => $info['deviceId']));
        if ($activeInfo) {
            $this->output(0, 'device_id recoreded', array());
        }
        $data = array('request_id' => $info['requestId'], 'unit_id' => $originalityInfo['unit_id'], 'ad_id' => $originalityInfo['ad_id'], 'originality_id' => $info['originalityId'], 'device_id' => $info['deviceId']);
        $result = MobgiCharge_Service_ActiveModel::add($data);
        $this->saveData($data);
        if ($result) {
            $this->output(0, 'success', array());
        } else {
            $this->output(1, 'error', array());
        }
    }


    
    /**
     * 保存上报数据
     * @param type $data
     * @return type
     */
    private function saveData($data) {
        $redis = Common::getQueue('adx');
        $data['unit_id'] = intval($data['unit_id']);
        $data['ad_id'] = intval($data['ad_id']);
        $data['originality_id'] = $data['originality_id'];
        $data['uuid'] = trim($data['device_id']);
        $data['event_type'] = 45;
        $data['charge_type'] = Common_Service_Const::CHARGE_TYPE_CPA;
        $data['request_id'] = $data['request_id'];
        $data['server_time'] = Common::getTime();
        $write = $redis->push('RQ:active_list', $data);
        if ($write <= 0) {
            $this->errlog('active_list');
        }
        return $write;
    }
    
    //    错误记录
    private function errlog($tag, $quit = 1) {
        $logContent = date('H:i:s') . "\t" . $tag . "\t" . json_encode($this->data, JSON_UNESCAPED_UNICODE) . "\n";
        $typeSendToFile = 3;
        $fileName = 'postback_savechargedata_' . date('Ymd') . '.log';
        $filePath = Common::getConfig('siteConfig', 'logPath') . $fileName;
        error_log($logContent, $typeSendToFile, $filePath);
//        $this->mReportType = 'error_' . $tag;
//        $this->mReportMsg = $tag;
//        if ($quit) {
//            header("Content-type:text/json");
//            exit(json_encode([status => false, msg => $tag, data => json_encode($this->data, JSON_UNESCAPED_UNICODE)]));
//        }

    }
    
}
