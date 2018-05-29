<?php
if (!defined('BASE_PATH')) exit('Access Denied!');


class Interative_ReportController extends Admin_BaseController {


    public static $filterFields = ['sdate', 'edate', 'dims', 'kpis', 'app_id', 'type'];


    /**
     * 获取字段
     * @param $filterFields
     * @return array
     */
    public function getParamsByFilters($filterFields) {
        $params = $this->getInput($filterFields, '#s_t');
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


    /**
     * Qr报表
     */
    public function qrAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-d', strtotime('-14 days'));
            $edate = date("Y-m-d", strtotime('-1 days'));
        }
        MobgiData_Service_InteractiveModel::setUserId($this->getUserId());
        MobgiData_Service_InteractiveModel::setUserName($this->getUserName());
        $filterFields = array_merge(MobgiData_Service_InteractiveModel::getFilterFields(), self::$filterFields);
        $params = $this->getParamsByFilters($filterFields);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', json_encode(MobgiData_Service_InteractiveModel::getQrChartConf($params)));

    }

    /**
     * 互动报表
     */
    public function indexAction() {
        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        if (empty($sdate) && empty($edate)) {
            $sdate = date('Y-m-d', strtotime('-14 days'));
            $edate = date("Y-m-d", strtotime('-1 days'));
        }
        MobgiData_Service_InteractiveModel::setUserId($this->getUserId());
        MobgiData_Service_InteractiveModel::setUserName($this->getUserName());
        $filterFields = array_merge(MobgiData_Service_InteractiveModel::getFilterFields(), self::$filterFields);
        $params = $this->getParamsByFilters($filterFields);
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', json_encode(MobgiData_Service_InteractiveModel::getChartConf($params)));
    }

    //  获取Qr数据
    public function getQrDataAction() {
        $data = [
            'table' => [],
            'total' => [],
        ];
        $filterFields = array_merge(MobgiData_Service_InteractiveModel::getFilterFields(), self::$filterFields);
        $params = $this->getParamsByFilters($filterFields);

        $data['table'] = MobgiData_Service_InteractiveModel::getData($params);
        $data['total'] = MobgiData_Service_InteractiveModel::getTotal($data['table'], $params);
        $this->output(0, "", $data);
    }

    //  获取互动式广告数据
    public function getDataAction() {
        $data = [
            'table' => [],
            'total' => [],
        ];
        $filterFields = array_merge(MobgiData_Service_InteractiveModel::getFilterFields(), self::$filterFields);
        $params = $this->getParamsByFilters($filterFields);

        $data['table'] = MobgiData_Service_InteractiveModel::getData($params);
        $data['total'] = MobgiData_Service_InteractiveModel::getTotal($data['table'], $params);
        $this->output(0, "", $data);
    }

    public function updateQrKpiAction() {
        $type = 'qr';
        $userId = $this->getUserId();
        $kpis = $this->getInput('kpis', '#s_t');
        $result = MobgiData_Service_MobgiModel::updateUserKpi($userId, $type, $kpis);
        $this->output(0, 'OK');
    }

    public function updateKpiAction() {
        $type = 'inter';
        $userId = $this->getUserId();
        $kpis = $this->getInput('kpis', '#s_t');
        $result = MobgiData_Service_MobgiModel::updateUserKpi($userId, $type, $kpis);
        $this->output(0, 'OK');
    }


}