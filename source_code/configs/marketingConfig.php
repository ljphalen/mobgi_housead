<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-12-25 16:22:32
 * $Id: marketingConfig.php 62100 2017-12-25 16:22:32Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

$config = array(
    'test' => array (
        'MARKETING_API_URL' => 'https://sandbox-api.e.qq.com/',
    ),
    'product' => array (
        'MARKETING_API_URL' => 'https://api.e.qq.com/',
    ),
    'develop' => array (
//        'MARKETING_API_URL' => 'https://api.e.qq.com/',
        'MARKETING_API_URL' => 'https://sandbox-api.e.qq.com/',
    )
);
$marketingConfig = defined('ENV') ? $config[ENV] : $config['product'];

$marketingConfig['API_VERSION'] = "v1.0";
$marketingConfig['RESOURCE_NAME'] = array(
    //帐号服务
    "advertiser",               //广告帐号
    'fund_transfer',            //发起代理商与子客户之间转账
    'funds',                    //获取资金账户信息
    'fund_statements_daily',    //获取资金账户日结明细
    'fund_statements_detailed', //获取资金流水
    'realtime_cost',            //实时消耗
    //广告投放
    'campaigns',                //推广计划
    'adgroups',                 //广告组 
    'adcreatives',              //广告创意 
    'adcreative_template_info',              //获取创意规格信息 
    'ads',                      //广告 
    'products',                 //标的物 
    'targetings',               //定向 
    'targeting_tags',           //定向标签 
    'images',                   //图片 
    'videos',                   //视频 
    'estimation',               //人数预估 
    //数据洞察
    'daily_reports',            //日报表
    'custom_audience_insights', //人群洞察分析
    'tracking_reports',         //点击追踪报表
    //人群管理
    'custom_audiences',         //客户人群 
    'custom_audience_files',    //客户人群数据文件
    //用户数据接入
    'user_action_sets',         //用户行为数据源
    'user_action_set_reports',  //用户行为数据源报表
    'user_actions',             //用户行为数据
);
$marketingConfig['RESOURCE_ACTION'] = array(
    'add',                      //创建
    'get',                      //获取信息
    'update',                   //更新
    'delete' ,                  //删除
);
$marketingConfig['ACTION_METHOD'] = array(
    'add'=>'post',
    'get'=>'get',
    'update'=>'post',
    'delete'=>'post',
);


//推广计划类型
$marketingConfig['CAMPAIGN_TYPE'] = array(
    'CAMPAIGN_TYPE_NORMAL'=> [
        'name' => '普通展示广告',
        'site_set' => [
//            'SITE_SET_QZONE','SITE_SET_QQCLIENT','SITE_SET_MUSIC','SITE_SET_QQCOM',
            'SITE_SET_MOBILE_UNION','SITE_SET_WECHAT','SITE_SET_MOBILE_INNER','SITE_SET_TENCENT_NEWS','SITE_SET_TENCENT_VIDEO']
    ],
    'CAMPAIGN_TYPE_WECHAT_OFFICIAL_ACCOUNTS'=> [
        'name' => '微信公众号广告',
        'site_set' => ['SITE_SET_WECHAT']
    ],
    'CAMPAIGN_TYPE_WECHAT_MOMENTS'=>[
        'name' => '微信朋友圈广告',
        'site_set' => ['SITE_SET_WECHAT']
    ]
);

