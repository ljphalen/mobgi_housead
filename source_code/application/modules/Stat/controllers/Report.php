<?php
/**
 * Created by PhpStorm.
 * User: atom.zhan
 * Date: 2016/11/9
 * Time: 15:26
 */
if (!defined('BASE_PATH')) exit('Access Denied!');

class ReportController extends Stat_BaseController {
    const ERR_CODE_SIGN = 1000;
    const ERR_CODE_REQUEST = 1001;
    const ERR_CODE_FORMAT = 1002;

    private $secretKey = "1aa3408d92fe59cda813527095eaac53";

    //    参数转换
    private function exchangeParamsArray($params) {
        $arrParams = [];
        foreach ($params as $key => $val) {
            if (!is_null($val)) {
                if (in_array($key, ['sdate', 'edate'])) {
                    $arrParams[$key] = $val;
                } else {
                    $arrParams[$key] = explode(',', trim($val, ','));
                }
            }
        }
        return $arrParams;
    }

    //报表数据接口
    public function apiAction() {
        $fields = ['sign' => 1, 'sdate' => 1, 'edate' => 1, 'app_key' => 0,'data_type'=>1];
        foreach ($fields as $field => $isNeed) {
            $params[$field] = Util_Filter::get($field);
            if (is_null($params[$field])) {
                if ($isNeed) {
                    $this->error(self::ERR_CODE_REQUEST, $field . ' is need.');
                } else {
                    unset($params[$field]);
                }
            }
        }

        $xdebug = Util_Filter::get('XDEBUG');
        if (strtotime($params['sdate']) == 0) {
            $this->error(self::ERR_CODE_FORMAT, 'start_date format error.');
        }
        if (strtotime($params['edate']) == 0) {
            $this->error(self::ERR_CODE_FORMAT, 'start_date format error.');
        }
        //        if (strlen($params['sign']) != 32) {
        //            $this->error(self::ERR_CODE_FORMAT, 'sign format error.'.($params['sign']).'*'. md5($this->secretKey . $params['sdate'] . $params['sdate']));
        //        }
        //        $this->secretKey  = Common::getConfig('siteConfig', 'secretKey');
        if (is_null($xdebug) and $params['sign'] != md5($this->secretKey . $params['sdate'] . $params['sdate'])) {
            $this->error(self::ERR_CODE_SIGN, 'sign authentication failed.' . ($this->secretKey . $params['sdate'] . $params['sdate']));
        }
        $data = $this->getData($params);

        if ($xdebug == 'idsy') {
            $data['sign_val'] = 'md5(' . $this->secretKey . '+' . $params['sdate'] . '+' . $params['sdate'] . ')';
            $data['sign'] = md5($this->secretKey . $params['sdate'] . $params['sdate']);
        }
        $this->success($data);
    }

    private function getData($params) {
        $params['theader'] = ['days', 'hours','app_key', 'ad_type', 'pos_key','is_mobgi','sum(views) as views', 'sum(clicks) as clicks', 'sum(amount) as amount'];
        $params['dims'] = ['days', 'hours','app_key', 'ad_type', 'pos_key'];
        $list = MobgiCharge_Service_AdxChargeDayModel::getApiDataList($params);
        #$list = MobgiCharge_Service_AdxChargeDayModel::getSampleData($params);
        $fields = [
            'date' => 'days',
            'hour' => 'hours',
            'app_key' => 'app_key',
            'ad_type' => 'ad_type',
            'pos_key' => 'pos_key',
            'revenue' => 'amount',
            'clicks' => 'clicks',
            'impressions' => 'views',
            'is_mobgi'=>'is_mobgi',
            'pos_key'=>'pos_key',
        ];
        $result = [];
        $adTypeName = Common_Service_Config::AD_TYPE_NAME;
        foreach ($list as $item) {
            $data = [];
            foreach ($fields as $key => $mkey) {
                $data[$key] = $item[$mkey];
            }
            if (isset($adTypeName[intval($data['ad_type'])])) {
                $data['ad_type'] = $adTypeName[intval($data['ad_type'])];
            } else {
                continue;
            }
            $data['revenue'] = floatval(number_format($data['revenue'], 2, '.', ''));
            $data['clicks'] = intval($data['clicks']);
            $data['impressions'] = intval($data['impressions']);
            $result[] = $data;
        }
        $this->success($result);
    }


    private function success($data) {
        header("content-type:application/json");
        exit(json_encode(array(
            'ret' => 0,
            'msg' => 'success',
            'data' => $data
        )));
    }

    //    protected function error($errCode, $msg) {
    //        header("content-type:application/json");
    //        exit(json_encode([ret => $errCode, msg => $msg, data => null]));
    //    }

    private function log($tag) {
        $logContent = date('H:i:s') . "\t" . $tag . "\t" . json_encode($this->data, JSON_UNESCAPED_UNICODE) . "\n";
        $typeSendToFile = 3;
        $fileName = 'report_' . date('md') . '.log';
        $filePath = Common::getConfig('siteConfig', 'logPath') . $fileName;
        error_log($logContent, $typeSendToFile, $filePath);
    }
}