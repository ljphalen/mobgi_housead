#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import codecs
import commands
import urllib2
import json
from ad_base import AdBase
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis

LIMIT_COUNTS = configAdx.LIMIT_COUNTS

BIT_LENGTH = configAdx.BIT_LENGTH
IP_MAP = {}
SQL_PATH = 'sql'


# 国家地区统计
class AdMidIp(AdBase):
    ip = {}
    ip_pre = 'ip::'
    kpis = []
    dims = []
    dataLength = 0
    hours = {}
    apps = None
    channels = None

    def init(self):
        self.kpis = configAdx.DIMS_CITY
        self.dmis = configAdx.KPIS_HOUR
        self.r = self.initRedis(configRedis.REDIS_MOBGI, 1)
        self.statConfig = configDb.MYSQL_BH_AD_STAT
        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.midConfig = configDb.MYSQL_BH_AD_MID

        self.clientTable = configAdx.TABLE_STAT_CLIENT
        self.midTable = configAdx.TABLE_MID_IP

        self.clientFields = configAdx.FIELDS[self.clientTable]
        self.midFields = configAdx.FIELDS[self.midTable]

    def initIb(self):
        self.ib = self.initDb(self.statConfig)

    def closeIb(self):
        self.ib.close()

    # 检查infobright是否有新数据
    def checkCondition(self, nextPosition):
        if nextPosition < self.lastPosition:
            return True
        sql = "SELECT max(id) as id FROM %s" % (self.clientTable)
        result = self.ib.fetchone(sql)
        # 判断记录是否存在
        if result['id'] is None:
            return False
        else:
            self.lastPosition = int(result['id']) / BIT_LENGTH
            return nextPosition < self.lastPosition

    def getRecordList(self, startPosition, nextPosition):
        startId = startPosition * BIT_LENGTH
        nextId = nextPosition * BIT_LENGTH
        fileds = " as id_range,app_key,platform,event_type,client_ip,count(1) as event_count,sum(event_time) as event_time,FROM_UNIXTIME(" \
                 "server_time,'%Y-%m-%d') as server_time"
        fileds = str(startPosition) + fileds
        groupby = "app_key,platform,event_type,client_ip,FROM_UNIXTIME(server_time,'%Y-%m-%d')"
        sql = "SELECT %s FROM %s WHERE id >= %s and id < %s group by %s " % (fileds, self.clientTable, str(startId), str(nextId), groupby)
        self.info('getRecordList:' + str(startPosition))
        result, self.dataLength = self.ib.fetchall(sql)
        return result, self.dataLength

    def getCityByIp(self, ip):
        # 在这里根据 ip 访问api获取国家和地区信息
        country = '--'
        province = '--'
        city = '--'
        if len(ip) < 7:
            return country, province, city
        try:
            if ip.find(',') != -1:
                ip = ip.split(',').strip()
            flag = True
            # 首先判断是否存在内存中
            if ip in IP_MAP:
                [country, province, city] = IP_MAP[ip].split(',')
            else:
                ip_key = self.ip_pre + ip
                result = self.r.get(ip_key)
                if result == 'None' or result is None:
                    [country, province, city] = self.getIpInfo(ip)
                    self.r.set(ip_key, result)
                    self.r.expire(ip_key, configAdx.IP_CACHE_SECOND)
                    IP_MAP[ip] = country + "," + province + "," + city

        except Exception, e:
            raise Exception('get country and area failed! ip=' + str(ip) + " error:" + str(e))

        return country, province, city

    def saveRecordData(self, data):
        if data is None:
            self.info("save data is None")
            return False
        self.info("saveRecordData:" + str(len(data)))
        file = self.getSqlPath(str(self.startPosition))
        if os.path.isfile(file):
            self.info("a file already exists:" + file)
            os.remove(file)
            # return False
        content = ""
        for item in data.values():
            items = []
            for filed in self.midFields:
                items.append(str(item[filed]))
            content += '\t'.join(items) + '\n'
        self.saveDataToFile(file, content)
        self.info("save as file:" + file)
        return file

    def loadFileToMid(self, file):
        if file is False or os.path.isfile(file) is False:
            self.info("file does not exist:" + str(file))
            return False
        try:
            self.info('load file:' + file)
            conn = self.midConfig
            loadSql = "LOAD DATA LOCAL INFILE '%s' INTO TABLE %s character set utf8 FIELDS TERMINATED BY '\\t' ENCLOSED BY '' " \
                      "escaped by '' LINES TERMINATED BY '\\n' STARTING BY '' (%s) " % (file, self.midTable, ','.join(self.midFields))
            command = configAdx.MYSQL_BIN + " -u%s -p%s  -h%s -D%s -P%s --local-infile=1 -e \"%s\" && rm -rf %s 2>&1" % (
                conn["user"], conn["passwd"], conn["host"], conn["db"], conn["port"], loadSql, file)
            output = commands.getstatusoutput(command)  # 导入成功则删除sql文件
            # output = { 0: 0 }
            if output[0] != 0:
                self.info("###:" + command)
                self.error('load file failed:' + file)
                return False
            return True
        except Exception, e:
            raise Exception("load data error:" + str(e))

    def delMidData(self, startPosition):
        self.dbMid = self.initDb(self.midConfig)
        sql = "delete FROM %s WHERE id_range = %s " % (self.midTable, startPosition)
        return self.dbMid.execute(sql)

    def xchangeData(self, recordData):
        result = {}
        if len(recordData) < 1:
            self.info('xchangeData len < 1')
            return result
        try:
            self.info('xchangeData len:' + str(len(recordData)))
            for record in recordData:
                idRange = str(record.get('id_range'))
                eventType = str(record.get('event_type'))
                appKey = str(record.get('app_key'))
                platform = int(self.getAppPlatform(appKey))
                if platform < 0:
                    self.error('platform-continue:' + appKey + "#" + str(platform))
                    continue
                ip = str(record.get('client_ip'))
                country, province, city = self.getCityByIp(ip)
                serverTime = record.get("server_time")
                event_time = record.get("event_time")
                key = idRange + appKey + serverTime + country + province + city + eventType
                if key not in result:
                    result[key] = {
                        "id_range": idRange,
                        "app_key": appKey,
                        "platform": platform,
                        "server_time": serverTime,
                        "ip": ip,
                        "country": country,
                        "province": province,
                        "city": city,
                        "event_type": eventType,
                        "event_count": 0,
                        "event_time": event_time
                    }
                result[key]["event_count"] += int(record.get('event_count'))
        except Exception, e:
            raise Exception("xchangeData:" + str(e))
        return result

    # infobright数据汇总统计
    def run(self):
        try:
            startTimeStamp = time.time()
            self.startPosition, status = self.getStartPosition()
            # 判断状态
            if status != 1:
                self.dataLength = 0
                self.info("status is stop")
                return False
            self.initIb()
            # 判断是否有新数据
            self.nextPosition = self.startPosition + 1
            if self.checkCondition(self.nextPosition) is not True:
                self.info("Not to start position")
                return False

            ##获取统计
            recordData, self.dataLength = self.getRecordList(self.startPosition, self.nextPosition)
            self.closeIb()
            cityData = self.xchangeData(recordData)
            if self.dataLength > 0:
                file = self.saveRecordData(cityData)
                self.delMidData(self.startPosition)
                if self.loadFileToMid(file):
                    self.updatePosition()
            else:
                self.info("no recordData")
                self.updatePosition()

            self.info("use time : " + str(time.time() - startTimeStamp))
            return True

        except Exception, e:
            self.error("run error:" + str(e))
        return False


if __name__ == '__main__':
    sleepCount = 0
    max_count = float(LIMIT_COUNTS)

    while 1:
        obj = AdMidIp('ad_mid_ip')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        ip_len = len(IP_MAP)
        if ip_len > 1000000:
            IP_MAP = {}
        else:
            obj.info('ip len:' + str(ip_len))

        if obj.run() is not True:
            obj.info("zzz")
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            # time.sleep(1)
            continue
