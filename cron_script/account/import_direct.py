#!/usr/bin/env python
# -*- coding:utf-8 -*-
import time
import datetime
import os
from utils.mysql import MySQL
from settings import config
import MySQLdb
import sys
import csv
import xlrd
import types

reload(sys)
sys.setdefaultencoding('utf8')

 #python import_direct.py ditiepaoku.xlsx 1
 #python import_direct.py shenmiaotaowan2.xlsx 2

class ImportDirect(object):
    """回调广告商通知激活"""
    def __init__(self, filename, filetype):
        self.initdb()
        self.logfileHandle=open(config.LOGPATH + "import_direct.txt", 'a')
        self.import_direct_fieldstr = '`imei`,  `appkey`, `app_interest`, `pay_ability`,  `game_frequency`'
        self.filename = filename
        self.filetype = filetype

    def initdb(self):
        self.db = MySQL(config.MOBGI_HOUSEAD['host'], config.MOBGI_HOUSEAD['user'], config.MYSQL['passwd'], port=config.MOBGI_HOUSEAD['port'], db=config.MOBGI_HOUSEAD['db'])
        return self.db

    def mylog(self,msg):
        print time.strftime("%Y-%m-%d %H:%M:%S",time.localtime())+ " " + msg
        self.logfileHandle.write(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime())+" " + msg + '\n')

    def getRows(self,select_sql):
        try:
            self.db.queryNotCatch(select_sql)
        except MySQLdb.Error as m:
            self.mylog("db reconnect!")
            self.initdb()
            self.db.query(select_sql)
        result = self.db.fetchAll();
        return result

    #新增记录
    def insertRows(self,insert_sql):
        self.cur_sql = insert_sql
        try:
            self.db.queryNotCatch(insert_sql)
            return self.db.commit()
        except MySQLdb.Error as m:
            self.mylog("db reconnect!")
            self.initdb()
            self.db.queryNotCatch(insert_sql)
            return self.db.commit()

    def run(self):
        self.mylog('start ImportDirect.')
        appkey = config.APPKEY[self.filetype]
        self.mylog('filename:' + self.filename)
        data = xlrd.open_workbook(self.filename)
        table = data.sheets()[0]
        nrows = table.nrows #行数
        ncols = table.ncols #列数
        print nrows, ncols
        for i in range(0, nrows):
            imei = table.cell(i,0).value
            if type(imei) is types.FloatType:#科学计数转成int型
                imei = int(imei)
            interest = table.cell(i,1).value
            user_type = table.cell(i,2).value
            pay_type = table.cell(i,3).value

            for key in config.INTEREST:
                # print key, config.INTEREST[key]
                interest = interest.replace(str(key), str(config.INTEREST[key]))

            select_sql = 'select * from delivery_device_direct where imei="' + str(imei) + '" and appkey= "' + appkey +'"';
            result = self.getRows(select_sql)
            if len(result):
                self.mylog('imei:' + str(imei) + ', appkey:' + appkey +  " exist!")
            else:
                curTime = time.time()
                insert_sql = 'insert into delivery_device_direct(`imei`, `appkey`, `app_interest`, `pay_ability`, `game_frequency`, `create_time`, `update_time`) value("'+str(imei)+'", "'+str(appkey)+ '","'+str(interest)+ '","'+str(pay_type)+ '","'+str(user_type)+ '","'+str(curTime)+ '","'+str(curTime)+ '");'
                print insert_sql
                self.insertRows(insert_sql)
            print i


        self.mylog('end ImportDirect.')

if __name__ == '__main__':
    scriptname = sys.argv[0]
    filename = sys.argv[1]
    filetype = sys.argv[2]# 1:地铁跑酷 2:神庙逃亡2
    imporedirect=ImportDirect(filename, filetype)
    imporedirect.run()
