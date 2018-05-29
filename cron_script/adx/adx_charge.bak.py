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

logging.basicConfig(filename=os.path.join(os.getcwd(), settings.LOGPATH + "/charge.log"), level=logging.INFO)

try:
    poolr = redis.ConnectionPool(host=settings.REDIS_HOUSEAD["host"], port=settings.REDIS_HOUSEAD["port"])
    r = redis.Redis(connection_pool=poolr)
except Exception, e:
    print 'redis error!'
    logging.error(time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()) + ":redis has gone away")

try:
    pool_rq = redis.ConnectionPool(host=settings.REDIS_QUEUE["host"], port=settings.REDIS_QUEUE["port"])
    rq = redis.Redis(connection_pool=pool_rq)
except Exception, e:
    print 'redis queue error!'
    logging.error(time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()) + ":redis queue has gone away")

CHAREG_TYPE_CLICK = settings.CHAREG_TYPE_CLICK
CHAREG_TYPE_VIEW = settings.CHAREG_TYPE_VIEW
RQ_CHARGE = settings.REDIS_RQ_CHARGE
MAX_COUNT = 100


class Stat(object):
    threads = 1
    rlen = 0
    r = None
    rq = None
    warmkey = {}


    def __init__(self):
        self.sqlpath = os.path.abspath(settings.SQLFILEPATH) + "/"
        self.logpath = os.path.abspath(settings.LOGPATH) + "/"
        # self.logfile = open(self.logpath + "charge_" + time.strftime("%Y%m%d", time.localtime()) + ".log", 'a')
        self.dbTableName = "charge_data"
        self.dbFields = settings.MYSQL_MOBGI_HOUSEAD_DATA["tables"][self.dbTableName]
        self.minuteKey = settings.REDIS_MINUTE_STAT

    def getRlen(self):
        try:
            if rq.ping():
                self.rlen = rq.llen(RQ_CHARGE)
                if self.rlen > 10000:
                    self.errlog('houseAd RQ_CHARGE rlen>10000')
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
        num = str(int(count / 1000) + 1)
        return len(num)

    def run(self):
        start_time = time.time()
        self.saveMinuteData()
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
            amount = {};
            clicks = {};
            views = {};
            filename = "sql_charge_" + str(threadnum) + "_" + time.strftime("%Y%m%d_%H%M%S.sql", time.localtime())
            file_object = codecs.open(self.sqlpath + filename, 'a', 'utf-8')
            count = 0  # 实际个数
            for i in range(MAX_COUNT):
                stream = rq.lpop(RQ_CHARGE)
                if stream is None:
                    break
                stream = eval(stream)
                if isinstance(stream, dict) is False:
                    break
                count = count + 1
                fields = []
                origKey = time.strftime("%Y%m%d%H%M", time.localtime(stream['created_time'])) + "_" + str(stream['originality_id'])
                if amount.has_key(origKey):
                    amount[origKey].append(float(stream['price']))
                else:
                    amount[origKey] = [float(stream['price'])]

                # 按类型汇总计费次数
                if stream['event_type'] == CHAREG_TYPE_CLICK:
                    if clicks.has_key(origKey):
                        clicks[origKey] = clicks[origKey] + 1
                    else:
                        clicks[origKey] = 1
                elif stream['event_type'] == CHAREG_TYPE_VIEW:
                    if views.has_key(origKey):
                        views[origKey] = views[origKey] + 1
                    else:
                        views[origKey] = 1

                for field in self.dbFields:
                    fields.append(str(stream[field]))

                sqlcontent = '\t'.join(fields)
                if sqlcontent != "":
                    file_object.write(sqlcontent + "\n")
            file_object.close()

            if count > 0:
                print "count=", count
                self.loadData(filename, settings.MYSQL_MOBGI_HOUSEAD_DATA, "charge_data")
                self.updateRealData(threadnum, time.localtime(start_time), amount, views, clicks)
            else:
                os.remove(self.sqlpath + filename)
            self.mylog(str(threadnum) + '_thread_use_time=' + str(time.time() - start_time))
        except Exception, e:
            self.errlog(str(e))

    def loadData(self, filename, dbConf, tableName):
        dbtable = dbConf["db"] + "." + tableName
        dbFields = dbConf["tables"][tableName]
        sqlconn = "/usr/bin/mysql -u" + dbConf["user"] + " -p" + dbConf["passwd"] + " -h" + dbConf["host"] + " -P" + str(dbConf["port"])
        fields = "(`" + "`,`".join(dbFields) + "`)"
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
                    self.mylog("load sql fault")
            except Exception, e:
                self.errlog(str(e))
        return

    def loadFiles(self):
        sqlfiles = [f for f in os.listdir(self.sqlpath) if fnmatch.fnmatch(f, 'sql_charge_*.sql')]
        for sqlfile in sqlfiles:
            self.loadData(sqlfile, settings.MYSQL_MOBGI_HOUSEAD_DATA, "charge_data")

    def updateRealData(self, threadnum, timestamp, amount, views, clicks):
        pre = settings.REDIS_CHARGE_PREFIX
        # minute = time.strftime("%Y%m%d%H%M", timestamp)

        # 消费队列
        filename = "batch_deduction_detail_" + str(threadnum) + "_" + str(time.strftime("%Y%m%d%H%M%S", timestamp)) + '.sql'
        file_object = codecs.open(self.sqlpath + filename, 'a', 'utf-8')

        for origKey, v in views.items():
            minute, origId = origKey.split('_');
            mkey = pre + origKey
            # dkey = pre + today + '_' + origId
            minval = r.hincrby(mkey, 'views', v)
            self.updateDayData(pre, timestamp, origId, 'views', v)
            # dayval = r.hincrby(dkey, 'views', v)

        for origKey, v in clicks.items():
            minute, origId = origKey.split('_');
            mkey = pre + origKey
            minval = r.hincrby(mkey, 'clicks', v)
            self.updateDayData(pre, timestamp, origId, 'clicks', v)

        for origKey, v in amount.items():
            sumval = float(sum(v))
            minute, origId = origKey.split('_');
            mkey = pre + origKey
            mval = r.hincrbyfloat(mkey, 'amount', sumval)
            self.updateDayDataFloat(pre, timestamp, origId, 'amount', sumval)
            self.addMinuteKey(mkey)

            minute_timestamp = int(time.mktime(time.strptime(minute, "%Y%m%d%H%M")))
            fields = []
            fields.append(origId)
            fields.append(str(minute_timestamp))
            fields.append(str(sum(v)))
            sqlcontent = '\t'.join(fields)
            if sqlcontent != "":
                file_object.write(sqlcontent + "\n")

        file_object.close()
        self.loadData(filename, settings.MYSQL_MOBGI_HOUSEAD, "advertiser_batch_deduction_detail")

    def addMinuteKey(self, key):
        if r.sismember(self.minuteKey, key) == 0:
            r.sadd(self.minuteKey, key)

    def updateDayData(self, pre, timestamp, origId, field, v):
        today = time.strftime("%Y%m%d", timestamp)
        dkey = pre + today + '_' + origId
        dayval = r.hincrby(dkey, field, v)
        if (dayval == v and v != 0):
            r.hincrby(dkey, field, int(self.getTodayData(timestamp, field, origId)))
            r.expire(dkey, 1800)

    def updateDayDataFloat(self, pre, timestamp, origId, field, v):
        today = time.strftime("%Y%m%d", timestamp)
        dkey = pre + today + '_' + origId
        dayval = r.hincrbyfloat(dkey, field, v)
        if (dayval == v and v != 0):
            total = self.getTodayData(timestamp, field, origId)
            r.hincrbyfloat(dkey, field, float(total))
            r.expire(dkey, 1800)

    def getTodayData(self, timestamp, field, origId):
        dbconn = settings.MYSQL_MOBGI_HOUSEAD_STAT
        try:
            self.promote_db = MySQLdb.connect(host=dbconn["host"], port=dbconn["port"], user=dbconn["user"], passwd=dbconn["passwd"], db=dbconn["db"], charset='utf8', init_command="set names utf8")

        # , cursorclass = MySQLdb.cursors.DictCursor
        except Exception, e:
            self.errlog("Connection db MYSQL_MOBGI_HOUSEAD_STAT error:" + str(e))
            result = {}
        sql = """select sum(%s) as total from %s where originality_id = '%s' and `minute` BETWEEN  "%s"  and "%s";""" % (
            field, 'stat_minute', origId, time.strftime("%Y-%m-%d", timestamp), time.strftime("%Y-%m-%d %H:%M:%S", timestamp))
        promote_cursor = self.promote_db.cursor(MySQLdb.cursors.DictCursor)
        promote_cursor.execute(sql)
        result = promote_cursor.fetchone()
        promote_cursor.close()
        if result["total"] is None:
            return 0
        else:
            return result["total"]

    def saveMinuteData(self):
        # 延迟10s

        now = time.strftime("%Y%m%d%H%M", time.localtime(time.time() - 10))
        now_timestamp = int(time.mktime(time.strptime(now, "%Y%m%d%H%M")))
        keys = r.smembers(self.minuteKey)
        for key in keys:
            [minute, origId] = key.split('_')[-2:]
            minute_timestamp = int(time.mktime(time.strptime(minute, "%Y%m%d%H%M")))
            if minute_timestamp < now_timestamp:
                print key, now_timestamp - minute_timestamp
                self.saveMinuteDataByKey(key, self.exchangeStrDate(minute, "%Y%m%d%H%M", "%Y-%m-%d %H:%M"), origId)
        return

    def saveMinuteDataByKey(self, key, minute, origId):
        hval = r.hgetall(key)
        if hval.has_key("clicks") is False:
            hval["clicks"] = "0"
        if hval.has_key("views") is False:
            hval["views"] = "0"
        if hval.has_key("amount") is False:
            hval["amount"] = "0"

        dbconn = settings.MYSQL_MOBGI_HOUSEAD_STAT
        try:
            self.promote_db = MySQLdb.connect(host=dbconn["host"], port=dbconn["port"], user=dbconn["user"], passwd=dbconn["passwd"], db=dbconn["db"], charset='utf8', init_command="set names utf8")
        except Exception, e:
            self.errlog("Connection MYSQL_MOBGI_HOUSEAD_STAT error:" + str(e))

        promote_cursor = self.promote_db.cursor(MySQLdb.cursors.DictCursor)
        try:
            sql = """INSERT INTO `%s` (`originality_id`, `minute`, `clicks`, `views`, `amount`) VALUES (%s,"%s",%s,%s,%s)""" % (
                "stat_minute", origId, minute, hval['clicks'], hval['views'], hval['amount'])
            self.mylog("minute_sql:" + sql)
            result = promote_cursor.execute(sql)
            if result:
                self.promote_db.commit()
                r.hdel(key, "clicks", "views", "amount")
                r.srem(self.minuteKey, key)
            promote_cursor.close()
        except Exception, e:
            promote_cursor.close()
            r.srem(self.minuteKey, key)
            self.errlog("execute sql error:" + str(e))
        finally:
            self.promote_db.close()

    def exchangeStrDate(self, strTime, formFormat, toFormat):
        start_timeArray = time.strptime(strTime, formFormat)
        start_timestamp = int(time.mktime(start_timeArray))
        return time.strftime(toFormat, time.localtime(start_timestamp))

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
