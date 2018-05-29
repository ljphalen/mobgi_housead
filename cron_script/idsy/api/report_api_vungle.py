#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiVungle(ReportApiBase):
    # 获取第三方数据
    def getApiData(self, startTime):
        self.thirdAppMap = self.getThirdAppMap()
        if self.thirdAppMap is None or len(self.thirdAppMap) < 1:
            self.error("len(self.thirdAppMap) < 1")
            self.saveApiLogData("len(self.thirdAppMap) < 1", self.adsId, 0, str(self.requestDate))
            exit()
        self.requestDate = time.strftime('%Y-%m-%d', time.localtime(startTime))
        self.info("requestDate：" + str(self.requestDate))
        result = None
        for repordId in self.thirdAppMap:
            data = self.getRemoteData(repordId, self.thirdAppMap[repordId])
            if data is not False:
                if result is None:
                    result = {}
                result = dict(result, **data)
        self.info("result：" + str(result))
        return result

    # 获取远程数据
    def getRemoteData(self, repordId, appKey):
        returnResult = self.getJosnData(self.requestDate, repordId)
        if returnResult is False:
            return False
        appResult = {}
        for data in returnResult:
            platform = self.getPlatformByAppkey(appKey)
            key = appKey + self.requestDate
            appResult[key] = {
                "ads_id": self.adsId,
                "app_key": appKey,
                "ad_type": 1,
                "days": str(data['date']),
                "hours": 0,
                "ad_income": float(data['revenue']),
                "third_clicks": int(data['clicks']),
                "third_views": int(data['views']),
                "platform": platform,
            }
        return appResult

    # 抓取数据
    def getJosnData(self, requestDate, repordId):
        url = 'https://ssl.vungle.com/api/applications/'+str(repordId) + "?key=ace296d6c40853ea17857939cbf85f68&date=" + self.requestDate
        self.info("request_url: " + url)
        s_time = time.time()
        try:
            req = urllib2.Request(url)
            req.add_header("Content-Type", "application/json")  # 这里通过add_header方法很容易添加的请求头
            res = urllib2.urlopen(req)
        except urllib2.HTTPError, error:
            self.saveApiLogData("response error! status=%s,%s" % (error.code, error.read()), self.adsId, 0, requestDate)
            self.error("response error! status=%s,%s" % (error.code, error.read()))
            return False
        html = res.read()
        status_code = res.getcode()
        jsonData = json.loads(html)
        e_time = time.time()
        use_time = e_time - s_time
        if status_code == 400:
            # 这里要写入一个标记#状态，错误原因，app_key,这个是全局错误，只限于广告商，不限于应用
            self.saveApiLogData("response error! status=400 Bad Request" + str(status_code), self.adsId, 0, requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=400 Bad Request")
            return False
        elif status_code == 401:
            self.saveApiLogData("response error! status=401 Unauthorized" + str(status_code), self.adsId, 0, requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=401 Unauthorized")
            return False
        elif status_code != 200:
            self.saveApiLogData(" response error! status=" + str(status_code), self.adsId, 0, requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=" + str(status_code) + " content=" + html)
            return False
        self.saveApiLogData("-", self.adsId, 1, requestDate)
        self.info('use_time=' + str(use_time) + " response success! status=" + str(status_code) + " content=" + html)
        return jsonData

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiVungle('Vungle')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
