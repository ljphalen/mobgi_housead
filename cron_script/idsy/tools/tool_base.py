#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time

sys.path.append("..")
from lib.mybase import Base
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis


# 工具基础类
class ToolBase(Base):

    def initApi(self):
        if self.dbApi == None:
            self.myredis = self.initRedis(configRedis.REDIS_MOBGI)
            self.apiConfig = configDb.MYSQL_MOBGI_API
            self.dbApi = self.initDb(self.apiConfig)

    def getChannelList(self):
        self.initApi()
        if self.myredis.hlen(self.channelGroupKey) == 0:
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
        self.initApi()
        if self.myredis.hlen(self.appPlatformKey) == 0:
            sql = "SELECT app_key,platform FROM ad_app where state=1"
            list, count = self.dbApi.fetchall(sql)
            result = {}
            for item in list:
                result[item['app_key']] = item['platform']
            self.myredis.hmset(self.appPlatformKey, result)
            self.myredis.expire(self.appPlatformKey, 3600)
        else:
            result = self.myredis.hgetall(self.appPlatformKey)
        return result

    def getChannelGid(self, cid):
        if self.channels == None:
            self.channels = self.getChannelList()
        if len(self.channels) == 0:
            return 0
        elif cid in self.channels:
            return self.channels[cid]
        else:
            # other
            return 57

    def getAppPlatform(self, appKey):
        if self.apps == None:
            self.apps = self.getAppPlatformList()
        if len(self.apps) == 0:
            return 0
        elif appKey in self.apps:
            return self.apps[appKey]
        else:
            return False

    def exchangeStrDate(self, strTime, formFormat, toFormat):
        start_timeArray = time.strptime(strTime, formFormat)
        start_timestamp = int(time.mktime(start_timeArray))
        return time.strftime(toFormat, time.localtime(start_timestamp))

    def exchangeTimeStampDate(self,timeStamp,toFormat):
        time_local = time.localtime(timeStamp)
        return time.strftime(toFormat, time_local)

    def checkAdType(self, adType):
        return int(adType) in configAdx.AD_TYPE