//标的物类型
$marketingConfig['PRODUCT_TYPE'] = array(
    //腾讯开放平台移动应用，创建广告前需通过 [product 模块] 登记腾讯开放平台的应用 id，创建广告时需填写之前登记的应用 id，仅普通展示广告（ campaign_type = CAMPAIGN_TYPE_NORMAL ）支持
    'PRODUCT_TYPE_APP_ANDROID_OPEN_PLATFORM'=>[
        'name' => '腾讯开放平台Android应用',
        'desc' => '推广Android应用，增加应用的下载',
        'campaign_type' => ['CAMPAIGN_TYPE_NORMAL'],
        'product_refs_id' => 1,
    ],
    //苹果应用，创建广告前需通过 [product 模块] 登记 App Store 的应用 id，创建广告时需填写之前登记的应用 id，仅普通展示广告（ campaign_type = CAMPAIGN_TYPE_NORMAL ）支持
    'PRODUCT_TYPE_APP_IOS'=>[
        'name' => '苹果IOS应用',
        'desc' => '推广iOS应用，增加应用的下载',
        'campaign_type' => ['CAMPAIGN_TYPE_NORMAL'],
        'product_refs_id' => 1,
    ],
    //电商推广，创建广告时无需创建和指定标的物，所有的 campaign_type 均支持投放
    'PRODUCT_TYPE_ECOMMERCE'=>[
        'name' => '电商推广',
        'desc' => '推广电商页面，增加商品购买量',
        'campaign_type' => ['CAMPAIGN_TYPE_NORMAL','CAMPAIGN_TYPE_WECHAT_OFFICIAL_ACCOUNTS','CAMPAIGN_TYPE_WECHAT_MOMENTS'],
        'product_refs_id' => 0,
    ],
    //微信品牌页，创建广告时无需创建和指定标的物，仅微信公众号广告（ campaign_type = CAMPAIGN_TYPE_WECHAT_OFFICIAL_ACCOUNTS ）以及微信朋友圈广告（ campaign_type = CAMPAIGN_TYPE_WECHAT_MOMENTS ）支持
    'PRODUCT_TYPE_LINK_WECHAT'=>[
        'name' => '微信品牌页',
        'desc' => '在微信平台，推广品牌活动，增加知名度',
        'campaign_type' => ['CAMPAIGN_TYPE_WECHAT_OFFICIAL_ACCOUNTS','CAMPAIGN_TYPE_WECHAT_MOMENTS'],
        'product_refs_id' => 0,
    ],
    //微信本地门店推广，创建广告前需在对应的微信公众号中注册登记门店信息，创建广告时需填写之前登记的门店 id，仅微信朋友圈广告（ campaign_type = CAMPAIGN_TYPE_WECHAT_MOMENTS ）支持门店信息的登记及获取可以通过微信公众平台提供的接口进行操作，具体方式可以参考 [本地门店的创建及获取]
    'PRODUCT_TYPE_LBS_WECHAT'=>[
        'name' => '微信本地门店推广',
        'desc' => '本地门店或活动，吸引本地用户到店或参加活动',
        'campaign_type' => ['CAMPAIGN_TYPE_WECHAT_MOMENTS'],
        'product_refs_id' => 1,
    ],
    //普通链接，创建广告时无需创建和指定标的物，仅普通展示广告（ campaign_type = CAMPAIGN_TYPE_NORMAL ）支持
    'PRODUCT_TYPE_LINK'=>[
        'name' => '普通链接',
        'desc' => '推广网页，增加网页的访问量',
        'campaign_type' => ['CAMPAIGN_TYPE_NORMAL'],
        'product_refs_id' => 0,
    ],
);

//标的物类型（可创建标的物id）
$marketingConfig['PRODUCT_REFS_TYPE'] = array(
    //腾讯开放平台移动应用，创建广告前需通过 [product 模块] 登记腾讯开放平台的应用 id，创建广告时需填写之前登记的应用 id，仅普通展示广告（ campaign_type = CAMPAIGN_TYPE_NORMAL ）支持
    'PRODUCT_TYPE_APP_ANDROID_OPEN_PLATFORM'=>[
        'name' => '腾讯开放平台Android应用',
        'desc' => '推广Android应用，增加应用的下载',
        'campaign_type' => ['CAMPAIGN_TYPE_NORMAL'],
    ],
    //苹果应用，创建广告前需通过 [product 模块] 登记 App Store 的应用 id，创建广告时需填写之前登记的应用 id，仅普通展示广告（ campaign_type = CAMPAIGN_TYPE_NORMAL ）支持
    'PRODUCT_TYPE_APP_IOS'=>[
        'name' => '苹果IOS应用',
        'desc' => '推广iOS应用，增加应用的下载',
        'campaign_type' => ['CAMPAIGN_TYPE_NORMAL'],
    ],
    //微信本地门店推广，创建广告前需在对应的微信公众号中注册登记门店信息，创建广告时需填写之前登记的门店 id，仅微信朋友圈广告（ campaign_type = CAMPAIGN_TYPE_WECHAT_MOMENTS ）支持门店信息的登记及获取可以通过微信公众平台提供的接口进行操作，具体方式可以参考 [本地门店的创建及获取]
    'PRODUCT_TYPE_LBS_WECHAT'=>[
        'name' => '微信本地门店推广',
        'desc' => '本地门店或活动，吸引本地用户到店或参加活动',
        'campaign_type' => ['CAMPAIGN_TYPE_WECHAT_MOMENTS'],
    ],
);

