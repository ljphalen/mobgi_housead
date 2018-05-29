<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * Created by PhpStorm.
 * User: atom.zhan
 * Date: 2017/12/21
 * Time: 09:47
 */
class Spm_ReportController extends Admin_BaseController {

    public function init() {
        parent::init();
        $module = $this->getTopModule();
        $this->assign('module', $module);
    }

    public static $filterFields = ['sdate', 'edate', 'dims', 'kpis', 'app_id', 'type'];

    public function getAppId() {
        return isset($_COOKIE['app_id']) ? intval($_COOKIE['app_id']) : 0;
    }

    public static $defaultRetetionDays = [1 => '第一天', 2 => '第二天', 3 => '第三天', 4 => '第四天', 5 => '第五天', 6 => '第六天', 7 => '第七天'];//默认留存天数

    public static $defaultRetentionBase = ['app_id' => '应用', 'create_date' => '日期'];//默认留存指标


    /**
     * 获取字段
     * @param $filterFields
     * @return array
     */
    public function getMyParams($filterFields) {
        $params = $this->getInput($filterFields, '#s_t');
        $arrParams = [];
        foreach ($params as $key => $val) {
            if (!is_null($val)) {
                if (in_array($key, ['sdate', 'edate',  'type'])) {
                    $arrParams[$key] = $val;
                } else {
                    $arrParams[$key] = explode(',', trim($val, ','));
                }
            }
        }
        return $arrParams;
    }

