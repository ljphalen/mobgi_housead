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


##统计DSP中间表
class AdMidDsp(AdBase):
    dataLength = 0
    hours = {}
    apps = None
    channels = None
    dims = configAdx.DIMS_DSP
    kpis = configAdx.KPIS_DSP

    def runInit(self):
        self.r = self.initRedis(configRedis.REDIS_MOBGI)
        self.statConfig = configDb.MYSQL_BH_AD_STAT
        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.midConfig = configDb.MYSQL_BH_AD_MID

        self.dbData = self.initDb(self.dataConfig)
        self.dbMid = self.initDb(self.midConfig)

        self.hourTable = configAdx.TABLE_REPORT_DSP
        self.clientTable = configAdx.TABLE_STAT_SERVER
        self.midTable = configAdx.TABLE_MID_DSP

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

        fileds = " as id_range,dsp_id,event_type,app_key,block_id,platform,ad_type,count(*) as event_count," \
                 "FROM_UNIXTIME(server_time,'%Y-%m-%d %H') as server_time"
        fileds = str(startPosition) + fileds
        groupby = "dsp_id,event_type,app_key,block_id,platform,ad_type,FROM_UNIXTIME(server_time,'%Y-%m-%d %H')"
        sql = "SELECT %s FROM %s WHERE id >=%s and id<%s group by %s " % (
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
        for item in data:
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
        obj = AdMidDsp('ad_mid_dsp')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        obj.lastPosition = lastPosition
        result = obj.run()
        count = obj.dataLength
        lastPosition = obj.lastPosition
        if result is False:
            obj.info("zzz:" + str(count))
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            # time.sleep(1)
            continue
