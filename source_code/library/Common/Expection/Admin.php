<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/6
 * Time: 14:16
 */
class Common_Expection_Admin {

    # common code
    const EXP_SUCCESS=0;
    const EXP_PARTIAL_SUCCESS=1;
    const EXP_FAILED=2;
    const EXP_PARAM_ERROR=3;
    const EXP_SIGN_ERROR = 4;
    const EXP_REDIS_ERROR = 5;
    # system code
    const EXP_RESPONSE_ERROR = -1;
    # login code
    const EXP_SESSION_EXPIRED = 1001;

    public static $mReportCodeDesc = array(
        self::EXP_SUCCESS => 'success',
        self::EXP_PARTIAL_SUCCESS => 'partial_success',
        self::EXP_FAILED => 'failed',
        self::EXP_PARAM_ERROR => 'param_error',
        self::EXP_SIGN_ERROR => 'sign_error',
        self::EXP_REDIS_ERROR => 'redis_error',
        self::EXP_RESPONSE_ERROR => 'reponse_error',
        self::EXP_SESSION_EXPIRED => 'session_expired',
    );

    public static function getCodeDesc($code){
        return self::$mReportCodeDesc[$code];
    }

}