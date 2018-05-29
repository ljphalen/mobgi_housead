#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import datetime
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis
import lib.mysql as db
from ad_base import AdxBase

LIMIT_COUNTS = configAdx.LIMIT_COUNTS

BIT_LENGTH = 100000000000

dimFields = configAdx.FIELDS_CHANNEL_DIMS
kpiFields = configAdx.FIELDS_CHANNEL_KPIS

# 最大留存天数
MAX_RETENTION_DAYS = 7
UPDATE_LOOPS = 1

DAU_SET_KEY = 'adx_dau_user_set'
TOTAL_SET_KEY = 'adx_total_user_set'


##统计实时新增用户
class AdNewUser(AdxBase):
    dataCount = 0
    lastServerTime = 0
    dauTable = 'report_dau'
    userTable = 'report_user'

    def init(self):
        self.dbTable = configAdx.TABLE_REPORT_CHANNEL_STAT
        self.dbData = self.initDb(configDb.MYSQL_MOBGI_DATA)
        self.ibTable = configAdx.TABLE_CLIENT_STAT
        self.ibFields = configAdx.FIELDS_REPORT_CLIENT_CHANNEL_STAT
        self.r = self.initRedis(configRedis.REDIS_MOBGI)
        self.today = datetime.date.today()

    def initIb(self):
        self.ib = self.initDb(configDb.MYSQL_BH_AD_STAT)

    def closeIb(self):
        self.ib = None

    # 检查infobright是否有新数据
    def checkCondition(self, nextPosition):
        if nextPosition < self.lastPosition:
            return True
        sql = "SELECT max(id) as id FROM %s" % (self.ibTable)
        result = self.ib.fetchone(sql)
        # 判断记录是否存在
        if result is None:
            return False
        else:
            self.lastPosition = int(result['id']) / BIT_LENGTH
            return nextPosition < self.lastPosition

    # 判断是否新增用户
    def isNewUser(self, app_key, uuid):
        sql = "SELECT created_at,bitmap FROM %s WHERE app_key='%s' and uuid='%s'" % (self.userTable, app_key, uuid)
        result = self.dbData.fetchone(sql)
        if result is None:
            datetime = None
            bitmap = 0
        else:
            datetime = result['created_at']
            bitmap = result['bitmap']
        return datetime, bitmap

    # 追加到新用户或更新bitmap
    def addNewUser(self, data):
        if len(data) < 1:
            self.info('len(data) <1')
            return False
        try:
            self.info('addNewUser count:' + str(len(data)))
            sql = "insert into " + self.userTable + " (app_key,uuid,created_at,bitmap) values (%s,%s,%s,%s) " \
                                                    "on duplicate key update bitmap =%s"
            result = self.dbData.executeMany(sql, data)
            return result
        except Exception, e:
            raise Exception("insert data error:" + str(e))

    # 获取实时dau
    def getReportDauData(self):
        list = self.r.smembers(DAU_SET_KEY)
        if len(list) < 1:
            self.info('dau list<0')
            return []
        try:
            result = []
            for keyDau in list:
                [appKey, gid, mydate] = keyDau.split('_')
                keyTotal = appKey + ":" + gid + ":" + mydate
                mydate = datetime.datetime.strptime(mydate, "%Y%m%d").date()
                # 判断是否今天数据
                if self.today == mydate:
                    dau = self.r.hlen(keyDau)
                    total = self.r.hlen(keyTotal)
                    value = (appKey, gid, mydate, dau, total, dau, total)
                    result.append(value)
                else:
                    self.r.delete(keyDau)
                    self.r.delete(keyTotal)
        except Exception, e:
            self.error("getReportDauData error:" + str(e))
        finally:
            return result

    # 更新DAU
    def updateReportDau(self):
        data = self.getReportDauData()
        if len(data) < 1:
            self.info('len(data) <1')
            return False
        try:
            self.info('updateReportDau =>' + str(len(data)))
            sql = "insert into " + self.dauTable + " (app_key,channel_gid,ad_type,date_of_log,user_dau,user_total) values (%s,%s,0,%s,%s,%s) " \
                                                   "on duplicate key update user_dau=%s, user_total=%s"
            self.dbData.executeMany(sql, data)

        except Exception, e:
            raise Exception("updateReportDau Exception:" + str(e))

    # 更新新增数量
    def updateReportNewUser(self):
        # if self.lastServerTime>0:
        #     myday = time.strftime('%Y-%m-%d %H:0', time.localtime(self.lastServerTime))
        # else:

        sql = "SELECT app_key,DATE(created_at) as myday,COUNT(1) as count FROM %s where created_at>='%s' " \
              "GROUP BY app_key,DATE(created_at);" % (self.userTable, str(self.today))

        list, count = self.dbData.fetchall(sql)

        if count > 0:
            data = []
            for item in list:
                value = (item['app_key'], item['myday'], self.getAppPlatfrom(item['app_key']), item['count'], item['count'])
                data.append(value)
            self.info('updateReportNewUser =>' + str(len(data)))
            sql = "insert into " + self.dauTable + " (app_key,date_of_log,platform,new_user) values (%s,%s,%s,%s) " \
                                                   "on duplicate key update new_user =%s"
            self.dbData.executeMany(sql, data)

    def getRecordList(self, startPosition, nextPosition):
        startId = startPosition * BIT_LENGTH
        nextId = nextPosition * BIT_LENGTH
        fields = "app_key,uuid,platform,event_type,cid,server_time"
        groupBy = "group by app_key,uuid,platform,event_type,cid,server_time"
        sql = "SELECT %s FROM %s where id>=%s and id<%s and event_type in (5,15) %s" % (fields, self.ibTable, str(startId), str(nextId), groupBy)
        self.info('getRecordList:' + str(startPosition))
        result, count = self.ib.fetchall(sql)
        return result, count

    def parseRecordData(self, recordData):
        dauMap = {}
        TotalMap = {}
        if len(recordData) < 1:
            self.info('parseRecord len(recordData) < 1')
            return []
        try:
            result = []
            self.lastServerTime = int(recordData[0]['server_time'])
            for record in recordData:
                appKey = str(record.get('app_key'))
                platform = str(record.get('platform'))
                cid = str(record.get('cid'))
                # -----------------------------------------------
                myPlatform = self.getAppPlatform(appKey)
                if myPlatform is False or (myPlatform > 0 and myPlatform != platform):
                    continue
                gid = self.getChannelGid(cid)
                if gid is False:
                    continue
                # --------------------------------------------------
                eventType = str(record.get('event_type'))
                uuid = str(record.get('uuid'))
                serverTime = int(record.get('server_time'))
                serverDate = time.strftime('%Y%m%d', time.localtime(serverTime))
                keyAppDau = appKey + "_" + gid + "_" + serverDate
                keyAppTotal = appKey + ":" + gid + ":" + serverDate
                if keyAppTotal not in TotalMap:
                    if self.r.exists(keyAppTotal) == 0:
                        self.r.hset(keyAppTotal, uuid, 0)
                        self.r.expire(keyAppTotal, 88000)
                    TotalMap[keyAppTotal] = gid
                    self.r.sadd(TOTAL_SET_KEY, keyAppTotal)
                else:
                    if self.r.hexists(keyAppTotal, uuid) is False:
                        self.r.hset(keyAppTotal, uuid, 0)

                if keyAppDau not in dauMap:
                    if self.r.exists(keyAppDau) == 0:
                        self.r.hset(keyAppDau, uuid, 0)
                        self.r.expire(keyAppDau, 88000)
                    dauMap[keyAppDau] = gid
                    self.r.sadd(DAU_SET_KEY, keyAppDau)
                else:
                    if self.r.hexists(keyAppDau, uuid):
                        continue
                    else:
                        self.r.hset(keyAppDau, uuid, 0)

                # 判断是否新增
                createdAt, bitmap = self.isNewUser(appKey, uuid)
                if createdAt is None:
                    serverDatetime = datetime.datetime.fromtimestamp(serverTime)
                    values = (appKey, uuid, serverDatetime, 0, 0)
                    result.append(values)
                else:
                    diffDays = (datetime.datetime.fromtimestamp(serverTime).date() - createdAt.date()).days - 1
                    if diffDays >= 0 and diffDays <= MAX_RETENTION_DAYS:
                        bitmap = bitmap | 1 << diffDays
                        values = (appKey, uuid, createdAt, bitmap, bitmap)
                        result.append(values)

        except Exception, e:
            raise Exception("parseRecordData error:" + str(e))

        return result

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
            self.nextPosition = self.startPosition + 1
            self.initIb()
            # 判断是否有新数据
            if self.checkCondition(self.nextPosition) is not True:
                self.dataCount = 0
                self.info("No data")
                return False

            # 解析保存数据
            recordList, count = self.getRecordList(self.startPosition, self.nextPosition)
            self.closeIb()
            parseData = self.parseRecordData(recordList)

            if self.addNewUser(parseData) > 0:
                self.updatePosition()
                self.info("use time : " + str(time.time() - startTimeStamp))

            if self.loop_times >= UPDATE_LOOPS:
                # 更新活跃
                self.updateReportDau()
                # 更新新增
                self.updateReportNewUser()

        except Exception, e:
            self.error("run error:" + str(e))


if __name__ == '__main__':
    sleepCount = 0
    loop_times = 1
    max_count = float(LIMIT_COUNTS)
    while 1:
        obj = AdNewUser('ad_new_user')
        if obj.flag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue

        obj.loop_times = loop_times
        obj.run()
        count = obj.dataCount
        if count > 0:
            if loop_times >= UPDATE_LOOPS:
                loop_times = 1
            else:
                loop_times = loop_times + 1

        if count < max_count:
            obj.info("zzz")
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
