#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import logging
import pandas as pd
import numpy as np
from fbprophet import Prophet
import matplotlib as mpl
from matplotlib import pyplot as plt


# facebookprophet模块
class prophet(object):
    def __init__(self, holidays=None, config='default'):
        if holidays is not None:
            self.holiday = holidays
        else:
            self.holiday = None
        if config == 'default':
            self.config = {
                'daily_seasonality':True,
                'weekly_seasonality':True,
                'holidays' :self.holiday,
                'holidays_prior_scale' :0.05,
                'periods':1,
                'freq':'H'
            }
        elif config == 'days':
            self.config = {
                'daily_seasonality': True,
                'weekly_seasonality': True,
                'holidays': self.holiday,
                'holidays_prior_scale': 0.05,
                'periods': 1,
                'freq': 'D'
            }
        else:
            self.config = config

    def predict(self, data):
        try:
            #清洗参数,如果有0,那么就用前后中间值代替
            data = self.filterData(data)
            data['y'] = np.log(data['y'])
            prophet = Prophet(
                daily_seasonality=self.config['daily_seasonality'],
                weekly_seasonality=self.config['weekly_seasonality'],
                holidays=self.holiday,
                holidays_prior_scale=self.config['holidays_prior_scale'],
            )
            prophet.fit(data)
            future = prophet.make_future_dataframe(periods=self.config['periods'], freq=self.config['freq'])
            forecast = prophet.predict(future)
            return forecast[['ds', 'yhat', 'yhat_lower', 'yhat_upper']]
        except Exception, e:
            print e

    #将DB数据处理成为表格数据便于机器学习处理
    def exchangeFormat(self,data,needKey):
        formatData = {'ds':[],'y':[]}
        for item in data:
            ds = str(item['days'])+' '+str(item['hours'])+':00'
            formatData['ds'].append(str(ds))
            formatData['y'].append(np.int64(item[needKey]))
        return pd.DataFrame(formatData)


    #过滤和清洗数据
    def filterData(self,yData):
        yData['y'] = yData['y'].replace(0,1)
        return yData