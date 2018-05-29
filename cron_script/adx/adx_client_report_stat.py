#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis
import lib.mysql as db
from adx_base import AdxBase

LIMIT_COUNTS = configAdx.LIMIT_COUNTS

dimFields = configAdx.FIELDS_DIMS
kpiFields = configAdx.FIELDS_CLIENT_KPIS
RMB = 1


##把聚合数据导入inforbright
class AdxReportStat(AdxBase):
    dataCount = 0

    def init(self):
        self.dbTable = configAdx.TABLE_REPORT_STAT
        self.dbData = self.initDb(configDb.MYSQL_MOBGI_DATA)
        self.ibTable = configAdx.IB_TABLE_CLIENT_STAT
        self.ibFields = configAdx.FIELDS_REPORT_CLIENT_STAT

    def initIb(self):
        self.dbIB =self.initDb(configDb.MYSQL_BH_ADX)

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
        eventTypes = configAdx.ADX_EVENT_TYPE
        try:
            for record in recordData:
                eventType = int(record.get('event_type'))
                if eventType in eventTypes:
                    sspId = str(record.get('provider_id'))
                    adsId = str(record.get('dsp_id'))
                    appKey = str(record.get('app_key'))
                    sdkVersion = str(record.get('sdk_version'))
                    appVersion = str(record.get('app_version'))

                    adId = str(record.get('ad_id'))
                    adType = str(record.get('ad_type'))
                    # cid = str(record.get('cid'))
                    blockId = str(record.get('blockid'))
                    platform = str(record.get('platform'))
                    price = float(record.get('price'))
                    currency = int(record.get('currency'))
                    usedTime = int(record.get('used_time'))
                    if currency == 1:
                        price = round(price / 6.5, 5)
                    if adId != "housead":
                        adId = "-"
                    serverTime = time.strftime('%Y-%m-%d_%H', time.localtime(float(record.get("server_time"))))
                    eventTypeName = eventTypes[eventType]
                    # 添加广告位维度
                    key = sspId + adsId + appKey + blockId + adId + adType + platform + serverTime + sdkVersion + appVersion
                    if key not in result:
                        result[key] = {
                            "ssp_id": sspId,
                            "ads_id": adsId,
                            "app_key": appKey,
                            "sdk_version": sdkVersion,
                            "app_version": appVersion,
                            "block_id": blockId,
                            "ad_id": adId,
                            "intergration_type": adType,
                            "date_of_log": serverTime[0:10],
                            "hour_of_log": serverTime[-2:],
                            "platform": str(int(platform) - 1),
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
                for field in dimFields:
                    values.append(str(item[field]))
                for field in kpiFields:
                    values.append(str(item[field]))
                for field in kpiFields:
                    values.append(str(item[field]))
                result.append(tuple(values))
            return result
        except Exception, e:
            self.error("formatData error:" + str(e) + "\n" + str(listData))
            return []

    def updateReport(self, data):
        if len(data) < 1:
            self.info('len(data) <1')
            return False
        try:
            fields = dimFields + kpiFields
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
            return False

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
            self.initIb()
            # 判断是否有新数据
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
        obj = AdxReportStat('adx_client_report_stat')
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
