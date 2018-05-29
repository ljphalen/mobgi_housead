#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
import hashlib
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiYoumi(ReportApiBase):
    endDate = ""

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
            data = self.getRemoteData(repordId)
            if data is not False:
                if result is None:
                    result = {}
                result = dict(result, **data)
        self.info("result：" + str(result))
        return result

    # 获取远程数据
    def getRemoteData(self, repordId):
        url = configApi.YoumiApiHost + '?' + self.getUrlParams(repordId, self.requestDate, 1)
        self.info("start_data：" + str(self.requestDate) + " request_url: " + url)
        appResult = {}
        jsonData = self.getJosnData(url)
        if jsonData is False:
            return False
        if len(jsonData["data"]) > 0:
            appKey = self.thirdAppMap[repordId]
            platform = self.getPlatformByAppkey(appKey)
            if jsonData['data'][0]['finished_playing'] is None:
                jsonData['data'][0]['finished_playing'] = 0
            YoumiAdType = 1
            if jsonData['ad_type'] and configApi.YoumiType.has_key(jsonData['ad_type']):
                YoumiAdType = configApi.YoumiType[jsonData['ad_type']]
            appResult[str(jsonData['data'][0]['date']) + str(appKey)]={
                "ads_id": self.adsId,
                "app_key": appKey,
                "ad_type": YoumiAdType,  # video
                "platform": platform,
                "days": jsonData['data'][0]['date'],
                "hours": 0,
                "ad_income": jsonData['data'][0]['revenue'] / configApi.EXCHANGE_RATE,
                "third_views": jsonData['data'][0]['finished_playing']
            }
        else:
            self.saveApiLogData("jsonData ==[]", self.adsId, 0, str(self.requestDate))
            self.info("jsonData data ==[]")
        return appResult

    def getUrlParams(self, youmi_app_id, request_data, page):
        app_id, access_key = str.split(str(youmi_app_id), "@")
        params = {}
        params['access_id'] = app_id
        params['start_date'] = request_data
        params['end_date'] = request_data
        params['per_page'] = 30
        params['page'] = page
        params['ad_type'] = 'video'
        params['sign'] = self.getSign(params, access_key)
        return urllib.urlencode(params)


    def getSign(self, params_map, access_key):
        """
        获取参数签名
        @params:
        params_map: 字典，需要签名的参数按 key/value 形式保存
        access_key: 签名用的密钥
        """
        sorted_params = self.ksort(params_map)
        kv_str = "".join(str(param) for param in sorted_params)
        input_str = kv_str.encode("utf-8") + access_key
        sign = hashlib.md5(input_str).hexdigest()
        return sign

    def ksort(self, d):
        return ['%s=%s' % (k, d[k]) for k in sorted(d.keys())]

    # 抓取数据
    def getJosnData(self, url):
        s_time = time.time()
        try:
            req = urllib2.Request(url)
            res = urllib2.urlopen(req)
        except urllib2.HTTPError, error:
            self.error("response error! status=%s,%s" % (error.code, error.read()))
            return False
        html = res.read()
        status_code = res.getcode()
        jsonData = json.loads(html)
        e_time = time.time()
        use_time = e_time - s_time
        if status_code == 400:
            # 这里要写入一个标记#状态，错误原因，app_key,这个是全局错误，只限于广告商，不限于应用
            self.saveApiLogData("response error! status=400 Bad Request" + str(status_code), self.adsId, 0,self.requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=400 Bad Request")
            return False
        elif status_code == 401:
            self.saveApiLogData("response error! status=401 Unauthorized" + str(status_code), self.adsId, 0, self.requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=401 Unauthorized")
            return False
        elif status_code != 200:
            self.saveApiLogData(" response error! status=" + str(status_code), self.adsId, 0,self.requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=" + str(status_code) + " content=" + html)
            return False
        self.saveApiLogData("-", self.adsId, 1,self.requestDate)
        self.info('use_time=' + str(use_time) + " response success! status=" + str(status_code) + " content=" + html)
        if jsonData['c'] is not 0:
            self.saveApiLogData("Data error! appid:"+str(jsonData['appid']), self.adsId, 0, self.requestDate)
            self.info("Data error! appid:"+str(jsonData['appid']))
        else:
            return jsonData

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiYoumi('Youmi')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
