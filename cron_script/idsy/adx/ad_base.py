#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import datetime
import binascii
import json
import urllib2

sys.path.append("..")
from lib.mybase import Base
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis


class AdBase(Base):
    channelGroupKey = 'channel_group_set'
    appPlatformKey = 'app_platform_set'

    spChannelKey = 'sp_channel_key'
    appPoskeyKey = 'app_pos_key'
    dbApi = None
    dbReport = None
    myredis = None
    apps = None
    channels = None
    poskeys = None
    spChannel = None

    def initApi(self):
        if self.dbApi == None:
            self.info('connect_api----------')
            self.apiConfig = configDb.MYSQL_MOBGI_API
            self.dbApi = self.initDb(self.apiConfig)

    def initReport(self):
        if self.dbReport == None:
            self.info('connect_report-----------')
            self.reportConfig = configDb.MYSQL_MOBGI_DATA
            self.dbReport = self.initDb(self.reportConfig)

    def initMyredis(self):
        if self.myredis == None:
            self.myredis = self.initRedis(configRedis.REDIS_MOBGI, 2)

    def getChannelList(self):
        self.initMyredis()
        if self.myredis.hlen(self.channelGroupKey) == 0:
            self.initApi()
            sql = "SELECT channel_id,group_id FROM channel where group_id>0"
            list, count = self.dbApi.fetchall(sql)
            result = {}
            for item in list:
                result[item['channel_id']] = int(item['group_id'])
            self.myredis.hmset(self.channelGroupKey, result)
            self.myredis.expire(self.channelGroupKey, 3600)
        else:
            result = self.myredis.hgetall(self.channelGroupKey)
        return result

    def getAppPlatformList(self):
        self.initMyredis()
        if self.myredis.hlen(self.appPlatformKey) == 0:
            self.initApi()
            sql = "SELECT app_key,platform FROM ad_app"
            list, count = self.dbApi.fetchall(sql)
            result = {}
            for item in list:
                result[item['app_key']] = item['platform']
            self.myredis.hmset(self.appPlatformKey, result)
            self.myredis.expire(self.appPlatformKey, 3600)
        else:
            result = self.myredis.hgetall(self.appPlatformKey)
        return result

    def getPosKeyList(self):
        self.initMyredis()
        if self.myredis.hlen(self.appPoskeyKey) == 0:
            self.initReport()
            sql = "SELECT pos_key,app_key FROM config_pos"
            list, count = self.dbReport.fetchall(sql)
            result = {}
            for item in list:
                result[item['pos_key']] = item['app_key']
            self.myredis.hmset(self.appPoskeyKey, result)
            self.myredis.expire(self.appPoskeyKey, 3600)
        else:
            result = self.myredis.hgetall(self.appPoskeyKey)
        return result

    def getChannelGid(self, cid):
        if self.channels == None:
            self.channels = self.getChannelList()
        if len(self.channels) == 0:
            raise Exception("channel is none")
        elif cid in self.channels:
            return int(self.channels[cid])
        else:
            # other
            return 57

    def getSpChannel(self):
        self.initMyredis()
        if self.myredis.hlen(self.spChannelKey) == 0:
            self.initApi()
            sql = "SELECT channel_id,is_custom,ads_id FROM channel where group_id=0 and is_custom=1"
            list, count = self.dbApi.fetchall(sql)
            self.info("##channel:" + str(list))
            result = {}
            for item in list:
                channel_id = str(item['channel_id'])
                result[channel_id] = 1
                result[item['ads_id'] + '_' + channel_id] = 1
            self.myredis.hmset(self.spChannelKey, result)
            self.myredis.expire(self.spChannelKey, 3600)
        else:
            result = self.myredis.hgetall(self.spChannelKey)
        return result

    def getChannelCustomMap(self, gid, ads_id=None):

        if self.spChannel == None:
            self.spChannel = self.getSpChannel()

        if len(self.spChannel) == 0:
            raise Exception("channel is none")
        elif ads_id is None or ads_id == '(null)' or len(ads_id) < 3:
            key = str(gid)
        else:
            key = str(ads_id) + '_' + str(gid)
        channelCustomMap = self.spChannel
        if key in channelCustomMap:
            return 1
        else:
            return 0

    def isPosKeyMatchAppKey(self, app_key, pos_key):
        if self.poskeys == None:
            self.poskeys = self.getPosKeyList()
        if len(self.poskeys) == 0:
            raise Exception("poskeys is none")
        elif pos_key in self.poskeys:
            return self.poskeys[pos_key] == app_key
        else:
            return False

    def getAppPlatform(self, appKey):
        if self.apps == None:
            self.apps = self.getAppPlatformList()
        if len(self.apps) == 0:
            raise Exception("platform is none")
        elif appKey in self.apps:
            return int(self.apps[appKey])
        else:
            return -1

    def checkAdType(self, adType):
        return int(adType) in configAdx.AD_TYPE

    # 分表策略
    def getCutTableId100(self, value):
        return binascii.crc32(value) % 100

    def calDays(self, day1, day2):
        d1 = datetime.datetime.strptime(str(day1), '%Y-%m-%d')
        d2 = datetime.datetime.strptime(str(day2), '%Y-%m-%d')
        delta = d1 - d2
        return delta.days

    def getIpInfo(self, ip):
        try:
            country = '--'
            province = '--'
            city = '--'
            url = configAdx.IpApiUrl + ip
            req = urllib2.Request(url)
            res = urllib2.urlopen(req)
            status_code = res.getcode()
            while (status_code != 200):
                self.info("zzz:10 ,ip error:" + str(status_code))
                time.sleep(10)
                res = urllib2.urlopen(req)
                status_code = res.getcode()

            html = res.read()
            res = json.loads(html)

            # 判断返回信息是否出错
            if int(res['ret']) != 1:
                self.info(str(res['msg']) + ':' + ip)
            else:
                if res.has_key('data') and res['data'].has_key('country_code'):
                    country = res['data']['country_code']
                    province = res['data']['province']
                    if province != '':
                        province = province[0:2]
                    else:
                        province = "--"
                    # 台湾、香港、澳门 特殊处理
                    if country == "TW":
                        country = "CN"
                        province = "台湾"
                    if country == "HK":
                        country = "CN"
                        province = "香港"
                    if country == "MO":
                        country = "CN"
                        province = "澳门"
                    if country == 'CN':
                        city = res['data']['city'][0:5]

        except Exception, e:
            self.error("ip error:" + str(e), ip)
        return country, province, city

    def getOrigInfoFromDb(self, OrigId):
        dbHousead = self.initDb(configDb.MYSQL_MOBGI_HOUSEAD)
        table = "mobgi_housead.delivery_originality_relation"
        sql = "SELECT id,ad_id,unit_id,originality_type,account_id FROM %s WHERE `id` = %s LIMIT 0, 1" % (table, str(OrigId))
        result = dbHousead.fetchone(sql)
        return result

    def getOrigInfo(self, orig_id, rconn=None):
        result = None
        self.initMyredis()
        if orig_id > 0:
            rkey = configAdx.REDIS_ORIGINFO_PRE + str(orig_id)
            if self.myredis.hget(rkey, "ad_id") is None:
                result = self.getOrigInfoFromDb(orig_id)
                if result is not None:
                    self.myredis.hmset(rkey, {
                        'ad_id': result.get('ad_id'),
                        'unit_id': result.get('unit_id'),
                        'originality_type': result.get('originality_type'),
                        'account_id': result.get('account_id')
                    })
                    self.myredis.expire(rkey, 7200)
            else:
                result = self.myredis.hgetall(rkey)
        if result is None:
            result = {
                "ad_id": 0,
                "unit_id": 0,
                "originality_type": 0,
                "account_id": 0
            }
        return result
