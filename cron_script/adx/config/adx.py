#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os

SQL_PATH = "sql"
LOG_PATH = "log"

# 循环一次休眠时间
SLEEP_SECOND = 30

# 循环n次后重新load一次未导入数据
LOAD_COUNT = 10

# 数据处理一次最大长度
LIMIT_COUNTS = 50000

# 队列处理一次最大长度
RQ_ADX_LIMIT = 2000
RQ_ADX_CLIENT_LIMIT = 2000

MYSQL_BIN = "/usr/bin/mysql"

if os.path.exists('/home/ad_tc') is False:
    SLEEP_SECOND = 2
    RQ_ADX_LIMIT = 1
    RQ_ADX_CLIENT_LIMIT = 10

# 位置表
TABLE_POSITION = "config_import_time"
# 获取ip地址api
IpApiUrl = "http://ip.lua.uu.cc/?ip="
# 事件类型（修改时要同步KPI定义）
ADX_EVENT_TYPE = {
    1: "request",  # 请求配置
    2: "request_success",  # 请求配置成功
    3: "download",  # 下载资源
    4: "download_ok",  # 下载资源成功
    5: "impressions",  # 插页展示(视频开始播放)
    6: "clicks",  # 点击
    7: "effect_impressions",  # 插页关闭(视频落地页展示)
    8: "play_finish",  # 触发奖励
    # 9: "resume",  # 重新观看(视频)
    10: "redirect_browser",  # 跳转浏览器
    11: "redirect_internal_browser",  # 跳转内建浏览器
    12: "redirect_shop",  # 跳转商店
    13: "redirect_internal_shop",  # 跳转商店内页
    14: "sdk_impressions",  # sdk展示
    # 15: "init",  # 初始化
    16: "skips",  # 跳过
    41: "download_app",  # 下载APP
    42: "download_app_ok",  # 下载APP成功
    43: "install_app",  # 安装APP
    44: "install_app_ok",  # "安装APP成功"
    51: "dsp_request",  # dsp请求
    52: "dsp_response",  # dsp响应
    53: "dsp_win",  # dsp竞价成功
    54: "dsp_notice"  # "dsp通知"
}

ADX_CHANNEL_EVENT_TYPE = {
    1: "request",  # 请求配置
    2: "request_success",  # 请求配置成功
    5: "impressions",  # 插页展示(视频开始播放)
    6: "clicks",  # 点击
    7: "effect_impressions",  # 插页关闭(视频落地页展示)
    8: "play_finish",  # 触发奖励
}

ADX_DAU_EVENT_TYPE = {
    5: "user_total",  # 触达用户
    15: "dau_user"  # 活跃用户
}

# 客户端上报队列定义
RQ_ADX_CLIENT_DATA = "adx_RQ:adx_data"
RQ_ADX_CLIENT_CHARGE = "adx_RQ:adx_charge"
RQ_ADX_NOTICE = "adx_RQ:adx_dsp_notice"

# AdxArea
TABLE_AREA_STAT = "adx_stat_area"
FIELDS_AREA_STAT = ['id', 'provider_id', 'dsp_id', 'app_key', 'block_id', 'ad_type', 'ad_sub_type', 'cid', 'brand', 'model', 'event_type', 'net_type',
                    'operator', 'platform', 'uuid', 'server_time']
FIELDS_AREA_STAT_SRC = FIELDS_AREA_STAT + ['client_ip']
FIELDS_AREA_STAT_DES = FIELDS_AREA_STAT + ['country', 'province', 'city']

IB_TABLE_AREA_STAT = "adx_stat_area"

# HouseAd上报统计字段
TABLE_HOUSEAD_STAT = "housead_stat"
FIELDS_HOUSEAD_STAT = ['ad_id', 'originality_id', 'block_id', 'app_key', 'ad_type', 'brand', 'model', 'event_type', 'net_type', 'charge_type',
                       'price', 'imei', 'imsi', 'operator', 'platform', 'resolution', 'app_version', 'sdk_version', 'client_ip', 'created_time',
                       'uuid']

# 客户端上报统计字段
TABLE_CLIENT_STAT = "adx_stat_client"
FIELDS_CLIENT_STAT = ['provider_id', 'dsp_id', 'bid_id', 'out_bid_id', 'app_key', 'block_id', 'ad_id', 'originality_id', 'ad_type', 'cid', 'brand',
                      'model', 'event_type', 'net_type', 'price', 'currency', 'charge_type', 'imei', 'imsi', 'operator', 'platform', 'resolution',
                      'uuid', 'app_version', 'sdk_version', 'dsp_version', 'client_ip', 'server_time', 'ad_sub_type', 'used_time', 'vh', 'point_x',
                      'point_y']

IB_TABLE_CLIENT_STAT = TABLE_CLIENT_STAT
IB_FIELDS_CLIENT_STAT = ['id'] + FIELDS_CLIENT_STAT

TABLE_REPORT_STAT = "intergration_report_global"
FIELDS_REPORT_CLIENT_STAT = ['id', 'provider_id', 'dsp_id', 'bid_id', 'app_key', 'block_id', 'ad_id', 'ad_type', 'app_version', 'sdk_version',
                             'ad_sub_type', 'event_type', 'price', 'currency', 'platform', 'server_time', 'used_time']

FIELDS_REPORT_SERVER_STAT = ['id', 'provider_id', 'dsp_id', 'bid_id', 'app_key', 'block_id', 'ad_type', 'event_type', 'platform', 'server_time']

# 报表
FIELDS_REPORT_CLIENT_CHANNEL_STAT = ['id', 'app_key', 'cid', 'ad_type', 'event_type', 'platform', 'server_time']
TABLE_REPORT_CHANNEL_STAT = "intergration_report_country_channel"
FIELDS_CHANNEL_DIMS = ['app_key', 'ads_id', 'channel_id', 'country', 'area', 'intergration_type', 'date_of_log', 'platform', 'block_id']
FIELDS_CHANNEL_KPIS = ['impressions', 'effect_impressions', 'clicks', 'play_finish', 'request', 'request_success']

# 报表字段
FIELDS_DIMS = ['ssp_id', 'ads_id', 'app_key', 'block_id', 'ad_id', 'intergration_type', 'app_version', 'sdk_version', 'platform', 'date_of_log',
               'hour_of_log']

FIELDS_CLIENT_KPIS = ["request", "request_success", "download", "download_ok", "impressions", "effect_impressions", "sdk_impressions", "play_finish",
                      "clicks", "redirect_browser", "redirect_internal_browser", "redirect_shop", "redirect_internal_shop", "download_app",
                      "download_app_ok", "install_app", "install_app_ok", "amount", "skips", "used_time"]

FIELDS_SERVER_KPIS = ["dsp_request", "dsp_response", "dsp_win", "dsp_notice"]

# 服务端上报队列定义
RQ_ADX_SERVER_DATA = "adx_RQ:adx_dsp_event"
RQ_ADX_SERVER_LIMIT = 2000

# 服务端上报统计字段
TABLE_SERVER_STAT = "adx_dsp_event"
FIELDS_SERVER_STAT = ['provider_id', 'bid_id', 'dsp_id', 'event_type', 'app_key', 'block_id', 'platform', 'ad_type', 'server_time']

IB_TABLE_SERVER_STAT = "adx_stat_server"
IB_FIELDS_SERVER_STAT = ['id'] + FIELDS_SERVER_STAT
