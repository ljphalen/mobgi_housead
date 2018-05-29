#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import gc
import housead_config as Config
from housead_base import HouseadBase

statConfig = Config.MYSQL_MOBGI_HOUSEAD_STAT
dataConfig = Config.MYSQL_MOBGI_HOUSEAD_DATA
infoBhConfig = Config.MYSQL_BH_HOUSEAD


##把聚合数据导入inforbright
class DataToInforbright(HouseadBase):
    scriptName = 'housead_original'
    fields = Config.FIELDS_ORIGINAL
    table = Config.DB_TABLES_ORIGINAL
    bhTable = Config.BH_TABLES_ORIGINAL


if __name__ == '__main__':
    gcCount=0
    sleepCount = 0
    recordCount = Config.LIMIT_COUNTS
    minCount = Config.LIMIT_COUNTS
    while 1:
        gcCount += 1
        print gcCount
        if gcCount>1000 :
            gcCount=0
            gc.collect()
        obj = DataToInforbright()
        if recordCount < minCount and sleepCount < 3:
            time.sleep(Config.SLEEPSECOND)
            sleepCount += 1
            continue
        if obj.init is False:
            continue
        print "run"
        obj.run()
        recordCount = obj.recordCount
        sleepCount = 0
