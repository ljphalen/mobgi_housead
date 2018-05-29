<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/21
 * Time: 18:02
 */
class Common_Expection_Spm {

    # common code
    const EXP_SUCCESS=0;
    const EXP_PARTIAL_SUCCESS=1;
    const EXP_FAILED=2;
    const EXP_PARAM_ERROR=3;
    const EXP_SIGN_ERROR = 4;
    const EXP_REDIS_ERROR = 5;
    # system code
    const EXP_RESPONSE_ERROR = -1;
    # spm code (track)
    const EXP_UNKOWN_SHORT_KEY = 1001;
    const EXP_UNKOWN_ACID = 1002;
    const EXP_ACTIVITY_NOT_EXIST = 1003;
    const EXP_ACTIVITY_NOT_DELIVERY = 1004;
    const EXP_APP_NOT_EXIST = 1005;
    const EXP_EMPTY_LOCATION_URL = 1006;
    # spm code (active)
    const EXP_EMPTY_CONSUMERKEY = 1101;
    const EXP_PLATFORM_ERROR = 1102;
    const EXP_EMPTY_DEVICEID = 1103;
    const EXP_EMPTY_PID = 1104;
    const EXP_CHECKPOINT_ERROR = 1105;
    const EXP_DEVICEID_ACTIVED = 1106;
    const EXP_UDID_ACTIVED = 1107;
    const EXP_BACKFLOW = 1108;
    const EXP_DEVICEID_UNDEFINE = 1109;
    const EXP_EMTPY_CONFIG = 1110;

    public static $mReportCodeDesc = array(
        self::EXP_SUCCESS => 'success',
        self::EXP_PARTIAL_SUCCESS => 'partial_success',
        self::EXP_FAILED => 'failed',
        self::EXP_PARAM_ERROR => 'param_error',
        self::EXP_SIGN_ERROR => 'sign_error',
        self::EXP_REDIS_ERROR => 'redis_error',
        self::EXP_RESPONSE_ERROR => 'reponse_error',
        self::EXP_UNKOWN_SHORT_KEY => 'unknown_short_key',
        self::EXP_UNKOWN_ACID => 'unknown_acid',
        self::EXP_ACTIVITY_NOT_EXIST => 'activity_not_exist',
        self::EXP_ACTIVITY_NOT_DELIVERY => 'activity_not_delivery',
        self::EXP_APP_NOT_EXIST => 'app_not_exist',
        self::EXP_EMPTY_LOCATION_URL => 'empty_location_url',
        self::EXP_EMPTY_CONSUMERKEY => 'empty_consumerkey',
        self::EXP_PLATFORM_ERROR => 'platform_error',
        self::EXP_EMPTY_DEVICEID => 'empty_deviceid',
        self::EXP_EMPTY_PID => 'empty_pid',
        self::EXP_CHECKPOINT_ERROR => 'checkpoint_error',
        self::EXP_DEVICEID_ACTIVED => 'deviceid_actived',
        self::EXP_UDID_ACTIVED => 'udid_actived',
        self::EXP_BACKFLOW => 'backflow',
        self::EXP_DEVICEID_UNDEFINE => 'deviceid_undefine',
        self::EXP_EMTPY_CONFIG => 'empty_config',
    );

    public static function getCodeDesc($code){
        return self::$mReportCodeDesc[$code];
    }

}