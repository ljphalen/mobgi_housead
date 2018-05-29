<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 *
 * Enter description here ...
 * @author rock.luo
 *
 */
class StatController extends Advertiser_BaseController {

    public $actions = array(
        'editpasswd' => '/Advertiser/User/edit',
        'logout' => '/Advertiser/Login/logout',
        'default' => '/Advertiser/Index/default',
        'getdesc' => '/Advertiser/Index/getdesc',
        'search' => '/Advertiser/Index/search',
        'passwdUrl' => '/Advertiser/User/passwd',
    );
//    public $actions = array(
//        'listUrl' => '/Admin/Stat/pv',
//        'monkeytime' => '/Admin/Stat/monkeytime',
//    );

    const kpiConf = [
        "ad_id" => ["name" => "广告"],
        "unit_id" => ["name" => "投放单元"],
        "originality_type" => ["name" => "创意类型", 'alias' => 'originality_type_name'],
        "account_id" => ["name" => "账号"],
        "originality_id" => ["name" => "创意"],
        "block_id" => ["name" => "广告位"],
        "app_key" => ["name" => "应用", 'alias' => 'app_name'],
        "ad_type" => ["name" => "广告类型"],
        "ad_sub_type" => ["name" => "广告子类型"],
        "platform" => ["name" => "平台"],
        "date" => ["name" => "日期"],
        "hour" => ["name" => "小时"],
        "request" => ["name" => "请求配置"],
        "request_ok" => ["name" => "请求配置成功"],
        "download" => ["name" => "下载资源次数"],
        "download_ok" => ["name" => "下载资源成功"],
        "view" => ["name" => "展示量"],
        "click" => ["name" => "点击量"],
        "skips" => ["name" => "跳过"],
        "close" => ["name" => "关闭"],
        "amount" => ["name" => "消费金额"],
        "avg_price" => ["name" => "点击均价"],
        "ecpm" => ["name" => "ECPM"],
        "click_rate" => ["name" => "点击率(%)"],
        "skips_time" => ["name" => "跳过时间均值"],
    ];
    const filterFields = [
        'date',
        'hour',
        'view',
        'click',
        'ad_id',
        'ad_type',
        'platform',
        'originality_id',
        'originality_type',
        'account_id',
        'unit_id',
        'app_key',
        'block_id',
        'amount',
        'request',
        'request_ok',
        'download',
        'download_ok',
        'sdate',
        'edate',
        'dims',
        'kpi',
        'theader'
    ];

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
        $accountId = $this->userInfo['advertiser_uid'];
        $info = Report_Service_MobgiModel::getByID($accountId);
        $data['kpis'] = $kpis;
        if (empty($info)) {
            $data['user_id'] = $accountId;
            $data['created_at'] = time();
            $re = Report_Service_MobgiModel::save($data);
        } else {
            $data['updated_at'] = time();
            $re = Report_Service_MobgiModel::updateByID($data, $accountId);
        }
        $this->output(1, 'OK');

    }

    public function exportDataAction() {
        $data = [
            'table' => [],
            'total' => []
        ];
        $filterFields = self::filterFields;
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);
        $data = HouseAdStat_Service_ReportBaseModel::getData($params);

        if (isset($params['theader'])) {
            $header = $params['theader'];
            $result = [];
            $kpiConf = self::kpiConf;
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
        $filterFields = self::filterFields;
        $params = $this->getInput($filterFields);
        $params = $this->exchangeParamsArray($params);
        $data['table'] = HouseAdStat_Service_ReportBaseModel::getData($params, $this->getReportAccountId());
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
                "data" => '/Advertiser/Stat/getData',
                "conf" => '/Advertiser/Stat/updateKpi',
            ],
            'kpiConf' => self::kpiConf,
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
                    "ad_type" => "广告类型",
                    "ad_sub_type" => "广告子类型",
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

