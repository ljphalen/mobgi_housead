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

class ReportApiAliyunYumi(ReportChannelApiBase):
    # 获取第三方数据
    def getApiData(self, startTime):
        #self.thirdAppMap = self.getThirdAppMap()
        self.thirdAppMap ={'e19081b4527963d70c7a':'f548c4c73555d164b784f7445d995cf7','8E69498B356D95CCB579':'88768ef3368e492f820edc524d069d77'}
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
            tmp = val.split('_')
            appKey = tmp[1]
            platform = self.getPlatformByAppkey(appKey)
            ad_type = 2
            key = appKey+str(self.requestDate)
            ad_income = returnResult[val][0][-1].encode('utf-8')
            ad_income = float(ad_income)
            third_views = str(returnResult[val][0][5]).replace(',','')
            third_clicks = str(returnResult[val][0][7]).replace(',','')
            appResult[key] = {
                "ads_id": self.adsId,
                "app_key": appKey,
                "ad_type": ad_type,
                "platform": platform,
                "days": self.requestDate,
                "ad_income": ad_income/configApi.EXCHANGE_RATE/0.5,
                "third_views": third_views,
                "third_clicks": third_clicks
            }
        if len(appResult) < 1:
            self.saveApiLogData("getListDataFromRemote len(result)<1!", self.adsId, 0,self.requestDate)
            self.info("getListDataFromRemote len(result)<1!")
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate,report_id):
        params = {
            'starDate': requestDate,
            'endDate': requestDate,
            'pageNum':10,
            'app':report_id,
            'adStyle':3,
            'region':''
        }
        return urllib.urlencode(params)

    #爬虫模块
    def spiderData(self,config,report_id):
        flag = True
        # Enable cookie support for urllib2
        # login_page = config['loginUrl']

        cookiejar = cookielib.CookieJar()
        urlopener = urllib2.build_opener(urllib2.HTTPCookieProcessor(cookiejar))
        urllib2.install_opener(urlopener)
        urlopener.addheaders.append(('Referer', 'https://developers.yumimobi.com/index.php/AppList/appDetails'))
        urlopener.addheaders.append(('Connection', 'keep-alive'))
        urlopener.addheaders.append(('Accept', 'application/json, text/plain, */*'))
        urlopener.addheaders.append(('Accept-Encoding', 'gzip, deflate'))
        urlopener.addheaders.append(('Accept-Language', 'zh-CN,zh;q=0.8,en;q=0.6'))
        urlopener.addheaders.append(('Cookie',
                                     'session_id=eonbkptd940e719nmb08ebq9t6; Hm_lvt_55e25d6e6a6997e5f4bf65be2cc85fdb=1513152967,1514277757,1514340005,1514344509; Hm_lpvt_55e25d6e6a6997e5f4bf65be2cc85fdb=1514344512; PHPSESSID=eonbkptd940e719nmb08ebq9t6; local_lang=zh'))
        urlopener.addheaders.append(('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8'))
        urlopener.addheaders.append(('Origin', 'https://www.yousuode.cn'))
        urlopener.addheaders.append(('Host', 'developers.yumimobi.com'))
        urlopener.addheaders.append(('User-Agent',
                                     'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'))
        params = self.getUrlParams(self.requestDate,report_id)
        contentUrl = config['dataUrl']
        resp = urlopener.open(urllib2.Request(contentUrl, params))
        html = resp.read()
        jsonData = json.loads(html)
        if jsonData['msg'] == 'OK':
            return jsonData['data']['tableList']


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
            'dataUrl': configApi.AliyunYumiGetDataUrl
        }
        data = {}
        for item in self.thirdAppMap:
            data[str(self.requestDate)+'_'+item] = self.spiderData(configMap,self.thirdAppMap[item])
        return data

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiAliyunYumi('Yumi')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
