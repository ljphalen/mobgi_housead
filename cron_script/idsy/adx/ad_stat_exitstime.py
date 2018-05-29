#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import datetime
from ad_base import AdBase
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis

LIMIT_COUNTS = configAdx.LIMIT_COUNTS

BIT_LENGTH = 1


# 用户退出事件的统计
class AdStatExitsTime(AdBase):
    kpis = []
    dims = []
    dataLength = 0
    hours = {}
    apps = None
    channels = None

    def runInit(self):
        self.kpis = configAdx.KPIS_EXITHOUR
        self.dims = configAdx.DIMS_HOUR

        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.midConfig = configDb.MYSQL_BH_AD_MID

        self.dbData = self.initDb(self.dataConfig)
        self.dbMid = self.initDb(self.midConfig)

        self.hourTable = configAdx.TABLE_REPORT_HOUR
        self.midTable = configAdx.TABLE_MID_USERS

        self.midFields = configAdx.FIELDS[self.midTable]

    # 获取最后统计表的时间
    def getLastRecordTime(self):
        sql = "select action_date,action_hour from %s order by action_date desc,action_hour desc limit 1" % (self.midTable)
        result = self.dbMid.fetchone(sql)
        if result is not None:
            return int(time.mktime(datetime.datetime.strptime(str(result['action_date']) + " " + str(result['action_hour']), "%Y-%m-%d %H").timetuple()))
        else:
            return 0

    def checkCondition(self, startPosition):
        today = int(time.mktime(time.strptime(time.strftime('%Y-%m-%d %H', time.localtime()), "%Y-%m-%d %H")))
        # today = int(time.mktime(datetime.date.today().timetuple()))
        lastTime = self.getLastRecordTime()
        if startPosition < today and startPosition < lastTime:
            return True
        else:
            if lastTime > 0:
                self.info("lastTime:" + str(time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(lastTime))))
            else:
                self.info("lastTime:" + str(lastTime))

            return False

    def getDaysData(self, days, hours):
        where = "action_date = '%s' and action_hour = %s and event_type = 18 and event_time >= 0" % (days, hours)
        fileds = "app_key,ad_type,action_date as days,event_time,sdk_version,action_hour as hours,gid as channel_gid,platform,is_custom,pos_key,ad_type"
        sql = "SELECT %s FROM %s WHERE %s" % (fileds, self.midTable, where)
        return self.dbMid.fetchall(sql)

    def paramData(self, recordData):
        result = {}
        if len(recordData) < 1:
            self.info('parseRecord len < 1')
            return result
        try:
            for record in recordData:
                # app_key, ads_id, ssp_id, pos_key, ad_type, channel_gid, platform, app_version, sdk_version, days, hours
                adType = int(record.get('ad_type'))
                days = record.get("days")
                hours = record.get("hours")
                sdkVersion = record.get("sdk_version")
                eventTime = record.get("event_time")
                channelGid = record.get("channel_gid")
                platform = record.get("platform")
                appVersion = record.get("app_version")
                isCustom = record.get("is_custom")
                posKey = record.get("pos_key")
                sspId = 1
                adsId = -1
                appKey = str(record.get('app_key'))
                # 添加广告位维度
                key = appKey + str(adType) + str(days) + str(hours) + str(sdkVersion) + str(channelGid) + str(posKey) + str(adsId) + str(appVersion) + str(platform) + str(sspId)
                if key not in result:
                    result[key] = {
                        "ssp_id": sspId,
                        "ads_id": adsId,
                        "app_key": appKey,
                        "pos_key": posKey,
                        "channel_gid": channelGid,
                        "is_custom": isCustom,
                        "platform": platform,
                        "ad_type": adType,
                        "sdk_version": sdkVersion,
                        "app_version": appVersion,
                        "days": days,
                        "hours": hours,
                        "effective_exits": 0,
                        "exits": 0,
                        "exit_stay_time": 0,
                    }
                if eventTime >= 3000 and eventTime < 14400000:
                    result[key]['effective_exits'] += 1
                    result[key]['exit_stay_time'] += eventTime
                result[key]['exits'] += 1
        except Exception, e:
            raise Exception("paramData:" + str(e))
        return result

    def saveData(self, data):
        if len(data) < 1:
            self.info('saveData len(data) <1')
            return False
        try:
            result = []
            for item in data.values():
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
                updateArr.append(kpi + "=%s")
            sql = "insert into %s (%s) values (%s) on duplicate key update %s;" % (self.hourTable, ",".join(fields), ("%s," * len(fields))[:-1], ",".join(updateArr))
            self.info('saveData')
            self.dbData.executeMany(sql, result)
            return True
        except Exception, e:
            raise Exception("saveData error :" + str(e))

    def run(self):
        try:
            self.runInit()
            startTimeStamp = time.time()
            startPosition, status = self.getStartPosition()

            # 判断状态
            if status != 1:
                self.info("status is stop")
                return False
            # 判断是否有新数据
            if self.checkCondition(startPosition) is True:
                # self.info("Condition does not meet")
                # return False
                self.nextPosition = startPosition + 3600
            else:
                # self.info("Stat today")
                # self.nextPosition = startPosition
                return False
                # 解析保存数据
            days = datetime.datetime.fromtimestamp(startPosition).date()
            hours = datetime.datetime.fromtimestamp(startPosition).hour

            self.info('StartTime:' + str(time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(startPosition))))
            dayData, count = self.getDaysData(days, hours)
            paramData = self.paramData(dayData)
            if self.saveData(paramData):
                self.updatePosition()
                return self.nextPosition > startPosition
            else:
                self.error('saveData fail')
                return False
            self.info("use time: " + str(time.time() - startTimeStamp))

        except Exception, e:
            self.error("run error:" + str(e))


if __name__ == '__main__':
    sleepCount = 0
    max_count = float(LIMIT_COUNTS)
    while 1:
        obj = AdStatExitsTime('ad_stat_exittime')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        if obj.run() is not True:
            quit()
            # obj.info("zzz:" + str(obj.dataLength))
            # obj = None
            # time.sleep(configAdx.SLEEP_SECOND)
            # continue
