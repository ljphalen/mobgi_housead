<?php
/**      
 * 
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-8-15 16:23:35
 * $Id: Checkconfig.php 62100 2017-8-15 16:23:35Z hunter.fang $
 */
if (! defined ( 'BASE_PATH' ))
    exit ( 'Access Denied!' );

class Systemtool_CheckconfigController extends Admin_BaseController {
    public $actions = array (
            'listUrl' => '/Admin/Systemtool_Checkconfig/index',
            'configsUrl' => '/Admin/Systemtool_Checkconfig/configs',
            'getChannelListUrl' => '/Admin/Systemtool_Checkconfig/getChannelList',
            'getRequestDataUrl'=>'/Admin/Systemtool_Checkconfig/buildData'
    );
    public $perpage = 10;
    

    public function indexAction() {
        $info = $this->getInput ( 
                        array (
                                'interface_type',
                                'app_key',
                                'ad_type',
                                'channel_group',
                                'channel_id',
                                'ip',
                                'game_version',
                                'device_id',
                                'pos_key',
                                'request_data',
                                'act'
                        ) );
        // 设置默认的游戏版本
        $appList = MobgiApi_Service_AdAppModel::getsBy ( array (
                'is_check' => 1 
        ) );
        $channelGroups = MobgiApi_Service_ChannelModel::getsBy ( array (
                'group_id' => 0 
        ) );
        if ( $info ['channel_group']) {
            $channelParams ['group_id'] = $info ['channel_group'];
        }else{
            $channelParams ['group_id'] = array('>', 0);
        }
        $channels = MobgiApi_Service_ChannelModel::getsBy ($channelParams);
        $interfaceType = $this->getInterfaceTypeConfig();
        $ipArea = Common::getConfig ( 'intergrationConfig', 'ipArea' );
        $this->assign ( 'interfaceType',  $interfaceType);
        $this->assign ( 'appList', Common::resetKey($appList, 'app_key') );
        $this->assign ( 'channelGroups', $channelGroups );
        $this->assign ( 'channels', $channels );
        $this->assign ( 'ipArea', $ipArea );
        $this->assign ( 'search', $info );
        $this->fillDataToInfo ( $info, $interfaceType );

    }

