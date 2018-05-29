#!/usr/bin/env python
# -*- coding:utf-8 -*-
import sys
import time
import urllib
import urllib2
import json
import hmac
import hashlib
import base64
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiYoulan(ReportApiBase):
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
        for data in result:
            #如果点击和展示超过某个值之后才开始判断是否需要每天每小时重跑，并且不更新重跑时间
            if result[data]['third_clicks']>1000 and result[data]['third_views']>1000:
                if result[data]['ad_income'] <= 0:
                    self.scriptFault = True
        return result

    # 获取远程数据
    def getRemoteData(self, requestDate):
        returnResult = self.getJosnData(requestDate)
        if requestDate is False:
            return None
        appResult = {}
        for data in returnResult:
            date_of_log = requestDate
            third_pos_key = str(data["adSpaceId"])
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
                    "ad_income": float(data['settlement']) / configApi.EXCHANGE_RATE,
                    "third_clicks": int(data['click']),
                    "third_views": int(data['impression']),
                    "pos_key": block_id,
                    "platform": platform,
                }
            else:
                self.info("thirdPosMap has not third_pos_key: " + third_pos_key)

        return appResult

    # 组装参数
    def getUrlParams(self):
        params = {}
        params['startDate'] = str(self.requestDate)
        params['endDate'] = str(self.requestDate)
        params['id'] = configApi.YoulanId
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, requestDate):
        #url = configApi.YoulanHost + '?' + self.getUrlParams(self.requestDate)
        url = configApi.YoulanHost
        s_time = time.time()
        text = 'endDate='+self.requestDate+"&id="+configApi.YoulanId+'&startDate='+self.requestDate
        # post 传参
        params = {
            "id": configApi.YoulanId,
            "startDate": requestDate,
            "endDate": requestDate,
            "signature":base64.b64encode(hmac.new(configApi.YoulanToken,text,hashlib.sha1).digest())
        }
        data_urlencode = json.dumps(params)
        headers = {'Content-Type':'application/json'}
        req = urllib2.Request(url, data_urlencode,headers)
        responseResult = urllib2.urlopen(req)
        self.info("send request url=" + url + ", params=" + data_urlencode)
        status_code = responseResult.getcode()
        if status_code != 200:
            self.error("get api data response error!  code=" + str(status_code))
            #这里要写入一个标记#状态，错误原因，app_key,这个是全局错误，只限于广告商，不限于应用
            self.saveApiLogData("get api data response error"+str(status_code),self.adsId,0,str(requestDate))
            return False
        try:
            html = responseResult.read()
            jsonStr = json.loads(html)
            self.info("data:" + str(jsonStr))
            self.saveApiLogData("-", self.adsId, 1,str(requestDate))
        except Exception, e:
            # 这里要写入一个标记
            self.saveApiLogData("parse json error", self.adsId, 0,str(requestDate))
            raise Exception('parse json error:' + str(e))
        return jsonStr['data']

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = int(time.time())
    while 1:
        obj = ReportApiYoulan('Youlan')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
