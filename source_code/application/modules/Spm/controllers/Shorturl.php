<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/9
 * Time: 20:48
 */

class ShorturlController extends Spm_BaseController{

    private $formatKeys = [
        'short_key' => ['short_key', ['maxLen', 7], ''],
        'idfa' => ['idfa', ['toUpper'], ''],
        'imei' => ['imei', ['toLower'], ''],
        'midfa' => ['midfa', ['toLower'], ''],
        'muid' => ['muid', ['toLower'], ''],
        'sub_channel' => ['sub_channel', ['normal'], ''],
        'redirect' => ['redirect', ['normal'], 'true'],
        'ip' => ['ip', ['normal'], '']
    ];

    /*
     * short url tracking api
     */
    public function commonAction(){
        header("Content-type: text/html; charset=utf-8");
        $params = $_GET;
        $params = $this->format($params,$this->formatKeys);
        $shortKey = $params['short_key'];
        if(empty($shortKey)){
            $this->output(Common_Expection_Spm::EXP_UNKOWN_SHORT_KEY,'unknown short key');
        }
        if($shortKey == 'favicon'){ // filter request icon
            $this->output(Common_Expection_Spm::EXP_UNKOWN_SHORT_KEY,'unknown short key');
        }
        # find activity by short_key
        $activity = MobgiSpm_Service_TrackModel::getActivityByShortKey($shortKey);
        if(empty($activity)){
            $this->error('tracking is not exist! short_key:'.$shortKey,Common_Expection_Spm::EXP_ACTIVITY_NOT_EXIST);
//            $this->output(Common_Expection_Spm::EXP_ACTIVITY_NOT_EXIST,'tracking is not exist');
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
            $muid = $ipua;
            $uid = $ipua;
        }
        return array($uid, $muid);
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



}