<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/9
 * Time: 20:48
 */

class TrackController extends Spm_BaseController{

    public function indexAction(){
        $this->output(Common_Expection_Spm::EXP_FAILED,'nothing');
    }

    private $formatKeys = [
        'acid' => ['acid', ['normal'], 0],
        'idfa' => ['idfa', ['toUpper'], ''],
        'imei' => ['imei', ['toLower'], ''],
        'midfa' => ['midfa', ['toLower'], ''],
        'muid' => ['muid', ['toLower'], ''],
        'sub_channel' => ['sub_channel', ['normal'], ''],
        'redirect' => ['redirect', ['normal'], 'true'],
        'ip' => ['ip', ['normal'], '']
    ];

    /*
     * tracking api for guangdiantong
     */
    public function gdtAction() {
        header("Content-type: text/html; charset=utf-8");
        $params = $_GET;
        $acid = $params['acid'];
        # 投放人员错误创建活动，临时性适配代码
        if($acid == 11120){
            $this->gdtcgiAction();die;
        }
        $channelNo = 'guangdiantong';
        if(empty($acid)){
            $this->output(Common_Expection_Spm::EXP_UNKOWN_ACID,'unknown acid');
        }
        # find activity by acid
        $activity = MobgiSpm_Service_TrackModel::getActivityById($acid);
        if(empty($activity)){
            $this->output(Common_Expection_Spm::EXP_ACTIVITY_NOT_EXIST,'tracking is not exist');
        }
        # check the activity delivery status
        if($activity['status'] != 2 && $activity['status'] != 4){
            $this->output(Common_Expection_Spm::EXP_ACTIVITY_NOT_DELIVERY,'tracking is not delivery');
        }
        # find app by app_id
        $appId = $activity['app_id'];
        $app = MobgiSpm_Service_TrackModel::getAppById($appId);
        if(empty($app)){
            $this->output(Common_Expection_Spm::EXP_APP_NOT_EXIST,'app is not exist');
        }
        $locationUrl = empty($activity['origin_url']) ? $app['appstore_url'] : $activity['origin_url'];
        if (empty($params['muid'])) {
            $ip = empty($params['ip']) ? Common::getClientIP() : $params['ip'];
            $ua = Common::getUserAgent();
            $ipua = $this->getIpua($ip, $ua);
            # push ipua data into redis queue
            $queueArr = array(
                'idfa' => $ipua,
                'idfamd5' => $ipua,
                'ipua' => $ipua,
                'activity_id' => $activity['id'],
                'ip' => $ip,
                'game_id' => $app['appstore_id'],
                'mober' => $channelNo,
                'sub_channel' => '',
                'callback' => 'unknown',
                'clicktime' => time(),
                'ua' => $ua,
            );
            $this->pushData($queueArr);
            header("Location:" . $locationUrl);die;
        }

        # format device id and ipua
        $ip = empty($params['ip']) ? Common::getClientIP() : $params['ip'];
        $ua = Common::getUserAgent();
        $ipua = $this->getIpua($ip, $ua);
        $gdtCallback = $this->getMoberCallback($channelNo);
        # replace {data} while active
        $callback = sprintf($gdtCallback, $params['appid'], 'MOBILEAPP_ACTIVITE', 'IOS', $params['advertiser_id']);
        $clickTime = empty($params['click_time']) ? time() : $params['click_time'];
        $subChannel = isset($params['sub_channel']) ? $params['sub_channel'] : '';
        # push data into redis queue
        $queueArr = array(
            'idfa' => $params['muid'],
            'idfamd5' => $params['muid'],
            'ipua' => $ipua,
            'activity_id' => $activity['id'],
            'ip' => $ip,
            'game_id' => $app['appstore_id'],
            'mober' => $channelNo,
            'sub_channel' => $subChannel,
            'callback' => $callback,
            'clicktime' => $clickTime,
            'ua' => $ua,
        );
        $this->pushData($queueArr);
        if(empty($locationUrl)){
            $this->output(Common_Expection_Spm::EXP_EMPTY_LOCATION_URL,'empty location url');
        }else{
            $this->output(Common_Expection_Spm::EXP_SUCCESS,'success');
        }
    }

    /*
     * tracking api for gdtcgi
     */
    public function gdtcgiAction() {
        // self::_set_exe_log_point("Conf-Index");
        header("Content-type: text/html; charset=utf-8");
        $params = $_GET;
        $acid = $params['acid'];
        $channelNo = 'gdtcgi';
        if(empty($acid)){
            $this->output(Common_Expection_Spm::EXP_UNKOWN_ACID,'unknown acid');
        }
        # find activity by acid
        $activity = MobgiSpm_Service_TrackModel::getActivityById($acid);
        if(empty($activity)){
            $this->output(Common_Expection_Spm::EXP_ACTIVITY_NOT_EXIST,'tracking is not exist');
        }
        # check the activity delivery status
        if($activity['status'] != 2 && $activity['status'] != 4){
            $this->output(Common_Expection_Spm::EXP_ACTIVITY_NOT_DELIVERY,'tracking is not delivery');
        }
        # find app by app_id
        $appId = $activity['app_id'];
        $app = MobgiSpm_Service_TrackModel::getAppById($appId);
        if(empty($app)){
            $this->output(Common_Expection_Spm::EXP_APP_NOT_EXIST,'app is not exist');
        }
        $locationUrl = empty($activity['origin_url']) ? $app['appstore_url'] : $activity['origin_url'];
        if (empty($params['muid'])) {
            $ip = empty($params['ip']) ? Common::getClientIP() : $params['ip'];
            $ua = Common::getUserAgent();
            $ipua = $this->getIpua($ip, $ua);
            # push ipua data into redis queue
            $queueArr = array(
                'idfa' => $ipua,
                'idfamd5' => $ipua,
                'ipua' => $ipua,
                'activity_id' => $activity['id'],
                'ip' => $ip,
                'game_id' => $app['appstore_id'],
                'mober' => $channelNo,
                'sub_channel' => '',
                'callback' => 'unknown',
                'clicktime' => time(),
                'ua' => $ua,
            );
            $this->pushData($queueArr);
            header("Location:" . $locationUrl);die;
        }
        # format device id and ipua
        $ip = empty($params['ip']) ? Common::getClientIP() : $params['ip'];
        $ua = Common::getUserAgent();
        $ipua = $this->getIpua($ip, $ua);
        $gdtCallback = $this->getMoberCallback($channelNo);
        $appType = strtoupper($params['app_type']);
        # replace {active_time} {active_ip} {encstr} while active
        $callback = sprintf($gdtCallback, $params['appid'], $params['click_id'], $params['muid'], $params['appid'], $params['advertiser_id'], $appType, 'MOBILEAPP_ACTIVITE');
        $clickTime = empty($params['click_time']) ? time() : $params['click_time'];
        $subChannel = isset($params['sub_channel']) ? $params['sub_channel'] : '';
        # push data into redis queue
        $queueArr = array(
            'idfa' => $params['muid'],
            'idfamd5' => $params['muid'],
            'ipua' => $ipua,
            'activity_id' => $activity['id'],
            'ip' => $ip,
            'game_id' => $app['appstore_id'],
            'mober' => $channelNo,
            'sub_channel' => $subChannel,
            'callback' => $callback,
            'clicktime' => $clickTime,
            'ua' => $ua,
        );
        $this->pushData($queueArr);
        if(empty($locationUrl)){
            $this->output(Common_Expection_Spm::EXP_EMPTY_LOCATION_URL,'empty location url');
        }else{
            $this->output(Common_Expection_Spm::EXP_SUCCESS,'success');
        }
    }

