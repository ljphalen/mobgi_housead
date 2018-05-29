#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
import hashlib
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiHousead_DSP(ReportApiBase):

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
        return data

    # 获取远程数据
    def getRemoteData(self):
        returnResult = self.getJosnData(self.requestDate)
        if returnResult is False:
            return False
        appResult = {}
        for val in returnResult:
            #如果存在pos_key
            if self.thirdPosMap.has_key(val['pos_key']):
                appKey = self.thirdPosMap[val['pos_key']]['app_key']
                posKey = val['pos_key']
            else:
                appKey = val['app_key']
                posKey = 0
            platform = self.getPlatformByAppkey(appKey)
            hour = int(val['hour'][0:2])
            adtype = configApi.Housead_DSPType[val['ad_type']]
            key = str(appKey) + str(val['date']) + ":" + str(hour) + str(adtype)+str(val['pos_key'])
            appResult[key] = {
                "ads_id": self.adsId,
                "app_key": appKey,
                "ad_type": adtype,
                "platform": platform,
                "days": val['date'],
                "hours": hour,
                "ad_income": round(val['revenue']/configApi.EXCHANGE_RATE, 2),
                "third_views": val['impressions'],
                "third_clicks": val['clicks'],
                "pos_key":posKey,
                "is_mobgi":val['is_mobgi'],
            }
        if len(appResult) < 1:
            self.saveApiLogData("getListDataFromRemote len(result)<1!", self.adsId, 0,
                                self.requestDate)
            self.info("getListDataFromRemote len(result)<1!")
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate):
        params = {}
        #params['XDEBUG'] = 'idsy'
        params['sdate'] = str(requestDate)
        params['edate'] = str(requestDate)
        params['data_type'] = 'Housead_DSP'
        params['sign'] = hashlib.md5('1aa3408d92fe59cda813527095eaac53' + requestDate + requestDate).hexdigest()
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, requestDate):
        url = configApi.Housead_DSPHost + '?' + self.getUrlParams(self.requestDate)
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
        if jsonData['ret'] == 0:
            return jsonData['data']
        else:
            return False


    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiHousead_DSP('Housead_DSP')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
