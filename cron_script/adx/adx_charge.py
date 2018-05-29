#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import config.adx as configAdx
import config.housead as configHouseAd
import config.db as configDb
import config.redis as configRedis
from adx_qbase import AdxQbase
from lib.mysql import mysql
import codecs
import threading
import json
import fnmatch
import adx_set_frequency_cache

CHAREG_TYPE_ACTIVE = configHouseAd.CHAREG_TYPE_ACTIVE
CHAREG_TYPE_CLICK = configHouseAd.CHAREG_TYPE_CLICK
CHAREG_TYPE_VIEW = configHouseAd.CHAREG_TYPE_VIEW
RQ_LIMIT = configAdx.RQ_ADX_LIMIT

SQL_PATH = os.path.join(sys.path[0], 'sql/')
KS = "::"


class AdxCharge(AdxQbase):
    lastMinute = None
    threads = 1
    rlen = 0
    r = None
    rq = None
    warmkey = {}

    def init(self):
        self.r = self.initRedis(configRedis.REDIS_ADX)
        self.rq = self.initRedis(configRedis.REDIS_QUEUE_ADX)
        self.rkey = configHouseAd.RQ_ADX_CLIENT_CHARGE
        self.rq_limit = RQ_LIMIT
        self.minuteKey = configHouseAd.REDIS_MINUTE_STAT
        self.dbConfStat = configDb.MYSQL_MOBGI_HOUSEAD_DATA
        self.dbData = self.initDb(configDb.MYSQL_MOBGI_HOUSEAD_STAT)
        self.table = configHouseAd.TABLE_CHARGE_STAT
        self.fields = configHouseAd.FIELDS_CHARGE_STAT

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
            self.info("loop_use_time:" + str(time.time() - start_time))
        except Exception, e:
            self.error("run error:" + str(e))

    def store(self, threadnum, rkey):
        try:
            start_time = time.time()
            amount = {}
            actives = {}
            clicks = {}
            views = {}
            filename = self.scriptName + "_" + str(threadnum) + time.strftime("_%Y%m%d_%H%M%S.sql", time.localtime())
            file = os.path.join(sys.path[0], SQL_PATH, self.scriptName, filename)
            file_object = codecs.open(file, 'a', 'utf-8')
            count = 0  # 实际个数
            frequencyStreamList = []
            for i in range(RQ_LIMIT):
                stream = self.rq.lpop(rkey)
                if stream is None:
                    break
                stream = json.loads(stream)
                if isinstance(stream, dict) is False:
                    break
                count = count + 1
                createdMinite = time.strftime("%Y%m%d%H%M", time.localtime(stream['created_time']))
                origKey = str(stream['dsp_id']) + KS + createdMinite + KS + str(stream['originality_id'])
                if origKey in amount:
                    amount[origKey].append(float(stream['price']))
                else:
                    amount[origKey] = [float(stream['price'])]
                # 按类型汇总计费次数
                if stream['event_type'] == CHAREG_TYPE_CLICK:
                    if origKey in clicks:
                        clicks[origKey] += 1
                    else:
                        clicks[origKey] = 1
                    if origKey not in views:
                        views[origKey] = 0
                    if origKey not in actives:
                        actives[origKey] = 0

                elif stream['event_type'] == CHAREG_TYPE_VIEW:
                    if origKey in views:
                        views[origKey] += 1
                    else:
                        views[origKey] = 1
                    if origKey not in clicks:
                        clicks[origKey] = 0
                    if origKey not in actives:
                        actives[origKey] = 0

                elif stream['event_type'] == CHAREG_TYPE_ACTIVE:
                    if origKey in actives:
                        actives[origKey] += 1
                    else:
                        actives[origKey] = 1
                    if origKey not in clicks:
                        clicks[origKey] = 0
                    if origKey not in views:
                        views[origKey] = 0

                items = []
                for field in self.fields:
                    items.append(str(stream[field]))
                sqlcontent = '\t'.join(items)
                if sqlcontent != "":
                    file_object.write(sqlcontent + "\n")
                if stream['event_type'] == CHAREG_TYPE_VIEW and stream['originality_id']!=0:
                    frequencyStreamList.append(stream)
            file_object.close()

            #进入设置曝光缓存的缓存的逻辑
            if len(frequencyStreamList)>0:
                adx_set_frequency_cache.FrequencyCache().run(frequencyStreamList)

            if count > 0:
                self.loadData(file)
                self.updateRealData(threadnum, time.localtime(start_time), amount, views, clicks, actives)
                self.lastMinute = createdMinite
            else:
                os.remove(file)
            self.info(str(threadnum) + '_thread_time=' + str(time.time() - start_time))
        except Exception, e:
            self.error("store Exception:" + str(e).strip("'") + ",stream:" + str(stream))

            # 更新实时数据

    def updateRealData(self, threadnum, timestamp, amount, views, clicks, actives):
        pre = configHouseAd.REDIS_CHARGE_PREFIX
        # minute = time.strftime("%Y%m%d%H%M", timestamp)

        # 消费队列
        filename = "batch_deduction_detail_" + str(threadnum) + "_" + str(time.strftime("%Y%m%d%H%M%S", timestamp)) + '.sql'
        file = os.path.join(sys.path[0], SQL_PATH, self.scriptName, filename)
        file_object = codecs.open(file, 'a', 'utf-8')

        for origKey, v in views.items():
            dspId, minute, origId = origKey.split(KS);
            mkey = pre + origKey
            minval = self.r.hincrby(mkey, 'views', v)
            self.updateDayData(pre, timestamp, dspId, origId, 'views', v)

        for origKey, v in clicks.items():
            dspId, minute, origId = origKey.split(KS);
            mkey = pre + origKey
            minval = self.r.hincrby(mkey, 'clicks', v)
            self.updateDayData(pre, timestamp, dspId, origId, 'clicks', v)

        for origKey, v in actives.items():
            dspId, minute, origId = origKey.split(KS);
            mkey = pre + origKey
            minval = self.r.hincrby(mkey, 'actives', v)
            self.updateDayData(pre, timestamp, dspId, origId, 'actives', v)

        for origKey, v in amount.items():
            sumval = float(sum(v))
            dspId, minute, origId = origKey.split(KS);
            mkey = pre + origKey
            mval = self.r.hincrbyfloat(mkey, 'amount', sumval)
            self.updateDayDataFloat(pre, timestamp, dspId, origId, 'amount', sumval)
            self.addMinuteKey(mkey)

            minute_timestamp = int(time.mktime(time.strptime(minute, "%Y%m%d%H%M")))
            fields = []
            fields.append(origId)
            fields.append(str(minute_timestamp))
            fields.append(str(sum(v)))
            sqlcontent = '\t'.join(fields)
            if sqlcontent != "":
                file_object.write(sqlcontent + "\n")

        file_object.close()
        # 扣费队列分钟表
        myTable = configHouseAd.TABLE_ADVERTISER_BATCH_DEDUCTION_DETAIL
        self.loadDataSpecial(file, configDb.MYSQL_MOBGI_HOUSEAD, myTable, configHouseAd.FIELDS[myTable])

    def loadChargeFiles(self):
        self.info('loadChargeFiles')
        myTable = configHouseAd.TABLE_ADVERTISER_BATCH_DEDUCTION_DETAIL
        filePrefix=""
        path = os.path.join(sys.path[0], SQL_PATH, self.scriptName)
        sqlfiles = [f for f in os.listdir(path) if fnmatch.fnmatch(f, filePrefix + "_*.sql")]
        for sqlfile in sqlfiles:
            # todo 优化隔天不导入
            self.info('loadFile:' + sqlfile)
            file = os.path.join(path, sqlfile)
            self.loadDataSpecial(file, configDb.MYSQL_MOBGI_HOUSEAD, myTable, configHouseAd.FIELDS[myTable])


    def addMinuteKey(self, key):
        if self.r.sismember(self.minuteKey, key) == 0:
            self.r.sadd(self.minuteKey, key)

    def updateDayData(self, pre, timestamp, dspId, origId, field, v):
        today = time.strftime("%Y%m%d", timestamp)
        dkey = pre + dspId + KS + today + KS + origId
        dayval = self.r.hincrby(dkey, field, v)
        if (dayval == v and v >= 0):
            self.r.hincrby(dkey, field, int(self.getTodayData(timestamp, field, dspId, origId)))
            self.r.expire(dkey, 1800)

    def updateDayDataFloat(self, pre, timestamp, dspId, origId, field, v):
        today = time.strftime("%Y%m%d", timestamp)
        dkey = pre + dspId + KS + today + KS + origId
        dayval = self.r.hincrbyfloat(dkey, field, v)
        if (dayval == v and v >= 0):
            total = self.getTodayData(timestamp, field, dspId, origId)
            self.r.hincrbyfloat(dkey, field, float(total))
            self.r.expire(dkey, 1800)

    def getTodayData(self, timestamp, field, dspId, origId):
        sql = """select sum(%s) as total from %s where originality_id = '%s' and dsp_id = '%s' and `minute` BETWEEN  "%s"  and "%s";""" % (
            field, 'stat_minute', origId, dspId, time.strftime("%Y-%m-%d", timestamp), time.strftime("%Y-%m-%d %H:%M:%S", timestamp))
        result = self.dbData.fetchone(sql)
        if result is None or result["total"] is None:
            return 0
        else:
            return result["total"]

    def saveMinuteData(self):
        # 延迟
        if self.lastMinute is None:
            return
        now_timestamp = int(time.mktime(time.strptime(self.lastMinute, "%Y%m%d%H%M")))
        keys = self.r.smembers(self.minuteKey)
        for key in keys:
            [dspId, minute, origId] = key.split(KS)[-3:]
            minute_timestamp = int(time.mktime(time.strptime(minute, "%Y%m%d%H%M")))
            # 保存1分钟前->60分钟前分钟数据
            if minute_timestamp < now_timestamp - 60:
                if minute_timestamp > now_timestamp - 360000:
                    self.saveMinuteDataByKey(key, self.exchangeStrDate(minute, "%Y%m%d%H%M", "%Y-%m-%d %H:%M"), origId, dspId)
                else:
                    self.info('too long time to save minute key:' + key)
                    self.r.delete(key)
                    self.r.srem(self.minuteKey, key)
        return

    def saveMinuteDataByKey(self, key, minute, origId, dspId):
        hval = self.r.hgetall(key)
        if hval.has_key("clicks") is False:
            hval["clicks"] = "0"
        if hval.has_key("views") is False:
            hval["views"] = "0"
        if hval.has_key("actives") is False:
            hval["actives"] = "0"
        if hval.has_key("amount") is False:
            hval["amount"] = "0"
        try:
            sql = """INSERT INTO `%s` (`originality_id`, `dsp_id`, `minute`, `clicks`, `views`, `actives`, `amount`) VALUES ("%s","%s","%s",%s,%s,%s,%s)""" % (
                "stat_minute", origId, dspId, minute, hval['clicks'], hval['views'], hval['actives'], hval['amount'])
            self.info("saveMinuteData:" + key + "," + str(minute))
            # self.mylog("minute_sql:" + sql)
            result = self.dbData.execute(sql)
            if result:
                self.r.delete(key)
                self.r.srem(self.minuteKey, key)
        except Exception, e:
            self.error("execute sql error:" + str(e))


if __name__ == '__main__':
    sleepCount = 0
    loadCount = 0
    looptime = time.time()
    while 1:
        obj = AdxCharge('adx_charge')
        if obj.flag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        rlen = obj.getRlen()
        if rlen < RQ_LIMIT and sleepCount < 3 or rlen == 0:
            obj.info("zzz:"+str(rlen))
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            sleepCount = sleepCount + 1
            continue
        sleepCount = 0
        obj.run()
        if time.time() - looptime > 30:
            looptime = time.time()
            obj.saveMinuteData()
        if loadCount > 3:
            loadCount = 0
            obj.loadFiles()
            obj.loadChargeFiles()
        else:
            loadCount += 1
