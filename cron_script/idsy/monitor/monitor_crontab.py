#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
from monitor_base import MonitorBase
from lib.task import CheckTask
import config.db as configDb


# 数据监控定时任务脚本
class monitorCrontab(MonitorBase):
    # 初始化条件
    def runInit(self):
        self.dbNewData = self.initDb(configDb.MYSQL_MOBGI_DATA)  # 初始化data库
        self.dbMonitor = self.initDb(configDb.MYSQL_MOBGI_MONITOR)  # 初始化Monitor库
        self.MonitorTaskTable = 'ad_task'

    # checkdatabase
    def checkTask(self,tasks):
        contentInfo = {}
        userInfo = {}
        config = {'send_time': -1, 'send_type': 1}
        for item in tasks:
            updateTimeStamp = self.exchangeTimeStamp(str(item['update']))
            if int(time.time()) > int(updateTimeStamp)+int(item['permid']):
                contentInfo['title'] = "Crontab脚本延迟预警"
                contentInfo['content'] = "[Scrpit Delay]:Script Name:"+str(item['script_name'])+" Last update:"+str(item['update'])
                userInfo['account'] = "17688939163"
                self.saveSendmsgquene(contentInfo,userInfo,config)
        return True

    def getAllCronTask(self):
        sql = "select * from config_cron where status = 1"
        tasks,count = self.dbNewData.fetchall(sql)
        if count > 0:
            return tasks
        else:
            self.error('no crontabTask')
            exit()



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
            allTask = self.getAllCronTask()
            if self.checkTask(allTask) is not False:
                self.endTask(self.taskId)  # 更新位置
            else:
                self.info('check error!')
                return False
        except Exception, e:
            self.info("run error:" + str(e))
            exit()


if __name__ == '__main__':
    startTimeStamp = time.time()
    taskId = CheckTask(sys.argv)
    obj = monitorCrontab('monitor_crontab')
    if obj.run(taskId) == False:
        exit()
