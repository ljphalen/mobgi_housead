#!/usr/bin/python
# -*- coding:utf-8 -*-

import os
import time
import MySQLdb
import urllib
import urllib2
import datetime
import logging
import intergration_config
import MySQLdb.cursors
import json
import sys
import hashlib
import base64
from intergration_base import IntergrationBase
from CustomCursors import CustomCursors
import code
import ConfigParser

reload(sys)
sys.setdefaultencoding('utf-8')


# 因为不同广告商用的是不同的接口，所以添加不同的广告商时需要修改getListDataFromRemote方法解析
# gdt  token 每隔20分钟失效

class IntergrationGdt(IntergrationBase):
    DEAFALT_RUN_TIME = 8  # 跑数据时间

    # gdt
    def getThirdPartMapConf(self):
        sql = "select * from config_third_ad_pos where ads_id ='" + self.ads_id + "'"
        promote_db_cursor = self.promote_db.cursor()
        promote_db_cursor.execute(sql)
        row = promote_db_cursor.fetchall()
        promote_db_cursor.close()
        return row

    def explainThirdMapConf(self, conf, type):
        mapAppkeyList = {}
        if type is None or conf is None:
            return mapAppkeyList
        for val in conf:
            mapAppkeyList[val['third_pos_key']] = val['app_key']
        return mapAppkeyList

    def getThirdReportMapConf(self, adsId):
        thirdPartMapConf = self.getThirdPartMapConf()
        mapAppkeyList = self.explainThirdMapConf(thirdPartMapConf, adsId)
        return mapAppkeyList

    # 映射第三方广告位
    def explainThirdPosKey(self, conf, type):
        mapPoskeyList = {}
        if type is None or conf is None:
            return mapPoskeyList
        for val in conf:
            mapPoskeyList[val['third_pos_key']] = val['pos_key']
        self.mylog("Relate to blockId: pos key " + str(mapPoskeyList))
        return mapPoskeyList

    def getListDataFromRemote(self):
        self.mylog("mapAppkeyList:" + str(self.mapAppkeyList))
        if len(self.mapAppkeyList) < 1:
            self.errlog("len(self.mapAppkeyList) < 1")
            exit()
        try:
            applicationList = self.getListDataResult()

            # 格式化数据
            result = self.formatData(applicationList)
            return result
        except Exception, e:
            self.mylog("Deal remote data error " + str(e))
            return False

    def formatData(self, applicationList):
        result = {}
        thirdPartMapConf = self.getThirdPartMapConf()
        self.mapPoskeyList = self.explainThirdPosKey(thirdPartMapConf, self.ads_id)
        if len(applicationList) < 1:
            self.errlog("len(listData); return!")
            return result
        # 人民币对应美元的汇率
        exchange_rate = intergration_config.EXCHANGE_RATE
        for val in applicationList:
            # 广告位ID
            siteId = val['PlacementId']
            if self.mapAppkeyList.has_key(siteId):
                appKey = self.mapAppkeyList[siteId]
                blockId = self.mapPoskeyList[siteId]
                platform = self.getPlatformByAppkey(appKey)
                key = appKey + blockId + val['Date']
                # 过滤广点通数值的逗号
                Revenue = val['Revenue'].replace(',', '')
                thirdView = val['Pv'].replace(',', '')
                thirdClick = val['Click'].replace(',', '')
                # 将收入由人民币转换为美元
                cn_ad_income = float(Revenue)
                usa_ad_income = round(cn_ad_income / exchange_rate, 2)
                hour = 0
                result[key] = {
                    "ads_id": self.ads_id,
                    "app_key": appKey,
                    "intergration_type": 2,
                    "platform": platform,
                    "date_of_log": self.requestDate,
                    "hour_of_log": hour,
                    "ad_income": usa_ad_income,
                    "third_views": thirdView,
                    "block_id": blockId,
                    "third_clicks": thirdClick
                    }
        if len(result) < 1:
            self.mylog("formatData len(result)<1!")
        return result

    def getHttpCode(self, responseResult):
        return responseResult.getcode()

    # 获取token，20分钟失效，超过19分钟取一次
    def getToken(self):
        cf = ConfigParser.ConfigParser()
        confname = 'intergration_gdt.conf'
        cf.read(confname)
        curtable = 'create_time'
        token_time = cf.getint("gdt_token", curtable)
        now_time = int(time.mktime(time.localtime()))
        if token_time < now_time - 1140:
            token_time = now_time
            cf.set("gdt_token", curtable, token_time)
            cf.write(open(confname, "w"))

        appid = intergration_config.GDTAppid
        appkey = intergration_config.GDTAppkey
        agid = intergration_config.GDTAgid
        # 组合sign_key
        sign_key = str(appid) + str(appkey) + str(token_time)
        sign = hashlib.sha1(sign_key).hexdigest()
        # 组合token_key
        token_key = str(agid) + "," + str(appid) + "," + str(token_time) + "," + str(sign)
        token = base64.b64encode(token_key)
        return token

    def getListDataResult(self):
        memberid = intergration_config.GDTMemberid
        requestDate = time.strftime("%Y%m%d", time.localtime(int(self.startPosition)))
        token = self.getToken()
        # post 传参
        params = {
            "memberId": memberid,
            "start_date": requestDate,
            "end_date": requestDate
            }
        data_urlencode = urllib.urlencode(params)
        url = intergration_config.GDTApiHost + token
        req = urllib2.Request(url, data_urlencode)
        req.add_header("Content-Type", "application/x-www-form-urlencoded")  # 这里通过add_header方法很容易添加的请求头
        responseResult = urllib2.urlopen(req)
        self.mylog("send request to get data url=" + url + ", params=" + data_urlencode)
        code = self.getHttpCode(responseResult)
        if code != 200:
            self.mylog("get api data response error!  code=" + str(code))
            return False
        returnData = {}
        try:
            html = responseResult.read()
            jsonStr = json.loads(html)
            # 判断返回信息是否出错
            if int(jsonStr['ret']) != 0:
                self.mylog("get api data response failed! error msg:" + str(jsonStr['msg']))
                # 休眠5秒
                time.sleep(5)
                return False
            if jsonStr.has_key('data'):
                returnData = jsonStr['data']
            self.mylog("get data jsonStr=" + str(jsonStr))
        except Exception, e:
            self.mylog('parse  json error' + str(e))
            return False
        return returnData


if __name__ == '__main__':
    startTimeStamp = time.time()
    while 1:
        intergrctionGdt = IntergrationGdt("GDT_YS")
        intergrctionGdt.run()
        time.sleep(intergration_config.SLEEPSECOND)
        # 脚步执行时间超过50分钟直接跳出
        if int(time.time() - startTimeStamp) > 3000:
            break