    /*
     * tracking api for wechat
     */
    public function wechatAction() {
        header("Content-type: text/html; charset=utf-8");
        $params = $_GET;
        $acid = $params['acid'];
        $channelNo = 'wechat';
        if(empty($acid)){
            $this->output(Common_Expection_Spm::EXP_UNKOWN_ACID,'unknown acid');
        }
        # find activity by acid
        $activity = MobgiSpm_Service_TrackModel::getActivityById($acid);
        if(empty($activity)){
            $this->output(Common_Expection_Spm::EXP_ACTIVITY_NOT_EXIST,'tracking is not exist');
        }
        # check the activity delivery status
        if($activity['status'] != 2 && $activity['status'] != 4){
            $this->output(Common_Expection_Spm::EXP_ACTIVITY_NOT_DELIVERY,'tracking is not delivery');
        }
        # find app by app_id
        $appId = $activity['app_id'];
        $app = MobgiSpm_Service_TrackModel::getAppById($appId);
        if(empty($app)){
            $this->output(Common_Expection_Spm::EXP_APP_NOT_EXIST,'app is not exist');
        }
        $locationUrl = empty($activity['origin_url']) ? $app['appstore_url'] : $activity['origin_url'];
        if (empty($params['muid'])) {
            $ip = empty($params['ip']) ? Common::getClientIP() : $params['ip'];
            $ua = Common::getUserAgent();
            $ipua = $this->getIpua($ip, $ua);
            # push ipua data into redis queue
            $queueArr = array(
                'idfa' => $ipua,
                'idfamd5' => $ipua,
                'ipua' => $ipua,
                'activity_id' => $activity['id'],
                'ip' => $ip,
                'game_id' => $app['appstore_id'],
                'mober' => $channelNo,
                'sub_channel' => '',
                'callback' => 'unknown',
                'clicktime' => time(),
                'ua' => $ua,
            );
            $this->pushData($queueArr);
            header("Location:" . $locationUrl);die;
        }
        # format device id and ipua
        $ip = empty($params['ip']) ? Common::getClientIP() : $params['ip'];
        $ua = Common::getUserAgent();
        $ipua = $this->getIpua($ip, $ua);
        $gdtCallback = $this->getMoberCallback($channelNo);
        $appType = strtoupper($params['app_type']);
        # replace {data} while active
        $callback = sprintf($gdtCallback, $params['appid'], $params['click_id'], $params['muid'], $params['appid'], 'MOBILEAPP_ACTIVITE', $appType, $params['advertiser_id']);
        $clickTime = empty($params['click_time']) ? time() : $params['click_time'];
        $subChannel = isset($params['sub_channel']) ? $params['sub_channel'] : '';
        # push data into redis queue
        $queueArr = array(
            'idfa' => $params['muid'],
            'idfamd5' => $params['muid'],
            'ipua' => $ipua,
            'activity_id' => $activity['id'],
            'ip' => $ip,
            'game_id' => $app['appstore_id'],
            'mober' => $channelNo,
            'sub_channel' => $subChannel,
            'callback' => $callback,
            'clicktime' => $clickTime,
            'ua' => $ua,
        );
        $this->pushData($queueArr);
        if(empty($locationUrl)){
            $this->output(Common_Expection_Spm::EXP_EMPTY_LOCATION_URL,'empty location url');
        }else{
            $this->output(Common_Expection_Spm::EXP_SUCCESS,'success');
        }
    }

