#!/usr/bin/env python
# -*- coding:utf-8 -*-
import time
import json
import cookielib
import urllib2
import httplib2
import urllib
from report_api_base import ReportApiBase
import config.api as configApi
from PIL import Image,ImageEnhance
import pytesseract



class ReportApiAdview(ReportApiBase):
    adType = {1:6,2:1,4:5}#0=>横幅,1=>插屏广告,5=>开屏,6=>视频,8=>原生信息,10=>互动效果
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
            tmp = val.split('_')
            appKey = tmp[0]
            platform = self.getPlatformByAppkey(appKey)
            ad_type = tmp[1]
            key = appKey+str(self.requestDate)+ad_type
            appResult[key] = {
                "ads_id": self.adsId,
                "app_key": appKey,
                "ad_type": ad_type,
                "platform": platform,
                "hours":0,
                "days": self.requestDate,
                "ad_income": round(returnResult[val]['income']/configApi.EXCHANGE_RATE,2),
                "third_views": int(returnResult[val]['dis']),
                "third_clicks": int(returnResult[val]['cli'])
            }
        if len(appResult) < 1:
            self.saveApiLogData("getListDataFromRemote len(result)<1!", self.adsId, 0,self.requestDate)
            self.info("getListDataFromRemote len(result)<1!")
        return appResult

    # 组装参数
    def getUrlParams(self, requestDate,reportId,adtype):
        params = {
            'sdkType':adtype,
            'endDate':requestDate,
            'startDate':requestDate,
            'flag':'on',
            'appId':reportId,
            'adFill':1,
            'chartType':1,
        }
        return urllib.urlencode(params)

    #爬虫模块
    def spiderData(self,config,reportId,adtype):
        flag = True
        try:
            # Enable cookie support for urllib2
            #login_page = config['loginUrl']
            cookiejar = cookielib.CookieJar()
            urlopener = urllib2.build_opener(urllib2.HTTPCookieProcessor(cookiejar))
            urllib2.install_opener(urlopener)
            urlopener.addheaders.append(('Referer', 'http://www.adview.cn/user/bid/income'))
            urlopener.addheaders.append(('Connection', 'keep-alive'))
            urlopener.addheaders.append(('Accept', '*/*'))
            urlopener.addheaders.append(('Accept-Encoding', 'gzip, deflate'))
            urlopener.addheaders.append(('Accept-Language', 'zh-CN,zh;q=0.8,en;q=0.6'))
            urlopener.addheaders.append(('Cookie', '_qddamta_4000131400=3-0; tencentSig=2086236160; websession=1126443e88f94b808e7f3eb6ba2a1c38; _qddaz=QD.edfwp8.g67wbc.jfkf1zc3; _qdda=3-1.3obubl; _qddab=3-msvwk2.jfkf1zc4'))
            urlopener.addheaders.append(('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8'))
            urlopener.addheaders.append(('Origin', 'www.adview.cn'))
            urlopener.addheaders.append(('Host', 'www.adview.cn'))
            urlopener.addheaders.append(('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'))
            urlopener.addheaders.append(('Connection', 'Keep-Alive'))
            urlopener.addheaders.append(('X-Requested-With', 'XMLHttpRequest'))
            urlopener.addheaders.append(('X-Tingyun-Id', 'cLPkRJPvonQ;r=5783640'))
            # verifyurl = config['verifyUrl']
            # imageFilePath = self.DownloadFile(verifyurl, urlopener)#获取验证码
            # # authcode = raw_input('Please enter the authcode:')
            # authcode = self.VerifyingCodeRecognization(imageFilePath)
            # for item in cookiejar:
            #     print item.name+'>'+item.value
            # Send login/password to the site and get the session cookie
            # keepRequest=httplib2.Http('.cache')
            # r,c = keepRequest.request(login_page,'GET')
            # sc = c.decode('utf-8')
            # regx = r'<input type="hidden" name="csrf" value="(\S+?)" />'
            # tmp = re.search(regx,sc)
            # csrf = tmp.group(1)  # 获取csrf
            # values = {'username': config['userName'], 'password': config['passWord'], 'captcha': authcode, 'autologin': 1,'csrf': csrf}
            # r, c = urlopener.open(keepRequest.request(login_page, 'POST', body=urllib.urlencode(values)))
            # exit()
            # req = urlopener.open(login_page)
            # login_html=req.read()
            # print login_html
            # exit()
            # regx = r'<input type="hidden" name="csrf" value="(\S+?)" />'
            # tmp = re.search(regx, login_html)
            # csrf = tmp.group(1) #获取csrf
            # print csrf
            # exit()
            # values = {'username': config['userName'], 'password': config['passWord'], 'captcha': authcode,'autologin':1,'csrf':csrf}
            # print login_page
            # urlcontent = urlopener.open(urllib2.Request(login_page, urllib.urlencode(values)))
            #
            # for item in cookiejar:
            #     print item.name+'>'+item.value
            # exit()
            # tk = None
            # for item in cookiejar:
            #     if item.name == 'beaker.session.id':
            #         tk = item.value
            # urlopener.addheaders.append(('beaker.session.id', tk))
            # #urlopener.addheaders.append(('Content-Length', 182))
            # params = self.getUrlParams(self.requestDate)
            params = self.getUrlParams(self.requestDate,reportId,self.adType[adtype])
            contentUrl = config['dataUrl']
            resp = urlopener.open(urllib2.Request(contentUrl, params))
            html = resp.read()
            jsonData = json.loads(html)
        except Exception,e:
            flag = False
            self.info(reportId+":"+str(e))
        if jsonData['allTypeIncome']:
            flag = True
        else:
            flag = False
        if flag:
            return jsonData['allTypeIncome'][self.requestDate]
        else:
            return False


    def DownloadFile(self,fileUrl, urlopener):
        try:
            if fileUrl:
                filePath = r'./log/verify_adview.jpg'
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
        bpoint = orig_color = (0, 0, 0)
        wpoint = replacement_color = (255, 255, 255)
        rpoint = (255, 0, 0)
        img = Image.open(imgfile).convert('RGB')
        datas = img.getdata()
        w, h = img.size
        newData = []
        for item in datas:
            if item == bpoint:
                newData.append(wpoint)
            else:
                if item[0] > 128 and item[1] < 128 and item[2] < 128:
                    newData.append(rpoint)
                else:
                    newData.append(wpoint)
        for x in xrange(1, w - 1):
            for y in xrange(1, h - 1):
                black_point = 0
                good_point = 0
                mid_pixel = newData[w * y + x]  # 中央像素点像素值
                if mid_pixel != wpoint:  # 找出上下左右四个方向像素点像素值
                    top_pixel = newData[w * (y - 1) + x]
                    left_pixel = newData[w * y + (x - 1)]
                    down_pixel = newData[w * (y + 1) + x]
                    right_pixel = newData[w * y + (x + 1)]
                    # 判断上下左右的黑色像素点总个数
                    if abs(sum(top_pixel) - sum(mid_pixel)) > 30:
                        black_point += 1
                    if abs(sum(left_pixel) - sum(mid_pixel)) > 30:
                        black_point += 1
                    if abs(sum(down_pixel) - sum(mid_pixel)) > 30:
                        black_point += 1
                    if abs(sum(right_pixel) - sum(mid_pixel)) > 30:
                        black_point += 1
                    # if mid_pixel[0]>:
                    if abs(sum(top_pixel) - sum(mid_pixel)) > 128:
                        good_point += 1
                    if abs(sum(left_pixel) - sum(mid_pixel)) > 128:
                        good_point += 1
                    if abs(sum(down_pixel) - sum(mid_pixel)) > 128:
                        good_point += 1
                    if abs(sum(right_pixel) - sum(mid_pixel)) > 128:
                        good_point += 1
                    if black_point >= 3:
                        newData[w * y + x] = wpoint
                    elif mid_pixel == wpoint and good_point >= 3:
                        newData[w * y + x] = rpoint
        img.putdata(newData)
        code = pytesseract.image_to_string(img)
        code = code.replace(' ', '')
        if len(code) != 4:
            self.getJosnData()
        return code

    # 抓取数据
    def getJosnData(self):
        configMap = {
            'userName':configApi.AdviewUsername,
            'passWord':configApi.AdviewPassWord,
            'loginUrl':configApi.AdviewLoginUrl,
            'verifyUrl':configApi.AdviewVerifyUrl,
            'dataUrl':configApi.AdviewGetDataUrl
        }
        data = {}
        for reportId in self.thirdAppMap:
            for adtype in self.adType:
                key = self.thirdAppMap[reportId]+'_'+str(adtype)
                if self.spiderData(configMap,reportId,adtype):
                    data[key] = self.spiderData(configMap,reportId,adtype)
                else:
                    continue
        if data:
            return data
        else:
            return False

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        obj = ReportApiAdview('Adview')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
