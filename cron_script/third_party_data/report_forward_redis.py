#!/usr/bin/env python
# -*- coding:utf-8 -*-
import time
import third_party_setting
import logging
import os
import sys
import urllib2
import hashlib
import xml.dom.minidom
import redis
import MySQLdb
import codecs

reload(sys)
sys.setdefaultencoding('utf-8')

class REPORTFORWARD(object):
    """回调广告商通知激活"""
    def __init__(self,i):
        print "start report key:",i
        self.key = i
        fileName = "report_forward_redis.log.txt"
        self.initLog(fileName)

    def initLog(self, fileName):
         logging.basicConfig(filename=os.path.join(os.getcwd(), third_party_setting.LOGPATH + fileName),
                             format='%(asctime)s %(levelname)s %(message)s',
                             datefmt='%Y-%m-%d %H:%M:%S', level=logging.INFO)

    def writeLog(self, msg):
         print time.strftime("%Y-%m-%d %H:%M:%S",time.localtime())+ " " + str(msg)
         logging.info(msg)

    def run(self):
        num=third_party_setting.NUM
        # self.writeLog("start ClickForward:")
        poolr=redis.ConnectionPool(host=third_party_setting.REDIS["host"],port=third_party_setting.REDIS["port"])
        self.r=redis.Redis(connection_pool=poolr)
        redis_list_name = "housead_RQ:housead_report_forward"
        for j in range(third_party_setting.REDIS_POP_NUM):
            #从redis中取出一条待回调的id
            try:
                itemlen=self.r.llen(redis_list_name)
                if itemlen<num:#不足设定条数则暂停SLEEPSECOND秒
                    time.sleep(third_party_setting.SLEEPSECOND)
                    continue
                reportforward_data = self.r.lpop(redis_list_name)
                reportforward_data=eval(str(reportforward_data))
                self.writeLog(str(reportforward_data))
                for data_val in reportforward_data['report_url']:
                    request_status = 0
                    for k in xrange(3):# 给三次请求机会
                        if self.http(data_val) is True:
                            request_status = 1
                            break
                # 保存记录 成功上报或者失败上报
                reportforward_data['request_status'] = request_status
                reportforward_data['request_time'] = int(time.time())
                print reportforward_data
                reportforward_data['report_url'] = ''
                self.pushRedis(str(reportforward_data))
            except Exception, e:
                self.writeLog("thread_key:" + str(self.key) + " step2 error:" + str(e)+"\n" +"reportforward_data:"+str(reportforward_data)+"\n")
                return
        # self.writeLog("end ClickForward!")

    def pushRedis(self,result):
        redis_list_name = "housead_RQ:housead_report_record"
        self.r.rpush(redis_list_name,result)

    def http(self,result):
        report_url = str(result)
        if report_url=="":
            return True
        # 替换空格为 %20，不替换会出现400 BAD_REQUEST 问题
        report_url = report_url.replace(" ","%20")
        try:
            s_time = time.time()
            #使用urllib2,可以兼容tapjoy返回的是xml格式的数据
            response = urllib2.urlopen(report_url, timeout=3)
            status_code = response.getcode()
            headers=response.headers
            content=response.read()
            e_time = time.time()
            use_time = e_time - s_time
            if status_code!=200:#如果服务挂了
                self.writeLog('step4 e_time='+str(e_time)+' use_time='+str(use_time) + " thread_key:" +str(self.key) +" report_url="+report_url +"\n" + "response error!  response info--> status="+str(status_code)+" headers="+str(headers)+" content="+content+"\n")
                return True
            else:
                self.writeLog('step5 e_time='+str(e_time)+' use_time='+str(use_time) + " thread_key:" +str(self.key) +" report_url="+report_url +"\n" + "response success!  response info--> status="+str(status_code)+" headers="+str(headers)+" content="+content+"\n")
                return True
        except Exception, e:
            e_time = time.time()
            use_time = e_time - s_time
            self.writeLog('step6 e_time='+str(e_time)+' use_time='+str(use_time) + " thread_key:" +str(self.key) +" report_url="+report_url +"\n" + "response exception! exception info: "+ str(e)+"\n")
            return False

