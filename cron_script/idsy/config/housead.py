#!/usr/bin/env python
# -*- coding:utf-8 -*-

# 数据处理一次最大长度
LIMIT_COUNTS = 50000

CHAREG_TYPE_VIEW = 5
CHAREG_TYPE_CLICK = 6
CHAREG_TYPE_ACTIVE = 45

# 事件类型
EVENT_TYPE = {
    1: "request",  # 请求配置
    2: "request_ok",  # 请求配置成功
    3: "download",  # 下载资源
    4: "download_ok",  # 下载资源成功
    5: "view",  # 展示(开始播放)
    6: "click",  # 点击
    7: "close",  # 关闭
    8: "reward",  # 触发奖励(视频)
    9: "resume",  # 重新观看(视频)
    10: "redirect_browser",  # 跳转浏览器
    11: "redirect_internal_browser",  # 跳转内建浏览器
    12: "redirect_shop",  # 跳转商店
    13: "redirect_internal_shop",  # 跳转商店内页
    # 15: "init",  # 初始化
    16: "skips",  # 跳过
    41: "download_app",  # 下载APP
    42: "download_app_ok",  # 下载APP成功
    43: "install_app",  # 安装APP
    44: "install_app_ok"  # "安装APP成功"
}

# 客户端上报队列定义
RQ_ADX_CLIENT_DATA = "adx_RQ:adx_data"
RQ_ADX_CLIENT_CHARGE = "adx_RQ:adx_charge"
RQ_ADX_NOTICE = "adx_RQ:adx_dsp_notice"

RQ_HOUSE_ORIGINAL = "adx_RQ:housead_data_original"
REDIS_CHARGE_PREFIX = "adx_dsp::"  # 必须_结尾

# 分钟统计表
REDIS_MINUTE_STAT = "adx_minute_stat"
# 缓存origid关联信息
REDIS_ORIGINFO = "adx_origid_"

# HouseAd计费队列
TABLE_CHARGE_STAT = "charge_data"
FIELDS_CHARGE_STAT = ['originality_id', 'uuid', 'created_time', 'event_type', 'charge_type', 'price']

# HouseAd上报统计字段
TABLE_HOUSEAD_STAT = "original_data"
FIELDS_HOUSEAD_STAT = ['ad_unit_id', 'ad_id', 'originality_id', 'block_id', 'app_key', 'ad_type', 'brand', 'model', 'event_type', 'net_type',
                       'charge_type', 'price', 'imei', 'imsi', 'operator', 'platform', 'resolution', 'uuid', 'app_version', 'sdk_version',
                       'client_ip', 'created_time', 'ad_sub_type', 'used_time', 'vh', 'point_x', 'point_y']

IB_TABLE_HOUSEAD_STAT = TABLE_HOUSEAD_STAT
IB_FIELDS_HOUSEAD_STAT = ['id'] + FIELDS_HOUSEAD_STAT

TABLE_REPORT_STAT = "report_base"
FIELDS_REPORT_STAT = ['id', 'provider_id', 'dsp_id', 'bid_id', 'app_key', 'block_id', 'ad_id', 'ad_type', 'event_type', 'price', 'currency',
                      'platform', 'server_time', 'ad_sub_type']

TABLE_ADVERTISER_BATCH_DEDUCTION_DETAIL = "advertiser_batch_deduction_detail"

# 报表字段
FIELDS_HOUSEAD_DIMS = ['originality_id', 'block_id', 'app_key', 'ad_type', 'platform', 'date', 'hour', 'ad_sub_type']
FIELDS_HOUSEAD_DIMS_MORE = ["ad_id", "unit_id", "originality_type", "account_id"]
FIELDS_HOUSEAD_KPIS = EVENT_TYPE.values() + ['amount','used_time']

# load Fields
FIELDS = {
    "advertiser_batch_deduction_detail": ['originality_id', 'create_time', 'price'],
    "stat_minute": ['originality_id', 'uuid', 'created_time', 'charge_type', 'event_type', 'price'],
    "stat_day": ['originality_id', 'uuid', 'created_time', 'charge_type', 'event_type', 'price']
}
