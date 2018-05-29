#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
from monitor_base import MonitorBase
from lib.task import CheckTask
import config.db as configDb


# databases
# 数据监控结果集脚本
class monitordatabase(MonitorBase):
    # 初始化条件
    def runInit(self):
        self.dbNewData = self.initDb(configDb.MYSQL_MOBGI_DATA)  # 初始化data库
        self.dbMonitor = self.initDb(configDb.MYSQL_MOBGI_MONITOR)  # 初始化Monitor库
        self.ibMiddle = self.initDb(configDb.MYSQL_BH_AD_MID)# 初始化middle库
        self.ibAdclient = self.initDb(configDb.MYSQL_BH_AD_STAT)
        self.adCilentTable = 'ad_client'
        self.adCilentValue = 1800
        self.ReportHourTable='report_hour'
        self.ReportHourValue = 7200
        self.ThirdDataTable = 'report_third_data'  # 获取第三方数据的表
        self.MonitorTable = 'ad_monitor'  # monitor表
        self.MonitorTaskTable = 'ad_task'  # task表

    # checkdatabase
    def checkdatabases(self):
        adClientsql = "select server_time from %s order by server_time desc limit 1"%(self.adCilentTable)
        checkAdclientRsult = self.ibAdclient.fetchone(adClientsql)
        if int(time.time()) - checkAdclientRsult['server_time'] > self.adCilentValue:
            lastUpdateTime = self.exchangeTimeStampDate(checkAdclientRsult['server_time'],'%Y-%m-%d %H:%M:%S')
            contentInfo = {'title': '数据库数据源报警','content': '[DataSoureErr]ad_client:最后数据更新时间:'+str(lastUpdateTime)}
            userInfo = {'account': '17688939163,13713849652'}
            config = {'send_time': -1,'send_type':1}
            self.saveSendmsgquene(contentInfo,userInfo,config)
        reportHourSql = "select update_time from %s order by update_time desc limit 1"%(self.ReportHourTable)
        checkReportHourResult =self.dbNewData.fetchone(reportHourSql)
        updateTime = self.exchangeTimeStamp(str(checkReportHourResult['update_time']))
        if int(time.time()) - updateTime > self.ReportHourValue:
            lastUpdateTime = checkReportHourResult['update_time']
            contentInfo = {'title': '数据库数据源报警','content': '[DataSoureErr]report_hour:最后数据更新时间:' +str(lastUpdateTime)}
            userInfo = {'account': '17688939163,13713849652'}
            config = {'send_time': -1, 'send_type': 1}
            self.saveSendmsgquene(contentInfo,userInfo,config)
        return True


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

        self.period = condition['period']
        self.info('start position data for Dyas is:' + str(self.startDateTimePosition))

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
            if self.checkdatabases() is not False:
                self.endTask(self.taskId)  # 更新位置
            else:
                self.info('check database error!')
                return False
        except Exception, e:
            self.info("run error:" + str(e))
            exit()


if __name__ == '__main__':
    startTimeStamp = time.time()
    taskId = CheckTask(sys.argv)
    obj = monitordatabase('monitor_database')
    if obj.run(taskId) == False:
        exit()

