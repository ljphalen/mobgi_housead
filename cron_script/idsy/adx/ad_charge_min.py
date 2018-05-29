#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import codecs
import commands
from ad_base import AdBase
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis

LIMIT_COUNTS = configAdx.LIMIT_COUNTS

BIT_LENGTH = configAdx.BIT_LENGTH


##统计分钟表
class AdChargeMin(AdBase):
    dataLength = 0
    hours = {}
    apps = None
    channels = None

    def runInit(self):
        self.r = self.initRedis(configRedis.REDIS_MOBGI)
        self.statConfig = configDb.MYSQL_BH_AD_STAT  # inforbride原数据
        self.dataConfig = configDb.MYSQL_MOBGI_DATA  # dbdata
        self.chargeConfig = configDb.MYSQL_MOBGI_CHARGE  # dbcharge

        self.dbData = self.initDb(self.dataConfig)
        self.dbCharge = self.initDb(self.chargeConfig)

        self.chargeTable = configAdx.TABLE_STAT_CHARGE  # 原数据表
        self.minChageTable = configAdx.TABLE_CHARGE_MIN  # 落地minute数据表
        self.initIb()

    def initIb(self):
        self.ib = self.initDb(self.statConfig)

    # 检查是否已经统计
    def checkCondition1(self, startPosition):
        sql = "SELECT count(*) as count FROM %s where id_range=%s" % (self.minChageTable, startPosition)
        result = self.dbCharge.fetchone(sql)
        if result['count'] > 0:
            return False
        else:
            return True

    # 检查infobright是否有新数据
    def checkCondition2(self, nextPosition):
        if nextPosition < self.lastPosition:
            return True
        sql = "SELECT max(id) as id FROM %s" % (self.chargeTable)
        result = self.ib.fetchone(sql)
        if result['id'] is None:
            return False
        else:
            self.lastPosition = int(result['id']) / BIT_LENGTH
            return nextPosition < self.lastPosition

    # 从ad_charge表读取数据
    def getRecordList(self, startPosition, nextPosition):
        startId = startPosition * BIT_LENGTH
        nextId = nextPosition * BIT_LENGTH
        fileds = " as id_range,orig_id,app_key,ads_id,pos_key,ad_type,event_type,count(*) as event_count," \
                 "sum(price) as amount,FROM_UNIXTIME(server_time,'%Y-%m-%d %H:%i') as server_time"
        fileds = str(startPosition) + fileds
        groupby = "orig_id,app_key,ads_id,pos_key,ad_type,event_type,FROM_UNIXTIME(server_time,'%Y-%m-%d %H:%i')"
        sql = "SELECT %s FROM %s WHERE orig_id>0 and id >= %s and id < %s group by %s " % (
            fileds, self.chargeTable, str(startId), str(nextId), groupby)
        self.info('getRecordList:' + str(startPosition))
        result, self.dataLength = self.ib.fetchall(sql)
        return result, self.dataLength

    def paramData(self, recordData):
        result = {}
        if len(recordData) < 1:
            self.info('parseRecord len < 1')
            return result
        try:
            eventTypes = configAdx.CHARGE_EVENT_TYPE
            for record in recordData:
                eventType = int(record.get('event_type'))
                if eventType in eventTypes:
                    origId = record.get('orig_id')
                    adsId = record.get('ads_id')
                    appKey = record.get('app_key')
                    posKey = record.get("pos_key")
                    adType = record.get("ad_type")
                    serverTime = record.get("server_time")
                    id_range = record.get('id_range')
                    origInfo = self.getOrigInfo(origId)
                    adId = origInfo['ad_id']
                    unitId = origInfo['unit_id']
                    eventCount = int(record.get('event_count'))
                    eventTypeName = eventTypes[eventType]
                    amount = float(record.get('amount'))
                    # 添加广告位维度
                    key = str(origId) + str(adsId) + str(appKey) + str(posKey) + str(adType) + str(serverTime)
                    if key not in result:
                        result[key] = {
                            "id_range": id_range,
                            "ad_id": adId,
                            "ad_unit_id": unitId,
                            "originality_id": origId,
                            "ads_id": adsId,
                            "app_key": appKey,
                            "pos_key": posKey,
                            "ad_type": adType,
                            "days": serverTime[0:10],
                            "minutes": serverTime[-5:],
                            "amount": 0,
                        }
                        for eventName in eventTypes.itervalues():
                            result[key][eventName] = 0
                    result[key][eventTypeName] += eventCount
                    result[key]["amount"] += amount
        except Exception, e:
            raise Exception("paramData:" + str(e))
        return result

    def getOrigInfoFromDb(self, OrigId):
        dbHousead = self.initDb(configDb.MYSQL_MOBGI_HOUSEAD)
        table = "mobgi_housead.delivery_originality_relation"
        sql = "SELECT id,ad_id,unit_id,originality_type,account_id FROM %s WHERE `id` = %s LIMIT 0, 1" % (table, str(OrigId))
        result = dbHousead.fetchone(sql)
        return result

    def getOrigInfo(self, OrigId):
        if OrigId == '0' or OrigId is None:
            return None
        rkey = configAdx.REDIS_ORIGINFO_PRE + str(OrigId)
        if self.r.hget(rkey, "ad_id") is None:
            result = self.getOrigInfoFromDb(OrigId)
            if result is not None:
                self.r.hmset(rkey, {
                    'ad_id': result.get('ad_id'),
                    'unit_id': result.get('unit_id'),
                    'originality_type': result.get('originality_type'),
                    'account_id': result.get('account_id')
                })
                self.r.expire(rkey, 1800)
        else:
            result = self.r.hgetall(rkey)
        if result is None:
            result = {
                "ad_id": 0,
                "unit_id": 0,
                "originality_type": 0,
                "account_id": 0
            }
            self.info("Cannot get origInfo with id:" + str(OrigId))
        return result

    # 存入min表
    def saveRecordData(self, data):
        if data is None:
            self.info("save data is None")
            return False
        saveData = self.formatData(data)
        sql = """insert into adx_charge_minute(id_range,ad_unit_id,ad_id,originality_id,ads_id,app_key,pos_key,ad_type,clicks,views,actives,amount,
        days,minutes)values (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)on duplicate key update clicks=%s,views=%s,actives=%s,amount=%s"""
        # self.info("data:" + str(data))
        return self.dbCharge.executeMany(sql, saveData)

    def formatData(self, listData):
        result = []
        for item in listData.values():
            if item['ads_id'] is None:
                item['ads_id'] = ''
            values = (item["id_range"], item["ad_unit_id"], item["ad_id"], item["originality_id"], item["ads_id"], item["app_key"], item["pos_key"],
                      item['ad_type'], item["clicks"], item["views"], item["actives"], item["amount"], item["days"], item["minutes"], item["clicks"],
                      item["views"], item["actives"], item["amount"])
            result.append(values)
        return tuple(result)

    def run(self):
        try:
            self.runInit()
            startTimeStamp = time.time()
            self.startPosition, status = self.getStartPosition()
            # 判断状态
            if status != 1:
                self.dataLength = 0
                self.info("status is stop")
                return False

            if self.checkCondition1(self.startPosition) is not True:
                self.error("record is exist")
                return False

            # 判断是否有新数据
            self.nextPosition = self.startPosition + 1
            if self.checkCondition2(self.nextPosition) is not True:
                self.info("Not to start position")
                return False

            ##获取统计
            recordData, self.dataLength = self.getRecordList(self.startPosition, self.nextPosition)
            if self.dataLength > 0:
                if self.saveRecordData(self.paramData(recordData)) is not False:
                    self.updatePosition()
            else:
                self.info("no recordData")
                self.updatePosition()
            self.info("use time : " + str(time.time() - startTimeStamp))
        except Exception, e:
            self.error("run error:" + str(e))


if __name__ == '__main__':
    sleepCount = 0
    max_count = 5
    lastPosition = 0
    while 1:
        obj = AdChargeMin('ad_charge_min')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        obj.lastPosition = lastPosition
        obj.run()
        count = obj.dataLength
        lastPosition = obj.lastPosition
        if count < 1:
            obj.info("zzz:" + str(count))
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            # time.sleep(1)
            continue
