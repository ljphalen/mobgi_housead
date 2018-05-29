#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
import base64
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiSupersonic(ReportApiBase):

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
        return data

    # 获取远程数据
    def getRemoteData(self):
        returnResult = self.getJosnData(self.requestDate)
        if returnResult is False:
            return False
        appResult = {}
        for redata in returnResult:
            ad_type = configApi.SupersonicType[redata['adUnits']]
            app_id = str(redata['appId'])
            if self.thirdAppMap.has_key(app_id):
                appKey = self.thirdAppMap[app_id]
                platform = self.getPlatformByAppkey(appKey)
                symbolKey = app_id + redata['date']+str(ad_type)
                for data in redata['data']:
                    if appResult.has_key(symbolKey):
                        # 合并地区统计信息
                        ad_income = appResult[symbolKey]["ad_income"] + data['revenue']
                        third_views = appResult[symbolKey]["third_views"] + data['impressions']
                    else:
                        ad_income = data['revenue']
                        third_views = data['impressions']
                    appResult[symbolKey] = {"ads_id": self.adsId, "app_key": appKey,
                                            "ad_type": ad_type,
                                            "platform": platform, "days": redata['date'], "hours": 0,
                                            "ad_income": ad_income, "third_clicks": 0, "third_views": third_views, }
                #appResult[symbolKey]['ad_income'] = float(appResult[symbolKey]['ad_income'])/100
        if len(appResult) < 1:
            self.saveApiLogData("getListDataFromRemote len(result)<1!", self.adsId, 0,self.requestDate)
            self.info("getListDataFromRemote len(result)<1!")
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate):
        params = {}
        params['startDate'] = requestDate
        params['endDate'] = requestDate
        params['appId'] = ','.join(self.thirdAppMap.keys())
        params['breakdowns'] = 'date,app,country'
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, requestDate):
        url = configApi.SupersonicApiHost + '?' + self.getUrlParams(self.requestDate)
        base64_str = base64.encodestring(configApi.SupersonicAccessKey + ":" + configApi.SupersonicSecretKey).rstrip('\n') #[username]:[secret key]
        self.info("request_url: " + url)
        self.info('Authorization:'+"Basic " + base64_str)
        s_time = time.time()
        try:
            req = urllib2.Request(url)
            req.add_header('Authorization', "Basic " + base64_str)
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
        obj = ReportApiSupersonic('Supersonic')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
