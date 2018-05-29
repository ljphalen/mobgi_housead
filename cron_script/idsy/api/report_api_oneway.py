#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiOneway(ReportApiBase):
    # thirdAppMap区分广告类型
    distinguishAdType = 0

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
        if len(returnResult) > 0:
            for item in returnResult:
                tmptime = time.localtime(float(str(item['dateTime'])[0:10]))
                mydate = time.strftime('%Y-%m-%d', tmptime)
                myhour = time.strftime('%H', tmptime)
                single_key = appKey + str(mydate) + str(myhour)
                if single_key in appResult.keys():
                    ad_income = appResult[single_key]['ad_income'] + float(item['revenue'])
                    third_clicks = appResult[single_key]['third_clicks'] + int(item['clicks'])
                    third_views = appResult[single_key]['third_views'] + int(item['views'])
                else:
                    ad_income = float(item['revenue'])
                    third_clicks = int(item['clicks'])
                    third_views = int(item['views'])
                appResult[single_key] = {
                    "ads_id": self.adsId,
                    "app_key": appKey,
                    "ad_type": configApi.OnewayType,
                    "platform": item['platform'],
                    "days": mydate,
                    "hours": int(myhour),
                    "ad_income": ad_income,
                    "third_clicks": third_clicks,
                    "third_views": third_views,
                }
        else:
            self.saveApiLogData("results == [] appkey="+appKey, self.adsId, 0,
                                self.requestDate)
            self.info(self.requestDate + "results == []")
        return appResult

    # 组装参数
    def getUrlParams(self, request_date,key):
        params = {}
        params['apikey'] = key
        params['startTime'] = request_date
        params['endTime'] = self.getNextDate(request_date)
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, requestDate, repordId):
        url = configApi.OnewayApiHost + '?' + self.getUrlParams(self.requestDate, repordId)
        self.info("request_url: " + url)
        s_time = time.time()
        try:
            req = urllib2.Request(url)
            res = urllib2.urlopen(req)
        except urllib2.HTTPError, error:
            self.error("response error! status=%s,%s" % (error.code, error.read()))
            return False
        # except:
        #     import sys
        #     print sys.exc_info()

        html = res.read()
        status_code = res.getcode()
        jsonData = json.loads(html)
        e_time = time.time()
        use_time = e_time - s_time
        if status_code == 400:
            # 这里要写入一个标记#状态，错误原因，app_key,这个是全局错误，只限于广告商，不限于应用
            self.saveApiLogData("response error! status=400 Bad Request" + str(status_code), self.adsId, 0,requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=400 Bad Request")
            return False
        elif status_code == 401:
            self.saveApiLogData("response error! status=401 Unauthorized" + str(status_code), self.adsId, 0, requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=401 Unauthorized")
            return False
        elif status_code != 200:
            self.saveApiLogData(" response error! status=" + str(status_code), self.adsId, 0,requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=" + str(status_code) + " content=" + html)
            return False
        self.saveApiLogData("-", self.adsId, 1,requestDate)
        self.info('use_time=' + str(use_time) + " response success! status=" + str(status_code) + " content=" + html)
        return jsonData['data']['rows']

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiOneway('Oneway')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
