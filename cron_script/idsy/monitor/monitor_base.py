#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time

sys.path.append("..")
from lib.task import Task


# 监控基类
class MonitorBase(Task):
    def exchangeStrDate(self, strTime, formFormat, toFormat):
        start_timeArray = time.strptime(strTime, formFormat)
        start_timestamp = int(time.mktime(start_timeArray))
        return time.strftime(toFormat, time.localtime(start_timestamp))

    def exchangeTimeStampDate(self, timeStamp, toFormat):
        time_local = time.localtime(timeStamp)
        return time.strftime(toFormat, time_local)

    def exchangeTimeStamp(self, date):
        timeArray = time.strptime(date, "%Y-%m-%d %H:%M:%S")
        return time.mktime(timeArray)
