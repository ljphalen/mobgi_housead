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

#ecpm监控统计脚本
class monitorimpressions(MonitorBase):

    #初始化条件
    def runInit(self):
        self.dbNewData = self.initDb(configDb.MYSQL_MOBGI_DATA)  # 初始化data库
        self.dbMonitor = self.initDb(configDb.MYSQL_MOBGI_MONITOR)  # 初始化Monitor库
        self.thirdDataTable = 'report_third_data' #impressions_hour表
        self.scriptTable = 'monitor_script'#monitor表
        self.MonitorTaskTable = 'monitor_task'  # task表
        self.MonitorEcpmTable = 'monitor_ecpm'#ecpm数据表
        self.RestDayTable = 'monitor_daytype'#节假日表


    #获得当日数据
    def getTodayData(self):
        try:  # 求出实际数据做对比
            sql = "select app_key,sum(third_views) as third_views,sum(ad_income) as ad_income,ad_type,days from %s where app_key = '%s' and days = '%s' and ad_type = %s"%(self.thirdDataTable, self.taskConfig['app_key'],self.days,self.taskConfig['ad_type'])
            todayData= self.dbNewData.fetchone(sql)
        except Exception, e:
            self.info("getecpmList Err:" + str(e))
        return todayData



    #判断是否报警操作,后期加上报警程度,暂时不区分报警程度
    #{'impressions_lower': 11.224159355056051, 'Impressions': 11.36161408385013, 'impressions_upper': 11.98936913723599, 'impressions_predict': 11.621798855650544}
    def checkData(self,predictData):
        if self.taskConfig['ecpm_min_val'] > predictData['ecpm']:
            #需要预警的条件
            isWarming = 1
        else:
            isWarming = 0
        return isWarming



    #获取请求日期是否为工作日daytype=0为工作日1为周末2为法定节假日
    def getIsWorkDay(self, days):
        sql = "select count(*) as count from %s where days = '%s' and daytype = 0"%(self.RestDayTable,days)
        result = self.dbMonitor.fetchone(sql)
        if result['count'] == 1:
            return True
        else:
            return False


    #整理数据
    def formatData(self,data,is_warming):
        formatData={
            'app_key':self.taskConfig['app_key'],
            'days':self.days,
            'task_id':self.taskId,
            'ad_type':self.taskConfig['ad_type'],
            'ecpm':round(data['ecpm'],4),
            'create_time':self.exchangeTimeStampDate(time.time(),'%Y-%m-%d %H:%M:%S'),
            'ecpm_lower':self.taskConfig['ecpm_min_val'],
            'is_warming':is_warming,
        }
        return formatData


    #存入数据
    def saveData(self, predictData):
        if len(predictData) != 0:
            if predictData['third_views'] is not None and int(predictData['third_views']) != 0 :
                predictData['ecpm'] =float('%.2f' % ((predictData['ad_income'] / predictData['third_views']) * 1000))
            else:
                self.info('third_views is 0')
                return False
            needWarming = self.checkData(predictData)
            formatData = self.formatData(predictData,needWarming)
            sql = """insert into %s (task_id,app_key,days,ad_type,create_time,value,lower,is_warming)values(%s,'%s','%s',%s,'%s',%s,%s,%s)"""%(self.MonitorEcpmTable,formatData['task_id'],formatData['app_key'],formatData['days'],formatData['ad_type'],formatData['create_time'],formatData['ecpm'],formatData['ecpm_lower'],formatData['is_warming'])
            logId = self.dbMonitor.insert(sql)
            if needWarming:
                #存入taskLog
                formatData['log_id'] = logId
                formatData['monitor_type'] = self.taskConfig['monitor_type']
                self.saveTaskLog(formatData)#存储监控事件log
                self.sendMsg(formatData)
            return True
        else:
            self.info('NO DATA!')
            return False


    #报警发送准备，存储发送记录
    def sendMsg(self,data):
        allUserInfo = self.getAllUserInfo(self.condition['warning_target'])
        tels = []
        emails = []
        for item in allUserInfo:
            tels.append(str(item['tel']))
            emails.append(str(item['email']))
        # 組裝数据
        appName = self.getAppMap(self.taskConfig['app_key'])
        adTypeName = self.getAdTypeMap(self.taskConfig['ad_type'])
        info = {
            'title':'[ecpm预警]',
            'content':'监控应用:'+appName+'|广告类型:'+adTypeName+'|发生时间:'+str(data['days'])+',请知悉！'
        }
        userTelInfo = {
            'tel':','.join(tels)
        }
        userEmailInfo = {
            'email': ','.join(emails)
        }
        if int(self.condition['warning_type']) == 3:
            telConfig = {
                'send_type': 1,
                'send_time': -1,
            }
            self.checkSendMsgType(info,telConfig,userTelInfo)
            emailConfig = {
                'send_type': 2,
                'send_time': -1,
            }
            self.checkSendMsgType(info,emailConfig,userEmailInfo)
        elif int(self.condition['warning_type']) == 2:
            config = {
                'send_type': self.condition['warning_type'],
                'send_time': -1,
            }
            self.checkSendMsgType(info,config,userEmailInfo)
        else:
            telConfig = {
                'send_type': 1,
                'send_time': -1,
            }
            self.checkSendMsgType(info, telConfig, userTelInfo)


    # 获取监控脚本状态
    def checkCondition(self):
        self.condition = self.getTask(self.taskId)
        self.taskConfig = json.loads(self.condition['params'])
        self.info('-----start monitor_ecpm script----')
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
            allData = self.getTodayData()
            #整理格式进行预测,得出预测数据
            #predictData = self.predictData(allData)
            #存入数据
            if self.saveData(allData) is not False:
                self.endTask(self.taskId)#更新位置
            else:
                self.info('Save data error! or NO DATA!')
                return False
        except Exception, e:
            traceback.print_exc()
            self.info("run error:" + str(e))


if __name__ == '__main__':
    startTimeStamp = time.time()
    taskId = CheckTask(sys.argv)
    #taskId = 2
    obj = monitorimpressions('monitor_impressions')
    obj.run(taskId)





