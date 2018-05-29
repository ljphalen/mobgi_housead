<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/9
 * Time: 20:49
 */

class ToolsController extends Spm_BaseController{

    private $formatKeys = [
        'authorization_code' => ['authorization_code', ['normal'], ''],
        'state' => ['state', ['normal'], '']
    ];

    public function indexAction()
    {
        $this->output(Common_Expection_Spm::EXP_FAILED, 'nothing');
    }

    public function gdtAccessTokenAction(){
        $params = $_GET;
        $params = $this->format($params,$this->formatKeys);
        if(empty($params['authorization_code']) || empty($params['state'])){
//            $this->redirect('http://admin.mobgi.com/marketing/#/setting/accountmanage'); // 直接跳转页面
//            $this->output(Common_Expection_Spm::EXP_SUCCESS, '请返回后台，查看用户授权情况');
            $this->output(Common_Expection_Spm::EXP_PARAM_ERROR, 'empty params');
        }
        # 获取账户配置
        $accountId = intval($params['state']);
        $accountData = MobgiMarket_Service_SettingModel::getUserAuthByParams( ['account_id' => $accountId]);
        if(empty($accountData)){
            $this->output(Common_Expection_Spm::EXP_EMTPY_CONFIG, 'empty config');
        }
        # 拼装 获取 access_token 的url
        $accountData['authorization_code'] = $params['authorization_code'];
        $codeTime = time();
        $oauthApiConfig = Common::getConfig('spmConfig', 'OAUTH_API');
        $gdtUrl = $oauthApiConfig['GDT_ACCESS_TOKEN_BY_CODE_URL'];
        foreach(['client_id','client_secret','authorization_code'] as $value){
            $gdtUrl = str_replace('{'.$value.'}', $accountData[$value], $gdtUrl);
        }
        $result = file_get_contents($gdtUrl);
        $resultArr = json_decode($result, true);
        # 校验结果,保存 message 结果
        if($resultArr['code'] != 0){
            $data = [
                'authorization_code' => $params['authorization_code'],
                'code_time' => $codeTime,
                'message' => $resultArr['message']
            ];
            MobgiMarket_Service_SettingModel::updateUserAuth($data, [ 'account_id' => $accountId]);
            $this->output(Common_Expection_Spm::EXP_FAILED, $resultArr['message']);
        }
        # QQ号码 $resultArr['data']['authorizer_info']['account_uin']
        $data = [
            'authorization_code' => $params['authorization_code'],
            'code_time' => $codeTime,
            'access_token' => $resultArr['data']['access_token'],
            'refresh_token' => $resultArr['data']['refresh_token'],
            'access_token_expires_in' => $resultArr['data']['access_token_expires_in'],
            'refresh_token_expires_in' => $resultArr['data']['refresh_token_expires_in'],
            'token_time' => time(),
            'message' => $resultArr['message']
        ];
        $data['message'] = $data['message'] ? : '已授权';
        MobgiMarket_Service_SettingModel::updateUserAuth($data, [ 'account_id' => $accountId]);
        $this->redirect('http://admin.mobgi.com/marketing/#/setting/accountmanage'); // 直接跳转页面
//        $this->output(Common_Expection_Spm::EXP_SUCCESS, $resultArr['message']);
    }

    public function gdtRefreshTokenAction(){
        $params = $_GET;
        if(!isset($params['sign']) || $params['sign'] !='refreshtoken'){
            $this->output(Common_Expection_Spm::EXP_SIGN_ERROR, 'sign error');
        }
        # 获取账户列表
        $accountList = MobgiMarket_Service_SettingModel::getUserAuthsByParams( ['state' => 'ON']);
        if(empty($accountList)){
            $this->output(Common_Expection_Spm::EXP_EMTPY_CONFIG, 'empty config');
        }
        $oauthApiConfig = Common::getConfig('spmConfig', 'OAUTH_API');
        $gdtUrl = $oauthApiConfig['GDT_ACCESS_TOKEN_BY_REFRESH_TOKEN_URL'];
        foreach($accountList as $key => $accountVal){
            $url = $gdtUrl;
            foreach(['client_id','client_secret','refresh_token'] as $keyVal){
                $url = str_replace('{'.$keyVal.'}', $accountVal[$keyVal], $url);
            }
            $result = file_get_contents($url);
            $resultArr = json_decode($result, true);

            # 校验结果,保存 message 结果
            if($resultArr['code'] != 0){
                $logContent = date('H:i:s') . "\n"  .
                '| gdtUrl:' . $url . "\n"  .
                '| accountInfo:' . json_encode($accountVal) . "\n"  .
                '| errorResult:' . json_encode($resultArr) . "\n";
                $this->markGdtError($logContent);
                $data = [
                    'message' => $resultArr['message']
                ];
                MobgiMarket_Service_SettingModel::updateUserAuth($data, ['id'=>$accountVal['id']]);
            }else{
                # QQ号码 $resultArr['data']['authorizer_info']['account_uin']
                $data = [
                    'access_token' => $resultArr['data']['access_token'],
                    'refresh_token' => $resultArr['data']['refresh_token'],
                    'access_token_expires_in' => $resultArr['data']['access_token_expires_in'],
                    'token_time' => time(),
                    'message' => $resultArr['message']
                ];
                $data['message'] = empty($data['message']) ? '已授权' : $data['message'];
                MobgiMarket_Service_SettingModel::updateUserAuth($data, ['id'=>$accountVal['id']]);
            }
        }
        $this->output(Common_Expection_Spm::EXP_SUCCESS, '');
    }

    private function markGdtError($logContent){
        $typeSendToFile = 3;
        $fileName = 'gdt_refreshtoken_' . date('Ymd') . '.log';
        $filePath = Common::getConfig('siteConfig', 'logPath') . $fileName;
        error_log($logContent, $typeSendToFile, $filePath);
    }
}