#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
import hashlib
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiUniplay(ReportApiBase):
    # thirdAppMap区分广告类型
    distinguishAdType = 1

    # 获取第三方数据
    def getApiData(self, startTime):
        self.thirdAppMap = self.getThirdAppMap()
        if len(self.thirdAppMap) < 1:
            self.error("len(self.thirdAppMap) < 1")
            self.saveApiLogData("len(self.thirdAppMap) < 1", self.adsId, 0, str(self.requestDate))
            exit()
        result = None
        self.requestDate = time.strftime('%Y-%m-%d', time.localtime(startTime))
        self.info("requestDate：" + str(self.requestDate))
        for reportId in self.thirdAppMap:
            for adType in self.thirdAppMap[reportId]:
                data = self.getRemoteData(self.requestDate, adType, reportId, self.thirdAppMap[reportId][adType])
                if data is not False:
                    if result is None:
                        result = {}
                    result = dict(result, **data)
        if len(result) == 0:
            self.scriptFault = True
        return result

    # 获取远程数据
    def getRemoteData(self, requestDate, adType, reportId, appKey):
        returnResult = self.getJosnData(requestDate, reportId, appKey)
        if returnResult is False:
            return False
        appResult = {}
        for date in returnResult:
            #tmptime = time.strptime(str(data['date'])[0:10], '%Y-%m-%d')
            date_of_log = date
            platform = self.getPlatformByAppkey(appKey)
            appResult[str(date_of_log) + str(appKey) + str(adType)] = {
                "ads_id": self.adsId,
                "app_key": appKey,
                "ad_type": adType,
                "days": date_of_log,
                "hours": 0,
                "ad_income": float('%.2f'%(returnResult[date]['rmb'] / configApi.EXCHANGE_RATE)),
                "third_clicks": int(returnResult[date]['cnum']),
                "third_views": int(returnResult[date]['snum']),
                "platform": platform,
            }
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate, appId):
        params = {}
        params['account'] = configApi.UniplayAccount
        params['pwdmd5'] = hashlib.md5(configApi.UniplayPwd).hexdigest()
        params['appid'] = appId
        params['sdate'] = requestDate
        params['edate'] = requestDate
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, requestDate, appId, appKey):
        url = configApi.UniplayApiHost + '?' + self.getUrlParams(requestDate, appId)
        self.info("request_url: " + url)
        s_time = time.time()
        req = urllib2.Request(url)
        res = urllib2.urlopen(req)
        html = res.read()
        status_code = res.getcode()
        jsonData = json.loads(html)
        e_time = time.time()
        use_time = e_time - s_time
        if status_code != 200:
            self.error('use_time=' + str(use_time) + " status=" + str(status_code) + " appKey=" + str(
                appKey) + " content=" + html + " requestDate=" + requestDate + " appId=" + appId + " appKey=" + appKey)
            return False
        if jsonData['ret'] != 0:
            if jsonData['eno'] == 404:  # eno = 404        没有数据
                self.info('use_time=' + str(use_time) + " eno=" + str(jsonData['eno']) + " tip=" + jsonData[
                    'tip'] + " requestDate=" + requestDate + " appId=" + appId + " appKey=" + appKey)
                # 这里要写入一个标记#状态，错误原因，app_key,这个是全局错误，只限于广告商，不限于应用
                self.saveApiLogData("response error! status=" + str(jsonData['eno']) + "or data format error appkey="+str(appKey), self.adsId,
                                    0, requestDate)
                return []
            elif jsonData['eno'] == 403:  # eno = 403        等待出数
                self.error('use_time=' + str(use_time) + " eno=" + str(jsonData['eno']) + " tip=" + jsonData[
                    'tip'] + " requestDate=" + requestDate + " appId=" + appId + " appKey=" + appKey)
                # 这里要写入一个标记#状态，错误原因，app_key,这个是全局错误，只限于广告商，不限于应用
                self.saveApiLogData("response error! status=" + str(jsonData['eno']) + "or data format error appkey="+str(appKey), self.adsId,
                                    0, requestDate)
                quit()
        self.info('use_time=' + str(use_time) + " status=" + str(status_code) + " content=" + html)
        self.saveApiLogData("-", self.adsId, 1,requestDate)
        return jsonData['days']

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    looptimes = 0
    while 1:
        obj = ReportApiUniplay('Uniplay')
        obj.run()
        looptimes += obj.looptimes
        time.sleep(configApi.SLEEP_SECOND)
        # 脚步执行时间超过30分钟直接跳出
        if looptimes > 1 or int(time.time() - startTimeStamp) > 1800:
            obj.error('too many times or too long time')
            break
