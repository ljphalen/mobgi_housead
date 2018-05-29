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

BIT_LENGTH = 1


# Ltv统计
class AdStatLtv(AdBase):
    kpis = []
    dims = []
    dataLength = 0
    hours = {}
    apps = None
    channels = None

    def runInit(self):
        self.kpis = configAdx.KPIS_LTV
        self.dims = configAdx.DIMS_LTV

        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.midConfig = configDb.MYSQL_BH_AD_MID

        self.dbData = self.initDb(self.dataConfig)
        self.dbMid = self.initDb(self.midConfig)

        self.ltvTable = configAdx.TABLE_REPORT_LTV
        self.midTable = configAdx.TABLE_MID_USERS

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
        sql = "SELECT DISTINCT action_date FROM %s WHERE id_range>%s limit 1 " % (self.midTable, startId)
        list, count = self.dbMid.fetchall(sql, None)
        result = []
        if count > 0:
            for item in list:
                result.append(item[0])
        return result

    def getMaxIdRange(self, days):
        sql = "SELECT max(id_range) as id FROM %s WHERE action_date='%s'" % (self.midTable, max(days))
        result = self.dbMid.fetchone(sql)
        if result['id'] is None:
            return False
        else:
            return int(result['id'])

    def getDaysData(self, days):
        if len(days) == 1:
            where = "action_date = '%s' " % (days[0])
        else:
            where = "action_date in %s " % (str(tuple(days)))
        fileds = "app_key,ads_id,gid,ad_type,create_date,action_date,event_type,count(*) as event_count"
        groupby = "app_key,ads_id,gid,ad_type,create_date,action_date"
        sql = "SELECT %s FROM %s WHERE %s group by %s " % (fileds, self.midTable, where, groupby)
        return self.dbMid.fetchall(sql)

    def paramData(self, recordData):
        result = {}
        if len(recordData) < 1:
            self.info('parseRecord len < 1')
            return result
        try:
            eventTypes = configAdx.LTV_EVENT_TYPE
            for record in recordData:
                eventType = int(record.get('event_type'))
                if eventType in eventTypes:
                    appKey = str(record.get('app_key'))
                    adsId = str(record.get('ads_id'))
                    channelGid = int(record.get('gid'))
                    adType = int(record.get('ad_type'))
                    createDate = record.get("create_date")
                    actionDate = record.get("action_date")
                    if createDate is None:
                        continue

                    rDay = (actionDate - createDate).days
                    eventTypeName = eventTypes[eventType]
                    eventCount = int(record.get('event_count'))
                    # 添加广告位维度
                    key = appKey + adsId + str(channelGid) + str(adType) + str(createDate) + str(actionDate) + str(rDay)
                    if key not in result:
                        result[key] = {
                            "app_key": appKey,
                            "platform": self.getAppPlatform(appKey),
                            "ads_id": adsId,
                            "channel_gid": channelGid,
                            "ad_type": adType,
                            "create_date": createDate,
                            "action_date": actionDate,
                            "rday": rDay
                        }
                        for eventName in eventTypes.itervalues():
                            result[key][eventName] = 0
                    result[key][eventTypeName] += eventCount
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
                self.ltvTable, ",".join(fields), ("%s," * len(fields))[:-1], ",".join(updateArr))
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
        obj = AdStatLtv('ad_stat_ltv')
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
