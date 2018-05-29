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
class AdStatTryHour(AdBase):
    dataLength = 0
    hours = {}
    apps = None
    channels = None
    dims = configAdx.DIMS_TRY
    kpis = configAdx.KPIS_TRY

    def runInit(self):
        self.r = self.initRedis(configRedis.REDIS_MOBGI)

        self.statConfig = configDb.MYSQL_BH_AD_STAT
        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.midConfig = configDb.MYSQL_BH_AD_MID

        self.dbData = self.initDb(self.dataConfig)
        self.dbMid = self.initDb(self.midConfig)

        self.tryHourTable = configAdx.TABLE_REPORT_TRY_HOUR
        self.midTable = configAdx.TABLE_MID_TRY
        self.midFields = configAdx.FIELDS[self.midTable]

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
        fileds = "app_key,try_key,orig_id,pos_key,cid,ad_type,platform,app_version,sdk_version,server_time,event_type,count(1) as event_count"
        groupby = "app_key,try_key,orig_id,pos_key,cid,ad_type,platform,app_version,sdk_version,server_time,event_type"
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

    def paramData(self, recordData):
        result = {}
        if len(recordData) < 1:
            self.info('parseRecord len < 1')
            return result

        eventTypes = configAdx.TRY_EVENT_TYPE
        for record in recordData:
            eventType = int(record.get('event_type'))
            if eventType in eventTypes:
                try_key = str(record.get('app_key'))
                app_key = str(record.get('app_key'))
                pos_key = str(record.get('pos_key'))
                ad_type = str(record.get('ad_type'))
                orig_id = str(record.get('orig_id'))
                gid = str(record.get('gid'))
                platform = int(self.getAppPlatform(app_key))
                sdk_version = str(record.get('sdk_version'))
                app_version = str(record.get('app_version'))
                eventCount = int(record.get('event_count'))
                server_time = record.get("server_time").strftime('%Y-%m-%d_%H')
                eventTypeName = eventTypes[eventType]
                # 添加广告位维度
                key = app_key + try_key + pos_key + ad_type + server_time + app_version + sdk_version
                if key not in result:
                    result[key] = {
                        "app_key": app_key,
                        "pos_key": pos_key,
                        "try_key": try_key,
                        "ad_type": ad_type,
                        "channel_gid": gid,
                        "originality_id": orig_id,
                        "platform": platform,
                        "sdk_version": sdk_version,
                        "app_version": app_version,
                        "days": server_time[0:10],
                        "hours": server_time[-2:],
                        "event_count": 0,
                        "event_value": 0
                    }
                    for eventName in eventTypes.itervalues():
                        result[key][eventName] = 0
                result[key][eventTypeName] += eventCount
        return result

    def saveData(self, data):
        if len(data) < 1:
            self.info('saveData len(data) <1')
            return True

        result = []
        for item in data.values():
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
            self.tryHourTable, ",".join(fields), ("%s," * len(fields))[:-1], ",".join(updateArr))
        self.info('updateReport')
        self.dbData.executeMany(sql, result)
        return True

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
        obj = AdStatTryHour('ad_stat_try_hour')
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
