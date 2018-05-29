#!/usr/bin/env python
# -*- coding:utf-8 -*-
import sys
import time
import urllib
import urllib2
import json
import base64
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiInnotech(ReportApiBase):
    # 获取第三方数据
    def getApiData(self, startTime):
        self.thirdPosMap = self.getThirdBlockMap()
        if self.thirdPosMap is None or len(self.thirdPosMap) < 1:
            self.error("len(self.thirdAppMap) < 1")
            self.saveApiLogData("len(self.thirdAppMap) < 1", self.adsId, 0, str(self.requestDate))
            exit()
        self.requestDate = time.strftime('%Y-%m-%d', time.localtime(startTime))
        self.info("requestDate：" + str(self.requestDate))
        return self.getRemoteData(self.requestDate)

    # 获取远程数据
    def getRemoteData(self, requestDate):
        returnResult = self.getJosnData(requestDate)
        if requestDate is False:
            return None
        appResult = {}
        for data in returnResult:
            date_of_log = requestDate
            third_pos_key = str(data["slot_id"])
            if self.thirdPosMap.has_key(third_pos_key):
                appKey = self.thirdPosMap[third_pos_key]['app_key']
                adType = self.thirdPosMap[third_pos_key]['ad_type']
                block_id = self.thirdPosMap[third_pos_key]['pos_key']
                platform = self.getPlatformByAppkey(appKey)
                appResult[str(date_of_log) + str(appKey) + str(block_id) + str(adType)] = {
                    "ads_id": self.adsId,
                    "app_key": appKey,
                    "ad_type": str(adType),
                    "days": date_of_log,
                    "hours": 0,
                    "ad_income": float(data['prebids_income']) / configApi.EXCHANGE_RATE,
                    "third_clicks": int(data['prebids_click']),
                    "third_views": int(data['prebids_impression']),
                    "pos_key": block_id,
                    "platform": platform,
                }
            else:
                self.info("thirdPosMap has not third_pos_key: " + third_pos_key)
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate):
        params = {}
        params['start_date'] = requestDate
        params['end_date']= requestDate
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, requestDate):
        #需要循环处理数据

        url = configApi.InnotechApiHost+'?'+ self.getUrlParams(requestDate)
        s_time = time.time()
        headers = {
            "Authorization": base64.encodestring(configApi.InnotechUsername+":"+configApi.InnotechPassword).replace('\n',''),
        }
        req = urllib2.Request(url,None,headers)
        res = urllib2.urlopen(req)
        html = res.read()
        status_code = res.getcode()
        jsonData = json.loads(html)
        e_time = time.time()
        use_time = e_time - s_time
        if status_code != 200 or 'data' not in jsonData:
            self.error('use_time=' + str(use_time) + " status=" + str(status_code)  + " content=" + html + " url=" + url)
            # 这里要写入一个标记#状态，错误原因，app_key,这个是全局错误，只限于广告商，不限于应用
            self.saveApiLogData("response error! status=" + str(status_code)+"or data format error", self.adsId, 0, self.requestDate)
            if self.missError(self.adsId):
                pass
            else:
                exit()
        if len(jsonData['data']) == 0:
            # 这里要写入一个标记#状态，错误原因，app_key,这个是全局错误，只限于广告商，不限于应用
            self.error('use_time=' + str(use_time) + " status=" + str(status_code) + " content=" + html + " url=" + url)
            self.saveApiLogData("jsonData error!", self.adsId, 0, self.requestDate)
            if self.missError(self.adsId):
                pass
            else:
                exit()
        else:
            # 这里要写入一个标记#状态，错误原因，app_key,这个是全局错误，只限于广告商，不限于应用
            self.saveApiLogData("-", self.adsId, 1, self.requestDate)
            self.info('use_time=' + str(use_time) + " status=" + str(status_code) + " content=" + html)
        return jsonData['data']['items']

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = int(time.time())
    while 1:
        obj = ReportApiInnotech('Innotech')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
