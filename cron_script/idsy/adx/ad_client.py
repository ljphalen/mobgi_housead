#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import datetime
import threading
import json
from ad_qbase import AdQbase
from lib.snowflask import Snowflask

import config.worker as worker
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis

RQ_LIMIT = configAdx.RQ_ADX_CLIENT_LIMIT


class AdClient(AdQbase):
    whitelist = {}
    key_pre = 'adx_abTest_'

    myqueue = {
        "ad_client": configRedis.REDIS_QUEUE_ADX,
        "ad_client2": configRedis.REDIS_QUEUE_MOBGI2,
        "ad_client3": configRedis.REDIS_QUEUE_MOBGI3,
        "ad_client4": configRedis.REDIS_QUEUE_MOBGI4,
        "ad_client5": configRedis.REDIS_QUEUE_MOBGI5,
    }

    def init(self):
        if self.myqueue.has_key(self.scriptName):
            self.rq = self.initRedis(self.myqueue[self.scriptName])
        else:
            self.error('no queue')
        self.r = self.initRedis(configRedis.REDIS_MOBGI)
        self.rkey = configAdx.RQ_ADX_CLIENT

    def runInit(self):
        self.statConfig = configDb.MYSQL_BH_AD_STAT
        self.whiteConfig = configDb.MYSQL_BH_WHITELIST
        self.statTable = configAdx.TABLE_STAT_CLIENT
        self.statWhitelistTable = configAdx.TABLE_STAT_CLIENT_WHITELIST
        self.statFields = configAdx.FIELDS[self.statTable]
        self.defaultVal = configAdx.DEFAULT_VAL

    def run(self, sf):
        try:
            self.getWhitelist()
            self.runInit()
            self.idCreater = sf
            start_time = time.time()
            self.info("rlen:" + str(self.rlen) + "\tlimit:" + str(self.rqLimit))
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
            # self.info(str(threadnum) + '_thread_redis_time=' + str(time.time() - start_time))
            sqlcontent = ''
            whitelistsqlcontent = ''
            for stream in listStream:
                stream = json.loads(stream)
                if isinstance(stream, dict) is False:
                    continue
                stream['id'] = self.idCreater.nextId(threadnum)

                # 新增ABTEST
                # if stream.has_key('imei') and len(stream['imei']) > 10:
                #     key = self.key_pre + str(stream['imei'])
                #     if self.r.hget(key, 'config_id') is not None:
                #         stream['config_id'] = self.r.hget(key, 'config_id')
                #         stream['user_type'] = 1

                items = []
                for field in self.statFields:
                    if field in stream:
                        items.append(str(stream[field]))
                    elif field in self.defaultVal:
                        items.append(self.defaultVal[field])
                    else:
                        self.info('field:' + field + ',default:' + str(self.defaultVal[field]))
                        items.append('-1')
                sqlcontent += '\t'.join(items) + '\n'
                if self.isWhitelistDevice(stream) == True:
                    whitelistsqlcontent += '\t'.join(items) + '\n'
            ready_time = time.time() - self.startTimestamp
            if sqlcontent != "":
                filename = self.scriptName + time.strftime("_%Y%m%d_%H%M%S", time.localtime()) + "_" + str(threadnum)
                file = self.getSqlPath(filename)
                self.saveDataToFile(file, sqlcontent)
                self.loadData(file)
                save_time = time.time() - self.startTimestamp
                # 白名单设备上报数据入库
                if whitelistsqlcontent != "":
                    self.info('whitelist')
                    whitelistfilename = 'whitelist_' + filename
                    whitelistfile = self.getSqlPath(whitelistfilename)
                    self.saveDataToFile(whitelistfile, whitelistsqlcontent)
                    self.loadDataSpecial(whitelistfile, self.whiteConfig, self.statWhitelistTable, self.statFields)

            self.info(str(threadnum) + '_thread_time=' + str(ready_time) + ',' + str(save_time) + ',' + str(time.time() - self.startTimestamp))
        except Exception, e:
            self.error("store Exception:" + str(e) + ",stream:" + str(stream))

    def initApi(self):
        self.dbApi = self.initDb(configDb.MYSQL_MOBGI_API)

    # 设置白名单list
    def getWhitelist(self):
        self.initApi()
        today = str(datetime.datetime.now().date())
        sql = """select content from ab_conf where status=1 and is_report=1 and start_time >='%s' and end_time<='%s' """ % (today, today)
        # SQL_NO_CACHE
        list, count = self.dbApi.fetchall(sql, False)
        self.dbApi.close()
        if len(list) != 0:
            for item in list:
                vals = json.loads(item[0])
                for val in vals:
                    self.whitelist[val] = 1

    # 判断是否需要额外走白名单
    def isWhitelistDevice(self, stream):
        if len(self.whitelist) == 0:
            return False

        imei = str(stream['imei'])
        if len(imei) == 0:
            return False
        if self.whitelist.has_key(imei):
            return True


if __name__ == '__main__':
    sleepCount = 0
    loadCount = 0
    creater = Snowflask(worker.WORK_ID)
    while 1:
        loadCount += 1
        obj = AdClient('ad_client')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        rlen = obj.getRlen()
        if (rlen < worker.WORK_LIMIT and sleepCount < 5) or rlen == 0:
            obj.info("zzz:" + str(rlen))
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            sleepCount = sleepCount + 1
            continue
        sleepCount = 0
        obj.run(creater)
        if loadCount > 5:
            loadCount = 0
            obj.loadFiles()
