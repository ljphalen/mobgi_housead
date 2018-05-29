#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import threading
import json
import traceback
from ad_qbase import AdQbase
from lib.snowflask import Snowflask

import config.worker as worker
import config.adx as configAdx
import config.housead as configHouseAd
import config.db as configDb
import config.redis as configRedis

import adx_set_frequency_cache

CHAREG_TYPE_CLICK = configHouseAd.CHAREG_TYPE_CLICK
CHAREG_TYPE_VIEW = configHouseAd.CHAREG_TYPE_VIEW
CHAREG_TYPE_ACTIVE = configHouseAd.CHAREG_TYPE_ACTIVE
RQ_LIMIT = configAdx.RQ_ADX_LIMIT

KS = "::"


class AdCharge(AdQbase):
    key_pre = configHouseAd.REDIS_CHARGE_PREFIX
    minute_table = "adx_charge_minute"

    def init(self):
        self.rq_limit = RQ_LIMIT
        self.r = self.initRedis(configRedis.REDIS_MOBGI)
        self.rq = self.initRedis(configRedis.REDIS_QUEUE_ADX)
        self.rkey = configAdx.RQ_ADX_CHARGE
        self.statConfig = configDb.MYSQL_BH_AD_STAT
        self.statTable = configAdx.TABLE_STAT_CHARGE
        self.statFields = configAdx.FIELDS[self.statTable]
        self.defaultVal = configAdx.DEFAULT_VAL

        self.chargeConfig = configDb.MYSQL_MOBGI_CHARGE

    def run(self, sf):
        try:
            # self.runInit()
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
            start_time = time.time()
            frequencyStreamList = []
            cdata = {}
            sqlcontent = ''
            for i in range(RQ_LIMIT):
                stream = self.rq.lpop(rkey)
                if stream is None:
                    break
                stream = json.loads(stream)
                if isinstance(stream, dict) is False:
                    break
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

                day = time.strftime("%Y%m%d", time.localtime(stream['server_time']))
                origKey = str(stream['ads_id']) + KS + day + KS + str(stream['orig_id'])
                if origKey not in cdata:
                    cdata[origKey] = {
                        'views': 0,
                        'clicks': 0,
                        'actives': 0,
                        'amount': 0,
                    }
                if stream['event_type'] == CHAREG_TYPE_VIEW:
                    frequencyStreamList.append(stream)
                    cdata[origKey]['views'] += 1
                elif stream['event_type'] == CHAREG_TYPE_CLICK:
                    cdata[origKey]['clicks'] += 1
                elif stream['event_type'] == CHAREG_TYPE_ACTIVE:
                    cdata[origKey]['actives'] += 1

                cdata[origKey]['amount'] += float(stream['price'])

            if sqlcontent != "":
                file = self.getSqlPath(self.scriptName + "_" + str(threadnum) + time.strftime("_%Y%m%d_%H%M%S", time.localtime()))
                self.saveDataToFile(file, sqlcontent)
                self.loadData(file)
                sqlcontent = ""
                self.updateRealData(cdata)
                # 进入设置曝光缓存的缓存的逻辑
                if len(frequencyStreamList) > 0:
                    adx_set_frequency_cache.FrequencyCache('frequency_cache').run(frequencyStreamList)
        except Exception, e:

            if sqlcontent != "":
                file = self.getSqlPath(self.scriptName + "_" + str(threadnum) + time.strftime("_%Y%m%d%H%M%S", time.localtime()))
                self.saveDataToFile(file, sqlcontent)
                self.loadData(file)
            traceback.print_exc()
            self.error("store Exception:" + str(e) + ",stream:" + str(stream))
        finally:
            self.info(str(threadnum) + '_thread_time=' + str(time.time() - start_time))

    # 更新实时数据
    def updateRealData(self, cdata):
        for origKey in cdata:
            adsId, day, origId = origKey.split(KS);
            dkey = self.key_pre + origKey
            dval = cdata[origKey]
            dexist = True
            if self.r.exists(dkey) is False:
                dexist = False

            self.r.hincrby(dkey, 'views', int(dval['views']))
            self.r.hincrby(dkey, 'clicks', int(dval['clicks']))
            self.r.hincrby(dkey, 'actives', int(dval['actives']))
            self.r.hincrbyfloat(dkey, 'amount', float(dval['amount']))

            if dexist is False:
                self.info('dkey is not exist:' + dkey)
                self.r.expire(dkey, 7200)
                today = self.exchangeStrDate(day, '%Y%m%d', '%Y-%m-%d')
                myval = self.getTodayData(adsId, origId, today)
                self.r.hincrby(dkey, 'views', int(myval['views']))
                self.r.hincrby(dkey, 'clicks', int(myval['clicks']))
                self.r.hincrby(dkey, 'actives', int(myval['actives']))
                self.r.hincrbyfloat(dkey, 'amount', float(myval['amount']))

    def getTodayData(self, adsId, origId, today):
        self.dbData = self.initDb(self.chargeConfig)
        sql = """select IFNULL(sum(views), 0) as views, IFNULL(sum(clicks), 0) as clicks, IFNULL(sum(actives), 0) as actives,IFNULL(sum(amount),
        0) as amount from %s where originality_id ='%s' and ads_id = '%s' and `days` =  "%s" """ % (self.minute_table, origId, adsId, today)
        result = self.dbData.fetchone(sql)
        return result


if __name__ == '__main__':
    sleepCount = 0
    loadCount = 0
    creater = Snowflask(worker.WORK_ID)
    limit = 10
    while 1:
        obj = AdCharge('ad_charge')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        rlen = obj.getRlen()

        if (rlen < limit and sleepCount < 3) or rlen == 0:
            obj.info("zzz:" + str(rlen))
            obj = None
            time.sleep(30)
            sleepCount = sleepCount + 1
            continue
        sleepCount = 0

        obj.run(creater)
        if loadCount > 3:
            loadCount = 0
            obj.loadFiles()
        else:
            loadCount += 1
