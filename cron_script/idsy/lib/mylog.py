#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import logging


class mylog(object):
    def __init__(self, filename='default.log'):
        self.logPath = os.path.dirname(filename)
        logging.basicConfig(filename=filename, format='%(asctime)s %(levelname)s %(message)s', datefmt='%Y-%m-%d %H:%M:%S', level=logging.INFO)

    def info(self, msg):
        print time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()) + " " + str(msg)
        logging.info(msg)

    def error(self, msg):
        print time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()) + " " + str(msg)
        logging.error(msg)
