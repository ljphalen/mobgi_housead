<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class IndexController extends Advertiser_BaseController {


    public $actions = array(
        'editpasswd' => '/Advertiser/User/edit',
        'logout' => '/Advertiser/Login/logout',
        'default' => '/Advertiser/Index/default',
        'getdesc' => '/Advertiser/Index/getdesc',
        'search' => '/Advertiser/Index/search',
        'passwdUrl' => '/Advertiser/User/passwd',
    );

    /**
     *
     * Enter description here ...
     */
    public function indexAction() {
        
        /*权限校验start*/
        if (!$this->hasAdvertiserPermission('Advertiser_Homepage_View')) {
            $this->showMsg(100001, 'permission denied!');
        }
        /*权限校验end*/
        //报表帐号只允许访问报表
        if ($this->userInfo['isreport'] && $this->userInfo['related_advertiser_uid']) {
            $this->redirect('/Advertiser/Stat/index');
        }
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');

        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');

        if (empty($sdate) && empty($edate)) {
            $sdate = date("Y-m-d");
            $edate = date("Y-m-d");
        }

        $uId = $this->userInfo['advertiser_uid'];

        $accountDayAmountLimitList = Advertiser_Service_AccountConsumptionLimitModel::getConsumptionlimit($uId);
        $this->assign('accountDayAmountLimitList', intval($accountDayAmountLimitList));

        #获取各个帐户内的余额
        $account_detail_arr = Advertiser_Service_AccountDetailModel::getsBy(array('uid' => $uId));
        $account_detail_arr = common::resetKey($account_detail_arr, 'account_type');
        foreach ($account_detail_arr as $key => $account_item) {
            $account_detail_arr[$key]['account_type_str'] = $this->getAccountTypeStr($account_item['account_type']);
        }
        #获取各个帐户内的今日消耗，并计算今日总消耗
        $day_consumption_arr = Advertiser_Service_AccountDayConsumeModel::getsBy(array('uid' => $uId, 'date' => date('Ymd')));
        $day_consumption_arr = common::resetKey($day_consumption_arr, 'account_type');
        $accountTodayConsumeAmount = 0;
        if ($day_consumption_arr) {
            foreach ($day_consumption_arr as $key => $item) {
                $accountTodayConsumeAmount += $item['consumption'];
            }
        }
        //广告总量
        $params['account_id'] = $uId;
        $params['status'] = array('IN', array(1, 2, 3, 4, 5));
        $adTotal = Dedelivery_Service_AdConfListModel::getCountBy($params);

        $params['status'] = array('IN', array(1, 4));
        $checkPassTotal = Dedelivery_Service_AdConfListModel::getCountBy($params);

        $params['status'] = 3;
        $checkNoPassTotal = Dedelivery_Service_AdConfListModel::getCountBy($params);

        $params['status'] = 1;
        $checkingTotal = Dedelivery_Service_AdConfListModel::getCountBy($params);

        $Advertiser_cache_tip_limit = Common::getConfig('advertiserConfig', 'Advertiser_cache_tip_limit');
        $this->assign('advertiserCacheTipLimit', $Advertiser_cache_tip_limit);

        $this->assign('accountTodayConsumeAmount', $accountTodayConsumeAmount);
        $this->assign('account_detail_arr', $account_detail_arr);
        $this->assign('day_consumption_arr', $day_consumption_arr);
        $this->assign('adTotal', $adTotal);
        $this->assign('checkPassTotal', $checkPassTotal);
        $this->assign('checkNoPassTotal', $checkNoPassTotal);
        $this->assign('checkingTotal', $checkingTotal);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', $this->getChartConf());
    }

    #帐户类型
    private function getAccountTypeStr($account_type) {
        $Advertiser_account_type_config = Common::getConfig('advertiserConfig', 'Advertiser_account_type');
        return $Advertiser_account_type_config[$account_type];
    }

    #帐户类型
    private function getAccountTypeList() {
        return Common::getConfig('advertiserConfig', 'Advertiser_account_type');
    }

    /**
     * 获取 传参 数组
     * @return array
     */
    private function exchangeParamsArray($params) {
        $arrParams = array();
        foreach ($params as $key => $val) {
            if (!is_null($val)) {
                $arrParams[$key] = strpos($val, ',') !== false ? explode(',', $val) : $val;
            }
        }
        return $arrParams;
    }


//    获取报表配置
    private function getChartConf() {
        $conf = [
            'api' => [
                "data" => '/Advertiser/Stat/getData',
                "conf" => '/Advertiser/Stat/updateKpi',
            ],
            'kpiConf' => HouseAdStat_Service_ReportBaseModel::$kpiConf,
            'kpi' => [],
            'dim' => [
                "default_dim_dom" => "#dim",
                "default_dim_fields" => [
                    "date" => "日期",
                    "hour" => "小时",
                    "hr1" => "-",
                    "unit_id" => "投放单元",
                    "ad_id" => "广告",
                    "originality_id" => "创意",
                    "hr2" => "-",
                    "ad_type" => "创意类型",
                    "app_key" => "应用",
                    "block_id" => "广告位",
                    "platform" => "平台"
                ],
                "default_dim_value" => ["date" => []],
                "dims" => [],
            ],
        ];
        $reportAccountId = $this->getReportAccountId();
        $accountId = $this->userInfo['advertiser_uid'];
        $conf['kpi'] = HouseAdStat_Service_ReportBaseModel::getKpis($accountId);
        $conf['dim']['dims'] = HouseAdStat_Service_ReportBaseModel::getDims($reportAccountId);
        $conf['dim']['relations'] = HouseAdStat_Service_ReportBaseModel::getRelations($reportAccountId);
        return json_encode($conf);
    }


}
