#!/usr/bin/env python
# -*- coding:utf-8 -*-
import time
from ad_base import AdBase
import config.adx as configAdx
import config.db as configDb


# 统计渠道收益情况
class AdChannelIncome(AdBase):
    def runInit(self):
        self.dbDataConfig = configDb.MYSQL_MOBGI_DATA
        self.DayTable = 'report_day'  # 数据日报表，从这里统计数据
        self.ThirdDataTable = 'report_third_data'  # 数据总表，从这里根据日报表的比例提取收益
        self.dbData = self.initDb(self.dbDataConfig)

    # 检测是否有新数据需要统计
    def checkCondition(self):
        sql = "select update_time from %s where update_time > '%s' order by update_time desc limit 1" % (
            self.ThirdDataTable, self.dateTimeStartPosition)
        check = self.dbData.fetchone(sql)
        if check:
            # 记录最后本次更新最后更新时间,为下一个开始的节点
            self.nextPosition = int(time.mktime(check['update_time'].timetuple()))
            return True
        else:
            return False

    # 根据update_time获取需要更新的数据的日期
    def getUpdateData(self):
        sql = "select days from %s where update_time > '%s' and days >= '2017-09-01' group by days" % (
            self.ThirdDataTable, self.dateTimeStartPosition)
        return self.dbData.fetchall(sql)

    # 计算Day中的总数
    def calAmountDayData(self, days):
        allChannelDatasql = "select sum(impressions) as impressionsCount,app_key,ad_type,ads_id from %s where impressions>0 and is_custom = 0 and " \
                            "days = '%s' group by app_key,ad_type,ads_id" % (self.DayTable, str(days))
        # 算出某日所有渠道的总展示
        allData = self.dbData.fetchall(allChannelDatasql)
        amount = {}
        for item in allData[0]:
            key = item['ads_id'] + str(item['ad_type']) + item['app_key']
            amount[key] = {
                'impressionsCount': item['impressionsCount'],
            }
        return amount

    # 算出某日的每个渠道impressions的数据和总的数据
    def getAllChannelImpressionList(self, days):
        perChannelDatasql = "select sum(impressions) as impressionsCount,days,channel_gid,ads_id,ad_type,app_key,is_custom from %s where " \
                            "impressions>0 and is_custom = 0 and days = '%s' group by channel_gid,ads_id,ad_type,app_key" % (self.DayTable, str(days))
        perData, item = self.dbData.fetchall(perChannelDatasql)
        if perData:  # 存在数据算出比例
            return perData
        else:
            self.info('NO DATA IN TABLE report_day days=' + str(days))
            return False

    # 获取每天分app_key,ad_type,ads_id下的总收入
    def getAmountFromThirdDay(self, days):
        dayAmount = {}
        data = self.getAmount(days)
        for item in data[0]:
            key = item['ads_id'] + str(item['ad_type']) + item['app_key']
            dayAmount[key] = {
                'third_views': item['third_views'],
                'ad_income': item['ad_income'],
                'third_clicks': item['third_clicks'],
            }
        return dayAmount

    # 处理逻辑
    def dealData(self, days):
        if len(days) is 0: return False
        for day in days[0]:
            amount = self.getAmountFromThirdDay(day['days'])  # 算出这一天的分app_key,ad_type,ads_id不分渠道的总收入，总点击，总展示
            allData = self.calAmountDayData(day['days'])  # 获取day表的汇总，分app_key,ad_type,ads_id不分渠道
            perChannelData = self.getAllChannelImpressionList(day['days'])  # 按照渠道汇总
            saveData = {}
            if perChannelData is not False:
                for item in perChannelData:
                    appKey = item['app_key']
                    is_custom = item['is_custom']
                    platform = int(self.getAppPlatform(appKey))
                    key = item['ads_id'] + str(item['ad_type']) + appKey
                    ratio = float(item['impressionsCount'] / allData[key]['impressionsCount'])
                    if amount.has_key(key):
                        saveData[key + str(item['channel_gid'])] = {
                            'app_key': item['app_key'],
                            'ad_type': item['ad_type'],
                            'ads_id': item['ads_id'],
                            'platform': platform,
                            'is_custom': is_custom,
                            'third_clicks': int(ratio * int(amount[key]['third_clicks'])),
                            'third_views': int(ratio * int(amount[key]['third_views'])),
                            'ad_income': float('%.2f' % (ratio * float(amount[key]['ad_income']))),
                            'channel_gid': item['channel_gid'],
                            'days': day['days']
                        }
                    else:
                        self.info('key not exists :' + key + "," + str(day['days']))
                formatData = self.formatData(saveData)
                # 存储数据到channel表
                if self.saveChannelData(formatData) is False:
                    return False
            else:
                return False
        return True

    # 计算总的结果
    def getAmount(self, day):
        sql = "select sum(third_clicks) as third_clicks,sum(third_views) as third_views,sum(ad_income) as ad_income,app_key,ad_type,ads_id " \
              "from %s where days = '%s' group by app_key,ad_type,ads_id " % (self.ThirdDataTable, str(day))
        return self.dbData.fetchall(sql)

    # 存储渠道数据,finance表写入
    def saveChannelData(self, data):
        sql = """insert into report_finance(channel_gid,ads_id,ad_type,app_key,platform,days,is_custom,third_views,ad_income,third_clicks)
        values (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)on duplicate key update ad_income=%s,third_views=%s,third_clicks=%s"""
        # self.info("data:" + str(data))
        return self.dbData.executeMany(sql, data)

    # 格式化数据
    def formatData(self, listData):
        if len(listData) < 1:
            return []
        result = []
        for item in listData:
            values = (listData[item]['channel_gid'], listData[item]["ads_id"], listData[item]["ad_type"], listData[item]["app_key"],
                      listData[item]["platform"], listData[item]["days"], listData[item]['is_custom'], listData[item]["third_views"],
                      listData[item]["ad_income"], listData[item]["third_clicks"], listData[item]["ad_income"], listData[item]["third_views"],
                      listData[item]["third_clicks"])
            result.append(values)
        return tuple(result)

    # 更新完毕记录更新节点
    def updatePosition(self):
        try:
            if self.nextPosition <= self.startPosition:
                return False
            sql = "update %s set position=%s where script_name ='%s';" % (self.positionTable, self.nextPosition, self.scriptName)
            self.info("nextPosition:" + str(self.nextPosition))
            return self.dbPosition.execute(sql)
        except Exception, e:
            raise Exception('updatePosition fail:' + str(e))

    def run(self):
        try:
            startTimeStamp = time.time()
            self.runInit()
            self.startPosition, status = self.getStartPosition()
            self.dateTimeStartPosition = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(self.startPosition))
            self.dateStartPosition = time.strftime('%Y-%m-%d', time.localtime(self.startPosition))
            if status != 1:
                self.dataLength = 0
                self.info("status is stop")
                return False
            # 判断是否有新数据
            if self.checkCondition() is not True:
                self.info("THE START POSITION NO DATA position=" + str(self.dateTimeStartPosition))
                return False
            self.info("startPosition:" + str(self.dateStartPosition))
            days = self.getUpdateData()  # 获取需要更新的日期
            result = self.dealData(days)  # 处理数据，并输出格式

            if result is False:  # 如果没有数据不更新节点
                self.info("no recordData")
            else:  # 有数据更新完毕后更新节点
                self.updatePosition()
            self.info("use time : " + str(time.time() - startTimeStamp))
        except Exception, e:
            self.error("run error:" + str(e))


if __name__ == '__main__':
    obj = AdChannelIncome('ad_channel_income')
    if obj.errorFlag:
        obj = None
        time.sleep(configAdx.SLEEP_SECOND)
    obj.run()
