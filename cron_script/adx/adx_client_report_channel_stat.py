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

dimFields = configAdx.FIELDS_CHANNEL_DIMS
kpiFields = configAdx.FIELDS_CHANNEL_KPIS
RMB = 1


##把聚合数据导入inforbright
class AdxReportChannelStat(AdxBase):
    dataCount = 0

    def init(self):
        self.dbTable = configAdx.TABLE_REPORT_CHANNEL_STAT
        self.dbData = self.initDb(configDb.MYSQL_MOBGI_DATA)
        self.ibTable = configAdx.TABLE_CLIENT_STAT
        self.ibFields = configAdx.FIELDS_REPORT_CLIENT_CHANNEL_STAT

    def initIb(self):
        self.dbIB = self.initDb(configDb.MYSQL_BH_ADX)

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
        eventTypes = configAdx.ADX_CHANNEL_EVENT_TYPE
        try:
            for record in recordData:
                eventType = int(record.get('event_type'))
                if eventType in eventTypes:
                    appKey = str(record.get('app_key'))
                    adType = str(record.get('ad_type'))
                    cid = str(record.get('cid'))
                    platform = str(record.get('platform'))
                    serverTime = time.strftime('%Y-%m-%d', time.localtime(float(record.get("server_time"))))
                    eventTypeName = eventTypes[eventType]
                    # 添加广告位维度
                    key = appKey + adType + serverTime + platform
                    if key not in result:
                        result[key] = {
                            "ads_id": 2,  # 区分新老版本
                            "app_key": appKey,
                            "channel_id": cid,
                            "country": 'CN',
                            "area": '--',
                            "intergration_type": "0",
                            "platform": str(platform),
                            "date_of_log": serverTime,
                            "block_id": "0"
                        }
                        for eventName in eventTypes.itervalues():
                            result[key][eventName] = 0

                    result[key][eventTypeName] += 1

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
        obj = AdxReportChannelStat('adx_client_report_channel_stat')
        if obj.flag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        obj.run()
        count = obj.dataCount
        if count < max_count:
            obj.info("zzz")
            obj = None
            time.sleep(int((1 - count / max_count) * configAdx.SLEEP_SECOND))
            continue
