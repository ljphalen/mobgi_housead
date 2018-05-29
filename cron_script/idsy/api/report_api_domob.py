#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiDomob(ReportApiBase):
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
        result = {}
        for key in self.thirdAppMap:
            app_data = self.getRemoteData(str(key))
            if app_data:
                result[self.thirdAppMap[key]] = app_data
        self.info("result：" + str(result))
        return result

    # 获取远程数据
    def getRemoteData(self, repordId):
        returnResult = self.getJosnData(self.exchangeStrDate(self.requestDate, "%Y-%m-%d", "%Y%m%d"), repordId)
        if returnResult is False:
            return False
        if len(returnResult) > 0:
            appKey = self.thirdAppMap[repordId]
            platform = self.getPlatformByAppkey(appKey)
            return {
                "ads_id": self.adsId,
                "app_key": appKey,
                "ad_type": 1,  # video
                "platform": platform,
                "days": self.exchangeStrDate(returnResult["dt"], "%Y%m%d", "%Y-%m-%d"),
                "hours": 0,
                "ad_income": float('%.2f'%(returnResult["revenue"] / 1000000 / configApi.EXCHANGE_RATE)),
                "third_views": int(returnResult["imp_finish"]),
                "third_clicks": int(returnResult["clk"])
            }
        else:
            self.saveApiLogData("jsonData['data'] == []", self.adsId, 0, str(self.requestDate))
            self.info("jsonData['data'] == [] ; return!")

    # 组装参数
    def getUrlParams(self, requestDate):
        params = {}
        params['key'] = configApi.DomobKey
        params['dt'] = str(requestDate)
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, requestDate, repordId):
        url = configApi.DomobApiHost +'/'+ urllib.quote(repordId) +'?' + self.getUrlParams(requestDate)
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
        return jsonData


    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiDomob('Domob')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
