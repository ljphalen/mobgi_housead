#!/usr/bin/env python
# -*- coding:utf-8 -*-
import sys
import time
import urllib
import urllib2
import json
from report_api_base import ReportApiBase
import config.api as configApi
import hashlib


class ReportApiMobvista_YS(ReportApiBase):
    # 获取第三方数据
    def getApiData(self, startTime):
        self.thirdPosMap = self.getThirdBlockMap()
        if self.thirdPosMap is None or len(self.thirdPosMap) < 1:
            self.error("len(self.thirdAppMap) < 1")
            self.saveApiLogData("len(self.thirdAppMap) < 1", self.adsId, 0, str(self.requestDate))
            exit()
        self.requestDate = time.strftime('%Y-%m-%d', time.localtime(startTime))
        self.info("requestDate：" + str(self.requestDate))
        result = self.getRemoteData(self.requestDate)
        if len(result) == 0:
            self.scriptFault = True
        return result

    # 获取远程数据
    def getRemoteData(self, requestDate):
        result = self.getJosnData(requestDate)
        appResult = {}
        if result['code'] == "ok" and result["data"].has_key("lists"):
            resultItems = result["data"]["lists"]
            for item in resultItems:
                unit_id = str(item['unit_id'])
                if self.thirdPosMap.has_key(unit_id):
                    appKey = self.thirdPosMap[unit_id]['app_key']
                    platform = self.getPlatformByAppkey(appKey)
                    mydate = self.exchangeStrDate(str(item['date']), "%Y%m%d", "%Y-%m-%d")
                    ad_type = self.thirdPosMap[unit_id]['ad_type']
                    key = item['unit_id'] + item['date']
                    if appResult.has_key(key):
                        appResult[key]['ad_income'] += float(item['est_revenue'])
                        appResult[key]['third_clicks'] += float(item['click'])
                        appResult[key]['third_views'] += float(item['impression'])
                    else:
                        appResult[key] = {
                            "ads_id": self.adsId,
                            "app_key": appKey,
                            "ad_type": ad_type,
                            "platform": platform,
                            "days": mydate,
                            "hours": 0,
                            "ad_income": float(item['est_revenue']),
                            "third_clicks": int(item['click']),
                            "third_views": int(item['impression'])
                        }
                else:
                    self.info("can not found unit_id:" + unit_id)
        else:
            self.saveApiLogData(self.requestDate + " results == []", self.adsId, 0, str(self.requestDate))
            self.info(self.requestDate + " results == []")
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate):
        date = self.exchangeStrDate(requestDate, "%Y-%m-%d", "%Y%m%d")
        params = {}
        params['skey'] = configApi.MobvistaSkey
        params['v'] = configApi.MobvistaApiVer
        params['time'] = time.time()
        params['page'] = 1
        params['limit'] = 200
        params['group_by'] = "date,app_id,unit_id"
        params['start'] = date
        params['end'] = date
        params['sign'] = self.getSign(params)
        return urllib.urlencode(params)


    def getSign(self, params):
        date = self.ksort(params)
        url = urllib.urlencode(date)
        return hashlib.md5(hashlib.md5(url).hexdigest() + configApi.MobvistaSecret).hexdigest()

    def ksort(self, d):
        return [(k, d[k]) for k in sorted(d.keys())]

    # 抓取数据
    def getJosnData(self, requestDate):
        url = configApi.MobvistaApiHost + '?' + self.getUrlParams(requestDate)
        s_time = time.time()
        req = urllib2.Request(url)
        res = urllib2.urlopen(req)
        html = res.read()
        status_code = res.getcode()
        jsonData = json.loads(html)
        e_time = time.time()
        use_time = e_time - s_time

        if status_code == 400:
            self.saveApiLogData('use_time=' + str(use_time), self.adsId, 0, str(self.requestDate))
            self.error('use_time=' + str(use_time) + " response error! status=400 Bad Request")
            return False
        elif status_code == 401:
            self.saveApiLogData('use_time=' + str(use_time) + " response error! status=401 Unauthorized", self.adsId, 0, str(self.requestDate))
            self.error('use_time=' + str(use_time) + " response error! status=401 Unauthorized")
            return False
        elif status_code != 200:
            self.saveApiLogData('use_time=' + str(use_time) + " response error! status=" + str(status_code), self.adsId, 0, str(self.requestDate))
            self.error('use_time=' + str(use_time) + " response error! status=" + str(status_code) + " content=" + html)
            return False
        self.saveApiLogData('-', self.adsId, 1,str(self.requestDate))
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
        obj = ReportApiMobvista_YS('Mobvista_YS')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
