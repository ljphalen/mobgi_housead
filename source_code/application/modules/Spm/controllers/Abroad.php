<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/9
 * Time: 20:49
 */

class AbroadController extends Spm_BaseController{

    public function indexAction()
    {
        $this->output(Common_Expection_Spm::EXP_FAILED, 'nothing');
    }

    /*
     * active api for android active data
     */
    public function activeAction() {
        $get = $_GET;
        $data = rawurldecode($get['data']);
        $ip = Common::getClientIP();
        $data .= '|'.$ip;
        $params = explode('|', $data);
        # yulongzaitian
        $encryptStr = $get['encrypt_str'];
        $this->checkTokenSign($params,$encryptStr);
        $pid = intval($params[8]);
        $consumerKey = $params[3];
        $nowTime = time();
        if(empty($consumerKey)){
            $this->output(Common_Expection_Spm::EXP_EMPTY_CONSUMERKEY, 'consumerkey is empty');
        }
        # check params
        # only support android abroad
        if ( $params[1] != 'android') {
            $this->output(Common_Expection_Spm::EXP_PLATFORM_ERROR, 'platform error');
        }
        # decrypt imei
        $params[18] = base64_decode($params[18]);
        $data = implode('|',$params);
        # call up data to rainbow
        $this->callUpd($data);
        if (empty($params[16]) and empty($params[17])) { //adversiting_id 、 android_id
            $this->output(Common_Expection_Spm::EXP_EMPTY_DEVICEID, 'deviceid is empty');
        }
        $app = MobgiSpm_Service_TrackModel::getAppByConsumerKey($consumerKey);
        try{
            $adid = empty($params[16]) ? $params[17] : $params[16];
            $imei = $params[18];
            $this->isActive($adid, $imei, $ip, $pid, $app, $consumerKey, $nowTime, $params);
        } catch (Exception $e) {
            $this->error('reponse error! msg:' . $e->getMessage(),Common_Expection_Spm::EXP_RESPONSE_ERROR);
//            $this->output(Common_Expection_Spm::EXP_RESPONSE_ERROR, 'reponse error');
        }
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

    /*
     * set idfa or imei as uid, check whether or not it is actived
     */
    private function isActive($adid, $imei, $ip, $pid, $app, $consumerKey, $nowTime, $params){
        $adidmd5 = md5($adid);
        $ua = count($params) > 20 ? $params[20] : Common::getUserAgent();
        $ipua = $this->getIpua($ip, $ua);
        # check active data
        $activeStatus = $this->checkAdidData($adidmd5, $consumerKey);
        if($activeStatus){
            $this->output(Common_Expection_Spm::EXP_DEVICEID_ACTIVED, 'actived device');
        }
        # appstore active
        $this->addAppstoreActiveData($adid, $imei, $ip, $ua, $pid, $app, $consumerKey, $nowTime, $params, $ipua);
    }

    /*
     * add appstore active
     */
    private function addAppstoreActiveData($adid, $imei, $ip, $ua, $pid, $app, $consumerKey, $nowTime, $params, $ipua){
        $platform = strtoupper($params[1]);
        $adidmd5 = md5($adid);
        $udid = $params[15];
        $androidId  = $params[17];
        $gameId = empty($app) ? 0 : $app[0]['appstore_id'];
        $activeData = array(
            'adid' => $adid,
            'adidmd5' => $adidmd5,
            'ipua' => $ipua,
            'click_id' => 0,
            'activity_id' => 0,
            'ip' => $ip,
            'pid' => $pid,
            'consumer_key' => $consumerKey,
            'game_id' => $gameId,
            'mober' => 'appstore',
            'sub_channel' => '',
            'isactive' => 1,
            'click_time' => $nowTime,
            'active_time' => $nowTime,
            'ua' => $ua,
            'imei' => $imei,
            'android_id' => $androidId,
            'udid' => $udid,
            'platform' => $platform,
        );
        # set adid cache
        $this->setAdidCache($adidmd5, $consumerKey);
        $activeId = MobgiSpm_Service_TrackModel::addAdidData($activeData, $adidmd5);
        $this->output(Common_Expection_Spm::EXP_SUCCESS, 'active success');

    }

    /*
     * query adid active data, return the existing state
     */
    private function checkAdidData($adidmd5, $consumerKey){
        $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS,'spm');
        $key = $this->getAdidDataCacheKey($adidmd5, $consumerKey);
        $activeStatus = $cache->get($key);
        if ($activeStatus === false) {
            $result = MobgiSpm_Service_TrackModel::getAdidByIdConsumerKey($adidmd5, $consumerKey);
            $activeStatus = empty($result) ? 0 : 1;
            $cache->set($key, $activeStatus, Util_CacheKey::CACHE_KEY_EXPRIE_ONE_DAY);
        }
        return $activeStatus;
    }

