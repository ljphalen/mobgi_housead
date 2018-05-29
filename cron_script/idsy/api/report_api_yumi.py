#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
import hashlib
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiYumi(ReportApiBase):
    endDate = ""

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
            data = self.getRemoteData(repordId)
            if data is not False:
                if result is None:
                    result = {}
                result = dict(result, **data)
        self.info("result：" + str(result))
        return result

    # 获取远程数据
    def getRemoteData(self, repordId):
        #如果大于一周
        url = configApi.YumiApiHost + "developer/" + configApi.YumiDevId + "/app/" + repordId + "/stat?" + self.getUrlParams(self.requestDate)
        self.info("start_data：" + str(self.requestDate) + " request_url: " + url)
        returnResult = self.getJosnData(url)
        if returnResult is False:
            return False
        appResult = {}
        if len(returnResult) > 0:
            for item in returnResult:
                appKey = self.thirdAppMap[repordId]
                platform = self.getPlatformByAppkey(appKey)
                if configApi.YumiType.has_key(item['ad_type']):
                    intergration_type = configApi.YumiType[item['ad_type']]
                else:
                    continue
                key = repordId+ str(intergration_type) + item['date']

                if key in appResult.keys():
                    ad_income = appResult[key]['ad_income'] + float(item['income']) / configApi.EXCHANGE_RATE
                    third_clicks = appResult[key]['third_clicks'] + int(item['click'])
                    third_views = appResult[key]['third_views'] + int(item['exposure'])
                else:
                    ad_income = float(item['income']) / configApi.EXCHANGE_RATE
                    third_clicks = int(item['click'])
                    third_views = int(item['exposure'])

                appResult[repordId + str(intergration_type) + item['date']] = {
                    "ads_id": self.adsId,
                    "app_key": appKey,
                    "ad_type": str(intergration_type),
                    "platform": platform,
                    "days": item['date'],
                    "hours": 0,
                    "ad_income": ad_income,
                    "third_clicks": third_clicks,
                    "third_views": third_views
                }
        else:
            self.saveApiLogData("jsonData is empty", self.adsId, 0, str(self.requestDate))
            self.info("jsonData is empty")
        return appResult

    # 组装参数
    def getUrlParams(self, request_date):
        params = {}
        params['nonce'] = "123"
        params['timestamp'] = int(time.time())
        tmp = params['nonce'] + str(params['timestamp']) + configApi.YumiSecret
        tmp = hashlib.sha1(tmp).hexdigest()
        params['signature'] = hashlib.sha1(
            params['nonce'] + str(params['timestamp']) + configApi.YumiSecret).hexdigest()
        params['start_date'] = request_date
        params['end_date'] = request_date
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, url):
        s_time = time.time()
        try:
            req = urllib2.Request(url)
            res = urllib2.urlopen(req)
        except urllib2.HTTPError, error:
            self.error("response error! status=%s,%s" % (error.code, error.read()))
            return False
        html = res.read()
        status_code = res.getcode()
        jsonData = json.loads(html)
        e_time = time.time()
        use_time = e_time - s_time
        if status_code == 400:
            # 这里要写入一个标记#状态，错误原因，app_key,这个是全局错误，只限于广告商，不限于应用
            self.saveApiLogData("response error! status=400 Bad Request" + str(status_code), self.adsId, 0,self.requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=400 Bad Request")
            return False
        elif status_code == 401:
            self.saveApiLogData("response error! status=401 Unauthorized" + str(status_code), self.adsId, 0, self.requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=401 Unauthorized")
            return False
        elif status_code != 200:
            self.saveApiLogData(" response error! status=" + str(status_code), self.adsId, 0,self.requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=" + str(status_code) + " content=" + html)
            return False
        self.saveApiLogData("-", self.adsId, 1,self.requestDate)
        self.info('use_time=' + str(use_time) + " response success! status=" + str(status_code) + " content=" + html)
        return jsonData['data']

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiYumi('Yumi')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
