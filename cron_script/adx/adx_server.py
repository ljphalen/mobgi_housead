#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis
import threading
from adx_qbase import AdxQbase

RQ_LIMIT = configAdx.RQ_ADX_SERVER_LIMIT


class AdxServer(AdxQbase):
    def init(self):
        self.rq = self.initRedis(configRedis.REDIS_QUEUE_ADX)
        self.rkey = configAdx.RQ_ADX_SERVER_DATA
        self.dbConfStat = configDb.MYSQL_MOBGI_ADX_STAT
        self.table = configAdx.TABLE_SERVER_STAT
        self.fields = configAdx.FIELDS_SERVER_STAT
        self.positionTable = configAdx.TABLE_POSITION

    def run(self):
        try:
            start_time = time.time()
            self.info("rlen:" + str(self.rlen))
            tasks = []
            if self.rlen > 0:
                for i in xrange(0, self.threads):
                    t = threading.Thread(target=self.store, args=(i, self.rkey))
                    tasks.append(t)
                    t.start()
                for t in tasks:
                    t.join()
            self.info("loop_time:" + str(time.time() - start_time))
        except Exception, e:
            self.error("run error:" + str(e))


if __name__ == '__main__':
    sleepCount = 0
    loadCount = 0
    while 1:
        obj = AdxServer('adx_server')
        if obj.flag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        rlen = obj.getRlen()
        if rlen < RQ_LIMIT and sleepCount < 5 or rlen == 0:
            obj.info("zzz:"+str(rlen))
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            sleepCount = sleepCount + 1
            continue
        sleepCount = 0
        obj.run()
        if loadCount > 3:
            loadCount = 0
            obj.loadFiles()
        else:
            loadCount += 1
