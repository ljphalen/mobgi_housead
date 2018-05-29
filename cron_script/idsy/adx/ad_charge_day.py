#!/usr/bin/python
# -*- coding:utf-8 -*-
import os
import time
from ad_base import AdBase
import config.adx as configAdx
import config.db as configDb

DEAFALT_CYCLE_TIME = 300  # 循环周期300s(5分钟)


# 天数据汇总
# 分两次统计，一次统计当日所有的clicks和views，以及总的amount
class AdChargeDay(AdBase):
    def init(self):
        self.chargeConfig = configDb.MYSQL_MOBGI_CHARGE  # dbcharge
        self.dbCharge = self.initDb(self.chargeConfig)
        self.minChageTable = configAdx.TABLE_CHARGE_MIN  # 落地minute数据表
        self.dayChageTable = configAdx.TABLE_CHARGE_DAY  # 落地day数据表
        self.kpis = ['amount']
        self.totalKpis = ['clicks', 'views', 'actives', 'total_amount']
        self.dims = ['ads_id', 'originality_id', 'app_key', 'pos_key', 'days']
        self.fields = ['ad_unit_id', 'ad_id', 'ads_id', 'originality_id', 'app_key', 'pos_key', 'days', 'minutes', 'ad_type','is_mobgi']
        self.insertField = ['ad_unit_id', 'ad_id', 'ads_id', 'originality_id', 'app_key', 'pos_key', 'days', 'hours', 'ad_type','is_mobgi']

    # 获取需要统计的天
    def getChangeDays(self, updateTime):
        # 第一步統計
        try:
            sql = """SELECT * FROM %s where update_time>= "%s" group by days ORDER BY days;""" % (self.minChageTable, str(updateTime))
            return self.dbCharge.fetchall(sql)
        except Exception, e:
            raise Exception("getChangeDays fail :" + str(e))

    # 重排统计数据
    def parseStatData(self, listData, isCharged=None):
        result = []
        if len(listData) < 1:
            self.info('parseStatData len(listData) < 1')
            return result
        try:
            if isCharged is not None:
                for item in listData:
                    item['hours'] = str(item['minutes'])[:2]
                    values = []
                    for field in self.insertField:
                        values.append(str(item[field]))
                    for field in self.kpis:
                        values.append(str(item[field]))
                    for field in self.kpis:
                        values.append(str(item[field]))
                    result.append(tuple(values))
            else:
                for item in listData:
                    item['hours'] = str(item['minutes'])[:2]
                    values = []
                    for field in self.insertField:
                        values.append(str(item[field]))
                    for field in self.totalKpis:
                        values.append(str(item[field]))
                    for field in self.totalKpis:
                        values.append(str(item[field]))
                    result.append(tuple(values))
            return result
        except Exception, e:
            raise Exception("parseStatData error :" + str(e))

    # 按天更新统计数据
    def insertDayStat(self, data, isChraged=None):
        if len(data) < 1:
            self.info('len(data) <1')
            return False
        try:
            if isChraged is not None:  # 计算amount
                data = self.parseStatData(data, isChraged)
                fields = self.insertField + self.kpis
                kpifields = []
                for kpi in self.kpis:
                    kpifields.append(kpi + "=%s")
                sql = "insert into `%s` (%s) values (%s) on duplicate key update %s;" % (
                    self.dayChageTable, ",".join(fields), ("%s," * len(fields))[:-1], ",".join(kpifields))
            else:  # 计算total_amount
                data = self.parseStatData(data)
                fields = self.insertField + self.totalKpis
                kpifields = []
                for kpi in self.totalKpis:
                    kpifields.append(kpi + "=%s")
                sql = "insert into `%s` (%s) values (%s) on duplicate key update %s;" % (
                    self.dayChageTable, ",".join(fields), ("%s," * len(fields))[:-1], ",".join(kpifields))
            self.dbCharge.executeMany(sql, data)
            return True
        except Exception, e:
            raise Exception("insertDayStat:" + str(e))

    def getStatData(self, day, isCharged=None):
        try:
            kpifields = []
            if isCharged is not None:
                for kpi in self.kpis:  # 计算amount
                    kpifields.append("sum(" + kpi + ") AS " + kpi)
                sql = "SELECT %s,%s FROM %s WHERE days ='%s' and is_charged =%s GROUP BY %s;" % (
                    ",".join(self.fields), ",".join(kpifields), self.minChageTable, day, isCharged, ",".join(self.dims))
            else:  # 计算total_amount
                for kpi in self.totalKpis:  # 计算amount
                    kpifields.append("sum(" + kpi + ") AS " + kpi)
                sql = "SELECT %s,%s FROM %s WHERE days ='%s' GROUP BY %s;" % (
                    ",".join(self.fields), "sum(views) as views,sum(clicks) as clicks,sum(actives) as actives,sum(amount) as total_amount",
                    self.minChageTable, day, ",".join(self.dims))
            return self.dbCharge.fetchall(sql)
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
            requestDate = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(int(self.startPosition)))
            list, count = self.getChangeDays(requestDate)
            # 判断是否有新数据
            if count == 0:
                self.info("Not DATA!")
                return False
            else:
                for item in list:
                    self.info("stat day:" + str(item["days"]))
                    statData, count = self.getStatData(item["days"], 1)# 计算amount
                    if count > 0:  # 存入amount数据
                        self.insertDayStat(statData, 1)
                    total_Data, count = self.getStatData(item["days"])# 计算total_amount
                    if count > 0:
                        self.insertDayStat(total_Data)
                self.updatePosition()
                self.info("use time :" + str(time.time() - self.startTimestamp))
        except Exception, e:
            self.error("run:" + str(e))


if __name__ == '__main__':
    obj = AdChargeDay("ad_charge_day")
    obj.run()