    /*
     * tracking api for baidu
     */
    public function baiduAction() {
        header("Content-type: text/html; charset=utf-8");
        $params = $_GET;
        $acid = $params['acid'];
        $channelNo = 'sbapi';
        if(empty($acid)){
            $this->output(Common_Expection_Spm::EXP_UNKOWN_ACID,'unknown acid');
        }
        # find activity by acid
        $activity = MobgiSpm_Service_TrackModel::getActivityById($acid);
        if(empty($activity)){
            $this->output(Common_Expection_Spm::EXP_ACTIVITY_NOT_EXIST,'tracking is not exist');
        }
        # check the activity delivery status
        if($activity['status'] != 2 && $activity['status'] != 4){
            $this->output(Common_Expection_Spm::EXP_ACTIVITY_NOT_DELIVERY,'tracking is not delivery');
        }
        # find app by app_id
        $appId = $activity['app_id'];
        $app = MobgiSpm_Service_TrackModel::getAppById($appId);
        if(empty($app)){
            $this->output(Common_Expection_Spm::EXP_APP_NOT_EXIST,'app is not exist');
        }
        $locationUrl = empty($activity['origin_url']) ? $app['appstore_url'] : $activity['origin_url'];
        $uid = empty($params['idfa']) ? strtolower($params['imei']) : strtoupper($params['idfa']);
        # 百度异常转化muid为imei，特殊处理
        $muid = $params['muid'];
        if(!empty($params['imei'])){
            $muid = $params['imei'];
        }
        if(empty($muid)) {
            $muid = md5($uid);
        }else{
            $uid = $muid;
        }
        if (empty($uid)) {
            header("Location:" . $locationUrl);die;
        }
        # format device id and ipua
        $ip = empty($params['ip']) ? Common::getClientIP() : $params['ip'];
        $ua = Common::getUserAgent();
        $ipua = $this->getIpua($ip, $ua);
        $userId = $params['userid'];
        $subChannel = isset($params['sub_channel']) ? $params['sub_channel'] : '';
        # api已经自动url解码了，不需要重复操作
        $baiduCallback = $params['callback'];
        $callback = str_replace(array( '{{ATYPE}}', '{{AVALUE}}' ), array( 'activate', 0 ), $baiduCallback);
        $config = MobgiSpm_Service_ChannelModel::getBaiduConfigByUserId($userId);
        if(empty($config)){
            $this->output(Common_Expection_Spm::EXP_PARAM_ERROR,'this akey has not config');
        }
        $akey = $config['akey'];
        $sign = md5($callback.$akey);
        $callback .= '&sign='.$sign;

        # push data into redis queue
        $queueArr = array(
            'idfa' => $uid,
            'idfamd5' => $muid,
            'ipua' => $ipua,
            'activity_id' => $activity['id'],
            'ip' => $ip,
            'game_id' => $app['appstore_id'],
            'mober' => $channelNo,
            'sub_channel' => $subChannel,
            'callback' => $callback,
            'clicktime' => time(),
            'ua' => $ua,
        );
        $this->pushData($queueArr);
        if(empty($locationUrl)){
            $this->output(Common_Expection_Spm::EXP_EMPTY_LOCATION_URL,'empty location url');
        }else{
            $this->output(Common_Expection_Spm::EXP_SUCCESS,'success');
        }

    }

    /*
     * tracking api
     */
    public function commonAction(){
        header("Content-type: text/html; charset=utf-8");
        $params = $_GET;
        $params = $this->format($params,$this->formatKeys);
        $acid = $params['acid'];
        if(empty($acid)){
            $this->output(Common_Expection_Spm::EXP_UNKOWN_ACID,'unknown acid');
        }
        # find activity by acid
        $activity = MobgiSpm_Service_TrackModel::getActivityById($acid);
        if(empty($activity)){
            $this->output(Common_Expection_Spm::EXP_ACTIVITY_NOT_EXIST,'tracking is not exist');
        }
        # check the activity delivery status
        if($activity['status'] != 2 && $activity['status'] != 4){
            $this->output(Common_Expection_Spm::EXP_ACTIVITY_NOT_DELIVERY,'tracking is not delivery');
        }
        # find app by app_id
        $appId = $activity['app_id'];
        $app = MobgiSpm_Service_TrackModel::getAppById($appId);
        if(empty($app)){
            $this->output(Common_Expection_Spm::EXP_APP_NOT_EXIST,'app is not exist');
        }
        $this->recordData($app,$activity,$params);

    }

    /**
     * format tracking data
     * @param $app
     * @param $activity
     * @param $params
     */
    public function recordData($app, $activity, $params){

        $uid = empty($params['idfa']) ? $params['imei'] : $params['idfa']; // save device id ,idfa upper ,imei lower
        $muid = empty($params['midfa']) ? $params['muid'] : $params['midfa']; // save device id md5 ,must be lower

        # replace macro params in locate url
        $locateMacros = $this->getMacros($params,'locate_macros'); // get the macro params of the locate_url
        $locationUrl = empty($activity['origin_url']) ? $app['appstore_url'] : $activity['origin_url'];
        $locationUrl = $this->replaceMacros($locationUrl,$locateMacros);

        # replace macro params in callback url
        $callbackMacros = $this->getMacros($params,'callback_macros'); // get the defined macro params
        $callbackUrl = $activity['callback_config'];
        $callbackUrl = $this->replaceMacros($callbackUrl,$callbackMacros);
        $callbackUrl = empty($callbackUrl) ? 'unknown' : rawurldecode($callbackUrl);

        # format device id and ipua
        $ip = empty($params['ip']) ? Common::getClientIP() : $params['ip'];
        $ua = Common::getUserAgent();
        $ipua = $this->getIpua($ip, $ua);
        # choose the unique id as idfa
        list($uid, $muid) = $this->chooseUniqueId($uid, $muid, $ipua);

        # get channel data
        $channel = MobgiSpm_Service_ChannelModel::getChannelById($activity['channel_id']);

        # push data into redis queue
        $queueArr = array(
            'idfa' => $uid,
            'idfamd5' => $muid,
            'ipua' => $ipua,
            'activity_id' => $activity['id'],
            'ip' => $ip,
            'game_id' => $app['appstore_id'],
            'mober' => $channel['channel_no'],
            'sub_channel' => $params['sub_channel'],
            'callback' => $callbackUrl,
            'clicktime' => time(),
            'ua' => $ua,
        );
        $this->pushData($queueArr);
        # location
        $this->customResponse($locationUrl,$params['redirect']);

    }

    /**
     * choose the unique id as uid
     * @param $uid
     * @param $muid
     * @param $ipua
     * @return array
     */
    private function chooseUniqueId($uid, $muid, $ipua){
        if(!empty($uid)){
            $muid = md5($uid);
        }elseif(!empty($muid)){
            $uid = $muid;
        }else{
            $uid = $muid = $ipua;
        }
        if(in_array($uid, ['{idfa}','{imei}']) || in_array($muid, ['muid','midfa'])){
            $uid = $muid = $ipua;
        }
        return array($uid, $muid);
    }