//标的物类型描述
$marketingConfig['PRODUCT_TYPE_DESC'] = array(
    'PRODUCT_TYPE_APP_ANDROID_OPEN_PLATFORM'=>'推广Android应用，增加应用的下载',//腾讯开放平台移动应用，创建广告前需通过 [product 模块] 登记腾讯开放平台的应用 id，创建广告时需填写之前登记的应用 id，仅普通展示广告（ campaign_type = CAMPAIGN_TYPE_NORMAL ）支持
    'PRODUCT_TYPE_APP_IOS'=>'推广iOS应用，增加应用的下载',//苹果应用，创建广告前需通过 [product 模块] 登记 App Store 的应用 id，创建广告时需填写之前登记的应用 id，仅普通展示广告（ campaign_type = CAMPAIGN_TYPE_NORMAL ）支持
    'PRODUCT_TYPE_ECOMMERCE'=>'推广电商页面，增加商品购买量',//电商推广，创建广告时无需创建和指定标的物，所有的 campaign_type 均支持投放
    'PRODUCT_TYPE_LINK_WECHAT'=>'在微信平台，推广品牌活动，增加知名度',//微信品牌页，创建广告时无需创建和指定标的物，仅微信公众号广告（ campaign_type = CAMPAIGN_TYPE_WECHAT_OFFICIAL_ACCOUNTS ）以及微信朋友圈广告（ campaign_type = CAMPAIGN_TYPE_WECHAT_MOMENTS ）支持
    'PRODUCT_TYPE_LBS_WECHAT'=>'本地门店或活动，吸引本地用户到店或参加活动',//微信本地门店推广，创建广告前需在对应的微信公众号中注册登记门店信息，创建广告时需填写之前登记的门店 id，仅微信朋友圈广告（ campaign_type = CAMPAIGN_TYPE_WECHAT_MOMENTS ）支持门店信息的登记及获取可以通过微信公众平台提供的接口进行操作，具体方式可以参考 [本地门店的创建及获取]
    'PRODUCT_TYPE_LINK'=>'推广网页，增加网页的访问量',//普通链接，创建广告时无需创建和指定标的物，仅普通展示广告（ campaign_type = CAMPAIGN_TYPE_NORMAL ）支持
);

//客户设置的状态
$marketingConfig['AD_STATUS'] = array(
    'AD_STATUS_NORMAL'=>'有效',
    'AD_STATUS_SUSPEND'=>'暂停',
);

//系统状态
$marketingConfig['AD_SYSTEM_STATUS'] = array(
    'AD_STATUS_NORMAL'=>'有效',
    'AD_STATUS_PENDING'=>'待审核',
    'AD_STATUS_DENIED'=>'审核不通过',
    'AD_STATUS_FROZEN'=>'封停',
    'AD_STATUS_PREPARE'=>'准备资源',
);

//投放速度类型
$marketingConfig['SPEED_MODE'] = array(
    'SPEED_MODE_FAST'=>'加速投放',//加速投放，广告会以较快的速度获得曝光，选择加速投放可能会导致您的预算较快地耗尽
    'SPEED_MODE_STANDARD'=>'标准投放',//标准投放，系统会优化您的广告的投放，让您的预算在设定的投放时段内较为平稳地消耗，默认为标准投放
);

//站点集合
$marketingConfig['SITE_SET'] = array(
//    'SITE_SET_QZONE'=>'QQ 空间',
//    'SITE_SET_QQCLIENT'=>'QQ 客户端',
//    'SITE_SET_MUSIC'=>'QQ 音乐',
    'SITE_SET_MOBILE_UNION'=>'移动联盟',
//    'SITE_SET_QQCOM'=>'腾讯网',
    'SITE_SET_WECHAT'=>'微信',
    'SITE_SET_MOBILE_INNER'=>'移动内部站点',
    'SITE_SET_TENCENT_NEWS'=>'腾讯新闻',
    'SITE_SET_TENCENT_VIDEO'=>'腾讯视频'
);

