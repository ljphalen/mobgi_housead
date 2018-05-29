#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import sys
import commands
import fnmatch


sys.path.append("..")
from lib.mybase import Base
import config.adx as configAdx

MYSQL_BIN = configAdx.MYSQL_BIN


# 队列基类
class AdQbase(Base):
    rqLimit = configAdx.RQ_ADX_LIMIT
    rq = None
    idCreater = None
    # 线程数
    threads = 1
    # 队列长度
    rlen = 0
    # 队列键名
    rkey = ""
    statConfig = {}
    statTable = ""
    statFields = []
    defaultVal = {}

    def getRlen(self):
        return self.getRQlen(self.rq, self.rkey)

    def setAutoCreater(self, creater):
        self.idCreater = creater;

    def getRQlen(self, rq, rkey):
        try:
            if rq.ping():
                self.rlen = rq.llen(rkey)
                if self.rlen > 5000000:
                    self.error('rlen>5000000', 'len=' + str(self.rlen), 0)
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
        if count>2000000:
            return 2
        else:
            return len(str(int(count / 20000) + 1))



    def loadData(self, sqlfile):
        self.loadDataSpecial(sqlfile, self.statConfig, self.statTable, self.statFields)

    def loadFiles(self):
        self.info('loadFiles')
        path = os.path.join(sys.path[0], self.sqlPath)
        sqlfiles = [f for f in os.listdir(path) if fnmatch.fnmatch(f, self.scriptName + "_*.sql")]
        for sqlfile in sqlfiles:
            # todo 优化隔天不导入
            self.info('loadFile:' + sqlfile)
            self.loadData(os.path.join(path, sqlfile))

    def loadDataSpecial(self, sqlfile, conf, table, fields):
        # self.info("loadDataSpecial")
        dbTable = conf["db"] + "." + table
        sqlconn = "%s -u%s -p%s -h%s -P%s -D%s" % (MYSQL_BIN, conf["user"], conf["passwd"], conf["host"], conf["port"], conf["db"])
        dbFields = ",".join(fields)
        if os.path.isfile(sqlfile):
            try:
                loadSql = "set @bh_dataformat='mysql';LOAD DATA LOCAL INFILE '%s' INTO TABLE %s character set utf8 FIELDS TERMINATED BY '\\t' ENCLOSED BY '' " \
                          "escaped by '' LINES TERMINATED BY '\\n' STARTING BY ''  " % (sqlfile, dbTable)
                command = "%s --local-infile=1 -e \"%s\" && rm -rf %s 2>&1" % (sqlconn, loadSql, sqlfile)
                output = commands.getstatusoutput(command)  # 导入成功则删除sql文件
                if output[0] != 0:
                    self.info("###:" + command)
                    self.error("loadData Failed:" + sqlfile)
                    time.sleep(60)
            except Exception, e:
                raise Exception("loadData Exception:" + str(e))
        return
