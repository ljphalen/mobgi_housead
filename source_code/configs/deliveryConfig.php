<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
return array(
    'ageDirectList' => array(1 => '<18', 2 => '18~~25', 3 => '25~~35', 4 => '35~~45', 5 => '45~~55', 6 => '>55'),
    'netWorkList' => array(1 => 'wifi', 4 => '4G', 3 => '3G', 2 => '2G', 0 => '未知'),
    'operatorList' => array(0=>'未知',1 => '联通', 2 => '电信', 3 => '移动'),
    'brandList' => array(
        1 => 'iphone',
        2 => 'ipad',
        3 => 'ipod',
        4 => 'samsung',
        5 => 'oppo',
        6 => 'huawei',
        7 => 'vivo',
        8 => 'xiaomi',
        9 => 'gionee',
        10 => 'lenovo',
        11 => 'TCL',
        12 => 'lg',
        13 => 'google',
        14 => 'sony',
        0 => '其它'
    ),
    'screenList' => array(1 => '微屏', 2 => '小屏', 3 => '中屏', 4 => '大屏', 0 => '其它'),
    'interestList' => array(
        1 => '塔防类', 2 => '策略类', 3 => '动作类', 4 => '消除类', '5' => '角色扮演类',
        11 =>'休闲时间',12 =>'体育格斗',13 =>'儿童益智',14 =>'动作射击',15 =>'塔防守卫',
        16 =>'宝石消除',17 =>'扑克棋牌',18 =>'游戏中心',19 =>'游戏辅助',20 =>'经营策略',
        21 =>'网络游戏',22 =>'角色扮演',23 =>'跑酷竞速',
        ),
    'payAbilityList' => array(0 => '潜在', 1 => '低', 2 => '中', 3 => '高'),
    'gameFrequencyList' => array(0 => '潜在', 1 => '低频', 2 => '中频', 3 => '高频'),
    'sexTypeList' => array(0 => '不限', 1 => '男', 2 => '女'),
    'osTypeList' => array(0 => '不限', 1 => '安卓', 2 => 'ios'),
    //操作系统对应的手机品牌
    'ostypeBrandList' => array(
        2=>array(1 => 'iphone',2 => 'ipad',3 => 'ipod'),
        1=>array(4 => 'samsung',5=> 'oppo',6 => 'huawei',7 => 'vivo',8 => 'xiaomi',9 => 'gionee',10 => 'lenovo',11 => 'TCL',12 => 'lg',13 => 'google',14 => 'sony'),
        0=>array(-1 => '其它')
    ),
    'chargeTypeList' => array(1 => 'cpm', 2 => 'cpc'),
    'adTargetType' => array(1 => 'Android应用', 2 => 'IOS应用',3 => '网页'),
    //点击后跳转动作
    'jumpType'=>array(
        //Android应用
        1=>array(7=>'通知栏下载', 0=>'静默下载', 1=>'跳转应用市场下载'),
        //IOS应用
        2=>array(1=>'AppStore打开', 8=>'应用内商店内页打开'),
        //网页
        3=>array(2=>'默认浏览器打开', 3=>'自建浏览器打开', 6=>'无动作'),

    ),
    'timeType' => array(0 => '全部时段', 1 => '指定时段'),
    'originalityType' => array(1 => '视频广告', 2 => '插屏广告', 3 => '交叉推广', 4 => '开屏', 5 => '原生'),
    'adSubType' => array(
        3 => [
            31 => '精品橱窗－焦点图',
            32 => '精品橱窗－应用墙',
            33 => '原生Banner',
        ],
        5 => [
            51 => '单图',
            52 => '组图',
        ],
        6 => [
            61 => '悬浮窗',
            62 => '下一送一',
            63 => '公告',
            64 => '插屏',
            65 => '角标',
            66 => '激励',
            67 => '试玩墙',
        ],
    ),
    'strategy' => array(1 => '优选曝光', 2 => '平均曝光'),
    'h5upload' => array(1 => '直接上传', 2 => '使用模板'),
    'h5template' => array(1 => '轮播图模板', 2 => '单图模板'),
    'modeType' => array(1 => '匀速', 2 => '普通'),
    'limitType' => array(0 => '无限', 1 => '每日限额'),
    'unitStatus' => array(1 => '投放', 2 => '暂停'),
    'adStatus' => array(1 => '投放', 2 => '暂停'),
    'originalityStatus' => array(1 => '投放', 2 => '暂停'),
    'originalityTypeSona' => array(1 => '安卓插页', 2 => '安卓视频', 3 => 'IOS插页', 4 => 'IOS视频'),
    'customAnimationEffect'=>array(0=>'不使用', 1=>'淡出', 2=>'平移'),//交叉推广原生banner动画特效
    
    'trialAdTargetType' => array(1 => 'UI', 2 => 'Service', 3 => '逻辑代码', 4 => 'SDK'),
    'trialFloatView' => [1=>'显示', 2=>'不显示'],
    'trialShortcut' => [1=>'显示', 2=>'不显示'],
    
    //广点通直投定向条件
    'gdt_age' => '',
    'gdt_gender' => array(
        'MALE' => '男性',
        'FEMALE' => '女性',
        'UNKNOWN' => '未知',
    ),
    'gdt_location' => array(),//通过接口获取
    'gdt_keyword' => '',
    'gdt_app_behavior' => array(),//通过接口获取
    'gdt_app_act' => array(
        'ACTIVE' => '活跃',
        'PAID' => '付费'
    ),
    'gdt_device_price' => array(
        'INEXPENSIVE' => '￥1500 以下',
        'AFFORDABLE' => '￥1500 ~ 2500',
        'EXPENSIVE' => '￥2500 以上',
    ),
    'gdt_user_os' => array(
        'IOS' => '苹果系统',
        'ANDROID' => '安卓系统',
        'WINDOWS' => '微软系统',
        'SYMBIAN' => '塞班系统',
        'JAVA' => 'JAVA 系统',
        'UNKNOWN' => '未知',
    ),
    'gdt_network_type' => array(
        'WIFI' => '无线网络',
        'NET_2G' => '2G 网络',
        'NET_3G' => '3G 网络',
        'NET_4G' => '4G 网络',
        'UNKNOWN' => '联网方式未知',
    ),
    'gdt_network_operator' => array(
        'CMCC' => '中国移动',
        'CUC' => '中国联通',
        'CTC' => '中国电信',
        'UNKNOWN' => '未知',
    ),
    'gdt_region' => array(),//通过接口获取
    'gdt_business_interest' => array(),//通过接口获取
    'gdt_online_scenario' => array(
        'PUBLIC_PLACE' => '公共场所',
        'HOME' => '家庭',
        'COMPANY' => '公司',
        'UNKNOWN' => '未知',
    ),
    'gdt_education' => array(
        'DOCTOR' => '博士',
        'MASTER' => '硕士',
        'BACHELOR' => '本科',
        'SENIOR' => '高中',
        'JUNIOR' => '初中',
        'PRIMARY' => '小学',
        'UNKNOWN' => '未知',
    ),
    'gdt_relationship_status' => array(
        'PARENTING' => '育儿',
        'SINGLE' => '单身',
        'NEWLY_MARRIED' => '新婚',
        'MARRIED' => '已婚',
    ),
    'gdt_paying_user_type' => array(
        'LATENT_VIR_PAY' => '潜在虚拟付费用户',
        'ECOMMERCE_PAID' => '已有电商付费用户',
    ),
    'gdt_dressing_index' => array(
        'FREEZING' => '寒冷',
        'COLD' => '冷',
        'CHILLY' => '凉',
        'COOL' => '温凉',
        'MILDLY_COOL' => '凉舒适',
        'MILD' => '舒适',
        'WARM' => '热舒适',
        'TORRIDITY' => '炎热',
    ),
    'gdt_uv_index' => array(
        'WEAK' => '弱',
        'TEND_WEAK' => '偏弱',
        'MEDIUM' => '中等',
        'TEND_STRONG' => '偏强',
        'STRONG' => '强',
    ),
    'gdt_makeup_index' => array(
        'PREVENT_CRACKING' => '防龟裂',
        'MOISTURING' => '保湿',
        'OIL_CONTROL' => '控油',
        'UV_PROTECT' => '防晒',
    ),
    'gdt_climate' => array(
        'SHINE' => '晴天',
        'CLOUDY' => '阴天',
        'RAINY' => '雨天',
        'FOGGY' => '雾',
        'SNOWY' => '雪',
        'SANDY' => '沙尘',
    ),
    'gdt_living_status' => array(
        'COLLEGE_STUDENT' => '在校大学生',
        //                                'BUSINESS_USER'=>'商旅用户',
        //                                'GASTRONOME'=>'美食达人',
        //                                'DRIVER'=>'有车一族',
        //                                'MEDICAL_STAFF'=>'医护人士',
        //                                'HOUSE_OWNER'=>'有房一族',
        //                                'IT_PROFESSIONAL'=>'IT 人士',
    ),
    'gdt_union_media_category' => array(),//通过接口获取
    'gdt_product_type' => array(
//                                'PRODUCT_TYPE_LINK '=>'普通链接',
'PRODUCT_TYPE_APP_IOS' => 'IOS',
'PRODUCT_TYPE_APP_ANDROID_OPEN_PLATFORM' => '安卓',
    ),
    //广点通创意类型
    'gdt_creative_template' => array(
        array('name' => '1000*560 feed信息流', 'template_id' => 65, 'site_set' => 'SITE_SET_MOBILE_INNER'),
        array('name' => '1000*560 Feed轮播广告-长形', 'template_id' => 271, 'site_set' => 'SITE_SET_MOBILE_INNER'),
        array('name' => '640×360沉浸视频流视频广告-5s创意', 'template_id' => 351, 'site_set' => 'SITE_SET_MOBILE_INNER'),
        array('name' => '640×360沉浸视频流视频广告-15s创意', 'template_id' => 351, 'site_set' => 'SITE_SET_MOBILE_INNER'),
    ),
    //广点通广告、推广计划、创意状态定义
    'gdt_configured_status' => array(
        'AD_STATUS_NORMAL' => array('value' => '正常', 'color' => 'green'),
        'AD_STATUS_PENDING' => array('value' => '待审核', 'color' => 'gray'),
        'AD_STATUS_DENIED' => array('value' => '审核不通过', 'color' => 'red'),
        'AD_STATUS_FROZEN' => array('value' => '冻结', 'color' => 'red'),
        'AD_STATUS_SUSPEND' => array('value' => '挂起', 'color' => 'gray'),
        'AD_STATUS_PREPARE' => array('value' => '准备状态', 'color' => 'gray'),
        'AD_STATUS_DELETED' => array('value' => '删除', 'color' => 'red'),
    ),
    'gdt_campaign_speed_mode_type' => array(
        'SPEED_MODE_STANDARD' => '标准投放',
        'SPEED_MODE_FAST' => '加速投放',
    ),
    'gdt_creative_selection_type' => array(
        'CREATIVE_SELECTION_TYPE_BY_TURNS' => '轮询播放',
        'CREATIVE_SELECTION_TYPE_AUTO_OPTIMIZED' => '自动优化播放',
    ),
    'gdt_bid_type' => array(
        'BID_TYPE_CPC' => 'CPC扣费',
        'BID_TYPE_CPM' => 'CPM扣费'
    ),
    'creative_combination_type' => array(
        'COMBINATION_TYPE_NORMAL' => '普通广告',
        'COMBINATION_TYPE_CAROUSEL' => '集装箱广告',
        'COMBINATION_TYPE_DYNAMIC' => '动态创意'
    ),
    'creative_template_refs' => array(
        '65' => array(
            'image' => array('width' => 1000, 'height' => 560, 'file_size_KB_limit' => 90, 'file_format' => array('jpg', 'jpeg', 'png'))
        ),
        '271' => array(
            'image' => array('width' => 1000, 'height' => 560, 'file_size_KB_limit' => 90, 'file_format' => array('jpg', 'jpeg', 'png'))
        ),
        '351' => array(
            'image' => array('width' => 640, 'height' => 360, 'file_size_KB_limit' => 50, 'file_format' => array('jpg', 'jpeg', 'png')),
            'image2' => array('width' => 300, 'height' => 300, 'file_size_KB_limit' => 50, 'file_format' => array('jpg', 'jpeg', 'png')),
            'video' => array('width' => 640, 'height' => 360, 'file_size_KB_limit' => 1536, 'file_format' => array('mp4')),
        ),
    ),
    'gdt_oauth_url'=>'/api/gdtauth/authorizationCode',
    'gdt_token_url'=>'/api/gdtauth/token',
    'gdt_developers_oauth_token_url' => "https://developers.e.qq.com/oauth/token?timestamp={timestamp}&nonce={nonce}&client_id={client_id}&client_secret={client_secret}&authorization_code={authorization_code}&grant_type=authorization_code",
);
