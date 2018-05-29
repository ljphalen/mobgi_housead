<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/21
 * Time: 16:48
 */
class Spm_IndexController extends Admin_BaseController {

    public function init() {
        parent::init();
        $module = $this->getTopModule();
        $this->assign('module', $module);
    }

    public $actions = [
        'indexUrl' => '/Admin/Spm_Index/index',
    ];

    /**
     * filter params
     * @param $params
     * @return array
     * @throws Exception
     */
    protected function filterParams($params) {
        foreach ($params as $field => $val) {
            if (is_array($val)) {
                list($op, $value) = $val;
                if (is_null($value) || $value === '') {
                    unset($params[$field]);
                }
            } else {
                if (is_null($val) || $val === '') {
                    unset($params[$field]);
                }
            }
        }
        return $params;
    }

    public function getTop5Data($sdate, $edate, $kpi, $apps, $channels, $type = 0) {
        $appWhere['dims'] = ['channel_gid', 'is_natural'];
        $appWhere['kpis'] = [$kpi];
        $appWhere['sdate'] = $sdate;
        $appWhere['edate'] = $edate;
        $appWhere['is_natural'] = 0;
        if ($type) {
            $appWhere['type'] = 'channel';
            $appWhere['dims'] = ['channel_gid'];
        }

        $appWhere['permit']['app_id'] = $apps;
        $result = MobgiSpm_Service_ReportModel::getCommonData($appWhere);
        $keyMap = MobgiSpm_Service_ReportModel::$expandFields;
        $reg = 0;
        if ($keyMap[$kpi]) {
            $regs = [];
            foreach ($result as $key => $val) {
                $regs[$key] = $val[$keyMap[$kpi][1]];
            }
            if (count($regs)) {
                $reg = intval(array_sum($regs) / count($regs) * 0.05);
            }
            foreach ($result as $key => $val) {
                if ($keyMap[$kpi]) {
                    if ($reg > 0) {
                        if ($val[$keyMap[$kpi][1]] > $reg) {
                            if (empty($val['channel_gid'])) {
                                unset($result[$key]);
                            }
                        } else {
                            unset($result[$key]);
                        }
                    }
                }
            }
        }

        $res = [];
        $sortKey = [];
        foreach ($result as $key => $val) {
            if (empty($val['channel_gid'])) {
                continue;
            }
            $item = [
                'channel_gid' => $val['channel_gid'],
            ];
            if ($keyMap[$kpi]) {
                $item['val1'] = $val[$keyMap[$kpi][0]];
                $item['val2'] = $val[$keyMap[$kpi][1]];
            }
            $item['val'] = $val[$kpi];
            if (isset($item['val']) and $item['val'] > 0) {
                $res[] = $item;
                $sortKey[$key] = $item['val'];
            }
        }
        array_multisort($sortKey, SORT_DESC, SORT_NUMERIC, $res);
        return $res;


        array_multisort($sortkey, SORT_DESC, SORT_NUMERIC, $result);
        $data = [];
        foreach ($result as $key => $val) {
            $channelName = isset($channels[$val['channel_gid']]) ? $channels[$val['channel_gid']] : $val['channel_gid'];
            if (in_array($kpi, ['registers', 'ltv1', 'ltv7', 'ltv14', 'ltv30'])) {
                $data[] = [
                    'id' => $key + 1,
                    'name' => $channelName,
                    'count' => $val[$kpi],
                ];
            } else {
                $data[] = [
                    'id' => $key + 1,
                    'name' => $channelName,
                    'rate' => $val[$kpi],
                ];
            }
            if ($key >= 4) break;
        }
        return $data;
    }


    public function getAppMapAction() {
        $apps = MobgiSpm_Service_ReportModel::getAppMap();
        $apps[0] = '其他';
        $this->output(0, '', $apps);
    }

    public function getChannelMapAction() {
        $channels = MobgiSpm_Service_ReportModel::getChannelGroupMap();
        $channels[0] = '其他';
        $this->output(0, '', $channels);
    }

