#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import logging
import third_party_setting
import redis
import sys
import third_report_store

reload(sys)
sys.setdefaultencoding('utf-8')


class ThirdReportClient(object):
    def __init__(self):
        # 连接redis
        logging.basicConfig(filename = os.path.join(os.getcwd(), third_party_setting.LOGPATH+'redis_run.log.txt'),level=logging.ERROR)
        try:
            poolr=redis.ConnectionPool(host=third_party_setting.REDIS["host"],port=third_party_setting.REDIS["port"])
            self.r=redis.Redis(connection_pool=poolr)
        except Exception, e:
            print time.strftime("%Y-%m-%d %H:%M:%S",time.localtime()) + ':ERROR:Redis has gone away!'
            logging.error(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime())+":ERROR:Redis has gone away!")

    """docstring for Stat"""
    def run(self):
        while 1:
            num=third_party_setting.NUM
            itemlen=self.r.llen("housead_RQ:housead_third_report")
            print itemlen
            #防止字符串中出现null字符无法识别
            null='unknow'
            if itemlen<num:#不足设定条数则暂停SLEEPSECOND秒
                print "sleep "+str(third_party_setting.SLEEPSECOND)+"s"
                time.sleep(third_party_setting.SLEEPSECOND)
                continue
            streamlist=[]
            for i in range(third_party_setting.REQUEST_BATCHNUM):
                stream = self.r.lpop("housead_RQ:housead_third_report")
                print stream
                stream=eval(stream)
                if stream is None or isinstance(stream,dict) is False:
                    continue
                streamlist.append(stream)
                itemlen=self.r.llen("housead_RQ:housead_third_report")
                if itemlen==0:
                    break
            #print stream'
            third_report_store.ThirdReportStore().run(streamlist)

if __name__ == '__main__':
        third_report_client=ThirdReportClient()
        third_report_client.run()
