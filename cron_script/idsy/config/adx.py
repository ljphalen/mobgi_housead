#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os

workId = 1

# 循环一次休眠时间
SLEEP_SECOND = 60

# IP缓存时间
IP_CACHE_SECOND = 86400
# USER缓存时间
USER_CACHE_SECOND = 86400

# 循环n次后重新load一次未导入数据
LOAD_COUNT = 10

# 数据处理一次最大长度
LIMIT_COUNTS = 20000

# 用户分表个数
USER_TABLE_COUNTS = 100

# 队列处理一次最大长度
RQ_ADX_LIMIT = 20000
RQ_ADX_CLIENT_LIMIT = 20000

MYSQL_BIN = "/usr/bin/mysql"

BIT_LENGTH = 1000000000000

IS_PROD = True

if os.path.exists('/home/ad_tc') is False:
    SLEEP_SECOND = 5
    RQ_ADX_LIMIT = 100
    RQ_ADX_CLIENT_LIMIT = 2
    USER_TABLE_COUNTS = 3
    IS_PROD = False
# 位置表
TABLE_POSITION = "config_position"
# 获取ip地址api
IpApiUrl = "http://ip.lua.uu.cc/?ip="
# 事件类型（修改时要同步KPI定义）
ADX_EVENT_TYPE = {
    1: "request",  # 请求配置
    2: "request_ok",  # 请求配置成功
    3: "download",  # 下载资源
    4: "download_ok",  # 下载资源成功
    5: "impressions",  # 插页展示(视频开始播放)
    6: "clicks",  # 点击
    7: "closes",  # 插页关闭(视频落地页展示)
    8: "reward",  # 触发奖励
    9: "resume",  # 重新观看(视频)
    10: "redirect_browser",  # 跳转浏览器
    11: "redirect_internal_browser",  # 跳转内建浏览器
    12: "redirect_shop",  # 跳转商店
    13: "redirect_internal_shop",  # 跳转商店内页
    14: "sdk_impressions",  # sdk展示
    15: "inits",  # 初始化
    16: "skips",  # 跳过
    # 17: "cache_ready",
    1700: "cache_ready_ok",
    1701: "cache_ready_fail",
    1702: "cache_ready_fail",
    1703: "cache_ready_fail",
    1704: "cache_ready_fail",
    1705: "cache_ready_fail",
    1706: "cache_ready_fail",
    18: "exits",  # 退出
    41: "download_app",  # 下载APP
    42: "download_app_ok",  # 下载APP成功
    43: "install_app",  # 安装APP
    44: "install_app_ok",  # "安装APP成功"
    45: "active",  # "app激活成功"

}
SERVER_EVENT_TYPE = {
    51: "dsp_request",  # dsp请求
    52: "dsp_response",  # dsp响应
    53: "dsp_win",  # dsp竞价成功
    54: "dsp_notice"  # "dsp通知"
}

HOUSEAD_EVENT_TYPE = {
    1: "request",  # 请求配置
    2: "request_ok",  # 请求配置成功
    3: "download",  # 下载资源
    4: "download_ok",  # 下载资源成功
    5: "impressions",  # 插页展示(视频开始播放)
    6: "clicks",  # 点击
    7: "closes",  # 插页关闭(视频落地页展示)
    8: "rewards",  # 触发奖励
    9: "resume",  # 重新观看(视频)
    10: "redirect_browser",  # 跳转浏览器
    11: "redirect_internal_browser",  # 跳转内建浏览器
    12: "redirect_shop",  # 跳转商店
    13: "redirect_internal_shop",  # 跳转商店内页
    14: "sdk_impressions",  # sdk展示
    15: "inits",  # 初始化
    16: "skips",  # 跳过
    # 17: "cache_ready",
    1700: "cache_ready_ok",
    1701: "cache_ready_fail",
    1702: "cache_ready_fail",
    1703: "cache_ready_fail",
    1704: "cache_ready_fail",
    1705: "cache_ready_fail",
    1706: "cache_ready_fail",

    18: "exits",  # 退出
    41: "download_app",  # 下载APP
    42: "download_app_ok",  # 下载APP成功
    43: "install_app",  # 安装APP
    44: "install_app_ok",  # "安装APP成功"
    45: "active",  # "app激活成功"
}

CHARGE_EVENT_TYPE = {
    5: "views",  # 插页展示(视频开始播放)
    6: "clicks",  # 点击
    45: "actives",  # "app激活成功"
}

LTV_EVENT_TYPE = {
    5: "impressions",  # 插页展示(视频开始播放)
    6: "clicks",  # 点击
    45: "actives",  # "app激活成功"
}

