#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
from monitor_base import MonitorBase
import config.db as configDb
from lib.task import CheckTask


#人均次数监控统计脚本
class monitorcapite(MonitorBase):

    #初始化条件
    def runInit(self):
        self.dbNewData = self.initDb(configDb.MYSQL_MOBGI_DATA)  # 初始化data库
        self.dbMonitor = self.initDb(configDb.MYSQL_MOBGI_MONITOR)  # 初始化Monitor库
        self.DauTable = 'report_dau'#dau表
        self.ReportDayTable  = 'report_day'#impressions表
        self.MonitorTable = 'ad_monitor'#monitor表
        self.MonitorTaskTable = 'ad_task'  # task表
        self.CapitalTable = 'ad_capital'#人均数据表
        self.RestDayTable = 'ad_days'#节假日表

    #获取所有或者某个appkey在某日的impressions值
    def getImpressions(self,appkey=None,days=None):
        try:
            if days is not None and appkey is not None:
                sql = "select app_key,sum(impressions) as impressions from %s where days = '%s' and app_key = '%s'" % (
                self.ReportDayTable,days,appkey)
                items = self.dbNewData.fetchall(sql)
            else:
                sql = "select app_key,sum(impressions) as impressions from %s where days = '%s' group by app_key"%(self.ReportDayTable,self.getDatePosition)
                items = self.dbNewData.fetchall(sql)
        except Exception, e:
            self.info("getGameIdMap Err:" + str(e))
        result = {}
        for item in items[0]:
            result[item['app_key']] = item['impressions']
        if appkey is not None:
            return result[appkey]
        else:
            return result

    #获取所有appkey和对应的dau值
    def getDauList(self,appkey=None,days=None):
        result = {}
        try:
            sql = "select sum(user_dau) as dau,app_key from %s where days=\"%s\" and channel_gid = 0 and ad_type = 0 GROUP by app_key" % (self.DauTable,self.getDatePosition)
            items = self.dbNewData.fetchall(sql)
        except Exception, e:
            self.info("getDauList Err:" + str(e))
        for item in items[0]:
            result[item['app_key']] = item['dau']
        if appkey is not None:
            return result[appkey]
        else:
            return result

    #计算当日全部应用的人均和预测人均
    def calCapite(self):
        calResult = {}
        for items in self.impressionsMap:
            if self.dauMap.has_key(items):#计算人均
                key = items+str(self.getDatePosition)
                if self.dauMap[items] != 0:
                    capital = float('%.2f' % (self.impressionsMap[items] / self.dauMap[items]))
                else:
                    capital = 0
                planCapital = self.getPlanCapital(items)#获取下一天的预测数据
                self.info('day='+self.getDatePosition+'appkey='+items+'capital='+str(capital)+'plancapital='+str(planCapital))
                create_time = self.exchangeTimeStampDate(time.time(),"%Y-%m-%d %H:%M:%S")
                calResult[key] = {
                    'app_key':items,
                    'capital':capital,
                    'plancapital':planCapital,
                    'days':self.getDatePosition,
                    'create_time':create_time
                }#获取指定日期的人均
        return calResult



    #获得预测数据,这个需要数据模型(以后在训练)
    def getPlanCapital(self,appkey):
        isWorkDay = self.getIsWorkDay(self.getDatePosition)
        if isWorkDay :#工作日的计算模式#py=y1(近30个工作日)*0.1+y2(近7个工作日)*0.2+y3(近3个工作日)*0.3+y4(近1个工作日)*0.4；
            y1 = self.calDayData(appkey,30,'workday')
            y2 = self.calDayData(appkey,7,'workday')
            y3 = self.calDayData(appkey,3,'workday')
            y4 = self.calDayData(appkey, 1, 'workday')
            return float('%.2f'%(y1*0.1+y2*0.2+y3*0.3+y4*0.4))
        else:#非工作日的计算模式#py=y1(近10个非工作日)*0.1+y2(近4个非工作日)*0.2+y3(近2个非工作日)*0.3+y3(近1个非工作日)*0.4；
            y1 = self.calDayData(appkey, 10, 'holiday')
            y2 = self.calDayData(appkey, 4, 'holiday')
            y3 = self.calDayData(appkey, 2, 'holiday')
            y4 = self.calDayData(appkey, 1, 'holiday')
            return float('%.2f'%(y1*0.1+y2*0.2+y3*0.3+y4*0.4))


    #获取某段时间内某个app在该时间内的人均的均值
    def calDayData(self,appkey,limit,type):
        if type is 'workday':
            sql = "select days from %s where days < '%s' and daytype =0 order by days desc limit 0,%s"%(self.RestDayTable,self.getDatePosition,limit)
        else:
            sql = "select days from %s where days < '%s' and daytype !=0 order by days desc limit 0,%s"%(self.RestDayTable,self.getDatePosition,limit)
        result,count = self.dbMonitor.fetchall(sql)
        sum = 0
        for item in result:
            sql = "select a.app_key,user_dau as dau,a.days,sum(b.impressions) as impressions from %s as b left join %s as a \
                   on a.app_key = b.app_key and a.channel_gid = 0 and a.ad_type=0 where a.days = '%s' and b.days = '%s' and \
                   a.app_key = '%s' and b.app_key = '%s';"%(self.ReportDayTable,self.DauTable,str(item['days']),str(item['days']),appkey,appkey)
            temp = self.dbNewData.fetchone(sql)
            if temp['impressions'] != None and temp['dau'] != None and temp['dau'] !=0:
                sum +=float('%.2f'%(temp['impressions']/temp['dau']))
        return float('%.2f'%(sum/count))



    #获取请求日期是否为工作日daytype=0为工作日1为周末2为法定节假日
    def getIsWorkDay(self, days):
        sql = "select count(*) as count from %s where days = '%s' and daytype = 0"%(self.RestDayTable,days)
        result = self.dbMonitor.fetchone(sql)
        if result['count'] == 1:
            return True
        else:
            return False

    #修正格式
    def formatApiData(self, listData):
        if len(listData) < 1:
            return []
        result = []
        for item in listData.values():
            values = (
                item["app_key"], item["days"], item["capital"], item["plancapital"], item["create_time"], item["capital"], item["plancapital"])
            result.append(values)
        return tuple(result)

    #存入数据
    def saveData(self, capitalData):
        if len(capitalData) != 0:
            data = self.formatApiData(capitalData)
            sql = """insert into ad_capital(app_key,days,capital,plancapital,create_time)values(%s,%s,%s,%s,%s)on duplicate key update capital=%s,plancapital=%s"""
            return self.dbMonitor.executeMany(sql, data)
        else:
            self.info('No DATA!')
            return False


    # 获取监控脚本状态
    def checkCondition(self):
        sql = "select * from %s where id = '%s'"% (self.MonitorTaskTable,self.taskId)
        condition = self.dbMonitor.fetchone(sql)
        #这个是跑数据的实际日期
        self.startPosition = self.exchangeTimeStamp(str(condition['next_time']))  #nextposition 为跑前一天的数据
        self.startDatePosition = self.exchangeTimeStampDate(self.startPosition, '%Y-%m-%d')
        self.startDateTimePosition = self.exchangeTimeStampDate(self.startPosition, '%Y-%m-%d %H:%M:%S')  # 用这个来取数据

        #这个是能跑出来数据的实际日期，一般来说跑的数据是跑的时候的前一天的数据
        self.getPosition = int(self.startPosition) - 86400 #这是获取数据的真实日期
        self.getDatePosition = self.exchangeTimeStampDate(self.getPosition, '%Y-%m-%d')  # 用这个来取数据
        self.getDateTimePosition = self.exchangeTimeStampDate(self.getPosition, '%Y-%m-%d %H:%M:%S')  # 用这个来取数据
        self.period = condition['period']
        self.info('start position data for Dyas is:' + str(self.startDateTimePosition))
        self.info('get position data for Dyas is:' + str(self.getDateTimePosition))
        self.info('use Time start:' + time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time())))
        if condition['status'] != 1 and condition['status'] !=3:
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
            self.impressionsMap = self.getImpressions()
            self.dauMap = self.getDauList()
            calResult = self.calCapite()
            if self.saveData(calResult) is not False:
                self.endTask(self.taskId)# 更新位置
            else:
                self.info('Save data error! or No DATA!')
                return False
        except Exception, e:
            self.info("run error:" + str(e))


if __name__ == '__main__':
    startTimeStamp = time.time()
    taskId = CheckTask(sys.argv)
    #taskId = 3
    while 1:
        obj = monitorcapite('monitor_capite')
        if obj.run(taskId) == False:
            break
        time.sleep(1)
        # 脚步执行时间超过一个小时直接跳出
        if int(time.time() - startTimeStamp) > 3600:
            break





