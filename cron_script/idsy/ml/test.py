# coding=utf-8
import matplotlib.pyplot as plt
import numpy as np
import pandas as pd
from sklearn import datasets, linear_model
from sklearn.cross_validation import train_test_split
from sklearn.linear_model import LinearRegression
from sklearn import metrics
import seaborn as sns

data = pd.read_csv('./ads.csv')

#data.head()#前五行

#data.shape#样本维度


X = data[['impressions',"user_dau","new_user","total_init"]]#训练集

y = data[['ad_income']]#测试集合
#y.head()

X_train, X_test, y_train, y_test = train_test_split(X, y, random_state=1)

# print X_train.shape
# print y_train.shape
# print X_test.shape
# print y_test.shape

#PE = k0+k1*AT+k2*V+*RH

linreg = LinearRegression()
linreg.fit(X_train, y_train)


#模型拟合测试集
y_pred = linreg.predict(X_test)


#我们需要评估我们的模型的好坏程度，对于线性回归来说，我们一般用均方差（Mean Squared Error, MSE）
# 或者均方根差(Root Mean Squared Error, RMSE)在测试集上的表现来评价模型的好坏。
# 用scikit-learn计算MSE
# print "MSE:",metrics.mean_squared_error(y_test, y_pred)
# 用scikit-learn计算RMSE
# print "RMSE:",np.sqrt(metrics.mean_squared_error(y_test, y_pred))



#交叉验证
X = data[['impressions',"user_dau","new_user","total_init"]]
y = data[['ad_income']]
from sklearn.model_selection import cross_val_predict
predicted = cross_val_predict(linreg, X, y, cv=20)
# 用scikit-learn计算MSE
# print "MSE:",metrics.mean_squared_error(y, predicted)
# 用scikit-learn计算RMSE
# print "RMSE:",np.sqrt(metrics.mean_squared_error(y, predicted))


fig, ax = plt.subplots()
ax.scatter(y, predicted)
ax.plot([y.min(), y.max()], [y.min(), y.max()], 'k--', lw=4)
ax.set_xlabel('Measured')
ax.set_ylabel('Predicted')
plt.show()
exit()