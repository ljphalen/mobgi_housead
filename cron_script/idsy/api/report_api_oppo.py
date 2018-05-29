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

class ReportApiOppo(ReportChannelApiBase):
    # 获取第三方数据
    def getApiData(self, startTime):
        self.thirdBlockMap = self.getThirdBlockMap()
        if self.thirdBlockMap is None or len(self.thirdBlockMap) < 1:
            self.error("len(self.thirdBlockMap) < 1")
            self.saveApiLogData("len(self.thirdBlockMap) < 1", self.adsId, 0, str(self.requestDate))
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
            if self.thirdBlockMap.has_key(str(val['posId'])):
                appKey = self.thirdBlockMap[str(val['posId'])]['app_key']
                platform = self.getPlatformByAppkey(appKey)
                ad_type = self.thirdBlockMap[str(val['posId'])]['ad_type']
                key = appKey+str(self.requestDate)+str(ad_type)
                if appResult.has_key(key):
                    if round(val['income'] / configApi.EXCHANGE_RATE / 0.5,2) != 0.0:
                        appResult[key]["third_views"]= int(appResult[key]["third_views"])+val['view']
                        appResult[key]["third_clicks"] = int(appResult[key]["third_clicks"])+val['click']
                        appResult[key]["ad_income"] = appResult[key]["ad_income"] + round(val['income'] / configApi.EXCHANGE_RATE / 0.5,2)
                else:
                    appResult[key] = {
                        "ads_id": self.adsId,
                        "app_key": appKey,
                        "ad_type": ad_type,
                        "platform": platform,
                        "days": self.requestDate,
                        "ad_income": round(val['income'] / configApi.EXCHANGE_RATE / 0.5,2),
                        "third_views": val['view'],
                        "third_clicks": val['click'],
                    }
            else:
                self.saveApiLogData("APPID NOT FOUND appid="+str(val['posId']), self.adsId, 0,self.requestDate)
        if len(appResult) < 1:
            self.saveApiLogData("getListDataFromRemote len(result)<1!", self.adsId, 0,self.requestDate)
            self.info("getListDataFromRemote len(result)<1!")
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate):
        params = {
            'startTime': self.exchangeStrDate(requestDate,'%Y-%m-%d','%Y%m%d'),
            'endTime': self.exchangeStrDate(requestDate,'%Y-%m-%d','%Y%m%d'),
            'page': 1,
            'rows': 40,
            'order': 'desc',
            'orderBy': 'posName',
            'dimensions': 'posId',
            'timeGranularity': 'sum',
            'metrics': 'income,view,click',
        }
        return urllib.urlencode(params)

    #爬虫模块
    def spiderData(self,username,password):
        # Enable cookie support for urllib2
        login_page = r'https://u.oppomobile.com/union/login'
        cookiejar = cookielib.CookieJar()
        urlopener = urllib2.build_opener(urllib2.HTTPCookieProcessor(cookiejar))
        urllib2.install_opener(urlopener)
        urlopener.addheaders.append(('Referer', 'https://u.oppomobile.com/'))
        urlopener.addheaders.append(('Accept-Language', 'zh-CN'))
        urlopener.addheaders.append(('Host', 'u.oppomobile.com'))
        urlopener.addheaders.append(('User-Agent',
                                     'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'))
        urlopener.addheaders.append(('Connection', 'Keep-Alive'))
        imgurl = r'https://u.oppomobile.com/union/captcha'
        imageFilePath = self.DownloadFile(imgurl, urlopener)
        # authcode = raw_input('Please enter the authcode:')
        authcode = self.VerifyingCodeRecognization(imageFilePath)
        # Send login/password to the site and get the session cookie
        values = {'name': username, 'passwd': password, 'captcha': authcode}
        urlcontent = urlopener.open(urllib2.Request(login_page, urllib.urlencode(values)))
        page = urlcontent.read(500000)
        tk = None
        for item in cookiejar:
            if item.name == 'tk':
                tk = item.value
        urlopener.addheaders.append(('tk', tk))
        #urlopener.addheaders.append(('Content-Length', 182))
        params = self.getUrlParams(self.requestDate)
        contentUrl = r'https://u.oppomobile.com/union/static/report/query'
        resp = urlopener.open(urllib2.Request(contentUrl, params))
        html = resp.read()
        jsonData = json.loads(html)
        return jsonData['data']['items']


    def DownloadFile(self,fileUrl, urlopener):
        try:
            if fileUrl:
                filePath = r'./log/demo.jpg'
                outfile = open(filePath, 'w')
                outfile.write(urlopener.open(urllib2.Request(fileUrl)).read())
                outfile.close()
            else:
                print 'ERROR: fileUrl is NULL!'
        except:
            print "error!"
        return filePath

    # Verifying code recoginization
    def VerifyingCodeRecognization(self,imgfile):
        im = Image.open(imgfile)
        imgry = im.convert('L')
        sharpness = ImageEnhance.Contrast(imgry)
        sharp_img = sharpness.enhance(2.0)
        code = pytesseract.image_to_string(sharp_img)
        return code.replace(' ', '')

    # 抓取数据
    def getJosnData(self):
        username = r'1345982369@qq.com'
        password = r'Idsky2018'
        data = self.spiderData(username,password)
        return data

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiOppo('Oppo')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