    /**
     * locate url or response result
     * @param $locationUrl
     * @param $redirect
     */
    private function customResponse($locationUrl, $redirect){
        if(empty($locationUrl)){
            $this->output(Common_Expection_Spm::EXP_EMPTY_LOCATION_URL,'empty location url');
        }
        if(strcmp($redirect, 'js') == 0){
            die("<script>window.location.href ='".$locationUrl."'</script>");
        }
        if(strcmp($redirect, 'true') == 0){
            header("Location:" . $locationUrl);die;
        }
        $this->output(Common_Expection_Spm::EXP_SUCCESS,'success');
    }

    /*
     * push tracking data in redis
     * @param $queueArr
     * @return mixed
     */
    private function pushData($queueArr){
        $redis = Common::getQueue('spm');
        $write = $redis->push('RQ:AdTrackMonitorRedisQ_CLICK', $queueArr);//push into redis list
        if ($write <= 0) {
            $this->error('save the data error',Common_Expection_Spm::EXP_REDIS_ERROR);
        }
        return $write;
    }

    /*
     * active api for active data
     */
    public function activeAction() {
        $get = $_GET;
        $data = rawurldecode($get['data']);
        $ip = Common::getClientIP();
        $data .= '|'.$ip;
        $params = explode('|', $data);
        # ios sign has not been update , only android (梦幻花园) is ok
        #$encryptStr = $get['encrypt_str'];
        #$this->checkTokenSign($params,$encryptStr);
        $pid = intval($params[8]);
        $consumerKey = $params[3];
        $nowTime = time();
        if(empty($consumerKey)){
            $this->output(Common_Expection_Spm::EXP_EMPTY_CONSUMERKEY, 'consumerkey is empty');
        }
        # call up data to rainbow
        $this->callUpd($data);
        # check params
        if ( !in_array($params[1], array('ios', 'android'))) {
            $this->output(Common_Expection_Spm::EXP_PLATFORM_ERROR, 'platform error');
        }
        if (empty($params[15]) and empty($params[18])) {
            $this->output(Common_Expection_Spm::EXP_EMPTY_DEVICEID, 'deviceid is empty');
        }
        $app = MobgiSpm_Service_TrackModel::getAppByConsumerKey($consumerKey);
        $appDetail = empty($app) ? array() : MobgiSpm_Service_TrackModel::getAppDetailById($app[0]['app_id']);
        try{
            $uid = ($params[1] == 'android') ? strtolower($params[18]) : strtoupper($params[18]);
            $udid = $params[15];
            if($uid == '00000000-0000-0000-0000-000000000000' || $uid == 'unknown'){
                $this->isActiveUdid($udid, $ip, $pid, $app, $appDetail, $consumerKey, $nowTime, $params);
            }else{
                $this->isActive($uid, $udid, $ip, $pid, $app, $appDetail, $consumerKey, $nowTime, $params);
            }
        } catch (Exception $e) {
            $this->error('reponse error! msg:' . $e->getMessage(),Common_Expection_Spm::EXP_RESPONSE_ERROR);
//            $this->output(Common_Expection_Spm::EXP_RESPONSE_ERROR, 'reponse error');
        }
    }

    /*
     * set udid as uid, check whether or not it is actived
     */
    private function isActiveUdid($udid, $ip, $pid, $app, $appDetail, $consumerKey, $nowTime, $params){
        if($pid == 0){
            $this->output(Common_Expection_Spm::EXP_EMPTY_PID, 'empty deviceId, empty pid');
        }
        $uid = $udid;
        $uidmd5 = md5($uid);
        $ua = count($params) > 20 ? $params[20] : Common::getUserAgent();
        $ipua = $this->getIpua($ip, $ua);
        $versionType = 10;
        # check active data
        $activeStatus = $this->checkActiveData($uidmd5, $consumerKey);
        if($activeStatus){
            $backFlowStatus = empty($appDetail) ? 0 : $appDetail['backflow_status'];
            if($backFlowStatus == 1){
                $this->backFlow($uid, $udid, $ip, $ua, $pid, $app, $consumerKey, $nowTime, $params, $ipua);
            }
            $this->output(Common_Expection_Spm::EXP_DEVICEID_ACTIVED, 'actived device');
        }
        if(!empty($app)){ # check click data while having app data
            if(empty($appDetail)){
                $ipuaClickPeriod = 24;
            }else{
                $ipuaClickPeriod = $appDetail['ipua_click_period'];
            }
            if(count($app) == 1){
                $gameId = $app[0]['appstore_id'];
            }else{
                $gameId = [];
                foreach($app as $value){
                    $gameId[] = $value['appstore_id'];
                }
            }
            # check ipua click data ( ipua )
            $clickData = $this->checkIpuaClickData($ipua, $gameId, $nowTime, $ipuaClickPeriod);
            if(!empty($clickData)){
                # here is a unsolved problem, when there are more than a click having the same ipua, only choose the lastest one, ignore others all the way, just record
                if($clickData['isactive'] == 0){
                    $this->addActiveData($uid, $udid, $ip, $ua, $pid, $consumerKey, $nowTime, $params, $ipua, $versionType, $clickData, 'ipua');
                }else{
                    $this->recordSameIpuaActiveData($uid, $udid, $ip, $ua, $pid, $nowTime, $ipua, $versionType, $clickData);
                }
            }
        }
        # appstore active
        $this->addAppstoreActiveData($uid, $udid, $ip, $ua, $pid, $app, $consumerKey, $nowTime, $params, $ipua, $versionType);
    }

