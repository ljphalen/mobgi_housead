<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/14
 * Time: 21:19
 */
class Marketing_AccountController extends Admin_MarketingController {


    public $actions = [
        'getUrl' => '/Admin/Marketing_Account/get',
        'addUrl' => '/Admin/Marketing_Account/add',
        'editUrl' => '/Admin/Marketing_Account/edit',
        'changeStateUrl' => '/Admin/Marketing_Account/changeState',
    ];

    /**
     * 获取账号列表
     */
    public function getAction(){
        $info = $this->getInput(array('id','state', 'account_name', 'qq'));
        $where = $this->checkGetParam($info);
        $accountList = MobgiMarket_Service_SettingModel::getUserAuthsByParams($where);
        $accountList = $this->formatAccountList($accountList);
        $this->output(0, '获取成功', $accountList);
    }

    /**
     * 检查获取账号列表的参数
     * @param type $info
     * @return type
     */
    private function checkGetParam($info){
        $where = array();
        $where['user_id'] = $this->userInfo['user_id'];
        $where['id'] = $info['id'];
        $where['state'] = $info['state'];
        $where['account_name'] = array('LIKE', trim($info['account_name']));
        $where['qq'] = array('LIKE', trim($info['qq']));
        $where = $this->filterParams($where);
        return $where;
    }

    /**
     * 拼装账号列表
     * @param type $accountList
     * @return type
     */
    private function formatAccountList($accountList){
        if(empty($accountList)){
            return $accountList;
        }
        $oauthApiConfig = Common::getConfig('spmConfig', 'OAUTH_API');
        $gdtUrl = $oauthApiConfig['GDT_CODE_URL'];
        $data = array();
        foreach($accountList as $key => $value){
            $data[$key] = array(
                'id' => $value['id'],
                'account_id' => $value['account_id'],
                'account_name' => $value['account_name'],
                'qq' => $value['qq'],
                'message' => $value['message'],
                'state' => $value['state'],
                'create_time' => $value['create_time'],
            );
            $data[$key]['grant_url'] = str_replace(
                array('{client_id}', '{state}'),
                array($value['client_id'], $value['account_id']),
                $gdtUrl);
            if($value['token_time'] == 0){
                $data[$key]['grant_type'] = '待授权';
            }elseif($value['token_time'] + $value['access_token_expires_in'] < time()){
                $data[$key]['grant_type'] = '授权过期';
            }else{
                $data[$key]['grant_type'] = '已授权';
            }
        }
        return $data;
    }

