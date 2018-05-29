#!/usr/bin/env python
# -*- coding:utf-8 -*-
import sys
import time

sys.path.append("..")
from lib.snowflask import Snowflask
from ad_client import AdClient
import config.worker as worker
import config.adx as configAdx

if __name__ == '__main__':
    sleepCount = 0
    loadCount = 0
    creater = Snowflask(worker.WORK_ID + 3)
    while 1:
        loadCount += 1
        obj = AdClient('ad_client4')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        rlen = obj.getRlen()
        if (rlen < worker.WORK_LIMIT and sleepCount < 5) or rlen == 0:
            obj.info("zzz:" + str(rlen))
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            sleepCount = sleepCount + 1
            continue
        sleepCount = 0
        obj.run(creater)
        if loadCount > 5:
            loadCount = 0
            obj.loadFiles()

