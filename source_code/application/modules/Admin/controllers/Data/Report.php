<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * 数据报表
 * @author atom.zhan
 *
 */
class Data_ReportController extends Admin_BaseController {

    public static $filterFields = ['sdate', 'edate', 'dims', 'compare', 'theader', 'kpis', 'is_custom'];


    public $actions = [
        'listUrl' => '/Admin/Stat/pv',
        'monkeytime' => '/Admin/Stat/monkeytime',
        'retentionList' => '/Admin/Data_Report/Retention',
        'weightLogList' => '/Admin/Data_Report/weightLog',
        'weekReportList' => '/Admin/Data_Report/weekReport',
        'setKpi' => '/Admin/Data_Report/setKpi',
        'getKpi' => '/Admin/Data_Report/getKpi'
    ];

    public $perpage = 20;




    /**
     * mobgi报表
     * Enter description here ...
     */
    public function mobgiAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-d', strtotime('-7 days'));
            $edate = date("Y-m-d", strtotime('-1 days'));
        }
        $userId = $this->getUserId();

        $filterFields = array_merge(MobgiData_Service_MobgiModel::getFilterFields(), self::$filterFields);

        $params = $this->getInput($filterFields);

        $params = $this->exchangeParamsArray($params);

        $inform = MobgiData_Service_MobgiModel::getInform();

        $this->assign('inform', $inform);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', json_encode(MobgiData_Service_MobgiModel::getChartConf($userId, $params)));
    }


    public function houseadAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-d', strtotime('-7 days'));
            $edate = date("Y-m-d", strtotime('-1 days'));
        }
        $userId = $this->getUserId();
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', json_encode(MobgiData_Service_HouseadModel::getChartConf($userId)));
    }

    /**
     * 效果数据概览
     */
    public function indexAction() {
        $yesterday = date('Y-m-d', strtotime('-1 days'));
        $firstDayOfThisMonth = date('Y-m-01');
        $firstDayOfLastMonth = date('Y-m-01', strtotime('-1 month', strtotime($firstDayOfThisMonth)));
        $endDayOfLastMonth = date('Y-m-t', strtotime('-1 month', strtotime($firstDayOfThisMonth)));

        $this->assign('sdate', $yesterday);
        $this->assign('edate', $yesterday);
        $params['sdate'] = date('Y-m-d', strtotime('-30 days'));
        $params['edate'] = $yesterday;

        $income['yesterday'] = MobgiData_Service_MobgiModel::getIncome($yesterday, $yesterday, $this->getUserId());
        $income['thisMonth'] = MobgiData_Service_MobgiModel::getIncome($firstDayOfThisMonth, $yesterday, $this->getUserId());
        $income['lastMonth'] = MobgiData_Service_MobgiModel::getIncome($firstDayOfLastMonth, $endDayOfLastMonth, $this->getUserId());

        $chartConfig = MobgiData_Service_MobgiModel::getChartConf($this->getUserId());
        $chartConfig['api']['data'] = '/Admin/Data_Report/getOverviewData';

        $kpis = ['user_dau', 'third_views', 'third_clicks', 'third_click_rate', 'third_ecpm', 'user_view_count', 'arpu', 'ad_income'];

        foreach ($chartConfig['kpi'] as $key => $val) {
            foreach ($val as $skey => $sval) {
                $chartConfig['kpi'][$key][$skey] = in_array($skey, $kpis) ? 1 : 0;
            }
        }
        $this->assign('config', json_encode($chartConfig));
        $this->assign('params', $params);
        $this->assign('income', $income);
    }

    /**
     * 定制渠道数据
     */
    public function customAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-d', strtotime('-7 days'));
            $edate = date("Y-m-d", strtotime('-1 days'));
        }
        $userId = $this->getUserId();

        $filterFields = array_merge(MobgiData_Service_MobgiModel::getFilterFields(), self::$filterFields);
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);

        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', json_encode(MobgiData_Service_CustomModel::getChartConf($userId, $params)));
    }


    /*
     * ltv数据
     */
    public function ltvAction() {
        $channels = MobgiData_Service_MobgiModel::getChannels();
        $apps = MobgiData_Service_MobgiModel::getApps();
        $adType = Common_Service_Config::AD_TYPE;
        $platform = Common_Service_Config::PLATFORM;

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 days'));
        $sdate = date('Y-m-d', strtotime('-30 days'));


        $this->assign('sdate', $sdate);
        $this->assign('edate', $yesterday);

        $this->assign('channels', $channels);
        $this->assign('apps', $apps);
        $this->assign('adType', $adType);
        $this->assign('platform', $platform);

    }

    public function getLtvDataAction() {
        $mydims = ['days', 'app_key', 'channel_gid', 'ad_type', 'platform'];
        $params = $this->getInput($mydims);
        list($params['sdate'], $params['edate']) = explode(' - ', $params['days']);
        unset($params['days']);

        foreach ($mydims as $dim) {
            if (empty($params[$dim])) {
                unset($params[$dim]);
            }
        }

        $params = $this->exchangeParamsArray($params);
        $data = MobgiData_Service_MobgiModel::getLtvData($params);
        $this->output(0, 'ok', $data);
    }

    public function getLtv2DataAction() {
        $mydims = ['days', 'app_key', 'channel_gid', 'ad_type', 'platform'];
        $params = $this->getInput($mydims);
        list($params['sdate'], $params['edate']) = explode(' - ', $params['days']);
        unset($params['days']);
        foreach ($mydims as $dim) {
            if (empty($params[$dim])) {
                unset($params[$dim]);
            }
        }
        $params = $this->exchangeParamsArray($params);
        $data = MobgiData_Service_MobgiModel::getLtv2Data($params);
        $this->output(0, 'ok', $data);
    }

    /*
     * Nuv数据
     */
    public function NuvAction() {
        $channels = MobgiData_Service_MobgiModel::getChannels();
        $apps = MobgiData_Service_MobgiModel::getApps();
        $adType = Common_Service_Config::AD_TYPE;
        $platform = Common_Service_Config::PLATFORM;
        $ads = MobgiData_Service_MobgiModel::getAdsIdMap();
        $posKey = MobgiData_Service_MobgiModel::getPosKeyMap();
        //        $country = MobgiData_Service_MobgiModel::getCountryMap();
        //        $province = Common_Service_Config::PROVINCE;
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 days'));
        $sdate = date('Y-m-d', strtotime('-30 days'));

        //        $this->assign('province', $province);
        //        $this->assign('country', $country);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $yesterday);
        $this->assign('ads', $ads);
        $this->assign('pos_key', $posKey);
        $this->assign('channels', $channels);
        $this->assign('apps', $apps);
        $this->assign('adType', $adType);
        $this->assign('platform', $platform);
    }

    public function getNuvDataAction() {
        $mydims = ['days', 'app_key', 'channel_gid', 'ad_type', 'platform', 'pos_key', 'ads_id'];
        $params = $this->getInput($mydims);
        list($params['sdate'], $params['edate']) = explode(' - ', $params['days']);
        unset($params['days']);

        foreach ($mydims as $dim) {
            if (empty($params[$dim])) {
                unset($params[$dim]);
            }
        }
        $params = $this->exchangeParamsArray($params);
        $data = MobgiData_Service_MobgiModel::getNuvData($params);
        $this->output(0, 'ok', $data);
    }

    /*
     * 用户时间段内观看人数统计
     */

    public function watchingTimeAction() {
        $apps = MobgiData_Service_MobgiModel::getApps();
        $adType = Common_Service_Config::AD_TYPE;
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 days'));
        $sdate = date('Y-m-d', strtotime('-30 days'));
        $this->assign('sdate', $sdate);
        $this->assign('edate', $yesterday);
        $this->assign('apps', $apps);
        $this->assign('adType', $adType);
    }

    /*
     *  用户在时间段观看人数统计（总体）点图
     */
    public function getWatchingTimeDataAction() {
        $mydims = ['days', 'app_key', 'ad_type'];
        $params = $this->getInput($mydims);
        list($params['sdate'], $params['edate']) = explode(' - ', $params['days']);
        unset($params['days']);

        foreach ($mydims as $dim) {
            if (empty($params[$dim])) {
                unset($params[$dim]);
            }
        }

        $params = $this->exchangeParamsArray($params);
        $data = MobgiData_Service_MobgiModel::getWatchingTimeData($params);
        $this->output(0, 'ok', $data);
    }


    /*
    *  用户在时间段观看人数统计（细化）柱状图 ,ajax
    */
    public function getWathcingTimeDetailDataAction() {
        $mydims = ['times', 'days', 'detail'];
        $params = $this->getInput($mydims);
        list($params['sdate'], $params['edate']) = explode(' - ', $params['days']);
        if ($params['detail'] != 'all') {
            $tmp = explode('_', $params['detail']);
            if (strlen($tmp[1]) != 1) {
                $params['app_key'] = $tmp[1];
            } else {
                $params['ad_type'] = $tmp[1];
            }
            if (isset($tmp[2])) {
                $params['ad_type'] = $tmp[2];
            }
        }

        unset($params['detail']);
        unset($params['days']);
        foreach ($mydims as $dim) {
            if (empty($params[$dim])) {
                unset($params[$dim]);
            }
        }
        $params = $this->exchangeParamsArray($params);
        $data = MobgiData_Service_MobgiModel::getWatchingTimeDetailData($params);
        $this->output(0, 'ok', $data);
    }


    /**
     * 对外数据展示
     */
    public function officialAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-d', strtotime('-7 days'));
            $edate = date("Y-m-d", strtotime('-1 days'));
        }
        $userId = $this->getUserId();

        $filterFields = array_merge(MobgiData_Service_OfficialModel::getFilterFields(), self::$filterFields);

        $params = $this->getInput($filterFields);

        $params = $this->exchangeParamsArray($params);

        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', json_encode(MobgiData_Service_OfficialModel::getChartConf($userId, $params)));
    }

    /*
     * 获取对外数据
     */
    public function getOfficeDataAction() {
        $data = [
            'table' => [],
            'total' => [],
        ];

        $filterFields = array_merge(MobgiData_Service_OfficialModel::getFilterFields(), self::$filterFields);
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);

        $data['table'] = MobgiData_Service_OfficialModel::getData($params, $this->getUserId());
        $data['total'] = MobgiData_Service_OfficialModel::getTotal($data['table'], $params);
        $this->output(1, "", $data);
    }

    public function getMobgiDataAction() {
        $data = [
            'table' => [],
            'total' => [],
        ];
        $filterFields = array_merge(MobgiData_Service_MobgiModel::getFilterFields(), self::$filterFields);
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);
        $params['is_custom'] = 0;
        $data['table'] = MobgiData_Service_MobgiModel::getData($params, $this->getUserId());
        $data['total'] = MobgiData_Service_MobgiModel::getTotal($data['table'], $params);
        $this->output(0, "", $data);
    }

    public function getOverviewDataAction() {
        $data = [
            'table' => [],
            'total' => [],
        ];
        $filterFields = array_merge(MobgiData_Service_MobgiModel::getFilterFields(), self::$filterFields);
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);
        $data['table'] = MobgiData_Service_MobgiModel::getData($params, $this->getUserId());
        $data['total'] = MobgiData_Service_MobgiModel::getTotal($data['table'], $params);
        $this->output(0, "", $data);
    }

    public function getCustomDataAction() {
        $data = [
            'table' => [],
            'total' => [],
        ];
        $filterFields = array_merge(MobgiData_Service_CustomModel::getFilterFields(), self::$filterFields);
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);
        $params['is_custom'] = 1;
        $data['table'] = MobgiData_Service_CustomModel::getData($params, $this->getUserId());
        $data['total'] = MobgiData_Service_CustomModel::getTotal($data['table'], $params);
        $this->output(0, "", $data);
    }


    public function getHouseadDataAction() {
        $data = [
            'table' => [],
            'total' => [],
        ];

        $filterFields = array_merge(MobgiData_Service_HouseadModel::getFilterFields(), self::$filterFields);
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);
        $data['table'] = MobgiData_Service_HouseadModel::getData($params, $this->getUserId());
        $data['total'] = MobgiData_Service_HouseadModel::getTotal($data['table'], $params);
        $this->output(1, "", $data);
    }

    public function updateKpiAction() {
        $type = $this->getInput('type');
        if (in_array($type, ['mobgi', 'housead', 'official', 'custom'])) {
            $this->updateKpi($type);
        } else {
            $this->output(1, 'Illegal type');
        }

    }


    public function updateOfficialKpiAction() {
        $this->updateKpi('official');
    }

    public function updateCustomKpiAction() {
        $this->updateKpi('custom');
    }

    public function updateMobgiKpiAction() {
        $this->updateKpi('mobgi');
    }

    public function updateHouseadKpiAction() {
        $this->updateKpi('housead');
    }

    public function updateAbtestKpiAction() {
        $this->updateKpi('abtest');
    }
    public function updateSpmKpiAction() {
        $this->updateKpi('spm');
    }


    private function updateKpi($type) {
        $userId = $this->getUserId();
        $kpis = $this->getInput('kpis');
        $result = MobgiData_Service_MobgiModel::updateUserKpi($userId, $type, $kpis);
        $this->output(0, 'OK');
    }


    public function exportDataAction() {
        $filterFields = array_merge(MobgiData_Service_MobgiModel::getFilterFields(), self::$filterFields);
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);
        $data = MobgiData_Service_MobgiModel::getData($params, $this->getUserId());
        if (isset($params['theader'])) {
            $header = $params['theader'];
            $result = [];
            $kpiConf = MobgiData_Service_MobgiModel::$conf;
            foreach ($header as $key) {
                $result[0][$key] = $kpiConf[$key]['name'];
            }
            foreach ($data as $i => $item) {
                foreach ($header as $key) {
                    $result[$i + 1][$key] = $item[$key];
                }
            }
            Util_Csv::putHead('mobgi' . date('Ymd'));
            Util_Csv::putData($result);
        }
        exit;
    }


    public function exportOfficialDataAction() {
        $filterFields = array_merge(MobgiData_Service_OfficialModel::getFilterFields(), self::$filterFields);
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);
        $data = MobgiData_Service_OfficialModel::getData($params, $this->getUserId());
        if (isset($params['theader'])) {
            $header = $params['theader'];
            $result = [];
            $kpiConf = MobgiData_Service_OfficialModel::$conf;
            foreach ($header as $key) {
                $result[0][$key] = $kpiConf[$key]['name'];
            }
            foreach ($data as $i => $item) {
                foreach ($header as $key) {
                    $result[$i + 1][$key] = $item[$key];
                }
            }
            Util_Csv::putHead('mobgi' . date('Ymd'));
            Util_Csv::putData($result);
        }
        exit;
    }

    public function exportHouseAdDataAction() {
        $filterFields = array_merge(MobgiData_Service_HouseadModel::getFilterFields(), self::$filterFields);
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);
        $data = MobgiData_Service_HouseadModel::getData($params, $this->getUserId());
        if (isset($params['theader'])) {
            $header = $params['theader'];
            $result = [];
            $kpiConf = MobgiData_Service_MobgiModel::$conf;
            foreach ($header as $key) {
                $result[0][$key] = $kpiConf[$key]['name'];
            }
            foreach ($data as $i => $item) {
                foreach ($header as $key) {
                    $result[$i + 1][$key] = $item[$key];
                }
            }
            Util_Csv::putHead('housead' . date('Ymd'));
            Util_Csv::putData($result);
        }
        exit;
    }

    //周报报表数据
    public function runDetailReportAction() {
        $search = $this->getInput(['years']);
        $params = [];
        if ($search['years'] != NULL) {
            $params['years'] = $search['years'];
        } else {
            $params['years'] = date('Y');
        }
        $planList = MobgiData_Service_MobgiModel::getDao('ReportWeekPlan')->getsBy($params);
        $params['app_type'] = array('!=', 0);
        $list = MobgiData_Service_MobgiModel::getDao('ReportWeek')->getsBy($params);
        $data = array();
        foreach ($planList as $item => $val) {
            $data[$val['type']][$val['month']]['plan'] = floatval($val['ad_income']);
        }
        foreach ($list as $items => $vals) {
            $data[$vals['app_type']][$vals['month']]['finish'] += $vals['ad_income'];
            $data[$vals['app_type']][0]['finish'] += $vals['ad_income'];
        }
        foreach ($data as $type => &$items) {
            foreach ($items as &$item) {
                if (!isset($item['plan'])) $item['plan'] = 0;
                if (!isset($item['finish'])) $item['finish'] = 0;
                $item['rate'] = empty($item['plan']) ? $this->exchangeNumColorAction(round($item['finish'], 2)) : $this->exchangeNumColorAction(round($item['finish'] / $item['plan'] * 100, 2));
            }
        }
        $sumData = array();
        $sumData['yearCount'] ['plan'] = $data[1][0]['plan'] + $data[2][0]['plan'] + $data[3][0]['plan'];
        $sumData['yearCount'] ['finish'] = $data[1][0]['finish'] + $data[2][0]['finish'] + $data[3][0]['finish'];
        $sumData['yearCount'] ['rate'] = $this->exchangeNumColorAction(round(empty($sumData['yearCount']['plan']) ? 0 : $sumData['yearCount']['finish'] / $sumData['yearCount']['plan'], 2) * 100, 1);
        $sumData['yearCount'] ['diff'] = $this->exchangeNumColorAction($sumData['yearCount']['finish'] - $sumData['yearCount']['plan'], 2);

        $sumData['monthCount'] ['plan'] = $data[1][date('n')]['plan'] + $data[2][date('n')]['plan'] + $data[3][date('n')]['plan'];
        $sumData['monthCount'] ['finish'] = $data[1][date('n')]['finish'] + $data[2][date('n')]['finish'] + $data[3][date('n')]['finish'];
        $sumData['monthCount'] ['rate'] = $this->exchangeNumColorAction(round(empty($sumData['monthCount']['plan']) ? 0 : $sumData['monthCount']['finish'] / $sumData['monthCount']['plan'], 2) * 100, 1);
        $sumData['monthCount'] ['diff'] = $this->exchangeNumColorAction($sumData['monthCount']['finish'] - $sumData['monthCount']['plan'], 2);

        $lastUpdate = MobgiData_Service_MobgiModel::getDao('ReportWeek')->getsBy($params, array('update_time' => 'desc'));
        $lastUpdateDay = $lastUpdate[0]['update_time'];
        $this->assign('sumData', $sumData);
        $this->assign('lastUpdate', $lastUpdateDay);
        $this->assign('years', $params['years']);
        $this->assign('data', $data);
    }


    //ajax概要
    public function getRunRepoetDataByajaxAction() {
        $search = $this->getInput(['years']);
        $params = [];
        if ($search['years'] != NULL) {
            $params['years'] = $search['years'];
        } else {
            $params['years'] = date('Y');
        }
        $planList = MobgiData_Service_MobgiModel::getDao('ReportWeekPlan')->getsBy($params);
        $params['app_type'] = array('!=', 0);
        $list = MobgiData_Service_MobgiModel::getDao('ReportWeek')->getsBy($params);
        $data = array();
        foreach ($planList as $item => $val) {
            $data[$val['type']][$val['month']]['plan'] = floatval($val['ad_income']);
        }
        foreach ($list as $items => $vals) {
            $data[$vals['app_type']][$vals['month']]['finish'] += $vals['ad_income'];
            $data[$vals['app_type']][0]['finish'] += $vals['ad_income'];
        }
        $jsonData = array();
        foreach ($data as $type => &$items) {
            foreach ($items as &$item) {
                if (!isset($item['plan'])) $item['plan'] = 0;
                if (!isset($item['finish'])) $item['finish'] = 0;
                $item['rate'] = empty($item['plan']) ? round($item['finish'], 2) : round($item['finish'] / $item['plan'] * 100, 2);
            }
            $jsonData['year']['finishrate'][$type] = $data[$type][0]['rate'];
            $jsonData['year']['notfinishrate'][$type] = $data[$type][0]['rate'] > 100 ? 0 : 100 - $data[$type][0]['rate'];
            $jsonData['month']['finishrate'][$type] = $data[$type][date('n')]['rate'];
            $jsonData['month']['notfinishrate'][$type] = $data[$type][date('n')]['rate'] > 100 ? 0 : 100 - $data[$type][date('n')]['rate'];
        }

        $yearPlanCount = $data[1][0]['plan'] + $data[2][0]['plan'] + $data[3][0]['plan'];
        $yearFinishCount = $data[1][0]['finish'] + $data[2][0]['finish'] + $data[3][0]['finish'];
        $jsonData['year'] ['finishrate'][0] = round(empty($yearPlanCount) ? 0 : $yearFinishCount / $yearPlanCount, 2) * 100;
        $jsonData['year'] ['notfinishrate'][0] = $jsonData['year']['finishrate'][0] > 100 ? 0 : 100 - $jsonData['year']['finishrate'][0];

        $monthPlanCount = $data[1][date('n')]['plan'] + $data[2][date('n')]['plan'] + $data[3][date('n')]['plan'];
        $monthFinishCount = $data[1][date('n')]['finish'] + $data[2][date('n')]['finish'] + $data[3][date('n')]['finish'];
        $jsonData['month'] ['finishrate'][0] = round(empty($monthPlanCount) ? 0 : $monthFinishCount / $monthPlanCount, 2) * 100;
        $jsonData['month'] ['notfinishrate'][0] = $jsonData['month']['finishrate'][0] > 100 ? 0 : 100 - $jsonData['month']['finishrate'][0];


        #算基准线
        $date1 = date_create(date("Y-m-d", strtotime("-1 day")));
        $date2 = date_create($params['years'] . '-01-01');
        $diffYear = date_diff($date1, $date2);
        $jsonData['year']['average'] = $diffYear->days > 365 ? 100 : round($diffYear->days / 365, 2) * 100;
        $data3 = date_create(date("y-m-01"));
        $diffMonth = date_diff($date1, $data3);
        $jsonData['month']['average'] = $diffMonth->days > 30 ? 100 : round($diffMonth->days / 30, 2) * 100;
        echo json_encode($jsonData);
    }

    //ajax月报
    public function getRunMonthRepoetDataByajaxAction() {
        $search = $this->getInput(['year', 'month']);
        if (empty($search['year']) || empty($search['month'])) return [];
        $params['year'] = $search['year'];
        $planList = MobgiData_Service_MobgiModel::getDao('ReportMonthApp')->getsBy($params);
        $sumData = array();
        foreach ($planList as $key => $val) {
            $where = array(
                'app_key' => $val['app_key'],
                'month' => $val['month'],
                'years' => $val['year'],
                'is_custom' => $val['is_custom']
            );
            $appFinish = MobgiData_Service_MobgiModel::getDao('ReportMonth')->getBy($where);
//            #求出每个月的总数
//            if(empty($sumData['month']['plan'][$val['month']])){
//                $sumData['month']['plan'][$val['month']] =0;
//            }
//            if(empty($sumData['month']['finish'][$val['month']])){
//                $sumData['month']['finish'][$val['month']] =0;
//            }
//            $sumData['month']['plan'][$val['month']] +=$val['ad_income'];
//            $sumData['month']['finish'][$val['month']] +=$appFinish['ad_income'];
            #分平台统计
            if ($val['month'] == $search['month']) {
                if ($val['platform'] == 2) {#ios不区分定制和非定制
                    $sumData['platform']['plan'][$val['platform']] += $val['ad_income'];
                    $sumData['platform']['finish'][$val['platform']] += $appFinish['ad_income'];
                } else {
                    $sumData['platform']['plan'][$val['platform']][$val['is_custom']] += $val['ad_income'];
                    $sumData['platform']['finish'][$val['platform']][$val['is_custom']] += $appFinish['ad_income'];
                }
            }
        }

        #读取月度KPI
        $where = array('years' => $params['year']);
        $monthPlanData = MobgiData_Service_MobgiModel::getDao('ReportWeekPlan')->getsBy($where);
        $monthFinishData = MobgiData_Service_MobgiModel::getDao('ReportWeek')->getsBy($where);
        foreach ($monthPlanData as $key => $val) {

            if ($val['month'] != 0) {
                $sumData['month']['plan'][$val['month']] += $val['ad_income'];
            }
        }

        foreach ($monthFinishData as $key => $val) {
            if (empty($sumData['month']['finish'][$val['month']])) {
                $sumData['month']['finish'][$val['month']] = 0;
            }
            $sumData['month']['finish'][$val['month']] += $val['ad_income'];
        }

        $jsonData = array();
        foreach ($sumData['month'] as $key => $val) {
            foreach ($val as $keys => $vals) {
                $jsonData['month']['finish_rate'][$keys] = $sumData['month']['finish'][$keys] > 0 ? round($sumData['month']['finish'][$keys] / $sumData['month']['plan'][$keys], 2) * 100 : 0;
                $jsonData['month']['nofinish_rate'][$keys] = $jsonData['month']['finish_rate'][$keys] > 100 ? 0 : 100 - $jsonData['month']['finish_rate'][$keys];
            }
        }

        foreach ($sumData['platform'] as $key => $val) {
            foreach ($val as $keys => $vals) {
                if ($keys == 1) {
                    $jsonData['platform'][$keys]['name'] = '安卓公版';
                    $jsonData['platform'][$keys]['value'] = $sumData['platform']['finish'][$keys][0];
                    $jsonData['platform'][$keys + 2]['name'] = '安卓定制';
                    $jsonData['platform'][$keys + 2]['value'] = $sumData['platform']['finish'][$keys][1];
                } else {#2
                    $jsonData['platform'][$keys]['name'] = 'IOS';
                    $jsonData['platform'][$keys]['value'] = $sumData['platform']['finish'][$keys];
                }
            }
        }

        #算基准线
        $date1 = date_create(date("Y-m-d", strtotime("-1 day")));
        $data3 = date_create(date("y-m-01"));
        $diffMonth = date_diff($date1, $data3);
        $jsonData['month']['average'] = $diffMonth->days > 30 ? 100 : round($diffMonth->days / 30, 2) * 100;
        echo json_encode($jsonData);
    }

    //数值样式转换(低于100%显示红色，高于100%显示绿色)
    public function exchangeNumColorAction($data, $type = 1, $useFlag = False) {
        if ($data > 100 && $type == 1) {
            if ($useFlag) {
                $data = "<span style='color:#009688'><i class='fa fa-long-arrow-up fa-lg'></i><b>" . $data . "</b></span>";
            } else {
                $data = "<span style='color:#009688'><b>" . $data . "</b></span>";
            }
        } elseif ($type == 1) {
            if ($useFlag) {
                $data = "<span style='color:red'><i class='fa fa-long-arrow-down fa-lg'></i><b>" . $data . "</b></span>";
            } else {
                $data = "<span style='color:red'><b>" . $data . "</b></span>";
            }
        }

        if ($data > 0 && $type == 2) {
            if ($useFlag) {
                $data = "<span style='color:#009688'><i class='fa fa-long-arrow-up fa-lg'></i><b>" . $data . "</b></span>";
            } else {
                $data = "<span style='color:#009688'><b>" . $data . "</b></span>";
            }
        } elseif ($type == 2) {
            if ($useFlag) {
                $data = "<span style='color:red'><i class='fa fa-long-arrow-down fa-lg'></i><b>" . $data . "</b></span>";
            } else {
                $data = "<span style='color:red'><b>" . $data . "</b></span>";
            }
        }
        return $data;
    }

    public function setKpiAction() {
        $params = ['data', 'type', 'years'];
        $inputData = $this->getInput($params);
        if (!empty($inputData['data'])) {
            unset($inputData['data']['LAY_TABLE_INDEX']);
            foreach ($inputData['data'] as $month => $income) {
                if ($month == 'total') {
                    $month = 0;
                }
                $where = array(
                    'type' => intval($inputData['type']),
                    'years' => $inputData['years'],
                    'month' => intval($month),
                );
                $data = array(
                    'ad_income' => $income,
                );
                MobgiData_Service_MobgiModel::getDao('ReportWeekPlan')->getBy($where);
                if (MobgiData_Service_MobgiModel::getDao('ReportWeekPlan')->getBy($where)) {
                    MobgiData_Service_MobgiModel::getDao('ReportWeekPlan')->updateBy($data, $where);
                } else {
                    $insertData = array_merge($data, $where);
                    MobgiData_Service_MobgiModel::getDao('ReportWeekPlan')->insert($insertData);
                }
            }
            $data = json_encode(array('success' => 1, 'data' => '修改成功!'));
            $this->output(0, 'ok', $data);
        }
    }

    //月应用明细
    public function monthAppReportAction() {
        $search = $this->getInput(['year', 'month']);
        $params = [];
        if ($search['year'] != NULL) {
            $params['year'] = $search['year'];
        } else {
            $params['year'] = date('Y');
        }
        if ($search['month'] != NULL) {
            $params['month'] = $search['month'];
        } else {
            $params['month'] = date('n');
        }
        $list = MobgiData_Service_MobgiModel::getDao('ReportMonthApp')->getsBy($params, array('platform' => 'desc'));#读取导入的数据
        $data = array();
        $sumData = array();
        $apiApp = array();
        foreach ($list as $item => &$val) {
            $where = array(
                'app_key' => $val['app_key'],
                'month' => $val['month'],
                'years' => $val['year'],
                'app_type' => $val['type'],
                'is_custom' => $val['is_custom'],
            );
            array_push($apiApp, $where['app_key']);
            $appPlan = MobgiData_Service_MobgiModel::getDao('ReportMonth')->getBy($where);
            if ($appPlan) {
                $val['mau_sys'] = $appPlan['mau'];
                $val['arpu_sys'] = $appPlan['arpu'];
                $val['game_cover_sys'] = $appPlan['game_cover'];
                $val['ad_income_sys'] = $appPlan['ad_income'];
            } else {
                $val['mau_sys'] = 0;
                $val['arpu_sys'] = 0;
                $val['game_cover_sys'] = 0;
                $val['ad_income_sys'] = 0;
            }
            $val['game_cover'] = $val['game_cover'] * 100;
            $val['game_cover_sys'] = $val['game_cover_sys'] * 100;
            if ($val['mau'] != 0)
                $val['mau_diff'] = $this->exchangeNumColorAction(round($val['mau_sys'] / $val['mau'], 2) * 100);
            else
                $val['mau_diff'] = $this->exchangeNumColorAction(0);

            $val['arpu_diff'] = $this->exchangeNumColorAction(round($val['arpu_sys'] - $val['arpu'], 3), 2);
            $val['game_cover_diff'] = $this->exchangeNumColorAction(round($val['game_cover_sys'] - $val['game_cover'], 2), 2);

            if ($val['ad_income'] != 0)
                $val['ad_income_diff'] = $this->exchangeNumColorAction(round($val['ad_income_sys'] / $val['ad_income'], 2) * 100);
            else
                $val['ad_income_diff'] = $this->exchangeNumColorAction(0);
            #$sumData['month']['plan_income'] +=$val['ad_income'];
            #$sumData['month']['finish_income'] +=$val['ad_income_sys'];
            if ($val['platform'] == 1) {
                $sumData['month']['andiro']['plan_income'] += $val['ad_income'];
                $sumData['month']['andiro']['finish_income'] += $val['ad_income_sys'];
            } else {
                $sumData['month']['ios']['plan_income'] += $val['ad_income'];
                $sumData['month']['ios']['finish_income'] += $val['ad_income_sys'];
            }
            $data[$val['type']][$val['platform']][] = $val;
        }

        #统计没有统计到的数据
        $groupBy = "group by platform,is_custom";
        $fields = "platform,is_custom,sum(ad_income) as ad_income,sum(game_cover) as game_cover,sum(arpu) as arpu,sum(mau) as mau";
        $where = array("app_key" => array('NOT IN', $apiApp), 'years' => $params['year'], 'month' => $params['month']);
        $orderBy = '';
        $otherData = MobgiData_Service_MobgiModel::getDao('ReportMonth')->getData($fields, $where, $groupBy, $orderBy);
        foreach ($otherData as $key => $val) {
            $other = $val;
            $other['mau_sys'] = $val['mau'];
            $other['arpu_sys'] = $val['arpu'];
            $other['game_cover_sys'] = $val['game_cover'];
            $other['ad_income_sys'] = $val['ad_income'];
            $other['mau'] = 0;
            $other['arpu'] = 0;
            $other['game_cover'] = 0;
            $other['ad_income'] = 0;
            $other['mau_diff'] = $this->exchangeNumColorAction(0);
            $other['ad_income_diff'] = $this->exchangeNumColorAction(0);
            $other['game_cover_diff'] = $this->exchangeNumColorAction(0);
            $other['arpu_diff'] = $this->exchangeNumColorAction(0);
            if ($val['platform'] == 1 && $val['is_custom'] == 1) {
                $other['app_name'] = "安卓定制渠道其他汇总";
            } elseif ($val['platform'] == 1 && $val['is_custom'] == 0) {
                $other['app_name'] = "安卓非定制渠道其他汇总";
            } else {
                $other['app_name'] = "IOS其他汇总";
            }
            if ($val['platform'] == 1) {
                $sumData['month']['andiro']['plan_income'] += $val['ad_income'];
                $sumData['month']['andiro']['finish_income'] += $val['ad_income_sys'];
            } else {
                $sumData['month']['ios']['plan_income'] += $val['ad_income'];
                $sumData['month']['ios']['finish_income'] += $val['ad_income_sys'];
            }
            $data[1][$val['platform']][] = $other;
        }
        #总概况重新统计
        $sumData['month']['andiro']['diff'] = $this->exchangeNumColorAction($sumData['month']['andiro']['finish_income'] - $sumData['month']['andiro']['plan_income'], 1);
        $sumData['month']['andiro']['finish_rate'] = empty($sumData['month']['andiro']['plan_income']) ? 0 : $this->exchangeNumColorAction(round($sumData['month']['andiro']['finish_income'] / $sumData['month']['andiro']['plan_income'], 2) * 100, 1);

        $sumData['month']['ios']['diff'] = $this->exchangeNumColorAction($sumData['month']['ios']['finish_income'] - $sumData['month']['ios']['plan_income'], 1);
        $sumData['month']['ios']['finish_rate'] = empty($sumData['month']['ios']['plan_income']) ? 0 : $this->exchangeNumColorAction(round($sumData['month']['ios']['finish_income'] / $sumData['month']['ios']['plan_income'], 2) * 100, 1);

        #读取月度KPI
        $where = array('years' => $params['year'], 'month' => $params['month']);
        $monthPlanData = MobgiData_Service_MobgiModel::getDao('ReportWeekPlan')->getsBy($where);
        $where['app_type'] = array('!=', 0);
        $monthFinishData = MobgiData_Service_MobgiModel::getDao('ReportWeek')->getsBy($where);
        foreach ($monthPlanData as $key => $val) {
            $sumData['month']['plan_income'] += $val['ad_income'];
        }

        foreach ($monthFinishData as $key => $val) {
            $sumData['month']['finish_income'] += $val['ad_income'];
        }


        $sumData['month']['diff'] = $this->exchangeNumColorAction($sumData['month']['finish_income'] - $sumData['month']['plan_income'], 1);
        $sumData['month']['finish_rate'] = empty($sumData['month']['plan_income']) ? 0 : $this->exchangeNumColorAction(round($sumData['month']['finish_income'] / $sumData['month']['plan_income'], 2) * 100, 1);

        $lastUpdate = MobgiData_Service_MobgiModel::getDao('ReportWeek')->getsBy(array('years' => $params['year']), array('update_time' => 'desc'));
        $sumData['lastUpdate'] = $lastUpdate[0]['update_time'];
        $this->assign('sumData', $sumData);
        $this->assign('month', $params['month']);
        $this->assign('year', $params['year']);
        $this->assign('data', $data);
    }

    //周应用明细
    public function weekAppReportAction() {
        //求出上周的dau,展示,ecpm,人均,Arpu,总收入
        if($this->getInput('date')){
            $days = $this->getInput('date');
        }else{
            $days = "this Monday";
        }
        $lastWeekFirstDay = date("Y-m-d H:i:s", strtotime($days) - 86400 * 7);
        $lastWeekEndDay = date("Y-m-d H:i:s", strtotime($days));
        $lastLastWeekFirstDay = date("Y-m-d H:i:s", strtotime($days) - 86400 * 14);
        $rateMap = ['dau', 'ad_income', 'impressions', 'arpu', 'perpeople', 'ecpm'];
        $platformMap = [1, 2, 'all'];
        $whereLastWeek['days'] = array(array('>=', $lastWeekFirstDay), array('<', $lastWeekEndDay));

        $data = array();
        $dauField = 'sum(user_dau) as dau,app_key,platform,is_custom';
        $financeField = 'sum(ad_income)*6.5 as ad_income,app_key,platform,is_custom';
        $impressionField = 'sum(impressions) as impressions,app_key,platform,is_custom';
        $dauWhere['days'] = array(array('>=', $lastWeekFirstDay), array('<', $lastWeekEndDay));


        $dauWhere['ad_type'] = 0;
        $dauWhere['channel_gid'] = 0;
        $data['lastWeek']['dau'] = MobgiData_Service_MobgiModel::getWeekData('dau', $dauWhere, $dauField);
        $data['lastWeek']['ad_income'] = MobgiData_Service_MobgiModel::getWeekData('finance', $whereLastWeek, $financeField);
        $data['lastWeek']['impressions'] = MobgiData_Service_MobgiModel::getWeekData('day', $whereLastWeek, $impressionField);

        #上上周
        $whereDoubleLastWeek['days'] = array(array('>=', $lastLastWeekFirstDay), array('<', $lastWeekFirstDay));
        $dauWhere['days'] = array(array('>=', $lastLastWeekFirstDay), array('<', $lastWeekFirstDay));
        $dauWhere['ad_type'] = 0;
        $dauWhere['channel_gid'] = 0;
        $data['doubleLastWeek']['dau'] = MobgiData_Service_MobgiModel::getWeekData('dau', $dauWhere, $dauField);
        $data['doubleLastWeek']['ad_income'] = MobgiData_Service_MobgiModel::getWeekData('finance', $whereDoubleLastWeek, $financeField);
        $data['doubleLastWeek']['impressions'] = MobgiData_Service_MobgiModel::getWeekData('day', $whereDoubleLastWeek, $impressionField);

        $sumData = array();

        //过滤汇总操作
        foreach ($data as $type => $val) {
            foreach ($val as $keys => $vals) {
                foreach ($vals as $valss) {
                    if (is_array($sumData[$type][$valss['platform']]['detail'][$valss['is_custom']][$valss['app_key']])) {
                        $sumData[$type][$valss['platform']]['detail'][$valss['is_custom']][$valss['app_key']] = array_merge($sumData[$type][$valss['platform']]['detail'][$valss['is_custom']][$valss['app_key']], $valss);
                    } else {
                        $sumData[$type][$valss['platform']]['detail'][$valss['is_custom']][$valss['app_key']] = $valss;
                    }
                    $sumData[$type][$valss['platform']]['dau'] += $valss['dau'];
                    $sumData[$type][$valss['platform']]['ad_income'] += $valss['ad_income'];
                    $sumData[$type][$valss['platform']]['impressions'] += $valss['impressions'];
                    $sumData[$type]['all']['dau'] += $valss['dau'];
                    $sumData[$type]['all']['ad_income'] += $valss['ad_income'];
                    $sumData[$type]['all']['impressions'] += $valss['impressions'];
                }
            }
        }
        $apps = MobgiData_Service_MobgiModel::getApps();
        foreach ($sumData as $type => $val) {
            foreach ($val as $platform => $vals) {
                if (!empty($vals['detail'])) {
                    foreach ($vals['detail'] as $isCustom => $valss) {
                        foreach ($valss as $appKey => $valsss) {
                            $sumData[$type][$platform]['detail'][$isCustom][$appKey]['arpu'] = empty($valsss['dau']) ? 0 : round($valsss['ad_income'] / $valsss['dau'], 3);
                            $sumData[$type][$platform]['detail'][$isCustom][$appKey]['ecpm'] = empty($valsss['impressions']) ? 0 : round($valsss['ad_income'] / $valsss['impressions'], 3);
                            $sumData[$type][$platform]['detail'][$isCustom][$appKey]['perpeople'] = empty($valsss['dau']) ? 0 : round($valsss['impressions'] / $valsss['dau'], 3);
                            $sumData[$type][$platform]['detail'][$isCustom][$appKey]['app_name'] = $apps[$appKey];
                        }
                    }
                }
                $sumData[$type][$platform]['arpu'] = empty($sumData[$type][$platform]['dau']) ? 0 : round($sumData[$type][$platform]['ad_income'] / $sumData[$type][$platform]['dau'], 3);
                $sumData[$type][$platform]['ecpm'] = empty($sumData[$type][$platform]['impressions']) ? 0 : round(($sumData[$type][$platform]['ad_income'] / $sumData[$type][$platform]['impressions']) * 1000, 3);
                $sumData[$type][$platform]['perpeople'] = empty($sumData[$type][$platform]['dau']) ? 0 : round($sumData[$type][$platform]['impressions'] / $sumData[$type][$platform]['dau'], 3);
            }
        }
        foreach ($sumData['lastWeek'] as $platform => $val) {
            if (!empty($val['detail'])) {
                foreach ($val['detail'] as $isCustom => $vals) {
                    foreach ($vals as $appKey => $valss) {
                        $sumData['lastWeek'][$platform]['detail'][$isCustom][$appKey]['income_rate'] = $this->exchangeNumColorAction(round(empty(intval($sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['ad_income'])) ? 0 : ($sumData['lastWeek'][$platform]['detail'][$isCustom][$appKey]['ad_income'] - $sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['ad_income']) / $sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['ad_income'], 3), 2);
                        $sumData['lastWeek'][$platform]['detail'][$isCustom][$appKey]['impressions_rate'] = $this->exchangeNumColorAction(round(empty($sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['impressions']) ? 0 : ($sumData['lastWeek'][$platform]['detail'][$isCustom][$appKey]['impressions'] - $sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['impressions']) / $sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['impressions'], 3), 2);
                        $sumData['lastWeek'][$platform]['detail'][$isCustom][$appKey]['dau_rate'] = $this->exchangeNumColorAction(round(empty($sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['dau']) ? 0 : ($sumData['lastWeek'][$platform]['detail'][$isCustom][$appKey]['dau'] - $sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['dau']) / $sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['dau'], 3), 2);
                        $sumData['lastWeek'][$platform]['detail'][$isCustom][$appKey]['arpu_rate'] = $this->exchangeNumColorAction(round(empty($sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['arpu']) ? 0 : ($sumData['lastWeek'][$platform]['detail'][$isCustom][$appKey]['arpu'] - $sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['arpu']) / $sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['arpu'], 3), 2);
                        $sumData['lastWeek'][$platform]['detail'][$isCustom][$appKey]['ecpm_rate'] = $this->exchangeNumColorAction(round(empty($sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['ecpm']) ? 0 : ($sumData['lastWeek'][$platform]['detail'][$isCustom][$appKey]['ecpm'] - $sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['ecpm']) / $sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['ecpm'], 3), 2);
                        $sumData['lastWeek'][$platform]['detail'][$isCustom][$appKey]['perpeople_rate'] = $this->exchangeNumColorAction(round(empty($sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['perpeople']) ? 0 : ($sumData['lastWeek'][$platform]['detail'][$isCustom][$appKey]['perpeople'] - $sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['perpeople']) / $sumData['doubleLastWeek'][$platform]['detail'][$isCustom][$appKey]['perpeople'], 3), 2);
                    }
                }
            }
        }

        foreach ($rateMap as $rate) {
            foreach ($platformMap as $platform) {
                $sumData['rate'][$platform][$rate] = $this->exchangeNumColorAction(round(($sumData['lastWeek'][$platform][$rate] - $sumData['doubleLastWeek'][$platform][$rate]) / $sumData['doubleLastWeek'][$platform][$rate], 3) * 100, 2, true);
            }
        }

        $this->assign('now',$days);
        $this->assign('lastWeekFirstDay', $lastWeekFirstDay);
        $this->assign('lastWeekEndDay', $lastWeekEndDay);
        $this->assign('lastLastWeekFirstDay', $lastLastWeekFirstDay);
        #$this->assign('data',$data);
        $this->assign('sumData', $sumData);
    }

    //应用明细KPI
    public function appDetailKpiAction() {
        $apps = MobgiData_Service_MobgiModel::getApps();
        $this->assign('apps', $apps);
    }

    //kpi列表
    public function weekKpiAction() {
        $search = $this->getInput(['years']);
        $params = [];
        if ($search['years'] != NULL) {
            $params['years'] = $search['years'];
        } else {
            $params['years'] = date('Y');
        }

        $planList = MobgiData_Service_MobgiModel::getDao('ReportWeekPlan')->getsBy($params);
        $data = array();
        foreach ($planList as $item => $val) {
            $data[$val['type']][$val['month']]['plan'] = floatval($val['ad_income']);
        }

        $lastUpdate = MobgiData_Service_MobgiModel::getDao('ReportWeek')->getsBy($params, array('update_time' => 'desc'));
        $lastUpdateDay = $lastUpdate[0]['update_time'];
        $this->assign('lastUpdate', $lastUpdateDay);
        $this->assign('years', $params['years']);
        $this->assign('data', $data);
    }

    //weekappkpi列表
    public function weekAppKpiAction() {
        $search = $this->getInput(['years', 'month', 'app_key']);
        $params = [];
        if ($search['years'] != NULL) {
            $params['year'] = $search['years'];
        } else {
            $params['year'] = date('Y');
        }
        if ($search['month'] != NULL) {
            $params['month'] = $search['month'];
        } else {
            $params['month'] = date('n');
        }
        if ($search['app_key'] != NULL) {
            $params['app_key'] = $search['app_key'];
        }
        $list = MobgiData_Service_MobgiModel::getDao('ReportMonthApp')->getsBy($params);
        foreach ($list as $item => &$val) {
            $val['game_cover'] = ($val['game_cover'] * 100) . '%';
            if ($val['platform'] == 1) $val['platform'] = '安卓'; else $val['platform'] = 'IOS';
        }

        $apps = MobgiData_Service_MobgiModel::getApps();
        $this->assign('apps', $apps);
        $this->assign('month', $params['month']);
        $this->assign('years', $params['year']);
        $this->assign('data', $list);
    }

    //修改应用KPI
    public function editAppKpiAction() {
        $ids = $this->getInput(['id']);
        if ($_POST) {
            $data = array(
                'arpu' => round($_POST['arpu'], 3),
                'game_cover' => round($_POST['game_cover'], 3),
                'ad_income' => round($_POST['ad_income'], 2),
                'mau' => intval($_POST['mau'])
            );
            $where = array(
                'id' => intval($_POST['id']),
            );
            if (MobgiData_Service_MobgiModel::getDao('ReportMonthApp')->updateBy($data, $where)) {
                echo 1;
            } else {
                echo 0;
            }
        } else {
            $data = MobgiData_Service_MobgiModel::getDao('ReportMonthApp')->get($ids['id']);
            $this->assign('data', $data);
        }
    }

    //新增应用明细KPI
    public function setAppKpiAction() {
        if ($_FILES) {
            $tmp_file = $_FILES['file']['tmp_name'];
            if (!file_exists($tmp_file)) {
                echo -1;
                die;
            } else {
                $data = $this->readExcel($tmp_file);#读取excel文件
                if ($data == false) {
                    echo -1;
                    die;
                }
                foreach ($data as $key => $val) {
                    $where = array('app_key' => trim($val['C']));
                    $appInfo = MobgiData_Service_MobgiModel::getDao('ConfigApp')->getBy($where);
                    $check_select = array(
                        'app_key' => trim($val['C']),
                        'month' => $val['B'],
                        'year' => $val['A'],
                        'platform' => $appInfo['platform'],
                        'type' => $appInfo['app_type'],
                        'is_custom' => $val['F']
                    );
                    $check_result = MobgiData_Service_MobgiModel::getDao('ReportMonthApp')->getBy($check_select);
                    if ($check_result == false) {
                        $add_data = array(
                            'app_key' => trim($val['C']),
                            'month' => $val['B'],
                            'year' => $val['A'],
                            'platform' => $appInfo['platform'],
                            'type' => $appInfo['app_type'],
                            'app_name' => $appInfo['app_name'],
                            'is_custom' => $val['F'],
                            'game_cover' => round($val['H'], 2),
                            'arpu' => round($val['I'], 3),
                            'ad_income' => round($val['J'], 2),
                            'mau' => intval($val['G'])
                        );
                        if (MobgiData_Service_ThirdApiModel::getDao('ReportMonthApp')->insert($add_data) == false) {
                            echo -1;
                            die;
                        }
                    }
                }
                echo 1;
                die;
            }
        }
    }


    private function readExcel($file) {
        Yaf_loader::import("Util/PHPExcel/PHPExcel.php");
        Yaf_loader::import("Util/PHPExcel/PHPExcel/IOFactory.php");#引入
        $reader = PHPExcel_IOFactory::createReader('Excel5');
        $PHPExcel = $reader->load($file);// 文档名称
        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数
        $data = array();
        for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
            for ($colIndex = 'A'; $colIndex <= $highestColumm; $colIndex++) {
                $addr = $colIndex . $rowIndex;
                if ($colIndex == 'C' || $colIndex == 'D' || $colIndex == 'E') {
                    //数值必须是float类型
                    $cell = $sheet->getCell($addr)->getValue();
                    if (is_string($cell)) {
                        $cell = $sheet->getCell($addr)->getCalculatedValue();
                    }
                } else {
                    $cell = $sheet->getCell($addr)->getValue();
                }
                $data[$rowIndex][$colIndex] = $cell;
            }
        }
        return $data;
    }

    public function getKpiAction() {
        $search = ['years', 'type'];
        $params = $this->getInput($search);
        if ($params['years'] || $params['type']) {
            $where = array(
                'years' => $params['years'],
                'type' => $params['type'],
            );

            $info = MobgiData_Service_MobgiModel::getDao('ReportWeekPlan')->getsBy($where);
        } else {
            $where = array(
                'years' => date("Y", time()),
                'type' => 1,
            );
            $info = MobgiData_Service_MobgiModel::getDao('ReportWeekPlan')->getsBy($where);
        }
        $data = array();
        if (empty($info)) {
            $data['data'][] = array(
                'total' => '0',
                '1' => '0',
                '2' => '0',
                '3' => '0',
                '4' => '0',
                '5' => '0',
                '6' => '0',
                '7' => '0',
                '8' => '0',
                '9' => '0',
                '10' => '0',
                '11' => '0',
                '12' => '0',
            );
        } else {
            $total = 0;
            foreach ($info as $key => $val) {
                $data['data'][0][$val['month']] = strval($val['ad_income']);
                if ($val['month'] != 0) {
                    $total += $val['ad_income'];
                }
            }
            $data['data'][0]['total'] = strval($total);
        }
        $data['code'] = 0;
        $data['count'] = 300000;
        echo json_encode($data);
    }


    //用户配置日志变更
    public function weightLogAction() {
        $page = intval($this->getInput('page'));
        $search = $this->getInput(['appkey', 'sdate', 'edate', 'ad_type']);
        if ($page < 1) $page = 1;
        $params = [];
        if ($search['appkey'] != NULL || (!empty($search['sdate']) || !empty($search['edate'])) || $search['ad_type'] != NULL) {
            $sdate = $search['sdate'];
            $edate = $search['edate'];
            if ($search['sdate'] == $search['edate']) {
                $params['effect_time'] = ['>', date("Y-m-d H:i:s", strtotime($search['sdate'])), '<', date("Y-m-d H:i:s", strtotime($search['edate']) + 86399)];
            } else {
                $params['effect_time'] = ['>', date("Y-m-d H:i:s", strtotime($search['sdate'])), '<', date("Y-m-d H:i:s", strtotime($search['edate']) + 86399)];
            }
            if ($search['appkey'] != -1) {
                $params['app_key'] = $search['appkey'];
            }
            if ($search['ad_type'] != -1) {
                $params['ad_type'] = $search['ad_type'];
            }
        }

        $List = MobgiData_Service_MobgiModel::getDao('ReportWeightLog')->getList(($page - 1) * $this->perpage, $this->perpage * $page, $params, ['id' => "DESC"]);

        $total = MobgiData_Service_MobgiModel::getDao('ReportWeightLog')->count($params);
        foreach ($List as $key => &$val) {
            $tmp = json_decode($val['ads_positon_list'], true);
            $ads_weight = array();
            foreach ($tmp as $ads_id => $tmp_val) {
                if ($tmp_val['weight'] > 0) {
                    $ads_weight[] = $ads_id . ':' . $tmp_val['weight'];
                }
            }
            $val['ads_positon_list'] = implode(',', $ads_weight);
        }

        $applist = MobgiData_Service_ThirdApiModel::getDao('ConfigApp')->getsBy(['status' => 1], ['app_name' => 'asc']);


        $appMap = [];
        foreach ($applist as $item) {
            $appMap[$item['app_key']] = $item['app_name'];

        }

        $url = $this->actions['weightLogList'] . '/?' . http_build_query($search) . '&';
        if (empty($sdate) && empty($edate)) {
            $sdate = date("Y-m-d");
            $edate = date("Y-m-d");
        }

        $this->assign('ad_type', Common_Service_Config::AD_TYPE);
        $this->assign('params', $params);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('applist', $applist);
        $this->assign('appMap', $appMap);
        $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        $this->assign('search', $search);
        $this->assign('list', $List);
    }

    //用户留存数据
    public function RetentionAction() {
        $page = intval($this->getInput('page'));
        $search = $this->getInput(['appkey', 'sdate', 'edate']);
        if ($page < 1) $page = 1;
        $params = [];
        if ($search['appkey'] != NULL || (!empty($search['sdate']) || !empty($search['edate']))) {
            $sdate = $search['sdate'];
            $edate = $search['edate'];
            if ($search['sdate'] == $search['edate']) {
                $params['days'] = $search['sdate'];
            } else {
                $params['days'] = ['>', $search['sdate'], '<', $search['edate']];
            }
            if ($search['appkey'] != -1) {
                $params['app_key'] = $search['appkey'];
            }
        }
        if (empty($params['days'])) {
            $params['days'] = date('Y-m-d', strtotime("-2 days"));
        }
        $RetentionList = MobgiData_Service_ThirdApiModel::getDao('ReportRetention')->getsBy($params, ['id' => "DESC"]);
        foreach ($RetentionList as $key => &$val) {
            $app_name = MobgiData_Service_ThirdApiModel::getDao('ConfigApp')->getAllByFields('app_name', ['app_key' => $val['app_key']]);
            $new_user = MobgiData_Service_ThirdApiModel::getDao('ReportDau')->getAllByFields("sum(new_user),sum(user_dau)", [
                'app_key' => $val['app_key'],
                'days' => $val['days'],
                'channel_gid' => 0,
                'ad_type' => 0
            ]);
            $val['newuser'] = $new_user[0]['sum(new_user)'];
            $val['app_name'] = $app_name[0]['app_name'];
            $val['dau'] = $new_user[0]['sum(user_dau)'];
            $val['r1'] = empty($val['newuser']) ? $val['r1'] : number_format(($val['r1'] / $val['newuser']) * 100, 2) . '%';
            $val['r2'] = empty($val['newuser']) ? $val['r2'] : number_format(($val['r2'] / $val['newuser']) * 100, 2) . '%';
            $val['r3'] = empty($val['newuser']) ? $val['r3'] : number_format(($val['r3'] / $val['newuser']) * 100, 2) . '%';
            $val['r4'] = empty($val['newuser']) ? $val['r4'] : number_format(($val['r4'] / $val['newuser']) * 100, 2) . '%';
            $val['r5'] = empty($val['newuser']) ? $val['r5'] : number_format(($val['r5'] / $val['newuser']) * 100, 2) . '%';
            $val['r6'] = empty($val['newuser']) ? $val['r6'] : number_format(($val['r6'] / $val['newuser']) * 100, 2) . '%';
            $val['r7'] = empty($val['newuser']) ? $val['r7'] : number_format(($val['r7'] / $val['newuser']) * 100, 2) . '%';
        }

        $applist = MobgiData_Service_ThirdApiModel::getDao('ConfigApp')->getFields('app_key,app_name', ['status' => 1]);
        $url = $this->actions['retentionList'] . '/?' . http_build_query($search) . '&';
        if (empty($sdate) && empty($edate)) {
            $sdate = $params['days'];
            $edate = $params['days'];
        }
        $this->assign('params', $params);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('applist', $applist);
        $this->assign('search', $search);
        $this->assign('list', $RetentionList);
    }

    //    参数转换
    private function exchangeParamsArray($params) {
        $arrParams = [];
        foreach ($params as $key => $val) {
            if (!is_null($val)) {
                if (in_array($key, ['sdate', 'edate', 'is_custom'])) {
                    $arrParams[$key] = $val;
                } else {
                    $arrParams[$key] = explode(',', trim($val, ','));
                }
            }
        }
        return $arrParams;
    }


    //    获取维度
    private function getChartDims() {
        //        获取用户权限
        $userId = $this->userInfo['uid'];
        //        $paramAccount = is_array($userId) ? array('account_id' => array('in', $userId)) : array('account_id' => $userId);
        $paramAccount = [];
        $OriginalityIds = Dedelivery_Service_OriginalityRelationModel::getOriginalityIdOfAccount($paramAccount);
        $param = ['id' => ['in', $OriginalityIds]];
        $param = [];
        //        originality_id
        $dimOfOriginalityId = Dedelivery_Service_OriginalityRelationModel::getFields('id,title');
        //        originality_type
        $dimOfOriginalityType = Advertiser_Service_OriginalityConfModel::getOriginalityType();
        //        app_key
        $dimOfAppKey = Advertiser_Service_OriginalityRelationPositionModel::getAppKey($param);
        //        block_id
        $dimOfBlockId = Advertiser_Service_OriginalityRelationPositionModel::getBlockId($param);
        //        unit_id
        $dimOfUnitId = Dedelivery_Service_UnitConfModel::getUnitId($param);
        //        platform
        $dimOfPlatform = Common::getConfig('deliveryConfig', 'osTypeList');
        //        account_id
        $dimOfAccountId = Admin_Service_UserModel::getAcount();
        //        ad_id
        $dimOfAdId = Dedelivery_Service_AdConfListModel::getFields('id,ad_name');

        $dims = [
            'originality_id' => $dimOfOriginalityId,
            'originality_type' => $dimOfOriginalityType,
            'app_key' => $dimOfAppKey,
            'block_id' => $dimOfBlockId,
            'platform' => $dimOfPlatform,
            'unit_id' => $dimOfUnitId,
            'account_id' => $dimOfAccountId,
            'ad_id' => $dimOfAdId,
        ];
        return $dims;

    }

    private function getChartRelation() {
        //        获取用户权限
        $userId = $this->userInfo['uid'];
        $param = is_array($userId) ? ['account_id' => ['in', $userId]] : ['account_id' => $userId];
        $param = [];
        $OriginalityIds = Dedelivery_Service_OriginalityRelationModel::getOriginalityIdOfAccount($param);
        //        级联关系:账号==》投放单元==》广告==》创意
        $accountRelation = Dedelivery_Service_OriginalityRelationModel::getRelationOfAccount($param);
        //        创意类型==》应用
        $OrigAppkeyRelation = Advertiser_Service_OriginalityRelationPositionModel::getRelationOfAppkey([
            'originality_conf_id' => [
                'in',
                $OriginalityIds,
            ],
        ]);
        return array_merge($accountRelation, $OrigAppkeyRelation);
    }


    //ABTest数据
    public function abtestAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-d', strtotime('-7 days'));
            $edate = date("Y-m-d", strtotime('-1 days'));
        }
        $userId = $this->getUserId();

        $filterFields = array_merge(MobgiData_Service_AbTestModel::getFilterFields(), self::$filterFields);
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);

        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', json_encode(MobgiData_Service_AbTestModel::getChartConf($userId, $params)));
    }

    public function getAbtestDataAction() {
        $data = [
            'table' => [],
            'total' => [],
        ];
        $filterFields = array_merge(MobgiData_Service_MobgiModel::getFilterFields(), self::$filterFields);
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);
        $data['table'] = MobgiData_Service_AbTestModel::getData($params, $this->getUserId());
        $data['total'] = MobgiData_Service_AbTestModel::getTotal($data['table'], $params);
        $this->output(0, "", $data);
    }


}
