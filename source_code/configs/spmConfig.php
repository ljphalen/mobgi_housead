<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/11/22
 * Time: 20:31
 */

# 回调宏参数
$config['MACROS']['callback_macros'] = array(
    'idfa',    # Apple Advertiser ID
    'imei',    # Android Device ID
    'muid',    # Device ID Hash
    'midfa',   # Apple Advertiser ID Hash
    'androidid', # Android ID
    'clickid', # The Ad unique click identifier
    'ld_sub1', # Custom parameter 1
    'ld_sub2', # Custom parameter 2
    'ld_sub3', # Custom parameter 3
    'ld_sub4', # Custom parameter 4
    'ld_sub5',  # Custom parameter 5
    "af_siteid", # 历史遗留参数,调整后可以删除
    "af_sub1", #历史遗留参数,调整后可以删除
    "af_sub2", #历史遗留参数,调整后可以删除
    'sub_channel', # The sub channel ID
    'ip',
    'mac',
    'click_time',
    'callback'
);

# 重定向透传宏参数
$config['MACROS']['locate_macros'] = array(
    'locate_sub1', # Custom parameter 1
    'locate_sub2', # Custom parameter 2
    'locate_sub3', # Custom parameter 3
    'locate_sub4', # Custom parameter 4
    'locate_sub5'  # Custom parameter 5
);

$config['MACROS']['backend_macros'] = array(
    'clickid', # The Ad unique click identifier
    'ld_sub1', # Custom parameter 1
    'ld_sub2', # Custom parameter 2
    'ld_sub3', # Custom parameter 3
    'ld_sub4', # Custom parameter 4
    'ld_sub5',  # Custom parameter 5
    'sub_channel', # The sub channel ID
    'callback'
);

$config['MOBER'] = array(
    'guangdiantong' => 'http://t.gdt.qq.com/conv/app/%s/conv?v={data}&conv_type=%s&app_type=%s&advertiser_id=%s',
    'gdtcgi' => 'https://t.gdt.qq.com/conv/app/%s/conv?click_id=%s&muid=%s&appid=%s&conv_time={time}&client_ip={ip}&encstr={encstr}&encver=1.0&advertiser_id=%s&app_type=%s&conv_type=%s',
    'wechat' => 'http://t.gdt.qq.com/conv/app/%s/conv?v={data}&click_id=%s&muid=%s&appid=%s&conv_type=%s&app_type=%s&advertiser_id=%s',
);

$config['GDTHOST'] = array(
    'gdtcgi'=> 'https://t.gdt.qq.com',
    'guangdiantong'=> 'http://t.gdt.qq.com',
    'wechat'=>'http://t.gdt.qq.com',
    'zhihuituiapi'=>'http://jump.t.l.qq.com'
);

$config['DLOG'] = array(
    'host' => 'dlog.ildyx.com',
    'port' => '4455'
);

$config['MONITOR_PLATFORM'] = array(
    'type' => array(
        '1' => '第三方平台',
        '2' => '广告主'
    ),
);

$config['MONITOR_SETTING'] = array(
    'track_time' => array(
        '12' => '0.5天',
        '24' => '1天',
        '48' => '2天',
        '72' => '3天',
        '96' => '4天',
        '120' => '5天',
        '144' => '6天',
        '168' => '7天',
    ),
    'track_status' => array(
        '0' => '关闭',
        '1' => '开启',
    ),
    'delivery_type' => array(
        '1' => '普通',
        '2' => '代理(SDK不植入)',
        '3' => '代理(SDK植入)',
    ),
    'track_type' => array(
        'api' => 'api对接',
        'shortlink' => '追踪短链',
    ),
);

$config['MONITOR_STATUS'] = array(
    'checkpoint' => array(
        '1102' => '注册回调',
        '1001' => '打开回调'
    ),
    'activity_status' => array(
        '2' => '投放中',
        '3' => '暂停中',
        '4' => '接收不回调'
    ),
    'shortlink_status' => array(
        'OFF' => '关闭回调',
        'ON' => '开启回调'
    ),
    'channel_status' => array(
        'OFF' => '暂停合作',
        'ON' => '正在合作',
    ),
    'channel_status' => array(
        'OFF' => '暂停合作',
        'ON' => '正在合作',
    )
);

$config['MONITOR_TOOLS'] = array(
    'active_status' => array(
        '0' => '未激活',
        '1' => '已激活',
        '-1' => '被清除的激活'
    ),
    'callback_status' => array(
        '0' => '未回调',
        '1' => '已回调',
        '2' => '异常激活(系统判定属于二次激活设备)',
        '3' => '回调超时'
    ),
    'shortlink_status' => array(
        'OFF' => '关闭回调',
        'ON' => '开启回调'
    ),
    'channel_status' => array(
        'OFF' => '暂停合作',
        'ON' => '正在合作',
    ),
    'monitor_platform' => array(
        'Monitor' => '投放监控',
        'Data' => '数据报表',
        'Housead' => 'House AD'
    ),
    'alarm_type' => array(
        'phone' => '短信',
        'email' => '邮件'
    ),
);

$config['QUEUES']['track'] = array(
    'COMMON',
    'IOS10',
    'unity',
    'toyblast_share',
    'taptica',
    'xinzhixun',
    'domob',
    'shike',
    'limei',
    'tieba',
    'adsage',
    'mobvista',
    'youmi',
    'changba',
    'guangdiantong',
    'anwo',
    'youmi_shipin',
    'youmi_chapin',
    'xuangu',
    'behe',
    'guohe',
    'wanba',
    'applovin',
    'shunfei',
    'feiyu',
    'haoqu',
    'macs',
    'testin',
    'lanrentingshuapi',
    'glispa'
);

$config['OAUTH_API'] = array(
    'GDT_CODE_URL' => 'https://developers.e.qq.com/oauth/authorize?client_id={client_id}&redirect_uri=https://spm.mobgi.com/tools/gdtaccesstoken&state={state}', #&scope={scope}
    'GDT_ACCESS_TOKEN_BY_CODE_URL' => 'https://api.e.qq.com/oauth/token?client_id={client_id}&client_secret={client_secret}&grant_type=authorization_code&authorization_code={authorization_code}&redirect_uri=https://spm.mobgi.com/tools/gdtaccesstoken',
    'GDT_ACCESS_TOKEN_BY_REFRESH_TOKEN_URL' => 'https://api.e.qq.com/oauth/token?client_id={client_id}&client_secret={client_secret}&grant_type=refresh_token&refresh_token={refresh_token}',
);
$config['OAUTH_APP'] = array(
    'client_id' => 1106075364,
    'client_secret' => 'bLesQJjhrS3ObOy5',
);

# 查询激活记录redis缓存是否开启
$config['ACTIVE_REDIS_STATUS'] = TRUE;
# 查询激活UDID记录redis缓存是否开启
$config['UDID_REDIS_STATUS'] = TRUE;
# 查询激活ADID记录redis缓存是否开启
$config['ADID_REDIS_STATUS'] = TRUE;

return $config;