    public function mergeTop($data, $rate = 100) {
        $res = [];
        $val = [];
        $val1 = [];
        $val2 = [];
        foreach ($data as $items) {
            foreach ($items as $item) {
                if (isset($item['val1'])) {
                    $val1[$item['channel_gid']] += $item['val1'];
                    $val2[$item['channel_gid']] += $item['val2'];
                } else {
                    $val[$item['channel_gid']] += $item['val'];
                }
            }
        }
        foreach ($val1 as $gid => $sum) {
            if ($val2[$gid]) {
                $val[$gid] = round($sum / $val2[$gid] * $rate, 2);
            }
        }

        foreach ($val as $gid => $sum) {
            $res[] = [
                'channel_gid' => $gid,
                'val' => $sum
            ];
        }
        array_multisort($val, SORT_DESC, SORT_NUMERIC, $res);
        return $res;


    }

    public function getTop5Action() {
        $params = $this->getInput(array('sdate', 'edate', 'app_id'));
        if (!empty($params['sdate']) && !empty($params['edate'])) {
            $sdate = $params['sdate'];
            $edate = $params['edate'];
        } else {
            $sdate = date('Y-m-01');
            $edate = date("Y-m-d");
        }

        $appIds = isset($params['app_id']) ? [$params['app_id']] : [];
        $mykey = 'spm_top5_' . $sdate . $edate . '_' . $params['app_id'];
        $redis = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS);
        $top5 = $redis->get($mykey);
        if ($top5 == false) {
            $channels = MobgiSpm_Service_ReportModel::getChannelGroupMap();
            if (count($appIds)) {
                $platform = MobgiSpm_Service_ReportModel::getAppOs($params['app_id']);
                $os[$platform] = $appIds;
            } else {
                $os = MobgiSpm_Service_ReportModel::getOsMap();
            }

            if ($os['ios']) {
                $registers['ios'] = $this->getTop5Data($sdate, $edate, 'registers', $os['ios'], $channels);
                $roi1['ios'] = $this->getTop5Data($sdate, $edate, 'roi', $os['ios'], $channels);
                $roi2['ios'] = $this->getTop5Data($sdate, $edate, 'roi7', $os['ios'], $channels);
                $roi3['ios'] = $this->getTop5Data($sdate, $edate, 'roi14', $os['ios'], $channels);
                $roi4['ios'] = $this->getTop5Data($sdate, $edate, 'roi30', $os['ios'], $channels);
                $retention1['ios'] = $this->getTop5Data($sdate, $edate, 'retention1', $os['ios'], $channels);
                $retention2['ios'] = $this->getTop5Data($sdate, $edate, 'retention7', $os['ios'], $channels);
                $retention3['ios'] = $this->getTop5Data($sdate, $edate, 'retention14', $os['ios'], $channels);
                $retention4['ios'] = $this->getTop5Data($sdate, $edate, 'retention30', $os['ios'], $channels);
                $ltv1['ios'] = $this->getTop5Data($sdate, $edate, 'ltv1', $os['ios'], $channels);
                $ltv2['ios'] = $this->getTop5Data($sdate, $edate, 'ltv7', $os['ios'], $channels);
                $ltv3['ios'] = $this->getTop5Data($sdate, $edate, 'ltv14', $os['ios'], $channels);
                $ltv4['ios'] = $this->getTop5Data($sdate, $edate, 'ltv30', $os['ios'], $channels);
            }
            if ($os['android']) {
                $registers['android'] = $this->getTop5Data($sdate, $edate, 'registers', $os['android'], $channels, 1);
                $roi1['android'] = $this->getTop5Data($sdate, $edate, 'roi', $os['android'], $channels, 1);
                $roi2['android'] = $this->getTop5Data($sdate, $edate, 'roi7', $os['android'], $channels, 1);
                $roi3['android'] = $this->getTop5Data($sdate, $edate, 'roi14', $os['android'], $channels, 1);
                $roi4['android'] = $this->getTop5Data($sdate, $edate, 'roi30', $os['android'], $channels, 1);
                $retention1['android'] = $this->getTop5Data($sdate, $edate, 'retention1', $os['android'], $channels, 1);
                $retention2['android'] = $this->getTop5Data($sdate, $edate, 'retention7', $os['android'], $channels, 1);
                $retention3['android'] = $this->getTop5Data($sdate, $edate, 'retention14', $os['android'], $channels, 1);
                $retention4['android'] = $this->getTop5Data($sdate, $edate, 'retention30', $os['android'], $channels, 1);
                $ltv1['android'] = $this->getTop5Data($sdate, $edate, 'ltv1', $os['android'], $channels, 1);
                $ltv2['android'] = $this->getTop5Data($sdate, $edate, 'ltv7', $os['android'], $channels, 1);
                $ltv3['android'] = $this->getTop5Data($sdate, $edate, 'ltv14', $os['android'], $channels, 1);
                $ltv4['android'] = $this->getTop5Data($sdate, $edate, 'ltv30', $os['android'], $channels, 1);
            }
            $registers['all'] = self::mergeTop($registers);
            $roi1['all'] = self::mergeTop($roi1);
            $roi2['all'] = self::mergeTop($roi2);
            $roi3['all'] = self::mergeTop($roi3);
            $roi4['all'] = self::mergeTop($roi4);
            $retention1['all'] = self::mergeTop($retention1);
            $retention2['all'] = self::mergeTop($retention2);
            $retention3['all'] = self::mergeTop($retention3);
            $retention4['all'] = self::mergeTop($retention4);
            $ltv1['all'] = self::mergeTop($ltv1, 1);
            $ltv2['all'] = self::mergeTop($ltv2, 1);
            $ltv3['all'] = self::mergeTop($ltv3, 1);
            $ltv4['all'] = self::mergeTop($ltv4, 1);

            $all = [
                'register' => [$registers['all']],
                'retention' => [$retention1['all'], $retention2['all'], $retention3['all'], $retention4['all']],
                'roi' => [$roi1['all'], $roi2['all'], $roi3['all'], $roi4['all']],
                'ltv' => [$ltv1['all'], $ltv2['all'], $ltv3['all'], $ltv4['all']],
            ];

            if (count($appIds)) {
                $top5 = [$all];
            } else {
                $ios = [
                    'register' => [$registers['ios']],
                    'retention' => [$retention1['ios'], $retention2['ios'], $retention3['ios'], $retention4['ios']],
                    'roi' => [$roi1['ios'], $roi2['ios'], $roi3['ios'], $roi4['ios']],
                    'ltv' => [$ltv1['ios'], $ltv2['ios'], $ltv3['ios'], $ltv4['ios']],
                ];
                $android = [
                    'register' => [$registers['android']],
                    'retention' => [$retention1['android'], $retention2['android'], $retention3['android'], $retention4['android']],
                    'roi' => [$roi1['android'], $roi2['android'], $roi3['android'], $roi4['android']],
                    'ltv' => [$ltv1['android'], $ltv2['android'], $ltv3['android'], $ltv4['android']],
                ];
                $res = [$all, $ios, $android];
            }
            $top5 = [];
            foreach ($res as $os => $list) {
                foreach ($list as $k => $kpis) {
                    foreach ($kpis as $i => $items) {
                        foreach ($items as $key => $item) {
                            if ($key > 4) break;
                            $top5[$os][$k][$i][$key]['id'] = $key + 1;
                            $top5[$os][$k][$i][$key]['name'] = $channels[$item['channel_gid']] ?: $item['channel_gid'];
                            if (in_array($k, ['register', 'ltv'])) {
                                $top5[$os][$k][$i][$key]['count'] = round($item['val'], 2);
                            } else {
                                $top5[$os][$k][$i][$key]['rate'] = round($item['val'], 1);
                            }

                        }

                    }
                }
            }
            $redis->set($mykey, $top5, 300);
        }
        $this->output(0, '', $top5);
    }


    public function getStaffKpiAction() {
        $params = $this->getInput(array('sdate', 'edate', 'app_id'));
        if (!empty($params['sdate']) && !empty($params['edate'])) {
            $sdate = $params['sdate'];
            $edate = $params['edate'];
        } else {
            $sdate = date('Y-m-01');
            $edate = date("Y-m-d");
        }
        $where['sdate'] = $sdate;
        $where['edate'] = $edate;

        if (isset($params['app_id'])) {
            $where['app_id'] = [$params['app_id']];
        }

        $result = MobgiSpm_Service_ReportModel::getStaffKpi($where);

        $this->output(0, '', array_values($result));

    }

    public function getChannelKpiAction() {
        $params = $this->getInput(array('sdate', 'edate', 'app_id'));
        if (!empty($params['sdate']) && !empty($params['edate'])) {
            $sdate = $params['sdate'];
            $edate = $params['edate'];
        } else {
            $sdate = date('Y-m-01');
            $edate = date("Y-m-d");
        }
        $where['sdate'] = $sdate;
        $where['edate'] = $edate;

        if (isset($params['app_id'])) {
            $where['app_id'] = [$params['app_id']];
        }
//        $plan = MobgiSpm_Service_ReportModel::getPlan($where);
        $result = MobgiSpm_Service_ReportModel::getChannelKpi($where);
        $this->output(0, '', array_values($result));
    }


    public function getAdminUserAction() {
        $data = $this->userInfo;
        $userData = array(
            'user_id' => $data['user_id'],
            'user_name' => $data['user_name'],
            'email' => $data['email'],
        );
        $this->output(0, '', $userData);
    }

    public function getAppListAction() {
        $params = $this->getPost(array('page', 'limit', 'app_name', 'platform', 'sdate', 'edate', 'field', 'order'));
        $where = array(
            'app_name' => array('like', $params['app_name']),
        );
        if (!empty($params['sdate']) && !empty($params['edate'])) {
            $sdate = $params['sdate'];
            $edate = $params['edate'];
        } else {
            $sdate = date('Y-m-d', strtotime('-14 days'));
            $edate = date("Y-m-d");
        }
        $orderBy = array('app_id' => 'DESC');
        list($total, $appList) = MobgiSpm_Service_DeliveryModel::getAppList($params['page'], $params['limit'], $where, $orderBy);
        $appIds = [];
        foreach ($appList as $app) {
            $appIds[$app['app_id']] = $app['app_id'];
        }

        $appWhere['app_id'] = $appIds;
        $appWhere['dims'] = ['app_id'];
        $appWhere['kpis'] = ['clicks', 'effect_clicks', 'actives', 'active_rate', 'registers'];
        $appWhere['sdate'] = $sdate;
        $appWhere['edate'] = $edate;
        $reportList = MobgiSpm_Service_ReportModel::getData($appWhere);


        $channelWhere['app_id'] = $appIds;
        $channelWhere['dims'] = ['app_id'];
        $channelWhere['kpis'] = ['registers'];
        $channelWhere['sdate'] = $sdate;
        $channelWhere['edate'] = $edate;
        $channelWhere['type'] = 'channel';
        $reportChannelList = MobgiSpm_Service_ReportModel::getData($channelWhere);


        $natureWhere['app_id'] = $appIds;
        $natureWhere['activity_id'] = 0;
        $natureWhere['dims'] = ['app_id', 'activity_id'];
        $natureWhere['kpis'] = ['actives', 'registers'];
        $natureWhere['sdate'] = $sdate;
        $natureWhere['edate'] = $edate;
        $natureReportList = MobgiSpm_Service_ReportModel::getData($natureWhere);

        $appList = MobgiSpm_Service_DeliveryModel::formatAppList($appList, $reportList, $reportChannelList, $natureReportList, $appWhere['kpis']);

        $params['field'] = $params['field'] ?: 'actives';
        $params['order'] = $params['order'] ?: 'desc';
        $appList = MobgiSpm_Service_DeliveryModel::pageList($appList, $params['page'], $params['limit'], $params['field'], $params['order']);

        $result = array(
            'success' => 0,
            'msg' => '',
            'count' => $total,
            'data' => $appList,
        );
        exit(json_encode($result));
    }

    public function getAppAction() {
        $app = MobgiSpm_Service_DeliveryModel::getDeliveryApp();
        $result = array(
            'success' => 0,
            'msg' => '',
            'data' => $app,
        );
        exit(json_encode($result));
    }

    public function getMenuListAction() {
        $params = $this->getPost(array('app_id', 'menu_type'));
        if (empty($params['menu_type'])) {
            $params['menu_type'] = 'Spm_Monitor';
        }
        $menuType = '_' . $params['menu_type'] . '_Module';
        list($usermenu, $mainview, $usersite, $userlevels) = $this->getUserMenu();
        if (!isset($usermenu[$menuType])) {
            $this->output(-1, '找不到对应菜单', '');
        }
        $spmMenu = $usermenu['_Spm_Monitor_Module']['items'];
        # 过滤空菜单
        foreach ($spmMenu as $key => $value) {
            if (empty($value['items'])) {
                unset($spmMenu[$key]);
            }
        }
        $this->output(0, '', $spmMenu);
    }

    public function indexAction() {
        $params = $this->getPost(array('sdate', 'edate'));
        if (!empty($params['sdate']) && !empty($params['edate'])) {
            $sdate = $params['sdate'];
            $edate = $params['edate'];
        } else {
            $sdate = date('Y-m-01');
            $edate = date("Y-m-d");
        }
        $this->assign('sdate', $sdate);
        $this->assign('edate', $edate);
    }