    /*
     * set idfa or imei as uid, check whether or not it is actived
     */
    private function isActive($uid, $udid, $ip, $pid, $app, $appDetail, $consumerKey, $nowTime, $params){
        $uidmd5 = md5($uid);
        $ua = count($params) > 20 ? $params[20] : Common::getUserAgent();
        $ipua = $this->getIpua($ip, $ua);
        $versionType = 0;
        # check active data
        $activeStatus = $this->checkActiveData($uidmd5, $consumerKey);
        if($activeStatus){
            $backFlowStatus = empty($appDetail) ? 0 : $appDetail['backflow_status'];
            if($backFlowStatus == 1 && !empty($app)){
                $this->backFlow($uid, $udid, $ip, $ua, $pid, $app, $consumerKey, $nowTime, $params, $ipua);
            }
            $this->output(Common_Expection_Spm::EXP_DEVICEID_ACTIVED, 'actived device');
        }
        if(!empty($app)){ # check click data while having app data
            if(empty($appDetail)){
                $apiClickPeriod = 72;
                $ipuaClickPeriod = 24;
            }else{
                $apiClickPeriod = $appDetail['api_click_period'];
                $ipuaClickPeriod = $appDetail['ipua_click_period'];
            }
            if(count($app) == 1){
                $gameId = $app[0]['appstore_id'];
            }else{
                $gameId = [];
                foreach($app as $value){
                    $gameId[] = $value['appstore_id'];
                }
            }
            # check api click data ( device id)
            $clickData = $this->checkApiClickData($uidmd5, $gameId, $nowTime, $apiClickPeriod);
            if(!empty($clickData)){
                $this->addActiveData($uid, $udid, $ip, $ua, $pid, $consumerKey, $nowTime, $params, $ipua, $versionType, $clickData, 'api');
            }
            # check ipua click data ( ipua )
            $clickData = $this->checkIpuaClickData($ipua, $gameId, $nowTime, $ipuaClickPeriod);
            if(!empty($clickData)){
                # here is a unsolved problem, when there are more than a click having the same ipua, only choose the lastest one, ignore others all the way, just record
                if($clickData['isactive'] == 0){
                    $this->addActiveData($uid, $udid, $ip, $ua, $pid, $consumerKey, $nowTime, $params, $ipua, $versionType, $clickData, 'ipua');
                }else{
                    $this->recordSameIpuaActiveData($uid, $udid, $ip, $ua, $pid, $nowTime, $ipua, $versionType, $clickData);
                }
            }
        }
        # appstore active
        $this->addAppstoreActiveData($uid, $udid, $ip, $ua, $pid, $app, $consumerKey, $nowTime, $params, $ipua, $versionType);
    }

    /*
     * find out api click data
     */
    private function checkApiClickData($uidmd5, $gameId, $nowTime, $apiClickPeriod){
        $clickTime = $nowTime - $apiClickPeriod * 3600;
        $where['idfamd5'] = $uidmd5;
        $where['clicktime'] = array('>', $clickTime);
        $where['game_id'] = is_array($gameId) ? array('IN', $gameId) : $gameId;
        $clickData = MobgiSpm_Service_TrackModel::getClickData($where);
        return $clickData;
    }

    /*
     * find out ipua click data
     */
    private function checkIpuaClickData($ipua, $gameId, $nowTime, $ipuaClickPeriod){
        $clickTime = $nowTime - $ipuaClickPeriod * 3600;
        $where['ipua'] = $ipua;
        $where['clicktime'] = array('>', $clickTime);
        $where['game_id'] = is_array($gameId) ? array('IN', $gameId) : $gameId;
        $clickData = MobgiSpm_Service_TrackModel::getClickData($where);
        return $clickData;
    }

    /*
     * update click data
     */
    private function updateClickData($id, $uid, $uidmd5, $nowTime){
        $data = array(
            'isactive' => 1,
            'activetime' => $nowTime,
            'idfa' => $uid,
            'idfamd5' => $uidmd5,
        );
        MobgiSpm_Service_TrackModel::updateClickData($data, array('id' => $id));
    }

