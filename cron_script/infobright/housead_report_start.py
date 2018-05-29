#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import gc
import housead_config as Config
from housead_base import HouseadBase
from CustomCursors import CustomCursors


statConfig = Config.MYSQL_MOBGI_HOUSEAD_STAT
dataConfig = Config.MYSQL_MOBGI_HOUSEAD_DATA
infoBhConfig = Config.MYSQL_BH_HOUSEAD


##把聚合数据导入inforbright
class HouseadReport(HouseadBase):
    scriptName = 'house_infobright_stat'  # 脚本名,记录位置
    fields = Config.FIELDS_FOR_REPORT  # infobright表字段,用于获取统计数据
    bhTable = Config.BH_TABLES_ORIGINAL  # infobright统计表名

    # 实现过程的函数
    def parseReortData(self, startPosition):
        try:
            recordList = self.getInfoBrightRecordList(startPosition)
            parseData = self.parseRecordListData(recordList)
            return self.insertReportData(parseData)
        except Exception, e:
            self.errlog("parseReortData fail error:" + str(e))

    def parseRecordListData(self, recordData):
        result = {}
        if len(recordData) < 1:
            self.infolog('parseRecord len(recordData) < 1')
            return result

        # 下一次的位置
        positon = len(recordData)
        self.DATA_LEN = positon
        self.lastPostion = int(recordData[positon - 1]["id"])
        self.infolog('the last record id = ' + str(self.lastPostion))

        # configEventType = Config.EVENT_TYPE.keys()
        eventTypes = Config.EVENT_TYPE
        try:
            for record in recordData:
                # if record.get('originality_id') is None or record.get('app_key') is None or record.get('event_type') is None:
                #     continue
                # if record.get('event_type') not in eventType:
                #     print 'event_type out range:'
                # if record.get('os') != '0' and record.get('os') != '1':
                #     continue

                if eventTypes.has_key(record.get('event_type')):
                    server_time = time.strftime('%Y-%m-%d_%H', time.localtime(record.get("created_time")))
                    # 添加广告位维度
                    key = str(server_time) + str(record.get('originality_id')) + str(record.get('block_id')) + str(record.get('app_key')) + str(record.get('ad_type')) + str(record.get('platform'))
                    etype = eventTypes[int(record.get('event_type'))]
                    # 按事件维度初始化数据
                    # ['originality_id', 'block_id', 'app_key', 'ad_type', 'platform', 'date', 'hour']
                    if result.has_key(key) is False:
                        result[key] = {
                            "originality_id": record.get('originality_id'),
                            "block_id": record.get('block_id'),
                            "app_key": record.get('app_key'),
                            "ad_type": record.get('ad_type'),
                            "event_type": record.get('event_type'),
                            "platform": record.get('platform'),
                            "block_id": record.get('block_id'),
                            "date": server_time[0:10],
                            "hour": server_time[-2:],
                            "amount": 0
                        }
                        for eventType in eventTypes.itervalues():
                            result[key][eventType] = 0
                    result[key][etype] += 1
                    result[key]["amount"] += float(record.get('price'))
        except Exception, e:
            self.errlog("parseRecordListData error error:" + str(e))
        return result

    def insertReportData(self, listData):
        # dim+kpi
        dimFields = Config.FIELDS_REPORT
        kpiFields = Config.EVENT_TYPE.values()
        kpiFields.append('amount')
        moreFields = Config.FIELDS_REPORT_MORE
        result = []
        if len(listData) < 1:
            self.infolog('formatInsertData len(listData) < 1')
            return result
        try:
            for item in listData.values():
                values = []
                origInfo = self.getOrigInfo(item['originality_id'])
                if origInfo is None:
                    self.infolog("Cannot get origInfo with id:" + str(item['originality_id']))
                    origInfo = {
                        "ad_id": 0,
                        "unit_id": 0,
                        "originality_type": 0,
                        "account_id": 0
                        }
                for field in moreFields:
                    values.append(str(origInfo[field]))
                for field in dimFields:
                    values.append(str(item[field]))
                for field in kpiFields:
                    values.append(str(item[field]))
                # 用于跟新操作
                for field in kpiFields:
                    values.append(str(item[field]))
                result.append(tuple(values))
            return result
        except Exception, e:
            self.errlog("ERR listData:" + str(listData))
            self.errlog("formatInsertData data  error:" + str(e))

    def updateHouseadReport(self, data):
        if len(data) < 1:
            self.infolog('len(data) <1')
            return False
        try:
            kpiFields = Config.EVENT_TYPE.values()
            kpiFields.append('amount')
            fields = Config.FIELDS_REPORT_MORE + Config.FIELDS_REPORT + kpiFields
            updateArr = []
            for kpi in kpiFields:
                updateArr.append(kpi + "=" + kpi + "+%s")
            mysqlCursor = self.statDb.cursor(CustomCursors)
            sql = "insert into `report_base` (%s) values (%s) on duplicate key update %s;" % (",".join(fields), ("%s," * len(fields))[:-1], ",".join(updateArr))
            self.infolog('insert sql=' + sql)
            mysqlCursor.executemany(sql, data)
            self.statDb.commit()
            return True
        except Exception, e:
            self.errlog("insert data error :" + str(e))

    # infobright数据汇总统计
    def run(self):
        print "running..."
        try:
            startTimeStamp = time.time()
            # 取开始ID
            startPosition = self.getStartPosition()
            # 判断是否有新数据
            if self.checkInfoBrightCondition(startPosition) is not True:
                self.infolog("checkCondition failed -- no data")
                self.recordCount = 0
                return False

            # 解析保存数据
            reportData = self.parseReortData(startPosition)
            if self.updateHouseadReport(reportData) is True:
                self.updatePosition(self.lastPostion)
                self.infolog("lastPositon:" + str(self.lastPostion) + "  query use time : " + str(time.time() - startTimeStamp))

        except Exception, e:
            self.errlog("run error:" + str(e))
        finally:
            self.statDb.close()
            self.dataDb.close()
            self.bhDb.close()


if __name__ == '__main__':
    gcCount = 0
    sleepCount = 0
    recordCount = Config.LIMIT_COUNTS
    minCount = Config.LIMIT_COUNTS
    while 1:
        gcCount += 1
        if gcCount>1000 :
            gcCount=0
            gc.collect()
        obj = HouseadReport()
        if recordCount < minCount and sleepCount < 3:
            time.sleep(Config.SLEEPSECOND)
            sleepCount += 1
            continue
        if obj.init is False:
            continue
        obj.run()
        recordCount = obj.recordCount
        sleepCount = 0
