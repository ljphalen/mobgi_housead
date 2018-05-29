#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
from tool_base import ToolBase
import config.db as configDb
import traceback


class toolSpm(ToolBase):
    # 初始化条件
    def init(self):
        self.dbSpm = self.initDb(configDb.MYSQL_MOBGI_SPM)  # spm数据
        self.dataChangeTable = {
            'rainbow_channel_ltv': 'consumer_key,android_channel_no',
            'rainbow_channel_report': 'consumer_key,android_channel_no',
            'rainbow_channel_retention': 'consumer_key,android_channel_no',
            'rainbow_ltv': 'consumer_key,activity_id',
            'rainbow_report': 'consumer_key,activity_id',
            'rainbow_retention': 'consumer_key,activity_id'
        }
        self.initDataMap = self.getInitMap()

    def getInitMap(self):
        self.channelMap = self.getChannelMap()  # monitor_activity
        self.consumerKeyMap = self.getConsumerKeyAppIdMap()  # 去monitor_app
        self.androidChannelNoMap = self.getAndroidChannelNoMap()  # monitor_android_channel
        self.activityIdMap = self.getActivityIdMap()  # monitor_activity

    def getConsumerKeyAppIdMap(self):
        sql = 'select app_id,consumer_key from monitor_app'
        tmp, count = self.dbSpm.fetchall(sql)
        data = {}
        for item in tmp:
            if data.has_key(item['consumer_key']):
                self.info('The consumer_key has repeat:' + str(item['consumer_key']))
                continue
            data[item['consumer_key']] = item['app_id']
        return data

    def getAndroidChannelNoMap(self):
        sql = 'select channel_no,group_id,operator from monitor_android_channel'
        list, count = self.dbSpm.fetchall(sql)
        data = {}
        for item in list:
            channel_no = str(item['channel_no'])
            if len(channel_no) > 1:
                if data.has_key(channel_no):
                    self.info('The consumer_key has repeat:' + str(item['channel_no']))
                    continue
                else:
                    data[channel_no] = {
                        'group_id': item['group_id'],
                        'staff': item['operator']
                    }
            else:
                self.info('###' + channel_no)

        return data

    def getChannelMap(self):
        sql = 'select id,group_id from monitor_channel'
        tmp, count = self.dbSpm.fetchall(sql)
        data = {}
        for item in tmp:
            data[item['id']] = item['group_id']
        return data

    def getActivityIdMap(self):
        sql = 'select id,group_id,operator,channel_id from monitor_activity'
        list, count = self.dbSpm.fetchall(sql)
        data = {}
        for item in list:
            channel_gid = 0
            channel_id = item['channel_id']
            if self.channelMap.has_key(channel_id):
                channel_gid = self.channelMap[channel_id]
            data[item['id']] = {
                'activity_gid': item['group_id'],
                'staff': item['operator'],
                'channel_id': item['channel_id'],
                'channel_gid': channel_gid
            }
        return data

    # 用conusmer_key找app_id
    def checkAppid(self, inputTable):
        sql = "select distinct(consumer_key) from %s where app_id=0 and update_time > '%s'" % (inputTable, self.startTime)
        data, count = self.dbSpm.fetchall(sql)
        for item in data:
            if self.consumerKeyMap.has_key(item['consumer_key']):
                app_id = self.consumerKeyMap[item['consumer_key']]
                sql = "update %s set app_id = %s,update_time = '%s' where consumer_key = '%s' and update_time > '%s' " % (
                    inputTable, app_id, self.nextTime, item['consumer_key'], self.startTime)
                count = self.dbSpm.execute(sql)
                self.info('update count Appid:' + str(count))
            else:
                self.info('consumer_key is None:' + str(item['consumer_key']))

    # 用id找activity_gid,staff（opertar）,channel_id,channel_gid
    def checkActivity(self, inputTable):
        sql = "select distinct(activity_id) from %s where  update_time > '%s' and (channel_id =0 or staff='') " % (inputTable, self.startTime)
        data, count = self.dbSpm.fetchall(sql)
        for item in data:
            if self.activityIdMap.has_key(item['activity_id']):
                map = self.activityIdMap[item['activity_id']]
                sql = "update %s set activity_gid = %s,staff = '%s',channel_id = '%s',channel_gid = %s,update_time = '%s' where activity_id = %s and update_time > '%s'" % (
                    inputTable, map['activity_gid'], map['staff'], map['channel_id'], map['channel_gid'], self.nextTime, item['activity_id'], self.startTime)
                count = self.dbSpm.execute(sql)
                self.info('update count Activity:' + str(count))
            elif int(item['activity_id']) > 0:
                self.info('activity_id is None:' + str(item['activity_id']))

    # 用android_channel_no找android_channel_group_id，staff（opertar）
    def checkChannel(self, inputTable):
        sql = "select distinct(android_channel_no) from %s where update_time > '%s' and ( android_channel_group_id =0  or staff='')" % (inputTable, self.startTime)
        data, count = self.dbSpm.fetchall(sql)
        for item in data:
            if self.androidChannelNoMap.has_key(item['android_channel_no']):
                map = self.androidChannelNoMap[item['android_channel_no']]
                sql = "update %s set android_channel_group_id = %s,staff = '%s',update_time = '%s' where android_channel_no = '%s' and update_time > '%s' " % (
                    inputTable, map['group_id'], map['staff'], self.nextTime, item['android_channel_no'], self.startTime)
                count = self.dbSpm.execute(sql)
                self.info('update count Channel:' + str(count))

    # 获取某个表的更新数据
    def getTableUpdateData(self, tableName, field):
        fields = field.split(',')
        for key in fields:
            if key == 'consumer_key':
                self.checkAppid(tableName)
            elif key == 'activity_id':
                self.checkActivity(tableName)
            else:
                self.checkChannel(tableName)
        return True

    def run(self):
        try:
            self.init()
            self.startPosition, status = self.getStartPosition()  # 获取上次更新的时间戳
            if status == 0:
                self.info('The script is stop!')
                exit()
            self.nextPosition = time.time()
            self.startTime = self.exchangeTimeStampDate(self.startPosition, '%Y-%m-%d %H:%M:%S')
            self.nextTime = self.exchangeTimeStampDate(self.nextPosition, '%Y-%m-%d %H:%M:%S')
            self.info("start time =" + str(self.startTime) + "\tnext time =" + str(self.nextTime))

            for item in self.dataChangeTable:
                self.info("##table:" + item)
                self.getTableUpdateData(item, self.dataChangeTable[item])
            self.info("end time =" + str(self.exchangeTimeStampDate(time.time(), '%Y-%m-%d %H:%M:%S')))
            self.updatePosition()  # 更新位置
        except Exception, e:
            traceback.print_exc()
            self.error("run error:" + str(e))


if __name__ == '__main__':
    obj = toolSpm('tool_spm')
    obj.run()
