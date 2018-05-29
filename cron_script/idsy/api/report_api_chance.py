#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import urllib
import urllib2
import json
from report_api_base import ReportApiBase
import config.api as configApi


class ReportApiChance(ReportApiBase):

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
                # 1 = > 'Banner',
                # 2 = > '插屏',
                # 4 = > '开屏广告',
                # 6 = > '积分墙',
                # 20 = > '信息流',
                # 21 = > '原生信息流',
                # 40 = > '视频广告'
                for adform_id, mdata in returnResult[val].iteritems():
                    if int(adform_id) == 40:
                        intergration_type = 1
                        statViewField = 'playSuccess'
                        statClickField = 'clickvideopic'
                    elif int(adform_id) == 2:
                        intergration_type = 2
                        statViewField = 'imp'
                        statClickField = 'click'
                    else:
                        self.info('can not found adform_id:' + str(adform_id))
                        continue
                    for day in mdata['list']:
                        key = val + adform_id + day+ str(intergration_type)
                        cn_ad_income = float(mdata['list'][day]['income'])
                        usa_ad_income = round(cn_ad_income / configApi.EXCHANGE_RATE, 2)
                        appResult[key] = {
                            "ads_id": self.adsId,
                            "app_key": appKey,
                            "ad_type": intergration_type,
                            "platform": platform,
                            "days": day,
                            "hours": 0,
                            "ad_income": usa_ad_income,
                            "third_views": int(mdata['list'][day][statViewField]),
                            "third_clicks": int(mdata['list'][day][statClickField])
                        }
            else:
                self.saveApiLogData("APPID NOT FOUND appid="+str(val['appId']), self.adsId, 0,self.requestDate)
        if len(appResult) < 1:
            self.saveApiLogData("getListDataFromRemote len(result)<1!", self.adsId, 0,self.requestDate)
            self.info("getListDataFromRemote len(result)<1!")
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate):
        params = {}
        params['username'] = configApi.ChanceUserName
        params['key'] = configApi.ChanceMd5Key
        params['sdate'] = requestDate
        params['edate'] = requestDate
        return urllib.urlencode(params)

    # 抓取数据
    def getJosnData(self, requestDate):
        url = configApi.ChanceHost + '?' + self.getUrlParams(self.requestDate)
        self.info("request_url: " + url)
        s_time = time.time()
        try:
            req = urllib2.Request(url)
            req.add_header("Content-Type", "application/json")  # 这里通过add_header方法很容易添加的请求头
            res = urllib2.urlopen(req)
        except urllib2.HTTPError, error:
            self.saveApiLogData("response error! status=%s,%s" % (error.code, error.read()), self.adsId, 0, str(self.requestDate))
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
            self.saveApiLogData("response error! status=" + str(status_code), self.adsId, 0,requestDate)
            self.error('use_time=' + str(use_time) + " response error! status=" + str(status_code) + " content=" + html)
            return False
        self.saveApiLogData("-", self.adsId, 1,requestDate)
        self.info('use_time=' + str(use_time) + " response success! status=" + str(status_code) + " content=" + html)
        return jsonData['data']

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiChance('Chance')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
