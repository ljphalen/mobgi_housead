#!/usr/bin/env python
# -*- coding:utf-8 -*-

BIT_LENGTH = 1000000000000
LIMIT_COUNTS = 100000
SLEEP_SECOND = 60
MYSQL_BIN = "/usr/bin/mysql"

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
    "db": "bh_adx_stats",
    "user": "ad_system",
    "passwd": "wY7DTW6aBXV9ljG_g4sE"
}

IB_STAT_210 = {
    "host": "172.16.150.210",
    "port": 5029,
    "db": "bh_adx_stats",
    "user": "ad_system",
    "passwd": "wY7DTW6aBXV9ljG_g4sE"
}

TRANSFER = {
    "transfer_ad_client3": {
        'src_conn': IB_STAT_56,
        'dec_conn': IB_STAT_56,
        'src_table': 'ad_client2',
        'dec_table': 'ad_client3',
        'fields': """id,ssp_id,ads_id,orig_id,bit_id,app_key,pos_key,ad_type,ad_sub_type,cid,brand,model,operator,net_type,event_type,
        0 as event_sort,0 as event_time,0 as event_value,event_value as used_time,imei,imsi,platform,uuid,app_version,sdk_version,client_ip,
        server_time,0 as client_time,charge_type,currency,price,vh,point_x,point_y,width,height,ver,'' as session_id""",
    },
    "transfer_ad_client4": {
        'src_conn': IB_STAT_56,
        'dec_conn': IB_STAT_56,
        'src_table': 'ad_client3',
        'dec_table': 'ad_client4',
        'fields': """id,ssp_id,ads_id,orig_id,bit_id,app_key,pos_key,ad_type,ad_sub_type,cid,brand,model,operator,net_type,event_type,event_sort,
        event_time,event_value,used_time,imei,imsi,platform,uuid,app_version,sdk_version,client_ip,server_time,client_time,charge_type,currency,
        price,vh,point_x,point_y,width,height,ver,session_id,0 as try_id,'' as try_key,'' as string1,'' as string2,'' as string3,0 as int1,
        0 as int2,0 as long1""",
    }

}
