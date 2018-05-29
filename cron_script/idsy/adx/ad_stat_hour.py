#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import commands
from ad_base import AdBase
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis
import traceback

LIMIT_COUNTS = configAdx.LIMIT_COUNTS


##小时数据汇总
class AdStatHour(AdBase):
    dataLength = 0
    hours = {}
    apps = None
    channels = None
    dims = configAdx.DIMS_HOUR
    kpis = configAdx.KPIS_HOUR

    def runInit(self):
        self.r = self.initRedis(configRedis.REDIS_MOBGI)

        self.statConfig = configDb.MYSQL_BH_AD_STAT
        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.midConfig = configDb.MYSQL_BH_AD_MID

        self.dbData = self.initDb(self.dataConfig)
        self.dbMid = self.initDb(self.midConfig)

        self.hourTable = configAdx.TABLE_REPORT_HOUR
        self.midTable = configAdx.TABLE_MID_HOUR

        self.midFields = configAdx.FIELDS[self.midTable]
        # self.initIb()

    def initIb(self):
        self.ib = self.initDb(self.statConfig)

    # 检查infobright是否有新数据
    def checkCondition(self, startPosition):
        sql = "SELECT max(id_range) as id FROM %s" % (self.midTable)
        result = self.dbMid.fetchone(sql)
        # 判断记录是否存在
        if result['id'] is None:
            return False
        else:
            self.lastPosition = int(result['id'])
            return startPosition < self.lastPosition

    def getHours(self, startPosition):
        sql = "SELECT server_time FROM %s WHERE id_range>%s order by id_range limit 1" % (self.midTable, startPosition)
        list, count = self.dbMid.fetchall(sql, None)
        result = []
        if count > 0:
            for item in list:
                result.append(item[0].strftime("%Y-%m-%d %H:00:00"))
        return result

    def getMaxIdRange(self, hours):
        sql = "SELECT max(id_range) as id FROM %s WHERE server_time='%s'" % (self.midTable, max(hours))
        result = self.dbMid.fetchone(sql)
        if result['id'] is None:
            return False
        else:
            return int(result['id'])

    def getHourData(self, hours):
        if len(hours) == 1:
            where = "server_time = '%s' " % (hours[0])
        else:
            where = "server_time in %s " % (str(tuple(hours)))
        # where += " and cid='TEST0000000'"
        fileds = "ads_id,ssp_id,app_key,pos_key,cid,ad_type,platform,app_version,sdk_version,server_time,event_type," \
                 "sum(event_count) as event_count,sum(event_value) as event_value,sum(event_time) as event_time"
        groupby = "ads_id,ssp_id,app_key,pos_key,cid,ad_type,platform,app_version,sdk_version,server_time,event_type"
        sql = "SELECT %s FROM %s WHERE %s group by %s " % (fileds, self.midTable, where, groupby)
        # self.info('getHourData:' + str(sql))
        result, self.dataLength = self.dbMid.fetchall(sql)
        return result

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

    def paramData(self, recordData):
        result = {}
        if len(recordData) < 1:
            self.info('parseRecord len < 1')
            return result

        eventTypes = configAdx.ADX_EVENT_TYPE
        for record in recordData:
            eventType = int(record.get('event_type'))
            if eventType in eventTypes:
                sspId = str(record.get('ssp_id'))
                adsId = str(record.get('ads_id'))
                appKey = str(record.get('app_key'))
                posKey = str(record.get('pos_key'))

                adType = str(record.get('ad_type'))
                cid = str(record.get('cid'))
                platform = int(self.getAppPlatform(appKey))
                if platform < 0:
                    self.error('platform-continue:' + appKey + "#" + str(platform))
                    continue

                gid = self.getChannelGid(cid)
                isCustom = self.getChannelCustomMap(gid, adsId)
                sdkVersion = str(record.get('sdk_version'))
                appVersion = str(record.get('app_version'))
                eventCount = int(record.get('event_count'))
                eventTime = int(record.get('event_time'))
                eventValue = int(record.get('event_value'))
                serverTime = record.get("server_time").strftime('%Y-%m-%d_%H')
                eventTypeName = eventTypes[eventType]
                # 添加广告位维度
                key = sspId + adsId + appKey + posKey + str(gid) + adType + serverTime + sdkVersion + appVersion
                if key not in result:
                    result[key] = {
                        "ssp_id": sspId,
                        "ads_id": adsId,
                        "app_key": appKey,
                        "pos_key": posKey,
                        "channel_gid": gid,
                        "is_custom": isCustom,
                        "platform": platform,
                        "ad_type": adType,
                        "sdk_version": sdkVersion,
                        "app_version": appVersion,
                        "days": serverTime[0:10],
                        "hours": serverTime[-2:],
                        "event_count": 0,
                        "event_type": eventType,
                        "skip_stay_time": 0,
                        "exit_stay_time": 0
                    }
                    for eventName in eventTypes.itervalues():
                        result[key][eventName] = 0
                result[key][eventTypeName] += eventCount
                # if eventType == configAdx.EVENT_EXIT:
                #     result[key]['exit_stay_time'] += eventTime
                if eventType == configAdx.EVENT_SKIP:
                    result[key]['skip_stay_time'] += eventValue
        res = []
        for key in result:
            res.append(result[key])
            if len(result[key]['pos_key']) > 10 and self.isPosKeyMatchAppKey(result[key]['app_key'], result[key]['pos_key']) is False:
                self.info('poskey-continue:' + result[key]['pos_key'] + ",appKey:" + result[key]['app_key'] + ",event_type:" + str(
                    result[key]['event_type']) + ",ad_type:" + str(result[key]['ad_type']) + ",ads_id:" + str(
                    result[key]['ads_id']) + ",download:" + str(result[key]['download']))

        return res

    def saveData(self, data):
        if len(data) < 1:
            self.info('saveData len(data) <1')
            return True
        try:
            result = []
            for item in data:
                item['effective_impressions'] = 0
                ad_type = int(item['ad_type'])
                if ad_type == configAdx.AD_TYPE_PIC or ad_type == configAdx.AD_TYPE_VIDEO or ad_type == configAdx.AD_TYPE_SPLASH:
                    item['effective_impressions'] = item['closes']
                elif ad_type == configAdx.AD_TYPE_NATIVE:
                    item['effective_impressions'] = item['impressions']
                item['exit_stay_time'] = int(item['exit_stay_time'] / 1000)
                values = []
                for field in self.dims:
                    values.append(str(item[field]))
                for field in self.kpis:
                    values.append(str(item[field]))
                for field in self.kpis:
                    values.append(str(item[field]))
                result.append(tuple(values))

            fields = self.dims + self.kpis
            updateArr = []
            for kpi in self.kpis:
                updateArr.append(kpi + "=" + "%s")
            sql = "insert into %s (%s) values (%s) on duplicate key update %s;" % (
                self.hourTable, ",".join(fields), ("%s," * len(fields))[:-1], ",".join(updateArr))
            self.info('updateReport')
            self.dbData.executeMany(sql, result)
            return True
        except Exception, e:
            traceback.print_exc()
            raise Exception("saveData error :" + str(e))

    def run(self):

        try:
            self.runInit()
            startTimeStamp = time.time()
            self.startPosition, status = self.getStartPosition()
            # 判断状态
            if status != 1:
                self.info("status is stop")
                return False

            # 判断是否有新数据
            if self.checkCondition(self.startPosition) is not True:
                self.info("Not to start position")
                return False
            self.info("startPosition:" + str(self.startPosition))
            hours = self.getHours(self.startPosition)
            self.nextPosition = self.getMaxIdRange(hours)
            if self.nextPosition is False:
                return False
            hourData = self.getHourData(hours)
            # self.info("hourData" + str(hourData))
            paramData = self.paramData(hourData)
            if self.saveData(paramData) is True:
                self.updatePosition()
                self.info("use time : " + str(time.time() - startTimeStamp))
                return True
            else:
                self.info("use time : " + str(time.time() - startTimeStamp))
                return False



        except Exception, e:
            traceback.print_exc()
            self.error("run error:" + str(e))
        return False


if __name__ == '__main__':
    sleepCount = 0
    max_count = float(LIMIT_COUNTS)
    while 1:
        obj = AdStatHour('ad_stat_hour')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        if obj.run() is not True:
            obj.info("zzz:" + str(obj.dataLength))
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            # time.sleep(1)
            continue
