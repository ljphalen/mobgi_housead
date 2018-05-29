#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
from report_api_base import ReportApiBase
import config.api as configApi

'''"Vungle(2.0)
    application id": "5799e8c035aa6d8b6e00004d",
    "application name": "buddyman",
    "clicks": 3,
    "country": "CN",
    "date": "2017-09-01",
    "platform": "android",
    "revenue": 1.2,
    "views": 99'''


class ReportApiVungle(ReportApiBase):
    # 获取第三方数据
    def getApiData(self, startTime):
        self.thirdAppMap = self.getThirdAppMap()
        if self.thirdAppMap is None or len(self.thirdAppMap) < 1:
            self.error("len(self.thirdAppMap) < 1")
            self.saveApiLogData("len(self.thirdAppMap) < 1", self.adsId, 0, str(self.requestDate))
            exit()
        self.requestDate = time.strftime('%Y-%m-%d', time.localtime(startTime))
        self.requestDate == '2017-09-24'
        self.info("requestDate：" + str(self.requestDate))
        data = self.getRemoteData()
        self.info("result：" + str(data))
        return data

    # 获取远程数据
    def getRemoteData(self):
        returnResult = self.getJosnData()
        if returnResult is False:
            return False
        posKeyMap = self.getThirdBlockMap()
        appResult = {}
        for data in returnResult:
            if self.thirdAppMap.has_key(data['application id']):
                appKey = self.thirdAppMap[data['application id']]
                platform = self.getPlatformByAppkey(appKey)
                placementId = data['placement id']
                if placementId not in posKeyMap:
                    pos_key = 0
                else:
                    pos_key = posKeyMap[placementId]
                key = appKey + self.requestDate + str(pos_key)
                if key in appResult:
                    revenue = float(appResult[key]['ad_income']) + float(data['revenue'])
                    impressions = int(appResult[key]['third_views']) + int(data['views'])
                    clicks = int(appResult[key]['third_clicks']) + int(data['clicks'])
                else:
                    revenue = float(data['revenue'])
                    impressions = int(data['views'])
                    clicks = int(data['clicks'])

                appResult[key] = {
                    "ads_id": self.adsId,
                    "app_key": appKey,
                    "ad_type": 1,
                    "days": str(data['date']),
                    "hours": 0,
                    "ad_income": revenue,
                    "third_clicks": clicks,
                    "third_views": impressions,
                    "platform": platform,
                    "pos_key": pos_key
                }
            else:
                self.error('Can not find app_key map:' + str(data['application id']))
        return appResult

    # 组装参数
    def getUrlParams(self):
        params = {}
        params['start'] = self.requestDate
        params['end'] = self.requestDate
        params['dimensions'] = 'date,platform,application,placement'
        params['aggregates'] = 'views,clicks,revenue'
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self):
        url = configApi.VungleHost + '?' + self.getUrlParams()
        self.info("request_url: " + url)
        s_time = time.time()
        try:
            req = urllib2.Request(url)
            req.add_header("Authorization", configApi.VungleKey)
            req.add_header("Accept", "application/json")  # 这里通过add_header方法很容易添加的请求头
            req.add_header("Vungle-Version", "1")
            res = urllib2.urlopen(req)
        except urllib2.HTTPError, error:
            self.saveApiLogData("response error! status=%s,%s" % (error.code, error.read()), self.adsId, 0, self.requestDate)
            self.error("response error! status=%s,%s" % (error.code, error.read()))
            return False
        html = res.read()
        status_code = res.getcode()
        jsonData = json.loads(html)
        e_time = time.time()
        use_time = e_time - s_time
        if status_code == 400:
            # 这里要写入一个标记#状态，错误原因，app_key,这个是全局错误，只限于广告商，不限于应用
            self.saveApiLogData("response error! status=400 Bad Request" + str(status_code), self.adsId, 0, self.requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=400 Bad Request")
            return False
        elif status_code == 401:
            self.saveApiLogData("response error! status=401 Unauthorized" + str(status_code), self.adsId, 0, self.requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=401 Unauthorized")
            return False
        elif status_code != 200:
            self.saveApiLogData(" response error! status=" + str(status_code), self.adsId, 0, self.requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=" + str(status_code) + " content=" + html)
            return False
        self.saveApiLogData("-", self.adsId, 1, self.requestDate)
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
