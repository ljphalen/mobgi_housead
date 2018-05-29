#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import codecs
import commands
import urllib2
import json
from ad_base import AdBase
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis
import traceback

LIMIT_COUNTS = configAdx.LIMIT_COUNTS

BIT_LENGTH = configAdx.BIT_LENGTH

IP_HIT = {}
IP_MAP = {}
USER_MAP = {}


##统计中间表
class AdMidUsers(AdBase):
    dataLength = 0
    hours = {}
    apps = None
    channels = None

    dims = configAdx.DIMS_HOUR
    kpis = configAdx.KPIS_HOUR
    # app_date集合
    keymap = {}

    user_pre = 'user::'

    ip_pre = 'ip::'
    ip_hit = 0
    ip_rhit = 0
    ip_new = 0
    ip_time = 0
    ip_rtime = 0

    u_rhit = 0
    u_chk = 0
    u_rtime = 0
    u_time2 = 0
    u_new = 0
    u_hit = 0
    u_htime=0

    def runInit(self):
        self.r = self.initRedis(configRedis.REDIS_MOBGI, 1)
        self.statConfig = configDb.MYSQL_BH_AD_STAT
        self.userConfig = configDb.MYSQL_MOBGI_USER

        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.midConfig = configDb.MYSQL_BH_AD_MID

        self.dbData = self.initDb(self.dataConfig)
        self.dbUserData = self.initDb(self.userConfig)

        self.hourTable = configAdx.TABLE_REPORT_HOUR
        self.clientTable = configAdx.TABLE_STAT_CLIENT
        self.userMidTable = configAdx.TABLE_MID_USERS

        self.clientFields = configAdx.FIELDS[self.clientTable]
        self.midFields = configAdx.FIELDS[self.userMidTable]
        self.initIb()

    def initIb(self):
        self.ib = self.initDb(self.statConfig)

    def closeIb(self):
        self.ib.close()

    def initUserDb(self):
        self.dbUserData = self.initDb(self.userConfig)

    # 检查infobright是否有新数据
    def checkCondition(self, nextPosition):
        if nextPosition < self.lastPosition:
            return True
        sql = "SELECT max(id) as id FROM %s" % (self.clientTable)
        result = self.ib.fetchone(sql)
        # 判断记录是否存在
        if result is None:
            return False
        else:
            result = round(float(result['id']) / float(BIT_LENGTH), 1)
            self.lastPosition = int(result)
            return nextPosition < result

    def getRecordList(self, startPosition, nextPosition):
        startId = startPosition * BIT_LENGTH
        nextId = nextPosition * BIT_LENGTH
        fileds = str(startPosition) + " as id_range,app_key,pos_key,ad_type,ads_id,cid,uuid,platform,session_id,event_type,event_sort," \
                                      "event_time,client_ip,app_version,sdk_version,server_time"
        sql = "SELECT %s FROM %s WHERE id>=%s and id<%s and ver in (1,4) and event_type in %s " % (
            fileds, self.clientTable, startId, nextId, tuple(configAdx.USER_EVENT_TYPE))
        self.info('getRecordList:' + str(startPosition))
        result, self.dataLength = self.ib.fetchall(sql)
        return result, self.dataLength

    def saveRecordData(self, data):
        if data is None:
            self.info("save data is None")
            return False

        file = self.getSqlPath(str(self.startPosition))
        if os.path.isfile(file):
            self.info("a file already exists:" + file)
            os.remove(file)
            # return False
        content = ""
        for item in data:
            if item['event_time'] < 0:
                item['event_time'] = 0
            if item['event_time'] > 14400000:
                item['event_time'] = 14400000

            item['country'], item['province'], item['city'] = self.getCityByIp(item['client_ip'])
            item['gid'] = self.getChannelGid(item['cid'])
            if item['event_type'] == configAdx.EVENT_INIT:
                item['is_custom'] = self.getChannelCustomMap(item['gid'])
            else:
                item['is_custom'] = self.getChannelCustomMap(item['gid'], item['ads_id'])
            item['action_date'], item['action_time'], item['action_hour'], item['server_hour'] = self.formatServerTime(item['server_time'])
            item['user_id'], item['create_date'], item['is_new'] = self.changeUid(item['app_key'], item['uuid'], item['gid'], item['action_date'],
                                                                                  item['server_time'])
            items = []
            for filed in self.midFields:
                items.append(str(item[filed]))
            content += '\t'.join(items) + '\n'
        self.saveDataToFile(file, content)
        self.info("save as file:" + file)
        return file

    def getCityByIp(self, ip):
        # 在这里根据 ip 访问api获取国家和地区信息
        country = '--'
        province = '--'
        city = '--'

        if len(ip) < 7:
            return country, province, city

        if ip.find(',') != -1:
            ip = ip.split(',').strip()
        # 首先判断是否存在内存中
        if ip in IP_MAP:
            [country, province, city] = IP_MAP[ip].split(',')
            self.ip_hit += 1
        else:
            ip_time = time.time()
            ip_key = self.ip_pre + ip
            result = self.r.get(ip_key)
            if result == 'None' or result is None:
                self.ip_new += 1
                [country, province, city] = self.getIpInfo(ip)
                IP_MAP[ip] = country + "," + province + "," + city
                self.r.set(ip_key, IP_MAP[ip], configAdx.IP_CACHE_SECOND)
                self.ip_time += time.time() - ip_time
            else:
                self.ip_rhit += 1
                [country, province, city] = result.split(',')
                IP_MAP[ip] = country + "," + province + "," + city
                self.ip_rtime += time.time() - ip_time
        return country, province, city

    def getUser(self, appKey, uuid, gid, actionDate, serverTime):
        table_id = self.getCutTableId100(uuid)
        userTable = "user_" + str(table_id)
        if time.time() - self.startTimestamp > 59:
            self.initUserDb()
            self.info("initUserDb")
        self.startTimestamp = time.time()
        sql = "SELECT id,create_date,create_time FROM %s WHERE uuid='%s' limit 1" % (userTable, uuid)
        result = self.dbUserData.fetchone(sql)
        if result is None:
            create_time = time.strftime('%H:%M:%S', time.localtime(serverTime))
            insert_sql = "INSERT INTO %s (app_key,uuid,channel_gid,create_date,create_time) values ('%s','%s','%s','%s','%s');" % (
                userTable, appKey, uuid, gid, actionDate, create_time)
            uid = self.dbUserData.insert(insert_sql)
            self.u_new += 1
            return int(uid) * 100 + int(table_id), actionDate, 1
        else:
            self.u_chk += 1
            create_time = time.strftime('%H:%M:%S', time.localtime(serverTime))
            return int(result['id']) * 100 + int(table_id), result['create_date'], int(create_time == str(result['create_time']))

    def hsetUser(self, key_app_date, uuid, uid, create_date):
        data = str(uid) + ',' + str(create_date)
        self.r.hset(key_app_date, uuid, data)

    def hgetUser(self, key_app_date, uuid):
        data = self.r.hget(key_app_date, uuid)
        return data.split(',')

    def changeUid(self, appKey, uuid, gid, actionDate, server_time):
        user_time = time.time()
        user_key = self.user_pre + uuid
        is_new = 0
        # 首先判断是否存在内存中
        if user_key in USER_MAP:
            [uid, create_date] = USER_MAP[user_key].split(',')
            self.u_hit += 1
            self.u_htime += time.time() - user_time  
        else:
            result = self.r.get(user_key)
            if result is None:
                uid, create_date, is_new = self.getUser(appKey, uuid, gid, actionDate, server_time)
                if uid > 0:
                    self.r.set(user_key, str(uid) + ',' + str(create_date), configAdx.USER_CACHE_SECOND)
                else:
                    self.info("can not insert uuid:" + str(uuid))
                self.u_time2 += time.time() - user_time
            else:
                self.u_rhit += 1
                [uid, create_date] = result.split(',')
                self.u_rtime += time.time() - user_time       
            USER_MAP[user_key] = str(uid) + "," + str(create_date)    
        return uid, create_date, is_new

    def loadFileToMid(self, file):
        if file is False or os.path.isfile(file) is False:
            self.info("file does not exist:" + str(file))
            return False
        try:
            self.info('load file:' + file)
            conn = self.midConfig
            loadSql = "LOAD DATA LOCAL INFILE '%s' INTO TABLE %s character set utf8 FIELDS TERMINATED BY '\\t' ENCLOSED BY '' " \
                      "escaped by '' LINES TERMINATED BY '\\n' STARTING BY '' (%s) " % (file, self.userMidTable, ','.join(self.midFields))
            command = configAdx.MYSQL_BIN + " -u%s -p%s  -h%s -D%s -P%s --local-infile=1 -e \"%s\" && rm -rf %s 2>&1" % (
                conn["user"], conn["passwd"], conn["host"], conn["db"], conn["port"], loadSql, file)
            output = commands.getstatusoutput(command)  # 导入成功则删除sql文件
            # output = {0: 0}
            if output[0] != 0:
                self.info("###:" + command)
                self.error('load file failed:' + file)
                return False
            return True
        except Exception, e:
            raise Exception("load data error:" + str(e))

    def delMidData(self, startPosition):
        self.dbMid = self.initDb(self.midConfig)
        self.info("del id_range:" + str(startPosition))
        sql = "delete FROM %s WHERE id_range = %s " % (self.userMidTable, startPosition)
        return self.dbMid.execute(sql)

    def run(self):
        try:
            self.runInit()
            startTimeStamp = time.time()
            self.startPosition, status = self.getStartPosition()
            # 判断状态
            if status != 1:
                self.dataLength = 0
                self.info("status is stop")
                return False

            # 判断是否有新数据
            self.nextPosition = self.startPosition + 1
            if self.checkCondition(self.nextPosition) is not True:
                self.info("Not to start position")
                return False

            ##获取统计
            recordData, self.dataLength = self.getRecordList(self.startPosition, self.nextPosition)
            self.closeIb()
            if self.dataLength > 0:
                self.info("dataLength:" + str(self.dataLength))
                file = self.saveRecordData(recordData)
                self.delMidData(self.startPosition)
                if self.loadFileToMid(file):
                    self.updatePosition()
            else:
                self.info("no recordData")
                self.updatePosition()
            self.info("usetime : " + str(time.time() - startTimeStamp) + "\ttime : " + str(round(self.ip_time + self.u_rtime + self.u_time2, 2)))
            return True
        except Exception, e:
            traceback.print_exc()
            self.error("run error:" + str(e))
        return False


