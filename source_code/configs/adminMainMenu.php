<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
//菜单配置
//基础信息模块
$config['Admin_Baseinfo_Module'] = array(
    'name' => '基础信息',
    'parent' => 'Admin_Top_Module',
    'items' => array(
        array(
            'name' => '应用管理',
            'items' => array(
                'Admin_Baseinfo_App',
                'Admin_Baseinfo_AppCheck',
            ),
        ),
        array(
            'name' => '广告商管理',
            'items' => array(
                'Admin_Baseinfo_AdsList',
            ),
        ),
        array(
            'name' => '渠道清单管理',
            'items' => array(
                'Admin_Baseinfo_Channel',
            ),
        ),
        array(
            'name' => '基本信息管理',
            'items' => array(
                'Admin_Baseinfo_AdsRelConfig',
            ),
        ),
        array(
            'name' => '原生模板管理',
            'items' => array(
                'Admin_Baseinfo_Template',
            ),
        ),

    )
);

//DSP投放模块
$config['Admin_Dsp_Config_Module'] = array(
    'name' => 'DSP投放配置',
    'parent' => 'Admin_Top_Module',
    'items' => array(
        array(
            'name' => '帐务管理',
            'items' => array(
                'Admin_Dsp_Accounttask_Manage',
                'Admin_Dsp_Accounttask_Audit',
                'Advertiser_Account_Log'
            ),
        ),
        array(
            'name' => '广告全局设置管理',
            'items' => array(
                'Admin_Dsp_App',
                'Admin_Dsp_StrategyConfig',
                'Admin_Dsp_Config',

            ),
        ),
        array(
            'name' => '广告管理',
            'items' => array(
                'Admin_Dsp_Admanage',
                'Advertiser_Delivery_UnitList',
                'Advertiser_Delivery_Index',
                'Advertiser_Delivery_OriginalityList',
                'Advertiser_Direct_List',
            ),
        ),


    )
);
//聚合配置模块
$config['Admin_Intergration_Module'] = array(
    'name' => '聚合投放配置',
    'parent' => 'Admin_Top_Module',
    'items' => array(
        array(
            'name' => '流量管理',
            'items' => array(
                'Admin_Intergration_Flow',
            ),
        ),
        array(
            'name' => '互动广告管理',
            'items' => array(
                'Admin_Interative_Conf',
                'Admin_Interative_Template',
                'Admin_Interative_Qr',
                'Admin_Interative_Report',
                'Admin_Interative_Activity',
                'Admin_Interative_Goods',
                'Admin_Interative_Code',
            ),
        ),


        array(
            'name' => '渠道定制管理',
            'items' => array(
                'Admin_Intergration_ChannelCustom',
            ),
        ),
        array(
            'name' => '旧版本管理',
            'items' => array(
                'Admin_Intergration_OldConfig',
            ),
        )
    ),

);

