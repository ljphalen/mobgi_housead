#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
from report_api_base import ReportApiBase
import config.api as configApi



class ReportApiLoopme(ReportApiBase):
    # thirdAppMap区分广告类型
    distinguishAdType = 1

    # 获取第三方数据
    def getApiData(self, startTime):
        self.thirdAppMap = self.getThirdAppMap()
        if len(self.thirdAppMap) < 1:
            self.error("len(self.thirdAppMap) < 1")
            self.saveApiLogData("len(self.thirdAppMap) < 1", self.adsId, 0, str(self.requestDate))
            exit()
        result = None
        self.requestDate = time.strftime('%Y-%m-%d', time.localtime(startTime))
        for reportId in self.thirdAppMap:
            for adType in self.thirdAppMap[reportId]:
                data = self.getRemoteData(self.requestDate, adType, reportId, self.thirdAppMap[reportId][adType])
                if data is not False:
                    if result is None:
                        result = {}
                    result = dict(result, **data)
        return result

    # 获取远程数据
    def getRemoteData(self, requestDate, adType, reportId, appKey):
        returnResult = self.getJosnData(requestDate, reportId, appKey)
        if returnResult is False:
            return False
        appResult = {}
        for data in returnResult:
            date_of_log = self.exchangeStrDate(data['date'], "%Y-%m-%d %H:%M:%S UTC", '%Y-%m-%d')
            platform = self.getPlatformByAppkey(appKey)
            appResult[str(date_of_log) + str(appKey) + str(adType)] = {
                "ads_id": self.adsId,
                "app_key": appKey,
                "ad_type": adType,
                "days": date_of_log,
                "hours": 0,
                "ad_income": float(data['totals']['Earnings, $']),
                "third_clicks": int(data['totals']['Clicks']),
                "third_views": int(data['totals']['Views']),
                "platform": platform,
            }
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate, appId):
        params = {}
        params['api_auth_token'] = configApi.LoopmeApikey
        params['date_range'] = str(requestDate) + ":" + str(requestDate)
        params['app_key'] = appId
        params['granularity'] = 'day'
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, requestDate, appId, appKey):
        url = configApi.LoopmeApiHost + '?' + self.getUrlParams(requestDate, appId)
        self.info("request_url: " + url)
        s_time = time.time()
        req = urllib2.Request(url)
        res = urllib2.urlopen(req)
        html = res.read()
        status_code = res.getcode()
        jsonData = json.loads(html)
        e_time = time.time()
        use_time = e_time - s_time
        if status_code != 200 or 'series' not in jsonData:
            self.error('use_time=' + str(use_time) + " status=" + str(status_code) + " appKey=" + str(appKey) + " content=" + html + " url=" + url)
            # 这里要写入一个标记#状态，错误原因，app_key,这个是全局错误，只限于广告商，不限于应用
            self.saveApiLogData("response error! status=" + str(status_code)+"or data format error appkey="+ str(appKey), self.adsId, 0, str(requestDate))
        if len(jsonData['series']) == 0:
            self.error('requestDate=' + str(requestDate) + " appId=" + str(appId) + " appKey=" + str(appKey) + " content=" + html + " 数据为空")
            self.saveApiLogData("jsonStr error(string empty)appkey="+str(appKey), self.adsId, 0, str(requestDate))
        else:
            self.info('use_time=' + str(use_time) + " status=" + str(status_code) + " content=" + html)
            self.saveApiLogData("-", self.adsId, 1, str(requestDate))
        return jsonData['series']

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    looptimes = 0
    while 1:
        obj = ReportApiLoopme('Loopme')
        obj.run()
        looptimes += obj.looptimes
        time.sleep(configApi.SLEEP_SECOND)
        # 脚步执行时间超过30分钟直接跳出
        if looptimes > configApi.MAX_LOOP_TIMES or int(time.time() - startTimeStamp) > 1800:
            obj.error('too many times or too long time')
            break
