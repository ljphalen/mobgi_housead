#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import commands
from ad_base import AdBase
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis

LIMIT_COUNTS = configAdx.LIMIT_COUNTS


##houseAd数据汇总
class AdStatHousead(AdBase):
    dataLength = 0
    hours = {}
    apps = None
    channels = None
    dims = configAdx.DIMS_HOUSEAD
    kpis = configAdx.KPIS_HOUSEAD

    def runInit(self):
        self.r = self.initRedis(configRedis.REDIS_MOBGI)

        self.statConfig = configDb.MYSQL_BH_AD_STAT
        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.midConfig = configDb.MYSQL_BH_AD_MID

        self.dbData = self.initDb(self.dataConfig)
        self.dbMid = self.initDb(self.midConfig)

        self.houseadTable = configAdx.TABLE_REPORT_HOUSEAD
        self.clientTable = configAdx.TABLE_STAT_CLIENT
        self.midTable = configAdx.TABLE_MID_HOUSEAD

        self.clientFields = configAdx.FIELDS[self.clientTable]
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
        sql = "SELECT server_time FROM %s WHERE id_range>%s order by id_range limit 1" % (self.midTable, startPosition)
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
            where = "server_time = '%s' " % (hours[0])
        else:
            where = "server_time in %s " % (str(tuple(hours)))
        # where += " and cid='TEST0000000'"
        fileds = "orig_id,app_key,pos_key,ad_type,ad_sub_type,platform,server_time,currency,event_type," \
                 "sum(amount) as amount,sum(event_count) as event_count,sum(event_value) as event_value"
        groupby = "orig_id,app_key,pos_key,ad_type,ad_sub_type,platform,server_time,currency,event_type"
        sql = "SELECT %s FROM %s WHERE %s group by %s " % (fileds, self.midTable, where, groupby)
        # self.info('getHourData:' + str(sql))
        result, self.dataLength = self.dbMid.fetchall(sql)
        return result

    def getOrigInfoFromDb(self, OrigId):
        dbHousead = self.initDb(configDb.MYSQL_MOBGI_HOUSEAD)
        table = "mobgi_housead.delivery_originality_relation"
        sql = "SELECT id,ad_id,unit_id,originality_type,account_id FROM %s WHERE `id` = %s LIMIT 0, 1" % (table, str(OrigId))
        result = dbHousead.fetchone(sql)
        return result

    def getOrigInfo(self, OrigId):
        if OrigId == '0' or OrigId is None:
            return None
        rkey = configAdx.REDIS_ORIGINFO_PRE + str(OrigId)
        if self.r.hget(rkey, "ad_id") is None:
            result = self.getOrigInfoFromDb(OrigId)
            if result is not None:
                self.r.hmset(rkey, {
                    'ad_id': result.get('ad_id'),
                    'unit_id': result.get('unit_id'),
                    'originality_type': result.get('originality_type'),
                    'account_id': result.get('account_id')
                })
                self.r.expire(rkey, 1800)
        else:
            result = self.r.hgetall(rkey)
        if result is None:
            result = {
                "ad_id": 0,
                "unit_id": 0,
                "originality_type": 0,
                "account_id": 0
            }
            self.info("Cannot get origInfo with id:" + str(OrigId))
        return result

    def paramData(self, recordData):
        result = {}
        if len(recordData) < 1:
            self.info('parseRecord len < 1')
            return result
        try:
            eventTypes = configAdx.HOUSEAD_EVENT_TYPE
            for record in recordData:
                eventType = int(record.get('event_type'))
                if eventType in eventTypes:
                    origId = str(record.get('orig_id'))
                    appKey = str(record.get('app_key'))
                    posKey = str(record.get('pos_key'))
                    adType = str(record.get('ad_type'))
                    adSubType = str(record.get('ad_sub_type'))
                    platform = int(record.get('platform'))

                    # -----------------------------------------------
                    myPlatform = self.getAppPlatform(appKey)
                    if myPlatform < 0 or (myPlatform > 0 and myPlatform != platform):
                        self.info('platform-continue:' + appKey + "#" + str(platform) + "*" + str(myPlatform))
                        continue

                    if self.checkAdType(adType) is False:
                        self.info('adType-continue:' + adType)
                        continue
                    # --------------------------------------------------

                    eventCount = int(record.get('event_count'))
                    eventValue = int(record.get('event_value'))
                    currency = int(record.get('currency'))
                    amount = float(record.get('amount'))
                    if currency == configAdx.CURRENCY_USD:
                        amount *= configAdx.EXCHANGE_RATE_USD_RMB
                    serverTime = record.get("server_time").strftime('%Y-%m-%d_%H')
                    eventTypeName = eventTypes[eventType]
                    # 添加广告位维度
                    key = origId + appKey + posKey + adType + adSubType + serverTime
                    if key not in result:
                        result[key] = {
                            "originality_id": origId,
                            "app_key": appKey,
                            "pos_key": posKey,
                            "ad_type": adType,
                            "ad_sub_type": adSubType,
                            "platform": platform,
                            "days": serverTime[0:10],
                            "hours": serverTime[-2:],
                            "amount": 0,
                            "event_count": 0,
                            "event_value": 0
                        }
                        for eventName in eventTypes.itervalues():
                            result[key][eventName] = 0
                        result[key]['skip_stay_time'] = 0  # 开屏跳过时间
                    result[key][eventTypeName] += eventCount
                    result[key]["amount"] += amount
                    if eventType == 16:
                        result[key]['skip_stay_time'] += eventValue


        except Exception, e:
            raise Exception("paramData:" + str(e))
        return result

    def saveData(self, data):
        if len(data) < 1:
            self.info('saveData len(data) <1')
            return True
        try:
            result = []
            origMap = {}
            for item in data.values():
                values = []
                if item['originality_id'] not in origMap:
                    origMap[item['originality_id']] = self.getOrigInfo(item['originality_id'])
                origInfo = origMap[item['originality_id']]

                item["ad_id"] = origInfo["ad_id"]
                item["unit_id"] = origInfo["unit_id"]
                item["account_id"] = origInfo["account_id"]
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
                self.houseadTable, ",".join(fields), ("%s," * len(fields))[:-1], ",".join(updateArr))
            self.info('updateReport')
            # self.info(str(sql))
            self.dbData.executeMany(sql, result)
            return True
        except Exception, e:
            raise Exception("saveData error :" + str(e))

    # mid数据汇总统计
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

            self.info("hours:" + str(hours))
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
            self.error("run error:" + str(e))
        return False


if __name__ == '__main__':
    sleepCount = 0
    max_count = float(LIMIT_COUNTS)
    while 1:
        obj = AdStatHousead('ad_stat_housead')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        if obj.run() is not True:
            obj.info("zzz:" + str(obj.dataLength))
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            # time.sleep(1)
            continue
