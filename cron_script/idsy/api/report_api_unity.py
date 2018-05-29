#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
import datetime
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiUnity(ReportApiBase):
    # 获取第三方数据
    def getApiData(self, startTime):
        self.thirdAppMap = self.getThirdAppMap()
        if self.thirdAppMap is None or len(self.thirdAppMap) < 1:
            self.error("len(self.thirdAppMap) < 1")
            self.saveApiLogData("len(self.thirdAppMap) < 1", self.adsId, 0, str(self.requestDate))
            exit()
        self.requestDate = time.strftime('%Y-%m-%d', time.localtime(startTime))
        self.info("requestDate：" + str(self.requestDate))
        data = self.getRemoteData()
        self.info("result：" + str(data))
        if len(data) == 0:
            self.scriptFault = True
        return data

    # 获取远程数据
    def getRemoteData(self):
        returnResult = self.getJosnData()
        if returnResult is False:
            return False
        appResult = {}
        for tmp in returnResult:
            result_data = tmp.split(",")
            ad_income = result_data[3].strip('"')
            if self.thirdAppMap.has_key(result_data[1]):
                appKey = self.thirdAppMap[result_data[1]]
                key = self.thirdAppMap[result_data[1]] + result_data[0]
                platform = self.getPlatformByAppkey(appKey)
                start_timestamp = int(time.mktime(time.strptime(result_data[0].strip('"'), "%Y-%m-%d %H:%M:%S")))
                appResult[key] = {
                    "ads_id": self.adsId,
                    "app_key": appKey,
                    "ad_type": 1,
                    "platform": platform,
                    "days": time.strftime("%Y-%m-%d", time.localtime(start_timestamp)),
                    "hours": int(time.strftime("%H", time.localtime(start_timestamp))),
                    "ad_income": ad_income,
                    "third_views": result_data[4]
                }
        if len(appResult) < 1:
            self.info("getListDataFromRemote len(result)<1!")
        return appResult

    # 组装参数
    def getUrlParams(self):
        utc_st = datetime.datetime.utcfromtimestamp(self.startTime)
        utc_start = utc_st.strftime("%Y-%m-%dGMT%H:00:00Z")
        utc_end = utc_st.strftime("%Y-%m-%dGMT%H:59:59Z")
        url = configApi.UnityHost + configApi.UnityPath + "?apikey=" + configApi.UnityApikey + configApi.UnityOtherParams + "&start=" + utc_start +\
              "&end=" + utc_end
        return url

    # 抓取数据
    def getJosnData(self):
        url = self.getUrlParams()
        self.info("request_url: " + url)
        s_time = time.time()
        try:
            req = urllib2.Request(url)
            res = urllib2.urlopen(req, timeout=60)
        except urllib2.HTTPError, error:
            self.saveApiLogData("response error! status=%s,%s" % (error.code, error.read()), self.adsId, 0, self.requestDate)
            self.error("response error! status=%s,%s" % (error.code, error.read()))
            return False
        # except:
        #     import sys
        #     print sys.exc_info()

        html = res.read()
        status_code = res.getcode()
        jsonData = html.split("\n")
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

        if len(jsonData) > 0:
            self.saveApiLogData("-", self.adsId, 1, self.requestDate)
            self.info('use_time=' + str(use_time) + " response success! status=" + str(status_code) + " content=" + html)
            return jsonData[1:]
        else:
            self.saveApiLogData("response empty!", self.adsId, 0, self.requestDate)
            self.error("response empty!")
            return False

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiUnity('Unity')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