//获取月汇总数据
    public function getMonthGeneralAction() {
        $dim = $this->getInput('dim');
        $mydim = ['months'];
        if ($dim) {
            $mydim[] = $dim;
        }
        $myKey = 'spm_month_' . date("Y_m");
        $redis = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS);
        $res = $redis->get($myKey);
//        $res = false;
        if ($res == false) {
            $dims = ['app_id', 'channel_gid', 'months'];
            $where['sdate'] = date('Y-01-01');
            $where['edate'] = date("Y-m-d");
            $where['dims'] = $dims;
            $plan = MobgiSpm_Service_ReportModel::getMonthPlan($where);//广告计划
            $planMap = [];
            $planDim = ['app_id', 'months'];
            foreach ($plan as $item) {
                $mapkey = '';
                foreach ($planDim as $dim) {
                    $mapkey .= '_' . $item[$dim];
                }
                $planMap[$mapkey] = $item['daily_amount'];//计划新增
            }
            $app = MobgiSpm_Service_ReportModel::getOsMap();

            $where['kpis'] = ['registers', 'total_amount', 'real_consumption'];
            $where['app_id'] = $app['android'];
            $where['type'] = 'channel';
            $androidResult = MobgiSpm_Service_ReportModel::getCommonData($where);//安卓渠道
            unset($where['type']);

            $where['kpis'] = ['registers', 'total_amount', 'real_consumption'];
            $where['dims'] = ['months', 'app_id', 'is_natural', 'channel_gid'];
            $where['app_id'] = $app['ios'];
            $where['is_natural'] = 0;
            $iosResult = MobgiSpm_Service_ReportModel::getCommonData($where);//活动
            unset($where['is_natural']);

            $where['kpis'] = ['registers'];
            $where['dims'] = ['months', 'app_id', 'channel_gid'];
            $allResult = MobgiSpm_Service_ReportModel::getCommonData($where);//活动全部新增


            // 合并数据
            $result = [];
            foreach ($androidResult as $key => $item) {
                $mapkey = '';
                foreach ($planDim as $dim) {
                    $mapkey .= '_' . $item[$dim];
                }
                if ($planMap[$mapkey]) {
                    $item['plan_registers'] = $item['registers'];
                }
                $result['and_' . $key] = $item;
            }
            foreach ($iosResult as $key => $item) {
                $mapkey = '';
                foreach ($planDim as $dim) {
                    $mapkey .= '_' . $item[$dim];
                }
                if ($planMap[$mapkey]) {
                    $item['plan_registers'] = $item['registers'];
                }
                $item['ios_registers'] = $item['registers'];
                $result['ios_' . $key] = $item;
            }

            foreach ($allResult as $key => $item) {
                $item['total_registers'] = $item['registers'];
                unset($item['registers']);
                $result['all_' . $key] = $item;
            }

            $kpis = ['daily_consumption', 'daily_amount', 'registers', 'real_consumption', 'plan_registers', 'total_amount', 'ios_registers', 'total_registers'];
            $res = MobgiSpm_Service_ReportModel::mergeDate(array_merge_recursive($plan, $result), $dims, $kpis);
            $redis->set($myKey, $res, 3600);
        }
        $result = [];

        $kpis = ['daily_consumption', 'daily_amount', 'registers', 'real_consumption', 'plan_registers', 'total_amount', 'ios_registers', 'total_registers'];
        foreach ($res as $key => $item) {
            $mapkey = '';
            foreach ($mydim as $dim) {
                $mapkey .= '_' . $item[$dim];
            }

            if (!$result[$mapkey]) {
                foreach ($mydim as $dim) {
                    $result[$mapkey][$dim] = $item[$dim];
                }
                foreach ($kpis as $kpi) {
                    $result[$mapkey][$kpi] = 0;
                }
            }
            foreach ($kpis as $kpi) {
                $result[$mapkey][$kpi] += $item[$kpi];
            }
        }


        foreach ($result as $key => $item) {
            $sum = 0;
            foreach ($kpis as $kpi) {
                $result[$key][$kpi] = intval($item[$kpi]);
                $sum += $result[$key][$kpi];
            }
            if (empty($sum)) {
                unset($result[$key]);

            }
        }

        foreach ($result as $key => $item) {
            $item['daily_cost'] = $item['daily_amount'] ? round($item['daily_consumption'] / $item['daily_amount'], 2) : 0;
            $item['registers_rate'] = $item['total_registers'] ? round($item['ios_registers'] / $item['total_registers'] * 100, 2) : 0;//整体新增占比
            $item['kpi_rate'] = $item['daily_amount'] ? round($item['plan_registers'] / $item['daily_amount'] * 100, 2) : 0;//新增计划完成率
            $item['cost'] = $item['registers'] ? round($item['real_consumption'] / $item['registers'], 2) : 0;//实际新增成本
            $item['cost_rate'] = $item['daily_cost'] ? round($item['cost'] / $item['daily_cost'] * 100, 2) : 0;//整体成本KPI
            $item['roi'] = $item['real_consumption'] ? round($item['total_amount'] / $item['real_consumption'] * 100, 2) : 0;
            $result[$key] = $item;
        }

        $this->output(0, '', array_values($result));
    }


    public function getAdPlanAction() {
        $params = $this->getInput(array('sdate', 'edate', 'app_id'));
        if (!empty($params['sdate']) && !empty($params['edate'])) {
            $sdate = $params['sdate'];
            $edate = $params['edate'];
        } else {
            $sdate = date('Y-m-01');
            $edate = date("Y-m-d");
        }

        $where['sdate'] = $sdate;
        $where['edate'] = $edate;

        if (isset($params['app_id'])) {
            $where['app_id'] = [$params['app_id']];
            $myKey = 'spm_plan_' . $sdate . '_' . $edate . '_' . $params['app_id'];
        } else {
            $myKey = 'spm_plan_' . $sdate . '_' . $edate;
        }

        $redis = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS);
        $res = $redis->get($myKey);
