#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import MySQLdb
import MySQLdb.cursors
from mylog import mylog


class mysql(object):
    conf = None
    conn = None
    lasttime = 0

    def __init__(self, conf):
        self.conf = conf
        self.connect(conf)

    def __del__(self):
        self.close()

    def close(self):
        if self.conn is not None and hasattr(self.conn, 'open'):
            if getattr(self.conn, 'open') == 1:
                self.conn.close()

    def getConf(self):
        return self.conf

    def getConn(self):
        return self.conn

    def connect(self, conf):
        self.lasttime = time.time()
        self.conn = MySQLdb.connect(host=conf['host'], user=conf['user'], port=conf['port'], passwd=conf['passwd'], db=conf['db'], charset='utf8')
        return self.conn

    def chkConn(self):
        now = time.time()
        if now - self.lasttime > 580:
            self.connect(self.conf)
            # self.conn.ping(True)
            self.lasttime = now

    def fetchall(self, sql, cursorclass=MySQLdb.cursors.DictCursor):
        self.chkConn()
        cursor = self.conn.cursor(cursorclass)
        cursor.execute(sql)
        result = cursor.fetchall()
        count = cursor.rowcount
        cursor.close()
        return result, count

    def fetchone(self, sql, cursorclass=MySQLdb.cursors.DictCursor):
        self.chkConn()
        cursor = self.conn.cursor(cursorclass)
        cursor.execute(sql)
        result = cursor.fetchone()
        cursor.close()
        return result

    def query(self, sql):
        self.chkConn()
        cursor = self.conn.cursor()
        result = cursor.execute(sql)
        cursor.close()
        return result

    def execute(self, sql):
        self.chkConn()
        cursor = self.conn.cursor()
        result = cursor.execute(sql)
        self.conn.commit()
        cursor.close()
        return result

    def insert(self, sql):
        self.chkConn()
        cursor = self.conn.cursor()
        result = cursor.execute(sql)
        last_id = int(self.conn.insert_id())
        self.conn.commit()
        cursor.close()
        return last_id

    def executeMany(self, sql, data):
        self.chkConn()
        from custom_cursors import CustomCursors
        cursor = self.conn.cursor(CustomCursors)
        result = cursor.executemany(sql, data)
        self.conn.commit()
        cursor.close()
        return result
