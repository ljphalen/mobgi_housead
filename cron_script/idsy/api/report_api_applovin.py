#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiApplovin(ReportApiBase):

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
        for val in returnResult:
            if self.thirdAppMap.has_key(val['package_name']):
                appKey = self.thirdAppMap[val['package_name']]
                platform = self.getPlatformByAppkey(appKey)
                hour = int(val['hour'][0:2])
                if val['ad_type'] and val['size'] and configApi.ApplovinTypeMap.has_key(str(val['size']) + '-' + str(val['ad_type'])):
                    intergration_type = configApi.ApplovinTypeMap[str(val['size']) + '-' + str(val['ad_type'])]
                else:
                    if val['size'] and val['ad_type']:
                        self.saveApiLogData("filter ad_type:" + str(val['size']) + '-' + str(val['ad_type']), self.adsId, 0,self.requestDate)
                        self.info("filter ad_type:" + str(val['size']) + '-' + str(val['ad_type']))
                    continue
                key = appKey + val['day'] + ":" + str(hour) + "_" + str(intergration_type)
                if (appResult.has_key(key)):
                    revenue = appResult[key]['ad_income'] + float(val['revenue'])
                    impressions = appResult[key]['third_views'] + int(val['impressions'])
                    clicks = appResult[key]['third_clicks'] + int(val['clicks'])
                else:
                    revenue = float(val['revenue'])
                    impressions = int(val['impressions'])
                    clicks = int(val['clicks'])
                appResult[key] = {
                    "ads_id": self.adsId,
                    "app_key": appKey,
                    "ad_type": intergration_type,
                    "platform": platform,
                    "days": val['day'],
                    "hours": hour,
                    "ad_income": revenue,
                    "third_views": impressions,
                    "third_clicks": clicks
                }
        if len(appResult) < 1:
            self.saveApiLogData("getListDataFromRemote len(result)<1!", self.adsId, 0,
                                self.requestDate)
            self.info("getListDataFromRemote len(result)<1!")
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate):
        params = {}
        params['format'] = 'json'
        params['start'] = str(requestDate)
        params['end'] = str(requestDate)
        params['api_key'] = configApi.ApplovinApiKey
        params['columns'] = 'clicks,day,hour,impressions,package_name,revenue,ad_type,size'
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, requestDate):
        url = configApi.ApplovinHost + '?' + self.getUrlParams(self.requestDate)
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
        return jsonData['results']

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiApplovin('Applovin')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