if __name__ == '__main__':
    sleepCount = 0
    max_count = 100
    lastPosition = 0
    while 1:
        obj = AdMidUsers('ad_mid_users')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        ip_len = len(IP_MAP)
        if ip_len > 2000000:
            IP_MAP = {}
        else:
            obj.info('ip len:' + str(ip_len))

        user_len = len(USER_MAP)
        if user_len > 5000000:
            USER_MAP = {}
        else:
            obj.info('user len:' + str(user_len))

            

        obj.lastPosition = lastPosition
        result = obj.run()
        count = obj.dataLength
        lastPosition = obj.lastPosition

        obj.info('ip_hit:' + str(obj.ip_hit) + '\tip_rhit:' + str(obj.ip_rhit) + '\tip_new:' + str(obj.ip_new) + '\tip_time:' + str(
            round(obj.ip_time, 2)) + '\tip_rtime:' + str(round(obj.ip_rtime, 2)))
        obj.info('u_hit:' + str(obj.u_hit) + '\tu_rhit:' + str(obj.u_rhit) + '\tuu_chk:' + str(obj.u_chk) + '\tuu_new:' + str(obj.u_new) + '\tuu_rtime:' + str(
            round(obj.u_rtime, 2)) + '\tuu_time2:' + str(round(obj.u_time2, 2))+ '\tu_htime:' + str(round(obj.u_htime, 2)))
        if obj.u_chk > 0:
            obj.info('sql_time:' + str(round(obj.u_time2 / obj.u_chk, 4)))
        if result is False:
            obj.info("zzz:" + str(count))
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            # time.sleep(1)
            continue
