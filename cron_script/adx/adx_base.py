#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import redis
import config.adx as configAdx
import config.db as configDb
import config.warning as configWarning
import lib.mysql as db
import lib.mylog as mylog
import hashlib

default_encoding = 'utf-8'
if sys.getdefaultencoding() != default_encoding:
    reload(sys)
    sys.setdefaultencoding(default_encoding)


class AdxBase(object):
    # 错误标记
    flag = False
    #是否有sqlfile
    sqlFlag = False
    start_timestamp = 0
    scriptName = None
    log = None
    startPostion = 0
    lastPostion = 0
    positionTable = configAdx.TABLE_POSITION

    def __init__(self, name):
        self.scriptName = name
        self.start_timestamp = time.time()
        self.logPath = os.path.join(sys.path[0], configAdx.LOG_PATH)
        self.log = mylog.mylog(os.path.join(sys.path[0], configAdx.LOG_PATH, name + '.log'))
        self.checkSqlFilePath()
        self.initDbConfPos()
        self.init()

    def initDbConfPos(self):
        self.dbConfPos = self.initDb(configDb.MYSQL_MOBGI_DATA)

    def init(self):
        pass

    def checkSqlFilePath(self, path='sql'):
        if self.sqlFlag is True:
            path = os.path.join(sys.path[0], path, self.scriptName)
            if os.path.isdir(path) is False:
                os.mkdir(path)

    def getLogPath(self, log_path=configAdx.LOG_PATH):
        return os.path.join(sys.path[0], log_path)

    def initRedis(self, conf):
        try:
            poolr = redis.ConnectionPool(host=conf["host"], port=conf["port"])
            return redis.Redis(connection_pool=poolr)
        except Exception, e:
            self.error("redis has gone away")
            return False

    def initDb(self, conf):
        try:
            return db.mysql(conf)
        except Exception, e:
            self.error("db connect failed:" + str(conf))
            return None

    def info(self, msg):
        self.log.info(msg)

    def error(self, msg):
        self.flag = True
        self.log.error(msg)
        self.warning(msg)
        time.sleep(1)

    def warning(self, msg):
        try:
            from lib.myini import myini
            from lib.mywarning import mywarning
            mytime = time.localtime()
            md5key = hashlib.md5(time.strftime("%Y-%m-%d %H:%M", mytime) + msg).hexdigest()
            fo = myini(os.path.join(self.logPath, 'error.log'))
            mykey = fo.get(self.scriptName, 'key')
            if mykey != md5key:
                import socket
                fo.set(self.scriptName, 'key', md5key)
                mywarn = mywarning(configDb.MYSQL_MOBGI_DATA, configWarning)
                mywarn.record(socket.getfqdn(socket.gethostname()) + ":" + self.scriptName, msg, time.mktime(mytime))
            else:
                self.log.info("skip warning")
        except Exception, e:
            self.log.info("###" + str(e))

    def getStartPosition(self):
        if self.scriptName is None:
            self.error("scriptName cannot be None")
            quit()
        sql = "select time as start_position,status from %s where name = '%s';" % (self.positionTable, self.scriptName)
        result = self.dbConfPos.fetchone(sql)
        if result is None:
            return 0, 0
        else:
            self.startPosition = result.get('start_position')
            return result.get('start_position'), result.get('status')

    def updatePosition(self):
        try:
            if time.time() - self.start_timestamp > 59:
                self.initDbConfPos()
            if self.lastPostion <= self.startPosition:
                return False
            sql = "update %s set time=%s where name ='%s';" % (self.positionTable, self.lastPostion, self.scriptName)
            self.info("lastPostion:" + str(self.lastPostion))
            return self.dbConfPos.execute(sql)
        except Exception, e:
            raise Exception('update Position fail:' + str(e))
            return False

    def exchangeStrDate(self, strTime, formFormat, toFormat):
        start_timeArray = time.strptime(strTime, formFormat)
        start_timestamp = int(time.mktime(start_timeArray))
        return time.strftime(toFormat, time.localtime(start_timestamp))
