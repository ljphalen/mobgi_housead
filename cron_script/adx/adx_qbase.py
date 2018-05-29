#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import config.adx as configAdx
import sys
import codecs
import commands
import fnmatch
import json
from adx_base import AdxBase

default_encoding = 'utf-8'
if sys.getdefaultencoding() != default_encoding:
    reload(sys)
    sys.setdefaultencoding(default_encoding)

SQL_PATH = configAdx.SQL_PATH


class AdxQbase(AdxBase):
    rq_limit = configAdx.RQ_ADX_LIMIT
    rq = None
    # 线程数
    threads = 1
    # 队列长度
    rlen = 0
    # 队列键名
    rkey = ""
    dbConfStat = {}
    table = ""
    fields = []

    def getRlen(self):
        return self.getRQlen(self.rq, self.rkey)

    def getRQlen(self, rq, rkey):
        try:
            if rq.ping():
                self.rlen = rq.llen(rkey)
                if self.rlen > 5000000:
                    self.error('houseAd rlen=' + str(self.rlen) + ', more then 5000000')
            else:
                self.rlen = 0
                self.error('redis queue has gone away')
            self.threads = self.getThreads(self.rlen)
        except Exception, e:
            self.rlen = 0
            self.error("redis error:" + str(e))
        finally:
            return self.rlen

    def getThreads(self, count):
        num = str(int(count / 2000) + 1)
        # return 1
        return len(num)

    def store(self, threadnum, rkey):
        try:
            start_time = time.time()
            tmpStream = []
            for i in range(self.rq_limit):
                stream = self.rq.lpop(rkey)
                if stream is None:
                    break
                tmpStream.append(stream)
            # self.info(str(threadnum) + '_thread_redis_time=' + str(time.time() - start_time))
            sqlcontent = ''
            for stream in tmpStream:
                stream = json.loads(stream)
                if isinstance(stream, dict) is False:
                    continue
                items = []
                for field in self.fields:
                    items.append(str(stream[field]))
                sqlcontent += '\t'.join(items) + '\n'

            if sqlcontent != "":
                filename = self.scriptName + "_" + str(threadnum) + time.strftime("_%Y%m%d_%H%M%S.sql", time.localtime())
                file = os.path.join(sys.path[0], SQL_PATH, self.scriptName, filename)
                file_object = codecs.open(file, 'a', 'utf-8')
                file_object.write(sqlcontent)
                file_object.close()
                self.loadData(file)

            self.info(str(threadnum) + '_thread_time=' + str(time.time() - start_time))
        except Exception, e:
            self.error("store Exception:" + str(e).strip("'") + ",stream:" + str(stream))

    def loadData(self, sqlfile):
        self.loadDataSpecial(sqlfile, self.dbConfStat, self.table, self.fields)

    def loadFiles(self):
        self.info('loadFiles')
        path = os.path.join(sys.path[0], SQL_PATH, self.scriptName)
        sqlfiles = [f for f in os.listdir(path) if fnmatch.fnmatch(f, self.scriptName + "_*.sql")]
        for sqlfile in sqlfiles:
            # todo 优化隔天不导入
            self.info('loadFile:' + sqlfile)
            self.loadData(os.path.join(path, sqlfile))

    def loadDataSpecial(self, sqlfile, conf, table, fields):
        # self.info("loadDataSpecial")
        dbTable = conf["db"] + "." + table
        sqlconn = "%s -u%s -p%s -h%s -P%s" % (configAdx.MYSQL_BIN, conf["user"], conf["passwd"], conf["host"], conf["port"])
        dbFields = "(`" + "`,`".join(fields) + "`)"
        if os.path.isfile(sqlfile):
            try:
                load_sql = "LOAD DATA LOCAL INFILE \"" + sqlfile + "\" INTO TABLE  " + dbTable + " character set utf8  " + dbFields
                del_sql = " && rm -rf '" + sqlfile + "'"
                cmd_str = sqlconn + " --local-infile=1 -e '" + load_sql + "'" + del_sql + " 2>&1"
                # self.info("@@@:" + cmd_str)
                output = commands.getstatusoutput(cmd_str)  # 导入成功则删除sql文件
                if output[0] != 0:
                    self.info("###:" + cmd_str)
                    self.error("loadData Failed:" + sqlfile)
                    time.sleep(60)

            except Exception, e:
                self.error("loadData Exception:" + str(e))
        return
