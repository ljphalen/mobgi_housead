#!/usr/bin/env python
# -*- coding:utf-8 -*-

import os
import sys
import time
import commands
import datetime
import traceback
import smtplib
import json

sys.path.append("..")
import config.db as dbConfig
import config.warning as configWarning
from mybase import Base
from email.mime.text import MIMEText

reload(sys)

TASK_STATUS_WATTING = 1  # 等待
TASK_STATUS_RUNNING = 2  # 运行
#TASK_STATUS_FINISH = 3  # 完成
TASK_STATUS_STOP = 4  # 停止
TASK_STATUS_ERROR = 0  # 错误


def CheckTask(argv):
    if len(argv) != 2:
        print "taskId=0\n"
        exit()

    taskId = int(argv[1])
    if int(argv[1]) <= 0:
        print "Please use valid taskId as parameter.\n"
        exit()
    wc = commands.getoutput('ps aux|grep "%s %s$"|wc -l' % (argv[0], taskId))
    # if int(wc) > 2:
    #     print "Task is running.\n"
    #     exit()
    return taskId


class Task(Base):
    currentDir = sys.path[0]
    taskTable = "monitor_task" #任务管理表
    taskLogTable = 'monitor_task_log'  # 监控结论表
    scriptTable = "monitor_script" #脚本管理表
    targetUserGroup = 'monitor_group'  # 监控用户组表,用来发送邮件或者短信
    userAndGroupTable= 'monitor_user_group'
    userTable = 'monitor_user'
    appTable = 'config_app'
    posKeyTable = 'config_pos'
    channelTable = 'config_channels'
    logPath = 'log'
    sendQueue = 'monitor_send_queue' #报警信息表
    smsApi = None
    smtp = None

    def init(self):
        configDb = dbConfig.MYSQL_MOBGI_MONITOR
        configDbData = dbConfig.MYSQL_MOBGI_DATA
        self.dbData = self.initDb(configDbData)
        self.dbTask = self.initDb(configDb)
        self.smsApi = configWarning.SMS_API
        self.smtp = configWarning.MAIL_SMTP

    # 获取任务状态
        def getStatus(self, task_id):
            task = self.getTask(task_id)
            now = datetime.datetime.now()
            if task is not None and task['status'] == TASK_STATUS_WATTING and task['next_time'] < now:
                return True
            else:
                return False

    #获取任务
    def getTask(self, task_id):
        sql = "select * from %s where `id`='%s';" % (self.taskTable, task_id)
        return self.dbTask.fetchone(sql)

    #获取报警记录
    def getTasklogs(self,task_id):
        sql = "select * from %s where `task_id` = %s and `is_deal` = 0"%(self.taskLogTable,task_id)
        return self.dbTask.fetchall(sql)


    # 脚本信息获取
    def getScriptInfo(self, scriptId):
        sql = "select * from %s where status = 1 and id=%s" % (self.scriptTable, scriptId)
        return self.dbTask.fetchone(sql)

    #更新任务状态
    def updateTask(self, task_id, status):
        sql = "UPDATE %s SET `status`='%s' WHERE `id`='%s';" % (self.taskTable, status, task_id)
        return self.dbTask.execute(sql)


    #存入tasklog
    def saveTaskLog(self,data):
        if data.has_key('hours') is False:
            data['hours'] = 0
        sql = """insert into %s(task_id,log_id,days,hours,is_deal,warming_level,last_warming_datetime,monitor_type)values(%s,%s,'%s',%s,%s,%s,'%s','%s') """ \
              % (self.taskLogTable, data['task_id'], data['log_id'], data['days'], data['hours'],
                 0, 1,data['create_time'],data['monitor_type'])
        return self.dbTask.execute(sql)

    # 错误机制通用方法
    def errorTask(self, info, taskId):
        self.updateTask(taskId, TASK_STATUS_ERROR)  # 改变错误状态
        #self.saveSendmsgquene(taskId,info)#根据配置发送短信OR邮件
        self.error(info, "taskId:" + str(taskId))

    #压入预警记录表内
    def saveSendmsgquene(self,contentInfo,userInfo,config):
        create_time =time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        sql = """insert into %s(account,send_type,create_time,title,content,is_send,send_time)values('%s','%s','%s','%s','%s','%s',%s)"""\
        %(self.sendQueue,userInfo['account'],config['send_type'],create_time,contentInfo['title'],contentInfo['content'],0,config['send_time'])
        return self.dbTask.execute(sql)

    #判断发送类型并组织好发送信息,1短信 2邮件
    def checkSendMsgType(self,info,config,users):
        if int(config['send_type']) == 1:
            message = {
                'title': info['title'],
                'content':info['content'],
            }  # 发送信息
            config = {
                'send_type': config['send_type'],
                'send_time': config['send_time'],
            }  # 发送信息配置
            userInfo = {
                'account': users['tel'],
            }  # 发送对象
        else:
            message = {
                'title':info['title'],
                'content': info['content'],
            }  # 发送信息
            config = {
                'send_type': config['send_type'],
                'send_time': config['send_time'],
            }  # 发送信息配置
            userInfo = {
                'account': users['email'],
            }  # 发送对象
        return self.saveSendmsgquene(message,userInfo,config)


    #根据用户组获取用户所有信息
    def getAllUserInfo(self, groupId):
        userId = []
        sql = "select uid from %s where gid=%s" % (self.userAndGroupTable, groupId)
        uids,count1 = self.dbTask.fetchall(sql)
        for uid in uids:
            #print uid
            userId.append(str(uid['uid']))
        sql2 = "select * from %s where id in (%s)" % (self.userTable, ','.join(userId))
        userInfos,count2 = self.dbTask.fetchall(sql2)
        return userInfos

    #任务结束
    def endTask(self, task_id):
        status = TASK_STATUS_WATTING
        taskDetail = self.getTask(task_id)
        lastDateTime = taskDetail['next_time']
        nextTime = time.mktime(time.strptime(str(taskDetail['next_time']), "%Y-%m-%d %H:%M:%S")) + int(taskDetail['period'])
        nextDateTime = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(nextTime))
        self.info('NEXT DATE IS:' + str(nextDateTime))
        sql = "UPDATE %s SET `status`='%s',`last_time`='%s',`next_time`='%s' WHERE `id`='%s';" % (
            self.taskTable, status, lastDateTime, nextDateTime, task_id)
        self.dbTask.execute(sql)

    #获取任务列表
    def getTaskList(self, pid=0):
        now = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        sql = "SELECT * FROM %s WHERE pid = %s and status=%s and next_time<='%s' order by pid,next_time" % (
            self.taskTable, pid, TASK_STATUS_WATTING, now)
        return self.dbTask.fetchall(sql)

    #开始任务
    def startTask(self, task):
        #检查用到的脚本信息
        scriptInfo = self.getScriptInfo(task['script_id'])
        if scriptInfo is not None:
            if int(task['type']) == 1 or int(task['type']) == 2:#监控任务都是python
                cmd = "/usr/bin/python " + os.path.join(self.currentDir, scriptInfo['script']) + " " + str(task['id'])
            else:
                cmd = scriptInfo['script'] + " " + str(task['id'])
        else:
            self.error('The script is None or not Open,script id='+task['script_id'])
        self.info(cmd)
        os.system(cmd)

    # 发送手机短信
    def sendSms(self, mobile, content):
        # mobile = '13670182751,18664918062,15625046334'
        #  发送短信
        try:
            smsapi = self.smsApi
            curl_data = 'mobile=' + mobile + '&content=' + content + '&code=ids_monitor_zabbix'
            command = 'curl -d "' + curl_data + '" ' + smsapi
            # 发短信并写日志
            # curl -d "mobile=13670182751&content=content test xxx 2&code=ids_monitor_zabbix" http://sagent2.uu.cc/SMS/sa/sms/send
            # {"ret":"0","error_code":"0","data":"-5816693585345305780"}
            output = commands.getoutput(command)
            tmpmsg = 'command=' + command + "output=" + output + "content=" + content
            self.info('sendSms: ' + tmpmsg)
            return True
        except Exception, e:
            self.info('Failed! sendSms: ' + str(e) + '\n')
            return False

    # 发送 html 邮件提醒
    def sendEmail(self, receiverList, subject, content):
        # 收件人
        if len(receiverList) <= 0:
            self.info("sendEmail do not have a receive email!")
        msgcontent = str(content)
        self.info("Receiver: " + str(receiverList) + ", Content: " + msgcontent)
        # # 这部分需不需要每次都连接登录下，会不会因为等待时间而断掉
        toAddr = receiverList
        msg = MIMEText(msgcontent, 'html', 'utf-8')  # 发送html邮件
        msg['Subject'] = str(subject)
        msg['From'] = self.smtp['sender']
        msg['To'] = toAddr
        #  发送邮件
        try:
            smtp = smtplib.SMTP()
            smtp.connect(self.smtp['host'], self.smtp['port'])  # 连接smtp服务器
            smtp.login(self.smtp['user'], self.smtp['pass'])  # 登陆服务器
            smtp.sendmail(self.smtp['sender'], toAddr, msg.as_string())  # 发送邮件
            self.info('sendEmail:success!\n')
            return True
        except Exception, e:
            self.info('sendEmail failed! ' + str(e) + '\n')
            return False
        finally:
            smtp.close()


    #获取channelmap
    def getChannelMap(self,channelGid):
        sql = "select channel_name from %s where channel_id = '%s'" % (self.channelTable,channelGid)
        list = self.dbData.fetchone(sql)
        return str(list['channel_name'])

    #获取广告位map
    def getPoskeyMap(self,posKey):
        sql = "select pos_name from %s where pos_key = '%s'" % (self.posKeyTable, posKey)
        list = self.dbData.fetchone(sql)
        return str(list['pos_name'])

    #获取appMap
    def getAppMap(self,appKey):
        sql = "select app_key,app_name from %s where app_key = '%s'" % (self.appTable, appKey)
        list = self.dbData.fetchone(sql)
        return str(list['app_name'])


    #获取广告类型
    def getAdTypeMap(self,adType):
        if int(adType) == 1:
            name = '视频广告'
        elif int(adType) == 2:
            name = '插页广告'
        elif int(adType) == 3:
            name = '交叉推广广告'
        elif int(adType) == 4:
            name = '开屏广告'
        else:
            name = '原生信息流广告'
        return name

    def start(self, pid=0):
        try:
            self.init()
            tasks, count = self.getTaskList(pid)
            if count == 0:
                self.info("no task")
                return False
            for task in tasks:
                self.startTask(task)
        except Exception, e:
            self.errorTask(str(e), pid)



            # def setTaskLog(self, task_id, msg, create_time):
            #     key = hashlib.md5(msg).hexdigest()
            #     msg = MySQLdb.escape_string(msg)
            #     sql = """insert into %s (`script_name`,`mkey`,`content`,`create_time`) values ('%s','%s','%s','%s');""" % (
            #         self.dbWarning, script, key, msg, create_time)
            #     self.dbData.execute(sql)
            #     self.checkWarning(script, key, msg, create_time)
