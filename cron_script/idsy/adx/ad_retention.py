#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
from ad_base import AdBase
import time
import datetime
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis


# 留存统计
class AdRetention(AdBase):
    dataCount = 0
    max_retention_days = 8
    loginTable = configAdx.TABLE_MID_USERS
    retentionTable = 'report_retention'

    def init(self):
        self.statConfig = configDb.MYSQL_BH_AD_MID
        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.dbData = self.initDb(self.dataConfig)
        self.ibLoginLog = self.initDb(self.statConfig)

    # 检查是否能够获取当日的留存信息
    def checkCondition(self, startDate):
        sql = "SELECT count(*) as count FROM %s WHERE create_date>'%s'" % (self.loginTable, startDate)
        result = self.ibLoginLog.fetchone(sql)
        # 判断记录是否存在
        if result is not None and result['count'] > 0:
            return True
        else:
            return False

    def saveRetentin(self, data):
        if len(data) < 1:
            self.info('len(data) <1')
            return True
        for app_key in data:
            for item in data[app_key]:
                sql = "insert into %s (app_key,days,r%s) values ('%s','%s',%s)on duplicate key update r%s=%s" % (
                    self.retentionTable, item["rday"], app_key, item["days"], item["count"], item["rday"], item["count"])
                self.dbData.execute(sql)

        return True

    # 根据参数得到某一天的7日的留存
    def getRecordList(self, action_date):

        rdays = self.max_retention_days  # 7日留存
        result = {}
        for rday in range(1, rdays):
            create_date = action_date - datetime.timedelta(days=rday)
            sql = "select app_key,count(distinct(user_id)) as count from %s where event_type=15 and action_date='%s' and create_date='%s' group by " \
                  "app_key" % (self.loginTable, action_date, create_date)
            list, count = self.ibLoginLog.fetchall(sql)
            if count > 0:
                for item in list:
                    if item['app_key'] not in result:
                        result[item['app_key']] = []
                    result[item['app_key']].append({
                        "days": create_date,
                        "rday": rday,
                        "count": item['count']
                    })
        return result

    def run(self):
        try:
            startTimeStamp = time.time()
            self.startPosition, status = self.getStartPosition()
            self.nextPosition = self.startPosition + 86400
            start_date = datetime.datetime.fromtimestamp(self.startPosition).date()
            self.info("Retention:" + str(start_date))
            # 判断状态
            if status != 1:
                self.dataCount = 0
                self.info("status is stop")
                return False

            if self.checkCondition(start_date) is not True:
                self.dataCount = 0
                self.info("No data")
                return False

            # 获取留存数据 默认获取下一天
            data = self.getRecordList(start_date)
            if self.saveRetentin(data) is True:
                self.updatePosition()
                self.info("use time : " + str(time.time() - startTimeStamp))
                return True
            else:
                self.info("saveRetentin fail")
                return False

        except Exception, e:
            self.error("run error:" + str(e))


# 进行当天的七日留存计算
if __name__ == '__main__':
    sleepCount = 0

    while 1:
        if sleepCount > 3:  # 错误超过三次退出
            break
        obj = AdRetention('ad_retention')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            sleepCount += 1
            continue
        if obj.run() is not True:
            obj.info("zzz")
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            # time.sleep(1)
            sleepCount += 1
            continue
