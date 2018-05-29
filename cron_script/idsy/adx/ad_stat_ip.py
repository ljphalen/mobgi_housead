#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import codecs
import commands
import urllib2
import json
from ad_base import AdBase
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis

LIMIT_COUNTS = configAdx.LIMIT_COUNTS

BIT_LENGTH = configAdx.BIT_LENGTH
SQL_PATH = 'sql'


# 国家地区统计
class AdStatIp(AdBase):
    kpis = []
    dims = []
    dataLength = 0
    hours = {}
    apps = None
    channels = None

    def runInit(self):
        self.kpis = configAdx.KPIS_CITY
        self.dims = configAdx.DIMS_CITY

        self.r = self.initRedis(configRedis.REDIS_MOBGI)
        self.ipPre = 'ip_addr_'

        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.midConfig = configDb.MYSQL_BH_AD_MID

        self.dbData = self.initDb(self.dataConfig)
        self.dbMid = self.initDb(self.midConfig)

        self.cityTable = configAdx.TABLE_REPORT_CITY
        self.midTable = configAdx.TABLE_MID_IP

        self.midFields = configAdx.FIELDS[self.midTable]

    # 检查infobright是否有新数据
    def checkCondition(self, startPosition):
        sql = "SELECT max(id_range) as id FROM %s" % (self.midTable)
        result = self.dbMid.fetchone(sql)
        # 判断记录是否存在
        if result['id'] is None:
            return False
        else:
            self.lastPosition = int(result['id'])
            return startPosition < self.lastPosition

    def getDays(self, startId):
        sql = "SELECT DISTINCT server_time FROM %s WHERE id_range>%s limit 1 " % (self.midTable, startId)
        list, count = self.dbMid.fetchall(sql, None)
        result = []
        if count > 0:
            for item in list:
                result.append(item[0].strftime("%Y-%m-%d"))
        return result

    def getMaxIdRange(self, days):
        sql = "SELECT max(id_range) as id FROM %s WHERE server_time='%s'" % (self.midTable, max(days))
        result = self.dbMid.fetchone(sql)
        if result['id'] is None:
            return False
        else:
            return int(result['id'])

    def getDaysData(self, days):
        if len(days) == 1:
            where = "server_time = '%s' " % (days[0])
        else:
            where = "server_time in %s " % (str(tuple(days)))
        fileds = "app_key,server_time,country,province,event_type,sum(event_count) as event_count,sum(event_time) as event_time"
        groupby = "app_key,platform,server_time,country,province,event_type"
        sql = "SELECT %s FROM %s WHERE %s group by %s " % (fileds, self.midTable, where, groupby)
        return self.dbMid.fetchall(sql)

    def paramData(self, recordData):
        result = {}
        if len(recordData) < 1:
            self.info('parseRecord len < 1')
            return result
        try:
            eventTypes = configAdx.ADX_EVENT_TYPE
            for record in recordData:
                eventType = int(record.get('event_type'))
                if eventType in eventTypes:
                    appKey = str(record.get('app_key'))
                    platform = int(self.getAppPlatform(appKey))
                    if platform < 0:
                        self.error('platform-continue:' + appKey + "#" + str(platform))
                        continue
                    eventCount = int(record.get('event_count'))
                    serverTime = record.get("server_time")
                    country = record.get("country")
                    province = record.get("province")
                    eventTime = int(record.get('event_time'))
                    eventTypeName = eventTypes[eventType]
                    # 添加广告位维度
                    key = appKey + str(platform) + country + province + str(serverTime)
                    if key not in result:
                        result[key] = {
                            "app_key": appKey,
                            "platform": platform,
                            "days": serverTime,
                            "country": country,
                            "province": province,
                            "event_count": 0,
                            "skip_stay_time": 0,
                            "exit_stay_time": 0
                        }
                        for eventName in eventTypes.itervalues():
                            result[key][eventName] = 0
                    result[key][eventTypeName] += eventCount
                    if eventType == configAdx.EVENT_EXIT:
                        result[key]['exit_stay_time'] += eventTime
                    elif eventType == configAdx.EVENT_SKIP:
                        result[key]['skip_stay_time'] += eventTime
        except Exception, e:
            raise Exception("paramData:" + str(e))
        return result

    def saveData(self, data):
        if len(data) < 1:
            self.info('saveData len(data) <1')
            return False
        try:
            result = []
            for item in data.values():
                item['play_finish'] = int(item['reward'])
                item['exit_stay_time'] = int(item['exit_stay_time'] / 1000)
                values = []
                for field in self.dims:
                    values.append(str(item[field]))
                for field in self.kpis:
                    values.append(str(item[field]))
                for field in self.kpis:
                    values.append(str(item[field]))
                result.append(tuple(values))

            fields = self.dims + self.kpis
            updateArr = []
            for kpi in self.kpis:
                updateArr.append(kpi + "=%s")
            sql = "insert into %s (%s) values (%s) on duplicate key update %s;" % (
                self.cityTable, ",".join(fields), ("%s," * len(fields))[:-1], ",".join(updateArr))
            self.info('updateReport')
            self.dbData.executeMany(sql, result)
            return True
        except Exception, e:
            raise Exception("saveData error :" + str(e))

    def run(self):
        try:
            self.runInit()
            startTimeStamp = time.time()
            self.startPosition, status = self.getStartPosition()
            # 判断状态
            if status != 1:
                self.info("status is stop")
                return False

            if self.checkCondition(self.startPosition) is not True:
                self.info("Not to start position")
                return False

            ##获取统计
            days = self.getDays(self.startPosition)
            self.info("days:" + str(days))
            self.nextPosition = self.getMaxIdRange(days)
            if self.nextPosition is False:
                return False
            dayData, count = self.getDaysData(days)
            paramData = self.paramData(dayData)
            if self.saveData(paramData) is True:
                self.updatePosition()
                self.info("use time : " + str(time.time() - startTimeStamp))
                return True
            else:
                self.info("use time : " + str(time.time() - startTimeStamp))
                return False

        except Exception, e:
            self.error("run error:" + str(e))


if __name__ == '__main__':
    sleepCount = 0
    max_count = float(LIMIT_COUNTS)
    while 1:
        obj = AdStatIp('ad_stat_ip')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        if obj.run() is not True:
            quit()
            # obj.info("zzz:" + str(obj.dataLength))
            # obj = None
            # time.sleep(configAdx.SLEEP_SECOND)
            # continue
