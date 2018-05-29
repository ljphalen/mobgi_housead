#!/usr/bin/env python
# -*- coding:utf-8 -*-
import sys
import time
import urllib
import urllib2
import json
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiInmobi(ReportApiBase):
    # 获取第三方数据
    def getApiData(self, startTime):
        self.thirdPosMap = self.getThirdBlockMap()
        if self.thirdPosMap is None or len(self.thirdPosMap) < 1:
            self.error("len(self.thirdAppMap) < 1")
            self.saveApiLogData("len(self.thirdAppMap) < 1", self.adsId, 0, str(self.requestDate))
            exit()
        self.requestDate = time.strftime('%Y-%m-%d', time.localtime(startTime))
        self.info("requestDate：" + str(self.requestDate))
        #针对inmobi进行展示对比率的检测
        newData = self.getRemoteData(self.requestDate)
        self.checkDataIsNormal(newData)
        return newData


    def checkDataIsNormal(self,newData):
        sql = "select sum(impressions) as impressions from report_day where ads_id = '%s' and days = '%s'"%(self.adsId,self.requestDate)
        reportData = self.dbConf.fetchone(sql)
        newViews = 0
        for item in newData:
            newViews += int(newData[item]['third_views'])
        if reportData['impressions'] is not None:
            if newViews/reportData['impressions'] <= 0.7:
                self.scriptFault = True

    # 获取远程数据
    def getRemoteData(self, requestDate):
        sessionResponseResult = self.getSessionResponseResult()
        result = self.parseSessionResponseResult(sessionResponseResult)
        if result is False:
            self.saveApiLogData('Send request，get SessionId, accountId  error', self.adsId, 0, str(self.requestDate))
            self.error('Send request，get SessionId, accountId  error')
            exit()
        returnResult = self.getJosnData(requestDate)
        if returnResult is False:
            return None
        appResult = {}
        for data in returnResult:
            date_of_log = requestDate
            siteId = data['siteId']
            if self.thirdPosMap.has_key(siteId):
                appKey = self.thirdPosMap[siteId]['app_key']
                adType = self.thirdPosMap[siteId]['ad_type']
                block_id = self.thirdPosMap[siteId]['pos_key']
                platform = self.getPlatformByAppkey(appKey)
                adIncome = round(data['earnings'], 2)
                hour = data['date'][11:-6]
                appResult[str(date_of_log) + str(appKey) + str(block_id) + str(adType)] = {
                    "ads_id": self.adsId,
                    "app_key": appKey,
                    "ad_type": str(adType),
                    "days": date_of_log,
                    "hours": hour,
                    "ad_income": adIncome,
                    "third_clicks": int(data['clicks']),
                    "third_views": int(data['adImpressions']),
                    "pos_key": block_id,
                    "platform": platform,
                }
            else:
                self.saveApiLogData("thirdPosMap has not third_pos_key: " + siteId, self.adsId, 0,
                                    str(self.requestDate))
                self.info("thirdPosMap has not third_pos_key: " + siteId)
        return appResult


    def parseSessionResponseResult(self, responseResult):
        httpCode = responseResult.getcode()
        if httpCode != 200:
            self.saveApiLogData('parse SessionId, accountId error' + str(httpCode), self.adsId, 0, str(self.requestDate))
            self.info("get token response error!  httpCode=" + str(httpCode))
            return False
        try:
            html = responseResult.read()
            jsonStr = json.loads(html)
            self.sessionId = jsonStr['respList'][0]['sessionId']
            self.accountId = jsonStr['respList'][0]['accountId']
            self.info("get sessionId=" + self.sessionId + ',accountId=' + self.accountId)
        except Exception, e:
            self.saveApiLogData('parse SessionId, accountId error' + str(e), self.adsId, 0, str(self.requestDate))
            self.info('parse SessionId, accountId error' + str(e))
            return False

    #获取进入的token
    def getSessionResponseResult(self):
        responseResult = None
        try:
            url = configApi.InmobiTokenHost
            req = urllib2.Request(url)
            req.add_header("userName", configApi.InmobiUserName)  # 这里通过add_header方法很容易添加的请求头
            req.add_header("password", configApi.InmobiPassword)
            req.add_header("secretKey", configApi.InmobiSecretKey)
            responseResult = urllib2.urlopen(req)
        except Exception, e:
            self.saveApiLogData('send request,get SessionId, accountId  error' + str(e), self.adsId, 0, str(self.requestDate))
            self.info('send request,get SessionId, accountId  error' + str(e))
        return responseResult

    # 组装参数
    def getUrlParams(self, requestDate, appId):
        params = {}
        params['appIds'] = appId
        params['startDate'] = str(requestDate)
        params['endDate'] = str(requestDate)
        #params['key'] = configApi.CentrixLinkApikey
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, requestDate):
        # url = configApi.InmobiApiHost + '?' + self.getUrlParams(self.requestDate)
        timeFrame = requestDate + ":" + requestDate
        params = {
            "reportRequest": {
                "metrics": ["clicks", "earnings", "adImpressions"],
                "groupBy": ["site", "date"],
                "timeFrame": timeFrame,
                "orderBy": ["date"],
                "orderType": "asc"
            }
        }
        jdata = json.dumps(params)
        url = configApi.InmobiApiHost
        req = urllib2.Request(url, jdata)
        req.add_header("Content-Type", "application/json")  # 这里通过add_header方法很容易添加的请求头
        req.add_header("accountId", self.accountId)
        req.add_header("sessionId", self.sessionId)
        req.add_header("secretKey", configApi.InmobiSecretKey)
        responseResult = urllib2.urlopen(req)
        self.info("send request to get data url=" + url + ", params=" + jdata)
        code = responseResult.getcode()
        if code != 200:
            self.saveApiLogData("get api data response error!  code=" + str(code), self.adsId, 0,
                                str(self.requestDate))
            self.info("get api data response error!  code=" + str(code))
            return False
        returnData = {}
        try:
            html = responseResult.read()
            jsonStr = json.loads(html)
            if jsonStr.has_key('error') and jsonStr['error'] is True:
                self.saveApiLogData("get api data response error!  msg=" + str(jsonStr['message']), self.adsId, 0,
                                    str(self.requestDate))
                self.info("get api data response error!  msg=" + str(jsonStr['message']))
                return False
            if jsonStr.has_key('respList'):
                returnData = jsonStr['respList']
                self.saveApiLogData("-", self.adsId, 1,
                                    str(self.requestDate))
                self.info("get data jsonStr=" + str(jsonStr))
                return returnData
        except Exception, e:
            self.saveApiLogData('parse  json error'+str(e), self.adsId, 0,
                                str(self.requestDate))
            self.error('parse  json error' + str(e))
        return False

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = int(time.time())
    while 1:
        obj = ReportApiInmobi('Inmobi')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
