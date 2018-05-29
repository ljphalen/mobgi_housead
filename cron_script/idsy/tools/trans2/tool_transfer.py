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

TRANSFER = mydbConf.TRANSFER
MYSQL_BIN = mydbConf.MYSQL_BIN
LIMIT_COUNTS = mydbConf.LIMIT_COUNTS
BIT_LENGTH = mydbConf.BIT_LENGTH


class toolTransfer(Base):
    srcTableName = None
    descTableName = None
    iniFileName = 'transfer.ini'
    fo = None
    iniKey = None
    maxPosition = None

    def init(self):
        self.fo = myini(self.iniFileName)

    def initProj(self, projName):
        self.iniKey = projName
        self.srcConfig = TRANSFER[projName]['src_conn']
        self.decConfig = TRANSFER[projName]['dec_conn']
        self.srcTableName = TRANSFER[projName]['src_table']
        self.decTableName = TRANSFER[projName]['dec_table']
        self.fields = TRANSFER[projName]['fields']
        self.srcDb = self.initDb(self.srcConfig)
        # self.decDb = self.initDb(self.decConfig)

    # 检查infobright是否有新数据
    def checkCondition(self, nextPosition):
        if nextPosition < self.lastPosition:
            return True
        sql = "SELECT max(id) as id FROM %s" % (self.srcTableName)
        result = self.srcDb.fetchone(sql)
        # 判断记录是否存在
        if result is None:
            return False
        else:
            self.lastPosition = int(result['id']) / BIT_LENGTH
            return nextPosition < self.lastPosition

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
        if int(position) > 0:
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

    def dumpToFile(self, startPosition, nextPosition):
        startId = startPosition * BIT_LENGTH
        nextId = nextPosition * BIT_LENGTH
        db = self.srcConfig
        try:
            sql = """select %s from %s where id>%s and id<%s order by id""" % (self.fields, self.srcTableName, startId, nextId)
            file = self.getSqlPath(self.srcTableName + "_" + str(startPosition), '.')
            command = """%s -h%s -u%s -p%s -P%s -D%s -N -B -e "%s" > %s""" % (
                MYSQL_BIN, db["host"], db["user"], db["passwd"], str(db["port"]), str(db["db"]), sql, file)
            output = commands.getstatusoutput(command)
            if output[0] != 0:
                self.info("###:" + command)
                self.error('dumpToFile failed:' + file)
                return False
            else:
                return file
        except Exception, e:
            raise Exception("dumpToFile fail:" + str(e))

    def loadFile(self, sqlfile):
        # self.info("loadDataSpecial")
        conf = self.decConfig
        table = self.decTableName
        dbTable = conf["db"] + "." + table
        sqlconn = "%s -u%s -p%s -h%s -P%s -D%s" % (MYSQL_BIN, conf["user"], conf["passwd"], conf["host"], conf["port"], conf["db"])
        if os.path.isfile(sqlfile):
            try:
                loadSql = "set @bh_dataformat='mysql';LOAD DATA LOCAL INFILE '%s' INTO TABLE %s character set utf8 FIELDS TERMINATED BY '\\t' " \
                          "ENCLOSED BY '' escaped by '' LINES TERMINATED BY '\\n' STARTING BY ''  " % (sqlfile, dbTable)
                command = "%s --local-infile=1 -e \"%s\" && rm -rf %s 2>&1" % (sqlconn, loadSql, sqlfile)
                output = commands.getstatusoutput(command)  # 导入成功则删除sql文件
                if output[0] != 0:
                    self.info("###:" + command)
                    self.error("loadData Failed:" + sqlfile)
                    time.sleep(60)
                else:
                    return True
            except Exception, e:
                raise Exception("loadData Exception:" + str(e))
        return False

    def run(self, projName, lastPosition):
        try:
            startTimeStamp = time.time()
            self.lastPosition = lastPosition
            self.initProj(projName)
            self.startPosition, status = self.getStartPosition()
            self.nextPosition = self.startPosition + 1
            self.maxPosition = self.getMaxPosition()

            if status != 1:
                self.dataLength = 0
                self.info("status is stop")
                return False

            if self.checkCondition(self.startPosition) is False:
                self.dataCount = 0
                self.info("Not to start position")
                return False

            if self.maxPosition is not None and self.nextPosition >= self.maxPosition:
                self.info("Reach the maxPosition")
                quit()

            # 保存数据
            self.info("startPosition:" + str(self.startPosition))
            file = self.dumpToFile(self.startPosition, self.nextPosition)
            if file is not False and os.path.isfile(file):
                # self.delOldData(self.srcConfig, self.startPosition)
                if self.loadFile(file):
                    self.info("startPosition:" + str(file))
            self.updatePosition(self.nextPosition)

            self.info("use time : " + str(time.time() - startTimeStamp))
        except Exception, e:
            self.error("run error:" + str(e))


if __name__ == '__main__':

    projName = 'transfer_ad_client4'
    # if len(sys.argv) == 2:
    #     projName = sys.argv[1]
    #     if projName not in TRANSFER:
    #         print "proj name is not in [" + str(TRANSFER.keys()) + "]\n"
    #         exit()
    # else:
    #     print "proj name is none\n"
    #     exit()

    sleepCount = 0
    lastPosition = 0
    while 1:
        obj = toolTransfer('tool_transfer')
        if obj.errorFlag:
            obj = None
            time.sleep(mydbConf.SLEEP_SECOND)
            sleepCount += 1
        result = obj.run(projName, lastPosition)
        lastPosition = obj.lastPosition
        if result is False:
            obj.info("zzz")
            obj = None
            time.sleep(mydbConf.SLEEP_SECOND)
            sleepCount += 1
            continue
