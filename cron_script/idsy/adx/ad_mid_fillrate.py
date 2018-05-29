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
import json
LIMIT_COUNTS = configAdx.LIMIT_COUNTS

BIT_LENGTH = configAdx.BIT_LENGTH


##统计中间表
class AdMidFillRate(AdBase):
    dataLength = 0
    hours = {}
    apps = None
    channels = None

    def runInit(self):
        self.r = self.initRedis(configRedis.REDIS_MOBGI)
        self.statConfig = configDb.MYSQL_BH_AD_STAT
        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.midConfig = configDb.MYSQL_BH_AD_MID

        self.dbData = self.initDb(self.dataConfig)
        self.dbMid = self.initDb(self.midConfig)

        self.hourTable = configAdx.TABLE_REPORT_HOUR
        self.clientTable = configAdx.TABLE_STAT_CLIENT
        self.midTable = configAdx.TABLE_MID_FILLRATE

        self.clientFields = configAdx.FIELDS[self.clientTable]
        self.midFields = configAdx.FIELDS[self.midTable]
        self.initIb()

    def initIb(self):
        self.ib = self.initDb(self.statConfig)

    # 检查infobright是否有新数据
    def checkCondition(self, nextPosition):
        if nextPosition < self.lastPosition:
            return True
        sql = "SELECT max(id) as id FROM %s" % (self.clientTable)
        result = self.ib.fetchone(sql)
        # 判断记录是否存在
        if result is None:
            return False
        else:
            result = round(float(result['id']) / float(BIT_LENGTH), 1)
            self.lastPosition = int(result)
            return nextPosition < result

    def getRecordList(self, startPosition, nextPosition):
        startId = startPosition * BIT_LENGTH
        nextId = nextPosition * BIT_LENGTH
        fileds = " as id_range,ssp_id,ads_id,app_key,pos_key,ad_type,cid,platform,app_version,sdk_version," \
                 "event_value,FROM_UNIXTIME(server_time,'%Y-%m-%d %H') " \
                 "as server_time"
        fileds = str(startPosition) + fileds
        groupby = "ssp_id,ads_id,app_key,pos_key,ad_type,cid,platform,app_version,sdk_version,FROM_UNIXTIME(server_time,'%Y-%m-%d %H')"
        sql = "SELECT %s FROM %s WHERE id >=%s and id<%s and ver in (1,4) and event_type = 5 group by %s " % (
            fileds, self.clientTable, str(startId), str(nextId), groupby)
        self.info('getRecordList:' + str(startPosition))
        result, self.dataLength = self.ib.fetchall(sql)
        return result, self.dataLength

    def saveRecordData(self, data):
        if data is None:
            self.info("save data is None")
            return False
        file = self.getSqlPath(str(self.startPosition))
        if os.path.isfile(file):
            self.info("a file already exists:" + file)
            os.remove(file)
            # return False
        content = ""
        data = self.dataParase(data)
        for item in data:
            items = []
            for filed in self.midFields:
                if filed == 'gid':
                    data[item][filed] = self.getChannelGid(data[item]['cid'])
                if filed == 'is_custom':
                    data[item][filed] = self.getChannelCustomMap(data[item]['gid'])
                items.append(str(data[item][filed]))
            content += '\t'.join(items) + '\n'
        self.saveDataToFile(file, content)
        self.info("save as file:" + file)
        return file

    def dataParase(self,data):
        result = {}
        for item in data:
            adsJson = self.is_json(item['event_value'])
            if adsJson is False:
                continue
            for ads in adsJson:
                key = str(item['ssp_id'])+str(item['ads_id'])+str(item['cid'])+str(item['platform'])+str(item['pos_key'])+str(item['app_version'])+str(item['ad_type'])+str(item['app_key'])+str(item['server_time'])+ads
                if result.has_key(key) is False:
                    result[key] = {
                        'id_range':item['id_range'],
                        'ssp_id':item['ssp_id'],
                        'ads_id':item['ads_id'],
                        'cid':item['cid'],
                        'app_key':item['app_key'],
                        'ad_type':item['ad_type'],
                        'platform':item['platform'],
                        'sdk_version':item['sdk_version'],
                        'app_version':item['app_version'],
                        'pos_key':item['pos_key'],
                        'server_time':item['server_time'],
                        'cache_success':0,
                        'cache_fail':0,
                        'cache_show':0
                    }
                if self.checkIsSuccess(adsJson[ads]) is True:
                    result[key]['cache_success'] +=1
                    if item['ads_id'] == ads:
                        result[key]['cache_show'] +=1
                else:
                    result[key]['cache_fail'] +=1
        return result

    def checkIsSuccess(self,boolInfo):
        if boolInfo is True:
            return True
        elif boolInfo is False:
            return False
        elif boolInfo.lower() == 'true':
            return True
        elif boolInfo.lower() == 'false':
            return False
        else:
            return False

    def is_json(self,myjson):
        try:
            if myjson[0] == '{':
                json_object = json.loads(myjson)
                return json_object
            else:
                return False
        except ValueError, e:
            return False

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
            # output = {0: 0}
            if output[0] != 0:
                self.info("###:" + command)
                self.error('load file failed:' + file)
                return False
            return True
        except Exception, e:
            raise Exception("load data error:" + str(e))

    def delMidData(self, startPosition):
        sql = "delete FROM %s WHERE id_range = %s " % (self.midTable, startPosition)
        return self.dbMid.execute(sql)

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

            # 判断是否有新数据
            self.nextPosition = self.startPosition + 1
            if self.checkCondition(self.nextPosition) is not True:
                self.info("Not to start position")
                return False

            ##获取统计
            recordData, self.dataLength = self.getRecordList(self.startPosition, self.nextPosition)
            if self.dataLength > 0:
                file = self.saveRecordData(recordData)
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
    max_count = 100
    lastPosition = 0
    while 1:
        obj = AdMidFillRate('ad_mid_fillrate')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            break
        obj.lastPosition = lastPosition
        result = obj.run()
        count = obj.dataLength
        lastPosition = obj.lastPosition
        if result is False:
            obj.info("zzz:" + str(count))
            obj = None
            #time.sleep(configAdx.SLEEP_SECOND)
            # time.sleep(1)
            break
