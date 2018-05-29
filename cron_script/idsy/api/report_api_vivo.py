#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import json
import cookielib
import urllib2
import urllib
from report_channel_api_base import ReportChannelApiBase
import config.api as configApi
from PIL import Image,ImageEnhance
import pytesseract

class ReportApiVivo(ReportChannelApiBase):
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
        returnResult = self.getJosnData()
        if returnResult is False:
            return False
        appResult = {}
        for val in returnResult:
            if self.thirdAppMap.has_key(val['mediaId']):
                appKey = self.thirdAppMap[val['mediaId']]
                platform = self.getPlatformByAppkey(appKey)
                ad_type = 2
                key = appKey+str(self.requestDate)+str(ad_type)
                appResult[key] = {
                    "ads_id": self.adsId,
                    "app_key": appKey,
                    "ad_type": ad_type,
                    "platform": platform,
                    "days": self.requestDate,
                    "ad_income": round(float(val['income']) / configApi.EXCHANGE_RATE / 0.5,2),
                    "third_views": int(val['view']),
                    "third_clicks": int(val['click']),
                }
            else:
                self.saveApiLogData("APPID NOT FOUND appid="+str(val['mediaId']), self.adsId, 0,self.requestDate)
        if len(appResult) < 1:
            self.saveApiLogData("getListDataFromRemote len(result)<1!", self.adsId, 0,self.requestDate)
            self.info("getListDataFromRemote len(result)<1!")
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate):
        params = {
            'order':'',
            'orderBy':'',
            'startDate':requestDate,
            'endDate':requestDate,
            'dimensions':'mediaId',
            'metrics':'view',
            'searchKeyWord':'',
            'pageIndex':1,
            'pageSize':20,
            'timestamp':int(time.time()),
        }
        return urllib.urlencode(params)

    #爬虫模块
    def spiderData(self):
        flag = True
        try:
            # Enable cookie support for urllib2
            # login_page = config['loginUrl']
            cookiejar = cookielib.CookieJar()
            urlopener = urllib2.build_opener(urllib2.HTTPCookieProcessor(cookiejar))
            urllib2.install_opener(urlopener)
            urlopener.addheaders.append(('Referer', 'https://adnet.vivo.com.cn/admin/report/apps'))
            urlopener.addheaders.append(('Connection', 'keep-alive'))
            urlopener.addheaders.append(('Accept', 'application/json, text/plain, */*'))
            urlopener.addheaders.append(('Accept-Encoding', 'gzip, deflate, sdch, br'))
            urlopener.addheaders.append(('Accept-Language', 'zh-CN,zh;q=0.8,en;q=0.6'))
            urlopener.addheaders.append(('Cookie',
                                         'b_account_username=up9PghD9iJvHKXz1%2FWPDxw%3D%3D; b_account_token=2fd32ad9feb64fc7dfec66314f978a67.1524635668714; b_account_aid=JNtfu0xA1Rc%3D; b_account_salt=HLEukJsjNFj4fbJzhXKirg%3D%3D.1524635668714; JSESSIONID=E1712E9DC6CB9347252E2EED501132C8'))
            urlopener.addheaders.append(('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8'))
            urlopener.addheaders.append(('Host', 'adnet.vivo.com.cn'))
            urlopener.addheaders.append(('User-Agent',
                                         'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'))
            urlopener.addheaders.append(('Connection', 'Keep-Alive'))
            params = self.getUrlParams(self.requestDate)
            contentUrl = r'https://adnet.vivo.com.cn/api/report/getReportTableData'
            resp = urlopener.open(urllib2.Request(contentUrl, params))
            html = resp.read()
            jsonData = json.loads(html)
        except Exception, e:
            flag = False
            # self.info(reportId + ":" + str(e))
        if jsonData['data']['dataList']:
            flag = True
        else:
            flag = False
        if flag:
            return jsonData['data']['dataList']
        else:
            return False



    # 抓取数据
    def getJosnData(self):
        data = self.spiderData()
        return data

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiVivo('Vivo')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
