#!/usr/bin/env python
# -*- coding:utf-8 -*-

LIMIT_COUNTS = 100000
SLEEP_SECOND = 60
MYSQL_BIN = "/usr/bin/mysql"

FIELDS_SRC = {
    'video_ads_stat': "id, app_version, version, consumerkey, cid, eventtype, server_time, uuid, sdk_version, imei, client_ip, device_brand, "
                      "device_model,operator, blockid, udid, os, android_id, mober, intergration_type",
    'video_ads_area_data': "id,consumerkey,cid,eventtype,server_time,uuid,country,area,device_brand,operator,blockid,os,mober,intergration_type",
}
FIELDS_DEC = {
    'video_ads_stat_2016': FIELDS_SRC['video_ads_stat'],
    'video_ads_stat_2017_1': FIELDS_SRC['video_ads_stat'],

    'video_ads_area_data_2016': FIELDS_SRC['video_ads_area_data']
}

# infobright
IB_STAT_59 = {
    "host": "172.16.201.59",
    "port": 5029,
    "db": "BH_ad_stats",
    "user": "ad_system",
    "passwd": "wY7DTW6aBXV9ljG_g4sE"
}

IB_STAT_56 = {
    "host": "172.16.201.56",
    "port": 5029,
    "db": "bh_ad_stats",
    "user": "ad_system",
    "passwd": "wY7DTW6aBXV9ljG_g4sE"
}

IB_STAT_210 = {
    "host": "172.16.150.210",
    "port": 5029,
    "db": "bh_ad_stats",
    "user": "ad_system",
    "passwd": "wY7DTW6aBXV9ljG_g4sE"
}

TRANSFER_59_56 = {
    'name': 'stat_59_56',
    'table_map': {
        'video_ads_stat': 'video_ads_stat_2017_1',
        'video_ads_area_data': 'video_ads_area_data_2016',
    },
    'src_conn': IB_STAT_59,
    'dec_conn': IB_STAT_56
}

RUN_CONIF = TRANSFER_59_56