USER_EVENT_TYPE = {
    5: "impressions",  # 插页展示(视频开始播放)
    6: "clicks",  # 点击
    45: "actives",  # "app激活成功"
    15: "inits",  # "初始化"
    18: "exits",  # "退出"
}

# 试玩统计类型
TRY_EVENT_TYPE = {
    01: "requests",  # 请求配置
    02: "requests_ok",  # 请求配置成功
    03: "downloads",  # 下载试玩app (下载资源)
    04: "downloads_ok",  # 下载试玩app成功(下载资源成功)
    05: "impressions",  # 展示角标(所有trialAppKey 按‘，’隔开，放在额外字段eventValue里面上报)
    06: "clicks",  # 试玩app被点击
    15: "inits",  # 初始化SDK
    18: "exits",  # 宿主app退出（或切到后台超过1分钟）
    21: "starts",  # 试玩app启动
    22: "quits",  # 试玩app退出
    43: "installs",  # 安装试玩app
    44: "installs_ok",  # 安装试玩app成功
}

ADX_CHANNEL_EVENT_TYPE = {
    1: "request",  # 请求配置
    2: "request_success",  # 请求配置成功
    5: "impressions",  # 插页展示(视频开始播放)
    6: "clicks",  # 点击
    7: "effect_impressions",  # 插页关闭(视频落地页展示)
    8: "play_finish",  # 触发奖励
}

# 计费类型
CHARGE_TYPE_CPA = 3
CHARGE_TYPE_CPC = 2
CHARGE_TYPE_CPM = 1

# 1.RMB 2.美元
CURRENCY_RMB = 1
CURRENCY_USD = 2
EXCHANGE_RATE_USD_RMB = 6.5

EVENT_INIT = 15
EVENT_SKIP = 16
EVENT_EXIT = 18
EVENT_IMPRESSION = 5

ADX_DAU_EVENT_TYPE = {
    EVENT_IMPRESSION: "total_user",  # 触达用户
    EVENT_INIT: "user_dau"  # 活跃用户
}

AD_TYPE_VIDEO = 1
AD_TYPE_PIC = 2
AD_TYPE_CUSTOM = 3
AD_TYPE_SPLASH = 4
AD_TYPE_NATIVE = 5

AD_TYPE = {
    AD_TYPE_VIDEO: "video",
    AD_TYPE_PIC: "pic",
    AD_TYPE_CUSTOM: "custom",
    AD_TYPE_SPLASH: "splash",
    AD_TYPE_NATIVE: "native"
}

# 上报队列定义
RQ_ADX_CLIENT = "adx_RQ:ad_client"
RQ_ADX_CHARGE = "adx_RQ:ad_charge"
RQ_ADX_SERVER = "adx_RQ:adx_dsp_event"

# 激活队列
RQ_ADX_ACITIVE = "adx_RQ:active_list"

# 缓存定义
REDIS_ORIGINFO_PRE = "adx_origid_"

# 数据落地
TABLE_MIN_CHARGE = "adx_charge_minute"
TABLE_DAY_CHARGE = "adx_charge_day"

# 客户端上报

TABLE_STAT_CLIENT = "ad_client"
TABLE_STAT_CLIENT_WHITELIST = "ad_client_whitelist"
TABLE_STAT_CHARGE = "ad_charge"
TABLE_STAT_SERVER = "ad_server"
TABLE_MID_HOUR = "ad_mid_hour"
TABLE_MID_FILLRATE = "ad_mid_fillrate"
TABLE_MID_DSP = "ad_mid_dsp2"
TABLE_MID_HOUSEAD = "ad_mid_housead"
TABLE_MID_TRY = "ad_mid_try"
TABLE_MID_TEST = "ad_mid_test"
TABLE_MID_IP = "ad_mid_ip"
TABLE_CHARGE_MIN = "adx_charge_minute"
TABLE_CHARGE_DAY = "adx_charge_day"

TABLE_MID_USERS = "ad_mid_users"

TABLE_REPORT_LTV = "report_ltv"
TABLE_REPORT_TIMES = "report_times"
TABLE_REPORT_CITY = "report_city"
TABLE_REPORT_HOUR = "report_hour"
TABLE_REPORT_TEST = "report_test"
TABLE_REPORT_DSP = "report_dsp"
TABLE_REPORT_DAY = "report_day"
TABLE_REPORT_TRY = "report_try"
TABLE_REPORT_TRY_HOUR = "report_try_hour"

TABLE_REPORT_HOUSEAD = "report_housead"
TABLE_WATCHING_TIME = "report_watch_times"

TABLE_REPORT_DAU = "report_dau"

