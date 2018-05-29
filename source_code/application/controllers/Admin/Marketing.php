<?php
if (!defined('BASE_PATH')) exit('Access Denied!');


class Admin_MarketingController extends Admin_BaseController {
    public $marketingConfig = '';
    public $resourceName = '';
    public $resourceAction = '';

    public function init() {
        parent::init();
        $marketingConfig = Common::getConfig('marketingConfig');
        $this->marketingConfig = $marketingConfig;
    }

    /**
     * 获取MarketingApi访问token
     * @param $accountId
     * @return mixed
     */
    protected function getGdtAccesstoken($accountId) {

        $accountData = MobgiMarket_Service_SettingModel::getUserAuthByParams([
            'user_id' => $this->userInfo['user_id'],
            'account_id' => $accountId]
        );
        # 校验对传入账户是否拥有操作权限，并获取access_token
        if (empty($accountData)) {
            $this->output(-1, '账号不存在');
        }
        $effectTime = $accountData['token_time'] + $accountData['access_token_expires_in'];
        if ($effectTime < time()) {
            $this->output(-1, '授权过期，请重新授权账户');
        }
        $accessToken = $accountData['access_token'];
//        $accessToken = '56dcc606f45fb0682c57c4099a1b2314'; //沙箱环境使用固定的access_token
        return $accessToken;
    }

    /**
     * 获取当前的Marketing帐号id
     * @return string
     */
    protected function getGdtAccountId() {
        $accountId = intval($this->getInput('account_id'));
//        $accountId = '1068';//1068是代理商帐号，不能用来创建广告
//        $accountId = 100001230;  // 沙河环境账号1
//        $accountId = 100002014;  // 沙河环境账号2
//        $accountId = 580614;  // 正式环境账号1
//        $accountId = 1237127;  // 正式环境账号2
        return $accountId;
    }

    /**
     * 获取当前MarketingApi地址
     * @param $resourceName
     * @param $resourceAction
     * @param $accountId
     * @return string
     */
    protected function getGdtApiUrl($resourceName, $resourceAction, $accountId) {
        $accessToken = $this->getGdtAccesstoken($accountId);
        $nonce = Common::getNonce();
        $marketingConfig = Common::getConfig('marketingConfig');
        $url = $marketingConfig['MARKETING_API_URL'] . $marketingConfig['API_VERSION'] . '/' . $resourceName . '/' . $resourceAction . '?access_token=' . $accessToken . '&timestamp=' . time() . '&nonce=' . $nonce;
        return $url;
    }

    /**
     * 格式化MarketingApi的输出结果：枚举类型转化成中文，时间戳转化成日期格式
     * @param type $data
     * @param array $relArr key为第一个参数$data['list'][0][$key]的值，e:campaign_type ， value为$this->marketingConfig的索引,e:CAMPAIGN_TYPE
     * @return type
     */
    protected function parseGdtList($data, $relArr = array()) {
        if (empty($data['list']) || empty($relArr)) {
            return $data;
        }
        $marketingConfig = Common::getConfig('marketingConfig');
        foreach ($data['list'] as $key => $item) {
            foreach ($relArr as $relKey => $relItem) {
                //site_set是数组格式，需要特殊处理
                if ($relKey == 'site_set') {
                    $siteSetKey = $item[$relKey][0];
                    $siteSetName = $marketingConfig[$relItem][$siteSetKey];
                    $data['list'][$key]['site_set_name'] = $siteSetName;
                } elseif($relKey == 'time_series'){
                    $data['list'][$key][$relKey] = Common::update_time_series_add_zero($data['list'][$key][$relKey]);
                    $hourRange = Common::get_hours_from_series($data['list'][$key][$relKey]);
                    $timeSeries = Common::get_week_time_series($hourRange['start_hour'], $hourRange['end_hour']);
                    $hourRange['time_senior_type'] = ($timeSeries == $data['list'][$key][$relKey]) ? 0 : 1;
                    $data['list'][$key]['time_series_range'] = $hourRange;
                } else {
                    $relKeyConfig = $marketingConfig[$relItem][$data['list'][$key][$relKey]];
                    $data['list'][$key][$relKey . '_name'] = is_array($relKeyConfig) ? $relKeyConfig['name'] : $relKeyConfig;
                }
            }
            $data['list'][$key]['created_time'] = date("Y-m-d H:i:s", $data['list'][$key]['created_time']);
            $data['list'][$key]['last_modified_time'] = date("Y-m-d H:i:s", $data['list'][$key]['last_modified_time']);
            $data['list'][$key]['loading'] = true;
        }
        return $data;
    }

