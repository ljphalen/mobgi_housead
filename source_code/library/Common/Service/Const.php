<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class Common_Service_Const {

    const  DELETE_FLAG = 1;
    const  NOT_DELETE_FLAG = 0;
    const  ACCOUNTTASK_MAX_EXPIRE_TIME = 2147483647;
    const  ONE_DAY_FOR_SECONDS = 86400;
    const  ONE_HOUR = 3600;
    const  HTTP_SUCCESS_CODE = 200;
    const  TWO_SECONDS = 2;
    const THIRTY_DAY = 30;
    const SEVEN_DAY = 7;
    const THREE_DAY = 3;
    const ONE_DAY = 1;
    const ANDRIOD_PLATFORM = 1;
    const IOS_PLATFORM = 2;
    const SCREEN_CROSS = 1;
    const SCREEN_VERTICAL = 2;
    const DEFAULT_CPC_VIDEO_CTR = 0.05;
    const DEFAULT_CPC_PIC_CTR = 0.1;
	const DEFAULT_CPC_CUSTOME_CTR = 0.02;
	const DEFAULT_CPC_SLASH_CTR = 0.08;
	const DEFAULT_CPC_ENBED_CTR = 0.02;
    const RATE_BASE = 10000;

    //视频广告
    const VIDEO_AD_SUB_TYPE = 1;
    //插图广告
    const PIC_AD_SUB_TYPE = 2;
    //自定义广告
    const CUSTOME_AD_SUB_TYPE = 3;
    //开屏广告
    const SPLASH_AD_SUB_TYPE = 4;
    //原生嵌入广告
    const ENBED_AD_SUB_TYPE = 5;
    //互动广告
    const INTERATIVE_AD_SUB_TYPE = 6;

    //交叉推广 精品橱窗－焦点图
    const FOCUS_CUSTOME_AD_SUB_TYPE = 31;
    //交叉推广 精品橱窗－应用墙
    const WALL_CUSTOME_AD_SUB_TYPE = 32;
    //交叉推广 原生Banner
    const NATIVE_CUSTOME_AD_SUB_TYPE = 33;
    //原生广告 单图
    const SINGLE_ENBED_AD_SUB_TYPE = 51;
    //原生广告 组图
    const COMBINATION_ENBED_AD_SUB_TYPE = 52;


    //计费类型
    const CHARGE_TYPE_CPM = 1;
    const CHARGE_TYPE_CPC = 2;
    const CHARGE_TYPE_CPA = 3;

    //计费事件类型
    const EVENT_TYPE_VIEW = 5;
    const EVENT_TYPE_CLICK = 6;
    const EVENT_TYPE_ACTIVE = 45;

    //统计版本
    const STAT_MOBGI = 1;
    const STAT_HOUSEAD = 2;
    const STAT_ADX = 3;


    public static $mPlatformDesc = array(
        self::IOS_PLATFORM => 'IOS',
        self::ANDRIOD_PLATFORM => 'andriod'
    );


    // 广告类型
    const  INTERGRATION_AD_TYPE = 1;
    const  CHANNEL_AD_TYPE = 2;
    const  DSP_AD_TYPE = 3;
    public static $mAdType = array(
        self::INTERGRATION_AD_TYPE => '聚合广告',
        self::CHANNEL_AD_TYPE => '渠道广告',
        self::DSP_AD_TYPE => 'DSP广告'

    );

    // 广告子类型
    public static $mAdSubType = array(
        self::VIDEO_AD_SUB_TYPE => '视频广告',
        self::PIC_AD_SUB_TYPE => '插页广告',
        self::CUSTOME_AD_SUB_TYPE => '交叉推广',
        self::SPLASH_AD_SUB_TYPE => '开屏广告',
        self::ENBED_AD_SUB_TYPE => '原生广告'
    );
    // 广告子类型描述
    public static $mAdSubTypeDesc = array(
        self::VIDEO_AD_SUB_TYPE => 'video',
        self::PIC_AD_SUB_TYPE => 'pic',
        self::CUSTOME_AD_SUB_TYPE => 'custome',
        self::SPLASH_AD_SUB_TYPE => 'splash',
        self::ENBED_AD_SUB_TYPE => 'enbed'
    );

    //交叉推广子类型
    public static $mCustomeSubType = array(
        self::FOCUS_CUSTOME_AD_SUB_TYPE => '精品橱窗－焦点图',
        self::WALL_CUSTOME_AD_SUB_TYPE => '精品橱窗－应用墙',
        self::NATIVE_CUSTOME_AD_SUB_TYPE => '原生Banner',
    );

    //原生广告子类型
    public static $mEnbedSubType = array(
        self::SINGLE_ENBED_AD_SUB_TYPE => '单图',
        self::COMBINATION_ENBED_AD_SUB_TYPE => '组图',
    );

    //原生广告尺寸列表
    public static $mEnbedSize = array(
        '16:9' => '1280*720',
        '2:3' => '640*960',
        '3:2' => '960*640',
        '32:5' => '640*100',
        '1:1' => '1200*1200',
        '2:1' => '',
        '1200:627' => '1200*627',
    );

    // 广告位对应的广告类型
    static public $mAdPosType = array(
        self::VIDEO_AD_SUB_TYPE => 'VIDEO_INTERGRATION',
        self::PIC_AD_SUB_TYPE => 'PIC_INTERGRATION',
        self::CUSTOME_AD_SUB_TYPE => 'CUSTOME_INTERGRATION',
        self::SPLASH_AD_SUB_TYPE => 'SPLASH_INTERGRATION',
        self::ENBED_AD_SUB_TYPE => 'ENBED_INTERGRATION',
		self::INTERATIVE_AD_SUB_TYPE =>'INTERATIVE_AD'
    );
    //广告位类型对应的名称
    static public $mAdPosTypeName = array(
        'VIDEO_INTERGRATION' => '视频广告',
        'PIC_INTERGRATION' => '插屏广告',
        'CUSTOME_INTERGRATION' => '交叉推广',
        'SPLASH_INTERGRATION' => '开屏广告',
        'ENBED_INTERGRATION' => '原生广告',
		'INTERATIVE_AD'=>'互动广告'
    );


    //视频广告
    const VIDEO_ADS = 1;
    //插图广告
    const PIC_ADS = 2;
    //自定义广告
    const CUSTOME_ADS = 3;
    //开屏广告
    const SPLASH_ADS = 4;
    //原生流失广告
    const ENBED_ADS = 5;
    //交叉推广 精品橱窗－焦点图
    const CUSTOM_FOCUS = 31;
    //交叉推广 精品橱窗－应用墙
    const CUSTOM_WALL = 32;
    //交叉推广 原生Banner
    const CUSTOM_NATIVE = 33;


    //接口异常编码
    const FIELD_MISS = 50001;
    const FIELD_INVAILD = 50002;

    //配置状态码
    const STATUS_ACTIVE=1;
    const STATUS_NOT_ACTIVE=0;
    
    //dsp的编号id
    const ETORON_DSP_ID = 'Etoron_DSP';
    const DOMOB_DSP_ID    = 'Domob_DSP';
    const HOUSEAD_DSP_ID  = 'Housead_DSP';
    const UNIPLAY_DSP_ID ='Uniplay_DSP';
    const SMAATO_DSP_ID = 'Smaato_DSP';
    const TOUTIAO_DSP_ID = 'Toutiao_DSP';
    const INMOBI_DSP_ID = 'Inmobi_DSP';
    const OPERA_DSP_ID = 'Opera_DSP';
    const ZHIZIYUN_DSP_ID = "Zhiziyun_DSP";
    const ADIN_DSP_ID = 'Adin_DSP';
    const YOMOB_DSP_ID = 'Yomob_DSP';
    const BULEMOBI_DSP_ID='Bulemobi_DSP';





}