//创意形式
$marketingConfig['ADCREATIVE_STYLE'] = array(
    '视频',
    '多图轮播',
    '单图(文)',
    '多图(文)',
    '随心互动',
    '文字链',
    '微动',
);

//创意元素类型
$marketingConfig['ELEMENT_TYPE'] = array(
    'ELEMENT_TYPE_TEXT' => '文本',
    'ELEMENT_TYPE_IMAGE' => '图片',
    'ELEMENT_TYPE_VIDEO' => '视频',
    'ELEMENT_TYPE_URL' => 'URL',
    'ELEMENT_TYPE_ENUM' => '枚举类型',
    'ELEMENT_TYPE_CANVAS' => '画布',
    'ELEMENT_TYPE_STRUCT' => '结构体类型',
    'ELEMENT_TYPE_REFERENCE' => 'TSA 外部资源引用类型，例如应用宝落地页 id 、视频说说 id 、微信小程序 id',
);

//扣费方式
$marketingConfig['BILLINGEVENT'] = array(
    'BILLINGEVENT_CLICK'=>'按点击扣费',         //按点击扣费，仅可以在以下优化目标时（ optimization_goal = OPTIMIZATIONGOAL_CLICK, OPTIMIZATIONGOAL_APP_ACTIVATE, OPTIMIZATIONGOAL_APP_REGISTER, OPTIMIZATIONGOAL_PROMOTION_CLICK_KEY_PAGE, OPTIMIZATIONGOAL_ECOMMERCE_ORDER, OPTIMIZATIONGOAL_APP_PURCHASE, OPTIMIZATIONGOAL_ECOMMERCE_CHECKOUT, OPTIMIZATIONGOAL_PAGE_RESERVATION 时）使用
    'BILLINGEVENT_IMPRESSION'=>'按曝光扣费',    //按曝光扣费，优化目标为根据曝光量优化（ optimization_goal = OPTIMIZATIONGOAL_IMPRESSION 时）使用
);

//优化目标
$marketingConfig['OPTIMIZATION_GOAL'] = array(
    'OPTIMIZATIONGOAL_CLICK' => [
        'name' => '点击量',
        'billing_event' => 'BILLINGEVENT_CLICK'
    ],
    'OPTIMIZATIONGOAL_IMPRESSION' => [
        'name' => '曝光',
        'billing_event' => 'BILLINGEVENT_IMPRESSION'
    ],
    'OPTIMIZATIONGOAL_APP_ACTIVATE' => [
        'name' => '移动 App 激活',
        'billing_event' => 'BILLINGEVENT_OCPA'//不存在此扣费方式，oCPA使用的是按点击扣费，此处是为了前端便捷处理
    ],
    'OPTIMIZATIONGOAL_APP_REGISTER' => [
        'name' => 'App 注册',
        'billing_event' => 'BILLINGEVENT_OCPA'
    ],
    'OPTIMIZATIONGOAL_APP_PURCHASE' => [
        'name' => 'App 购买',
        'billing_event' => 'BILLINGEVENT_OCPA'
    ],
    'OPTIMIZATIONGOAL_ECOMMERCE_ORDER' => [
        'name' => '下单',
        'billing_event' => 'BILLINGEVENT_OCPA'
    ],
    'OPTIMIZATIONGOAL_ECOMMERCE_CHECKOUT' => [
        'name' => 'H5 购买',
        'billing_event' => 'BILLINGEVENT_OCPA'
    ],
    'OPTIMIZATIONGOAL_PROMOTION_CLICK_KEY_PAGE' => [
        'name' => 'H5 注册',
        'billing_event' => 'BILLINGEVENT_OCPA'
    ],
    'OPTIMIZATIONGOAL_PAGE_RESERVATION' => [
        'name' => '表单预约',
        'billing_event' => 'BILLINGEVENT_OCPA'
    ],
);

//优化目标
$marketingConfig['INTERACTION'] = array(
    'INTERACTION_DISABLED'=>'不支持',
    'INTERACTION_ENABLED'=>'支持',
);

