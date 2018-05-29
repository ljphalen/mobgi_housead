#!/usr/bin/env python
# -*- coding:utf-8 -*-


import MySQLdb
import MySQLdb.cursors
from mylog import mylog


class mysql(object):
    conf = None
    conn = None

    def __init__(self, conf):
        self.conf = conf
        self.conn(conf)

    def __del__(self):
        self.close()

    def close(self):
        if self.conn is not None and hasattr(self.conn, 'open'):
            self.conn.close()

    def getConf(self):
        return self.conf

    def getConn(self):
        return self.conn

    def conn(self, conf):
        self.conn = MySQLdb.connect(host=conf['host'], user=conf['user'], port=conf['port'], passwd=conf['passwd'], db=conf['db'], charset='utf8')
        return self.conn

    def fetchall(self, sql, cursorclass=MySQLdb.cursors.DictCursor):
        cursor = self.conn.cursor(cursorclass)
        cursor.execute(sql)
        result = cursor.fetchall()
        count = cursor.rowcount
        cursor.close()
        return result, count

    def fetchone(self, sql, cursorclass=MySQLdb.cursors.DictCursor):
        cursor = self.conn.cursor(cursorclass)
        cursor.execute(sql)
        result = cursor.fetchone()
        cursor.close()
        return result

    def query(self, sql):
        cursor = self.conn.cursor()
        result = cursor.execute(sql)
        cursor.close()
        return result

    def execute(self, sql):
        cursor = self.conn.cursor()
        result = cursor.execute(sql)
        self.conn.commit()
        cursor.close()
        return result

    def executeMany(self, sql, data):
        from custom_cursors import CustomCursors
        cursor = self.conn.cursor(CustomCursors)
        result = cursor.executemany(sql, data)
        self.conn.commit()
        cursor.close()
        return result
