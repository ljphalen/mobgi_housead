#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import datetime
from tool_base import ToolBase
import config.adx as configAdx
import config.db as configDb
import binascii


class toolNewUser(ToolBase):
    cutTableNum = configAdx.USER_TABLE_COUNTS  # 分表个数
    cutTable_pr = "user_"  # 分表前缀
    limit_count = 2000  # configAdx.LIMIT_COUNTS #每次提取的数据量
    dataLenght = 0

    def init(self):
        self.old_user_table = "intergration_report_new_user"

        self.dbNewUserDataDB = self.initDb(configDb.MYSQL_MOBGI_USER)
        self.dbOldUserDataDB = self.initDb(configDb.MYSQL_MOBGI_DATA_OLD)

    def checkCondition(self, startPosition):
        if startPosition < self.lastPosition:
            return True
        # 检测边界,按照降序排列
        sql = "select created_at from %s order by created_at desc limit 0,1 " % (self.old_user_table)
        result = self.dbOldUserDataDB.fetchone(sql)

        if result is None:
            return False
        else:
            self.lastPosition = int(time.mktime(result['created_at'].timetuple()))
            return startPosition <= self.lastPosition

    # 取出旧表数据
    def getRecordList(self):
        sql = "select * from %s order by created_at limit %s" % (self.old_user_table, self.limit_count)
        result, self.dataLenght = self.dbOldUserDataDB.fetchall(sql)
        if self.dataLenght > 0:
            self.nextPosition = int(time.mktime(result[0]['created_at'].timetuple()))
            return result
        else:
            return False

    # 写入新表数据
    def addFromoldTable(self, data):
        inserData = {}
        delData = []
        for value in data:
            tableId = self.cutTable(value['uuid'])
            if tableId not in inserData:
                inserData[tableId] = []
            createDate, createTime = self.dateTimeToFormat(str(value['created_at']))
            inserData[tableId].append((value['app_key'], value['uuid'], createDate, createTime, value['bitmap'], createDate))
            delData.append({
                'app_key': value['app_key'],
                'uuid': value['uuid']
            })

        self.info('inserData')
        for tableId in inserData:
            tableName = self.cutTable_pr + str(tableId)
            sql = "insert into " + tableName + " (app_key,uuid,channel_gid,create_date,create_time,bitmap) " \
                                               "values (%s,%s,0,%s,%s,%s) on duplicate key update create_date=%s"
            result = self.dbNewUserDataDB.executeMany(sql, inserData[tableId])
            if result is False:
                return False
        self.info('delData')
        for item in delData:
            delcheck = self.delOldTableData(item['uuid'], item['app_key'])
        return True

    # 转换时间格式
    def dateTimeToFormat(self, dateTime):
        timeArray = time.strptime(dateTime, "%Y-%m-%d %H:%M:%S")
        createDate = time.strftime("%Y-%m-%d", timeArray)
        createTime = time.strftime("%H:%M:%S", timeArray)
        return createDate, createTime

    # 删除旧表数据
    def delOldTableData(self, uuid, appKey):
        sql = "delete from %s where uuid='%s' and app_key='%s'" % (self.old_user_table, uuid, appKey)
        return self.dbOldUserDataDB.execute(sql)

    # 分表策略
    def cutTable(self, uuid):
        # tableNo = binascii.crc32(uuid) % self.cutTableNum
        # return self.cutTable_pr + '_' + str(tableNo)
        return binascii.crc32(uuid) % self.cutTableNum

    def run(self, lastPosition):
        try:
            startTimeStamp = time.time()
            self.lastPosition = lastPosition
            self.startPosition, status = self.getStartPosition()
            if self.checkCondition(self.startPosition) is not True:
                self.dataLenght = 0
                self.info("No data")
                return False
            # 解析保存数据
            self.info("startPosition:" + str(datetime.datetime.fromtimestamp(self.startPosition)))
            recordList = self.getRecordList()

            if self.addFromoldTable(recordList) is not True:
                self.dataLenght = 0
                self.info("save Data error!")
            else:
                self.updatePosition()
            self.info("use time : " + str(time.time() - startTimeStamp))
        except Exception, e:
            self.error("run error:" + str(e))


if __name__ == '__main__':
    sleepCount = 0
    lastPosition = 0
    while 1:
        obj = toolNewUser('tool_new_user')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue

        if sleepCount > 10:  # 错误过多自动退出
            obj.error("too many error to quit")
            break

        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            sleepCount += 1
        obj.run(lastPosition)
        lastPosition = obj.lastPosition

        if obj.dataLenght == 0:
            sleepCount += 1
            obj.info("zzz")
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
        else:
            sleepCount = 0
