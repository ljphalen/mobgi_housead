#!/usr/bin/env python
# -*- coding:utf-8 -*-
import json
from tool_base import ToolBase
import config.db as configDb
import config.redis as configRedis


class toolWeightLog(ToolBase):
    limit_count = 500  # configAdx.LIMIT_COUNTS #每次提取的数据量
    report_weight_table = "report_weight_log"

    # 初始化redis和数据库
    def init(self):
        self.rkey = "RQ:intergration_position_list"
        self.redisWeightData = self.initRedis(configRedis.REDIS_QUEUE_ADX)
        self.dbMobgiData = self.initDb(configDb.MYSQL_MOBGI_DATA)

    # 检测redis是否存在数据
    def checkCondition(self):
        queueLen = self.redisWeightData.llen(self.rkey)
        if queueLen > 0:
            return True
        else:
            return False

    # 取出redis数据
    def getRecordList(self):
        queueListData = []
        for i in range(self.limit_count):
            queueData = self.redisWeightData.lpop(self.rkey)
            queueData = json.loads(str(queueData))
            if queueData is None or isinstance(queueData, dict) is False:
                continue
            queueListData.append(queueData)
            itemlen = self.redisWeightData.llen(str(self.rkey))
            if itemlen == 0:
                break
        return queueListData

    # 写入新表数据
    def addToTable(self, items):
        if len(items) == 0:
            return False
        data = []
        for item in items:
            data.append([item['appKey'], item['intergrationType'], item['effectTime'], item['adsPositonList']])
        sql = "insert into " + self.report_weight_table + "(app_key,ad_type,effect_time,ads_positon_list) values (%s,%s,%s,%s)"
        self.dbMobgiData.executeMany(sql, data)
        return True

    def run(self):
        try:
            if self.checkCondition() is not True:
                self.dataCount = 0
                self.info("No data")
                return False
            # 解析保存数据
            recordList = self.getRecordList()
            if self.addToTable(recordList) is not True:
                self.dataCount = 0
                self.info("save Data error!")
                return False
            else:
                return True
        except Exception, e:
            self.error("run error:" + str(e))
            return False


if __name__ == '__main__':
    while 1:
        obj = toolWeightLog('tool_weight_log')
        if obj.run() is False:
            quit()
