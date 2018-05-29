#!/usr/bin/env python
# -*- coding:utf-8 -*-
import time
import datetime
import os
from settings import config
from utils.mysql import MySQL
import MySQLdb
import redis
import sys

reload(sys)
sys.setdefaultencoding('utf-8')

class RecoveryExpiredVirtualMoney(object):
    """回调广告商通知激活"""
    def __init__(self):
        self.initdb()
        self.logfileHandle=open(config.LOGPATH + "recovery_expired_virtualmoney_setting.txt", 'a')

    def initdb(self):
        self.db = MySQL(config.MOBGI_HOUSEAD['host'], config.MOBGI_HOUSEAD['user'], config.MOBGI_HOUSEAD['passwd'], port=config.MOBGI_HOUSEAD['port'], db=config.MOBGI_HOUSEAD['db'])
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
        try:
            self.db.queryNotCatch(insert_sql)
            return self.db.commit()
        except MySQLdb.Error as m:
            self.mylog("db reconnect!")
            self.initdb()
            self.db.query(insert_sql)
            return self.db.commit()

    #更新记录
    def updateRows(self,update_sql):
        try:
            self.db.queryNotCatch(update_sql)
            return self.db.commit()
        except MySQLdb.Error as m:
            self.mylog("db reconnect!")
            self.initdb()
            self.db.query(update_sql)
            return self.db.commit()

    def getExpiredVirtualdetail(self):
        curtime = str(time.time())
        expired_sql = "select * from advertiser_account_virtual_detail where status='normal' and expire_time <  '" + curtime + "'"
        expired_result = self.getRows(expired_sql)
        return expired_result

    def getAccount(self, uid, account_type):
        account_sql = "select * from advertiser_account_detail where uid= '" + str(uid) + "' and account_type='" + account_type +"'"
        account_result = self.getRows(account_sql)
        if len(account_result) == 0:
            self.mylog('cant get account_result. sql:' + account_sql)
            return False
        else:
            return account_result[0]

    def run(self):
        self.mylog('start RecoveryExpiredVirtualMoney.')

        self.expired_records = self.getExpiredVirtualdetail()

        if len(self.expired_records) == 0:
            self.mylog('len(self.expired_records) == 0')
            
            self.mylog('sleep ' + str(config.SLEEPSECONDS) + ' seconds!\n')
            time.sleep(config.SLEEPSECONDS)
            return False;
        curtime = time.time()
        self.mylog('curtime:' + str(curtime))
        if config.REDIS.has_key('password') is True:
            poolr=redis.ConnectionPool(host=config.REDIS["host"],port=config.REDIS["port"],password=config.REDIS["password"])
        else:
            poolr=redis.ConnectionPool(host=config.REDIS["host"],port=config.REDIS["port"])
        r=redis.Redis(connection_pool=poolr)

        for item in self.expired_records:
            accountdetail = self.getAccount(item['uid'], item['account_type'])
            self.mylog('account detail: '  + 'uid:' + accountdetail['uid'] + ", account_type:" + accountdetail['account_type']+ ", balance:" + accountdetail['balance'])
            self.mylog( 'account virtual detail: ' + 'id:' + item['id'] + ", uid:" + item['uid']+ ", account_type:" + item['account_type']+ ", balance:" + item['balance']+ ", status:" + item['status']+ ", taskdetailid:" + item['taskdetailid'] + ", expire_time:" + item['expire_time'])
            if float(accountdetail['balance'])<float(item['balance']):
                new_balance = 0
                real_fee = float(accountdetail['balance'])
            else:
                new_balance = float(accountdetail['balance']) - float(item['balance'])
                real_fee = float(item['balance'])
            #(1)更改虚拟帐呢余额
            update_account_detail_sql = 'update advertiser_account_detail set balance = "' + str(new_balance) + '" where uid="' + str(accountdetail['uid']) + '" and account_type="' +accountdetail['account_type'] + '"'
            self.updateRows(update_account_detail_sql)
            self.mylog('real_fee:'+ str(real_fee) + ', new_balance:' + str(new_balance) )
            self.mylog('update_account_detail_sql:' + update_account_detail_sql)
            #(2)更改帐户虚拟金过期状态
            update_virtual_detail_sql = 'update advertiser_account_virtual_detail set status = "expired", operator="py_script",update_time="' + str(curtime) + '" where id="' + str(item['id'] + '"')
            self.updateRows(update_virtual_detail_sql)
            self.mylog('update_virtual_detail_sql:' + update_virtual_detail_sql)
            #(3)添加帐户流水
            account_log_value_str = "'" + item['uid'] + "', '" + item['account_type'] + "', 'recovery', '" + str(real_fee) + "', '虚拟金过期回收', '" + str(curtime)+ "'"
            add_account_log_sql = 'insert into advertiser_account_log(uid, account_type, operate_type, trade_balance, description, create_time) value(' +account_log_value_str+ ')'
            self.updateRows('set names utf8;')#处理编码问题
            self.insertRows(add_account_log_sql)
            self.mylog('add_account_log_sql:' + add_account_log_sql)
            #(4)删除帐户余额缓存
            total_balance_rediskey = REDIS.REDIS_CACHEKEY['ACCOUNT_TOTAL_BALANCE'] + '_'+ item['uid']
            r.delete(total_balance_rediskey)
            self.mylog('delete redis cache:' + total_balance_rediskey + "\n")
        self.mylog('end RecoveryExpiredVirtualMoney.')

if __name__ == '__main__':
    while 1 :
		recoveryexpiredvirtualmoney=RecoveryExpiredVirtualMoney()
		recoveryexpiredvirtualmoney.run()


