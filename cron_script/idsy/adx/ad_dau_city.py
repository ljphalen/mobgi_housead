#!/usr/bin/env python
# coding=utf8
import os
import time
import datetime
from ad_base import AdBase
import config.adx as configAdx
import config.db as configDb


##定时统计新增用户
class AdDauCity(AdBase):
    apps = None

    def init(self):
        self.dims = ['app_key', 'platform', 'is_custom', 'country', 'province', 'days']
        self.kpis = ['new_user', 'total_user', 'user_dau', 'total_init']
        self.newUserTable = configAdx.TABLE_MID_USERS
        self.dauTable = 'report_dau_city'
        self.dataConfig = configDb.MYSQL_MOBGI_DATA
        self.statConfig = configDb.MYSQL_BH_AD_MID

        self.ibUser = self.initDb(self.statConfig)
        self.dbData = self.initDb(self.dataConfig)

    # 获取最后统计表的时间,活跃区分
    def getLastRecordTime(self):
        sql = "select action_date from %s order by action_date desc limit 1" % (self.newUserTable)
        result = self.ibUser.fetchone(sql)
        if result is not None:
            return int(time.mktime(datetime.datetime.strptime(str(result['action_date']), "%Y-%m-%d").timetuple()))
        else:
            return 0

    def checkCondition(self, startPosition):
        today = int(time.mktime(time.strptime(time.strftime('%Y-%m-%d', time.localtime()), "%Y-%m-%d")))
        # today = int(time.mktime(datetime.date.today().timetuple()))
        lastTime = self.getLastRecordTime()
        if startPosition < today and startPosition < lastTime:
            return True
        else:
            return False

    # new_user
    def getNewUserCount(self, startDate):
        try:
            self.info('getNewUserCount')
            eventType = configAdx.EVENT_INIT  # 15事件
            # 分country新增
            sql = """select app_key,is_custom,country,0 as province,count(distinct(user_id)) AS count from %s where create_date='%s' and
            action_date='%s' group by app_key,is_custom,country""" % (self.newUserTable, startDate, startDate)
            list_app, count_country = self.ibUser.fetchall(sql)
            self.info('count_country:' + str(count_country))
            # 分province新增
            sql = """select app_key,is_custom,0 as country,province,count(distinct(user_id)) AS count from %s where create_date='%s' and
            action_date='%s' group by app_key,is_custom,province""" % (self.newUserTable, startDate, startDate)
            list_sdk, count_province = self.ibUser.fetchall(sql)
            self.info('count_province:' + str(count_province))
            # 分country,province新增
            sql = """select app_key,is_custom,country,province,count(distinct(user_id)) AS count from %s where create_date='%s' and
            action_date='%s' group by app_key,is_custom,country,province""" % (self.newUserTable, startDate, startDate)
            list_app_sdk, count_country_province = self.ibUser.fetchall(sql)
            self.info('count_country_province:' + str(count_province))

            return list_app + list_sdk + list_app_sdk
        except Exception, e:
            raise Exception('getRecordList Exception:' + str(e))

    # user_dau
    def getUserDauCount(self, startDate):
        try:
            self.info('getUserDauCount')
            eventType = configAdx.EVENT_INIT  # 15事件
            # 分country新增
            sql = """select app_key,is_custom,country,0 as province,count(distinct(user_id)) AS count from %s where event_type = %s and
            action_date='%s' group by app_key,is_custom,country""" % (self.newUserTable, eventType, startDate)
            list_app, count_country = self.ibUser.fetchall(sql)
            self.info('count_country:' + str(count_country))

            # 分province新增
            sql = """select app_key,is_custom,0 as country,province,count(distinct(user_id)) AS count from %s where event_type = %s and
            action_date='%s' group by app_key,is_custom,province""" % (self.newUserTable, eventType, startDate)
            list_sdk, count_province = self.ibUser.fetchall(sql)
            self.info('count_province:' + str(count_province))

            # 分country,province新增
            sql = """select app_key,is_custom,country,province,count(distinct(user_id)) AS count from %s where event_type = %s and
            action_date='%s' group by app_key,is_custom,country,province""" % (self.newUserTable, eventType, startDate)
            list_app_sdk, count_country_province = self.ibUser.fetchall(sql)
            self.info('count_country_province:' + str(count_province))

            return list_app + list_sdk + list_app_sdk
        except Exception, e:
            raise Exception('getRecordList Exception:' + str(e))

    # total_user
    def getTotalUserCount(self, startDate):
        try:
            self.info('getTotalUserCount')
            eventType = configAdx.EVENT_IMPRESSION  # 5事件
            # 分country新增
            sql = """select app_key,is_custom,country,0 as province,count(distinct(user_id)) AS count from %s where event_type = %s and
            action_date='%s' group by app_key,is_custom,country""" % (self.newUserTable, eventType, startDate)
            list_app, count_country = self.ibUser.fetchall(sql)
            self.info('count_country:' + str(count_country))

            # 分province新增
            sql = """select app_key,is_custom,0 as country,province,count(distinct(user_id)) AS count from %s where event_type = %s and
            action_date='%s' group by app_key,is_custom,province""" % (self.newUserTable, eventType, startDate)
            list_sdk, count_province = self.ibUser.fetchall(sql)
            self.info('count_province:' + str(count_province))

            # 分country,province新增
            sql = """select app_key,is_custom,country,province,count(distinct(user_id)) AS count from %s where event_type = %s and
            action_date='%s' group by app_key,is_custom,country,province""" % (self.newUserTable, eventType, startDate)
            list_app_sdk, count_country_province = self.ibUser.fetchall(sql)
            self.info('count_country_province:' + str(count_province))

            return list_app + list_sdk + list_app_sdk
        except Exception, e:
            raise Exception('getRecordList Exception:' + str(e))

    # total_init
    def getTotalInitCount(self, startDate):
        try:
            self.info('getTotalInitCount')
            eventType = configAdx.EVENT_INIT  # 15事件
            # # 分country新增
            # sql = """select app_key,is_custom,country,0 as province,count(*) AS count from %s where ad_type=0 and event_type = %s and
            # action_date='%s' group by app_key,is_custom,country""" % (self.newUserTable, eventType, startDate)
            # list_app, count_country = self.ibUser.fetchall(sql)
            # self.info('count_country:' + str(count_country))

            # 分province新增
            sql = """select app_key,is_custom,0 as country,province,count(*) AS count from %s where ad_type=0 and event_type = %s and
            action_date='%s' group by app_key,is_custom,province""" % (self.newUserTable, eventType, startDate)
            list_sdk, count_province = self.ibUser.fetchall(sql)
            self.info('count_province:' + str(count_province))

            # 分country,province新增
            sql = """select app_key,is_custom,country,province,count(*) AS count from %s where ad_type=0 and event_type = %s and
            action_date='%s' group by app_key,is_custom,country,province""" % (self.newUserTable, eventType, startDate)
            list_app_sdk, count_country_province = self.ibUser.fetchall(sql)
            self.info('count_country_province:' + str(count_province))

            return list_sdk + list_app_sdk
        except Exception, e:
            raise Exception('getRecordList Exception:' + str(e))

    def paramData(self, data, days):
        result = {}
        for type in data:
            for item in data[type]:
                app_key = item['app_key']
                is_custom = str(item['is_custom'])
                country = str(item['country'])
                province = str(item['province'])
                count = int(item['count'])
                platform = self.getAppPlatform(app_key)
                key = app_key + is_custom + country + province
                if key not in result:
                    result[key] = {
                        'app_key': app_key,
                        'platform': platform,
                        'is_custom': is_custom,
                        'country': country,
                        'province': province,
                        'days': str(days),
                        'new_user': 0,
                        'user_dau': 0,
                        'total_user': 0,
                        'total_init': 0,
                    }
                result[key][type] += count
        return result

    def saveData(self, data):
        if len(data) < 1:
            self.info('saveData len(data) <1')
            return True
        try:
            result = []
            for item in data.values():
                values = []
                for field in self.dims:
                    values.append(str(item[field]))
                for field in self.kpis:
                    values.append(str(item[field]))
                for field in self.kpis:
                    values.append(str(item[field]))
                result.append(tuple(values))

            fields = self.dims + self.kpis
            updateArr = []
            for kpi in self.kpis:
                updateArr.append(kpi + "=%s")
            sql = "insert into %s (%s) values (%s) on duplicate key update %s;" % (
                self.dauTable, ",".join(fields), ("%s," * len(fields))[:-1], ",".join(updateArr))
            self.info('updateReport')
            self.dbData.executeMany(sql, result)
            return True
        except Exception, e:
            raise Exception("saveData error :" + str(e))

    def run(self):
        try:
            self.init()
            startPosition, status = self.getStartPosition()
            self.info("new:" + time.strftime('%Y-%m-%d', time.localtime(startPosition)))
            # 判断状态
            if status != 1:
                self.dataCount = 0
                self.info("status is stop")
                return False
            # 判断是否有新数据
            if self.checkCondition(startPosition) is True:
                # self.info("Condition does not meet")
                # return False
                self.nextPosition = startPosition + 86400
            else:
                self.info("Stat today")
                self.nextPosition = startPosition
                # 解析保存数据

            startDate = datetime.datetime.fromtimestamp(startPosition).date()
            data = {}
            data['new_user'] = self.getNewUserCount(startDate)
            data['user_dau'] = self.getUserDauCount(startDate)
            data['total_user'] = self.getTotalUserCount(startDate)
            data['total_init'] = self.getTotalInitCount(startDate)

            paramData = self.paramData(data, startDate)

            if self.saveData(paramData) is True:
                self.updatePosition()
                self.info("use time : " + str(time.time() - startTimeStamp))
                return self.nextPosition > startPosition
            else:
                self.info("use time : " + str(time.time() - startTimeStamp))
                return False

            return True
        except Exception, e:
            self.error(str(e))


if '__main__' == __name__:
    startTimeStamp = time.time()
    while (1):
        obj = AdDauCity("ad_dau_city")
        if obj.errorFlag:
            obj.info("zzz:" + str(configAdx.SLEEP_SECOND))
            obj = None
            time.sleep(configAdx.SLEEP_SECOND)
            continue
        if obj.run() is not True:
            quit()
        obj.info("zzz:" + str(configAdx.SLEEP_SECOND))
        obj = None
        time.sleep(configAdx.SLEEP_SECOND)
        # 脚步执行时间超过50分钟直接跳出
        if int(time.time() - startTimeStamp) > 3000:
            break
