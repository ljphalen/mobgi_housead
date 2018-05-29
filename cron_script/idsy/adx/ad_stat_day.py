#!/usr/bin/python
# -*- coding:utf-8 -*-
import os
import time
from ad_base import AdBase
import config.adx as configAdx
import config.db as configDb

DEAFALT_CYCLE_TIME = 300  # 循环周期300s(5分钟)


# 天数据汇总
class AdStatDay(AdBase):
    def init(self):
        self.dbData = self.initDb(configDb.MYSQL_MOBGI_DATA)
        self.dims = configAdx.DIMS_DAY
        self.kpis = configAdx.KPIS_DAY

    # 获取需要统计的天
    def getChangeDays(self, updateTime):
        try:
            sql = """SELECT DISTINCT app_key,days FROM report_hour where update_time>= "%s" ORDER BY days;""" % (str(updateTime))
            return self.dbData.fetchall(sql)
        except Exception, e:
            raise Exception("getChangeDays fail :" + str(e))

    # 重排统计数据
    def parseStatData(self, listData):
        result = []
        if len(listData) < 1:
            self.info('parseStatData len(listData) < 1')
            return result
        try:
            for item in listData:
                values = []
                for field in self.dims:
                    values.append(str(item[field]))
                for field in self.kpis:
                    values.append(str(item[field]))
                for field in self.kpis:
                    values.append(str(item[field]))
                result.append(tuple(values))
            return result
        except Exception, e:
            raise Exception("parseStatData error :" + str(e))

    # 按天更新统计数据
    def insertDayStat(self, data):
        if len(data) < 1:
            self.info('len(data) <1')
            return False
        try:
            data = self.parseStatData(data)
            fields = self.dims + self.kpis
            kpifields = []
            for kpi in self.kpis:
                kpifields.append(kpi + "=%s")

            sql = "insert into `report_day` (%s) values (%s) on duplicate key update %s;" % (
                ",".join(fields), ("%s," * len(fields))[:-1], ",".join(kpifields))
            self.dbData.executeMany(sql, data)
            return True
        except Exception, e:
            raise Exception("insertDayStat:" + str(e))

    def getStatData(self, appKey, day):
        try:
            kpifields = []
            for kpi in self.kpis:
                kpifields.append("sum(" + kpi + ") AS " + kpi)
            sql = "SELECT %s,%s FROM report_hour WHERE app_key ='%s' and days ='%s' GROUP BY %s;" % (
                ",".join(self.dims), ",".join(kpifields), appKey, day, ",".join(self.dims))
            return self.dbData.fetchall(sql)
        except Exception, e:
            raise Exception("getUpdateDay fail :" + str(e))

    # 更新昨天数据
    def updateYesterdayStat(self, requestDate):
        requestDayTimestamp = int(time.mktime(time.strptime(requestDate, "%Y-%m-%d")))
        if self.startPosition - requestDayTimestamp < DEAFALT_CYCLE_TIME:
            yesterday = time.strftime("%Y-%m-%d", time.localtime(requestDayTimestamp - 24 * 60 * 60))
            self.insertDayStat(yesterday)

    def run(self):
        try:
            self.init()
            self.startPosition, status = self.getStartPosition()
            self.nextPosition = time.time()
            # 判断状态
            if status != 1:
                self.dataLength = 0
                self.info("status is stop")
                return False
            requestTime = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(int(self.startPosition)))
            list, count = self.getChangeDays(requestTime)
            # 判断是否有新数据
            if count == 0:
                self.info("Not change")
                return False
            else:
                for item in list:
                    statData, count = self.getStatData(item["app_key"], item["days"])
                    if count > 0:
                        self.insertDayStat(statData)
                self.updatePosition()
                self.info("use time :" + str(time.time() - self.startTimestamp))
        except Exception, e:
            self.error("run:" + str(e))


if __name__ == '__main__':
    obj = AdStatDay("ad_stat_day")
    obj.run()
