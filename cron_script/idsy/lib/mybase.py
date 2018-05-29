#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import redis
import hashlib
import codecs

sys.path.append("..")
import config.db as configDb
import config.warning as configWarning
import lib.mysql as db
import lib.mylog as mylog

default_encoding = 'utf-8'
if sys.getdefaultencoding() != default_encoding:
    reload(sys)
    sys.setdefaultencoding(default_encoding)


class Base(object):
    errorFlag = False  # 错误标记
    sqlFlag = False
    logPath = 'log'
    sqlPath = 'sql'
    startTimestamp = 0
    scriptName = None
    log = None

    positionTable = 'config_cron'
    dbPosition = None
    startPosition = 0
    nextPosition = 0
    lastPosition = 0
    redis_conn = {}
    db_conn = {}

    def __init__(self, name):
        try:
            self.scriptName = name
            self.startTimestamp = time.time()
            self.baseConfig = configDb.MYSQL_MOBGI_DATA
            self.log = mylog.mylog(self.getLogPath(self.scriptName))
            if os.path.exists(os.path.join(sys.path[0], 'stop')) or os.path.exists(os.path.join(sys.path[0], name + '.stop')):
                self.info('---stop---')
                quit()
            self.init()
        except Exception, e:
            raise Exception("_init_ fail:" + str(e))

    def __del__(self):
        try:
            for key, conn in self.db_conn.iteritems():
                if conn is not None:
                    conn.close()
                    self.db_conn[key] = None
        except Exception, e:
            self.error("__del__ error:" + str(e))

    def init(self):
        pass

    def md5(self, key):
        m2 = hashlib.md5()
        m2.update(key)
        return m2.hexdigest()

    def getLogPath(self, filename, path=None):
        if path == None:
            fullpath = os.path.join(sys.path[0], self.logPath)
        else:
            fullpath = os.path.join(sys.path[0], path)
        self.initPath(fullpath)
        return os.path.join(fullpath, filename + '.log')

    def getSqlPath(self, filename, path=None):
        if path == None:
            fullpath = os.path.join(sys.path[0], self.sqlPath, self.scriptName)
        elif path == '.':
            fullpath = os.path.join(sys.path[0], self.sqlPath)
        else:
            fullpath = os.path.join(sys.path[0], self.sqlPath, path)
        self.initPath(fullpath)
        return os.path.join(fullpath, filename + '.sql')

    def initPath(self, fullpath):
        if os.path.isdir(fullpath) is False:
            os.mkdir(fullpath)

    def initPosition(self):
        self.dbPosition = self.initDb(self.baseConfig)

    def initRedis(self, conf, num=0):
        try:
            conn_key = self.md5(str(conf) + str(num))
            if self.redis_conn.has_key(conn_key) is False:
                if conf.has_key("password"):
                    poolr = redis.ConnectionPool(host=conf["host"], port=conf["port"], password=conf["password"], db=num)
                else:
                    poolr = redis.ConnectionPool(host=conf["host"], port=conf["port"], db=num)
                self.redis_conn[conn_key] = redis.Redis(connection_pool=poolr)
            return self.redis_conn[conn_key]
        except Exception, e:
            raise Exception("redis has gone away:" + str(e))

    def initDb(self, conf):
        try:
            conn_key = self.md5(str(conf))
            if self.db_conn.has_key(conn_key) is False or self.db_conn[conn_key] == None:
                self.db_conn[conn_key] = db.mysql(conf)
            return self.db_conn[conn_key]
        except Exception, e:
            raise Exception("db connect failed:" + str(e))

    def info(self, msg):
        self.log.info(msg)

    def error(self, msg, param='', sleep=3):
        self.errorflag = True
        self.log.error(msg + str(param))
        self.warning(msg, param)
        time.sleep(sleep)

    def warning(self, msg, param):
        try:
            from lib.myini import myini
            from lib.mywarning import mywarning
            mytime = time.localtime()
            md5key = hashlib.md5(time.strftime("%Y-%m-%d %H:%M", mytime) + msg).hexdigest()
            fo = myini(self.getLogPath('error'))
            if fo.has(self.scriptName, 'key') is False:
                mykey = ''
                fo.set(self.scriptName, 'key', mykey)
            else:
                mykey = fo.get(self.scriptName, 'key')
            if mykey != md5key:
                import socket
                fo.set(self.scriptName, 'key', md5key)
                # msg += "(" + str(param) + ")"
                mywarn = mywarning(self.baseConfig, configWarning)
                mywarn.record(socket.getfqdn(socket.gethostname()) + ":" + self.scriptName, msg + str(param), time.mktime(mytime))
            else:
                self.log.info("skip warning")
        except Exception, e:
            raise Exception("warning:" + str(e))

    def getStartPosition(self):
        if self.scriptName is None:
            raise Exception("scriptName cannot be None" + self.scriptName)
        if self.dbPosition is None:
            self.initPosition()
        sql = "select position as start_position,permid,status from %s where script_name = '%s';" % (self.positionTable, self.scriptName)
        result = self.dbPosition.fetchone(sql)
        if result is None:
            return 0, 0
        else:
            self.startPosition = result.get('start_position')
            return result.get('start_position'), result.get('status')

    def updatePosition(self):
        try:
            if time.time() - self.startTimestamp > 58:
                self.initPosition()
            if self.nextPosition <= self.startPosition:
                return False
            sql = "update %s set position=%s where script_name ='%s';" % (self.positionTable, self.nextPosition, self.scriptName)
            self.info("nextPosition:" + str(self.nextPosition))
            return self.dbPosition.execute(sql)
        except Exception, e:
            raise Exception('updatePosition fail:' + str(e))

    def exchangeStrDate(self, strTime, formFormat, toFormat):
        start_timeArray = time.strptime(strTime, formFormat)
        start_timestamp = int(time.mktime(start_timeArray))
        return time.strftime(toFormat, time.localtime(start_timestamp))

    def saveDataToFile(self, file, content):
        file_object = codecs.open(file, 'a', 'utf-8')
        file_object.write(content)
        file_object.close()

    def formatServerTime(self, server_time):
        localtime = time.localtime(server_time)
        mydate = time.strftime('%Y-%m-%d', localtime)
        mytime = time.strftime('%H:%M:%S', localtime)
        myhour = time.strftime('%H', localtime)
        mydatehour = time.strftime('%Y-%m-%d %H:0:0', localtime)
        return mydate, mytime, myhour, mydatehour