    /*
     * query uid active data, return the existing state
     */
    private function checkActiveData($uidmd5, $consumerKey){
        $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS,'spm');
        $key = $this->getActiveDataCacheKey($uidmd5, $consumerKey);
        $activeStatus = $cache->get($key);
        if ($activeStatus === false) {
            $result = MobgiSpm_Service_TrackModel::getActiveByIdConsumerKey($uidmd5, $consumerKey);
            $activeStatus = empty($result) ? 0 : 1;
            $cache->set($key, $activeStatus, Util_CacheKey::CACHE_KEY_EXPRIE_ONE_DAY);
        }
        return $activeStatus;
    }

    /*
     * set uid cache as active state
     */
    private function setActiveCache($uidmd5, $consumerKey){
        $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS,'spm');
        $key = $this->getActiveDataCacheKey($uidmd5, $consumerKey);
        $activeStatus = 1;
        $cache->set($key, $activeStatus, Util_CacheKey::CACHE_KEY_EXPRIE_ONE_DAY);
    }

    /*
     * add active data and decide if it need to be called back
     */
    private function addActiveData($uid, $udid, $ip, $ua, $pid, $consumerKey, $nowTime, $params, $ipua, $versionType, $clickData, $trackType){
        $acid = $clickData['activity_id'];
        $activity = MobgiSpm_Service_TrackModel::getActivityById($acid);
        $checkpoint = intval($activity['checkpoint']);
        if($params[2] != $checkpoint){ // 1001 : open; 1102 : register   1102 > 1001; when checkpoint is 1102 , ignore the activity checkpoint
            if($params[2] != 1102){
                $this->output(Common_Expection_Spm::EXP_CHECKPOINT_ERROR, 'checkpoint error');
            }
        }
        if($pid == 0 && $params[2] == 1102){
            $this->output(Common_Expection_Spm::EXP_CHECKPOINT_ERROR, 'checkpoint error');
        }
        if ( !in_array($activity['status'], array(2, 4))) { // 2 : 投放中; 4 : 接收不回调
            return false;
        }
        $platform = strtoupper($params[1]);
        $uidmd5 = md5($uid);
        $udidmd5 = md5($udid);
        $cid = $params[6];
        $activeData = array(
            'idfa' => $uid,
            'idfamd5' => $uidmd5,
            'ipua' => $ipua,
            'click_id' => $clickData['id'],
            'activity_id' => $acid,
            'ip' => $ip,
            'pid' => $pid,
            'consumer_key' => $consumerKey,
            'game_id' => $clickData['game_id'],
            'mober' => $clickData['mober'],
            'sub_channel' => $clickData['sub_channel'],
            'callback' => $clickData['callback'],
            'isactive' => 1,
            'iscallback' => 0,
            'clicktime' => $clickData['clicktime'],
            'activetime' => $nowTime,
            'callbacktime' => 0,
            'ua' => $ua,
            'udid' => $udid,
            'platform' => $platform,
            'version_type' => $versionType,
            'cid' => $cid,
        );
        if($versionType == 10 && $platform == 'IOS'){
            $activeData['mober'] = $activeData['mober'] . '_10_'  . $acid;
            $activeData['activity_id'] = -1;
        }
        # set active cache
        $this->setActiveCache($uidmd5, $consumerKey);
        $udidStatus = $this->checkUdidData($udidmd5, $consumerKey);
        if($udidStatus == 1){ # record the actived udid as unusual active, do not callback
            $activeData['iscallback'] = 2;
            $activeData['callbacktime'] = $nowTime;
        }else{
            $this->setUdidCache($udidmd5, $consumerKey);
        }
        if($activeData['mober'] == 'guangdiantong'){
            $activeData['callback'] = $this->formatCallbackGdt($activeData);
        }elseif($activeData['mober'] == 'gdtcgi'){
            $activeData['callback'] = $this->formatCallbackGdtPost($activeData);
        }elseif($activeData['mober'] == 'wechat'){
            $activeData['callback'] = $this->formatCallbackWeChat($activeData);
        }else{
            $activeData['callback'] = $this->formatCallbackCommon($activeData);
        }
        if($clickData['idfamd5'] == $clickData['ipua']){ # update ipua click data, prevent from being used again
            $this->updateClickData($clickData['id'], $uid, $uidmd5, $nowTime);
        }
        $activeId = MobgiSpm_Service_TrackModel::addActiveData($activeData, $uidmd5);
        if($udidStatus != 1) {
            $udidData = array(
                'udid' => $udid,
                'udidmd5' => $udidmd5,
                'consumer_key' => $consumerKey,
                'active_time' => $nowTime,
            );
            MobgiSpm_Service_TrackModel::addUdidData($udidData, $udidmd5);
        }
        # callback data
        if($activity['status'] == 2 && $versionType == 0 && ($trackType == 'api' || $activity['shortlink_status'] == 'ON')){
            if($activeData['callback'] != 'unknown'){
                $table = MobgiSpm_Service_TrackModel::getActiveTableName($uidmd5);
                $callbackData = $table . ',' . $activeId;
                $this->pushCallbackData($callbackData, $activeData['mober']);
            }
        }
        if($versionType == 0){ // uid active
            $this->output(Common_Expection_Spm::EXP_SUCCESS, 'active success');
        }else{ // udid active
            $this->output(Common_Expection_Spm::EXP_PARTIAL_SUCCESS, 'active partial success');
        }
    }

    /*
     * add appstore active
     */
    private function addAppstoreActiveData($uid, $udid, $ip, $ua, $pid, $app, $consumerKey, $nowTime, $params, $ipua, $versionType){
        $platform = strtoupper($params[1]);
        $uidmd5 = md5($uid);
        $udidmd5 = md5($udid);
        $cid = $params[6];
        $activeData = array(
            'idfa' => $uid,
            'idfamd5' => $uidmd5,
            'ipua' => $ipua,
            'click_id' => 0,
            'activity_id' => 0,
            'ip' => $ip,
            'pid' => $pid,
            'consumer_key' => $consumerKey,
            'game_id' => $app[0]['appstore_id'],
            'mober' => 'appstore',
            'sub_channel' => '',
            'callback' => '',
            'isactive' => 1,
            'iscallback' => 0,
            'clicktime' => $nowTime,
            'activetime' => $nowTime,
            'callbacktime' => 0,
            'ua' => $ua,
            'udid' => $udid,
            'platform' => $platform,
            'version_type' => $versionType,
            'cid' => $cid,
        );
        # set active cache
        $this->setActiveCache($uidmd5, $consumerKey);
        $udidStatus = $this->checkUdidData($udidmd5, $consumerKey);
        if($udidStatus == 1){ # record the actived udid as unusual active, do not callback
            $activeData['iscallback'] = 2;
            $activeData['callbacktime'] = $nowTime;
        }else{
            $this->setUdidCache($udidmd5, $consumerKey);
        }
        $activeId = MobgiSpm_Service_TrackModel::addActiveData($activeData, $uidmd5);
        if($udidStatus != 1) {
            $udidData = array(
                'udid' => $udid,
                'udidmd5' => $udidmd5,
                'consumer_key' => $consumerKey,
                'active_time' => $nowTime,
            );
            MobgiSpm_Service_TrackModel::addUdidData($udidData, $udidmd5);
        }
        if($versionType == 0){ // uid active
            $this->output(Common_Expection_Spm::EXP_SUCCESS, 'active success');
        }else{ // udid active
            $this->output(Common_Expection_Spm::EXP_PARTIAL_SUCCESS, 'active partial success');
        }

    }

    /*
     * query udid active data, return the existing state
     */
    private function checkUdidData($udidmd5, $consumerKey){
        $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS,'spm');
        $key = $this->getUdidDataCacheKey($udidmd5, $consumerKey);
        $activeStatus = $cache->get($key);
        if ($activeStatus === false) {
            $result = MobgiSpm_Service_TrackModel::getUdidByIdConsumerKey($udidmd5, $consumerKey);
            $activeStatus = empty($result) ? 0 : 1;
            $cache->set($key, $activeStatus, Util_CacheKey::CACHE_KEY_EXPRIE_ONE_DAY);
        }
        return $activeStatus;
    }

    /*
     * set udid cache as active state
     */
    private function setUdidCache($udidmd5, $consumerKey){
        $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS,'spm');
        $key = $this->getUdidDataCacheKey($udidmd5, $consumerKey);
        $activeStatus =  1;
        $cache->set($key, $activeStatus, Util_CacheKey::CACHE_KEY_EXPRIE_ONE_DAY);
    }

    /*
     * save back flow data
     */
    private function backFlow($uid, $udid, $ip, $ua, $pid, $app, $consumerKey, $nowTime, $params, $ipua){
        if($pid == 0){ // only record login
            return false;
        }
        $backFlowDate = date('Y-m-d',$nowTime);
        $uidmd5 = md5($uid);
        # check redis record consumer_key + idfa + Y-m-d
        if($this->checkBackFlowRecord($uidmd5, $consumerKey, $backFlowDate)){
            return false;
        }
        # record data , push in redis and try to find out click data
        $backFlowData = array(
            'idfa' => $uid,
            'idfamd5' => $uidmd5,
            'ip' => $ip,
            'ua' => $ua,
            'ipua' => $ipua,
            'pid' => $pid,
            'consumer_key' => $consumerKey,
            'game_id' => $app[0]['appstore_id'],
            'backflow_time' => $nowTime,
            'backflow_date' => $backFlowDate,
            'udid' => $udid,
            'udid' => strtoupper($params[1]),
        );
        //push into redis
        $this->pushBackFlowData($backFlowData);
        # set redis record consumer_key + idfa + Y-m-d
        $this->setBackFlowRecord($uidmd5, $consumerKey, $backFlowDate);
        $this->output(Common_Expection_Spm::EXP_BACKFLOW, 'backflow');
    }

    /*
     * query backflow data, return the existing state
     */
    private function checkBackFlowRecord($uidmd5, $consumerKey, $backFlowDate){
        $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS,'spm');
        $key = $this->getBackFlowDataCacheKey($uidmd5, $consumerKey, $backFlowDate);
        $backFlowStatus = $cache->get($key);
        if ($backFlowStatus === false) {
            $result = MobgiSpm_Service_TrackModel::getBackFlowData($uidmd5, $consumerKey, $backFlowDate);
            $backFlowStatus = empty($result) ? 0 : 1;
            $cache->set($key, $backFlowStatus, Util_CacheKey::CACHE_KEY_EXPRIE_ONE_DAY);
        }
        return $backFlowStatus;
    }

    /*
     * set backflow cache as active state
     */
    private function setBackFlowRecord($udidmd5, $consumerKey, $backFlowDate){
        $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS,'spm');
        $key = $this->getBackFlowDataCacheKey($udidmd5, $consumerKey, $backFlowDate);
        $backFlowStatus =  1;
        $cache->set($key, $backFlowStatus, Util_CacheKey::CACHE_KEY_EXPRIE_ONE_DAY);
    }

    /*
     * push tracking data in redis
     * @param $queueArr
     * @return mixed
     */
    private function pushBackFlowData($queueArr){
        $redis = Common::getQueue('spm');
        $write = $redis->push('RQ:AdTrackMonitorRedisQ_BACKFLOW', $queueArr);// push data
        if ($write <= 0) {
            $this->error('save the data error',Common_Expection_Spm::EXP_REDIS_ERROR);
        }
        return $write;
    }

    /*
     * push tracking data in redis
     * @param $string
     * @return mixed
     */
    private function pushCallbackData($string, $mober){
        $redis = Common::getQueue('spm');
        $queueConfig = Common::getConfig('spmConfig', 'QUEUES');
        if ( in_array($mober, $queueConfig['track']) ) {
            $redisKey = 'RQ:AdTrackCallbackRedisQ2_' . $mober;
        }else{
            $redisKey = 'RQ:AdTrackCallbackRedisQ2_COMMON';
        }
        $write = $redis->push($redisKey, $string);// push data
        if ($write <= 0) {
            $this->error('save the data error',Common_Expection_Spm::EXP_REDIS_ERROR);
        }
        return $write;
    }

    /*
     * save the active data which have the same ipua
     */
    private function recordSameIpuaActiveData($uid, $udid, $ip, $ua, $pid, $nowTime, $ipua, $versionType, $clickData){
        $queueArr = array(
            'uid' => $uid,
            'udid' => $udid,
            'pid' => $pid,
            'ip' => $ip,
            'ipua' => $ipua,
            'click_id' => $clickData['id'],
            'activetime' => $nowTime,
            'ua' => $ua,
            'version_type' => $versionType,
        );
        $redis = Common::getQueue('spm');
        $write = $redis->push('RQ:AdTrackMonitorRedisQ_SAMEIPUAACTIVE', $queueArr);// push data
        if ($write <= 0) {
            $this->error('save the data error',Common_Expection_Spm::EXP_REDIS_ERROR);
        }
        return $write;
    }

    /*
     * format gdt callback (GET)
     */
    private function formatCallbackGdt($activeData){
        $callback = $activeData['callback'];
        $callbackParse = parse_url($callback);
        parse_str($callbackParse['query'], $output);
        $advertiser_id = $output['advertiser_id'];
        $sign = urlencode($this->getGdtSign($activeData['activetime'], $activeData['ip'], $activeData['idfamd5'], $activeData['game_id'], $advertiser_id, $activeData['mober']));
        $callback = str_replace('{data}', $sign, $callback);
        return $callback;
    }

    /*
     * format gdt callback (POST)
     */
    private function formatCallbackGdtPost($activeData){
        $callback = $activeData['callback'];
        $callbackParse = parse_url($callback);
        parse_str($callbackParse['query'], $params);
        $encstr = $this->getGdtEncstr($params, $activeData['activetime'], $activeData['ip']);
        $macros = array(
            'time' => $activeData['activetime'],
            'ip' => $activeData['ip'],
            'encstr' => $encstr
        );
        foreach($macros as $macros_key=>$macros_value){
            $callback = str_replace('{'.$macros_key.'}', $macros_value, $callback);
        }
        return $callback;
    }

    /*
     * format wechat callback (GET)
     */
    private function formatCallbackWeChat($activeData){
        $callback = $activeData['callback'];
        $callbackParse = parse_url($callback);
        parse_str($callbackParse['query'], $params);
        $sign = urlencode($this->getWeChatSign($params, $activeData['activetime'], $activeData['ip'], $activeData['mober']));
        $callback = str_replace('{data}', $sign, $callback);
        return $callback;
    }

    /*
     * get gdt sign (GET)
     */
    private function getGdtSign($time, $ip, $muid, $appId,$advertiserId, $mober){
        //muid=$muid&conv_time=$conv_time&client_ip=$client_ip
        //muid=0f074dc8e1f0547310e729032ac0730b&conv_time=1422263664&client_ip=10.11.12.13
        $keyArr = MobgiSpm_Service_TrackModel::getGdtConfigByAppAdvertiser($appId, $advertiserId);
        if(empty($keyArr)){
            unset($keyArr);
            $keyArr['sign_key'] = 'unknown';
            $keyArr['encrypt_key'] = 'unknown';
        }
        $host = $this->getGdtHost($mober);
        $queryStr = 'muid='.$muid.'&conv_time='.$time.'&client_ip='.$ip;
        $signature = $host.'/conv/app/'.$appId.'/conv?'.'muid='.$muid.'&conv_time='.$time.'&client_ip='.$ip;
        $signKey = $keyArr['sign_key'];
        $signature = $signKey.'&GET&'.urlencode($signature);
        $signature = md5($signature);
        $base_data = $queryStr.'&sign='.urlencode($signature);
        $encryptKey = $keyArr['encrypt_key'];
        $data = $this->phpxor($base_data, $encryptKey);
        return base64_encode($data);
    }

    /*
     * get gdt encstr (POST)
     */
    private function getGdtEncstr($params, $time, $ip){
        $advertiserId=$params['advertiser_id'];
        $appId=$params['appid'];
        $keyArr = MobgiSpm_Service_TrackModel::getGdtConfigByAppAdvertiser($appId, $advertiserId);
        if(empty($keyArr)){
            unset($keyArr);
            $keyArr['sign_key'] = 'unknown';
        }
        $signKey = $keyArr['sign_key'];
        $signature = 'app_type='.$params['app_type'].'&click_id='.$params['click_id'].'&client_ip='.$ip.'&conv_time='.$time.'&muid='.$params['muid'].'&sign_key='.$signKey;
        $encstr = md5($signature);
        return $encstr;
    }

    /*
     * get wechat sign (GET)
     */
    private function getWeChatSign($params, $time, $ip, $mober){
        $advertiserId=$params['advertiser_id'];
        $appId=$params['appid'];
        $keyArr = MobgiSpm_Service_TrackModel::getGdtConfigByAppAdvertiser($appId, $advertiserId);
        if(empty($keyArr)){
            unset($keyArr);
            $keyArr['sign_key'] = 'unknown';
            $keyArr['encrypt_key'] = 'unknown';
        }
        $host = $this->getGdtHost($mober);
        $queryStr = 'click_id='.$params['click_id'].'&muid='.$params['muid'].'&conv_time='.$time.'&client_ip='.$ip;
        $signature = $host.'/conv/app/'.$appId.'/conv?'.$queryStr;
        $signKey = $keyArr['sign_key'];
        $signature = $signKey.'&GET&'.urlencode($signature);
        $signature = md5($signature);
        $base_data = $queryStr.'&sign='.urlencode($signature);
        $encryptKey = $keyArr['encrypt_key'];
        $data = $this->phpxor($base_data, $encryptKey);
        return base64_encode($data);
    }

    private function phpxor($str, $tem){
        $destr = '';
        for($i = 0; $i<strlen($str); $i++){
            $j = $i % strlen($tem);
            $destr .= $str{$i} ^ $tem{$j};
        }
        return $destr;
    }

    private function formatCallbackCommon($activeData){
        $callback = $activeData['callback'];
        if(strpos($callback, '{active_uid}') !== false){
            $callback = str_replace('{active_uid}', $activeData['idfa'], $callback);
        }
        if(strpos($callback, '{active_muid}') !== false){
            $callback = str_replace('{active_muid}', $activeData['idfamd5'], $callback);
        }
        if(strpos($callback, '{active_ip}') !== false){
            $callback = str_replace('{active_ip}', $activeData['ip'], $callback);
        }
        if(strpos($callback, '{active_time}') !== false){
            $callback = str_replace('{active_time}', $activeData['activetime'], $callback);
        }
        return $callback;
    }

    /*
     * check the token sign
     * @param $params
     * @param $encryptStr
     */
    private function checkTokenSign($params, $encryptStr){
        #  md5("token" + consumerkey + client_time + udid)
        $token = md5('token' . $params[3] . $params[9] . $params[15]);
        if($encryptStr != $token){
            $this->error('sign error',Common_Expection_Spm::EXP_SIGN_ERROR);
        }
    }

    /**
     * send dlog to rainbow
     * @param $data
     * @return bool
     */
    public function callUpd($data) {
        $dlogConfig = Common::getConfig('spmConfig', 'DLOG');
        $dlog_host = $dlogConfig['host'];
        $dlog_port = $dlogConfig['port'];
        $dlog_protocal = $data . "\n";
        try {
            $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            $len = strlen($dlog_protocal);
            $send_len = socket_sendto($sock, $dlog_protocal, $len, MSG_EOF, $dlog_host, $dlog_port);
            socket_close($sock);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getActiveDataCacheKey($uidmd5, $consumerKey) {
        return Util_CacheKey::SPM_ACTIVE_UIDMD5_CONSUMERKEY . $uidmd5 . '_' . $consumerKey;
    }

    private function getUdidDataCacheKey($udidmd5, $consumerKey) {
        return Util_CacheKey::SPM_ACTIVE_UDIDMD5_CONSUMERKEY . $udidmd5 . '_' . $consumerKey;
    }

    private function getBackFlowDataCacheKey($udidmd5, $consumerKey, $backFlowData) {
        return Util_CacheKey::SPM_BACKFLOW_UDIDMD5_CONSUMERKEY_DATE . $udidmd5 . '_' . $consumerKey . '_' . $backFlowData;
    }
}