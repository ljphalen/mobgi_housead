#!/usr/bin/env python
# -*- coding:utf-8 -*-

import os
import sys
import time
sys.path.append("..")
from tool_base import ToolBase
import config.adx as configAdx
import config.db as configDb



class toolCheckAdClient(ToolBase):
    dataCount = 0

    def init(self):
        self.ib = self.initDb(configDb.MYSQL_BH_AD_STAT)
        self.statTable = configAdx.TABLE_STAT_CLIENT

    # 检查infobright是否有新数据
    def getLastStatTime(self):
        sql = """SELECT server_time FROM %s order by id desc LIMIT 1""" % (self.statTable)
        result = self.ib.fetchone(sql)
        # 判断记录是否存在
        return result['server_time']

    def run(self):
        try:
            # 判断是否有新数据
            lastTime = self.getLastStatTime()
            # 解析保存数据
            self.info("lastTime:" + time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(lastTime)))

            if lastTime < time.time() - 3600:
                self.error("stat delay more then 3600")
            elif lastTime < time.time() - 1800:
                self.error("stat delay more then 1800")

        except Exception, e:
            self.error("run error:" + str(e))


if __name__ == '__main__':
    obj = toolCheckAdClient('tool_check_ad_client')
    result = obj.run()