    public function send($params, $resourceAction = '', $resourceName = '', $method = '') {
        $resourceAction = $resourceAction ?  : $this->resourceAction;
        $resourceName = $resourceName ?  : $this->resourceName;
        $method = $method ? : $this->marketingConfig['ACTION_METHOD'][$resourceAction];
        $params['account_id'] = isset($params['account_id']) ? $params['account_id'] :$this->getGdtAccountId();
        # curl log 日志，判断是谁频繁调用
        $log = [
            'user_id' => $this->userInfo['user_id'],
            'account_id' => $params['account_id'],
            'resource_name' => $resourceName,
            'resource_action' => $resourceAction,
            'action_method' => $method
        ];
        MobgiMarket_Service_LogModel::addCurlLog($log);
        $apiUrl = $this->getGdtApiUrl($resourceName, $resourceAction, $params['account_id']);
        $curl = new Util_Http_Curl($apiUrl,10000);
        $curl->setHeader("application/x-www-form-urlencoded", "application/json");
        $curl->setData($params);
        $result = $curl->send($method);
        return $result;
    }

    /**
     * filter params 过滤where条件中的空值/null值
     * @param $params
     * @return array
     * @throws Exception
     */
    protected function filterParams($params) {
        foreach ($params as $field => $val) {
            if(is_array($val)){
                list($op, $value) = $val;
                if(is_null($value) || $value === ''){
                    unset($params[$field]);
                }
            }else{
                if(is_null($val) || $val === ''){
                    unset($params[$field]);
                }
            }
        }
        return $params;
    }

    /**
     * 获取广告主信息
     * @param $accountId
     * @param $accessToken
     * @return mixed|string
     */
    protected function getAdvertiserData($accountId,$accessToken){
        $resourceAction = 'get';
        $resourceName = 'advertiser';
        $method = $this->marketingConfig['ACTION_METHOD'][$resourceAction];
        $nonce = Common::getNonce();
        $marketingConfig = Common::getConfig('marketingConfig');
        $apiUrl = $marketingConfig['MARKETING_API_URL'] . $marketingConfig['API_VERSION'] . '/' . $resourceName . '/' . $resourceAction . '?access_token=' . $accessToken . '&timestamp=' . time() . '&nonce=' . $nonce;

        $param['account_id'] = $accountId;
        $curl = new Util_Http_Curl($apiUrl);
        $curl->setHeader("application/x-www-form-urlencoded", "application/json");
        $curl->setData($param);
        $result = $curl->send($method);
        return $result;
    }

    /**
     * 获取资金账户信息
     * @param $accountId
     * @param $accessToken
     * @return mixed|string
     */
    protected function getAccountFunds($accountId,$accessToken){
        $resourceAction = 'get';
        $resourceName = 'funds';
        $method = $this->marketingConfig['ACTION_METHOD'][$resourceAction];
        $nonce = Common::getNonce();
        $marketingConfig = Common::getConfig('marketingConfig');
        $apiUrl = $marketingConfig['MARKETING_API_URL'] . $marketingConfig['API_VERSION'] . '/' . $resourceName . '/' . $resourceAction . '?access_token=' . $accessToken . '&timestamp=' . time() . '&nonce=' . $nonce;

        $param['account_id'] = $accountId;
        $curl = new Util_Http_Curl($apiUrl);
        $curl->setHeader("application/x-www-form-urlencoded", "application/json");
        $curl->setData($param);
        $result = $curl->send($method);
        return $result;
    }

