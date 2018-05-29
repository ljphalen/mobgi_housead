#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import threading
import json
from ad_qbase import AdQbase
from lib.snowflask import Snowflask

import config.worker as worker
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis

RQ_LIMIT = configAdx.RQ_ADX_CLIENT_LIMIT


class AdServer(AdQbase):
    def init(self):
        self.rq = self.initRedis(configRedis.REDIS_QUEUE_ADX)
        self.rkey = configAdx.RQ_ADX_SERVER
        self.whitelist = []

    def runInit(self):
        self.statConfig = configDb.MYSQL_BH_AD_STAT
        self.statTable = configAdx.TABLE_STAT_SERVER
        self.statFields = configAdx.FIELDS[self.statTable]
        self.defaultVal = configAdx.DEFAULT_VAL

    def run(self, sf):
        try:
            self.runInit()
            self.idCreater = sf
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

    def store(self, threadnum, rkey):
        try:
            stream = None
            listStream = []
            for i in range(self.rqLimit):
                stream = self.rq.lpop(rkey)
                if stream is None:
                    break
                listStream.append(stream)
            sqlcontent = ''
            for stream in listStream:
                stream = json.loads(stream)
                if isinstance(stream, dict) is False:
                    continue
                stream['id'] = self.idCreater.nextId(threadnum)
                items = []
                for field in self.statFields:
                    if field in stream:
                        items.append(str(stream[field]))
                    elif field in self.defaultVal:
                        items.append(self.defaultVal[field])
                    else:
                        self.info('field:' + field + ',default:' + str(self.defaultVal))
                        items.append('-1')
                sqlcontent += '\t'.join(items) + '\n'

            if sqlcontent != "":
                filename = self.scriptName + "_" + str(threadnum) + time.strftime("_%Y%m%d_%H%M%S", time.localtime())
                file = self.getSqlPath(filename)
                self.saveDataToFile(file, sqlcontent)
                self.loadData(file)

            self.info(str(threadnum) + '_thread_time=' + str(time.time() - self.startTimestamp))
        except Exception, e:
            self.error("store Exception:" + str(e) + ",stream:" + str(stream))

    # 判断是否需要额外走白名单
    def isWhitelistDevice(self, stream):
        if len(self.whitelist) == 0:
            return False
        # platform 1安桌 2IOS
        platform = str(stream['platform'])
        imei = str(stream['imei'])
        if platform == '1' or platform == '2':
            platform_deviceid = platform + "_" + imei
        else:
            platform_deviceid = 'none'
        if platform_deviceid in self.whitelist:
            return True


if __name__ == '__main__':
    sleepCount = 0
    loadCount = 0
    creater = Snowflask(worker.WORK_ID)
    limit = 10000
    while 1:
        obj = AdServer('ad_server')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        rlen = obj.getRlen()

        if (rlen < limit and sleepCount < 5) or rlen == 0:
            obj.info("zzz:" + str(rlen))
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            sleepCount = sleepCount + 1
            continue
        sleepCount = 0

        obj.run(creater)
        if loadCount > 3:
            loadCount = 0
            obj.loadFiles()
        else:
            loadCount += 1
