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

#曝光监控统计脚本
class monitorimpressions(MonitorBase):

    #初始化条件
    def runInit(self):
        self.dbNewData = self.initDb(configDb.MYSQL_MOBGI_DATA)  # 初始化data库
        self.dbMonitor = self.initDb(configDb.MYSQL_MOBGI_MONITOR)  # 初始化Monitor库
        self.hourTable = 'report_hour' #impressions_hour表
        self.scriptTable = 'monitor_script'#monitor表
        self.MonitorTaskTable = 'monitor_task'  # task表
        self.MonitorImpressionsTable = 'monitor_impressions'#曝光数据表
        self.RestDayTable = 'monitor_daytype'#节假日表
        self.floatRate = 0.05


    #获取展示历史数据
    def getImpresionsList(self):
        try:  # 求出条件下所有的历史数据
            if int(self.taskConfig['pos_key']) != 0:
                if int(self.condition['period']) == 3600:
                    sql = "select sum(impressions) as impressions,app_key,pos_key,days,hours from %s where pos_key = '%s' and app_key = '%s' and days<='%s' and is_custom=0 group by days,hours" % (
                self.hourTable, self.taskConfig['pos_key'], self.taskConfig['app_key'], self.days)
                else:
                    sql = "select sum(impressions) as impressions,app_key,pos_key,days,0 as hours from %s where pos_key = '%s' and app_key = '%s' and days<='%s' and is_custom=0 group by days" % (
                        self.hourTable, self.taskConfig['pos_key'], self.taskConfig['app_key'], self.days)
            else:
                if int(self.condition['period']) == 3600:
                    sql = "select sum(impressions) as impressions,app_key,days,hours from %s where app_key = '%s' and days<='%s' and is_custom=0 group by days,hours" % (
                    self.hourTable,self.taskConfig['app_key'], self.days)
                else:
                    sql = "select sum(impressions) as impressions,app_key,days,0 as hours from %s where app_key = '%s' and days<='%s' and is_custom=0 group by days" % (
                    self.hourTable,self.taskConfig['app_key'], self.days)
            allData, count = self.dbNewData.fetchall(sql)
            # 处理成为机器学习的处理模式
            if count == 0:
                self.info("no impressions data")
                return False
        except Exception, e:
            self.info("getImpressionsList Err:" + str(e))
        return allData

    #获得当日数据
    def getTodayData(self):
        try:  # 求出实际数据做对比
            if int(self.taskConfig['pos_key']) != 0:
                if int(self.condition['period']) == 3600:
                    sql = "select sum(impressions) as impressions from %s where pos_key = '%s' and app_key = '%s' and days='%s' and hours=%s and is_custom = 0" % (
                        self.hourTable, self.taskConfig['pos_key'], self.taskConfig['app_key'], self.days, self.hours)
                else:
                    sql = "select sum(impressions) as impressions from %s where pos_key = '%s' and app_key = '%s' and days='%s' and is_custom = 0" % (
                        self.hourTable, self.taskConfig['pos_key'], self.taskConfig['app_key'], self.days)
            else:
                if int(self.condition['period']) == 3600:
                    sql = "select sum(impressions) as impressions from %s where app_key = '%s' and days='%s' and hours=%s and is_custom = 0" % (
                        self.hourTable,self.taskConfig['app_key'], self.days, self.hours)
                else:
                    sql = "select sum(impressions) as impressions from %s where app_key = '%s' and days='%s' and is_custom = 0" % (
                        self.hourTable,self.taskConfig['app_key'], self.days)
            todayData= self.dbNewData.fetchone(sql)
        except Exception, e:
            self.info("getImpressionsList Err:" + str(e))
        return np.log(int(todayData['impressions']))

    #预测以及得出结论
    def predictData(self,data):
        predict = {}
        if int(self.condition['period']) ==3600:
            predictObj = prophet()
        else:
            predictObj = prophet(None,'days')
        formatData = predictObj.exchangeFormat(data,'impressions')
        predictData = predictObj.predict(formatData)
        dayData = predictData.loc[predictData['ds'] == self.date]
        predict['impressions_predict'] = dayData['yhat'].values[0]
        predict['impressions_upper'] = dayData['yhat_upper'].values[0]
        predict['impressions_lower'] = dayData['yhat_lower'].values[0]
        predict['impressions'] = self.getTodayData()
        return predict


    #判断是否报警操作,后期加上报警程度,暂时不区分报警程度
    #{'impressions_lower': 11.224159355056051, 'Impressions': 11.36161408385013, 'impressions_upper': 11.98936913723599, 'impressions_predict': 11.621798855650544}
    def checkData(self,predictData):
        #浮动比率5%
        if predictData['impressions_lower'] > predictData['impressions']+predictData['impressions_lower']*self.floatRate:
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
            'hours':self.hours,
            'task_id':self.taskId,
            'pos_key':self.taskConfig['pos_key'],
            'impressions':round(data['impressions'],4),
            'create_time':self.exchangeTimeStampDate(time.time(),'%Y-%m-%d %H:%M:%S'),
            'impressions_predict':round(data['impressions_predict'],4),
            'impressions_lower':round(data['impressions_lower'],4),
            'impressions_upper':round(data['impressions_upper'],4),
            'is_warming':is_warming,
        }
        return formatData


    #存入数据
    def saveData(self, predictData):
        if len(predictData) != 0:
            needWarming = self.checkData(predictData)
            formatData = self.formatData(predictData,needWarming)
            sql = """insert into %s (task_id,app_key,days,hours,pos_key,create_time,value,predict,lower,upper,is_warming)values(%s,'%s','%s',%s,%s,'%s',%s,%s,%s,%s,%s)"""%(self.MonitorImpressionsTable,formatData['task_id'],formatData['app_key'],formatData['days'],formatData['hours'],formatData['pos_key'],formatData['create_time'],formatData['impressions'],formatData['impressions_predict'],formatData['impressions_lower'],formatData['impressions_upper'],formatData['is_warming'])
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
        if self.taskConfig['pos_key'] != '0':
            posName = self.getPoskeyMap(self.taskConfig['pos_key'])
        else:
            posName = "不区分广告位"
        info = {
            'title':'[曝光预警]',
            'content':'监控应用:'+appName+'|广告位:'+posName+'|发生时间:'+str(data['days'])+' '+str(data['hours'])+'时,请知悉！'
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
        self.info('-----start monitor_impressions script----')
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
            allData = self.getImpresionsList()
            #整理格式进行预测,得出预测数据
            predictData = self.predictData(allData)
            #存入数据
            if self.saveData(predictData) is not False:
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