    /**
     * 产品概览
     */
    public function indexAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-01');
            $edate = date("Y-m-d");
        }
        $filterFields = array_merge(MobgiSpm_Service_ReportModel::getFilterFields(), self::$filterFields);
        $params = $this->getMyParams($filterFields);
        $params['app_id'] = $this->getAppId();
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
    }

    /**
     * 活动报表
     */
    public function activityAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        $appId = intval($this->getInput('appid'));
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-d', strtotime('-14 days'));
            $edate = date("Y-m-d", strtotime('-1 days'));
        }
        MobgiSpm_Service_ReportModel::setUserId($this->getUserId());
        MobgiSpm_Service_ReportModel::setUserName($this->getUserName());

        $filterFields = array_merge(MobgiSpm_Service_ReportModel::getFilterFields(), self::$filterFields);
        $params = $this->getMyParams($filterFields);
        $params['app_id'] = $appId ?: $this->getAppId();
        $this->assign('appid', $appId);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', json_encode(MobgiSpm_Service_ReportModel::getChartConf($params)));
    }

    /**
     * spm报表
     */
    public function apkAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-d', strtotime('-14 days'));
            $edate = date("Y-m-d", strtotime('-1 days'));
        }
        MobgiSpm_Service_ReportModel::setUserId($this->getUserId());
        MobgiSpm_Service_ReportModel::setUserName($this->getUserName());

        $filterFields = array_merge(MobgiSpm_Service_ReportModel::getFilterFields(), self::$filterFields);
        $params = $this->getMyParams($filterFields);
        $params['app_id'] = $this->getAppId();
        $params['type'] = 'channel';
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', json_encode(MobgiSpm_Service_ReportModel::getChartConf($params)));
    }


    public function ltvAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-d', strtotime('-14 days'));
            $edate = date("Y-m-d", strtotime('-1 days'));
        }
        MobgiSpm_Service_ReportModel::setUserId($this->getUserId());
        MobgiSpm_Service_ReportModel::setUserName($this->getUserName());

        $filterFields = array_merge(MobgiSpm_Service_ReportModel::getFilterFields(), self::$filterFields);
        $params = $this->getMyParams($filterFields);
        $params['app_id'] = $this->getAppId();

        $chartConfig = MobgiSpm_Service_ReportModel::getLtvChartConf($params);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', json_encode($chartConfig));
    }

    public function apkltvAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-d', strtotime('-14 days'));
            $edate = date("Y-m-d", strtotime('-1 days'));
        }
        MobgiSpm_Service_ReportModel::setUserId($this->getUserId());
        MobgiSpm_Service_ReportModel::setUserName($this->getUserName());

        $filterFields = array_merge(MobgiSpm_Service_ReportModel::getFilterFields(), self::$filterFields);
        $params = $this->getMyParams($filterFields);
        $params['app_id'] = $this->getAppId();
        $params['type'] = 'channel';

        $chartConfig = MobgiSpm_Service_ReportModel::getLtvChartConf($params);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', json_encode($chartConfig));
    }

    public function getSpmDataAction() {
        $data = [
            'table' => [],
            'total' => [],
        ];
        $filterFields = array_merge(MobgiSpm_Service_ReportModel::getFilterFields(), self::$filterFields);
        $params = $this->getMyParams($filterFields);
        $type = $this->getGet('type');
        if (!empty($type)) {
            $params['type'] = $type;
        }

        MobgiSpm_Service_ReportModel::setUserId($this->getUserId());
        MobgiSpm_Service_ReportModel::setUserName($this->getUserName());
        MobgiSpm_Service_ReportModel::setAppId($this->getAppId());

        $data['table'] = MobgiSpm_Service_ReportModel::getData($params);
        $data['total'] = MobgiSpm_Service_ReportModel::getTotal($data['table'], $params);
        $this->output(0, "", $data);
    }


    public function getLtvDataAction() {
        $filterFields = array_merge(MobgiSpm_Service_ReportModel::getFilterFields(), self::$filterFields);
        $params = $this->getMyParams($filterFields);
        $type = $this->getGet('type');
        if (!empty($type)) {
            $params['type'] = $type;
        }
        MobgiSpm_Service_ReportModel::setUserId($this->getUserId());
        MobgiSpm_Service_ReportModel::setUserName($this->getUserName());
        MobgiSpm_Service_ReportModel::setAppId($this->getAppId());

        $data = MobgiSpm_Service_ReportModel::getLtvData($params);
        $this->output(0, "", $data);
    }


    public function getRetentionDataAction() {
        $filterFields = array_merge(MobgiSpm_Service_ReportModel::getFilterFields(), self::$filterFields);
        $params = $this->getMyParams($filterFields);
        $type = $this->getGet('type');
        if (!empty($type)) {
            $params['type'] = $type;
        }
        MobgiSpm_Service_ReportModel::setUserId($this->getUserId());
        MobgiSpm_Service_ReportModel::setUserName($this->getUserName());
        MobgiSpm_Service_ReportModel::setAppId($this->getAppId());

        $data = MobgiSpm_Service_ReportModel::getRetentionData($params);
        $this->output(0, "", $data);
    }


    public function retentionAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-d', strtotime('-14 days'));
            $edate = date("Y-m-d", strtotime('-1 days'));
        }
        MobgiSpm_Service_ReportModel::setUserId($this->getUserId());
        MobgiSpm_Service_ReportModel::setUserName($this->getUserName());

        $filterFields = array_merge(MobgiSpm_Service_ReportModel::getFilterFields(), self::$filterFields);
        $params = $this->getMyParams($filterFields);
        $params['app_id'] = $this->getAppId();

        $chartConfig = MobgiSpm_Service_ReportModel::getRetentionChartConf($params);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', json_encode($chartConfig));
    }

    public function apkretentionAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-d', strtotime('-14 days'));
            $edate = date("Y-m-d", strtotime('-1 days'));
        }
        MobgiSpm_Service_ReportModel::setUserId($this->getUserId());
        MobgiSpm_Service_ReportModel::setUserName($this->getUserName());

        $filterFields = array_merge(MobgiSpm_Service_ReportModel::getFilterFields(), self::$filterFields);
        $params = $this->getMyParams($filterFields);
        $params['app_id'] = $this->getAppId();
        $params['type'] = 'channel';

        $chartConfig = MobgiSpm_Service_ReportModel::getRetentionChartConf($params);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', json_encode($chartConfig));
    }

    public function updateSpmKpiAction() {
        $type = 'spm';
        $userId = $this->getUserId();
        $kpis = $this->getInput('kpis', '#s_t');
        $result = MobgiData_Service_MobgiModel::updateUserKpi($userId, $type, $kpis);
        $this->output(0, 'OK');
    }

    public function dailyAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-03-01');
            $edate = date("Y-m-d");
        }
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
    }

    public function weeklyAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-01');
            $edate = date("Y-m-d");
        }
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
    }

    public function getMonthPlanAction() {
        $filterFields = ['sdate', 'edate', 'app_id'];
        $params = $this->getMyParams($filterFields);
        MobgiSpm_Service_ReportModel::setAppId($this->getAppId());
        $params['dims'] = ['months', 'app_id'];
        $params['kpis'] = [
            'daily_consumption',
            'daily_amount',
            'daily_cost',
            'registers',
            'real_consumption',
            'cost',
            'kpi_rate',
            'consumption_rate',
            'cost_rate'
        ];

        $data = MobgiSpm_Service_ReportModel::getData($params);
        $this->output(0, "", $data);
    }

    // 每日总体概况
    public function getGeneralAction() {
        $filterFields = ['sdate', 'edate', 'dims', 'kpis', 'app_id'];
        $params = $this->getMyParams($filterFields);
        MobgiSpm_Service_ReportModel::setAppId($this->getAppId());
        $data = MobgiSpm_Service_ReportModel::getData($params);
        $this->output(0, "", $data);
    }

    // 每日总体概况
    public function getChannel7DayAction() {
        $filterFields = ['sdate', 'edate', 'dims', 'kpis', 'app_id'];
        $params = $this->getMyParams($filterFields);
        MobgiSpm_Service_ReportModel::setAppId($this->getAppId());
        //        $params['dims'] = ['days'];
        //        $params['kpis'] = [
        //
        //            'real_consumption',
        //            'registers',
        //            'retention_stay1',
        //            'retention1',
        //            'cost',
        //            'income_new_user',
        //            'ltv1'
        //        ];

        //        $params['sdate'] = date('Y-m-d', strtotime('-7 days'));
        //        $params['edate'] = date("Y-m-d");

        $res['data'] = MobgiSpm_Service_ReportModel::getData($params);
        $res['cmap'] = MobgiSpm_Service_ReportModel::getChannelGroupMap();
        $this->output(0, "", $res);
    }

}