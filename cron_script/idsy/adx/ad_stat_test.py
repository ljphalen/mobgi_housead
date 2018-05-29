#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import commands
from ad_base import AdBase
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis
import traceback

LIMIT_COUNTS = configAdx.LIMIT_COUNTS


##统计TEST
class AdStatTest(AdBase):
    dataLength = 0
    hours = {}
    apps = None
    channels = None
    dims = configAdx.DIMS_TEST
    kpis = configAdx.KPIS_TEST

    def runInit(self):
        self.r = self.initRedis(configRedis.REDIS_MOBGI)

        self.statConfig = configDb.MYSQL_BH_AD_STAT
        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.midConfig = configDb.MYSQL_BH_AD_MID

        self.dbData = self.initDb(self.dataConfig)
        self.dbMid = self.initDb(self.midConfig)

        self.hourTable = configAdx.TABLE_REPORT_TEST
        self.midTable = configAdx.TABLE_MID_TEST

        self.midFields = configAdx.FIELDS[self.midTable]
        # self.initIb()

    def initIb(self):
        self.ib = self.initDb(self.statConfig)

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

    def getHours(self, startPosition):
        sql = "SELECT server_time FROM %s W" \
              "HERE id_range>%s order by id_range limit 1" % (self.midTable, startPosition)
        list, count = self.dbMid.fetchall(sql, None)
        result = []
        if count > 0:
            for item in list:
                result.append(item[0].strftime("%Y-%m-%d %H:00:00"))
        return result

    def getMaxIdRange(self, hours):
        sql = "SELECT max(id_range) as id FROM %s WHERE server_time='%s'" % (self.midTable, max(hours))
        result = self.dbMid.fetchone(sql)
        if result['id'] is None:
            return False
        else:
            return int(result['id'])

    def getHourData(self, hours):
        if len(hours) == 1:
            where = "server_time = '%s' and user_type = 1" % (hours[0])
        else:
            where = "server_time in %s and user_type = 1" % (str(tuple(hours)))
        # where += " and cid='TEST0000000'"
        fileds = "ads_id,ssp_id,app_key,pos_key,cid,ad_type,platform,app_version,sdk_version,server_time,event_type,flow_id,conf_id," \
                 "count(*) as event_count,sum(event_value) as event_value,sum(event_time) as event_time"
        groupby = "ads_id,ssp_id,app_key,pos_key,cid,ad_type,platform,app_version,sdk_version,server_time,event_type,flow_id,conf_id"
        sql = "SELECT %s FROM %s WHERE %s group by %s " % (fileds, self.midTable, where, groupby)
        # self.info('getHourData:' + str(sql))
        result, self.dataLength = self.dbMid.fetchall(sql)
        return result



    def paramData(self, recordData):
        result = {}
        if len(recordData) < 1:
            self.info('parseRecord len < 1')
            return result

        eventTypes = configAdx.ADX_EVENT_TYPE
        for record in recordData:
            eventType = int(record.get('event_type'))
            if eventType in eventTypes:
                sspId = str(record.get('ssp_id'))
                adsId = str(record.get('ads_id'))
                appKey = str(record.get('app_key'))
                posKey = str(record.get('pos_key'))

                adType = str(record.get('ad_type'))
                cid = str(record.get('cid'))
                platform = int(self.getAppPlatform(appKey))
                if platform < 0:
                    self.error('platform-continue:' + appKey + "#" + str(platform))
                    continue

                flowId = str(record.get('flow_id'))
                confId = str(record.get('conf_id'))

                gid = self.getChannelGid(cid)
                isCustom = self.getChannelCustomMap(gid, adsId)
                sdkVersion = str(record.get('sdk_version'))
                appVersion = str(record.get('app_version'))
                eventCount = int(record.get('event_count'))
                eventTime = int(record.get('event_time'))
                serverTime = record.get("server_time").strftime('%Y-%m-%d_%H')
                eventTypeName = eventTypes[eventType]
                # 添加广告位维度
                key = sspId + adsId + appKey + posKey + str(gid) + adType + serverTime + sdkVersion + appVersion+ str(flowId)+ str(confId)
                if key not in result:
                    result[key] = {
                        "ssp_id": sspId,
                        "ads_id": adsId,
                        "app_key": appKey,
                        "pos_key": posKey,
                        "channel_gid": gid,
                        "flow_id":flowId,
                        "conf_id":confId,
                        "is_custom": isCustom,
                        "platform": platform,
                        "ad_type": adType,
                        "sdk_version": sdkVersion,
                        "event_type": eventType,
                        "app_version": appVersion,
                        "days": serverTime[0:10],
                        "hours": serverTime[-2:],
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
        res = []
        for key in result:
            res.append(result[key])
            if len(result[key]['pos_key']) > 10 and self.isPosKeyMatchAppKey(result[key]['app_key'], result[key]['pos_key']) is False:
                self.info('poskey-continue:' + result[key]['pos_key'] + ",appKey:" + result[key]['app_key'] + ",event_type:" + str(
                    result[key]['event_type']) + ",ad_type:" + str(result[key]['ad_type']) + ",ads_id:" + str(
                    result[key]['ads_id']) + ",download:" + str(result[key]['download']))
        return res

    def saveData(self, data):
        if len(data) < 1:
            self.info('saveData len(data) <1')
            return True
        try:
            result = []
            for item in data:
                item['effective_impressions'] = 0
                ad_type = int(item['ad_type'])
                if ad_type == configAdx.AD_TYPE_PIC or ad_type == configAdx.AD_TYPE_VIDEO or ad_type == configAdx.AD_TYPE_SPLASH:
                    item['effective_impressions'] = item['closes']
                elif ad_type == configAdx.AD_TYPE_NATIVE:
                    item['effective_impressions'] = item['impressions']
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
                updateArr.append(kpi + "=" + "%s")
            sql = "insert into %s (%s) values (%s) on duplicate key update %s;" % (
                self.hourTable, ",".join(fields), ("%s," * len(fields))[:-1], ",".join(updateArr))
            self.info('updateReport')
            self.dbData.executeMany(sql, result)
            return True
        except Exception, e:
            traceback.print_exc()
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

            # 判断是否有新数据
            if self.checkCondition(self.startPosition) is not True:
                self.info("Not to start position")
                return False
            self.info("startPosition:" + str(self.startPosition))
            hours = self.getHours(self.startPosition)
            self.nextPosition = self.getMaxIdRange(hours)
            if self.nextPosition is False:
                return False
            hourData = self.getHourData(hours)
            # self.info("hourData" + str(hourData))
            paramData = self.paramData(hourData)
            if self.saveData(paramData) is True:
                self.updatePosition()
                self.info("use time : " + str(time.time() - startTimeStamp))
                return True
            else:
                self.info("use time : " + str(time.time() - startTimeStamp))
                return False



        except Exception, e:
            traceback.print_exc()
            self.error("run error:" + str(e))
        return False


if __name__ == '__main__':
    sleepCount = 0
    max_count = float(LIMIT_COUNTS)
    obj = AdStatTest('ad_stat_test')
    while 1:
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            exit()
        if obj.run() is not True:
            obj.info("zzz:" + str(obj.dataLength))
            obj = None
            exit()

