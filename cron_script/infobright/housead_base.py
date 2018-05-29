#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import logging
import housead_config as Config
import codecs
import redis
import commands
import MySQLdb
import MySQLdb.cursors
import glob

statConfig = Config.MYSQL_MOBGI_HOUSEAD_STAT
dataConfig = Config.MYSQL_MOBGI_HOUSEAD_DATA
infoBhConfig = Config.MYSQL_BH_HOUSEAD


##把聚合数据导入inforbright
class HouseadBase(object):
    scriptName = 'housead_base'
    startPostion = 0
    lastPostion = 0
    recordCount = 0
    init = True

    def __init__(self):
        fileName = self.scriptName + '.log'
        self.initLog(fileName)
        self.sqlFilePath = os.getcwd() + '/' + Config.SQLFILEPATH + '/'
        self.logFilePath = os.getcwd() + '/' + Config.LOGPATH + '/'
        self.init = self.initDb()
        self.initRedis()


    def initLog(self, fileName):
        logFileName = os.path.join(os.getcwd(), Config.LOGPATH + '/' + fileName)
        logging.basicConfig(filename=logFileName, format='%(asctime)s %(filename)s[line:%(lineno)d] %(levelname)s %(message)s',
                            datefmt='%Y-%m-%d %H:%M:%S', level=logging.INFO)

    def initRedis(self):
        try:
            poolr = redis.ConnectionPool(host=Config.REDIS_HOUSEAD["host"], port=Config.REDIS_HOUSEAD["port"])
            self.r = redis.Redis(connection_pool=poolr)
        except Exception, e:
            print 'redis error!'
            logging.error(time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()) + ":redis has gone away")

    def infolog(self, msg):
        print time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()) + " " + str(msg)
        logging.info(msg)

    def infolog(self, msg):
        print time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()) + " " + str(msg)
        logging.info(msg)

    def errlog(self, msg):
        print time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()) + " " + str(msg)
        logging.error(msg)

    def initDb(self):
        try:
            self.dataDb = MySQLdb.connect(host=dataConfig['host'], port=dataConfig['port'], user=dataConfig['user'], passwd=dataConfig['passwd'],
                                          db=dataConfig['db'], charset='utf8', init_command="set names utf8", cursorclass=MySQLdb.cursors.DictCursor)
            self.statDb = MySQLdb.connect(host=statConfig['host'], port=statConfig['port'], user=statConfig['user'], passwd=statConfig['passwd'],
                                          db=statConfig['db'], charset='utf8', init_command="set names utf8")
            self.bhDb = MySQLdb.connect(host=infoBhConfig['host'], port=infoBhConfig['port'], user=infoBhConfig['user'],
                                        passwd=infoBhConfig['passwd'], db=infoBhConfig['db'], charset='utf8', init_command="set names utf8")
            return True
        except Exception, e:
            self.errlog("Connection db error error:" + str(e))
            return False

    def getStartPosition(self):
        if self.scriptName is None:
            self.errlog("scriptName cannot be None")
            quit()
        sql = "select last_id as start_position from import_log where name = '%s';" % (self.scriptName)
        statDbcursor = self.statDb.cursor(MySQLdb.cursors.DictCursor)
        statDbcursor.execute(sql)
        result = statDbcursor.fetchone()
        statDbcursor.close()
        if result is None:
            startPosition = 0
            self.initPosition(startPosition)
        elif result.has_key('start_position') is not None:
            startPosition = result.get('start_position')
        else:
            startPosition = 0
            self.initPosition(startPosition)
        self.infolog("startPosition:" + str(startPosition))
        return startPosition

    def initPosition(self, startPosition):
        try:
            sql = "insert into import_log set last_id =" + str(startPosition) + ", name = '" + self.scriptName + "'"
            self.infolog("initPosition sql=" + sql)
            promote_cursor = self.statDb.cursor()
            promote_cursor.execute(sql)
            self.statDb.commit()
            promote_cursor.close()
        except Exception, e:
            self.errlog("init Position fail :" + str(e))

    def updatePosition(self, lastPostion):
        try:
            sql = "update import_log set last_id = " + str(lastPostion) + "  where name = '" + self.scriptName + "'"
            self.infolog("updatePosition last_id=" + str(lastPostion))
            promote_cursor = self.statDb.cursor()
            promote_cursor.execute(sql)
            self.statDb.commit()
            promote_cursor.close()
        except Exception, e:
            self.errlog("update Position fail :" + str(e))

    def saveDataToFile(self, data):
        if data is None:
            self.infolog("save data is None")
            return False

        file = self.sqlFilePath + self.scriptName + time.strftime("_%Y%m%d%H%M%S", time.localtime()) + ".sql"
        if os.path.isfile(file):
            self.infolog("cannot create a file already exists:" + file)
            return False

        content = ""
        for value in data:
            content += '\t'.join(map(str, value)) + "\n"

        fileObject = codecs.open(file, 'a', 'utf-8')
        fileObject.write(content)
        fileObject.close()
        self.infolog("save as file:" + file)

        # 下一次的位置
        self.dataCount = len(data)
        self.lastPostion = data[self.dataCount - 1][0]
        self.updatePosition(self.lastPostion)
        return file

    def loadFileToInfobright(self, file):
        if os.path.isfile(file) is False:
            self.infolog("file does not exist:" + file)
            return False
        try:
            # self.infolog('load file:' + file)
            loadSql = "LOAD DATA LOCAL INFILE '%s' INTO TABLE %s character set utf8 FIELDS TERMINATED BY '\\t' ENCLOSED BY '' " \
                      "escaped by '' LINES TERMINATED BY '\\n' STARTING BY '' (%s) " % (file, self.bhTable, ','.join(self.fields))
            command = Config.MYSQL_PATH + " -u%s -p%s  -h%s -D%s -P%s --local-infile=1 -e \"%s\" && rm -rf %s 2>&1" % (
                infoBhConfig["user"], infoBhConfig["passwd"], infoBhConfig["host"], infoBhConfig["db"], str(infoBhConfig["port"]), loadSql, file)
            # self.infolog('load inforbright:' + command)
            output = commands.getstatusoutput(command)  # 导入成功则删除sql文件
            if output[0] != 0:
                # 如果导入数据失败的则记录至异常sql文件中,以便以后恢复
                fileObject = codecs.open(self.logFilePath + "errorSql.log", 'a', 'utf-8')
                fileObject.write(str(time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())) + "\terror:" + output[1] + "->" + loadSql + "\r\n")
                fileObject.close()
        except Exception, e:
            self.errlog("load data error:" + str(e))
        return

    def checkCondition(self, startPosition):
        sql = "SELECT `id` FROM %s WHERE id > %s order by id asc LIMIT 0, 1" % (self.table, startPosition)
        statCursor = self.statDb.cursor()
        statCursor.execute(sql)
        result = statCursor.fetchone()
        statCursor.close()
        # 判断记录是否存在
        if result is None:
            return False
        else:
            return True

    # 检查infobright是否有新数据
    def checkInfoBrightCondition(self, startPosition):
        sql = "SELECT `id` FROM %s WHERE id > %s order by id asc LIMIT 0, 1" % (self.bhTable, startPosition)
        statCursor = self.bhDb.cursor()
        statCursor.execute(sql)
        result = statCursor.fetchone()
        statCursor.close()
        # 判断记录是否存在
        if result is None:
            return False
        else:
            return True

    def getReportRecordList(self, startPosition):
        sql = "SELECT %s FROM %s WHERE id > %s order by id asc LIMIT 0, %s" % (",".join(self.fields), self.table, startPosition, Config.LIMIT_COUNTS)
        self.infolog('getReportRecordList sql:' + sql)
        statCursor = self.statDb.cursor()
        statCursor.execute(sql)
        result = statCursor.fetchall()
        self.recordCount = statCursor.rowcount
        statCursor.close()
        return result

    # 数据导入infobright
    def run(self):
        print "running..."
        try:
            startTimeStamp = time.time()
            # 取开始ID
            startPosition = self.getStartPosition()
            # 判断是否有新数据
            if self.checkCondition(startPosition) is not True:
                self.infolog("checkCondition failed -- no data")
                self.dataCount = 0
                return False

            # 解析保存数据
            recordData = self.getReportRecordList(startPosition)
            file = self.saveDataToFile(recordData)

            self.loadFileToInfobright(file)
            self.infolog("lastPositon:" + str(self.lastPostion) + "  query use time : " + str(time.time() - startTimeStamp))
        except Exception, e:
            self.errlog("run error:" + str(e))
        finally:
            self.statDb.close()
            self.dataDb.close()
            self.bhDb.close()

    def getInfoBrightRecordList(self, startPosition):
        sql = "SELECT %s FROM %s WHERE id > %s order by id asc LIMIT 0, %s" % (
            ",".join(self.fields), self.bhTable, startPosition, Config.LIMIT_COUNTS)
        self.infolog('getReportRecordList sql:' + sql)
        bhCursor = self.bhDb.cursor(MySQLdb.cursors.DictCursor)
        bhCursor.execute(sql)
        result = bhCursor.fetchall()
        self.recordCount = bhCursor.rowcount
        bhCursor.close()
        return result

    def getOrigInfoFromDb(self, OrigId):
        table = "mobgi_housead.delivery_originality_relation"
        dbConfig = Config.MYSQL_MOBGI_HOUSEAD
        try:
            conn = MySQLdb.connect(host=dbConfig['host'], port=dbConfig['port'], user=dbConfig['user'], passwd=dbConfig['passwd'], db=dbConfig['db'],
                                   charset='utf8', init_command="set names utf8", cursorclass=MySQLdb.cursors.DictCursor)
        except Exception, e:
            self.errlog("Connection db error error:" + str(e))

        sql = "SELECT id,ad_id,unit_id,originality_type,account_id FROM %s WHERE `id` = %s LIMIT 0, 1" % (table, str(OrigId))
        cursor = conn.cursor(MySQLdb.cursors.DictCursor)
        cursor.execute(sql)
        result = cursor.fetchone()
        cursor.close()
        return result

    def getOrigInfo(self, OrigId):
        if OrigId is None:
            return None
        rkey = Config.REDIS_ORIGINFO + str(OrigId)
        if self.r.hget(rkey, "ad_id") is None:
            result = self.getOrigInfoFromDb(OrigId)
            if result is not None:
                self.r.hset(rkey, 'ad_id', result.get('ad_id'))
                self.r.hset(rkey, 'unit_id', result.get('unit_id'))
                self.r.hset(rkey, 'originality_type', result.get('originality_type'))
                self.r.hset(rkey, 'account_id', result.get('account_id'))
        else:
            result = self.r.hgetall(rkey)

        return result