     private function fillDataToInfo($info, $interfaceType) {
        if($info['act']){
            if (empty ( $info ['gameVersion'] )) {
                $info ['game_version'] = '1.0.0';
            }
            $apiHost = Yaf_Application::app ()->getConfig ()->mobgiroot;
            $url = $apiHost.$interfaceType[$info['interface_type']]['url'];
            $postData = html_entity_decode($info['request_data'],ENT_QUOTES);
            if ($info ['ip']) {
                $header['X-FORWARDED-FOR'] = $info ['ip'];
                $header['CLIENT-IP'] =$info ['ip'];
            }else{
                $header['X-FORWARDED-FOR'] = '210.21.221.18';
                $header['CLIENT-IP'] ='210.21.221.18';
            }
            // 模拟请求聚合逻辑
            if ($info ['interface_type'] == 'mobgi') {
                $url = $url . '?' . $postData;
                $curl = new Util_Http_Curl ( $url );
                $curl->setHeader ( $header );
                $reponseData = $curl->send ();
                $result = json_decode ( $reponseData, true );   
                $jsonResult['status'] = 0;
                $jsonResult['list'] = $reponseData;
                if ($result['success']) {
                    $jsonResult['status'] = 1;
                    $jsonResult['supportNetworkType'] = $result['globalConfig']['supportNetworkType'];
                    $jsonResult['lifeCycle'] = $result['globalConfig']['lifeCycle']/1000;
                    $blockList = Common::resetKey($result['data'], 'blockId');
                    foreach ($blockList as $key=>$val){
                        $blockList[$key]['generalConfig']= json_encode($val['configs']);
                        $blockList[$key]['prioritConfig']= json_encode($val['prioritConfig']);
                    }
                    $jsonResult['blockList'] = $blockList;
                } 
            }else {
                $header['content-type'] = 'application/json';
                $header['AUTHORIZATION'] =$this->getAdxToken();
                $curl = new Util_Http_Curl ( $url );
                $curl->setHeader($header);
                $curl->setData($postData);
                $reponseData = $curl->send ('POST');
                $result = json_decode ( $reponseData, true );
                $jsonResult['status'] = 0;
                if ($result['ret'] == Util_ErrorCode::CONFIG_SUCCESS) {
                    $jsonResult = $this->fillDataToJsonReuslt($info, $result);
                }

                $jsonResult['list'] = $reponseData;
            }
			$jsonResult['act'] = $info['act'];
            $this->assign ( 'url', $url );
            $this->assign ( 'info', $jsonResult );
        }
    }

    
    public function fillDataToJsonReuslt($info, $result){
        $jsonResult['status'] = 1;
        if($info ['interface_type'] == 'adx_v1'){
            if($result['data']['configType'] == 1){
                $globalConfig = $result['data']['configList']['globalConfig'];
                $thirdBlockList = $result['data']['configList']['thirdBlockList'];
                $appBlockList = $result['data']['configList']['appBlockList'];
                $thirdPartyAppInfo = $result['data']['configList']['thirdPartyAppInfo'];
            }else{
                $globalConfig = $result['data']['configList']['backupList']['globalConfig'];
                $thirdBlockList = $result['data']['configList']['backupList']['thirdBlockList'];
                $appBlockList = $result['data']['configList']['appBlockList'];
                $thirdPartyAppInfo = $result['data']['configList']['backupList']['thirdPartyAppInfo'];
            }
        }else{
            $globalConfig = $result['data']['globalConfig'];
            $thirdBlockList = $result['data']['thirdBlockList'];
            $appBlockList = $result['data']['appBlockIdList'];
            $thirdPartyAppInfo = $result['data']['thirdPartyAppInfo'];
        }
        $jsonResult['supportNetworkType'] = $globalConfig['supportNetworkType'];
        $jsonResult['lifeCycle'] = $globalConfig['lifeCycle']/1000;
        $appBlockList = Common::resetKey($appBlockList, 'blockId');
        $thirdPartyAppInfo = Common::resetKey($thirdPartyAppInfo, 'thirdPartyName');

        if($thirdBlockList){
			foreach ($thirdBlockList as $key=>$val){
				$generalConfig = isset($val['configs'])?$val['configs']:$val['generalConfigs'] ;
				foreach ($generalConfig as $ke=>$va){
					$generalConfig[$ke]['thirdPartyAppkey'] = $thirdPartyAppInfo[$va['thirdPartyName']]['thirdPartyAppkey'];
					$generalConfig[$ke]['thirdPartyAppsecret'] = $thirdPartyAppInfo[$va['thirdPartyName']]['thirdPartyAppsecret'];
				}
				$prioritConfig = $val['prioritConfig'] ;																																																																																																																																																																																																																																																																																																																																																																																																																				
				foreach ($prioritConfig as $ke=>$va){
					$prioritConfig[$ke]['thirdPartyAppkey'] = $thirdPartyAppInfo[$va['thirdPartyName']]['thirdPartyAppkey'];
					$prioritConfig[$ke]['thirdPartyAppsecret'] = $thirdPartyAppInfo[$va['thirdPartyName']]['thirdPartyAppsecret'];
				}
				$thirdBlockList[$key]['rate'] = $appBlockList[$val['blockId']]['rate'];
				$thirdBlockList[$key]['showLimit'] = $appBlockList[$val['blockId']]['showLimit'];
				$thirdBlockList[$key]['generalConfig']= json_encode($generalConfig);
				$thirdBlockList[$key]['prioritConfig']= json_encode($prioritConfig);
			}
		}
        $jsonResult['blockList'] = Common::resetKey($thirdBlockList, 'blockId');
        return $jsonResult;
        
    }
    
