#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
from ml_base import MlBase
import config.db as configDb
from lib.task import CheckTask
import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split

#机器学习模型脚本(Test)
class MlManage(MlBase):

    #初始化条件
    def runInit(self):
        self.dbNewData = self.initDb(configDb.MYSQL_MOBGI_DATA)  # 初始化data库
        self.DauTable = 'report_dau'#dau表
        self.DayTable = 'report_day'#day表
        self.ThirdDataTable = 'report_third_data'#三方数据表
        self.MlConfigTable = 'config_ml'

    def getMlConfig(self,id):
        sqlConfig = "select * from %s where id = %s"%(self.MlConfigTable,id)
        self.mlConfig = self.dbNewData.fetchone(sqlConfig)

    def getData(self):
        #以人均为例子
        data = {}
        sqlDau = "select days,sum(user_dau) as dau from report_dau where app_key = '8E69498B356D95CCB579' and days<='2017-11-01' and days>='2017-08-01' GROUP by days"
        dauData,countDau = self.dbNewData.fetchall(sqlDau)
        data['dau'] = dauData
        sqlViews ="select days,sum(impressions) as impressions from report_day where app_key = '8E69498B356D95CCB579' and days<='2017-11-01' and days>='2017-08-01' GROUP by days"
        viewData,countView = self.dbNewData.fetchall(sqlViews)
        data['impressions'] = viewData
        sqlIncome = "select days,sum(ad_income) as ad_income from report_third_data where app_key = '8E69498B356D95CCB579' and days<='2017-11-01' and days>='2017-08-01' GROUP by days"
        incomeData,countIncome = self.dbNewData.fetchall(sqlIncome)
        data['ad_income'] =incomeData
        return data

    #pd.DataFrame({'name':['Time','Jack','Lily'],'Age':[20,30,12],"weight":[56.7,64.0,50.0]})
    def makeDataFrame(self,data):
        dataFrame = {}
        for item in data:
            tmp = []
            for i,items in enumerate(data[item]):
                tmp.append(items[item])
            dataFrame[item] = tmp
        return dataFrame

    #整理数据并进行数据的训练集和测试集合分类
    def getBestModel(self,data):
        data = pd.DataFrame(data)
        #数据补充和清理,中间如果有空值那么就用均值代替
        data = data.replace(0, np.nan)#先将0填充为Nan
        for column in data.columns:
            data[column].fillna(data[column].mean())
        #区分测试集和验证集,并给出比例
        x = data[['dau','impressions']]
        y = data[['ad_income']]
        x_train, x_test, y_train, y_test = train_test_split(x, y,test_size=0.2,random_state=0)
        #进入各种模型进行数据训练
        # print x_train
        # print y_train
        # print x_test
        # print y_test
        # exit()
        self.try_different_method(x_train,y_train,x_test,y_test)


        #fileName = 'ml'+ str(self.mlConfig['id'])+str(int(time.time()))+'.csv'
        #data.to_csv('./log/'+fileName)#存入csv备份
        #X_train, X_test, y_train, y_test = train_test_split(X, y, random_state=1)


    def run(self,taskId):
        try:
            self.runInit()
            self.getMlConfig(taskId)
            data = self.getData()
            frameData = self.makeDataFrame(data)
            self.getBestModel(frameData)
            # self.taskId = taskId
            # if self.checkCondition() is not True:
            #     return False
            # self.dauMap = self.getDauList()
            # calResult = self.calDau()
            # if self.saveData(calResult) is not False:
            #     self.endTask(self.taskId)#更新位置
            # else:
            #     self.info('Save data error! or NO DATA!')
            #     return False
        except Exception, e:
            self.info("run error:" + str(e))


if __name__ == '__main__':
    taskId = 1
    obj = MlManage('ml_manage')
    obj.run(taskId)
    # startTimeStamp = time.time()
    # taskId = CheckTask(sys.argv)
    # taskId = 2
    # while 1:
    #     obj = MlManage(taskId)
    #     if obj.run(taskId) == False:
    #         break
    #     time.sleep(1)
    #     # 脚步执行时间超过30分钟直接跳出
    #     if int(time.time() - startTimeStamp) > 1800:
    #         break





