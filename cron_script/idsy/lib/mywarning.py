#!/usr/bin/env python
# -*- coding:utf-8 -*-

import commands
import hashlib
import smtplib
from email.mime.text import MIMEText

import MySQLdb

import mylog as mylog
import mysql as db


class mywarning(object):
    smsApi = None
    smtp = None

    def __init__(self, configDb, configWarning):
        self.log = mylog.mylog('warning.log')
        self.dbWarningConfig = "adx_warning_config"
        self.dbWarning = "adx_warning"
        self.dbData = db.mysql(configDb)
        self.smsApi = configWarning.SMS_API
        self.smtp = configWarning.MAIL_SMTP

    def record(self, script, msg, create_time):
        key = hashlib.md5(msg).hexdigest()
        msg = MySQLdb.escape_string(msg)
        sql = """insert into %s (`script_name`,`mkey`,`content`,`create_time`) values ('%s','%s','%s','%s');""" % (
            self.dbWarning, script, key, msg, create_time)
        self.dbData.execute(sql)
        self.checkWarning(script, key, msg, create_time)

    def updateRecord(self, script, key, create_time):
        sql = "UPDATE %s SET `status`='0' WHERE `script_name`='%s' and mkey='%s' and create_time>=%s;" % (self.dbWarning, script, key, create_time)
        self.dbData.execute(sql)

    def getConf(self, script):
        sql = "select * from %s where script_name='%s' and status=1;" % (self.dbWarningConfig, script)
        return self.dbData.fetchone(sql)

    def checkWarning(self, script, key, msg, create_time):
        myConf = self.getConf(script)
        if myConf is not None:
            myTime = create_time - int(myConf['period'])
            sql = "select create_time from %s where script_name='%s' and mkey='%s' and status=0 order by id desc;" % (self.dbWarning, script, key)
            result = self.dbData.fetchone(sql)
            if result is not None and int(result['create_time']) > myTime:
                self.log.info('checkWarning:has warning not long ago')
                return False

            sql = "select count(*) as count from %s where script_name='%s' and mkey='%s' and create_time>='%s' and status=1;" % (
                self.dbWarning, script, key, myTime)
            result = self.dbData.fetchone(sql)
            if result is not None and result['count'] >= myConf['times']:
                if self.sendWarnging(myConf['type'], myConf['params'], script, msg):
                    self.updateRecord(script, key, myTime)
            else:
                self.log.info('checkWarning:' + str(result['count']))
                return False

    def sendWarnging(self, type, params, script, msg):
        if type == 'sms':
            return self.sendSms(str(params), script + ":" + msg)
        elif type == 'email':
            return self.sendEmail(str(params), script, msg)
        return False

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
            self.log.info('sendSms: ' + tmpmsg)
            return True
        except Exception, e:
            self.log.info('Failed! sendSms: ' + str(e) + '\n')
            return False

    # 发送 html 邮件提醒
    def sendEmail(self, receiverList, subject, content):
        # 收件人
        if len(receiverList) <= 0:
            self.log.info("sendEmail do not have a receive email!")
        msgcontent = str(content)
        self.log.info("Receiver: " + str(receiverList) + ", Content: " + msgcontent)
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
            self.log.info('sendEmail:success!\n')
            return True
        except Exception, e:
            self.log.info('sendEmail failed! ' + str(e) + '\n')
            return False
        finally:
            smtp.close()
