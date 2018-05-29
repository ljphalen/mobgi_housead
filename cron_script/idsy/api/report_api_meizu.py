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

class ReportApiMeizu(ReportChannelApiBase):
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
            if self.thirdAppMap.has_key(str(val['mzAppId'])):
                appKey = self.thirdAppMap[str(val['mzAppId'])]
                platform = self.getPlatformByAppkey(appKey)
                ad_type = 2 #meizu只有插页
                key = appKey+str(self.requestDate)
                appResult[key] = {
                    "ads_id": self.adsId,
                    "app_key": appKey,
                    "ad_type": ad_type,
                    "platform": platform,
                    "days": self.requestDate,
                    "ad_income": val['income']/configApi.EXCHANGE_RATE,
                    "third_views": val['expoTimes'],
                    "third_clicks": val['clickTimes']
                }
            else:
                self.saveApiLogData("APPID NOT FOUND appid="+str(val['mzAppId']), self.adsId, 0,self.requestDate)
        if len(appResult) < 1:
            self.saveApiLogData("getListDataFromRemote len(result)<1!", self.adsId, 0,self.requestDate)
            self.info("getListDataFromRemote len(result)<1!")
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate):
        params = {
            'startTime': self.exchangeTimeStamp(requestDate,'%Y-%m-%d'),
            'endTime': self.exchangeTimeStamp(requestDate,'%Y-%m-%d'),
            'legend': 'expoTimes',
            'adType': '',
            'byHours': '',
            'mzAppId': '',
            'sortKey': 'expoTimes',
            'sortOrder': -1,
            'style':''
        }
        return urllib.urlencode(params)

    #爬虫模块
    def spiderData(self,config):
        flag = True
        # Enable cookie support for urllib2
        # login_page = config['loginUrl']
        cookiejar = cookielib.CookieJar()
        urlopener = urllib2.build_opener(urllib2.HTTPCookieProcessor(cookiejar))
        urllib2.install_opener(urlopener)
        urlopener.addheaders.append(('Referer', 'https://ssp.flyme.cn/'))
        urlopener.addheaders.append(('Connection', 'keep-alive'))
        urlopener.addheaders.append(('Accept', 'application/json, text/plain, */*'))
        urlopener.addheaders.append(('Accept-Encoding', 'gzip, deflate'))
        urlopener.addheaders.append(('Accept-Language', 'zh-CN,zh;q=0.8,en;q=0.6'))
        urlopener.addheaders.append(('Cookie',
                                     'DSESSIONID=b09d9f06-091e-4078-8d99-ddc992af132b; uid=2740269; uname=idreamsky; userStatus=2; isAdmin=false; ucuid=; _domain=; JSESSIONID=; _islogin=true; _uid=2740269; _keyLogin=9a212004e15fb4da1405706c302525; _rmtk=989d245ee52b286a00c2db05181fc4; _uticket=ns_00fd150e1f32d9ab9d783769528b5cc6; _ckk=ns_0f59f554b9c6a37010e6b57bdce3cd25; _cct=313531b8dc2d96efb010d9ac70; lang=zh_CN; JSESSIONID=m116py1jacdngiqp1g1laf03zasoa'))
        urlopener.addheaders.append(('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8'))
        urlopener.addheaders.append(('Origin', 'https://ssp.flyme.cn'))
        urlopener.addheaders.append(('Host', 'ssp.flyme.cn'))
        urlopener.addheaders.append(('User-Agent',
                                     'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'))
        urlopener.addheaders.append(('Connection', 'Keep-Alive'))
        urlopener.addheaders.append(('X-Requested-With', 'XMLHttpRequest'))
        urlopener.addheaders.append(('X-Tingyun-Id', 'cLPkRJPvonQ;r=25165213'))
        params = self.getUrlParams(self.requestDate)
        contentUrl = config['dataUrl']+'?t='+str(time.time())
        resp = urlopener.open(urllib2.Request(contentUrl, params))
        html = resp.read()
        jsonData = json.loads(html)
        if jsonData['code'] == 200:
            return jsonData['value']


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
        configMap = {
            'dataUrl': configApi.MeizuGetDataUrl
        }
        data = self.spiderData(configMap)
        return data

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiMeizu('Meizu')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
