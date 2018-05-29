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
    if int(wc) > 2:
        print "Task is running.\n"
        exit()
    return taskId


class Task(Base):
    currentDir = sys.path[0]
    taskTable = "ad_task"
    taskLogTable = 'ad_task_log'  # 监控结论表
    monitorTable = "ad_monitor"
    targetUserGroup = 'ad_group'  # 监控用户组表,用来发送邮件或者短信
    logPath = 'log'
    sendQueue = 'send_queue' #报警信息表
    smsApi = None
    smtp = None

    def init(self):
        configDb = dbConfig.MYSQL_MOBGI_MONITOR
        self.dbTask = self.initDb(configDb)
        self.smsApi = configWarning.SMS_API
        self.smtp = configWarning.MAIL_SMTP

    def getStatus(self, task_id):
        task = self.getTask(task_id)
        now = datetime.datetime.now()
        if task is not None and task['status'] == TASK_STATUS_WATTING and task['next_time'] < now:
            return True
        else:
            return False

    def getTask(self, task_id):
        sql = "select * from %s where `id`='%s';" % (self.taskTable, task_id)
        return self.dbTask.fetchone(sql)

    def getTasklogs(self,task_id):
        sql = "select * from %s where `task_id` = %s and `is_deal` = 0"%(self.taskLogTable,task_id)
        return self.dbTask.fetchall(sql)

    def updateTask(self, task_id, status):
        sql = "UPDATE %s SET `status`='%s' WHERE `id`='%s';" % (self.taskTable, status, task_id)
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

    def getTaskList(self, pid=0):
        now = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))
        sql = "SELECT * FROM %s WHERE pid = %s and status=%s and next_time<='%s' order by pid,next_time" % (
            self.taskTable, pid, TASK_STATUS_WATTING, now)
        return self.dbTask.fetchall(sql)

    def startTask(self, task):
        if int(task['type']) == 1 or int(task['type']) == 2:#监控任务都是python
            cmd = "/usr/bin/python " + os.path.join(self.currentDir, task['script']) + " " + str(task['id'])
        else:
            cmd = task['script'] + " " + str(task['id'])
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

    def start(self, pid=0):
        try:
            self.init()
            tasks, count = self.getTaskList(pid)
            if count == 0:
                self.info("no task")
                return False
            for task in tasks:
                #self.sendMsg(task['id'],'1111')
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
