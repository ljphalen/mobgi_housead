#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import numpy as np
from sklearn import tree
from sklearn import linear_model
from sklearn import svm
from sklearn import neighbors
from sklearn import ensemble
from sklearn.ensemble import BaggingRegressor
from sklearn.tree import ExtraTreeRegressor
import matplotlib.pyplot as plt

sys.path.append("..")
from lib.ml import Ml

# 机器学习基类
class MlBase(Ml):
    def exchangeStrDate(self, strTime, formFormat, toFormat):
        start_timeArray = time.strptime(strTime, formFormat)
        start_timestamp = int(time.mktime(start_timeArray))
        return time.strftime(toFormat, time.localtime(start_timestamp))

    def exchangeTimeStampDate(self, timeStamp, toFormat):
        time_local = time.localtime(timeStamp)
        return time.strftime(toFormat, time_local)

    def exchangeTimeStamp(self, date):
        timeArray = time.strptime(date, "%Y-%m-%d %H:%M:%S")
        return time.mktime(timeArray)


    def trainModelList(self):
        modelMap = {}
        ####3.1决策树回归####
        modelMap['model_DecisionTreeRegressor'] = tree.DecisionTreeRegressor()
        ####3.2线性回归####
        modelMap['model_LinearRegression'] = linear_model.LinearRegression()
        ####3.3SVM回归####
        modelMap['model_SVR']= svm.SVR()
        ####3.4KNN回归####
        modelMap['model_KNeighborsRegressor'] = neighbors.KNeighborsRegressor()
        ####3.5随机森林回归####
        modelMap['model_RandomForestRegressor'] = ensemble.RandomForestRegressor(n_estimators=20)  # 这里使用20个决策树
        ####3.6Adaboost回归####
        modelMap['model_AdaBoostRegressor'] = ensemble.AdaBoostRegressor(n_estimators=50)  # 这里使用50个决策树
        ####3.7GBRT回归####
        modelMap['model_GradientBoostingRegressor'] = ensemble.GradientBoostingRegressor(learning_rate=0.2,n_estimators=200)  # 这里使用100个决策树
        ####3.8Bagging回归####
        modelMap['model_BaggingRegressor'] = BaggingRegressor()
        ####3.9ExtraTree极端随机树回归####
        modelMap['model_ExtraTreeRegressor'] = ExtraTreeRegressor()
        return modelMap

    def try_different_method(self,x_train,y_train,x_test,y_test):
        modelMap = self.trainModelList()
        for model in modelMap:
            modelMap[model].fit(x_train, y_train.values.ravel())
            score = modelMap[model].score(x_test, y_test)
            result = modelMap[model].predict(x_test)
            plt.figure()
            plt.plot(np.arange(len(result)), y_test, 'go-', label='true value')
            plt.plot(np.arange(len(result)), result, 'ro-', label='predict value')
            plt.title('score: %f' % score)
            plt.legend()
            plt.show()
        exit()
            #result = model.predict(x_test)
        # plt.figure()
        # plt.plot(np.arange(len(result)), y_test, 'go-', label='true value')
        # plt.plot(np.arange(len(result)), result, 'ro-', label='predict value')
        # plt.title('score: %f' % score)
        # plt.legend()
        # plt.show()