    /**
     * 获取当前的数据的报表，并组装到result中(推广计划/广告组/创意/广告)
     * @param $result
     * @param $params
     * @return mixed
     */
    protected function getDailyReports($result, $params){
        //获取曝光量，点击量，点击率，点击均价，价格
        $ids = $this->getIds($result['data'], $params['field']);
        if(empty($ids)){
            return $result;
        }
        if(empty($params['start_date']) || empty($params['end_date'])){
            $params['start_date'] = $params['end_date'] = date('Y-m-d');
        }else{
            $params['start_date'] = date('Y-m-d',strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d',strtotime($params['end_date']));
        }
        $data = array(
            'level'=>$params['level'],
            'date_range'=>array('start_date'=>$params['start_date'], 'end_date'=>$params['end_date']),
            'page'=>$params['page'],
            'page_size'=>$params['page_size'],
            'group_by'=>[$params['field']],
            'filtering'=>array(
                array(
                    'field'=>$params['field'],
                    'operator'=>'IN',
                    'values'=>$ids,
                )
            ),
        );
        $dailyReport = $this->send($data, 'get', 'daily_reports');
        $dailyReportArr = json_decode($dailyReport, TRUE);
        if($dailyReportArr['data']['list']){
            $dailyReports = common::resetKey($dailyReportArr['data']['list'], $params['field']);
        }
        foreach($result['data']['list'] as $key=>$item){
            $filedId = $item[$params['field']];
            if($dailyReports[$filedId]){
                $dailyReports[$filedId]['cost'] = $dailyReports[$filedId]['cost'] / 100;
                $result['data']['list'][$key] = array_merge($result['data']['list'][$key], $dailyReports[$filedId]);
                $result['data']['list'][$key]['ctr'] = $result['data']['list'][$key]['cpc']= $result['data']['list'][$key]['cpt'] = '-';

                if($dailyReports[$filedId]['impression'] != 0){
                    $result['data']['list'][$key]['ctr'] = round($dailyReports[$filedId]['click']*1.0/$dailyReports[$filedId]['impression'],3) *100 . '%';
                }
                if($dailyReports[$filedId]['click'] != 0){
                    $result['data']['list'][$key]['cpc'] = round($dailyReports[$filedId]['cost']/$dailyReports[$filedId]['click'], 2);
                    $result['data']['list'][$key]['clicktraderate'] = round($dailyReports[$filedId]['conversion']/$dailyReports[$filedId]['click'], 2);
                }
                if($dailyReports[$filedId]['conversion'] != 0){
                    $result['data']['list'][$key]['cpt'] = round($dailyReports[$filedId]['cost']/$dailyReports[$filedId]['conversion'], 2);
                }
                # 激活均价
                if($dailyReports[$filedId]['activation'] != 0){
                    $result['data']['list'][$key]['activated_price'] = round($dailyReports[$filedId]['cost']/$dailyReports[$filedId]['activation'], 2);
                }
            }else{
                $result['data']['list'][$key] = array_merge($result['data']['list'][$key], [
                    'impression' => '-',
                    'click' => '-',
                    'ctr' => '-',
                    'cpc' => '-',
                    'clicktraderate' => '-',
                    'conversion' => '-',
                    'cpt' => '-',
                    'cost' => '-',
                    'activation' => '-',
                    'activated_price' => '-',
                ]);
            }
        }
        return $result;
    }

    /**
     * 获取当前的数据的小时报表，并组装到result中(推广计划/广告组/创意/广告)
     * @param $result
     * @param $params
     * @return mixed
     */
    protected function getHourlyReports($result, $params){
        //获取曝光量，点击量，点击率，点击均价，价格
        $ids = $this->getIds($result['data'], $params['field']);
        if(empty($ids)){
            return $result;
        }
        if(empty($params['date'])){
            $params['start_date'] = date('Y-m-d');
        }
        $data = array(
            'level'=>$params['level'],
            'date'=>$params['date'],
            'page'=>$params['page'],
            'page_size'=>$params['page_size'],
            'filtering'=>array(
                array(
                    'field'=>$params['field'],
                    'operator'=>'IN',
                    'values'=>$ids,
                )
            ),
        );
        $hourlyReport = $this->send($data, 'get', 'hourly_reports');
        $hourlyReportArr = json_decode($hourlyReport, TRUE);
        $totalArr = [];
        foreach($hourlyReportArr['data']['list'] as $reportVal){
            $filedId = $reportVal[$params['field']];
            $totalArr[$filedId][] = $reportVal;
        }

        foreach($result['data']['list'] as $key=>$item){
            $filedId = $reportVal[$params['field']];
            if(isset($totalArr[$filedId])){
                $result['data']['list'][$key]['hourly_report'] = $totalArr[$filedId];
            }
        }
        return $result;
    }

    /**
     * 汇总报表数据
     * @param $list
     * @return array
     */
    public function sumReportData($list){
        $sumKey = ['impression','click','conversion','activation','cost'];
        $sumData = [
            'impression' => 0,
            'click' => 0,
            'conversion' => 0,
            'cost' => 0,
            'activation' => 0,
        ];
        foreach($list as $key => $value){
            foreach($sumKey as $field){
                if($value[$field] != '-'){
                    $sumData[$field] += $value[$field];
                }
            }
        }
        if($sumData['impression'] != 0){
            $sumData['ctr'] = round($sumData['click']*1.0/$sumData['impression'],3) *100 . '%';
        }
        if($sumData['click'] != 0){
            $sumData['cpc'] = round($sumData['cost']/$sumData['click'], 2);
            $sumData['clicktraderate'] = round($sumData['conversion']/$sumData['click'], 2);
        }
        if($sumData['conversion'] != 0){
            $sumData['cpt'] = round($sumData['cost']/$sumData['conversion'], 2);
        }
        # 激活均价
        if($sumData['activation'] != 0){
            $sumData['activated_price'] = round($sumData['cost']/$sumData['activation'], 2);
        }
        return $sumData;
    }
    /**
     * 正则校验数据
     * @param $type
     * @param $value
     * @return bool
     */
    public function matchValue($type, $value){
        switch($type){
            case 1: // 精确到小数点后两位的正数
                $state = preg_match('/^([1-9]\d*)+(.\d{1,2})?$|^0\.\d{1,2}$/', $value) ? true : false;
                break;
            default:
                $state = false;
        }
        return $state;
    }
    
    /**
     * 获取第一条记录
     * @param $params
     * @param string $resourceAction
     * @param string $resourceName
     * @param string $method
     * @return array
     */
    public function getFirstData($params, $resourceAction = '', $resourceName = '', $method = ''){
        $result = $this->send($params, $resourceAction, $resourceName, $method);
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0){
            return $resultArr['data']['list'][0];
        }else{
            return [];
        }
    }