FIELDS = {
    "ad_client": ['id', 'ssp_id', 'ads_id', 'orig_id', 'bid_id', 'app_key', 'pos_key', 'ad_type', 'ad_sub_type', 'cid', 'brand', 'model', 'operator',
                  'net_type', 'event_type', 'event_sort', 'event_time', 'event_value', 'used_time', 'imei', 'imsi', 'platform', 'uuid', 'app_version',
                  'sdk_version', 'client_ip', 'server_time', 'client_time', 'charge_type', 'currency', 'price', 'vh', 'point_x', 'point_y', 'width',
                  'height', 'ver', 'session_id', 'out_bit_id', 'user_type', 'config_id', 'try_key'],
    "ad_server": ['id', 'provider_id', 'bid_id', 'dsp_id', 'event_type', 'app_key', 'block_id', 'platform', 'ad_type', 'server_time'],
    "ad_charge": ['id', 'ssp_id', 'ads_id', 'orig_id', 'bid_id', 'app_key', 'pos_key', 'ad_type', 'event_type', 'imei', 'uuid', 'client_ip',
                  'server_time', 'charge_type', 'currency', 'price', 'ver'],
    "ad_mid_users": ['id_range', 'ads_id', 'app_key', 'ad_type', 'pos_key', 'country', 'province', 'city', 'cid', 'gid', 'platform', 'session_id',
                     'event_type', 'event_sort', 'event_time', 'is_custom', 'app_version', 'sdk_version', 'uuid', 'user_id', 'is_new', 'create_date',
                     'action_date', 'action_hour', 'action_time'],
    "ad_mid_hour": ['id_range', 'ssp_id', 'ads_id', 'app_key', 'pos_key', 'ad_type', 'cid', 'platform', 'app_version', 'sdk_version', 'server_time',
                    'event_type', 'event_value', 'event_count', 'event_time'],
    "ad_mid_housead": ['id_range', 'orig_id', 'app_key', 'pos_key', 'ad_type', 'ad_sub_type', 'platform', 'server_time', 'charge_type', 'currency',
                       'amount', 'event_type', 'event_value', 'event_count'],
    "ad_mid_test": ['id_range', 'ssp_id', 'ads_id', 'app_key', 'pos_key', 'ad_type', 'cid', 'is_custom', 'gid', 'brand', 'model', 'operator',
                    'net_type', 'platform', 'app_version', 'sdk_version', 'server_time', 'event_type', 'event_sort', 'event_value', 'event_time',
                    'flow_id', 'conf_id', 'user_type', 'create_date', 'action_date', 'user_id', 'session_id', 'imei', 'uuid', 'out_bit_id'],
    "ad_mid_dsp2": ['id_range','dsp_id','event_type','app_key','block_id','platform','ad_type','server_time','event_count'],
    "ad_mid_ip": ['id_range', 'app_key', 'platform', 'country', 'province', 'city', 'server_time', 'event_type', 'event_count', 'event_time'],
    "ad_mid_try": ['id_range', 'try_key', 'app_key', 'pos_key', 'ad_type', 'country', 'province', 'city', 'cid', 'gid', 'unit_id', 'ad_id', 'orig_id',
                   'platform', 'session_id', 'event_type', 'event_sort', 'event_time', 'is_custom', 'app_version', 'sdk_version', 'uuid', 'net_type',
                   'operator', 'ver', 'user_id', 'is_new', 'create_date', 'server_time', 'action_time'],
    "ad_mid_fillrate": ['id_range','ssp_id', 'ads_id', 'app_key', 'ad_type', 'pos_key', 'cid', 'gid','platform','is_custom','app_version', 'sdk_version', 'server_time',
                    'cache_success', 'cache_fail', 'cache_show'],
}

# 报表
DIMS_HOUSEAD = ['ad_id', 'unit_id', 'account_id', 'originality_id', 'app_key', 'pos_key', 'ad_type', 'ad_sub_type', 'platform', 'days', 'hours']
DIMS_TRY = ['originality_id', 'app_key', 'pos_key', 'try_key', 'ad_type', 'channel_gid', 'platform', 'app_version', 'sdk_version', 'days', 'hours']
DIMS_HOUR = ['ssp_id', 'ads_id', 'app_key', 'pos_key', 'ad_type', 'channel_gid', 'platform', 'app_version', 'sdk_version', 'days', 'hours',
             'is_custom']
DIMS_DSP = ['ads_id', 'app_key', 'pos_key', 'ad_type', 'platform', 'days', 'hours']
DIMS_TEST = ['ssp_id', 'ads_id', 'app_key', 'pos_key', 'ad_type', 'channel_gid', 'platform', 'app_version', 'sdk_version', 'days', 'hours',
             'is_custom', 'flow_id', 'conf_id']

