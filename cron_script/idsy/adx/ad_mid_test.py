#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import codecs
import commands
from ad_base import AdBase
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis
import traceback

LIMIT_COUNTS = configAdx.LIMIT_COUNTS

BIT_LENGTH = configAdx.BIT_LENGTH


##AbTest中间表
class AdMidTest(AdBase):
    dataLength = 0
    hours = {}
    apps = None
    channels = None
    user_pre = 'user::'
    u_time2 = 0
    u_rhit = 0
    u_rtime = 0
    u_new = 0
    u_chk = 0
    key_pre = 'adx_abTest_tmp_'

    def runInit(self):
        self.r = self.initRedis(configRedis.REDIS_MOBGI)
        self.statConfig = configDb.MYSQL_BH_AD_STAT
        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.midConfig = configDb.MYSQL_BH_AD_MID
        self.userConfig = configDb.MYSQL_MOBGI_USER
        self.dataApiConfig = configDb.MYSQL_MOBGI_API

        self.dbData = self.initDb(self.dataConfig)
        self.dbApi = self.initDb(self.dataApiConfig)
        self.dbMid = self.initDb(self.midConfig)
        self.dbUserData = self.initDb(self.userConfig)

        self.testTable = configAdx.TABLE_REPORT_TEST
        self.clientTable = configAdx.TABLE_STAT_CLIENT
        self.midTable = configAdx.TABLE_MID_TEST
        self.confTable = 'ab_conf_rel'

        self.clientFields = configAdx.FIELDS[self.clientTable]
        self.midFields = configAdx.FIELDS[self.midTable]
        self.initIb()

    def initIb(self):
        self.ib = self.initDb(self.statConfig)


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
        fileds = " as id_range,ssp_id,ads_id,app_key,pos_key,ad_type,cid,platform,brand,model,operator,net_type,event_type,uuid,imei,out_bit_id,app_version,sdk_version,config_id,user_type,session_id," \
                 "event_value,event_time,server_time,event_sort"
        fileds = str(startPosition) + fileds
        #groupby = "ssp_id,ads_id,app_key,pos_key,ad_type,cid,platform,event_type,app_version,sdk_version,FROM_UNIXTIME(server_time,'%Y-%m-%d %H')"
        sql = "SELECT %s FROM %s WHERE id >=%s and id<%s and ver in (1,4) and user_type = 1" % (
            fileds, self.clientTable, str(startId), str(nextId))
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
            if item['config_id'] > 0:
                item['conf_id'], item['flow_id'] = self.getDataByApi(item['config_id'])
            else:
                item['flow_id'] = 0
                item['conf_id'] = 0
            item['gid'] = self.getChannelGid(item['cid'])
            if item['event_type'] == configAdx.EVENT_INIT:
                item['is_custom'] = self.getChannelCustomMap(item['gid'])
            else:
                item['is_custom'] = self.getChannelCustomMap(item['gid'], item['ads_id'])
            item['action_date'], item['action_time'], item['action_hour'], item['server_hour'] = self.formatServerTime(item['server_time'])

            item['user_id'], item['create_date'],is_new= self.changeUid(item['app_key'], item['uuid'],
                                                                                  item['gid'], item['action_date'],
                                                                                  item['server_time'])
            item['server_time'] = item['server_hour']
            items = []
            for filed in self.midFields:
                items.append(str(item[filed]))
            content += '\t'.join(items) + '\n'
        self.saveDataToFile(file, content)
        self.info("save as file:" + file)
        return file

    def changeUid(self, appKey, uuid, gid, actionDate, server_time):
        user_time = time.time()
        user_key = self.user_pre + uuid
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
            is_new = 0
        return uid, create_date, is_new

    def getDataByApi(self,id):
        sql = "select conf_id,flow_id from %s where id = %s limit 1" % (self.confTable, id)
        info = self.dbApi.fetchone(sql)
        if info is not None:
            conf_id, flow_id = info['conf_id'], info['flow_id']
        else:
            conf_id = 0
            flow_id = 0
        return conf_id,flow_id


    def loadFileToMid(self, file):
        if file is False or os.path.isfile(file) is False:
            self.info("file does not exist:" + str(file))
            return False
        try:
            self.info('load file:' + file)
            conn = self.midConfig
            loadSql = "LOAD DATA LOCAL INFILE '%s' INTO TABLE %s character set utf8 FIELDS TERMINATED BY '\\t' ENCLOSED BY '' " \
                      "escaped by '' LINES TERMINATED BY '\\n' STARTING BY '' (%s) " % (file, self.midTable, ','.join(self.midFields))
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
        sql = "delete FROM %s WHERE id_range = %s " % (self.midTable, startPosition)
        return self.dbMid.execute(sql)

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
            return int(result['id']) * 100 + int(table_id), result['create_date'], int(
                create_time == str(result['create_time']))

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
            if self.dataLength > 0:
                file = self.saveRecordData(recordData)
                if configAdx.IS_PROD:
                    self.delMidData(self.startPosition)
                if self.loadFileToMid(file):
                    self.updatePosition()
            else:
                self.info("no recordData")
                self.updatePosition()
            self.info("use time : " + str(time.time() - startTimeStamp))
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
        obj = AdMidTest('ad_mid_test')
        if obj.errorFlag:
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            exit()
        obj.lastPosition = lastPosition
        result = obj.run()
        count = obj.dataLength
        lastPosition = obj.lastPosition
        if result is False:
            obj.info("quit:" + str(count))
            obj = None
            # time.sleep(configAdx.SLEEP_SECOND)
            # time.sleep(1)
            quit()
