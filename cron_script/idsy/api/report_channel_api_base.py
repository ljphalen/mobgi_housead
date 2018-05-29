#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import datetime
import commands

sys.path.append('..')
import config.api as configApi
import config.db as configDb
from lib.mybase import Base
import lib.mysql as db
import lib.mylog as mylog
import hashlib

default_encoding = 'utf-8'
if sys.getdefaultencoding() != default_encoding:
    reload(sys)
    sys.setdefaultencoding(default_encoding)

def CheckTask(argv):#如果传递参数则为监控回滚配置
    if len(argv) !=2:
        return False
    time_length = int(argv[1])
    return  time_length

class ReportChannelApiBase(Base):
    # appMap是否区分广告类型
    distinguishAdType = 0
    # 广告商配置前缀
    scriptName = None
    # 出现错误不更新时间开关
    scriptFault = False
    adsId = None
    log = None
    startPostion = 0
    lastPostion = 0
    looptimes = 1
    double_check_flag = False #区分最后数据是否落地还是使用热数据进行doublecheck比较

    def init(self):
        self.adsId = self.scriptName
        self.adsTable = configApi.ADS_TABLE
        self.dbConf = db.mysql(configDb.MYSQL_MOBGI_DATA)
        self.dbOldConf = db.mysql(configDb.MYSQL_MOBGI_DATA_OLD)

    def getPlatformByAppkey(self, appKey):
        sql = "select app_key,platform from config_app where app_key='" + appKey + "'"
        result = self.dbConf.fetchone(sql)
        if result is None:
            platform = -1
        else:
            platform = result["platform"]
        return platform

    # <matt>获取时间的时候需要将格式转化为时间戳，存入时间需要将时间在转化为固定格式
    def getApiInfo(self):
        sql = "select is_doublecheck,next_time as start_time,period,status from %s where identifier = '%s';" % (self.adsTable, self.scriptName)
        result = self.dbConf.fetchone(sql)
        if result['start_time'] is not None:
            result['start_time'] = int(time.mktime(time.strptime(str(result['start_time']), "%Y-%m-%d %H:%M:%S")))
        else:  # <matt>
            result['start_time'] = int(time.time() - 223200)
        return result

    #doublecheck回滚重跑时间
    def changeStartPosition(self,time_length):
        now_position  = self.getApiInfo()
        reload_timestamp = now_position['start_time'] - 86400*time_length
        reload_position = time.strftime("%Y-%m-%d %H:%M:%S",time.localtime(reload_timestamp))
        self.info('reload DATE:'+reload_position)
        sql = "update %s set next_time = '%s' where identifier = '%s'"%(self.adsTable,reload_position,self.adsId)
        return self.dbConf.execute(sql) and self.changeDoubleCheckStatus(1)

    #doublecheck状态切换
    def changeDoubleCheckStatus(self,status):
        sql = "update %s set is_doublecheck = %s where identifier = '%s'"%(self.adsTable,status,self.adsId)
        return self.dbConf.execute(sql)


    #检查脚本是否处于doubleCheck状态
    def isDoubleCheck(self,apiInfo):
        if apiInfo['is_doublecheck'] == 1:
            self.double_check_flag = True
            return True
        else:
            return False

    def updateApi(self, nextTime):
        try:
            if nextTime <= self.startTime:
                self.info("lastPostion no change ")
                return False
            nextTimeToFomat = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(nextTime))
            lastTimeToFomat = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(self.startTime))
            sql = "update %s set next_time='%s',last_time='%s' where identifier ='%s';" % (
                self.adsTable, str(nextTimeToFomat), str(lastTimeToFomat), self.scriptName)
            self.dbConf.execute(sql)
            self.info("lastPostion:" + str(nextTime))
            return True
        except Exception, e:
            self.error("update Position fail :" + str(e))
            return False

    def checkCondition(self, info):
        # 判断状态
        if info is None:
            self.info("info is none")
            return False
        # 判断状态
        if info['status'] != 1:
            self.info("status is stop")
            return False
        # 判断时间
        if info['start_time'] + info['period'] > time.time():
            if self.double_check_flag is True:
                self.changeDoubleCheckStatus(0)#关闭double_check状态
            self.info("Not to the start time")
            return False
        return True

    # 获取第三方app映射
    def getThirdAppMap(self):
        sql = "select report_id,app_key,ad_type from config_ads_app where ads_id = '%s';" % (self.adsId)
        list, count = self.dbConf.fetchall(sql)
        if count == 0:
            return None
        result = {}
        if self.distinguishAdType:
            for item in list:
                if item['report_id'] in result:
                    self.error("the report_id have exists" + item['report_id'])
                else:
                    result.update({
                        item['report_id']: {
                            item['ad_type']: item['app_key']
                        }
                    })
        else:
            for item in list:
                result[item['report_id']] = item['app_key']
        return result
        # 获取第三方app广告位映射


    # 获取广告类型和app映射
    def getThirdAppAdTypeMap(self):
        sql = "select app_key,ad_type from config_ads_app where ads_id = '%s';" % (self.adsId)
        list, count = self.dbConf.fetchall(sql)
        result = {}
        for item in list:
            result[item['app_key']] = item['ad_type']
        return result


    def getThirdBlockMap(self):
        sql = "select app_key,ad_type,pos_key,third_pos_key from config_ads_pos where ads_id = '%s';" % (self.adsId)
        list, count = self.dbConf.fetchall(sql)
        if count == 0:
            return None
        result = {}
        for item in list:
            if item['third_pos_key'] in result:
                continue
            else:
                result.update({
                    item['third_pos_key'].strip('\t'): {
                        'ad_type': item['ad_type'],
                        'pos_key': item['pos_key'],
                        'app_key': item['app_key']
                    }
                })
        return result



    def addtwodimdict(self, thedict, key_a, key_b, val):
        if key_a in thedict:
            thedict[key_a].update({
                key_b: val
            })
        else:
            thedict.update({
                key_a: {
                    key_b: val
                }
            })

    def formatApiData(self, listData):
        if len(listData) < 1:
            return []
        result = []
        for item in listData.values():
            # 有些第三方没有没有下面字段的，初始化值
            if 'pos_key' not in item:
                item["pos_key"] = str(0)
            if 'third_clicks' not in item:
                item["third_clicks"] = 0
            if 'third_views' not in item:
                item["third_clicks"] = 0
            if 'is_custom' not in item:
                item['is_custom'] = 1
            if 'channel_gid' not in item:
                item['channel_gid'] = self.getChannelGid(item['ads_id'])
            values = (
                item["app_key"], item["platform"], item['is_custom'],int(item['channel_gid']),item["ads_id"],item["ad_type"], item["days"],
                item["third_views"], item["third_clicks"], item["ad_income"],item["third_views"], item["third_clicks"],item["ad_income"])
            result.append(values)
        return tuple(result)



    def getChannelGid(self,adsId):
        sql = "select channel_id from config_channels where ads_id = '%s' and group_id = 0"%(adsId)
        channelInfo = self.dbConf.fetchone(sql)
        return channelInfo['channel_id']

    # saveData调整，分为旧版本存入global,新增三方留存表report_third_data
    def saveData(self, apiData):
        data = self.formatApiData(apiData)
        if len(data) < 1:
            return False
        if self.double_check_flag is True:
            self.compareData(data)
            return 1
        if self.saveChannelData(data):
            return 1
        else:
            return 0

    #监控比较三方数据
    def compareData(self,data):
        check_clicks = check_views = check_income = 0
        for item in data:
            check_clicks +=item[-1]
            check_views +=item[-2]
            check_income +=item[-3]
        sql = "select sum(ad_income) as ad_income,sum(third_clicks) as third_clicks,sum(third_views) as third_views from report_third_data where days = '%s' and ads_id = '%s'"\
        %(time.strftime("%Y-%m-%d",time.localtime(self.startTime)),self.adsId)
        old_data = self.dbConf.fetchone(sql)
        if old_data['ad_income'] is not None:
            diff_range_clicks = check_clicks - int(old_data['third_clicks'])
            diff_range_views = check_views - int(old_data['third_views'])
            diff_range_income = int(check_income)- int(old_data['ad_income'])
            if diff_range_income or diff_range_clicks or diff_range_views:
                tmp_data_check = str(check_income) + '_' + str(check_views) + '_' + str(check_clicks)
                tmp_data_old = str(old_data['ad_income']) + '_' + str(old_data['third_views']) + str(old_data['third_clicks'])
                self.info('DoubleCheckInfo:ads_id:'+self.adsId+'days:'+time.strftime("%Y-%m-%d",time.localtime(self.startTime))+'old_info:'+tmp_data_old+'check_info:'+tmp_data_check)
                #self.saveMonitorLog(tmp_data_check,tmp_data_old)
                return self.saveChannelData(data)
            else:
                return True
        else:
            #如果是None，那么直接将新数据写入
            return self.saveChannelData(data)


    def saveChannelData(self, data):
        sql = """insert into report_finance(app_key,platform,is_custom,channel_gid,ads_id,ad_type,days,third_views,third_clicks,ad_income)values
        (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)on duplicate key update third_views=%s,third_clicks=%s,ad_income=%s"""
        self.info("data:" + str(data))
        return self.dbConf.executeMany(sql, data)

    # matt<log第三方log系统接入>
    def saveApiLogData(self, msg, ads, status, errordate):
        createtime = time.strftime("%Y-%m-%d %H:%M:%S")
        date = time.strftime("%Y-%m-%d")
        check_sql = """select * from report_third_monitor_log where date= '%s' and ads_id='%s' and status = %s and error_date = '%s'""" % (
            date, ads, status, errordate)
        result = self.dbConf.execute(check_sql)
        # 如果存在就更新，不存在就insert
        if result:
            sql = """update report_third_monitor_log set status=%s,msg='%s',createtime='%s' where ads_id='%s' and error_date = '%s'""" % (
                status, msg, createtime, ads, errordate)
        else:
            sql = """insert into report_third_monitor_log(ads_id,error_date,status,createtime,msg,date)values('%s','%s',%s,'%s','%s',
            '%s')""" % (ads, errordate, status, createtime, msg, date)
        return self.dbConf.execute(sql)


    def exchangeStrDate(self, strTime, formFormat, toFormat):
        start_timeArray = time.strptime(strTime, formFormat)
        start_timestamp = int(time.mktime(start_timeArray))
        return time.strftime(toFormat, time.localtime(start_timestamp))


    def exchangeTimeStamp(self,date,formFormat):
        start_timeArray = time.strptime(date, formFormat)
        return int(time.mktime(start_timeArray))


    def run(self):
        try:
            time_length = CheckTask(sys.argv)
            startTimeStamp = time.time()
            apiInfo = self.getApiInfo()
            #检查是否在doublecheck状态
            if self.isDoubleCheck(apiInfo) is False and time_length is not False:#double_check
                # 进入比较数据程序
                self.changeStartPosition(time_length)
                apiInfo = self.getApiInfo() #重新获取
            if self.checkCondition(apiInfo) is False:
                quit()
            self.startTime = apiInfo['start_time']  #strattime为next_time
            apiData = self.getApiData(self.startTime)
            if apiData is None or len(apiData) == 0:
                # try again
                time.sleep(1)
                apiData = self.getApiData(self.startTime)
            if len(apiData) > 0:
                if self.saveData(apiData) >= 0:
                    self.looptimes = 0
                    # 如果存在数据不完整的重跑默认只等待一天，一天过后如果还是没有跑出来则自动忽略跑下一天
                    if self.scriptFault is False or time.time() < (apiInfo['start_time'] + apiInfo['period']):
                        self.updateApi(apiInfo['start_time'] + apiInfo['period'])
                    else:
                        self.error("Data exception and run next hour!")
                        quit()
                else:
                    self.saveApiLogData("Save Data Failed", self.adsId, 0, time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(self.startTime)))
                    self.error("Save Data Failed")
            elif apiData is not None and len(apiData) == 0:
                self.looptimes = 0
                if self.scriptFault is False or time.time() < (apiInfo['start_time'] + apiInfo['period']):
                    self.updateApi(apiInfo['start_time'] + apiInfo['period'])
                else:
                    self.error("Data exception and run next hour!")
                    quit()
                self.info("Data=[]")
            else:
                self.error("====")
            self.info("use time : " + str(time.time() - startTimeStamp))

        except Exception, e:
            # self.saveApiLogData("run error" + str(e), self.adsId, 0, time.strftime('%Y-%m-%d',time.localtime(time.time())))
            self.error("run error:" + str(e))
            quit()
