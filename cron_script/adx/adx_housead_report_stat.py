#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import commands
import time
import config.housead as configHouseAd
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis
import lib.mysql as db
from adx_base import AdxBase

LIMIT_COUNTS = configAdx.LIMIT_COUNTS

moreFields = configHouseAd.FIELDS_HOUSEAD_DIMS_MORE
dimFields = configHouseAd.FIELDS_HOUSEAD_DIMS
kpiFields = configHouseAd.FIELDS_HOUSEAD_KPIS

RMB = 1


##把聚合数据导入inforbright
class AdxHouseadReportStat(AdxBase):
    dataCount = 0

    def init(self):
        self.r = self.initRedis(configRedis.REDIS_ADX)
        self.dbTable = configHouseAd.TABLE_REPORT_STAT
        self.dbData = self.initDb(configDb.MYSQL_MOBGI_HOUSEAD_STAT)
        self.ibTable = configHouseAd.IB_TABLE_HOUSEAD_STAT
        self.ibFields = configHouseAd.IB_FIELDS_HOUSEAD_STAT

    def initIb(self):
        self.dbIB = self.initDb(configDb.MYSQL_BH_HOUSEAD)

    # 检查infobright是否有新数据
    def checkCondition(self, startPosition):
        sql = "SELECT `id` FROM %s WHERE id > %s order by id asc LIMIT 0, 1" % (self.ibTable, startPosition)
        result = self.dbIB.fetchone(sql)
        # 判断记录是否存在
        if result is None:
            return False
        else:
            return True

    def getRecordList(self, startPosition):

        sql = "SELECT %s FROM %s WHERE id > %s order by id asc LIMIT 0, %s" % (",".join(self.ibFields), self.ibTable, startPosition, LIMIT_COUNTS)
        self.info('getRecordList:' + str(startPosition))
        result, self.dataCount = self.dbIB.fetchall(sql)
        if result is None:
            self.lastPostion = startPosition
        else:
            self.lastPostion = int(result[self.dataCount - 1]["id"])
        return result

    def parseRecordData(self, recordData):
        result = {}
        if len(recordData) < 1:
            self.info('parseRecord len < 1')
            return result
        eventTypes = configHouseAd.EVENT_TYPE
        try:
            for record in recordData:
                eventType = int(record.get('event_type'))
                if eventType in eventTypes:
                    appKey = str(record.get('app_key'))
                    origId = str(record.get('originality_id'))
                    adType = str(record.get('ad_type'))
                    adSubType = str(record.get('ad_sub_type'))
                    # cid = str(record.get('cid'))
                    blockId = str(record.get('blockid'))
                    platform = str(record.get('platform'))
                    price = float(record.get('price'))

                    usedTime = int(record.get('used_time'))
                    serverTime = time.strftime('%Y-%m-%d_%H', time.localtime(float(record.get("created_time"))))
                    eventTypeName = eventTypes[eventType]
                    # 添加广告位维度
                    key = appKey + blockId + origId + adType + adSubType + platform + serverTime
                    if key not in result:
                        result[key] = {
                            "originality_id": origId,
                            "block_id": blockId,
                            "app_key": appKey,
                            "ad_type": adType,
                            "ad_sub_type": adSubType,
                            "platform": platform,
                            "block_id": blockId,
                            "date": serverTime[0:10],
                            "hour": serverTime[-2:],
                            "amount": 0,
                            "used_time": 0
                        }
                        for eventName in eventTypes.itervalues():
                            result[key][eventName] = 0

                    result[key][eventTypeName] += 1
                    result[key]['amount'] += price
                    if eventType == 16:
                        result[key]['used_time'] += usedTime

        except Exception, e:
            self.error("parseRecordListData error:" + str(e))
        return result

    def formatReportData(self, listData):
        result = []
        if len(listData) < 1:
            self.info('formatReportData len < 1')
            return result
        try:
            for item in listData.values():
                values = []
                origInfo = self.getOrigInfo(item['originality_id'])
                if origInfo is None:
                    # self.info("Cannot get origInfo with id:" + str(item['originality_id']))
                    origInfo = {
                        "ad_id": 0,
                        "unit_id": 0,
                        "originality_type": 0,
                        "account_id": 0
                    }
                for field in moreFields:
                    values.append(str(origInfo[field]))
                for field in dimFields:
                    values.append(str(item[field]))
                for field in kpiFields:
                    values.append(str(item[field]))
                for field in kpiFields:
                    values.append(str(item[field]))
                result.append(tuple(values))
            return result
        except Exception, e:
            self.error("formatData error:" + str(e) + "\n" + str(origInfo) + "\toriginality_id:" + str(item['originality_id']))
            return []

    def updateReport(self, data):
        if len(data) < 1:
            self.info('len(data) <1')
            return False
        try:
            fields = moreFields + dimFields + kpiFields
            updateArr = []
            for kpi in kpiFields:
                updateArr.append(kpi + "=" + kpi + "+%s")
            sql = "insert into %s (%s) values (%s) on duplicate key update %s;" % (
                self.dbTable, ",".join(fields), ("%s," * len(fields))[:-1], ",".join(updateArr))
            self.info('updateReport')
            self.dbData.executeMany(sql, data)
            return True
        except Exception, e:
            self.error("updateReport error :" + str(e))

    def getOrigInfo(self, OrigId):
        if OrigId == '0' or OrigId is None:
            return None
        rkey = configHouseAd.REDIS_ORIGINFO + str(OrigId)
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

        if "originality_type" not in result:
            self.error("result" + str(result))
            self.r.delete(rkey)

        return result

    def getOrigInfoFromDb(self, OrigId):
        dbHousead = self.initDb(configDb.MYSQL_MOBGI_HOUSEAD)
        table = "mobgi_housead.delivery_originality_relation"
        sql = "SELECT id,ad_id,unit_id,originality_type,account_id FROM %s WHERE `id` = %s LIMIT 0, 1" % (table, str(OrigId))
        result = dbHousead.fetchone(sql)
        return result

    # infobright数据汇总统计
    def run(self):
        try:
            startTimeStamp = time.time()
            startPosition, status = self.getStartPosition()
            # 判断状态
            if status != 1:
                self.dataCount = 0
                self.info("status is stop")
                return False
            # 判断是否有新数据
            self.initIb()
            if self.checkCondition(startPosition) is not True:
                self.dataCount = 0
                self.info("No data")
                return False

            # 解析保存数据
            recordList = self.getRecordList(startPosition)
            self.dbIB = None
            parseData = self.parseRecordData(recordList)
            reportData = self.formatReportData(parseData)

            if self.updateReport(reportData) is True:
                self.updatePosition()
                self.info("use time : " + str(time.time() - startTimeStamp))

        except Exception, e:
            self.error("run error:" + str(e))


if __name__ == '__main__':
    sleepCount = 0
    max_count = float(LIMIT_COUNTS)

    while 1:
        obj = AdxHouseadReportStat('adx_housead_report_stat')
        if obj.flag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        obj.run()
        count = obj.dataCount
        if count < max_count:
            obj.info("zzz")
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