    /**
     * 获取账号列表
     */
    public function getDetailAction(){
        $params = $this->getInput(array('start_date','end_date'));
        $where['user_id'] = $this->userInfo['user_id'];
        $where['state'] = 'ON';
        $result = MobgiMarket_Service_SettingModel::getUserAuthsByParams($where);
        $accountList['user_name'] = $this->userInfo['user_name'];
        $accountList['total_num'] = count($result);
        $accountList['list'] = [];
        foreach($result as $value){
            $account = [
                'account_id' => $value['account_id'],
                'account_name' => $value['account_name'],
                'qq' => $value['qq'],
                'corporation_name' => '',
                'daily_budget' => 0,
                'balance' => 0,
                'realtime_cost' => 0,
                'general_cash_balance' => 0,
                'realtime_cost' => 0,
                'general_gift_balance' => 0,
                'credit_roll_balance' => 0,
            ];
            # 获取账号日限额
            $information = $this->getAdvertiserData($value['account_id'], $value['access_token']);
            $informationArr = json_decode($information, TRUE);
            if($informationArr['code'] == 0){
                $account['corporation_name'] = $informationArr['data']['list'][0]['corporation_name'];
                $account['daily_budget'] = $informationArr['data']['list'][0]['daily_budget'];
            }
            # 获取余额 和 当日花费 （单位：分）
            $balance = $this->getAccountFunds($value['account_id'], $value['access_token']);
            $balanceArr = json_decode($balance, TRUE);
            if($balanceArr['code'] == 0){
                foreach($balanceArr['data']['list'] as $balanceVal){
                    if($balanceVal['fund_type'] == 'GENERAL_CASH'){ // 现金账户，单位由分 转为 元
                        $account['general_cash_balance'] = $balanceVal['balance'];
                        $account['realtime_cost'] = $balanceVal['realtime_cost'];
                    }
                    if($balanceVal['fund_type'] == 'GENERAL_GIFT'){ // 虚拟账户，单位由分 转为 元
                        $account['general_gift_balance'] = $balanceVal['balance'];
                    }
                    if($balanceVal['fund_type'] == 'CREDIT_ROLL'){ // 信用账户，单位由分 转为 元
                        $account['credit_roll_balance'] = $balanceVal['balance'];
                    }
                }
            }
            if(empty($params['start_date']) || empty($params['end_date'])){
                $params['start_date'] = $params['end_date'] = date('Y-m-d');
            }else{
                $params['start_date'] = date('Y-m-d',strtotime($params['start_date']));
                $params['end_date'] = date('Y-m-d',strtotime($params['end_date']));
            }
            $data = array(
                'account_id'=>$value['account_id'],
                'level'=>'ADVERTISER',
                'date_range'=>array('start_date'=>$params['start_date'], 'end_date'=>$params['end_date']),
            );
            $dailyReport = $this->send($data, 'get', 'daily_reports');
            $dailyReportArr = json_decode($dailyReport, TRUE);
            if($dailyReportArr['data']['page_info']['total_number'] > 0){
                $account['impression'] = $account['click'] = $account['cost'] = $account['ctr'] = 0;
                foreach($dailyReportArr['data']['list'] as $dailyReportVal){
                    $account['impression'] += $dailyReportVal['impression'];
                    $account['click'] += $dailyReportVal['click'];
                    $account['cost'] += $dailyReportVal['cost'];
                }
                if($account['impression'] > 0){
                    $account['ctr'] = round($account['click'] / $account['impression'] * 100 , 2) . '%';
                }
            }else{
                $account['impression'] = $account['click'] = $account['cost'] = $account['ctr'] = '-';
            }
            $accountList['list'][] = $account;
        }
        $this->output(0, '获取成功', $accountList);
    }


    /**
     * 获取广告数据列表
     */
    public function getAdDetailAction(){
        # 获取 待审核广告、未通过广告、有效广告 数目
        $data = [];
        $statusArr = [
            'pending_ad_num' => 'AD_STATUS_PENDING',
            'denied_ad_num' => 'AD_STATUS_DENIED',
            'normal_ad_num' => 'AD_STATUS_NORMAL',
        ];
        foreach($statusArr as $statusKey => $statusVal){
            $data[$statusKey] = 0;
            $statusFilter = [
                'field'=>'system_status',
                'operator'=>'EQUALS',
                'values'=>[$statusVal],
            ];
            if($statusVal == 'AD_STATUS_NORMAL'){
                # 查找开启的推广计划
                $params = [
                    'page_size' => 100,
                    'filtering' => [ ['field'=>'configured_status','operator'=>'EQUALS','values'=>['AD_STATUS_NORMAL']] ],
                ];
                $campaignResult = $this->send($params, 'get', 'campaigns');
                $campaignArr = json_decode($campaignResult, TRUE);
                $campaignNum = $campaignArr['data']['page_info']['total_number'];
                if($campaignNum != 0){
                    if($campaignNum > 10){
                        $data[$statusKey] = '(无法统计)';
                    }else{
                        foreach($campaignArr['data']['list'] as $campaignVal){
                            $campaignFilter = [
                                'field'=>'campaign_id',
                                'operator'=>'EQUALS',
                                'values'=>[$campaignVal['campaign_id']],
                            ];
                            $adgroupResult = $this->send([ 'page_size' => 1, 'filtering' => [$statusFilter,$campaignFilter] ], 'get', 'adgroups');
                            $adgroupArr = json_decode($adgroupResult, TRUE);
                            $data[$statusKey] += $adgroupArr['data']['page_info']['total_number'];
                        }
                    }
                }
            }else{
                $adgroupResult = $this->send([ 'page_size' => 1, 'filtering' => [$statusFilter] ], 'get', 'adgroups');
                $adgroupArr = json_decode($adgroupResult, TRUE);
                $data[$statusKey] = $adgroupArr['data']['page_info']['total_number'];
            }
        }
        $this->output(0, '获取成功', $data);
    }

