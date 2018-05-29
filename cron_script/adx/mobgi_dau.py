#!/usr/bin/env python
# coding=utf8
import os
import time
import config.adx as configAdx
import config.mobgi as configMobgi
import config.db as configDb
import lib.mysql as db
from adx_base import AdxBase


# 此脚本用来计算每天的基于应用的广告DAU

class MobgiDau(AdxBase):
    def init(self):
        self.dbData = self.initDb(configDb.MYSQL_MOBGI_DATA)
        self.ibTable = configMobgi.IB_TABLE_MOBGI_STAT
        self.ibFields = configMobgi.IB_FIELDS_MOBGI_STAT
        self.dbIB = self.initDb(configDb.MYSQL_BH_MOBGI)

    # 获取最后统计表的时间
    def getLastRecordTime(self):
        sql = "select server_time from %s order by id desc limit 1" % (self.ibTable)
        result = self.dbIB.fetchone(sql)
        if result is not None and "server_time" in result:
            return int(result['server_time'])
        else:
            return 0

    def checkCondition(self, startPosition):
        today = int(time.mktime(time.strptime(time.strftime('%Y-%m-%d', time.localtime()), "%Y-%m-%d")))
        lastTime = self.getLastRecordTime()
        if startPosition < today and startPosition + 88000 < lastTime:
            return True
        else:
            return False

    def getRecordList(self, startPosition):
        try:
            self.lastPostion = startPosition + 86400
            eventType = 15
            # 最主要基于应用维度，针对活跃用户去重
            sql = """select consumerkey,os,eventtype,server_time,count(DISTINCT(uuid)) AS count from %s where eventtype = %s and
            server_time >= %s and server_time < %s group by consumerkey,os,eventtype""" % (self.ibTable, eventType, startPosition, self.lastPostion)
            result1, count = self.dbIB.fetchall(sql)

            eventType = tuple(configMobgi.MOBGI_USER_EVENT_TYPE.keys())
            # 最主要基于应用维度，针对活跃用户去重
            sql = """select consumerkey,os,1 as eventtype  ,server_time,count(DISTINCT(uuid)) AS count from %s where eventtype in %s and
            server_time >= %s and server_time < %s group by consumerkey,os""" % (self.ibTable, eventType, startPosition, self.lastPostion)
            result2, count = self.dbIB.fetchall(sql)
            result = result1 + result2
            self.dbIB = None
            return result
        except Exception, e:
            raise Exception('getRecordList Exception:' + str(e))

    def filterData(self, recordData):
        result = {}
        if len(recordData) < 1:
            self.info('len(filterData) < 1')
            return result
        try:
            dauEventType = configMobgi.MOBGI_DAU_EVENT_TYPE
            for record in recordData:
                app_key = str(record.get('consumerkey'))
                platform = str(record.get('os'))
                # 过滤无用数据
                if len(app_key) != 20:
                    continue
                if platform != '0' and platform != '1':
                    continue
                server_time = time.strftime('%Y-%m-%d', time.localtime(float(record.get("server_time"))))
                eventType = dauEventType[int(record.get('eventtype'))]
                # 基于应用维度
                key = str(server_time) + app_key + platform
                if result.has_key(key) is False:
                    result[key] = {
                        "app_key": app_key,
                        "platform": int(platform) + 1,
                        "date_of_log": server_time
                    }
                    for event in dauEventType:
                        result[key][dauEventType[event]] = 0
                result[key][eventType] = int(record.get('count'))
        except Exception, e:
            raise Exception('filterData Exception:' + str(e))
        return result

    def formatData(self, listData):
        result = []
        if len(listData) < 1:
            self.info('len(formatData) < 1')
            return result
        try:
            for tmp in listData:
                values = (listData[tmp]["app_key"], listData[tmp]["platform"], listData[tmp]["date_of_log"], listData[tmp]["user_total"],
                          listData[tmp]["dau_user"], listData[tmp]["user_total"], listData[tmp]["dau_user"])
                result.append(values)
            return tuple(result)
        except Exception, e:
            raise Exception('formatData Exception:' + str(e))

    def updateDau(self, data):
        if len(data) < 1:
            self.info('len(updateDau) < 1')
            return False
        try:
            if time.time() - self.start_timestamp>59:
                self.dbData = self.initDb(configDb.MYSQL_MOBGI_DATA)
            sql = """insert into `intergration_report_app_dau`(`app_key`,`platform`,`date_of_log`,`user_total`,`dau_user`)
            values(%s,%s,%s,%s,%s) on duplicate key update `user_total` = %s,`dau_user`= %s"""
            data = self.dbData.executeMany(sql, data)
            return True
        except Exception, e:
            self.error("updateDau Exception:" + str(e))
            self.dbData = None
            self.dbData = self.initDb(configDb.MYSQL_MOBGI_DATA)
            return False

    def getNextPostion(self, startPosition):
        postion = startPosition + self.ONE_DAY
        return postion

    # infobright数据汇总统计
    def run(self):
        try:
            startTimeStamp = time.time()

            startPosition, status = self.getStartPosition()
            self.info("dau:" + time.strftime('%Y-%m-%d', time.localtime(startPosition)))
            # 判断状态
            if status != 1:
                self.dataCount = 0
                self.info("status is stop")
                return False
            # 判断是否有新数据
            if self.checkCondition(startPosition) is not True:
                self.info("Condition does not meet")
                return False

            # 解析保存数据
            recordList = self.getRecordList(startPosition)
            filterData = self.filterData(recordList)
            if len(filterData) == 0:
                return False
            formatData = self.formatData(filterData)
            if (self.updateDau(formatData) is True or self.updateDau(formatData) is True):
                self.updatePosition()
                self.info("use time : " + str(time.time() - startTimeStamp))
                return True
            else:
                self.error("updateDau Error")
        except Exception, e:
            self.error(str(e))


if '__main__' == __name__:
    startTimeStamp = time.time()
    while (1):
        obj = MobgiDau("mobgi_dau")
        if obj.flag:
            obj.info("zzz:" + str(configAdx.SLEEP_SECOND))
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        if obj.run() is not True:
            quit()
        obj.info("zzz:" + str(configAdx.SLEEP_SECOND))
        obj = None
        time.sleep(configAdx.SLEEP_SECOND)
        # 脚步执行时间超过50分钟直接跳出
        if int(time.time() - startTimeStamp) > 3000:
            break