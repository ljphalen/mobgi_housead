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


# 国家地区统计
class AdStatTimes(AdBase):
    kpis = []
    dims = []
    dataLength = 0
    hours = {}
    apps = None
    channels = None

    def runInit(self):
        self.kpis = configAdx.KPIS_TIMES
        self.dims = configAdx.DIMS_TIMES

        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.midConfig = configDb.MYSQL_BH_AD_MID

        self.dbMid = self.initDb(self.midConfig)
        self.timesTable = configAdx.TABLE_REPORT_TIMES
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
        sql = "SELECT DISTINCT action_date FROM %s WHERE id_range>%s order by id_range limit 1" % (self.midTable, startId)
        list, count = self.dbMid.fetchall(sql, None)
        result = []
        if count > 0:
            for item in list:
                result.append(str(item[0]))
        return result

    def getMaxIdRange(self, days):
        if len(days) == 1:
            sql = "SELECT max(id_range) as id FROM %s WHERE action_date = '%s'" % (self.midTable, days[0])
        else:
            sql = "SELECT max(id_range) as id FROM %s WHERE action_date in %s" % (self.midTable, str(tuple(days)))
        result = self.dbMid.fetchone(sql)
        if result['id'] is None:
            return False
        else:
            return int(result['id'])

    def getDaysData(self, days):
        if len(days) == 1:
            where = "event_type = 5 and action_date = '%s' " % (days[0])
        else:
            where = "event_type = 5 and action_date in %s " % (str(tuple(days)))

        subsql = "select ads_id,app_key,ad_type,pos_key,gid,action_date,user_id,count(1) as event_count from %s WHERE %s group " \
                 "by ads_id,app_key,ad_type,pos_key,gid,action_date,user_id" % (self.midTable, where)
        fileds = "ads_id,app_key,ad_type,pos_key,gid,action_date as days,event_count,count(1) as people_count"
        groupby = "ads_id,app_key,ad_type,pos_key,gid,action_date,event_count"
        sql = "SELECT %s FROM (%s) as a group by %s " % (fileds, subsql, groupby)
        return self.dbMid.fetchall(sql)

    def exchangeTimes(self, event_count):
        if event_count > 20:
            times = 9
        elif event_count > 10:
            times = 8
        elif event_count > 6:
            times = 7
        else:
            times = event_count

        return times

    def paramData(self, recordData):
        result = {}
        if len(recordData) < 1:
            self.info('parseRecord len < 1')
            return result
        try:
            for record in recordData:
                app_key = record['app_key']
                ads_id = record['ads_id']
                ad_type = record['ad_type']
                pos_key = record['pos_key']
                gid = record['gid']
                days = record['days']
                platform = int(self.getAppPlatform(app_key))
                if platform < 0:
                    self.error('platform-continue:' + app_key + "#" + str(platform))
                    continue
                event_count = int(record['event_count'])
                people_count = int(record['people_count'])
                total_count = event_count * people_count
                times = self.exchangeTimes(event_count)

                # 添加广告位维度
                key = str(app_key) + str(pos_key) + str(ad_type) + str(ads_id) + str(gid) + str(days) + str(times)
                if key not in result:
                    result[key] = {
                        "ads_id": ads_id,
                        "app_key": app_key,
                        "pos_key": pos_key,
                        "channel_gid": gid,
                        "platform": platform,
                        "ad_type": ad_type,
                        "days": days,
                        "people_count": 0,
                        "total_count": 0,
                        "times": times
                    }
                result[key]["people_count"] += people_count
                result[key]["total_count"] += total_count
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
            sql = "insert into %s (%s) values (%s) on duplicate key update %s;" % (self.timesTable, ",".join(fields), ("%s," * len(fields))[:-1], ",".join(updateArr))
            self.info('updateReport')
            self.dbData = self.initDb(self.dataConfig)
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
            self.info("count: " + str(count))
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
        obj = AdStatTimes('ad_stat_times')
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
