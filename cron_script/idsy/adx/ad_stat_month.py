#!/usr/bin/python
# -*- coding:utf-8 -*-
import os
import time
import datetime
import calendar
from ad_base import AdBase
import config.adx as configAdx
import config.db as configDb
import traceback
import decimal

DEAFALT_CYCLE_TIME = 300  # 循环周期300s(5分钟)


# 月数据汇总
class AdStatMonth(AdBase):
    def init(self):
        self.dbData = self.initDb(configDb.MYSQL_MOBGI_DATA)
        self.dims = configAdx.DIMS_MONTH
        self.kpis = configAdx.KPIS_MONTH

    # 获取需要统计的天
    def getChangeDays(self, updateTime):
        try:
            sql = """SELECT DISTINCT app_key,MONTH(days) as month,YEAR(days) as year FROM report_finance where days>='2017-09-01' and update_time>=
            "%s" ORDER BY days;""" % (str(updateTime))
            return self.dbData.fetchall(sql)
        except Exception, e:
            raise Exception("getChangeDays fail :" + str(e))

    # 获得app_key和app_type的map
    def getAppTypeMap(self):
        self.appTypeMap = {}
        try:
            sql = "select app_key,app_type from config_app";
            result, count = self.dbData.fetchall(sql)
            for item in result:
                self.appTypeMap[item['app_key']] = item['app_type']
        except Exception, e:
            raise Exception("getAppTypeMap fail :" + str(e))

    # 重排统计数据
    def parseStatData(self, listData):
        result = []
        if len(listData) < 1:
            self.info('parseStatData len(listData) < 1')
            return result
        try:
            values = []
            for field in self.dims:
                if field == 'app_type':
                    if listData['app_key'] in self.appTypeMap:
                        values.append(self.appTypeMap[listData['app_key']])
                    else:
                        values.append(0)
                else:
                    values.append(listData[field])
            for field in self.kpis:
                values.append(listData[field])
            for field in self.kpis:
                values.append(listData[field])
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
            sql = "insert into `report_month` (%s) values (%s) on duplicate key update %s;" % (
                ",".join(fields), ("%s," * len(fields))[:-1], ",".join(kpifields))
            self.dbData.executeMany(sql,data)
            return True
        except Exception, e:
            raise Exception("insertDayStat:" + str(e))

    def getStatData(self, appKey, month, year):
        try:
            startDay, endDay = self.getMonthFirstDayAndLastDay(year, month)
            baseKpifields = ['app_key', 'MONTH(days) as month', 'YEAR(days) as years', 'sum(ad_income)*6.5 as ad_income']
            baseSql = "SELECT %s FROM report_finance WHERE app_key ='%s' and days >='%s' and days <='%s';" % (
                ",".join(baseKpifields), appKey, startDay, endDay)
            baseInfo = self.dbData.fetchone(baseSql)
            dauKpifields = ['sum(user_dau) as mau','sum(game_dau) as game_dau']
            if self.getAppPlatform(appKey) == 1:#根据平台来区分活跃
                dauSql = "SELECT %s FROM report_dau WHERE app_key ='%s' and ad_type = 0 and days >='%s' and days <='%s';" % (
                ",".join(dauKpifields), appKey,startDay,endDay)
            else:#IOS没有定制渠道数据
                dauSql = "SELECT %s FROM report_dau WHERE app_key ='%s' and is_custom = 0 and ad_type = 0 and channel_gid = 0 and days >='%s' and days <='%s';" % (
                    ",".join(dauKpifields), appKey, startDay, endDay)
            dauInfo = self.dbData.fetchone(dauSql)
            if dauInfo['mau'] != 0 and dauInfo['mau'] is not None:
                baseInfo['mau'] = int(dauInfo['mau'])
                baseInfo['arpu'] = decimal.Decimal(round(baseInfo['ad_income']/dauInfo['mau'],3))
            else:
                baseInfo['mau'] = 0
                baseInfo['arpu'] = 0
            if dauInfo['game_dau'] != 0 and dauInfo['game_dau'] is not None:
                baseInfo['game_cover'] = decimal.Decimal(round(dauInfo['mau']/dauInfo['game_dau'],3))
            else:
                baseInfo['game_cover'] = 0
            if self.appTypeMap.has_key(baseInfo['app_key']):
                baseInfo['app_type'] = self.appTypeMap[baseInfo['app_key']]
            else:
                baseInfo['app_type'] = 0
            return baseInfo,len(baseInfo)
        except Exception, e:
            traceback.print_exc()
            raise Exception("getUpdateDay fail :" + str(e))

    # 更新昨天数据
    def updateYesterdayStat(self, requestDate):
        requestDayTimestamp = int(time.mktime(time.strptime(requestDate, "%Y-%m-%d")))
        if self.startPosition - requestDayTimestamp < DEAFALT_CYCLE_TIME:
            yesterday = time.strftime("%Y-%m-%d", time.localtime(requestDayTimestamp - 24 * 60 * 60))
            self.insertDayStat(yesterday)

    def getMonthFirstDayAndLastDay(self, year, month):
        if year:
            year = int(year)
        else:
            year = datetime.date.today().year

        if month:
            month = int(month)
        else:
            month = datetime.date.today().month

        # 获取当月第一天的星期和当月的总天数
        firstDayWeekDay, monthRange = calendar.monthrange(year, month)

        # 获取当月的第一天
        firstDay = datetime.date(year=year, month=month, day=1)
        lastDay = datetime.date(year=year, month=month, day=monthRange)
        return firstDay, lastDay

    def run(self):
        try:
            self.init()
            self.startPosition, status = self.getStartPosition()
            self.nextPosition = time.time()
            self.getAppTypeMap()
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
                    statData, count = self.getStatData(item["app_key"], item["month"], item['year'])
                    if count > 0:
                        self.insertDayStat(statData)
                self.updatePosition()
                self.info("use time :" + str(time.time() - self.startTimestamp))
        except Exception, e:
            traceback.print_exc()
            self.error("run:" + str(e))


if __name__ == '__main__':
    obj = AdStatMonth("ad_stat_month")
    obj.run()
