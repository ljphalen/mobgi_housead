<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class StatController extends Admin_BaseController {

    public $actions = array(
        'listUrl' => '/Admin/Stat/pv',
        'monkeytime' => '/Admin/Stat/monkeytime',
    );

    public $perpage = 20;

    /**
     *
     * Enter description here ...
     */
    public function indexAction() {

        $sdate = $this->getInput('sdate');
        $edate = $this->getInput('edate');
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        if (empty($sdate) && empty($edate)) {
            $sdate = date("Y-m-d");
            $edate = date("Y-m-d");
        }
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
        $this->assign('chartConfig', $this->getChartConf());
    }


    public function updateKpiAction() {
        $kpis = $this->getInput('kpis');
        $accountId = $this->userInfo['uid'];
        $info = HouseAdStat_Service_ReportKpiConfModel::getByID($accountId);
        $data['kpis'] = $kpis;
        if (empty($info)) {
            $data['user_id'] = $accountId;
            $data['created_at'] = time();
            $re = HouseAdStat_Service_ReportKpiConfModel::save($data);
        } else {
            $data['updated_at'] = time();
            $re = HouseAdStat_Service_ReportKpiConfModel::updateByID($data, $accountId);
        }
        $this->output(1, 'OK');

    }

    public function exportDataAction() {
        $data = [
            'table' => [],
            'total' => []
        ];
        $filterFields = HouseAdStat_Service_ReportBaseModel::$filterFields;
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);
        $data = HouseAdStat_Service_ReportBaseModel::getData($params);

        if (isset($params['theader'])) {
            $header = $params['theader'];
            $result = [];
            $kpiConf = HouseAdStat_Service_ReportBaseModel::$kpiConf;
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

    public function getDataAction() {

        $data = [
            'table' => [],
            'total' => []
        ];
        $filterFields = HouseAdStat_Service_ReportBaseModel::$filterFields;
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);
        $data['table'] = HouseAdStat_Service_ReportBaseModel::getData($params);
        $data['total'] = HouseAdStat_Service_ReportBaseModel::getTotal($data['table']);
        $this->output(1, "", $data);
    }

//    参数转换
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
                "data" => '/Admin/Stat/getData',
                "conf" => '/Admin/Stat/updateKpi',
            ],
            'kpiConf' => HouseAdStat_Service_ReportBaseModel::$kpiConf,
            'kpi' => [],
            'dim' => [
                "default_dim_dom" => "#dim",
                "default_dim_fields" => [
                    "date" => "日期",
                    "hour" => "小时",
                    "hr1" => "-",
                    "account_id" => "账号",
                    "unit_id" => "投放单元",
                    "ad_id" => "广告",
                    "originality_id" => "创意",
                    "hr2" => "-",
                    "ad_type" => "创意类型",
                    "ad_sub_type" => "创意子类型",
                    "app_key" => "应用",
                    "block_id" => "广告位",
                    "platform" => "平台"
                ],
                "default_dim_value" => ["date" => []],
                "dims" => [],
            ],
        ];
        $conf['kpi'] = $this->getChartKpis();
        $conf['dim']['dims'] = HouseAdStat_Service_ReportBaseModel::getDims();
        $conf['dim']['relations'] = HouseAdStat_Service_ReportBaseModel::getRelations();
        return json_encode($conf);
    }

//   获取指标
    private function getChartKpis() {
        $defaultConf = [
            "data" => [
                "view" => 0,
                "click" => 0,
                "close" => 0,
                "skips" => 0,
                "amount" => 0,
                "reward" => 0,
                "resume" => 0,
                "request" => 0,
                "request_ok" => 0,
                "download" => 0,
                "download_ok" => 0,
                "redirect_browser" => 0,
                "redirect_internal_browser" => 0,
                "redirect_shop" => 0,
                "redirect_internal_shop" => 0,
                "download_app" => 0,
                "download_app_ok" => 0,
                "install_app" => 0,
                "install_app_ok" => 0,
            ],
            "stat" => [
                "avg_price" => 0,
                "ecpm" => 0,
                "click_rate" => 0,
                "skips_time" => 0,
            ],
        ];
        $accountId = $this->userInfo['uid'];
        $kpiconf = HouseAdStat_Service_ReportKpiConfModel::getByID($accountId);
        if (empty($kpiconf['kpis'])) {
            $kpi = 'view';
        } else {
            $kpi = $kpiconf['kpis'];
        }
        $kpis = explode('|', $kpi);
        foreach ($defaultConf as $key => $val) {
            foreach ($val as $skey => $sval) {
                if (in_array($skey, $kpis)) {
                    $defaultConf[$key][$skey] = 1;
                }
            }
        }
        return $defaultConf;


    }


//    获取维度
    private function getChartDims() {
//        获取用户权限
        $accountId = $this->userInfo['uid'];
//        $paramAccount = is_array($accountId) ? array('account_id' => array('in', $accountId)) : array('account_id' => $accountId);
        $paramAccount = [];
        $OriginalityIds = Dedelivery_Service_OriginalityRelationModel::getOriginalityIdOfAccount($paramAccount);
        $param = array('id' => array('in', $OriginalityIds));
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
        $accountId = $this->userInfo['uid'];
        $param = is_array($accountId) ? array('account_id' => array('in', $accountId)) : array('account_id' => $accountId);
        $param = [];
        $OriginalityIds = Dedelivery_Service_OriginalityRelationModel::getOriginalityIdOfAccount($param);
//        级联关系:账号==》投放单元==》广告==》创意
        $accountRelation = Dedelivery_Service_OriginalityRelationModel::getRelationOfAccount($param);
//        创意类型==》应用
        $OrigAppkeyRelation = Advertiser_Service_OriginalityRelationPositionModel::getRelationOfAppkey(array('originality_conf_id' => array('in', $OriginalityIds)));
        return array_merge($accountRelation, $OrigAppkeyRelation);
    }

}