DIMS_DAY = ['ssp_id', 'ads_id', 'app_key', 'pos_key', 'ad_type', 'channel_gid', 'platform', 'app_version', 'sdk_version', 'days', 'is_custom']
DIMS_MONTH = ['app_key', "month", 'years', 'app_type','is_custom','platform']
DIMS_CITY = ['app_key', 'platform', 'days', 'country', 'province']
DIMS_LTV = ['app_key', 'platform', 'ads_id', 'channel_gid', 'ad_type', 'create_date', 'action_date', 'rday']
DIMS_TIMES = ['app_key', 'platform', 'ads_id', 'pos_key', 'channel_gid', 'ad_type', 'days', 'times']
DIMS_WATCHINGTIME = ['app_key', 'ad_type', 'times', 'per_min', 'days','hours']

KPIS_HOUR = ["request", "request_ok", "download", "download_ok", "impressions", "effective_impressions", "clicks", "closes", "skips", "inits",
             "exits", "reward", "sdk_impressions", "redirect_browser", "redirect_internal_browser", "redirect_shop",
             "redirect_internal_shop", "download_app", "download_app_ok", "install_app", "install_app_ok", "skip_stay_time", "exit_stay_time",
             "cache_ready_ok", "cache_ready_fail"]


KPIS_EXITHOUR = ["exits", "effective_exits", "exit_stay_time"]

KPIS_FILLRATE = ["cache_fail", "cache_success", "cache_show"]

#有效退出次数和填充率单独脚本执行
KPIS_DAY = KPIS_HOUR + ["effective_exits"] + KPIS_FILLRATE

KPIS_TEST = ["request", "request_ok", "download", "download_ok", "impressions", "effective_impressions", "clicks", "closes", "skips", "inits",
             "exits", "reward", "sdk_impressions", "redirect_browser", "redirect_internal_browser", "redirect_shop", "redirect_internal_shop",
             "download_app", "download_app_ok", "install_app", "install_app_ok", "skip_stay_time", "exit_stay_time", "cache_ready_ok",
             "cache_ready_fail"]
KPIS_EXITHOUR =["exits","effective_exits","exit_stay_time"]
KPIS_MONTH = ["ad_income","mau","arpu","game_cover"]
KPIS_CITY = ["request", "request_ok", "download", "download_ok", "impressions", "clicks", "closes", "skips", "play_finish", "sdk_impressions",
             "redirect_browser", "redirect_internal_browser", "redirect_shop", "redirect_internal_shop", "download_app", "download_app_ok",
             "install_app", "install_app_ok", "skip_stay_time", "exit_stay_time", "cache_ready_ok", "cache_ready_fail"]
KPIS_DSP = ['dsp_request','dsp_response','dsp_win','dsp_notice']
KPIS_HOUSEAD = ["request", "request_ok", "download", "download_ok", "impressions", "clicks", "closes", "skips", "rewards", "redirect_browser",
                "redirect_internal_browser", "redirect_shop", "redirect_internal_shop", "download_app", "download_app_ok", "install_app",
                "install_app_ok", "skip_stay_time", "cache_ready_ok", "cache_ready_fail", "amount", "active"]

KPIS_LTV = ["impressions", "clicks", "actives"]
KPIS_TIMES = ["people_count", "total_count"]
KPIS_WATCHINGTIME = ['people_count']

KPIS_TRY = TRY_EVENT_TYPE.values()

# 报表字段
FIELDS_SERVER_KPIS = ["dsp_request", "dsp_response", "dsp_win", "dsp_notice"]

# 报表
FIELDS_REPORT_SERVER_STAT = ['id', 'provider_id', 'ads_id', 'bid_id', 'app_key', 'pos_key', 'ad_type', 'event_type', 'platform', 'server_time']

# 默认值
DEFAULT_VAL = {
    'ssp_id': '0',
    'originality_id': '0',
    'orig_id': '0',
    'bid_id': '-1',
    'ad_sub_type': '0',
    'net_type': '0',
    'used_time': '0',
    'event_sort': '0',
    'event_time': '0',
    'event_value': '0',
    'imsi': '-1',
    'charge_type': '0',
    'currency': '0',
    'price': '0',
    'vh': '0',
    'point_x': '-1',
    'point_y': '-1',
    'width': '0',
    'height': '0',
    'session_id': '0',
    'try_key': '-1',
    'client_time': '0',
    'out_bit_id': '-1',
    'user_type': '0',
    'config_id': '0'
}