    /*
     * set adid cache as active state
     */
    private function setAdidCache($adidmd5, $consumerKey){
        $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS,'spm');
        $key = $this->getAdidDataCacheKey($adidmd5, $consumerKey);
        $activeStatus = 1;
        $cache->set($key, $activeStatus, Util_CacheKey::CACHE_KEY_EXPRIE_ONE_DAY);
    }

    /*
     * track api for ios active data
     */
    public function getInstallReportAction(){
        error_reporting(E_ALL);
        $operator = 'push_api_get_install_report';
        $platform = 'ios';
        $queryArr = $_GET;
        $appsflyerAppid = $queryArr['app_id'];
        if($queryArr['platform'] == $platform ){
            $idfa = $queryArr['idfa'];
            if(empty($idfa)){
                #把idfa为空的数据写入到队列
                $this->pushEmptyData();
                $this->output(Common_Expection_Spm::EXP_EMPTY_DEVICEID, 'empty idfa');
            }
        }else{
            $this->output(Common_Expection_Spm::EXP_PLATFORM_ERROR, 'only support ios');
        }

        $channelNo = $this->chooseChannelNo($queryArr['media_source'], $queryArr['agency']);
        $appInfo = $this->getAppInfoByAppsflyerId($appsflyerAppid);
        if(empty($appInfo)){
            $this->output(Common_Expection_Spm::EXP_EMTPY_CONFIG, 'no appsflyer_appid config. appsflyer_appid: '.$appsflyerAppid);
        }
        $appId = $appInfo['app_id'];

        //如果不存在渠道编号，则自动新增渠道编号
        $channelId = $this->getChannelId($channelNo);

        //如果不存在活动组名，则自动新增活动组名．活动组名规则：渠道编号#游戏名称
        $activitGroupyName = $channelNo . '#' . $appInfo['app_name'];
        $activityGroupId = $this->getActivityGroupId($activitGroupyName, $appId, $operator);

        //如果不存在活动名，则自动新增活动名．活动名规则：渠道编号#游戏名称#活动名
        $campaign = empty($queryArr['campaign']) ? 'null' : $queryArr['campaign'];
        $activityName = $channelNo . '#' . $appInfo['app_name'] . '#' . $campaign;
        $activityId = $this->getActivityId($activityName, $appId, $channelId, $activityGroupId, $platform, $operator);

        $idfamd5 = md5($idfa);
        $consumerKey = $appInfo['consumer_key'];
        $record = MobgiSpm_Service_TrackModel::getActiveByIdConsumerKey($idfamd5, $consumerKey);
        if($record){
            if($record['mober'] == 'appstore'){ // 更新自然量分表的值 并 把需要更新infobright的数据写入队列
                MobgiSpm_Service_TrackModel::updateActiveData($idfamd5, array('mober'=>$channelNo, 'activity_id'=>$activityId), array('id' => $record['id']));
                $redisQueneKey = 'AdAppsflyerUpdInfobrightRedisQ';
                $redisValue = $activityId . '###' . $channelNo . '###' . $consumerKey . '###' . $idfa;
                $this->pushDataByKey($redisValue, $redisQueneKey);
                $this->output(Common_Expection_Spm::EXP_SUCCESS,  'update appstore record. idfa:'.$idfa);
            }else if($record['mober'] == $channelNo){ //已经激活的数据写入已激活队列
                #已经激活的数据写入已激活队列
                $redisQueneKey = 'AdAppsflyerActivatedRedisQ';
                $redisValue = $idfa . '###' .  $consumerKey;
                $this->pushDataByKey($redisValue, $redisQueneKey);
                $this->output(Common_Expection_Spm::EXP_DEVICEID_ACTIVED,  'found activated record. idfa:'.$idfa);
            }else{ //找到其它活动激活的数据写其它活动激活队列

                $redisQueneKey = 'AdAppsflyerOtherActivatedRedisQ';
                $redisValue = $idfa . '###' . $consumerKey . '###' . $record['mober'] . '###' . $channelNo;
                $this->pushDataByKey($redisValue, $redisQueneKey);
                $this->output(Common_Expection_Spm::EXP_DEVICEID_ACTIVED,  'found other activity record. idfa:' .$idfa. ' mober:' .$record['mober']);
            }
        }else{ //找不到激活记录的idfa写入没激活队列

            $redisQueneKey = 'AdAppsflyerNoActiveRedisQ';
            $redisValue = $idfa . '###' . $consumerKey . '###' . $activityId . '###' . $appInfo['appstore_id'] . '###' . $channelNo;
            $this->pushDataByKey($redisValue, $redisQueneKey);
            $this->output(Common_Expection_Spm::EXP_FAILED,  'can not find active data. idfa:' . $idfa);
        }
    }

    /*
     * track api for android active data
     */
    public function getInstallReportAndroidAction(){
        error_reporting(E_ALL);
        $operator = 'push_api_get_install_report_android';
        $platform = 'android';
        $queryArr = $_GET;
        $appsflyerAppid = $queryArr['app_id'];
        if($queryArr['platform'] == $platform){
            //安卓使用 advertising_id 做为唯一标识．
            $imei = $queryArr['imei'];
            $androidId = $queryArr['android_id'];
            $adid = $queryArr['advertising_id'];
            if(empty($adid)){
                #把advertising_id为空的数据写入到队列
                $this->pushEmptyData();
                $this->output(Common_Expection_Spm::EXP_EMPTY_DEVICEID, 'empty advertising_id');
            }
        }else{
            $this->output(Common_Expection_Spm::EXP_PLATFORM_ERROR, 'only support android');
        }

        $channelNo = $this->chooseChannelNo($queryArr['media_source'], $queryArr['agency']);
        $appInfo = $this->getAppInfoByAppsflyerId($appsflyerAppid);
        if(empty($appInfo)){
            $this->output(Common_Expection_Spm::EXP_EMTPY_CONFIG, 'no appsflyer_appid config. appsflyer_appid: '.$appsflyerAppid);
        }
        $appId = $appInfo['app_id'];

        //如果不存在渠道编号，则自动新增渠道编号
        $channelId = $this->getChannelId($channelNo);

        //如果不存在活动组名，则自动新增活动组名．活动组名规则：渠道编号#游戏名称
        $activitGroupyName = $channelNo . '#' . $appInfo['app_name'];
        $activityGroupId = $this->getActivityGroupId($activitGroupyName, $appId, $operator);

        //如果不存在活动名，则自动新增活动名．活动名规则：渠道编号#游戏名称#活动名
        $campaign = empty($queryArr['campaign']) ? 'null' : $queryArr['campaign'];
        $activityName = $channelNo . '#' . $appInfo['app_name'] . '#' . $campaign;
        $activityId = $this->getActivityId($activityName, $appId, $channelId, $activityGroupId, $platform, $operator);

        $adidmd5 = md5($adid);
        $consumerKey = $appInfo['consumer_key'];
        $record = MobgiSpm_Service_TrackModel::getAdidByIdConsumerKey($adidmd5, $consumerKey);
        if($record){
            if($record['mober'] == 'appstore'){ // 更新自然量分表的值 并 把需要更新infobright的数据写入队列
                MobgiSpm_Service_TrackModel::updateAdidData($adidmd5, array('mober'=>$channelNo, 'activity_id'=>$activityId), array('id' => $record['id']));
                $redisQueneKey = 'AdAbroadAndroidUpdInfobrightRedisQ';
                $redisValue = $activityId . '###' . $channelNo . '###' . $consumerKey . '###' . $adid;
                $this->pushDataByKey($redisValue, $redisQueneKey);
                $this->output(Common_Expection_Spm::EXP_SUCCESS,  'update appstore record. adid:'.$adid);
            }else if($record['mober'] == $channelNo){ //已经激活的数据写入已激活队列
                $redisQueneKey = 'AdAbroadAndroidActivatedRedisQ';
                $redisValue = $adid . '###' . $consumerKey;
                $this->pushDataByKey($redisValue, $redisQueneKey);
                $this->output(Common_Expection_Spm::EXP_DEVICEID_ACTIVED,  'found activated record. adid:'.$adid);
            }else{ //找到其它活动激活的数据写其它活动激活队列
                $redisQueneKey = 'AdAbroadAndroidOtherActivatedRedisQ';
                $redisValue = $adid . '###' . $consumerKey . '###' . $record['mober'] . '###' . $channelNo;
                $this->pushDataByKey($redisValue, $redisQueneKey);
                $this->output(Common_Expection_Spm::EXP_DEVICEID_ACTIVED,  'found other activity record. adid:'.$adid. ' mober:' .$record['mober']);
            }
        }else{
            #找不到激活记录的adid写入没激活队列
            $redisQueneKey = 'AdAbroadAndroidNoActiveRedisQ';
            $redisValue = $adid . '###' . $consumerKey . '###' . $activityId . '###' . $appInfo['appstore_id'] . '###' . $channelNo;
            $this->pushDataByKey($redisValue, $redisQueneKey);
            $this->output(Common_Expection_Spm::EXP_FAILED,  'can not find active data. adid:'.$adid);
        }
    }

    /**
     * 选择渠道号
     * @param $mediaSource
     * @param $agency
     * @return string
     */
    private function chooseChannelNo($mediaSource, $agency){
        if(empty($mediaSource)){
            $channelNo = $agency;
        }else{
            $channelNo = $mediaSource;
        }
        if(empty($channelNo)){
            $channelNo='empty_channel';
        }
        return $channelNo;
    }

    /**
     * 获取渠道id
     * @param $channelNo
     * @return string
     */
    private function getChannelId($channelNo){
        $channelInfo = MobgiSpm_Service_ChannelModel::getChannelByNo($channelNo);
        if(empty($channelInfo)){
            $data = array('channel_no'=>$channelNo, 'channel_name'=>$channelNo);
            $channelId = MobgiSpm_Service_ChannelModel::addAbroadChannel($data);
        }else{
            $channelId = $channelInfo['id'];
        }
        return $channelId;
    }

    /**
     * 获取活动组id
     * @param $activitGroupyName
     * @param $appId
     * @param $operator
     * @return string
     */
    private function getActivityGroupId($activitGroupyName, $appId, $operator){
        $activitGroupyInfo = MobgiSpm_Service_DeliveryModel::getDeliveryActivityGroupByName($activitGroupyName);
        if(empty($activitGroupyInfo)){
            $data = array('name'=>$activitGroupyName,'app_id'=>$appId,'operator'=>$operator);
            $activityGroupId = MobgiSpm_Service_DeliveryModel::addActivityGroup($data);
        }else{
            $activityGroupId = $activitGroupyInfo['id'];
        }
        return $activityGroupId;
    }

    /**
     * 获取活动id
     * @param $activityName
     * @param $appId
     * @param $channelId
     * @param $activityGroupId
     * @param $platform
     * @param $operator
     * @return string
     */
    private function getActivityId($activityName, $appId, $channelId, $activityGroupId, $platform, $operator){
        $activityInfo = MobgiSpm_Service_DeliveryModel::getDeliveryActivityByParams(array('name'=>$activityName,'app_id'=>$appId,'channel_id'=>$channelId));
        if(empty($activityInfo)){
            $data = array(
                'name'=>$activityName,
                'channel_id'=>$channelId,
                'app_id'=>$appId,
                'track_type'=>'api',
                'data_platform'=>'appsflyer',
                'group_id'=>$activityGroupId,
                'platform'=>$platform,
                'operator'=>$operator
            );
            $activityId = MobgiSpm_Service_DeliveryModel::addActivity($data);
        }else{
            $activityId = $activityInfo['id'];
        }
        return $activityId;
    }

    /**
     * 根据AP的应用id获取对应spm应用配置
     * @param $appsflyerId
     * @return array|mixed
     */
    private function getAppInfoByAppsflyerId($appsflyerId){
        $config = MobgiSpm_Service_DeliveryModel::getAppsflyerConfigById($appsflyerId);
        if($config){
            $appInfo = MobgiSpm_Service_DeliveryModel::getAppById($config['app_id']);
            return $appInfo;
        }else{
            return array();
        }
    }

    /**
     * push af data in redis
     * @param $value
     * @param $redisKey
     * @return mixed
     */
    private function pushDataByKey($value, $redisKey){
        $redis = Common::getQueue('spm');
        $redisKey = 'RQ:' . $redisKey;
        $write = $redis->push($redisKey, $value);//push into redis list
        if ($write <= 0) {
            $this->error('save the data error',Common_Expection_Spm::EXP_REDIS_ERROR);
        }
        return $write;
    }

    /*
     * push tracking data in redis
     * @param $queueArr
     * @return mixed
     */
    private function pushEmptyData(){
        $redis = Common::getQueue('spm');
        $str = $_SERVER['REQUEST_URI'];
        $write = $redis->push('RQ:AdAppsflyerPushApiEmptyImeiRedisQ', $str);//push into redis list
        if ($write <= 0) {
            $this->error('save the data error',Common_Expection_Spm::EXP_REDIS_ERROR);
        }
        return $write;
    }

    private function getAdidDataCacheKey($uidmd5, $consumerKey) {
        return Util_CacheKey::SPM_ACTIVE_ADIDMD5_CONSUMERKEY . $uidmd5 . $consumerKey;
    }


}