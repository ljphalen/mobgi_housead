#!/usr/bin/env python
# -*- coding:utf-8 -*-

import time
import json
import cookielib
import urllib2
import urllib
from report_api_base import ReportApiBase
import config.api as configApi
from PIL import Image,ImageEnhance
import pytesseract

class ReportApiYezi(ReportApiBase):
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
            if self.thirdAppMap.has_key(str(val['appKey'])):
                appKey = self.thirdAppMap[str(val['appKey'])]
                platform = self.getPlatformByAppkey(appKey)
                if val['systemDesc'] == 'Android':
                    ad_type = 1
                else:
                    ad_type = 2
                key = appKey+str(self.requestDate)+str(ad_type)
                if appResult.has_key(key):
                    if round(val['effectiveIncome'] / configApi.EXCHANGE_RATE / 0.5,2) != 0.0:
                        appResult[key]["third_views"]= int(appResult[key]["third_views"])+val['realImpressions']
                        appResult[key]["third_clicks"] = int(appResult[key]["third_clicks"])+val['realClickRate']
                        appResult[key]["ad_income"] = appResult[key]["ad_income"] + round(val['effectiveIncome'] / configApi.EXCHANGE_RATE,2)
                else:
                    appResult[key] = {
                        "ads_id": self.adsId,
                        "app_key": appKey,
                        "ad_type": ad_type,
                        "platform": platform,
                        "days": self.requestDate,
                        "hours":0,
                        "ad_income": round(val['effectiveIncome'] / configApi.EXCHANGE_RATE,2),
                        "third_views": val['realImpressions'],
                        "third_clicks": val['realClickRate'],
                    }
            else:
                self.saveApiLogData("APPID NOT FOUND appid="+str(val['appKey']), self.adsId, 0,self.requestDate)
        if len(appResult) < 1:
            self.saveApiLogData("getListDataFromRemote len(result)<1!", self.adsId, 0,self.requestDate)
            self.info("getListDataFromRemote len(result)<1!")
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate):
        params = {
            'acctIdSearch':'',
            'quickSearch': '',
            'system': '',
            'startTime': requestDate,
            'endTime': requestDate,
            'accessType': '',
            'page': '1',
            'rows': '50',
            'order': 'asc',
            '_':int(time.time())
        }
        return urllib.urlencode(params)

    #爬虫模块
    def spiderData(self,username,password):
        # Enable cookie support for urllib2
        # http://ssp.securecloud.com.cn/login/signin?loginName=ledou&pwd=ledou123
        login_page = r'http://ssp.securecloud.com.cn/login/signin?loginName=ledou&pwd=ledou123'
        cookiejar = cookielib.CookieJar()
        urlopener = urllib2.build_opener(urllib2.HTTPCookieProcessor(cookiejar))
        urllib2.install_opener(urlopener)
        urlopener.addheaders.append(('Accept', 'application/json, text/javascript, */*; q=0.01'))
        urlopener.addheaders.append(('Accept-Encoding', 'gzip, deflate, sdch'))
        urlopener.addheaders.append(('Accept-Language', 'zh-CN,zh;q=0.8,en;q=0.6'))
        urlopener.addheaders.append(('Host', 'ssp.securecloud.com.cn'))
        urlopener.addheaders.append(('User-Agent',
                                     'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'))
        urlopener.addheaders.append(('Connection', 'Keep-Alive'))
        urlopener.addheaders.append(('Cookie','session_cookie_mssp=5D3703600FFA3A37AFC93221D6E44F0B'))
        urlopener.addheaders.append(('Referer', 'http://ssp.securecloud.com.cn/view/login.html'))
        urlcontent = urlopener.open(urllib2.Request(login_page))
        page = urlcontent.read(500000)
        session_cookie_mssp = None
        for item in cookiejar:
           if item.name == 'session_cookie_mssp':
                session_cookie_mssp = item.value

        #查找数据
        cookiejar2 = cookielib.CookieJar()
        urlopener2 = urllib2.build_opener(urllib2.HTTPCookieProcessor(cookiejar2))
        urllib2.install_opener(urlopener)
        urlopener2.addheaders.append(('Cookie', 'session_cookie_mssp=' + str(session_cookie_mssp)))
        urlopener2.addheaders.append(('Referer', 'http://ssp.securecloud.com.cn/view/index.html'))
        urlopener2.addheaders.append(('Host', 'ssp.securecloud.com.cn'))
        urlopener2.addheaders.append(('User-Agent',
                                     'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'))
        urlopener2.addheaders.append(('Accept', '*/*'))
        urlopener2.addheaders.append(('User-Agent','Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'))
        params = self.getUrlParams(self.requestDate)
        #acctIdSearch=&quickSearch=&system=&startTime=2018-01-21&endTime=2018-01-21&accessType=&page=1&rows=50&order=asc&_=1516613907231
        contentUrl = r'http://ssp.securecloud.com.cn/appReport/select/userReport?'+params
        resp = urlopener2.open(urllib2.Request(contentUrl))
        html = resp.read()
        jsonData = json.loads(html)
        return jsonData['rows']


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
        username = r'ledou'
        password = r'ledou123'
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
        obj = ReportApiYezi('Yezi')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
