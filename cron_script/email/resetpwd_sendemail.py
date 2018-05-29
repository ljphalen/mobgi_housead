#!/usr/bin/python
# -*- coding:utf-8 -*-

import sys
import os
import time
import datetime
import logging
import redis
import email_settings
import math
import json
import smtplib
from email.mime.text import MIMEText

reload(sys)
sys.setdefaultencoding('utf-8')
# 从配置文件获得日志保存目录
LOGPATH = email_settings.LOGPATH

# （１）发送邮件验证邮箱
class Sendemail(object):

    def __init__(self):
        # 连接redis
        logging.basicConfig(filename = os.path.join(os.getcwd(), LOGPATH+'redis_run.log.txt'),level=logging.ERROR)
        try:
            poolr=redis.ConnectionPool(host=email_settings.REDIS["host"],port=email_settings.REDIS["port"])
            self.r=redis.Redis(connection_pool=poolr)
        except Exception, e:
            print time.strftime("%Y-%m-%d %H:%M:%S",time.localtime()) + ':ERROR:Redis has gone away!'
            logging.error(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime())+":ERROR:Redis has gone away!")

    def mylog(self,msg):
        print time.strftime("%Y-%m-%d %H:%M:%S",time.localtime())+ " " + msg
        self.logfileHandle.write(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime())+" " + msg + '\n')

    # （１）发送邮件验证邮箱
    def run(self):
        self.logfileHandle = open(LOGPATH + "resetpwd_sendemail_log.txt",'a')
        while 1:
            num=email_settings.NUM
            itemlen=self.r.llen("housead_RQ:housead_admin_email")
            #防止字符串中出现null字符无法识别
            null='unknow'
            if itemlen<num:#不足设定条数则暂停SLEEPSECOND秒
                # print "sleep "+str(email_settings.SLEEPSECOND)+"s"
                time.sleep(email_settings.SLEEPSECOND)
                continue
            self.mylog('email num:' + str(itemlen))
            streamlist=[]
            for i in range(email_settings.EMAIL_BATCHNUM):
                stream = self.r.lpop("housead_RQ:housead_admin_email")
                # stream = stream.decode(encoding='UTF-8',errors='strict')
                stream = stream.decode("unicode_escape")
                stream = stream.encode("utf-8")
                stream = stream.replace("\/","/")
                print str(stream)
                # print type(stream)
                stream=eval(str(stream))
                # perdist = json.dumps(perdists)
                if stream is None or isinstance(stream,dict) is False:
                    continue
                streamlist.append(stream)
                itemlen=self.r.llen("housead_RQ:housead_admin_email")
                if itemlen==0:
                    break
            self.sendemail(streamlist)
        self.logfileHandle.close()

    def sendemail(self,list):
        mail_conf = email_settings.MAIL_CONF
        self.mylog('Send email start:')
        # 登录邮箱
        try:
            smtp = smtplib.SMTP()
            smtp.connect(mail_conf['mail_host'], mail_conf['mail_port'])          # 连接smtp服务器
            smtp.login(mail_conf['mail_user'], mail_conf['mail_pass'])            # 登陆服务器
        except Exception, e:
            print "登录失败！"+str(e)
        for perdist in list:
            receiver = perdist['receiver'] # 收件人
            subject = perdist['subject'] # 主题
            mailbody = perdist['mailbody'] # 正文
                # # 这部分需不需要每次都连接登录下，会不会因为等待时间而断掉
            msg = MIMEText(mailbody,'html','utf-8')   #发送html邮件
            msg['Subject'] = subject
            msg['From'] = mail_conf['mail_sender']
            msg['To'] = receiver
            self.mylog('Receiver:' + str(receiver))
            try:
                smtp.sendmail(mail_conf['mail_sender'], receiver, msg.as_string())  # 发送邮件
                self.mylog('Email send success!')
            except Exception, e:
                 self.mylog('Email send failed! '+str(e))
        self.mylog('Send email end' + '\n')
        smtp.close()

if __name__ == '__main__':
    sendemail = Sendemail()
    sendemail.run()