    public function getAdxToken(){
        $providerId = 1;
        $timeStamp = Common::getTime ();
        $sign = sha1 ( $providerId . $timeStamp );
        $token = base64_encode ( $providerId . ',' . $timeStamp . ',' . $sign );
        return $token;
    }
    
    
    public function buildDataAction(){
        $info = $this->getInput (
                        array (
                                'interface_type',
                                'app_key',
                                'ad_type',
                                'channel_group',
                                'channel_id',
                                'ip',
                                'game_version',
                                'device_id',
                                'pos_key',
                                'platform'
                        ) );
        
        $interfaceConfig = $this->getInterfaceTypeConfig();
        $config = $this->trimAll(html_entity_decode($interfaceConfig[$info['interface_type']]['format'], ENT_QUOTES));
        
        $data['url'] = $interfaceConfig[$info['interface_type']]['url'];
        if($info['platform'] == Common_Service_Const::ANDRIOD_PLATFORM){
            $model = 'huawei';
            $deviceId = '867348026517816';
			$ua='Mozilla5.0 (Linux; Android 8.1.0; Nexus 6P Build/OPM1.171019.011; ) AppleWebKit 537.36 (KHTML, like Gecko) Version 4.0 Chrome 65.0.3325.109 Mobile Safari 537.36';

        }else{
            $model = 'iPhone 6';
            $deviceId = '67303841-D7C3-4A0F-9A41-989FBB2D0FB2a';
			$ua='Mozilla 5.0 (iPhone; CPU iPhone OS 9_3_4 like Mac OS X) AppleWebKit 601.1.46 (KHTML, like Gecko) Mobile 13G35 Version 4.0 Chrome 65.0.3325.109 Mobile Safari 537.36';
        }
        $andriodId = '3f19006a660d7025';
        if($info['device_id']){
            $deviceId=  $info['device_id'];
        }
        $gameVersion = '1.0';
        if($info['game_version']){
            $gameVersion = $info['game_version'];
        }
        $blockId = '';
        if(!in_array($info['ad_type'],array(Common_Service_Const::PIC_AD_SUB_TYPE,Common_Service_Const::VIDEO_AD_SUB_TYPE))){
            $blockId = $info['pos_key'];
        }
        $channelId = '';
        if($info['channel_id']){
            $channelId = $info['channel_id'];
        }

        $sdkVersion = '2.0';
        if($info['interface_type'] != 'mobgi'){
            $data ['content'] = str_ireplace ( array (
                    '{screenDirection}' ,
                    '{deviceVersion}',
                    '{net}',
                    '{brand}',
                    '{model}',
                    '{screenSize}',
                    '{screenDirection}',
                    '{resolution}',
                    '{platform}',
                    '{deviceId}',
                    '{andriodId}',
                    '{operator}',
                    '{ua}',
                    '{providerId}',
                    '{adType}',
                    '{adSubType}',
                    '{appKey}',
                    '{channelId}',
                    '{bundle}',
                    '{gameVersion}',
                    '{blockId}',
                    '{adsList}',
                    '{sdkVersion}',
                    '{isNewUser}',
                    '{uuid}'
            ), array (
                    '1' ,
                    '9.0',
                    '1',
                    '6',
                    $model,
                    '2.4',
                    '2',
                    '720*1184',
                    $info['platform'],
                    $deviceId,
                    $andriodId,
                    '1',
                    $ua,
                    '1',
                    $info['ad_type'],
                    '0',
                    $info['app_key'],
                    $channelId,
                    'com.idreamsky.TestInterstitialPolymerization',
                    $gameVersion,
                    $blockId,
                    'AdMob,GDT,Inmobi,Mobgi,Chartboost',
                    $sdkVersion,
                    '0',
                    '3D9721DC-7EF7-418F-86FE-1836404F79AC',
            ), $config );
        }else{
            $adType = $info ['ad_type'] - 1;
            $data ['content'] = str_ireplace ( 
                            array (
                                    '{platform}',
                                    '{deviceId}',
                                    '{adType}',
                                    '{appKey}',
                                    '{channelId}',
                                    '{gameVersion}',
                                    '{adsList}',
                                    '{sdkVersion}',
                                    '{isNewUser}' 
                            ), 
                            array (
                                    $info ['platform'],
                                    $deviceId,
                                    $adType,
                                    $info ['app_key'],
                                    $channelId,
                                    $gameVersion,
                                    'AdMob,GDT,Inmobi,Mobgi,Chartboost',
                                    $sdkVersion,
                                    '0' 
                            ), 
                            $config );
        }

        $this->output(0,'ok',$data);
    }
    
