#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
import hashlib
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiPingcoo(ReportApiBase):

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
        if data is False:
            self.saveApiLogData("return False error!", self.adsId, 0, str(self.requestDate))
            self.error("return False error!")
            exit()
        else:
            self.info("result：" + str(data))
            return data


    # 获取远程数据
    def getRemoteData(self):
        returnResult = self.getJosnData(self.requestDate)
        if returnResult is False:
            return False
        appResult = {}
        for val in returnResult:
            if self.thirdAppMap.has_key(val):
                appKey = self.thirdAppMap[val]
                platform = self.getPlatformByAppkey(appKey)
                cn_ad_income = float(returnResult[val]['profit'])
                usa_ad_income = round(cn_ad_income / configApi.EXCHANGE_RATE, 2)
                appResult[str(val + self.requestDate)] = {"ads_id": self.adsId, "app_key": appKey, "ad_type": 1, "platform": platform,
                               "days": self.requestDate, "hours": 0, "ad_income": usa_ad_income,
                               "third_views": returnResult[val]['played_num']}
            else:
                self.saveApiLogData("APPID NOT FOUND appid="+str(val['appId']), self.adsId, 0,
                                    self.requestDate)
        if len(appResult) < 1:
            self.saveApiLogData("getListDataFromRemote len(result)<1!", self.adsId, 0,
                                self.requestDate)
            self.info("getListDataFromRemote len(result)<1!")
        return appResult

    # 组装参数
    def getUrlParams(self, app_key):
        params = {}
        params['app_key'] = app_key
        params['date'] = self.requestDate
        params['email'] = configApi.PingcooEmail
        params['timestamp'] = int(time.time())
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, requestDate):
        applicationList = {}
        for appkey_appsecret in self.thirdAppMap:
            appkey_appsecret_list = appkey_appsecret.split(",")
            pingcoohost = configApi.PingcooHost
            pingcoopath = configApi.PingcooPath
            pingcooemail = configApi.PingcooEmail
            pingcooappkey = appkey_appsecret_list[0]
            pingcooappsecret = appkey_appsecret_list[1]
            requeststr = "app_key=" + pingcooappkey + "&date=" + self.requestDate + "&email=" + pingcooemail + "&timestamp=" + str(
                int(time.time()))
            md5srcstr = requeststr + pingcooappsecret
            m = hashlib.md5()
            m.update(md5srcstr)
            sign = m.hexdigest()
            url = pingcoohost + pingcoopath + "&" + requeststr + "&sign=" + sign
            s_time = time.time()
            try:
                req = urllib2.Request(url)
                res = urllib2.urlopen(req)
            except urllib2.HTTPError, error:
                self.saveApiLogData("response error! status=%s,%s" % (error.code, error.read()), self.adsId, 0,
                                    str(self.requestDate))
                self.error("response error! status=%s,%s" % (error.code, error.read()))
                return False
            # except:
            #     import sys
            #     print sys.exc_info()

            html = res.read()
            status_code = res.getcode()
            json_data = json.loads(html)
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
            if json_data['code'] !=0:
                self.saveApiLogData("response error! errorcode=" + str(json_data['code']), self.adsId, 0, self.requestDate)
                self.error('use_time=' + str(use_time) + " response error! status=" + str(status_code) + " content=" + html)
                return False
            applicationList[appkey_appsecret] = json_data['data']
        return applicationList

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiPingcoo('Pingcoo')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
