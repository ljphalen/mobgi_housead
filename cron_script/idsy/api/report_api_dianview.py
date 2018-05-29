#!/usr/bin/env python
# -*- coding:utf-8 -*-
import sys
import time
import urllib
import urllib2
import json
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiDianview(ReportApiBase):
    # 获取第三方数据
    def getApiData(self, startTime):
        self.ThirdAppMap = self.getThirdAppMap()
        if self.ThirdAppMap is None or len(self.ThirdAppMap) < 1:
            self.error("len(self.ThirdAppMap) < 1")
            self.saveApiLogData("len(self.thirdAppMap) < 1", self.adsId, 0, str(self.requestDate))
            exit()
        self.requestDate = time.strftime('%Y-%m-%d', time.localtime(startTime))
        self.info("requestDate：" + str(self.requestDate))
        return self.getRemoteData(self.requestDate)

    # 获取远程数据
    def getRemoteData(self, requestDate):
        appResult = {}
        for type in configApi.DianviewType:
            url = configApi.DianviewApiHost + '?' + self.getUrlParams(requestDate, type)
            self.info("start_data：" + str(requestDate) + " request_url: " + url)
            resultItems = self.getJosnData(url)
            if "code" in resultItems:
                self.info("code=" + str(resultItems['code']) + ",message=" + str(resultItems['message']))
                continue
            elif resultItems is not False and len(resultItems) > 0:
                if isinstance(resultItems, dict):
                    resultItems = [resultItems]
                for item in resultItems:
                    app_id = str(item['app_id'])
                    if self.ThirdAppMap.has_key(app_id):
                        appKey = self.ThirdAppMap[app_id]
                        platform = self.getPlatformByAppkey(appKey)
                        if type == "video":
                            appResult[item['app_id'] + str(item['day']) + type] = {
                                "ads_id": self.adsId,
                                "app_key": appKey,
                                "ad_type": configApi.DianviewType[type],
                                "platform": platform,
                                "days": item['day'],
                                "hours": 0,
                                "ad_income": float('%.2f'%(item['earning']/6.5)),
                                "third_clicks": int(item['ad_click']),
                                "third_views": int(item['ad_show']),
                            }
                        else:
                            appResult[item['app_id'] + str(item['day']) + type] = {
                                "ads_id": self.adsId,
                                "app_key": appKey,
                                "ad_type": configApi.DianviewType[type],
                                "platform": platform,
                                "days": item['day'],
                                "hours": 0,
                                "ad_income": float('%.2f'%(item['earning']/6.5)),
                                "third_clicks": int(item['ad_click']),
                                "third_views": int(item['ad_imp']),
                            }
            else:
                self.info(self.requestDate + " results == []")
        return appResult

    # 组装参数
    def getUrlParams(self, request_date, type):
        params = {}
        params['apikey'] = configApi.DianviewApikey
        params['group_by_date'] = 1
        params['ad_category'] = type
        params['start_time'] = request_date
        params['end_time'] = request_date#self.getNextDate(request_date)
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, url):
        s_time = time.time()
        req = urllib2.Request(url)
        res = urllib2.urlopen(req)
        html = res.read()
        status_code = res.getcode()
        jsonData = json.loads(html)
        e_time = time.time()
        use_time = e_time - s_time

        if status_code == 400:
            self.error('use_time=' + str(use_time) + " response error! status=400 Bad Request")
            self.saveApiLogData("get api data response error" + str(status_code), self.adsId, 0, str(self.requestDate))
            return False
        elif status_code == 401:
            self.error('use_time=' + str(use_time) + " response error! status=401 Unauthorized")
            self.saveApiLogData("get api data response error" + str(status_code), self.adsId, 0, str(self.requestDate))
            return False
        elif status_code != 200:
            self.error(
                'use_time=' + str(use_time) + " response error! status=" + str(status_code) + " content=" + html)
            self.saveApiLogData("get api data response error" + str(status_code), self.adsId, 0, str(self.requestDate))
            return False
        self.saveApiLogData("-", self.adsId, 1, str(self.requestDate))
        self.info('use_time=' + str(use_time) + " response success! status=" + str(status_code) + " content=" + html)
        return jsonData

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = int(time.time())
    while 1:
        obj = ReportApiDianview('Dianview')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