//投放监控模块
$config['Spm_Monitor_Module'] = array(
    'name' => '投放监控',
    'parent' => 'Monitor_Top_Module',
    'items' => array(
        array(
            'name' => '投放首页',
            'items' => array(
                'Admin_Spm_Index_Index',
                'Admin_Spm_Report_Index',

            ),
        ),
        array(
            'name' => '投放报表',
            'items' => array(
                'Admin_Spm_Report_Activity',
                'Admin_Spm_Report_Apk',
                'Admin_Spm_Report_Daily'
            ),
        ),
        array(
            'name' => '投放管理',
            'items' => array(
                'Admin_Spm_Delivery_Activity',
                'Admin_Spm_Delivery_ActivityGroup',
            ),
        ),
        array(
            'name' => '投放渠道管理',
            'items' => array(
                'Admin_Spm_Channel_Index',
                'Admin_Spm_Channel_Label',
                'Admin_Spm_Channel_Group',
                'Admin_Spm_Channel_AndroidGroup',
                'Admin_Spm_Channel_GdtConfig',
                'Admin_Spm_Channel_BaiduConfig',
                'Admin_Spm_Channel_PayConfig',
            ),
        ),
        array(
            'name' => '录入管理',
            'items' => array(
                'Admin_Spm_Entry_Cost',
                'Admin_Spm_Entry_CostAdd',
                'Admin_Spm_Entry_Plan',
                'Admin_Spm_Entry_PlanAdd',
                'Admin_Spm_Entry_StaffPlan',
                'Admin_Spm_Entry_StaffPlanAdd'
            ),
        ),
        array(
            'name' => '监测平台管理',
            'items' => array(
                'Admin_Spm_DataPlatform_Index',
            ),
        ),
        //        array(
        //            'name' => '反作弊管理',
        //            'items' => array(
        //                'Admin_Spm_AntiCheat_Config',
        //                'Admin_Spm_AntiCheat_Report',
        //                'Admin_Spm_AntiCheat_WhiteList',
        //            ),
        //        ),
        array(
            'name' => '工具管理',
            'items' => array(
                'Admin_Spm_Tools_Attribute',
                //                'Admin_Spm_Tools_ChannelTest',
                'Admin_Spm_Tools_Documents',
                'Admin_Spm_Tools_Monitor',
                'Admin_Spm_Tools_ChannelAccount',
            ),
        ),
        array(
            'name' => '海外投放管理',
            'items' => array(
                'Admin_Spm_Abroad_AppsflyerApp',
                'Admin_Spm_Abroad_AppsflyerActivity',
                'Admin_Spm_Abroad_AppsflyerChannel',
            ),
        ),
        //        array(
        //            'name' => '渠道商报表',
        //            'items' => array(
        //                'Admin_Spm_Advertiser_Report',
        //                'Admin_Spm_Advertiser_AndroidReport',
        //            ),
        //        ),
        array(
            'name' => '基础配置',
            'items' => array(
                'Admin_Spm_Setting_Product',
                //                'Admin_Spm_Setting_Defend',
            ),
        ),
        array(
            'name' => '素材库管理 ',
            'items' => array(
                'Admin_Spm_Material_Index',
                'Admin_Spm_Material_LabelManage',
                'Admin_Spm_Material_Group',
            ),
        )
    ),

);

//渠道报表模块
$config['Channelreport_Monitor_Module'] = array(
    'name' => '渠道报表',
    'parent' => 'Channelreport_Top_Module',
    'items' => array(
        array(
            'name' => '渠道报表',
            'url' => '/home',
            'items' => array(
                'Admin_Channelreport_Index_Index',
                'Admin_Channelreport_Api_Channel',
                'Admin_Channelreport_Api_Package',
            ),
        ),
    )

);


//MarketingApi模块
$config['Marketingapi_Ad_Module'] = array(
    'name' => 'Marketing Api',
    'parent' => 'Marketingapi_Top_Module',
    'items' => array(
        array(
            'name' => '首页',
            'url' => '/home',
            'items' => array(
                'Admin_Marketing_Index',
            ),
        ),
        array(
            'name' => '推广',
            'url' => '/advertising',
            'items' => array(
                'Admin_Marketing_Addetail',
                'Admin_Marketing_Campaigns',
                'Admin_Marketing_Adgroups',
                'Admin_Marketing_Ads',
                'Admin_Marketing_Adcreatives',
                'Admin_Marketing_Adcreativetemplateinfo',
                'Admin_Marketing_Common',
            ),
        ),
        array(
            'name' => '报表',
            'url' => '/report',
            'items' => array(
                'Admin_Marketing_Report',
            ),
        ),
        array(
            'name' => '财务',
            'url' => '/account',
            'items' => array(),
        ),
        array(
            'name' => '设置',
            'url' => '/setting',
            'items' => array(
                'Admin_Marketing_Account',
                'Admin_Marketing_Targetings',
                'Admin_Marketing_CustomAudiences',
                'Admin_Marketing_CustomAudienceFiles',
                'Admin_Marketing_Products',
                'Admin_Marketing_Images',
                'Admin_Marketing_Videos',
                'Admin_Marketing_Estimation',
                'Admin_Marketing_Advertiser',
            ),
        ),
    )

);

