#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
from tool_base import ToolBase
import config.db as configDb
import urllib2
import urllib
import traceback
import hashlib
import random
import json

#markingAPI缓存脚本
class toolMarket(ToolBase):
    # 初始化条件
    def init(self):
        self.dbMarket = self.initDb(configDb.MYSQ_MOBGI_MARKET)  #markingAPI数据
        self.authUserTable = 'market_user_auth'
        self.adCacheTabel = 'market_ads'
        self.adGroupCacheTable = 'market_adgroup'
        self.adcreativeCacheTabel = 'market_adcreative'
        self.adcampaignCacheTabel = 'market_campaign'
        self.apiMap = {
            'campaigns':'https://api.e.qq.com/v1.0/campaigns/get',
            'ads':'https://api.e.qq.com/v1.0/ads/get',
            'adgroups':'https://api.e.qq.com/v1.0/adgroups/get',
            'adcreatives':'https://api.e.qq.com/v1.0/adcreatives/get',
        }


    def getData(self):
        data = {}
        for type in self.apiMap:
            data[type] = self.getApiData(type,self.apiMap[type])
        return data

    def filterData(self,type,data):
        result = []
        now = self.startTime
        if type == 'campaigns':
            for item in data.values():
                values = (item["account_id"], item["campaign_name"], item["out_campaign_id"], now, item["campaign_name"])
                result.append(values)
            checkResult  = self.saveCampaignsData(result)
        elif type == 'ads':
            for item in data.values():
                values = (item["account_id"], item["ads_name"], item["out_ads_id"], now, item["ads_name"])
                result.append(values)
            checkResult = self.saveAdsData(result)
        elif type == 'adgroups':
            for item in data.values():
                values = (item["account_id"], item["adgroups_name"], item["out_adgroups_id"], now, item["adgroups_name"])
                result.append(values)
            checkResult = self.saveAdgroupData(result)
        elif type == 'adcreatives':
            for item in data.values():
                values = (item["account_id"], item["adcreatives_name"], item["out_adcreatives_id"], now, item["adcreatives_name"])
                result.append(values)
            checkResult = self.saveAdcreativesData(result)
        return checkResult

    def saveData(self,data):
        for type in data:
            if len(data[type]) > 0:
                if self.filterData(type,data[type]) is False:
                    return False
            else:
                continue
        return True


    def saveCampaignsData(self,data):
        sql = """insert into market_campaign(account_id,campaign_name,out_campaign_id,create_time)values
                (%s,%s,%s,%s)on duplicate key update campaign_name=%s"""
        self.info("data:" + str(data))
        return self.dbMarket.executeMany(sql, data)

    def saveAdsData(self,data):
        sql = """insert into market_ads(account_id,ads_name,out_ads_id,create_time)values
                        (%s,%s,%s,%s)on duplicate key update ads_name=%s"""
        self.info("data:" + str(data))
        return self.dbMarket.executeMany(sql, data)

    def saveAdgroupData(self,data):
        sql = """insert into market_adgroup(account_id,adgroup_name,out_adgroup_id,create_time)values
                                (%s,%s,%s,%s)on duplicate key update adgroup_name=%s"""
        self.info("data:" + str(data))
        return self.dbMarket.executeMany(sql, data)

    def saveAdcreativesData(self,data):
        sql = """insert into market_adgroup(account_id,adcreatives_name,out_adcreatives_id,create_time)values
                                        (%s,%s,%s,%s)on duplicate key update adcreatives_name=%s"""
        self.info("data:" + str(data))
        return self.dbMarket.executeMany(sql, data)

    def getApiData(self,type,url):
        auths = self.getAuths()
        returnData = {}
        for accountId in auths:
            jsonData = {}
            page = 1
            jsonData[page]= self.requestApi(url,auths[accountId],accountId,type,page)
            if jsonData[page] is False:
                continue
            pages = int(jsonData[page]['data']['page_info']['total_number'] / 100) + 1
            print 'pages is:'+str(pages)
            if pages>1:
                for i in xrange(2,pages+1):
                    jsonData[i] = self.requestApi(url, auths[accountId], accountId, type, i)
            if jsonData is False:
                continue
            list = {}
            for page in jsonData:
                list = jsonData[page]['data']['list']
                if type == 'campaigns':
                    for item in list:
                        key = str(accountId)+str(item['campaign_id'])
                        returnData[key] = {
                            'campaign_name':item['campaign_name'],
                            'out_campaign_id':item['campaign_id'],
                            'account_id':accountId,
                        }
                elif type == 'ads':
                    for item in list:
                        key = str(accountId) +str(item['ad_id'])
                        returnData[key] = {
                            'ads_name': item['ad_name'],
                            'out_ads_id': item['ad_id'],
                            'account_id': accountId,
                        }
                elif type == 'adgroups':
                    for item in list:
                        key = str(accountId) + str(item['adgroup_id'])
                        returnData[key] = {
                            'adgroup_name': item['adgroup_name'],
                            'out_adgroup_id': item['adgroup_id'],
                            'account_id': accountId,
                        }
                elif type == 'adcreatives':
                    for item in list:
                        key = str(accountId) + item['adcreative_template_id']
                        returnData[key] = {
                            'adcreative_name': item['adcreative_sample_image']["name"],
                            'out_adcreative_id': item['adcreative_template_id'],
                            'account_id': accountId,
                        }
        return returnData


    def requestApi(self,url,token,accountId,type,page):
        requestUrl = url + '?' + self.getParams(token, accountId,page)
        self.info('Type:' + type + "Url:" + requestUrl)
        try:
            req = urllib2.Request(requestUrl)
            res = urllib2.urlopen(req)
        except urllib2.HTTPError, error:
            self.error("response error! status=%s,%s" % (error.code, error.read()))
            return False
        html = res.read()
        jsonData = json.loads(html)
        if jsonData['code'] != 0:
            self.error('error:' + jsonData['message'] + 'code:' + str(jsonData['code']) + 'apiUrl:' + str(url))
            return False
        return jsonData

    def getParams(self,token,account_id,page):
        nonce = hashlib.md5(str(int(time.time())+random.randint(100000,999999))).hexdigest()
        params = {
            'access_token': token,
            'timestamp': int(time.time()),
            'account_id': account_id,
            'nonce': nonce,
            'page':page,
            'page_size':100,
        }
        return urllib.urlencode(params)


    #获取所有的权限
    def getAuths(self):
        sql = "select account_id,access_token from %s where state = 'ON'"%(self.authUserTable)
        auths,count = self.dbMarket.fetchall(sql)
        if count == 0:
            self.error('no auths!')
        authMap = {}
        for item in auths:
            if authMap.has_key(item['account_id']):
                self.info('account_id repeat!')
            else:
                authMap[item['account_id']] = item['access_token']
        return authMap

    def run(self):
        try:
            self.init()
            self.startPosition, status = self.getStartPosition()  # 获取上次更新的时间戳
            if status == 0:
                self.info('The script is stop!')
                exit()
            self.nextPosition = time.time()
            self.startTime = self.exchangeTimeStampDate(self.startPosition, '%Y-%m-%d %H:%M:%S')
            self.nextTime = self.exchangeTimeStampDate(self.nextPosition, '%Y-%m-%d %H:%M:%S')
            self.info("start time =" + str(self.startTime) + "\tnext time =" + str(self.nextTime))
            data = self.getData()
            if self.saveData(data) is False:
                self.error('saveData error!')
            self.info("end time =" + str(self.exchangeTimeStampDate(time.time(), '%Y-%m-%d %H:%M:%S')))
            self.updatePosition()# 更新位置
        except Exception, e:
            traceback.print_exc()
            self.error("run error:" + str(e))


if __name__ == '__main__':
    obj = toolMarket('tool_market')
    obj.run()
