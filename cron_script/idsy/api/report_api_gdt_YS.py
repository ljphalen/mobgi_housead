#!/usr/bin/env python
# -*- coding:utf-8 -*-
import sys
import time
import urllib
import urllib2
import json
import ConfigParser
from report_api_base import ReportApiBase
import config.api as configApi
import hashlib
import base64


class ReportApiGdt(ReportApiBase):
    # 获取第三方数据
    def getApiData(self, startTime):
        self.thirdPosMap = self.getThirdBlockMap()
        if self.thirdPosMap is None or len(self.thirdPosMap) < 1:
            self.error("len(self.thirdAppMap) < 1")
            self.saveApiLogData("len(self.thirdAppMap) < 1", self.adsId, 0, str(self.requestDate))
            exit()
        self.requestDate = time.strftime('%Y-%m-%d', time.localtime(startTime))
        self.info("requestDate：" + str(self.requestDate))
        result = self.getRemoteData()
        return result

    # 获取远程数据 GDT需要两种映射，一种是APPKEY映射，一种是三方poskey映射
    def getRemoteData(self):
        returnResult = self.getJosnData()
        self.mapPoskeyList = self.explainThirdPosKey()
        self.mapAppkeyList = self.explainThirdMapConf()
        appResult = {}
        if len(returnResult) < 1:
            self.info("len(listData); return!")
            self.saveApiLogData("data is empty[]", self.adsId, 0,str(self.requestDate))
            return False
        exchange_rate = configApi.EXCHANGE_RATE
        for val in returnResult:
            #广告位ID
            siteId = val['PlacementId']
            if self.mapAppkeyList.has_key(siteId):
                appKey = self.mapAppkeyList[siteId]
                blockId = self.mapPoskeyList[siteId]
                platform = self.getPlatformByAppkey(appKey)
                intergration_type = self.thirdPosMap[siteId]['ad_type']
                key = appKey + blockId + str(intergration_type) + val['Date']
                # 过滤广点通数值的逗号
                Revenue = val['Revenue'].replace(',', '')
                thirdView = val['Pv'].replace(',', '')
                thirdClick = val['Click'].replace(',', '')
                # 将收入由人民币转换为美元
                cn_ad_income = float(Revenue)
                usa_ad_income = round(cn_ad_income / exchange_rate, 2)
                hour = 0
                appResult[key] = {
                    "ads_id": self.adsId,
                    "app_key": appKey,
                    "ad_type": intergration_type,
                    "platform": platform,
                    "days": self.requestDate,
                    "hours": hour,
                    "ad_income": usa_ad_income,
                    "third_views": thirdView,
                    "pos_key": blockId,
                    "third_clicks": thirdClick
                }
            else:
                self.saveApiLogData("thirdPosMap has not third_pos_key:"+siteId, self.adsId, 0, str(self.requestDate))
                self.info("thirdPosMap has not third_pos_key: " + siteId)
        return appResult

    # 映射第三方广告位
    def explainThirdPosKey(self):
        mapPoskeyList = {}
        if self.thirdPosMap is None or self.adsId is None:
            return mapPoskeyList
        for val in self.thirdPosMap:
            mapPoskeyList[val] = self.thirdPosMap[val]['pos_key']
        self.info("Relate to blockId: pos key " + str(mapPoskeyList))
        return mapPoskeyList

    # 映射第三方APPKEY
    def explainThirdMapConf(self):
        mapAppkeyList = {}
        if self.thirdPosMap is None or self.adsId is None:
            return mapAppkeyList
        for val in self.thirdPosMap:
            mapAppkeyList[val] = self.thirdPosMap[val]['app_key']
        return mapAppkeyList



    # 组装参数
    def getUrlParams(self):
        memberid = configApi.GDTMemberid
        requestDate = self.exchangeStrDate(self.requestDate,"%Y-%m-%d",'%Y%m%d')
        params = {}
        params['memberId'] = memberid
        params['start_date'] = requestDate
        params['end_date'] = requestDate
        return urllib.urlencode(params)


    # 获取token，20分钟失效，超过19分钟取一次
    def getToken(self):
        cf = ConfigParser.ConfigParser()
        confname = 'report_api_gdt.conf'
        cf.read(confname)
        curtable = 'create_time'
        token_time = cf.getint("gdt_token", curtable)
        now_time = int(time.mktime(time.localtime()))
        if token_time < now_time - 1140:
            token_time = now_time
            cf.set("gdt_token", curtable, token_time)
            cf.write(open(confname, "w"))
        appid = configApi.GDTAppid
        appkey = configApi.GDTAppkey
        agid = configApi.GDTAgid
        # 组合sign_key
        sign_key = str(appid) + str(appkey) + str(token_time)
        sign = hashlib.sha1(sign_key).hexdigest()
        # 组合token_key
        token_key = str(agid) + "," + str(appid) + "," + str(token_time) + "," + str(sign)
        token = base64.b64encode(token_key)
        return token

    # 抓取数据
    def getJosnData(self):
        token = self.getToken()
        url = configApi.GDTApiHost+token
        data_urlencode = self.getUrlParams()
        req = urllib2.Request(url, data_urlencode)
        req.add_header("Content-Type", "application/x-www-form-urlencoded")  # 这里通过add_header方法很容易添加的请求头
        responseResult = urllib2.urlopen(req)
        self.info("send request url=" + url + ", params=" + data_urlencode)
        status_code = responseResult.getcode()
        if status_code != 200:
            self.error("get api data response error!  code=" + str(status_code))
            #这里要写入一个标记#状态，错误原因，app_key,这个是全局错误，只限于广告商，不限于应用
            self.saveApiLogData("get api data response error"+str(status_code),self.adsId,0,str(self.requestDate))
            return False
        try:
            html = responseResult.read()
            jsonStr = json.loads(html)
            if int(jsonStr['ret']) != 0:
                self.info("get api data response failed! error msg:" + str(jsonStr['msg']))
                self.saveApiLogData("get api data response failed! error msg:"+ str(jsonStr['msg']), self.adsId, 0, str(self.requestDate))
                # 休眠5秒
                time.sleep(5)
                return False
            else:
                if jsonStr.has_key('data'):
                    returnData = jsonStr['data']
                self.info("get data jsonStr=" + str(jsonStr))
                self.saveApiLogData("-", self.adsId, 1, str(self.requestDate))
        except Exception, e:
            # 这里要写入一个标记
            self.saveApiLogData("parse json error", self.adsId, 0,str(self.requestDate))
            raise Exception('parse json error:' + str(e))
        return returnData

    # endtime
    def getNextDate(self, request_date):
        start_timeArray = time.strptime(request_date, "%Y-%m-%d")
        start_timestamp = int(time.mktime(start_timeArray)) + 86400
        return time.strftime("%Y-%m-%d", time.localtime(start_timestamp))


if __name__ == '__main__':
    startTimeStamp = int(time.time())
    while 1:
        obj = ReportApiGdt('GDT_YS')
        obj.run()
        time.sleep(1)
        # 脚步执行时间超过30分钟直接跳出
        if int(time.time() - startTimeStamp) > 1800:
            break