//用户管理模块
$config['Admin_User_Module'] = array(
    'name' => '用户管理',
    'parent' => 'Admin_Top_Module',
    'items' => array(
        array(
            'name' => '用户管理',
            'items' => array(
                'Admin_User',
                'Admin_Group',
                'Admin_User_Passwd',
                'Admin_Menu_Config',
                'Admin_User_CheckList',
            ),
        )
    )
);
//运营数据模块
$config['Admin_Data_Module'] = array(
    'name' => '运营数据',
    'parent' => 'Admin_Top_Module',
    'items' => array(
        array(
            'name' => '聚合广告数据',
            'items' => array(
                'Admin_Data_Report_Index',
                'Admin_Data_Report_Mobgi',
                'Admin_Data_Report_Custom',
                'Admin_Data_Report_Official',
                'Admin_Data_Report_Retention',
                'Admin_Data_Report_Ltv',
                'Admin_Data_Report_Nuv',
                'Admin_Data_Report_WatchingTime',
                'Admin_Data_Report_Test',
            ),
        ),
        array(
            'name' => '自投广告数据',
            'items' => array(
                'Admin_Data_Report_Housead',
            ),
        ),
        array(
            'name' => 'MobGi分成配置',
            'items' => array(
                'Admin_Data_Balance_Globalconfig',
                'Admin_Data_Balance_Customconfig',
            ),
        ),
        array(
            'name' => '广告监控数据',
            'items' => array(#'Admin_Monitor_Report_index',
            ),
        ),
        array(
            'name' => '数据预警',
            'items' => array(
                'Admin_Monitor_Report_Index',
                'Admin_Data_ThirdApi_Index',
            ),
        ),
        array(
            'name' => '数据配置',
            'items' => array(
                'Admin_Data_ThirdApi_Import',
                'Admin_Data_ThirdApi_Config',
                'Admin_Data_ThirdApi_Adjust',
                'Admin_Data_Syn_Index',
                'Admin_Data_Report_WeightLog',
            ),
        ),
        array(
            'name' => '运营报表',
            'items' => array(
                'Admin_Data_Report_runDetailReport',
                'Admin_Data_Report_WeekKpi',
            ),
        ),
    )
);

//试玩模块
$config['Admin_Try_Module'] = array(
    'name' => '试玩模块',
    'parent' => 'Admin_Top_Module',
    'items' => array(
        array(
            'name' => '试玩数据详情',
            'items' => array(
                'Admin_Data_Try_ReportData',
            ),
        ),
    )
);
//官网配置模块
$config['Admin_Website_Module'] = array(
    'name' => '官网配置管理',
    'parent' => 'Admin_Top_Module',
    'items' => array(
        array(
            'name' => '官网配置管理',
            'items' => array(
                'Admin_Website_SdkList',
                'Admin_Website_SdkVersionList',
                'Admin_Website_RepositoryMenuList',
                'Admin_Website_RepositoryList',
            ),
        ),
    )
);
//系统工具模块
$config['Admin_System_Module'] = array(
    'name' => '系统工具',
    'parent' => 'Admin_Top_Module',
    'items' => array(
        array(
            'name' => '操作日志',
            'items' => array(
                'Admin_Systemtool_OperateLog'
            ),
        ),
        array(
            'name' => '聚合测试管理',
            'items' => array(
                'Admin_Systemtool_Checkconfig',
                'Admin_Systemtool_Checkconfigs',
            ),
        ),
        array(
            'name' => '白名单工具',
            'items' => array(
                'Admin_Systemtool_AbTestFlowList',
                'Admin_Systemtool_AbTestList',
                'Admin_Systemtool_Whitelist',
                'Admin_Systemtool_Adsrelconfigwhitelist',
            ),
        ),
        array(
            'name' => '落地页管理',
            'items' => array(
                'Admin_Systemtool_Landingpagelist',
                'Admin_Systemtool_LandingpageTemplatelist',
                'Admin_Systemtool_Materiallist',
            ),
        ),
        array(
            'name' => '验包工具',
            'items' => array(
                'Admin_Systemtool_Checkpackage',
            ),
        ),
        array(
            'name' => '优惠券管理',
            'items' => array(
                'Admin_Systemtool_Coupon',
            ),
        ),
    )
);


