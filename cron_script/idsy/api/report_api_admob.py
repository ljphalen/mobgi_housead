#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
import hashlib
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiAdMob(ReportApiBase):

    # 获取第三方数据
    def getApiData(self, startTime):
        self.thirdPosMap = self.getThirdBlockMap()
        if self.thirdPosMap is None or len(self.thirdPosMap) < 1:
            self.error("len(self.thirdAppMap) < 1")
            self.saveApiLogData("len(self.thirdAppMap) < 1", self.adsId, 0, str(self.requestDate))
            exit()
        self.requestDate = time.strftime('%Y-%m-%d', time.localtime(startTime))
        self.info("requestDate：" + str(self.requestDate))
        data = self.getRemoteData()
        if data is False:
            self.saveApiLogData("return False error!", self.adsId, 0, str(self.requestDate))
            self.error("return False error!")
            exit()
        else:
            self.info("result：" + str(data))
            return data

    # 获取远程数据
    def getRemoteData(self):
        returnResult = self.getJosnData(self.requestDate)
        if returnResult is False:
            return False
        appResult = {}
        for val in returnResult:
            report_id = str(val[0]).replace(':','/')
            if self.thirdPosMap.has_key(report_id):
                appKey = self.thirdPosMap[report_id]['app_key']
                platform = self.getPlatformByAppkey(appKey)
                posKey = self.thirdPosMap[report_id]['pos_key']
                adType = self.thirdPosMap[report_id]['ad_type']
                appResult[report_id + str(self.requestDate)+posKey] = {
                    "ads_id": self.adsId,
                    "app_key": appKey,
                    "ad_type": adType,
                    "platform": platform,
                    "days": self.requestDate,
                    "hours": 0,
                    "pos_key":posKey,
                    "ad_income": float(val[2]),
                    "third_clicks": int(val[3]),
                    "third_views": int(val[4])
                }
            else:
                self.saveApiLogData("REPORT ID NOT FOUND report_id="+str(val[0]), self.adsId, 0,self.requestDate)
        if len(appResult) < 1:
            self.saveApiLogData("getListDataFromRemote len(result)<1!", self.adsId, 0,self.requestDate)
            self.info("getListDataFromRemote len(result)<1!")
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate):
        params = {}
        m = hashlib.md5()
        m.update(configApi.AdmobKey+'_'+self.requestDate)
        params['encstr'] = m.hexdigest()
        params['date'] = requestDate
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, requestDate):
        url = configApi.AdmobApiHost + '?' + self.getUrlParams(self.requestDate)
        self.info("request_url: " + url)
        s_time = time.time()
        try:
            req = urllib2.Request(url)
            res = urllib2.urlopen(req)
        except urllib2.HTTPError, error:
            self.saveApiLogData("response error! status=%s,%s" % (error.code, error.read()), self.adsId, 0, str(self.requestDate))
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
            self.saveApiLogData("response error! status=" + str(status_code), self.adsId, 0,requestDate)
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
        obj = ReportApiAdMob('AdMob')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