//        $res = false;
        if ($res == false) {
            $res = [];
            $planApp = [];
            //广告计划
            $result1 = MobgiSpm_Service_ReportModel::getPlan($where);
            foreach ($result1 as $item) {
                $appId = $item['app_id'];
                $planApp[$appId] = $item['app_id'];
                $res[$appId]['daily_consumption'] = $item['daily_consumption'];//日均消耗金额
                $res[$appId]['daily_amount'] = $item['daily_amount'];//日均量
                $res[$appId]['registers'] = 0;
                $res[$appId]['plan_registers'] = 0;
                $res[$appId]['real_consumption'] = 0;
            }
            $app = MobgiSpm_Service_ReportModel::getOsMap();

            $result = [];
            $iosResult = [];
            $androidResult = [];
            if (isset($params['app_id'])) {
                if (in_array($params['app_id'], $app['android'])) {
                    $where['kpis'] = ['registers', 'total_amount', 'real_consumption'];
                    $where['dims'] = ['app_id'];
                    $where['app_id'] = [$params['app_id']];
                    $where['type'] = 'channel';
                    $androidResult = MobgiSpm_Service_ReportModel::getCommonData($where);//安卓渠道
                } elseif (in_array($params['app_id'], $app['ios'])) {
                    $where['kpis'] = ['registers', 'total_amount', 'real_consumption'];
                    $where['dims'] = ['app_id', 'is_natural'];
                    $where['app_id'] = [$params['app_id']];
                    $where['is_natural'] = 0;
                    $iosResult = MobgiSpm_Service_ReportModel::getCommonData($where);//活动
                    $where['kpis'] = ['registers'];
                    $where['app_id'] = [$params['app_id']];
                    $result = MobgiSpm_Service_ReportModel::getCommonData($where);//统计全部新增
                }

            } else {
                $where['kpis'] = ['registers', 'total_amount', 'real_consumption'];
                $where['dims'] = ['app_id'];
                $where['app_id'] = $app['android'];
                $where['type'] = 'channel';
                $androidResult = MobgiSpm_Service_ReportModel::getCommonData($where);//安卓渠道
                unset($where['type']);
                $where['kpis'] = ['registers', 'total_amount', 'real_consumption'];
                $where['dims'] = ['app_id', 'is_natural'];
                $where['app_id'] = $app['ios'];
                $where['is_natural'] = 0;
                $iosResult = MobgiSpm_Service_ReportModel::getCommonData($where);//活动
                $where['kpis'] = ['registers'];
                $where['dims'] = ['app_id'];
                $where['app_id'] = $app['ios'];
                $result = MobgiSpm_Service_ReportModel::getCommonData($where);//统计全部新增
            }

            foreach ($result as $key => $item) {
                $appId = $item['app_id'];
                $res[$appId]['total_registers'] += $item['registers'];
            }
            foreach ($iosResult as $key => $item) {
                $appId = $item['app_id'];
                $res[$appId]['iso_registers'] += $item['registers'];
            }

            // 合并数据
            foreach (array_merge_recursive($androidResult, $iosResult) as $key => $item) {
                $appId = $item['app_id'];
                $res[$appId]['real_consumption'] += $item['real_consumption'];
                $res[$appId]['registers'] += $item['registers'];
                $res[$appId]['total_amount'] += $item['total_amount'];
                //统计有计划新增
                if (in_array($appId, $planApp)) {
                    $res[$appId]['plan_registers'] += $item['registers'];
                }
            }

            $appName = MobgiSpm_Service_ReportModel::getAppMap();
            $total = [];
            $plan = [];
            foreach ($res as $appId => $val) {

                $res[$appId]['app_name'] = $appName[$appId] ?: '';
                $res[$appId]['daily_consumption'] = $val['daily_consumption'] ?: 0;
                $res[$appId]['daily_amount'] = $val['daily_amount'] ?: 0;
                $res[$appId]['daily_cost'] = $val['daily_amount'] > 0 ? round($val['daily_consumption'] / $val['daily_amount'], 2) : 0;//计划成本
                $res[$appId]['cost'] = $val['registers'] > 0 ? round($val['real_consumption'] / $val['registers'], 2) : 0;//计划成本

                $res[$appId]['kpi_rate'] = $val['daily_amount'] > 0 ? round($val['registers'] / $val['daily_amount'] * 100, 2) : 0;//计划新增完成率
                $res[$appId]['consumption_rate'] = $val['daily_consumption'] > 0 ? round($val['real_consumption'] / $val['daily_consumption'] * 100, 2) : 0;//计划预算使用率
                $res[$appId]['cost_rate'] = $res[$appId]['daily_cost'] > 0 ? round($res[$appId]['cost'] / $res[$appId]['daily_cost'] * 100, 2) : 0;//成本变动

                $total['real_consumption'] += $val['real_consumption'];
                $total['registers'] += $val['registers'];//新增人数
                $total['iso_registers'] += $val['iso_registers'];//IOS新增人数
                $total['total_registers'] += $val['total_registers'];//IOS总新增人数
                $total['daily_consumption'] += $val['daily_consumption'];//预算
                $total['daily_amount'] += $val['daily_amount'];//计划新增人数
                $total['total_amount'] += $val['total_amount'];//累计收入
                if (in_array($appId, $planApp)) {
                    $total['plan_real_consumption'] += $val['real_consumption'];//计划应用实际消耗
                    $total['plan_registers'] += $val['registers'];//计划应用实际新增
                    $plan[] = $res[$appId];
                } else {
                    unset($res[$appId]);
                }
            }


            $total['cost'] = $total['registers'] > 0 ? round($total['real_consumption'] / $total['registers'], 2) : 0;
            $total['daily_cost'] = $total['daily_amount'] > 0 ? round($total['daily_consumption'] / $total['daily_amount'], 2) : 0;

            $total['registers_rate'] = $total['total_registers'] > 0 ? round($total['iso_registers'] / $total['total_registers'] * 100, 2) : 0;//整体新增占比
            $total['kpi_rate'] = $total['daily_amount'] > 0 ? round($total['plan_registers'] / $total['daily_amount'] * 100, 2) : 0;
            $total['cost_rate'] = $total['daily_cost'] > 0 ? round($total['cost'] / $total['daily_cost'] * 100, 2) : 0;
            $total['roi'] = $total['real_consumption'] ? round($total['total_amount'] / $total['real_consumption'] * 100, 2) : 0;
            $result = $res;
            $res = [
                'data' => array_values($result),
                'total' => $total
            ];

            $redis->set($myKey, $res, 900);
        }
        $this->output(0, '', $res);

    }
}