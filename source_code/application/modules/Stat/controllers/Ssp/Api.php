<?php
/**
 * Created by PhpStorm.
 * User: atom.zhan
 * Date: 2016/11/9
 * Time: 15:26
 */
if (!defined('BASE_PATH')) exit('Access Denied!');

class Ssp_ApiController extends Stat_BaseController {
    const ERR_CODE_SIGN = 1000;
    const ERR_CODE_REQUEST = 1001;
    const ERR_CODE_FORMAT = 1002;

    private static $secretKey = "1aa3408d92fe59cda813527095eaac53";

    //报表数据接口
    public function apiAction() {
        $fields = ['sign' => 1, 'sdate' => 1, 'edate' => 1, 'sign' => 1, 'app_key' => 0, 'pos_key' => 0];
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

        if (strtotime($params['sdate']) == 0) {
            $this->error(self::ERR_CODE_FORMAT, 'sdate format error.');
        }
        if (strtotime($params['sdate']) < strtotime('-3 month')) {
            $this->error(self::ERR_CODE_FORMAT, 'The start time can\'t be less than 3 months.');
        }
        if (strtotime($params['edate']) == 0) {
            $this->error(self::ERR_CODE_FORMAT, 'sdate format error.');
        }
        $xdebug = Util_Filter::get('XDEBUG');

        $devId = MobgiData_Service_MobgiModel::getDevIdByKey(trim($params['sign']));
        if (empty($devId)) {
            $this->output(self::ERR_CODE_SIGN, 'sign authentication failed');
        }

        $appIds = MobgiData_Service_MobgiModel::getSspAppKeyByDevId($devId);
        if (empty($appIds)) {
            $this->output(self::ERR_CODE_FORMAT, 'no apps');
        }
        $data = [];
        $params['app_key'] = isset($params['app_key']) ? explode(',', $params['app_key']) : [];
        if ($appIds) {
            $apps = $params['app_key'] ? array_intersect($appIds, $params['app_key']) : $appIds;
            if (count($apps)) {
                $params['app_key'] = $apps;
                $data = $this->getData($params);
            }
        }

        if ($xdebug == 'idsy') {
            $data['sign_val'] = 'md5(' . self::$secretKey . '+' . $params['sdate'] . '+' . $params['sdate'] . ')';
            $data['sign'] = md5(self::$secretKey . $params['sdate'] . $params['sdate']);
        }
        $this->output(0, 'ok', $data);
    }

    private function getData($params) {
        $params['kpis'] = ['third_views', 'third_clicks', 'third_ad_income'];
        $params['dims'] = ['days', 'app_key', 'ad_type'];
        $list = MobgiData_Service_MobgiModel::getApiData($params);
        $fields = [
            'date' => 'days',
            'app_key' => 'app_key',
            'pos_key' => 'pos_key',
            'ad_type' => 'ad_type',
            'pos_key' => 'pos_key',
            'clicks' => 'third_clicks',
            'impressions' => 'third_views',
            'revenue' => 'third_ad_income',
        ];
        $result = [];
        $adTypeName = Common_Service_Config::AD_TYPE_NAME;
        foreach ($list as $item) {
            $data = [];
            foreach ($fields as $key => $mkey) {
                if (isset($item[$mkey]))
                    $data[$key] = $item[$mkey];
            }
            if (isset($adTypeName[intval($data['ad_type'])])) {
                $data['ad_type'] = $adTypeName[intval($data['ad_type'])];
            }
            $data['revenue'] = floatval(number_format($data['revenue'], 2, '.', ''));
            $data['clicks'] = intval($data['clicks']);
            $data['impressions'] = intval($data['impressions']);
            $result[] = $data;
        }
        return $result;
    }

}