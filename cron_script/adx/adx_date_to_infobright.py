#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis
import sys
import codecs
import commands
import fnmatch
import re
import lib.mysql as db
from adx_base import AdxBase

LIMIT_COUNTS = configAdx.LIMIT_COUNTS
SQL_PATH = configAdx.SQL_PATH


class AdxDataToInforbright(AdxBase):
    dataCount = 0
    desDbConf = None

    def checkCondition(self, startPosition):
        sql = "SELECT `id` FROM %s WHERE id > %s order by id asc LIMIT 0, 1" % (self.srcTable, startPosition)
        result = self.srcDb.fetchone(sql)
        # self.info(sql)
        # 判断记录是否存在
        if result is None:
            return False
        else:
            return True

    def getReportRecordList(self, startPosition):
        sql = "SELECT %s FROM %s WHERE id > %s order by id asc LIMIT 0, %s" % (",".join(self.srcFields), self.srcTable, startPosition, LIMIT_COUNTS)
        self.info('startPosition:' + str(startPosition))
        result, self.dataCount = self.srcDb.fetchall(sql, None)
        if self.dataCount > 0:
            self.info('getReportRecordList:' + str(self.dataCount))
            self.lastPostion = result[self.dataCount - 1][0]
        return result

    def saveDataToFile(self, data):
        if data is None:
            self.info("save data is None")
            return False
        file = os.path.join(sys.path[0], SQL_PATH, self.scriptName, self.scriptName + time.strftime("_%Y%m%d%H%M%S", time.localtime()) + ".sql")
        if os.path.isfile(file):
            self.error("a file already exists:" + file)
            return False

        content = ""
        for value in data:
            content += '\t'.join(map(str, value)) + "\n"
        fileObject = codecs.open(file, 'a', 'utf-8')
        fileObject.write(content)
        fileObject.close()
        self.info("save as file:" + file)
        if self.updatePosition():
            return file
        else:
            os.remove(file)
            return False

    def loadFileToInfobright(self, file):
        if file is False or os.path.isfile(file) is False:
            self.info("file does not exist:" + str(file))
            return False
        try:
            self.info('load file:' + file)
            conf = self.desDbConf
            loadSql = "LOAD DATA LOCAL INFILE '%s' INTO TABLE %s character set utf8 FIELDS TERMINATED BY '\\t' ENCLOSED BY '' " \
                      "escaped by '' LINES TERMINATED BY '\\n' STARTING BY '' (%s) " % (file, self.desTable, ','.join(self.desFields))
            command = configAdx.MYSQL_BIN + " -u%s -p%s  -h%s -D%s -P%s --local-infile=1 -e \"%s\" && rm -rf %s 2>&1" % (
                conf["user"], conf["passwd"], conf["host"], conf["db"], conf["port"], loadSql, file)
            output = commands.getstatusoutput(command)  # 导入成功则删除sql文件
            if output[0] != 0:
                self.info("###:" + command)
                self.error('load file failid:' + file)
        except Exception, e:
            self.error("load data error:" + str(e))
        return

    def loadFiles(self):
        path = os.path.join(sys.path[0], SQL_PATH, self.scriptName)
        sqlfiles = [f for f in os.listdir(path) if self.checkSqlFileName(f)]
        for sqlfile in sqlfiles:
            self.info('loadFile:' + sqlfile)
            self.loadFileToInfobright(os.path.join(path, sqlfile))

    def checkSqlFileName(self, filename):
        # fnmatch.fnmatch(f, self.scriptName + "_*.sql")
        SEARCH_PAT = re.compile(r"_(20\d{12})\.sql")
        # self.scriptName
        pat_search = SEARCH_PAT.search(filename)
        if pat_search is None:
            return False
        filetime = time.mktime(time.strptime(pat_search.group(1), '%Y%m%d%H%M%S'))
        if (time.time() - filetime) > 900:
            # todo warming
            return False
        return True

    def run(self):
        try:
            startTimeStamp = time.time()
            startPosition, status = self.getStartPosition()
            # 判断状态
            if status != 1:
                self.dataCount = 0
                self.info("status is stop")
                return False

            # 判断是否有新数据
            if self.checkCondition(startPosition) is not True:
                self.dataCount = 0
                self.info("No data")
                return False

            # 解析保存数据
            recordData = self.getReportRecordList(startPosition)
            file = self.saveDataToFile(recordData)
            self.loadFileToInfobright(file)
            self.loadFiles()

            self.info("query use time : " + str(time.time() - startTimeStamp))
        except Exception, e:
            self.error("run error:" + str(e))
