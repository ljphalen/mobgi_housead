# coding=utf-8
from sklearn.datasets import load_iris
from sklearn.preprocessing import MinMaxScaler
from sklearn.preprocessing import StandardScaler
from sklearn.preprocessing import Normalizer
from sklearn.preprocessing import Binarizer
import pandas as pd
from sklearn.cross_validation import train_test_split
from sklearn.preprocessing import Imputer
from numpy import vstack, array, nan
from sklearn.linear_model import LinearRegression
import numpy as np
import matplotlib.pyplot as plt

#导入IRIS数据集
iris = load_iris()
#
# #特征矩阵
# print iris.data
#
# #目标向量
# print iris.target

#标准化，返回值为标准化后的数据,数据服从正态分布
#print StandardScaler().fit_transform(iris.data)
#区间缩放，返回值为缩放到[0, 1]区间的数据
#print MinMaxScaler().fit_transform(iris.data)


#
# #二值化，阈值设置为3，返回值为二值化后的数据
# Binarizer(threshold=3).fit_transform(iris.data)


data = pd.read_csv('./ditie2017.csv')
#归一化，返回值为归一化后的数据,数据缺失值补全
#print Normalizer().fit_transform(iris.data)
#Imputer().fit_transform(vstack((array(['impressions',"user_dau","new_user","total_init","ad_income"]),data)))
imputer = Imputer(missing_values='NaN', strategy='mean', axis=0)
data = data.replace(0,np.nan)
data = np.array(data,dtype=float)
data = imputer.fit_transform(data)


x = data[:,:-1]#训练集

y = data[:,:-1]#测试集合

# print x
# print y
# exit()

#X_train, X_test, y_train, y_test = train_test_split(X, y, random_state=1)

linreg = LinearRegression()

linreg.fit(x, y)

print linreg.coef_
#print linreg.intercept_
exit()
#模型拟合测试集
#y_pred = linreg.predict(X_test)



#交叉验证
X = data[['impressions',"user_dau","new_user","total_init"]]
y = data[['ad_income']]
from sklearn.model_selection import cross_val_predict
predicted = cross_val_predict(linreg, X, y, cv=20)


fig, ax = plt.subplots()
ax.scatter(y, predicted)
ax.plot([y.min(), y.max()], [y.min(), y.max()], 'k--', lw=4)
ax.set_xlabel('Measured')
ax.set_ylabel('Predicted')
plt.show()
exit()

#
# from sklearn.feature_selection import VarianceThreshold
#
# #方差选择法，返回值为特征选择后的数据
# #参数threshold为方差的阈值
# print VarianceThreshold(threshold=3).fit_transform(data)
# exit()

print data
exit()
