#!/usr/bin/env python
# _*_ coding:utf-8 _*_
import config.db as configDB

LIMIT_COUNTS = 100000
LOGPATH = "log/"
SQLPATH = "sql/"

SLEEP_TIMES = 3
SLEEP_SECOND = 10
# src:dec
INFORBRIGHT_TABLE = {
    'video_ads_stat': 'video_ads_stat',
    'video_ads_area_data': 'video_ads_area_data',
    'adx_stat_client': 'adx_stat_client',
    'adx_stat_client_bak20170622': 'adx_stat_client',
    'original_data_bak20170622': 'original_data',
}

FIELDS_SRC = {
    'original_data_bak20170622': ['id', 'ad_unit_id', 'ad_id', 'originality_id', 'block_id', 'app_key', 'ad_type', 'brand', 'model', 'event_type',
                                  'net_type', 'charge_type', 'price', 'imei', 'imsi', 'operator', 'platform', 'resolution', 'uuid', 'app_version',
                                  'sdk_version', 'client_ip', 'created_time', '0 as ad_sub_type', '0 as used_time', '0 as vh', '-1 as point_x',
                                  '-1 as point_y'],
    'adx_stat_client_bak20170622': ['id', 'provider_id', 'dsp_id', 'bid_id', 'out_bid_id', 'app_key', 'block_id', 'ad_id', 'originality_id',
                                    'ad_type', 'cid', 'brand', 'model', 'event_type', 'net_type', 'price', 'currency', 'charge_type', 'imei', 'imsi',
                                    'operator', 'platform', 'resolution', 'uuid', 'app_version', 'sdk_version', 'dsp_version', 'client_ip',
                                    'server_time', '0 as ad_sub_type', '0 as used_time', '0 as vh', '-1 as point_x', '-1 as point_y'],
}
FIELDS_DES = {
    'adx_stat_client': ['id', 'provider_id', 'dsp_id', 'bid_id', 'out_bid_id', 'app_key', 'block_id', 'ad_id', 'originality_id', 'ad_type', 'cid',
                        'brand', 'model', 'event_type', 'net_type', 'price', 'currency', 'charge_type', 'imei', 'imsi', 'operator', 'platform',
                        'resolution', 'uuid', 'app_version', 'sdk_version', 'dsp_version', 'client_ip', 'server_time', 'ad_sub_type', 'used_time',
                        'vh', 'point_x', 'point_y'],
    'original_data': ['id', 'ad_unit_id', 'ad_id', 'originality_id', 'block_id', 'app_key', 'ad_type', 'brand', 'model', 'event_type', 'net_type',
                      'charge_type', 'price', 'imei', 'imsi', 'operator', 'platform', 'resolution', 'uuid', 'app_version', 'sdk_version', 'client_ip',
                      'created_time', 'ad_sub_type', 'used_time', 'vh', 'point_x', 'point_y']
}

CONFIG_SRC = {
    'adx_stat_client_bak20170622': configDB.MYSQL_BH_ADX,
    'original_data_bak20170622': configDB.MYSQL_BH_HOUSEAD
}
CONFIG_DES = {
    'adx_stat_client': configDB.MYSQL_BH_ADX,
    'original_data': configDB.MYSQL_BH_HOUSEAD
}
