#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import json
from monitor_base import MonitorBase
from lib.task import CheckTask
import config.db as configDb


# 监控预警存入预警短信发送表
class monitorsavequeue(MonitorBase):
    # 初始化条件
    def runInit(self):
        self.dbNewData = self.initDb(configDb.MYSQL_MOBGI_DATA)  # 初始化data库
        self.dbMonitor = self.initDb(configDb.MYSQL_MOBGI_MONITOR)  # 初始化Monitor库
        self.ThirdDataTable = 'report_third_data'  # 获取第三方数据的表
        self.MonitorTable = 'ad_monitor'  # monitor表
        self.CapitalTable = 'ad_ecpm'  # ecpm表,记录每天的ecpm值
        self.MonitorTaskTable = 'ad_task'  # task表
        self.MonitorUserGroupTable = 'ad_user_group'
        self.MontorUserTable = 'ad_user'
        self.AppTable = 'config_app'


    #获取所有未处理的报警任务
    def getTaskloglist(self):
        sql = "select * from %s where `is_deal` = 0" % (self.taskLogTable)
        return self.dbMonitor.fetchall(sql)

    #根据未处理任务获得报警配置
    def getTaskConfig(self,task_id):
        sql = "select * from %s where `id` = %s "%(self.taskTable,task_id)
        taskConfig = self.dbMonitor.fetchone(sql)
        temp = json.loads(taskConfig['params'])
        taskConfig['report_time'] = temp['report_time']
        return taskConfig

    #根据APPKEY获取appname
    def getAppName(self,appKey):
        sql = "select app_name from %s where `app_key` = '%s'"%(self.AppTable,appKey)
        return self.dbNewData.fetchone(sql)

    #获取报警等级
    def getWarming(self,level):
        if level == 0:
            return '重度报警'
        elif level == 1:
            return '中度报警'
        elif level == 2:
            return '轻度报警'
        else:
            return '未报警'

    #组合发送的数据
    def formatData(self,taskConfig,taskLog):
        #判断上次报警时间，如果报警未到周期时间
        if taskLog['last_warming_datetime'] is not None and time.time()< (self.exchangeTimeStamp(str(taskLog['last_warming_datetime']))+taskConfig['warming_period']):
            self.info('not to report time save queue fail!')
            return False
        temp = json.loads(taskLog['info'])
        app = self.getAppName(temp['app_key'])
        warming_level = self.getWarming(taskLog['warming_level'])
        list = {
            'monitor_type':temp['monitor_type'],
            'app_name':app['app_name'],
            'app_key':temp['app_key'],
            'plan_data':temp['plan_data'],
            'now_data':temp['now_data'],
            'diff':float('%.2f'%(float(temp['now_data'])-float(temp['plan_data']))),
            'days':taskLog['days'],
            'warming_level':warming_level,
        }
        return list

    #获取用户组的用户
    def getUserFromGroupId(self,gid):
        sql = "select * from %s where gid = %s"%(self.MonitorUserGroupTable,gid)
        result,count = self.dbMonitor.fetchall(sql)
        userlist = {}
        if count != 0:
            for item in result:
                sql = "select * from %s where id = %s"%(self.MontorUserTable,item['uid'])
                result = self.dbMonitor.fetchone(sql)
                userlist[item['uid']] = result
        else:
            self.info('NO WARMING OBEJCT!')
            return False
        return userlist


    #整理数据并压入发送库
    def getUserAndSend(self,infoList):
        for item in infoList:
            userList = self.getUserFromGroupId(item)
            for user in userList:
                if self.taskConfig['warning_type'] == 3:#混合发送需要两遍
                    msgStr_mobile = self.getTemplate(userList[user],infoList[item],1)
                    if self.sendForType(userList[user],msgStr_mobile,1) is None:#压入发送表
                        return False
                    msgStr_email = self.getTemplate(userList[user],infoList[item],2)
                    if self.sendForType(userList[user], msgStr_email,2) is None:  # 压入发送表
                        return False
                else:#单个发送
                    msgStr = self.getTemplate(userList[user], infoList[item],self.taskConfig['warning_type'])
                    if self.sendForType(userList[user],msgStr,self.taskConfig['warning_type']) is None:#压入发送表
                        return False
        return True



    def sendForType(self,user,content,type):
        if int(type) == 1:
            message = {
                'title': '数据预警提醒',
                'content': content,
            }  # 发送信息
            config = {
                'send_type': self.sendTypeMap(type),
                'send_time': str(self.taskConfig['report_time'])[:1],
            }  # 发送信息配置
            userInfo = {
                'account': user['tel'],
            }  # 发送对象
        else:
            message = {
                'title': '数据预警提醒',
                'content': content,
            }  # 发送信息
            config = {
                'send_type': self.sendTypeMap(type),
                'send_time': str(self.taskConfig['report_time'])[:1],
            }  # 发送信息配置
            userInfo = {
                'account': user['email'],
            } # 发送对象
        return self.saveSendmsgquene(message,userInfo,config)


    def sendTypeMap(self,type):
        if int(type) == 1:
            typeReal = 'message'
        elif int(type) == 2:
            typeReal = 'email'
        return typeReal

    #更新上次报警时间
    def updateReporttime(self,id):
        sql = "update %s set last_warming_datetime = '%s' where id = '%s'"%(self.taskLogTable,str(time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))),id)
        return self.dbMonitor.execute(sql)

    #获得模板并替换
    def getTemplate(self,user,info,type):
        msgStr = None
        ecpmCount = capitalCount = actCount = 0
        for item in info.values():
            if item['monitor_type'] == 'ecpm':
               ecpmCount+=1
            elif item['monitor_type'] == 'capital':
               capitalCount+=1
            elif item['monitor_type'] == 'act':
               actCount+=1
        if int(type) == 2:
            f = open("../templates/emailTemplate.html",'r')
            lines = f.readlines()  # 读取全部内容
            for line in lines:
                tableStr = self.getTableStr(info)
                if msgStr is None:
                    msgStr = line.replace('{$last_time}',str(time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time())))).replace('{$taskLog_count}', str(len(info))).replace('{$nick_name}', user['user_name']).replace('{$content}', tableStr)
                else:
                    msgStr += line.replace('{$last_time}', str(time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time())))).replace('{$taskLog_count}',str(len(info))).replace('{$nick_name}', user['user_name']).replace('{$content}', tableStr)
        elif int(type) == 1:
            f = open("../templates/mobileTemplate.txt", 'r')
            lines = f.readlines()  # 读取全部内容
            for line in lines:
                if msgStr is None:
                    msgStr = line.replace('{$last_time}',str(time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time())))).replace('{$taskLog_count}', str(len(info))).replace('{$nick_name}', user['user_name']).replace('{$ecpm_count}',str(ecpmCount)).replace('{$active_count}',str(actCount)).replace('{$capital_count}',str(capitalCount))
                else:
                    msgStr += line.replace('{$last_time}',str(time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time())))).replace('{$taskLog_count}', str(len(info))).replace('{$nick_name}', user['user_name']).replace('{$ecpm_count}',str(ecpmCount)).replace('{$active_count}',str(actCount)).replace('{$capital_count}',str(capitalCount))
        return msgStr


    #构造table模板
    def getTableStr(self,info):
        strs = None
        for item in info.values():
            if strs is None:
                strs = '<tr><td style="width:14.28%;border:1px solid #ccc;padding:5px;">' + str(item['monitor_type']) + '</td>'
            else:
                strs += '<tr><td style="width:14.28%;border:1px solid #ccc;padding:5px;">' + str(item['monitor_type']) + '</td>'
            strs += '<td style="width:14.28%;border:1px solid #ccc;padding:5px;">' + str(item['app_name']) + '</td>'
            strs += '<td style="width:14.28%;border:1px solid #ccc;padding:5px;">' + str(item['days']) + '</td>'
            strs += '<td style="width:14.28%;border:1px solid #ccc;padding:5px;">' + str(item['warming_level']) + '</td>'
            strs += '<td style="width:14.28%;border:1px solid #ccc;padding:5px;">' + str(item['now_data']) + '</td>'
            strs += '<td style="width:14.28%;border:1px solid #ccc;padding:5px;">' + str(item['plan_data']) + '</td>'
            strs += '<td style="width:14.28%;border:1px solid #ccc;padding:5px;">' + str(item['diff']) + '</td></tr>'
        return strs

    def checkCondition(self):
        sql = "select * from %s where id = '%s'" % (self.MonitorTaskTable, self.taskId)
        condition = self.dbMonitor.fetchone(sql)
        self.startPosition = self.exchangeTimeStamp(str(condition['next_time']))
        self.startDatePosition = self.exchangeTimeStampDate(self.startPosition, '%Y-%m-%d')  # 用这个来取数据
        self.startDateTimePosition = self.exchangeTimeStampDate(self.startPosition, '%Y-%m-%d %H:%M:%S')  # 用这个来取数据
        self.period = condition['period']
        self.info('start position is:' + str(self.startDateTimePosition))
        self.info('use Time start:' + time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time())))
        if condition['status'] != 1 and condition['status'] != 3:
            self.info('The script is not open!')
            return False
        if time.time() < self.startPosition:
            self.info('The script is not to StartTime!')
            return False
        return True

    def run(self,taskId):
        try:
            self.runInit()
            self.taskId = taskId
            if self.checkCondition() is not True:
                return False
            taskLogList,count = self.getTaskloglist()
            infoList = {}
            for item in taskLogList:
                self.taskConfig = self.getTaskConfig(item['task_id'])
                formatInfo = self.formatData(self.taskConfig, item)
                if formatInfo is False:
                    continue
                if self.taskConfig['warning_target'] in infoList:
                    infoList[self.taskConfig['warning_target']].update({str(formatInfo['app_key'])+str(formatInfo['days'])+str(formatInfo['monitor_type']):self.formatData(self.taskConfig, item)})
                else:
                    infoList.update({self.taskConfig['warning_target']:{str(formatInfo['app_key'])+str(formatInfo['days'])+str(formatInfo['monitor_type']):self.formatData(self.taskConfig, item)}})
                # 更新上一次报警时间
                self.updateReporttime(item['id'])
            if self.getUserAndSend(infoList) is not False:
                self.endTask(self.taskId)  # 更新位置
            else:
                self.info('Save data error! or No DATA!')
                return False
        except Exception, e:
            self.info("run error:" + str(e))


if __name__ == '__main__':
    startTimeStamp = time.time()
    #taskId = 4
    taskId = CheckTask(sys.argv)
    obj = monitorsavequeue('monitor_savequeue')
    obj.run(taskId)
