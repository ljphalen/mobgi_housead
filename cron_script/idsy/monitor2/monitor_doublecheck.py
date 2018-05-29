#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import json
from monitor_base import MonitorBase
import config.db as configDb
from lib.monitor import CheckTask
from lib.prophet import prophet
import numpy as np
import traceback

#doublecheck监控统计脚本
class monitordoublecheck(MonitorBase):

    #初始化条件
    def runInit(self):
        self.dbNewData = self.initDb(configDb.MYSQL_MOBGI_DATA)  # 初始化data库
        self.dbMonitor = self.initDb(configDb.MYSQL_MOBGI_MONITOR)  # 初始化Monitor库
        self.thirdDataTable = 'report_third_data' #impressions_hour表
        self.scriptTable = 'monitor_script'#monitor表
        self.MonitorTaskTable = 'monitor_task'  # task表
        self.MonitorEcpmTable = 'monitor_ecpm'#ecpm数据表
        self.RestDayTable = 'monitor_daytype'#节假日表


    # 获取监控脚本状态
    def checkCondition(self):
        self.condition = self.getTask(self.taskId)
        self.taskConfig = json.loads(self.condition['params'])
        self.info('-----start monitor_doublecheck script----')
        self.info('taskId:'+str(self.condition['id'])+' period:'+str(self.condition['period'])+' thisUpdatePosition:'+str(self.exchangeTimeStampDate(self.exchangeTimeStamp(str(self.condition['next_time'])), '%Y-%m-%d %H:%M:%S')))
        self.startPosition = self.exchangeTimeStamp(str(self.condition['next_time']))
        self.monitorPosition = self.startPosition-self.condition['period']
        self.days = self.exchangeTimeStampDate(self.monitorPosition, '%Y-%m-%d')
        self.hours = self.exchangeTimeStampDate(self.monitorPosition, '%H')
        self.date = str(self.days)+' '+str(self.hours)+':00:00'
        if self.condition['status'] != 1 and self.condition['status'] !=3:
            self.info('The script is not open!')
            return False
        if time.time()< self.startPosition:
            self.info('The script is not to StartTime!')
            return False
        return True

    def run(self,taskId):
        try:
            self.runInit()
            self.taskId = taskId
            if self.checkCondition() is not True:
                return False
            #根据任务读取历史数据
            self.endTask(self.taskId)  # 更新位置
            script_name = ('report_api_' + self.taskConfig['ads_id'] + '.py').lower()
            api_path = os.path.abspath(os.path.join(os.path.dirname(__file__), "..")) + '/api/'
            cmd = "/usr/bin/python " + os.path.join(api_path, script_name) + " " + str(self.taskConfig['time_length'])
            self.info('apiPath:' + cmd)
            os.system(cmd)
            return True
        except Exception, e:
            traceback.print_exc()
            self.info("run error:" + str(e))


if __name__ == '__main__':
    startTimeStamp = time.time()
    taskId = CheckTask(sys.argv)
    #taskId = 2
    obj = monitordoublecheck('monitor_doublecheck')
    obj.run(taskId)





