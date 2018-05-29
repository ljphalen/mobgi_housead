#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
from monitor_base import MonitorBase
from lib.task import CheckTask
import config.db as configDb


# ecpm
# 监控统计脚本
class monitorecpm(MonitorBase):
    # 初始化条件
    def runInit(self):
        self.dbNewData = self.initDb(configDb.MYSQL_MOBGI_DATA)  # 初始化data库
        self.dbMonitor = self.initDb(configDb.MYSQL_MOBGI_MONITOR)  # 初始化Monitor库
        self.ThirdDataTable = 'report_third_data'  # 获取第三方数据的表
        self.MonitorTable = 'ad_monitor'  # monitor表
        self.CapitalTable = 'ad_ecpm'  # ecpm表,记录每天的ecpm值
        self.MonitorTaskTable = 'ad_task'  # task表

    # 获取所有appkey和third_views值
    def getInfos(self):
        try:
            sql = "select app_key,sum(third_views) as third_views,sum(ad_income) as ad_income,ad_type,days from %s where days = '%s' group by " \
                  "app_key,ad_type" % (
            self.ThirdDataTable, self.getDatePosition)
            items, count = self.dbNewData.fetchall(sql)
        except Exception, e:
            self.info("getGameIdMap Err:" + str(e))
        result = {}
        for item in items:
            create_time = self.exchangeTimeStampDate(time.time(), "%Y-%m-%d %H:%M:%S")
            if item['third_views'] != 0 and item['ad_income'] != 0:
                result[item['app_key'] + str(item['ad_type']) + str(item['days'])] = {
                    'app_key': item['app_key'],
                    'ecpm': float('%.2f' % ((item['ad_income'] / item['third_views']) * 1000)),
                    'ad_type': item['ad_type'],
                    'days': item['days'],
                    'create_time': create_time
                }
            else:
                result[item['app_key'] + str(item['ad_type']) + str(item['days'])] = {
                    'app_key': item['app_key'],
                    'ecpm': 0,
                    'ad_type': item['ad_type'],
                    'days': item['days'],
                    'create_time': create_time
                }
        return result

    # 修正格式
    def formatApiData(self, listData):
        if len(listData) < 1:
            return []
        result = []
        for item in listData.values():
            values = (item["app_key"], item["days"], item["ecpm"], item["create_time"], item['ad_type'], item["ecpm"])
            result.append(values)
        return tuple(result)

    # 存入数据
    def saveData(self, Data):
        if len(Data) !=0:
            data = self.formatApiData(Data)
            sql = """insert into ad_ecpm(app_key,days,ecpm,create_time,ad_type)values(%s,%s,%s,%s,%s)on duplicate key update ecpm=%s"""
            self.info("data:" + str(data))
            return self.dbMonitor.executeMany(sql, data)
        else:
            self.info('NO DATA!')
            return False

    # 更新监控脚本状态
    def updatePosition(self):
        nextPosition = self.startPosition + self.period
        next_time = self.exchangeTimeStampDate(nextPosition, '%Y-%m-%d %H:%M:%S')
        sql = "update %s set next_time = '%s',last_time = '%s' where title = '%s'" % (
        self.MonitorTable, next_time, self.getDatePosition, self.scriptName)
        self.dbMonitor.execute(sql)
        self.info('next position is:' + str(next_time))

        # 获取监控脚本状态

    def checkCondition(self):
        sql = "select * from %s where id = '%s'" % (self.MonitorTaskTable, self.taskId)
        condition = self.dbMonitor.fetchone(sql)
        # 这个是跑数据的实际日期
        self.startPosition = self.exchangeTimeStamp(str(condition['next_time']))  # nextposition 为跑前一天的数据
        self.startDatePosition = self.exchangeTimeStampDate(self.startPosition, '%Y-%m-%d')
        self.startDateTimePosition = self.exchangeTimeStampDate(self.startPosition, '%Y-%m-%d %H:%M:%S')  # 用这个来取数据

        # 这个是能跑出来数据的实际日期，一般来说跑的数据是跑的时候的前一天的数据
        self.getPosition = int(self.startPosition) - 86400  # 这是获取数据的真实日期
        self.getDatePosition = self.exchangeTimeStampDate(self.getPosition, '%Y-%m-%d')  # 用这个来取数据
        self.getDateTimePosition = self.exchangeTimeStampDate(self.getPosition, '%Y-%m-%d %H:%M:%S')  # 用这个来取数据
        self.period = condition['period']
        self.info('start position data for Dyas is:' + str(self.startDateTimePosition))
        self.info('get position data for Dyas is:' + str(self.getDateTimePosition))
        self.info('use Time start:' + time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time())))
        if condition['status'] != 1 and condition['status'] != 3:
            self.info('The script is not open!')
            return False
        if time.time() < self.startPosition:
            self.info('The script is not to StartTime!')
            return False
        return True

    def run(self, taskId):
        try:
            self.runInit()
            self.taskId = taskId
            if self.checkCondition() is not True:
                return False
            info = self.getInfos()  # 获取某日[应用,广告类型,展示次数,收益]
            if self.saveData(info) is not False:
                self.endTask(self.taskId)  # 更新位置
            else:
                self.info('Save data error! or NO DATA!')
                return False
        except Exception, e:
            self.info("run error:" + str(e))


if __name__ == '__main__':
    startTimeStamp = time.time()
    taskId = CheckTask(sys.argv)
    while 1:
        obj = monitorecpm('monitor_ecpm')
        if obj.run(taskId) == False:
            break
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
