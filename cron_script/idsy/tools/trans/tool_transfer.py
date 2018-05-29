#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import commands
import transfer_config as mydbConf

sys.path.append("../..")
from lib.mybase import Base
from lib.myini import myini

TABLE_MAP = mydbConf.RUN_CONIF['table_map']
MYSQL_BIN = mydbConf.MYSQL_BIN
LIMIT_COUNTS = mydbConf.LIMIT_COUNTS


class toolTransfer(Base):
    srcTableName = None
    descTableName = None
    iniFileName = 'transfer.ini'
    fo = None
    iniKey = None
    maxPosition = None

    def init(self):
        self.fo = myini(self.iniFileName)
        self.iniKey = mydbConf.RUN_CONIF['name']
        self.srcConfig = mydbConf.RUN_CONIF['src_conn']
        self.decConfig = mydbConf.RUN_CONIF['dec_conn']

    def initTable(self, srcTable):
        self.srcTableName = srcTable
        self.decTableName = TABLE_MAP[srcTable]
        self.srcFields = mydbConf.FIELDS_SRC[self.srcTableName]
        self.decFields = mydbConf.FIELDS_DEC[self.decTableName]
        self.srcDb = self.initDb(self.srcConfig)
        self.decDb = self.initDb(self.decConfig)

    # 检查infobright是否有新数据
    def checkCondition(self, startPosition):
        if startPosition < self.lastPosition:
            return True
        sql = "SELECT max(id) as id FROM %s where id>%s" % (self.srcTableName, startPosition)
        result = self.srcDb.fetchone(sql)
        if result['id'] is None:
            return False
        else:
            self.lastPosition = int(result['id'])
            return startPosition < self.lastPosition

    def getStartPosition(self):
        if self.scriptName is None:
            raise Exception("scriptName cannot be None" + self.scriptName)
        position = self.fo.get(self.iniKey, 'position')
        if position is False:
            return 0, 0
        else:
            return int(position), 1

    def getMaxPosition(self):
        position = self.fo.get(self.iniKey, 'max_position')
        if position > 0:
            return int(position)
        else:
            return None

    def updatePosition(self, nextPosition):
        try:
            if nextPosition <= self.startPosition:
                return False
            return self.fo.set(self.iniKey, 'position', nextPosition)
        except Exception, e:
            raise Exception('updatePosition fail:' + str(e))

    def dumpToFile(self, dbConf, startPosition):
        # 2017-01-01
        if self.maxPosition is None:
            maxWhere = ""
        else:
            maxWhere = "id <=" + str(self.maxPosition)
        try:
            sql = """select * from %s where id>%s and %s order by id limit 0,%s""" % (self.srcTableName, startPosition, maxWhere, str(LIMIT_COUNTS))
            file = self.getSqlPath(self.srcTableName + "_" + str(startPosition), '.')
            command = """%s -h%s -u%s -p%s -P%s -D%s -N -B -e "%s" > %s""" % (
                MYSQL_BIN, dbConf["host"], dbConf["user"], dbConf["passwd"], str(dbConf["port"]), str(dbConf["db"]), sql, file)
            output = commands.getstatusoutput(command)
            if output[0] != 0:
                self.info("###:" + command)
                self.error('load file failed:' + file)
                return False
            else:
                return file
        except Exception, e:
            raise Exception("dumpToFile fail:" + str(e))

    def loadFile(self, file, dbConf):
        if os.path.isfile(file):
            try:
                table = dbConf["db"] + "." + self.decTableName
                loadSql = "LOAD DATA LOCAL INFILE '" + file + "' INTO TABLE " + table + " character set utf8 "
                loadSql += "FIELDS TERMINATED BY '\\t' ENCLOSED BY '' escaped by '' LINES TERMINATED BY '\\n' STARTING BY '' (" + self.srcFields + ")"
                delsql = "&& rm -rf " + file + " 2>&1"
                command = """%s -h%s -u%s -p%s -P%s --local-infile=1 -e "%s"  %s""" % (
                    MYSQL_BIN, dbConf["host"], dbConf["user"], dbConf["passwd"], str(dbConf["port"]), loadSql, delsql)
                output = commands.getstatusoutput(command)  # 导入成功则删除sql文件
                if output[0] != 0:
                    self.info("===:" + command)
                    self.error('load file failed:' + file)
                else:
                    return True
            except Exception, e:
                raise Exception("loadFile fail:" + str(e))

        return False

    def getNextPosition(self, file, startPosition):
        command = "tail -n 1 " + file + "|awk '{print $1}'"
        output = commands.getstatusoutput(command)

        if output[0] != 0:
            self.error('getNextPosition:' + file)
            return startPosition
        else:
            if len(output[1]) > 0:
                lastId = int(output[1])
            else:
                lastId = 0

            self.info('getNextPosition:' + str(lastId))
            if lastId > startPosition:
                return lastId
            else:
                return startPosition

    def run(self, srcTable, lastPosition):
        try:
            startTimeStamp = time.time()
            self.lastPosition = lastPosition
            self.initTable(srcTable)
            self.startPosition, status = self.getStartPosition()
            self.maxPosition = self.getMaxPosition()

            if status != 1:
                self.dataLength = 0
                self.info("status is stop")
                return False
            if self.checkCondition(self.startPosition) is False:
                self.dataCount = 0
                self.info("condition fail")
                return False
            # 保存数据
            self.info("startPosition:" + str(self.startPosition))
            file = self.dumpToFile(self.srcConfig, self.startPosition)
            if file is not False and os.path.isfile(file):
                self.nextPosition = self.getNextPosition(file, self.startPosition)
                self.updatePosition(self.nextPosition)
                self.loadFile(file, self.decConfig)
            self.info("use time : " + str(time.time() - startTimeStamp))
        except Exception, e:
            self.error("run error:" + str(e))


if __name__ == '__main__':
    tableName = 'video_ads_stat'
    # if len(sys.argv) == 2:
    #     tableName = sys.argv[1]
    #     if tableName not in TABLE_MAP:
    #         print "table name is not in [" + str(TABLE_MAP.keys()) + "]\n"
    #         exit()
    # else:
    #     print "table name is none\n"
    #     exit()

    sleepCount = 0
    lastPosition = 0
    while 1:
        obj = toolTransfer('tool_transfer')
        if obj.errorFlag:
            obj = None
            time.sleep(mydbConf.SLEEP_SECOND)
            sleepCount += 1
        result = obj.run(tableName, lastPosition)
        lastPosition = obj.lastPosition
        if result is False:
            obj.info("zzz")
            obj = None
            time.sleep(mydbConf.SLEEP_SECOND)
            sleepCount += 1
            continue
