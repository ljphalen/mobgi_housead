<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Cache key constants
 *
 * @package utility
 */
class Util_ErrorCode {
    const CONFIG_SUCCESS = 0;
    const PARAMS_CHECK = 3001;
    const APP_STATE_CHECK = 3002;
    const POS_STATE_CHECK = 3003;
    const FITER_CONFIG = 3004;
    const FLOW_CONFIG_EMPTY = 3005;
    const BASEINFO_CONFIG_EMPTY = 3006;
    const CONFIG_EMPTY = 3007;
    const DSP_FLOW_CONFIG_EMPTY = 3008;
    const DSP_RETURN_DATA_EMPTY = 3009;
    const DSP_STRATEGY_CONFIG_EMPTY = 3010;
    const DSP_ACCOUNT_EMPTY = 3011;
    const UNIT_CONFIG_EMPTY = 3012;
    const ORIGINALITY_LIST_EMPTY = 3013;
    const AD_ID_EMPTY = 3014;
    const DSP_FITER_CONFIG = 3015;
    const DSP_AD_INFO_EMPTY = 3016;
    const DSP_ACCOUNT_LIMIT = 3017;
    const DSP_UNIT_LIMIT = 3018;
    const DSP_DATE_FITER = 3019;
    const DSP_FREQUENCY_LIMIT = 3020;
    const NO_SUCCESSFUL_BIDDING = 3021;
    const DIRECT_CONFIT_EMPTY = 3022;
	const DSP_ADINFO_AMOUNT_LIMIT = 3023;
    public static  $mReportCodeDesc = array(
            self::PARAMS_CHECK => 'paramsCheckError',
            self::CONFIG_EMPTY => 'configEmpty',
            self::FITER_CONFIG => 'fiterConfig',
            self::APP_STATE_CHECK => 'appStateCheck',
            self::POS_STATE_CHECK=>'posStateCheck',
            self::CONFIG_SUCCESS => 'configSuccess',
            self::NO_SUCCESSFUL_BIDDING=>'bidInfoEmpty',
            self::DSP_FLOW_CONFIG_EMPTY=>'dspFlowConfigEmpty',
            self::DSP_STRATEGY_CONFIG_EMPTY=>'dspStrategyConfigEmpty',
            self::DSP_ACCOUNT_EMPTY =>'dspAccountEmpty',
            self::UNIT_CONFIG_EMPTY=>'unitConfigEmpty',
            self::ORIGINALITY_LIST_EMPTY=>'originalityListEmpty',
            self::AD_ID_EMPTY => 'adIdEmpty',
            self::DSP_FITER_CONFIG=>'dspFiterConfig',
            self::DSP_AD_INFO_EMPTY=>'dspAdinfoEmpty',
            self::DSP_ACCOUNT_LIMIT=>'dspAcountLimit',
            self::DSP_UNIT_LIMIT=>'dspUnitLimit',
            self::DSP_DATE_FITER=>'dspDateLimit',
            self::DSP_FREQUENCY_LIMIT=>'dspFrequencyLimit',
            self::FLOW_CONFIG_EMPTY =>'flowConfigEmpty',
            self::BASEINFO_CONFIG_EMPTY=>'baseInfoConfigEmpty',
			self::DSP_RETURN_DATA_EMPTY=>'dspReturnDataEmpty'
    );


}
