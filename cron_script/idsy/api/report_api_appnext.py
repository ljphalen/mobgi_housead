#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
import hashlib
import sys
sys.path.append("..")
from lib.myini import myini
from report_api_base import ReportApiBase
import config.api as configApi
import lib.mylog as mylog


class ReportApiAppnext(ReportApiBase):
    # thirdAppMap区分广告类型
    distinguishAdType = 1

    # 获取第三方数据
    def getApiData(self, startTime):
        self.thirdPosMap = self.getThirdBlockMap()
        if len(self.thirdPosMap) < 1:
            self.error("len(self.thirdPosMap) < 1")
            exit()
        result = {}
        self.requestDate = time.strftime('%Y-%m-%d', time.localtime(startTime))
        for reportId in self.thirdPosMap:
            appKey = self.thirdPosMap[reportId]['app_key']
            adType = self.thirdPosMap[reportId]['ad_type']
            data = self.getRemoteData(self.requestDate, adType, reportId, appKey)
            if len(data) > 0:
                result = dict(result, **data)
        return result

    # 获取远程数据
    def getRemoteData(self, requestDate, adType, appId, appKey):
        url = configApi.AppnextApiHost + '/GerReport?' + self.getUrlParams(self.requestDate, appId)
        returnResult = self.getJosnData(url)
        appResult = {}
        incomeMap = {
            '54C8BFBCBEAC92A48B3B':11,
            '9372E882638D9933786F':5,
            '5110be2586e884a9bc61':11,
        }
        if returnResult != []:
            for data in returnResult:
                platform = self.getPlatformByAppkey(appKey)
                date_of_log = requestDate
                appResult[str(date_of_log) + str(appKey)] = {
                    "ads_id": self.adsId,
                    "app_key": appKey,
                    "ad_type": adType,
                    "days": date_of_log,
                    "hours": 0,
                    "ad_income": (int(incomeMap[appKey])*int(data['Impressions']))/1000,
                    "third_clicks": int(data['Clicks']),
                    "third_views": int(data['Impressions']),
                    "platform": platform,
                }
        self.info("start_data：" + str(self.requestDate) + " request_url: " + url)
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate, appId):
        params = {}
        params['sessionId'] = self.getSessionId()
        params['appId'] = appId
        params['reportType'] = 3  # 按app返回
        params['platfromType'] = -1  # -1:所有平台  1:android ,2:ios (目前和后台一致)
        params['dateType'] = 7  # 按具体时间返回
        params['fromDate'] = requestDate
        params['toDate'] = self.getNextDate(requestDate)
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, url):
        s_time = time.time()
        req = urllib2.Request(url)
        res = urllib2.urlopen(req)
        html = res.read()
        status_code = res.getcode()
        jsonData = json.loads(html)
        e_time = time.time()
        use_time = e_time - s_time
        if status_code != 200:
            self.error('use_time=' + str(use_time) + " response error! status=" + str(status_code) + " content=" + html)
            # 这里要写入一个标记#状态，错误原因，app_key,这个是全局错误，只限于广告商，不限于应用
            self.saveApiLogData("response error! status=" + str(status_code) + "or data format error", self.adsId, 0,
                                self.requestDate)
        self.info('use_time=' + str(use_time) + " response success! status=" + str(status_code) + " content=" + html)
        self.saveApiLogData("response error! status=" + str(status_code) + "or data format error", self.adsId, 1,
                            self.requestDate)
        return jsonData

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


    def getSessionId(self):
        params = {}
        params['email'] = configApi.AppnextEmail
        params['password'] = configApi.AppnextPassword
        params['secret'] = configApi.AppnextSecret
        paramsStr = urllib.urlencode(params)
        url = configApi.AppnextApiHost + '/Login?' + paramsStr
        s_time = time.time()
        req = urllib2.Request(url)
        res = urllib2.urlopen(req)
        html = res.read()
        status_code = res.getcode()
        jsonData = json.loads(html)
        e_time = time.time()
        use_time = e_time - s_time
        if status_code != 200:
            self.error('use_time=' + str(use_time) + " status=" + str(status_code) + " content=" + html + " url=" + url)
            return None
        #self.info('use_time=' + str(use_time) + " status=" + str(status_code) + " content=" + html)
        if len(jsonData) == 1 and 'session_id' in jsonData[0]:
            return jsonData[0]['session_id']
        else:
            return None


if __name__ == '__main__':
    startTimeStamp = time.time()
    looptimes=0
    while 1:
        obj = ReportApiAppnext('Appnext')
        obj.run()
        looptimes += obj.looptimes
        time.sleep(configApi.SLEEP_SECOND)
        # 脚步执行时间超过30分钟直接跳出
        if looptimes > configApi.MAX_LOOP_TIMES or int(time.time() - startTimeStamp) > 1800:
            obj.error('too many times or too long time')
            break