//人群类型
$marketingConfig['AUDIENCE_TYPE'] = [
    'CUSTOMER_FILE'=> ['name'=>'号码文件人群', 'parent_name'=>'私有人群'],
    'LOOKALIKE'=> ['name'=>'智能拓展', 'parent_name'=>'拓展人群'],
    'USER_ACTION'=> ['name'=>'用户行为人群', 'parent_name'=>'私有人群'],
    'LBS'=> ['name'=>'地理位置人群', 'parent_name'=>'私有人群'],
    'KEYWORD'=> ['name'=>'关键词人群', 'parent_name'=>'私有人群'],
    'AD'=> ['name'=>'广告受众', 'parent_name'=>'私有人群'],
    'COMBINE'=> ['name'=>'交并差组合', 'parent_name'=>'组合人群'],
];

//人群状态
$marketingConfig['AUDIENCE_STATUS'] = [
    'PENDING'=> [ 'name' => '待处理'],
    'PROCESSING'=> [ 'name' => '处理中'],
    'SUCCESS'=> [ 'name' => '可用'],
    'ERROR'=> [ 'name' => '错误'],
];

//号码包用户 id 类型
$marketingConfig['USER_ID_TYPE'] = [
    'QQ'=> [ 'name' => 'QQ号', 'desc' => '5-12位的纯数字串'],
    'HASH_QQ'=> [ 'name' => 'QQ号-MD5', 'desc' => '加密后的QQ号，加密前为5-12位的纯数字串，加密后为不计大小写的32位数字字母串'],
//    'MOBILE_PHONE'=> [ 'name' => '手机号', 'desc' => '11位的纯数字串'],
    'HASH_MOBILE_PHONE'=> [ 'name' => '手机号-MD5', 'desc' => '加密后的手机号，加密前为11位的纯数字串，加密后为不计大小写的32位数字字母串'],
    'IDFA'=> [ 'name' => 'IDFA', 'desc' => '苹果设备ID，32位的数字+大写字母串，用“-”杠分隔，示例：B1C4AAF6-A7E3-4FB2-8E2C-C35EF3EFCA84'],
    'HASH_IDFA'=> [ 'name' => 'IDFA-MD5', 'desc' => '加密后的IDFA，加密前需要格式转化成32位的数字+大写字母，加密后为不计大小写的32位数字字母串，示例：加密前B1C4AAF6-A7E3-4FB2-8E2C-C35EF3EFCA84，加密后f42808c33896072de60efc1a86643c4f'],
    'IMEI'=> [ 'name' => 'IMEI', 'desc' => '安卓设备ID，14位或15位的纯数字串，或者14位或15位的数字+小写字母串'],
    'HASH_IMEI'=> [ 'name' => 'IMEI-MD5', 'desc' => '加密后的IMEI，加密前需要格式转化成14位或15位的数字+小写字母串，加密后为不计大小写的32位数字字母串，示例：加密前868030035048584，加密后a0180f5427694050503b02918c01262a'],
    'GDT_OPENID'=> [ 'name' => '广点通OpenID', 'desc' => '和广点通做cookie mapping后，广点通侧返回给您的ID，示例：FFFC0DF3F9A5F6EE49500B132979AE55'],
    'MAC'=> [ 'name' => 'MAC地址', 'desc' => '硬件标识符，格式为 6 组 16 进制数，用“:”分隔，示例： 08:00:20:0A:8C:6D'],
    'HASH_MAC'=> [ 'name' => 'MAC地址-MD5', 'desc' => '加密后的 MAC 地址，加密前需要去除分隔符“:”后转为大写，示例：加密前 02:00:00:00:00:00，加密后 e3f5536a141811db40efd6400f1d0a4e'],
];

//广告人群支持的规则类型
$marketingConfig['RULE_TYPE'] = [
    'CLICK' => '点击',
    'CONVERSION' => '转化',
];

//广告人群支持的转化类型
$marketingConfig['CONVERSION_TYPE'] = [
    'APP_START_DOWNLOAD' => 'APP 开始下载',
    'APP_FINISH_DOWNLOAD' => 'APP 下载完成',
    'APP_INSTALL' => 'APP 安装',
    'APP_ACTIVATE' => 'APP 激活',
];

return $marketingConfig;