    /**
     * 新增广点通账号
     */
    public function addAction(){
        $info = $this->getInput(array('account_id', 'account_name', 'qq'));
        $params = $this->checkAddParam($info);
        MobgiMarket_Service_SettingModel::addUserAuth($params);
        $this->output(0, '添加成功');
    }

    /**
     * 检查新增广点通账号的参数
     * @param type $info
     * @return type
     */
    private function checkAddParam($info){
        $checkList = [
            'account_id' => [['toInt'], [['empty', '','广点通账户ID不能为空','']], []],
            'account_name' => [['trim'], [['empty', '','账户名称不能为空','']], []],
        ];
        $info = $this->checkParams($info, $checkList);
        # 检查账户是否重复
        $accountData = MobgiMarket_Service_SettingModel::getUserAuthsByParams([
            'account_id' => $info['account_id'],
            'user_id' => $this->userInfo['user_id']
        ]);
        if($accountData){
            $this->output( -1, '广点通账户ID已经存在');
        }
        $info['qq'] = intval($info['qq']);
        $info['user_id'] = $this->userInfo['user_id'];
        $oauthAppConfig = Common::getConfig('spmConfig', 'OAUTH_APP');
        $info['client_id'] = $oauthAppConfig['client_id'];
        $info['client_secret'] = $oauthAppConfig['client_secret'];
        return $info;
    }

    /**
     * 编辑广点通账号
     */
    public function editAction(){
        $info = $this->getInput(array( 'id', 'account_name', 'qq'));
        $id = intval($info['id']);
        $data = $this->checkEditParam($info);
        MobgiMarket_Service_SettingModel::updateUserAuth($data, ['id'=>$id]);
        $this->output(0, '修改成功');
    }

    /**
     * 检查编辑广点通账号的参数
     * @param type $info
     * @return type
     */
    private function checkEditParam($info){
        $info['id'] = intval($info['id']);
        if(empty($info['id'])){
            $this->output( -1, '参数出错');
        }
        unset($info['id']);
        $info['account_name'] = trim($info['account_name']);
        if(empty($info['account_name'])){
            $this->output( -1, '账户名称不能为空');
        }
        $info['qq'] = intval($info['qq']);
        return $info;
    }

    /**
     * 修改广点通账号状态
     */
    public function changeStateAction(){
        $info = $this->getInput(array( 'id', 'state'));
        $id = intval($info['id']);
        $data = $this->checkChangeStateParam($info);
        MobgiMarket_Service_SettingModel::updateUserAuth($data, [ 'id' => $id ]);
        $this->output(0, '修改成功');
    }

    /**
     * 检查修改广点通账号状态的参数
     * @param type $info
     * @return type
     */
    private function checkChangeStateParam($info){
        $info['id'] = intval($info['id']);
        if(empty($info['id'])){
            $this->output( -1, '参数出错');
        }
        $info['state'] = trim($info['state']);
        if( !in_array($info['state'], array('ON','OFF')) ){
            $this->output ( -1, '修改状态错误' );
        }
        # 校验是否成功授权
        $accountData = MobgiMarket_Service_SettingModel::getUserAuthById($info['id']);
        if(empty($accountData)){
            $this->output( -1, '账号不存在');
        }
        if($info['state'] == 'ON'){
            $effectTime = $accountData['token_time'] + $accountData['access_token_expires_in'];
            if($effectTime < time()){
                $this->output( -1, '请重新授权账户');
            }
        }
        unset($info['id']);
        return $info;
    }
}