    /**
     * 检查单元名是否重复，（计划/广告组/广告/创意）
     * @param $resourceName
     * @param $nameKey
     * @param $name
     * @param $idKey
     * @param int $id
     * @return bool
     */
    public function checkUnitName($resourceName, $nameKey, $name, $idKey, $id = 0){
        $params['filtering'][] = array(
            'field'=>$nameKey,
            'operator'=>'EQUALS',
            'values'=>[ $name ],
        );
        $result = $this->getFirstData($params, 'get', $resourceName);
        if(!empty($result)){
            if($result[$idKey] != $id){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    /**
     * 删除单元
     * @param $resourceName
     * @param $idKey
     * @param $ids
     * @return array|mixed|string
     */
    public function deleteUnit($resourceName, $idKey, $ids){
        $result = [];
        if(is_array($ids)){
            foreach($ids as $id){
                if($resourceName == 'campaigns'){// 推广计划的删除需要先暂停，再删除
                    $this->send(['configured_status'=>'AD_STATUS_SUSPEND','campaign_id'=>$id], 'update', $resourceName);
                }
                $data[$idKey] = $id;
                $result[] = $this->send($data, 'delete', $resourceName);
            }
        }else{
            if($resourceName == 'campaigns'){// 推广计划的删除需要先暂停，再删除
                $this->send(['configured_status'=>'AD_STATUS_SUSPEND','campaign_id'=>$ids], 'update', $resourceName);
            }
            $data[$idKey] = $ids;
            $result = $this->send($data, 'delete', $resourceName);
        }
        return $result;
    }

    /**
     * @param unknown_type $code
     * @param string $msg
     * @param array $data
     */
    public function output($code, $msg = '', $data = array()) {
        $codeDesc = Common_Expection_Marketing::getCodeDesc($code);
        $msg = $codeDesc ? : $msg;
        header("Content-type:text/json");
        exit(json_encode(array(
//            'success' => $code == 0 ? true : false,
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        )));
    }

    /**
     * 获取当前的数据的对应id数组(推广计划/广告组/创意/广告)
     * @param $data
     * @param $field
     * @return array
     */
    protected function getIds($data,$field){
        $ids = [];
        if(empty($data['list'])){
            return $ids;
        }
        foreach($data['list'] as $item){
            $ids[] = $item[$field];
        }
        return $ids;
    }

}
