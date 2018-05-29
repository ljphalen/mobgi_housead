#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import codecs
import logging
import MySQLdb
from utils.mysql import MySQL
import third_party_setting
import ConfigParser
import redis
import commands
import urllib2

reload(sys)
sys.setdefaultencoding('utf-8')

"""将批量的redis实例的数据落地成sql文件导入"""

class ThirdReportStore(object):
    def __init__(self):
        # 数据库参数
        self.requst = third_party_setting.MYSQL_REQUST
        try:
            poolr=redis.ConnectionPool(host=third_party_setting.REDIS["host"],port=third_party_setting.REDIS["port"])
            self.r=redis.Redis(connection_pool=poolr)
        except Exception, e:
            print time.strftime("%Y-%m-%d %H:%M:%S",time.localtime()) + ':ERROR:Redis has gone away!'
        self.click_event_type = third_party_setting.CLICK_EVENT_TYPE
        self.show_event_type = third_party_setting.SHOW_EVENT_TYPE

    # 不用logging日志是因为上个目录已经使用logging了，非继承关系被覆盖
    def mylog(self,msg):
        print time.strftime("%Y-%m-%d %H:%M:%S",time.localtime())+ " " + msg
        self.logfileHandle.write(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime())+" " + msg + '\n')

    # 重连db
    def trysql(self,sql):
        try:
            self.requstdb.queryNotCatch(sql)
        except MySQLdb.Error as m:
            self.mylog('masterdb reconnecting now')
            self.requstdb = MySQL(self.requst['host'], self.requst['user'], self.requst['passwd'], port=self.requst['port'], db=self.requst['db'])
            self.requstdb.query(sql)
        return self.requstdb.fetchAll()

    def getDataFromRedis(self, streamlist):
        try:
            for stream in streamlist:
                eventType = int(stream['event_type'])
                if eventType == self.click_event_type: # 点击
                    field = 'clickmonurl'
                elif eventType == self.show_event_type: # 展示
                    field = 'showmonurl'
                else: # 非点击和展示事件类型，直接退出
                    self.mylog('step1: not need this eventType:'+str(eventType))
                    continue
                redis_cache_name = "housead_third_request_"+str(stream['request_id'])+"_"+str(stream['originality_id'])
                result = self.r.get(redis_cache_name)
                if result != None:
                    result = str(result).replace("\/","/")
                    result=eval(result)
                    report_url = result[field]
                    stream['report_url'] = report_url
                    self.mylog('step2: redisdata='+str(stream))
                    data = str(stream)
                    self.pushRedis(data)
                else:
                    self.mylog('step3: cant not find redis cache')
        except Exception, e:
            self.mylog("step4 get data from redis failed! error:"+str(e))

    def pushRedis(self,result):
        redis_list_name = "housead_RQ:housead_report_forward"
        self.r.rpush(redis_list_name,result)

    def run(self, streamlist):
        fileName = "third_report_store.log.txt"
        logfilename = os.path.join(os.getcwd(), third_party_setting.LOGPATH + fileName)
        self.logfileHandle = open(logfilename,'a')
        self.getDataFromRedis(streamlist)
        self.logfileHandle.close()
