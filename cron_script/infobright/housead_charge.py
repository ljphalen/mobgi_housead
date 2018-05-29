#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import housead_config as Config
from housead_base import HouseadBase

statConfig = Config.MYSQL_MOBGI_HOUSEAD_STAT
dataConfig = Config.MYSQL_MOBGI_HOUSEAD_DATA
infoBhConfig = Config.MYSQL_BH_HOUSEAD

##把聚合数据导入inforbright
class DataToInforbright(HouseadBase):
    scriptName = 'housead_charge'
    fields = Config.FIELDS_CHAEGE
    table = Config.DB_TABLES_CHARGE
    bhTable = Config.BH_TABLES_CHARGE


if __name__ == '__main__':
    sleepCount = 0
    recordCount = Config.LIMIT_COUNTS
    minCount = Config.LIMIT_COUNTS
    while 1:
        obj = DataToInforbright()
        if recordCount < minCount and sleepCount < 3:
            print "sleeping..."
            time.sleep(Config.SLEEPSECOND)
            sleepCount += 1
            continue
        obj.run()
        recordCount = obj.recordCount
        sleepCount = 0
