#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import config.adx as configAdx
import config.db as configDb
import lib.mysql as db
from adx_date_to_infobright import AdxDataToInforbright

LIMIT_COUNTS = configAdx.LIMIT_COUNTS


class AdxServerToInforbright(AdxDataToInforbright):
    def init(self):
        self.srcTable = configAdx.TABLE_SERVER_STAT
        self.desTable = configAdx.IB_TABLE_SERVER_STAT
        self.srcFields = configAdx.IB_FIELDS_SERVER_STAT
        self.desFields = self.srcFields
        self.sqlPath = os.path.join(sys.path[0], configAdx.SQL_PATH, self.scriptName)
        self.srcDb = self.initDb(configDb.MYSQL_MOBGI_ADX_STAT)
        self.desDbConf = configDb.MYSQL_BH_ADX


if __name__ == '__main__':
    sleepCount = 0
    loadCount = 0
    max_count = float(LIMIT_COUNTS)
    while 1:
        obj = AdxServerToInforbright('adx_server_to_infobright')
        if obj.flag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        obj.run()
        count = obj.dataCount
        if count < max_count and sleepCount < 5:
            obj.info("zzz:" + str(count))
            obj = None
            sleepCount += 1
            time.sleep(configAdx.SLEEP_SECOND)
            continue

        sleepCount = 0
        if loadCount > 3:
            loadCount = 0
            obj.loadFiles()
        else:
            loadCount += 1
