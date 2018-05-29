#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import datetime

sys.path.append("..")
from tool_base import ToolBase
import config.adx as configAdx
import config.db as configDb
from lib.myini import myini

INI_KEY = "clear_double"
fo = myini('tool_clear_double.ini')


# 删除重复上报
class toolClearDouble(ToolBase):
    def init(self):
        self.client_table = configAdx.TABLE_STAT_CLIENT;
        self.ib = self.initDb(configDb.MYSQL_BH_AD_STAT)

    # 取出旧表数据
    def clearData(self, position):
        try:
            startTimeStamp = time.time()
            start_position = position
            end_position = position + 1
            sql_str = """delete a from %s a inner join (select min(id) id,app_key,uuid,client_ip,server_time,event_type,count(1) as count from %s where
            platform=2 and ver=1 and server_time=%s group by app_key,uuid,client_ip,server_time,event_type having count(*) > 1) b on
            a.app_key = b.app_key and a.uuid = b.uuid and a.event_type = b.event_type and a.platform=2 and a.ver=1 and a.server_time = b.server_time and a.server_time=%s and a.id
            > b.id;""" % (self.client_table, self.client_table, start_position, start_position)
            result = self.ib.execute(sql_str)
            fo.set(INI_KEY, 'start_position', end_position)
            used_time = time.time() - startTimeStamp
            self.info("use time : " + str(used_time) + " clear:" + str(result) + " position:" + str(datetime.datetime.fromtimestamp(position)))
            return True
        except Exception, e:
            self.info("###:" + str(sql_str))
            self.error("run error:" + str(e))
            return False


if __name__ == '__main__':
    last_position = fo.get(INI_KEY, 'last_position')
    sleepCount = 0
    while 1:
        start_position = fo.get(INI_KEY, 'start_position')
        obj = toolClearDouble('tool_clear_double')

        if sleepCount > 10:  # 错误过多自动退出
            obj.error("too many error to quit")
            break

        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            sleepCount += 1

        if last_position <= start_position:
            obj.error("to the last position")
            break

        if obj.clearData(int(start_position)) is not True:
            obj.error("zzz:5")
            break
