#!/usr/bin/env python
# -*- coding:utf-8 -*-
import time
import third_party_setting
import logging
import os
import urllib2
import hashlib
import thread
import redis
import threading
import report_forward_redis

#logging.basicConfig(filename = os.path.join(os.getcwd(), settings.LOGPATH+'callback.log.txt'),level=logging.ERROR)
class Dispatcher(object):
    """回调广告商通知激活"""
    def __init__(self):
        print ""

    def run(self):
        sleepNum = 0
        while 1:
            poolr=redis.ConnectionPool(host=third_party_setting.REDIS["host"],port=third_party_setting.REDIS["port"])
            r=redis.Redis(connection_pool=poolr)
            redis_list_name = "housead_RQ:housead_report_forward"
            if r.llen(redis_list_name) < third_party_setting.NUM and sleepNum < 3:#不足设定条数则暂停SLEEPSECOND秒
                print "sleeping..."
                time.sleep(third_party_setting.SLEEPSECOND)
                sleepNum += 1
            else:
                sleepNum = 0
                tasks = []
                for i in range(third_party_setting.REPORT_FORWARD_NUM):
                    t = threading.Thread(target=self.DataForwardByI, args=(i,))
                    tasks.append(t)
                    t.start()
                for t in tasks:
                    t.join()

    def DataForwardByI(self,i):
        callback = report_forward_redis.REPORTFORWARD(i)
        callback.run()


if __name__ == '__main__':
    dispatcher=Dispatcher()
    dispatcher.run()


