#!/usr/bin/env python
# -*- coding:utf-8 -*-
import commands
import fnmatch
import os
import sys
import threading
import time
import json

sys.path.append("..")
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis
from lib.mybase import Base


class AdActive(Base):
    rqLimit = configAdx.RQ_ADX_LIMIT
    ACTIVE_EVENT_TYPE = 45

    def init(self):
        self.clientTable = configAdx.TABLE_STAT_CLIENT
        self.activeDataRQKey = configAdx.RQ_ADX_ACITIVE
        self.adxClientRQKey = configAdx.RQ_ADX_CLIENT
        self.adxChargeRQKey = configAdx.RQ_ADX_CHARGE
        self.adxChargeRQ = self.initRedis(configRedis.REDIS_QUEUE_ADX)
        self.adxClientRQ = self.initRedis(configRedis.REDIS_QUEUE_ADX)
        self.BH_STAT = self.initDb(configDb.MYSQL_BH_AD_STAT)

    def run(self):
        try:
            self.startTimestamp = time.time()
            self.info("activeDataLen:" + str(self.activeDataLen))
            tasks = []
            if self.activeDataLen > 0:
                for i in xrange(0, self.threads):
                    t = threading.Thread(target=self.store, args=(i, self.activeDataRQKey))
                    tasks.append(t)
                    t.start()
                for t in tasks:
                    t.join()
            self.info("loop_time:" + str(time.time() - self.startTimestamp))
        except Exception, e:
            self.error("run error:" + str(e))

    def store(self, threadnum, activeDataRQKey):
        try:
            stream = None
            listStream = []
            for i in range(self.rqLimit):
                stream = self.adxChargeRQ.lpop(activeDataRQKey)
                if stream is None:
                    break
                listStream.append(stream)

            bidIds = []
            for stream in listStream:
                stream = json.loads(stream)
                if isinstance(stream, dict) is False:
                    continue
                bidInfo = self.getDataByBidId(stream['request_id'])
                self.info('bidInfo Data:' + str(bidInfo))
                if bidInfo is None:
                    self.error('request_id is not find data:' + stream['request_id'])
                    continue
                if stream.has_key('server_time'):
                    bidInfo['server_time'] = int(stream['server_time'])
                else:
                    bidInfo['server_time'] = int(time.time())
                self.saveOriginalData(bidInfo)

            self.info(str(threadnum) + '_thread_time=' + str(time.time() - self.startTimestamp))
        except Exception, e:
            self.error("store Exception:" + str(e), ",stream:" + str(stream))

    def saveOriginalData(self, bidInfo):
        data = {
            'bid_id': str(bidInfo['bit_id']),
            'orig_id': str(bidInfo['orig_id']),
            'ads_id': 'Housead_Dsp',
            'ssp_id': bidInfo['ssp_id'],
            'app_key': str(bidInfo['app_key']),
            'pos_key': str(bidInfo['pos_key']),
            'ad_type': str(bidInfo['ad_type']),
            'ad_sub_type': str(bidInfo['ad_sub_type']),
            'event_type': self.ACTIVE_EVENT_TYPE,
            'charge_type': configAdx.CHARGE_TYPE_CPA,
            'price': str(bidInfo['price']),
            'currency': str(bidInfo['currency']),
            'cid': str(bidInfo['cid']),
            'brand': str(bidInfo['brand']),
            'model': str(bidInfo['model']),
            'operator': str(bidInfo['operator']),
            'event_value': str(bidInfo['event_value']),
            'imei': str(bidInfo['imei']),
            'imsi': str(bidInfo['imsi']),
            'platform': str(bidInfo['platform']),
            'uuid': str(bidInfo['uuid']),
            'server_time': int(bidInfo['server_time']),
            'app_version': str(bidInfo['app_version']),
            'sdk_version': str(bidInfo['sdk_version']),
            'client_ip': str(bidInfo['client_ip']),
            'vh': str(bidInfo['vh']),
            'point_x': str(bidInfo['point_x']),
            'point_y': str(bidInfo['point_y']),
            'ver': str(bidInfo['ver']),
        }
        originaData = json.dumps(data, skipkeys=True)
        self.adxChargeRQ.lpush(self.adxChargeRQKey, originaData)
        self.adxClientRQ.lpush(self.adxClientRQKey, originaData)

    def getDataByBidId(self, bidID):
        config_time = int(time.mktime(time.strptime(bidID[:14], '%Y%m%d%H%M%S')))
        config_time_end = int(config_time + 86400)
        sql = 'select * from ' + self.clientTable + ' where event_type=6 and server_time>' + str(config_time) + ' and server_time<' + str(config_time_end) + ' and bit_id="' + bidID + '" limit 1'
        return self.BH_STAT.fetchone(sql)

    def getRQlen(self, adxChargeRQ, activeDataRQKey):
        try:
            if adxChargeRQ.ping():
                self.activeDataLen = adxChargeRQ.llen(activeDataRQKey)
                if self.activeDataLen > 5000000:
                    self.error('rlen>5000000', 'len=' + str(self.activeDataLen))
            else:
                self.activeDataLen = 0
                self.error('redis queue has gone away')
            self.threads = self.getThreads(self.activeDataLen)
        except Exception, e:
            self.activeDataLen = 0
            self.error("redis error:" + str(e))
        finally:
            return self.activeDataLen

    def getThreads(self, count):
        return len(str(int(count / 2000) + 1))


if __name__ == '__main__':
    sleepCount = 0
    loadCount = 0

    while 1:
        obj = AdActive('ad_active')
        if obj.errorFlag:
            obj = None
            time.sleep(300)
            continue
        activeDataLen = obj.getRQlen(obj.adxChargeRQ, obj.activeDataRQKey)
        if (sleepCount < 2) or activeDataLen == 0:
            obj.info("active user info :" + str(activeDataLen))
            obj = None
            time.sleep(300)
            sleepCount = sleepCount + 1
            continue
        sleepCount = 0
        obj.run()
        if loadCount > 3:
            loadCount = 0
        else:
            loadCount += 1
