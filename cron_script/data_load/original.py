#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import MySQLdb
import logging
import settings
import redis
import sys
import codecs
import commands
import fnmatch
import threading
import hashlib

default_encoding = 'utf-8'
if sys.getdefaultencoding() != default_encoding:
    reload(sys)
    sys.setdefaultencoding(default_encoding)

logging.basicConfig(filename=os.path.join(os.getcwd(), settings.LOGPATH + '/original.log'), level=logging.INFO)

# 单次最大获取数
MAX_COUNT = settings.READ_CACHE_COUNT
RQ_ORIGINAL = settings.REDIS_RQ_ORIGINAL

class Stat(object):
    threads = 1
    rlen = 0
    r = None
    rq = None
    warmkey = {}

    def __init__(self):
        self.sqlpath = os.path.abspath(settings.SQLFILEPATH) + "/"
        self.logpath = os.path.abspath(settings.LOGPATH) + "/"
        self.dbTableName = "original_data"
        self.dbFields = settings.MYSQL_MOBGI_HOUSEAD_DATA["tables"][self.dbTableName]
        self.initRq()

    def initRedis(self):
        try:
            poolr = redis.ConnectionPool(host=settings.REDIS_HOUSEAD["host"], port=settings.REDIS_HOUSEAD["port"])
            self.r = redis.Redis(connection_pool=poolr)
        except Exception, e:
            print 'redis error!'
            logging.error(time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()) + ":redis has gone away")

    def initRq(self):
        try:
            pool_rq = redis.ConnectionPool(host=settings.REDIS_QUEUE["host"], port=settings.REDIS_QUEUE["port"])
            self.rq = redis.Redis(connection_pool=pool_rq)
        except Exception, e:
            print 'redis queue error!'
            logging.error(time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()) + ":redis queue has gone away")

    def getRlen(self):
        try:
            if self.rq.ping():
                self.rlen = self.rq.llen(RQ_ORIGINAL)
                if self.rlen > 1000000:
                    self.errlog('houseAd rlen>1000000')
            else:
                self.rlen = 0
                self.errlog('redis queue has gone away')
            self.threads = self.getThreads(self.rlen)
        except Exception, e:
            self.rlen = 0
            self.errlog("redis error:" + str(e))
        finally:
            return self.rlen

    def getThreads(self, count):
        num = str(int(count / MAX_COUNT) + 1)
        return len(num)

    def run(self):
        start_time = time.time()
        self.mylog("rlen:" + str(self.rlen))
        tasks = []
        if self.rlen > 0:
            for i in xrange(0, self.threads):
                t = threading.Thread(target=self.store, args=(i,))
                tasks.append(t)
                t.start()
            for t in tasks:
                t.join()
        self.mylog("loop_use_time:" + str(time.time() - start_time))

    def store(self, threadnum):
        try:
            start_time = time.time()
            filename = "sql_original_" + str(threadnum) + "_" + time.strftime("%Y%m%d_%H%M%S.sql", time.localtime())
            file_object = codecs.open(self.sqlpath + filename, 'a', 'utf-8')
            count = 0  # 实际个数
            for i in range(MAX_COUNT):
                stream = self.rq.lpop(RQ_ORIGINAL)
                if stream is None:
                    break
                stream = eval(stream)
                if isinstance(stream, dict) is False:
                    break
                count = count + 1
                # 椰子传媒
                if len(stream["ad_unit_id"]) > 0 and int(stream["ad_unit_id"]) == settings.UNIT_ID_YEZI:
                    yz = {
                        'originality_id': stream["originality_id"],
                        'request_id': stream["request_id"],
                        'ad_unit_id': stream["ad_unit_id"],
                        'event_type': stream["event_type"]
                    }
                    self.rq.rpush(settings.REDIS_RQ_YEZI, str(yz))
                    stream["originality_id"] = settings.ORIGINAL_ID_YEZI

                fields = []
                for field in self.dbFields:
                    fields.append(str(stream[field]))
                sqlcontent = '\t'.join(fields)
                if sqlcontent != "":
                    file_object.write(sqlcontent + "\n")

            file_object.close()
            if count > 0:
                self.loadData(filename)
            else:
                os.remove(self.sqlpath + filename)
            self.mylog(str(threadnum) + '_thread_use_time=' + str(time.time() - start_time))
        except Exception, e:
            self.errlog(str(e))

    def loadData(self, filename):
        dbConf = settings.MYSQL_MOBGI_HOUSEAD_DATA
        dbtable = dbConf["db"] + "." + self.dbTableName
        sqlconn = "/usr/bin/mysql -u" + dbConf["user"] + " -p" + dbConf["passwd"] + " -h" + dbConf["host"] + " -P" + str(dbConf["port"])
        fields = "(`" + "`,`".join(self.dbFields) + "`)"
        sqlfile = self.sqlpath + filename
        if os.path.isfile(sqlfile):  # 如果不是sql文件跳过
            try:
                load_sql = "LOAD DATA  LOCAL INFILE \"" + sqlfile + "\" INTO TABLE  " + dbtable + " character set utf8  " + fields
                del_sql = "&& rm -rf '" + sqlfile + "'"
                cmd_str = sqlconn + " --local-infile=1 -e '" + load_sql + "'" + del_sql + " 2>&1"
                output = commands.getstatusoutput(cmd_str)  # 导入成功则删除sql文件
                if output[0] != 0:
                    # 如果导入数据失败的则记录至异常sql文件中,以便以后恢复
                    filesql_obj = codecs.open(self.sqlpath + "errorSql.log", 'a', 'utf-8')
                    filesql_obj.write(str(time.strftime("[%Y-%m-%d %H:%M:%S]", time.localtime())) + cmd_str + "\r\n")
                    filesql_obj.close()
                    self.errlog("load sql fault")
            except Exception, e:
                self.errlog(str(e))
        return

    def loadFiles(self):
        sqlfiles = [f for f in os.listdir(self.sqlpath) if fnmatch.fnmatch(f, 'sql_content_*.sql')]
        for sqlfile in sqlfiles:
            self.loadData(sqlfile)

    def mylog(self, msg):
        print msg
        logging.info(str(time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())) + "\t" + msg)

    def errlog(self, msg):
        print msg
        logging.error(str(time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())) + "\t" + msg)
        key = hashlib.md5(msg).hexdigest()
        if key in self.warmkey.keys():
            sec = time.time() - self.warmkey[key]
            if sec < 3600:
                return
        self.warmkey[key] = time.time()
        self.warming(msg)

    def warming(self, msg):
        pass


if __name__ == '__main__':
    sleepCount = 0
    stat = Stat()
    while 1:
        rlen = stat.getRlen()
        if rlen < MAX_COUNT and sleepCount < 3:
            print sleepCount
            time.sleep(settings.SLEEPSECOND)
            sleepCount = sleepCount + 1
            continue
        sleepCount = 0
        stat.run()
