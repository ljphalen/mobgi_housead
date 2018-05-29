#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os

# 客户端上报队列定义
RQ_MOBGI_DATA = "RQ:video_ads_stat"

# 队列处理一次最大长度
RQ_MOBGI_LIMIT = 2000

EVENTTYPE = {
    # video
    "1": 'impressions',
    "3": 'effect_impressions',
    "2": "play_finish",
    "4": "clicks",
    "6": "request",
    "7": "request_success",  # pic
    "8": "impressions",
    "9": 'effect_impressions',
    "10": "clicks",
    "14": "request",
    "13": "request_success",
    "16": "sdk_impressions",  # splash
    "35": "impressions",
    "37": "effect_impressions",
    "36": "clicks",
    "33": "request",
    "34": "request_success",  # native
    "45": "impressions",  # "45": "effect_impressions",
    "46": "clicks",
    "43": "request",
    "44": "request_success"
}

MOBGI_USER_EVENT_TYPE = {
    1: "video_user_total",
    8: "pic_user_total",
    35: "splash_user_total",
    45: "native_user_total",
}

MOBGI_DAU_EVENT_TYPE = {
    1: "user_total",
    15: "dau_user"
}

# 客户端上报统计字段
TABLE_MOBGI_STAT = "video_ads_stat"
FIELDS_MOBGI_STAT = ['app_version', 'version', 'consumerkey', 'cid', 'eventtype', 'server_time', 'uuid', 'sdk_version', 'imei', 'client_ip',
                     'device_brand', 'device_model', 'operator', 'bloc]k[id', 'udid', 'os', 'android_id', 'mober', 'intergration_type']

IB_TABLE_MOBGI_STAT = TABLE_MOBGI_STAT
IB_FIELDS_MOBGI_STAT = ['id'] + FIELDS_MOBGI_STAT