$entry = Yaf_Registry::get('config')->adminroot;
$view = array(

    //DSP模块 -财务管理
    'Admin_Dsp_Accounttask_Manage' => array('帐务列表', $entry . '/Admin/Dsp_Accounttask/manage'),
    'Admin_Dsp_Accounttask_Audit' => array('帐务审核', $entry . '/Admin/Dsp_Accounttask/audit'),
    'Advertiser_Account_Log' => array('帐务记录', $entry . '/Advertiser/account/log'),

    //DSP模块 -广告全局设置
    'Admin_Dsp_App' => array('应用/广告位管理', $entry . '/Admin/Dsp_App/appList'),
    'Admin_Dsp_StrategyConfig' => array('展示策略', $entry . '/Admin/Dsp_StrategyConfig/list'),
    'Admin_Dsp_Config' => array('开关设置', $entry . '/Admin/Dsp_Config/index'),
    //Dsp模块－广告管理
    'Admin_Dsp_Admanage' => array('审核创意', $entry . '/Admin/Dsp_Admanage/index'),
    'Advertiser_Delivery_UnitList' => array('广告计划', $entry . '/Advertiser/Delivery/unitList'),
    'Advertiser_Delivery_Index' => array('广告活动', $entry . '/Advertiser/Delivery/index'),
    'Advertiser_Delivery_OriginalityList' => array('广告创意', $entry . '/Advertiser/Delivery/originalityList'),
    'Advertiser_Direct_List' => array('定向管理', $entry . '/Advertiser/Direct/list'),


    //运营数据模块
    'Admin_Data_Report_Index' => array('效果数据概览', $entry . '/Admin/Data_Report/index'),
    'Admin_Data_Report_Mobgi' => array('广告详情数据', $entry . '/Admin/Data_Report/mobgi'),
    'Admin_Data_Report_Custom' => array('定制渠道数据', $entry . '/Admin/Data_Report/custom'),
    'Admin_Data_Report_Official' => array('对外数据', $entry . '/Admin/Data_Report/official'),
    'Admin_Data_Report_Housead' => array('自投数据', $entry . '/Admin/Data_Report/housead'),
    'Admin_Data_Report_Nuv' => array('人均观看次数', $entry . '/Admin/Data_Report/Nuv'),
    'Admin_Data_Balance_Globalconfig' => array('全局配置', $entry . '/Admin/Data_Balance/globalconfig'),
    'Admin_Data_Balance_Customconfig' => array('定制配置', $entry . '/Admin/Data_Balance/customconfig'),
    'Admin_Data_Report_Retention' => array('用户留存', $entry . '/Admin/Data_Report/retention'),
    'Admin_Data_Report_Ltv' => array('LTV', $entry . '/Admin/Data_Report/ltv'),
    'Admin_Data_Report_WatchingTime' => array('用户观看时常', $entry . '/Admin/Data_Report/watchingTime'),
    'Admin_Data_Report_Test' => array('ABTest', $entry . '/Admin/Data_Report/abtest'),
    'Admin_Data_Report_WeightLog' => array('配置变更日志', $entry . '/Admin/Data_Report/weightLog'),
    'Admin_Data_ThirdApi_Import' => array('数据导入', $entry . '/Admin/Data_ThirdApi/import'),
    'Admin_Data_ThirdApi_Config' => array('数据管理', $entry . '/Admin/Data_ThirdApi/config'),
    'Admin_Data_ThirdApi_Index' => array('第三方数据监控', $entry . '/Admin/Data_ThirdApi/index'),
    'Admin_Data_ThirdApi_Adjust' => array('收益变更', $entry . '/Admin/Data_ThirdApi/adjust'),
    'Admin_Monitor_Report_Index' => array('数据监控', $entry . '/Admin/Monitor_Report/index'),
    'Admin_Data_Syn_Index' => array('数据同步', $entry . '/Admin/Data_Syn/appsyn'),
    'Admin_Data_Report_runDetailReport' => array('运营周报', $entry . '/Admin/Data_Report/runDetailReport'),
    'Admin_Data_Report_WeekKpi' => array('KPI', $entry . '/Admin/Data_Report/weekKpi'),
    //系统工具模块 - 操作日志
    'Admin_Systemtool_OperateLog' => array('操作日志列表', $entry . '/Admin/Systemtool_Operatelog/index'),
    'Admin_Systemtool_Checkconfigs' => array('配置验证工具', $entry . '/Admin/Systemtool_Checkconfig/configs'),
    'Admin_Systemtool_Checkconfig' => array('自定义配置验证工具', $entry . '/Admin/Systemtool_Checkconfig/index'),
    'Admin_Systemtool_Whitelist' => array('验包数据管理', $entry . '/Admin/Systemtool_Whitelist/search'),
    'Admin_Systemtool_Checkpackage' => array('APK信息管理', $entry . '/Admin/Systemtool_Whitelist/checkpackage'),
    'Admin_Systemtool_Coupon' => array('优惠券管理', $entry . '/Admin/Systemtool_Coupon/index'),
    'Admin_Systemtool_Adsrelconfigwhitelist' => array('白名单基础信息配置', $entry . '/Admin/Systemtool_Adsrelconfigwhitelist/index'),
    'Admin_Systemtool_AbTestFlowList' => array('测试流量管理', $entry . '/Admin/Systemtool_Abtestflow/index'),
    'Admin_Systemtool_AbTestList' => array('测试配置管理', $entry . '/Admin/Systemtool_Abtest/index'),

    //投放模块 - 落地页配置管理
    'Admin_Systemtool_Landingpagelist' => array('落地页管理', $entry . '/Admin/Systemtool_Landingpage/list'),
    'Admin_Systemtool_LandingpageTemplatelist' => array('落地页模板管理', $entry . '/Admin/Systemtool_Landingpagetemplate/list'),
    'Admin_Systemtool_Materiallist' => array('素材管理', $entry . '/Admin/Systemtool_Material/list'),

    //基础信息模块
    'Admin_Baseinfo_App' => array('应用列表', $entry . '/Admin/Baseinfo_App/index'),
    'Admin_Baseinfo_AppCheck' => array('应用审核', $entry . '/Admin/Baseinfo_App/checklist'),
    'Admin_Baseinfo_AdsList' => array('广告商列表', $entry . '/Admin/Baseinfo_AdsList/index'),
    'Admin_Baseinfo_Channel' => array('渠道列表', $entry . '/Admin/Baseinfo_Channel/index'),
    'Admin_Baseinfo_AdsRelConfig' => array('基本信息列表', $entry . '/Admin/Baseinfo_AdsRelConfig/index'),
    'Admin_Baseinfo_Template' => array('模板列表', $entry . '/Admin/Baseinfo_Template/index'),

    //聚合配置模块
    'Admin_Intergration_Flow' => array('流量分配', $entry . '/Admin/Intergration_Flow/index'),
    'Admin_Intergration_ChannelCustom' => array('渠道定制配置', $entry . '/Admin/Intergration_Channelcustom/index'),
    'Admin_Intergration_OldConfig' => array('旧配置列表', $entry . '/Admin/Intergration_Oldconfig/index'),
    //互动广告
    'Admin_Interative_Conf' => array('互动广告流量配置', $entry . '/Admin/Interative_Conf/index'),
    'Admin_Interative_Template' => array('互动广告模板列表', $entry . '/Admin/Interative_Template/index'),
    'Admin_Interative_Qr' => array('二维码数据', $entry . '/Admin/Interative_Report/qr'),
    'Admin_Interative_Report' => array('互动广告数据分析', $entry . '/Admin/Interative_Report/index'),
    'Admin_Interative_Activity' => array('活动管理', $entry . '/Admin/Interative_Activity/index'),
    'Admin_Interative_Goods' => array('商品管理', $entry . '/Admin/Interative_Goods/index'),
    'Admin_Interative_Code' => array('兑换码管理', $entry . '/Admin/Interative_Code/index'),


    // 投放Spm模块
    'Admin_Spm_Index_Index' => array('首页', $entry . '/Admin/Spm_Index/index'),
    'Admin_Spm_Report_Index' => array('产品概览', $entry . '/Admin/Spm_Report/index'),
    'Admin_Spm_Report_Activity' => array('活动报表', $entry . '/Admin/Spm_Report/activity'),
    'Admin_Spm_Report_Apk' => array('安卓渠道报表', $entry . '/Admin/Spm_Report/apk'),
    'Admin_Spm_Report_Daily' => array('日报周报', $entry . '/Admin/Spm_Report/daily'),
    'Admin_Spm_Report_Ltv' => array('投放LTV', $entry . '/Admin/Spm_Report/ltv'),
    'Admin_Spm_Delivery_Activity' => array('推广活动管理', $entry . '/Admin/Spm_Delivery/activity'),
    'Admin_Spm_Report_Retention' => array('用户留存数据', $entry . '/Admin/Spm_Report/retention'),
    'Admin_Spm_Delivery_ActivityGroup' => array('推广活动组管理', $entry . '/Admin/Spm_Delivery/activityGroup'),
    'Admin_Spm_Channel_Index' => array('投放渠道管理', $entry . '/Admin/Spm_Channel/index'),
    'Admin_Spm_Channel_Label' => array('渠道标签管理', $entry . '/Admin/Spm_Channel/label'),
    'Admin_Spm_Channel_Group' => array('渠道组管理', $entry . '/Admin/Spm_Channel/group'),
    'Admin_Spm_Channel_AndroidGroup' => array('安卓渠道组管理', $entry . '/Admin/Spm_Channel/androidGroup'),
    'Admin_Spm_Channel_GdtConfig' => array('广点通配置管理', $entry . '/Admin/Spm_Channel/gdtConfig'),
    'Admin_Spm_Channel_BaiduConfig' => array('百度配置管理', $entry . '/Admin/Spm_Channel/baiduConfig'),
    'Admin_Spm_Channel_PayConfig' => array('付费回调管理', $entry . '/Admin/Spm_Channel/payConfig'),
    'Admin_Spm_Entry_Cost' => array('广告成本', $entry . '/Admin/Spm_Entry/cost'),
    'Admin_Spm_Entry_CostAdd' => array('广告成本录入', $entry . '/Admin/Spm_Entry/costAdd'),
    'Admin_Spm_Entry_Plan' => array('广告计划', $entry . '/Admin/Spm_Entry/plan'),
    'Admin_Spm_Entry_PlanAdd' => array('广告计划录入', $entry . '/Admin/Spm_Entry/planAdd'),
    'Admin_Spm_Entry_StaffPlan' => array('投放师计划', $entry . '/Admin/Spm_Entry/staffPlan'),
    'Admin_Spm_Entry_StaffPlanAdd' => array('投放师计划录入', $entry . '/Admin/Spm_Entry/staffPlanAdd'),
    'Admin_Spm_DataPlatform_Index' => array('监测平台', $entry . '/Admin/Spm_DataPlatform/index'),
    'Admin_Spm_AntiCheat_Config' => array('反作弊配置', $entry . '/Admin/Spm_AntiCheat/config'),
    'Admin_Spm_AntiCheat_Report' => array('反作弊报表', $entry . '/Admin/Spm_AntiCheat/report'),
    'Admin_Spm_AntiCheat_WhiteList' => array('反作弊白名单', $entry . '/Admin/Spm_AntiCheat/whiteList'),
    'Admin_Spm_Tools_Attribute' => array('归因查询', $entry . '/Admin/Spm_Tools/attribute'),
    'Admin_Spm_Tools_ChannelTest' => array('渠道对接测试', $entry . '/Admin/Spm_Tools/channelTest'),
    'Admin_Spm_Tools_Documents' => array('文档管理', $entry . '/Admin/Spm_Tools/document'),
    'Admin_Spm_Tools_Monitor' => array('监控管理', $entry . '/Admin/Spm_Tools/monitor'),
    'Admin_Spm_Tools_ChannelAccount' => array('渠道账号管理', $entry . '/Admin/Spm_Tools/channelAccount'),
    'Admin_Spm_Abroad_AppsflyerApp' => array('AF应用配置', $entry . '/Admin/Spm_Abroad/appsflyerApp'),
    'Admin_Spm_Abroad_AppsflyerActivity' => array('AF活动管理', $entry . '/Admin/Spm_Abroad/appsflyerActivity'),
    'Admin_Spm_Abroad_AppsflyerChannel' => array('AF渠道管理', $entry . '/Admin/Spm_Abroad/appsflyerChannel'),
    'Admin_Spm_Advertiser_Report' => array('效果报表', $entry . '/Admin/Spm_Advertiser/report'),
    'Admin_Spm_Advertiser_AndroidReport' => array('渠道包报表', $entry . '/Admin/Spm_Advertiser/androidReport'),
    'Admin_Spm_Setting_Product' => array('基本信息', $entry . '/Admin/Spm_Setting/product'),
    'Admin_Spm_Setting_Defend' => array('作弊防护设置', $entry . '/Admin/Spm_Setting/defend'),
    'Admin_Spm_Material_Index' => array('素材库', $entry . '/Admin/Spm_Material/index'),
    'Admin_Spm_Material_LabelManage' => array('素材标签管理', $entry . '/Admin/Spm_Material/labelManage'),
    'Admin_Spm_Material_Group' => array('素材组管理', $entry . '/Admin/Spm_Material/Group'),
    //渠道报表模块 
    'Admin_Channelreport_Index_Index' => array('渠道报表首页', ''),
    'Admin_Channelreport_Api_Channel' => array('渠道报表', '/home/channelReport'),
    'Admin_Channelreport_Api_Package' => array('分包报表', 'home/packageReport'),

    //MarketingApi模块
    'Admin_Marketing_Index' => array('首页', ''),
    'Admin_Marketing_Addetail' => array('广告整体', ''),
    'Admin_Marketing_Campaigns' => array('推广计划', ''),
    'Admin_Marketing_Adgroups' => array('广告组', ''),
    'Admin_Marketing_Ads' => array('广告', ''),
    'Admin_Marketing_Adcreatives' => array('广告创意', ''),
    'Admin_Marketing_Adcreativetemplateinfo' => array('创意规格', ''),
    'Admin_Marketing_Common' => array('通用数据', ''),
    'Admin_Marketing_Report' => array('报表', ''),
    'Admin_Marketing_Account' => array('账号管理', ''),
    'Admin_Marketing_Targetings' => array('定向管理', ''),
    'Admin_Marketing_CustomAudiences' => array('人群管理', ''),
    'Admin_Marketing_CustomAudienceFiles' => array('人群文件管理', ''),
    'Admin_Marketing_Products' => array('标的物管理', ''),
    'Admin_Marketing_Images' => array('图片管理', ''),
    'Admin_Marketing_Videos' => array('视频管理', ''),
    'Admin_Marketing_Estimation' => array('人数预估', ''),
    'Admin_Marketing_Advertiser' => array('广告主管理', ''),

    //用户管理模块
    'Admin_User' => array('用户列表', $entry . '/Admin/User/index'),
    'Admin_Group' => array('权限组列表', $entry . '/Admin/Group/index'),
    'Admin_User_Passwd' => array('修改密码', $entry . '/Admin/User/passwd'),
    'Admin_Menu_Config' => array('开发配置菜单', $entry . '/Admin/Menu/index'),
    'Admin_User_CheckList' => array('开发者审核列表', $entry . '/Admin/User/index?type=check_list'),

    //官网配置模块
    'Admin_Website_SdkList' => array('SDK插件管理', $entry . '/Admin/Website_Sdk/index'),
    'Admin_Website_SdkVersionList' => array('SDK版本管理', $entry . '/Admin/Website_Sdk/versionlist'),
    'Admin_Website_RepositoryMenuList' => array('知识库菜单管理', $entry . '/Admin/Website_Repository/index'),
    'Admin_Website_RepositoryList' => array('知识库管理', $entry . '/Admin/Website_Repository/documentlist'),

);

$extends = array();

$topModule = array(
    'Admin_Top_Module' => array(
        'name' => 'Admin',
        'url' => $entry . '/Admin/Index/index',
        'icon' => $entry . '/static/img/admin.png'
    ),
    'Monitor_Top_Module' => array(
        'name' => 'Monitor',
        'url' => $entry . '/Admin/Spm_Index/index',
        'icon' => $entry . '/static/img/monitor.png'
    ),
    'Channelreport_Top_Module' => array(
        'name' => 'Channel Report',
        'url' => $entry . '/Admin/Channelreport_Index/index',
        'icon' => $entry . '/static/img/cr.png'
    ),
    'Marketingapi_Top_Module' => array(
        'name' => 'Marketing API',
        'url' => $entry . '/Admin/Marketing_Index/index',
        'icon' => $entry . '/static/img/mkt.png'
    ),
);

$noVerify = array(
    'Admin_Common',
    'Admin_Home',
    'Admin_Initiator',
    'Admin_Index',
    'Admin_Login'
);

return array($topModule, $config, $view, $extends, $noVerify);