   public  function trimAll($str){
        $space=array(" ","　","\t","\n","\r");
        return str_replace($space, '', $str);
    }
    
    
    public function getInterfaceTypeConfig(){
        $format = '{"device": {
                            "screenDirection": {screenDirection},
                            "version": "{deviceVersion}",
                            "net": {net},
                            "brand": "{brand}",
                            "model": "{model}",
                            "screenSize": {screenSize},
                            "resolution": "{resolution}",
                            "deviceId": "{deviceId}",
                            "platform": {platform},
                            "operator": {operator},
                            "andriodId":"{andriodId}",
                            "ua": "{ua}"
                        },
                        "providerId": {providerId},
                        "adType": {adType},
                        "adSubType":{adSubType},
                        "app": {
                            "appKey": "{appKey}",
                            "bundle": "{bundle}",
                            "version": "{gameVersion}",
                           "channelId":"{channelId}"
                        },
                         "imp": [{"blockId": "{blockId}"}],
                        "extra": {
                            "adsList": "{adsList}",
                            "sdkVersion": "{sdkVersion}",
                            "isNewUser": {isNewUser}
                        },
                        "user": {
                            "id": "{uuid}"
                        }
                    }';
        $interfaceType = array (
                'mobgi' => array (
                        'url' => '/VideoAds/getPicAdList',
                        'name' => '老聚合',
                        'method' => 'GET',
                        'format' => 'appKey={appKey}&adIntegrationType={adType}&sdkVersion={sdkVersion}&platform={platform}&adsList={adsList}&channelId={channelId}&gameVersion={gameVersion}&userId={deviceId}&isNewUser={isNewUser}' 
                ),
                'adx_v1' => array (
                        'url' => '/adx/v1/getAdList',
                        'name' => 'ADX_V1版本',
                        'method' => 'POST',
                        'format' => $format 
                ),
                'adx_v2_intergration' => array (
                        'url' => '/adx/v2/Intergration',
                        'name' => 'ADX_V2聚合接口',
                        'method' => 'POST',
                        'format' =>  $format
                ),
                'adx_v2_dsp' => array (
                        'url' => '/adx/v2/dsp',
                        'name' => 'ADX_V2DSP接口',
                        'method' => 'POST',
                        'format' => $format 
                ),
                'adx_v1_housead_dsp' => array (
                        'url' => '/adx/v1/getHouseadDsp',
                        'name' => 'houseadDsp请求接口',
                        'method' => 'POST',
                        'format' => $format 
                ) 
        );
        return $interfaceType;
    }

    public function configsAction() {
        $app_key = $this->getInput ( 'app_key' );
        if (empty ( $app_key )) {
            $app_key = '8E69498B356D95CCB579'; // 神庙逃亡2
        }
        $appInfo = MobgiApi_Service_AdAppModel::getBy ( array (
                'app_key' => $app_key 
        ) );
        if (empty ( $appInfo )) {
            $this->output ( 1, 'app not exist', array () );
        }
        $platform = $appInfo ['platform'];
        // 安桌渠道需要遍历各个渠道
        if ($platform == Common_Service_Const::ANDRIOD_PLATFORM) {
            $channelGroups = MobgiApi_Service_ChannelModel::getsBy ( array (
                    'group_id' => 0 
            ) );
            $useChannel = MobgiApi_Service_ChannelModel::getOneParentSubChannel ( "channel_id, channel_name, group_id", "is_check_config=1", 'group_id' );
            $useChannel = common::resetKey ( $useChannel, 'group_id' );
            $groupChannels = array ();
            foreach ( $channelGroups as $channelGroup ) {
                if (isset ( $useChannel [$channelGroup ['channel_id']] )) {
                    $item = [ ];
                    $item ['channel_id'] = $channelGroup ['channel_id'];
                    $item ['channel_name'] = $channelGroup ['channel_name'];
                    $item ['use_channel_id'] = $useChannel [$channelGroup ['channel_id']] ['channel_id'];
                    $item ['use_channel_name'] = $useChannel [$channelGroup ['channel_id']] ['channel_name'];
                    $groupChannels [] = $item;
                }
            }
            $this->assign ( 'groupChannels', $groupChannels );
        }
        $this->assign ( 'appInfo', $appInfo );
        $this->getAppKeyList ();
    }

    /**
     * 获取
     */
    public function getStateAction() {
        set_time_limit ( 0 );
        $app_key = $this->getInput ( 'app_key' );
        $adType = $this->getInput ( 'adType' );
        $platform = $this->getInput ( 'platform' );
        $useChannelId = $this->getInput ( 'use_channel_id' );
        $appInfo = MobgiApi_Service_AdAppModel::getBy ( array (
                'app_key' => $app_key 
        ) );
        if (empty ( $appInfo )) {
            $this->output ( 1, 'app not exist', array () );
        }
        $platform = $appInfo ['platform'];
        $cache = Cache_Factory::getCache ( Cache_Factory::ID_REMOTE_REDIS );
        if ($platform == Common_Service_Const::ANDRIOD_PLATFORM) {
            $stateKey = 'checkconfig_appkey_channel_adType_' . $app_key . '_' . $useChannelId . '_' . $adType;
        } else {
            $stateKey = 'checkconfig_appkey_channel_adType_' . $app_key . '_ios_' . $adType;
        }
        $cacheValue = $cache->get ( $stateKey );
        if ($cacheValue) {
            $this->output ( 0, 'ok', $cacheValue );
        }
        $data = $this->curlProvinceState ( $app_key, $useChannelId, $platform, $adType ); // 1全部开启 2部分开启 3全部关闭
        $CACHE_EXPRIE = 300;
        $cache->set ( $stateKey, $data, $CACHE_EXPRIE );
        $this->output ( 0, 'ok', $data );
    }

    public function curlProvinceState($app_key, $channel, $platform, $adxAdIntegrationType) {
        $ipArea = Common::getConfig ( 'intergrationConfig', 'ipArea' );
        $gameVersion = '2.74.1';
        $apiHost = Yaf_Application::app ()->getConfig ()->apiroot;
        //$apiHost = 'http://api.mobgi.com';
        // $url = $apiHost . '/adx/v1/getAdList';
        $url = $apiHost . '/adx/v2/intergration';
        $result = array ();
        foreach ( $ipArea as $area => $ip ) {
            $providerId = 1;
            $timeStamp = Common::getTime ();
            $sign = sha1 ( $providerId . $timeStamp );
            $token = base64_encode ( $providerId . ',' . $timeStamp . ',' . $sign );
            $header = array (
                    'AUTHORIZATION: Bearer ' . $token 
            );
            if ($ip) {
                $header [] = 'X-FORWARDED-FOR:' . $ip;
                $header [] = 'CLIENT-IP:' . $ip;
            }
            $deviceId = 'C20F62F2-4F10-4AE1-8182-11D5893833EF';
            $postData = '{"device":{"screenDirection":2,"version":"10.300000","net":1,"brand":"iPhone 7","deviceId":"' . $deviceId . '","platform":' . $platform . ',"ip":"","operator":1,"ua":"Mozilla\/5.0 (iPhone; CPU iPhone OS 10_3_2 like Mac OS X) AppleWebKit\/603.2.4 (KHTML, like Gecko) Mobile\/14F89","model":"iPhone","screenSize":-1,"resolution":"750*1334"},"providerId":1,"adType":' . $adxAdIntegrationType . ',"app":{"appKey":"' . $app_key . '","bundle":"com.indiesky.matchs","name":"com.indiesky.matchs","version":"' . $gameVersion . '","channelId":"' . $channel . '"},"imp":[{"blockId":"MC4xODU0NjIwMCAxNDk4MTI-MkNFREE3","attr":{"w":0,"h":0,"pos":0},"bidfloor":0}],"adSubType":3,"isTest":0,"extra":{"adsList":"","sdkVersion":"2.3.0","isNewUser":0},"sdkVersion":"2.3.0","user":{"id":"41B89D69-2745-4C28-AECB-8A89BBB1C34B"}}';
            $curl_url = $url;
            $ch = curl_init ( $curl_url );
            curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header ); // 加入header
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true ); // 获取数据返回
            curl_setopt ( $ch, CURLOPT_BINARYTRANSFER, true ); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
            curl_setopt ( $ch, CURLOPT_TIMEOUT, 30 ); // 设置超时时间30秒
            curl_setopt ( $ch, CURLOPT_POST, 1 );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postData );
            $jsonResult = curl_exec ( $ch );
            $arrResult = json_decode ( $jsonResult, true );
            $result [$area] = array (
                    'code' => $arrResult ['ret'],
                    'msg' => $arrResult ['msg'] 
            );
            // break;
            // if (count(array_unique($result))>=2){
            // break;
            // }
        }
        return $result;
        // $count = count(array_unique($result));
        // // var_dump($result);
        // //返回状态码：1全部开启 2部分开启 3全部关闭
        // if($count == 2){
        // return 2;
        // }else{
        // //值为true，说明状态码ret不为0，即有
        // if(array_pop($result)){
        // return 3;
        // }else{
        // return 1;
        // }
        // }
    }

    public function getProvinceStateAction() {
    }

    public function getTokenAction() {
        $this->mProviderId = $this->getGet ( 'providerId' );
        $timeStamp = Common::getTime ();
        $sign = sha1 ( $this->mProviderId . $timeStamp );
        $token = base64_encode ( $this->mProviderId . ',' . $timeStamp . ',' . $sign );
        echo $token;
    }

    /**
     * 获取渠道组下的渠道json串
     */
    public function getChannelListAction() {
        $params = $this->getInput ( array (
                'group_id' 
        ) );
        $whereParams = array ();
        if (isset ( $params ['group_id'] ) && $params ['group_id']) {
            $whereParams ['group_id'] = $params ['group_id'];
        }
        $channels = MobgiApi_Service_ChannelModel::getsBy ( $whereParams );
        $this->output ( 0, 'ok', $channels );
    }

    private function getAppKeyList() {
        $search = $params = array ();
        $page = intval ( $this->getInput ( 'page' ) );
        if ($page < 1)
            $page = 1;
        $search = $this->getInput ( array (
                'platform',
                'app_name' 
        ) );
        if (trim ( $search ['app_name'] )) {
            $appKeys = MobgiApi_Service_AdAppModel::getAppKeysByName ( $search ['app_name'] );
            if ($appKeys) {
                $params ['app_key'] = array (
                        'IN',
                        $appKeys 
                );
            } else {
                $params ['app_key'] = '0';
            }
        }
        if (isset ( $search ['platform'] ) && $search ['platform']) {
            $params ['platform'] = $search ['platform'];
        }
        $params ['is_check'] = MobgiApi_Service_AdAppModel::ISCHECK_PASS;
        list ( $total, $appList ) = MobgiApi_Service_AdAppModel::getList ( $page, $this->perpage, $params, array (
                'update_time' => 'DESC' 
        ) );
        $url = $this->actions ['configsUrl'] . '/?' . http_build_query ( $search ) . '&';
        $this->assign ( 'pager', Common::getPages ( $total, $page, $this->perpage, $url ) );
        foreach ( $appList as $key => $value ) {
            if (! stristr ( $value ['icon'], 'http' )) {
                $appList [$key] ['icon'] = Common::getAttachPath () . $value ['icon'];
            }
            if ($value ['platform'] == Common_Service_Const::ANDRIOD_PLATFORM) {
                $appList [$key] ['platform_class'] = 'android';
                $appList [$key] ['platform_name'] = 'Android';
            } else {
                $appList [$key] ['platform_class'] = 'ios';
                $appList [$key] ['platform_name'] = 'Ios';
            }
            // $appList [$key] ['is_config'] = MobgiApi_Service_FlowConfModel::getBy ( array (
            // 'app_key' => $value ['app_key'],
            // 'conf_type' => MobgiApi_Service_FlowConfModel::DEAFAULT_CONF_TYPE ) ) ? '已配置' : '未配置';
        }
        $this->assign ( 'appList', $appList );
        $this->assign ( 'search', $search );
        $this->assign ( 'total', $total );
    }
}
