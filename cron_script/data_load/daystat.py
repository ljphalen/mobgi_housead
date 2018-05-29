#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import datetime
import MySQLdb
import logging
import settings
import sys
import redis
import urllib2

default_encoding = 'utf-8'
if sys.getdefaultencoding() != default_encoding:
    reload(sys)
    sys.setdefaultencoding(default_encoding)

logging.basicConfig(filename=os.path.join(os.getcwd(), settings.LOGPATH + '/run_day.log'), level=logging.INFO)


class Stat(object):
    def __init__(self):
        self.sqlpath = os.path.abspath(settings.SQLFILEPATH) + "/"
        self.logpath = os.path.abspath(settings.LOGPATH) + "/"
        self.logfile = open(self.logpath + "charge_" + time.strftime("%Y%m%d", time.localtime()) + ".log", 'a')
        self.initRedis()

    def initRedis(self):
        try:
            poolr = redis.ConnectionPool(host=settings.REDIS_HOUSEAD["host"], port=settings.REDIS_HOUSEAD["port"])
            self.r = redis.Redis(connection_pool=poolr)
        except Exception, e:
            print 'redis error!'
            logging.error(time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()) + ":redis has gone away")

    def run(self, myday):
        for data in self.getStatDataByOneDay(myday):
            self.saveStatDataOfOneDay(myday, data)
        return

    def getStatDataByOneDay(self, myday):
        dbconf = settings.MYSQL_MOBGI_HOUSEAD_STAT
        try:
            conn = MySQLdb.connect(host=dbconf["host"], port=dbconf["port"], user=dbconf["user"], passwd=dbconf["passwd"], db=dbconf["db"], charset='utf8', init_command="set names utf8")
        # , cursorclass = MySQLdb.cursors.DictCursor
        except Exception, e:
            self.errlog("Connection db error error:" + str(e))
        result = {}
        try:
            sql = """SELECT originality_id,SUM(click) as clicks,SUM(view) as views,SUM(active) as actives,SUM(amount) as amount FROM `%s`where `date`="%s"  GROUP BY originality_id;
            """ % ("report_base", str(myday))
            promote_cursor = conn.cursor(MySQLdb.cursors.DictCursor)
            promote_cursor.execute(sql)
            result = promote_cursor.fetchall()
        except Exception, e:
            self.errlog("Excute sql err:" + str(e))
        finally:
            promote_cursor.close()

        conn.close()
        return result

    def saveStatDataOfOneDay(self, day, data):

        if len(data) == 0 or data['originality_id'] == 0:
            return
        dbconf = settings.MYSQL_MOBGI_HOUSEAD_STAT

        origInfo = self.getOrigInfo(data['originality_id'])
        if origInfo is None:
            data['ad_unit_id'] = 0
            data['ad_id'] = 0
        else:
            data['ad_unit_id'] = origInfo['unit_id']
            data['ad_id'] = origInfo['ad_id']

        try:
            conn = MySQLdb.connect(host=dbconf["host"], port=dbconf["port"], user=dbconf["user"], passwd=dbconf["passwd"], db=dbconf["db"], charset='utf8', init_command="set names utf8")
            promote_cursor = conn.cursor(MySQLdb.cursors.DictCursor)
        except Exception, e:
            self.errlog("Connection db error error:" + str(e))

        try:
            sql = """INSERT INTO `%s` (`ad_unit_id`,`ad_id`,`originality_id`,`day`,`clicks`,`views`,`actives`,`amount`) VALUES (%s,%s,%s,"%s",%s,%s,%s,%s)
             ON DUPLICATE KEY UPDATE clicks=%s, views=%s,actives=%s,amount=%s""" % (
                "stat_day", data['ad_unit_id'], data['ad_id'], data['originality_id'], day, data['clicks'], data['views'],data['actives'], data['amount'], data['clicks'], data['views'], data['actives'], data['amount'])
            # self.infolog("sql:" + sql)
            # promote_cursor.executemany(sql, data)
            result = promote_cursor.execute(sql)
            if result:
                conn.commit()
        except Exception as e:
            self.errlog("excute sql:%s ERROR:%s" % (sql, str(e)))
        finally:
            promote_cursor.close()
        conn.close()

    def exchangeStrDate(self, strTime, formFormat, toFormat):
        start_timeArray = time.strptime(strTime, formFormat)
        start_timestamp = int(time.mktime(start_timeArray))
        return time.strftime(toFormat, time.localtime(start_timestamp))

    def infolog(self, msg):
        print msg
        logging.info(msg)

    def errlog(self, msg):
        print msg
        logging.error(msg)

    def getOrigInfoFromDb(self, OrigId):
        table = "mobgi_housead.delivery_originality_relation"
        dbConfig = settings.MYSQL_MOBGI_HOUSEAD
        try:
            conn = MySQLdb.connect(host=dbConfig['host'], port=dbConfig['port'], user=dbConfig['user'], passwd=dbConfig['passwd'], db=dbConfig['db'], charset='utf8', init_command="set names utf8",
                                   cursorclass=MySQLdb.cursors.DictCursor)
        except Exception, e:
            self.errlog("Connection db error error:" + str(e))

        sql = "SELECT id,ad_id,unit_id,originality_type,account_id FROM %s WHERE `id` = %s LIMIT 0, 1" % (table, str(OrigId))
        cursor = conn.cursor(MySQLdb.cursors.DictCursor)
        cursor.execute(sql)
        result = cursor.fetchone()
        cursor.close()
        conn.close()
        return result

    def getOrigInfo(self, OrigId):
        if OrigId is None:
            return None
        rkey = settings.REDIS_ORIGINFO + str(OrigId)
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


def is_valid_date(str):
    try:
        time.strptime(str, "%Y-%m-%d")
        return True
    except:
        return False


def date_range(start_date, end_date):
    for n in range(int((end_date - start_date).days)):
        yield start_date + datetime.timedelta(n)


if __name__ == '__main__':
    if len(sys.argv) == 1:
        startday = datetime.date.today() - datetime.timedelta(days=1)
    elif len(sys.argv) == 2:
        argDate = sys.argv[1]
        if is_valid_date(argDate) is False:
            print "Please use valid date as parameter.\n"
            exit()
        startday = datetime.datetime.strptime(argDate, "%Y-%m-%d").date()
    else:
        print "Only one parameter is supported.\n"
        exit()
    stat = Stat()
    today = datetime.datetime.now().date()
    for i in date_range(startday, today):
        stat.run(i.strftime('%Y-%m-%d'))

    # 刷新数据库缓存
    url = "http://apiha.mobgi.com/api/cache/refreshCache?type=4"
    req = urllib2.Request(url)
    res_data = urllib2.urlopen(req)
    res_data.read()
    exit()
