#!/usr/bin/env python
#_*_ coding:utf-8 _*_

import os
import time
import datetime
import logging
import settings
import  redis
import csv
import smtplib
from email.mime.text import MIMEText
import urllib2
from itertools import islice  
import hashlib
import json

# 把数据中心的用户读取到缓存中
class Base:
	ONE_MONTH = 2592000
	redis0 = None
	redis1 = None
	redis2 = None

	def __init__(self, scriptName):
		self.scriptName = scriptName
		self.smtpConf = settings.EMAIL_INFO
	
	def sendMail(self, content, subject):
		# Mail Head Info
		#msg = MIMEText(content,'html','utf-8')   #发送html邮件
		smtpConf = self.smtpConf
		toAddr =smtpConf.get('toAddr')
		msg = MIMEText(content,'plain','utf-8') #发送文本邮件
		msg['Subject'] = subject
		msg['From'] = smtpConf.get('FromAddr')
		msg['To'] = ";".join(toAddr) 
	
		# Send Mail
		try:
			smtp = smtplib.SMTP()
			smtp.connect(smtpConf.get('host'), smtpConf.get('port'))          # 连接服务器
			smtp.login(smtpConf.get('user'),  smtpConf.get('pass'))            # 登录服务器
			smtp.sendmail(smtpConf.get('FromAddr'), toAddr, msg.as_string())  # 发送邮件
		except Exception, e:
			self.writeLog('邮件发送失败'+str(e));
		finally:
			smtp.close()
				
	
	def initRedis(self):
		try:
			if settings.REDIS0.has_key('password') is True:
				poolr0=redis.ConnectionPool(host=settings.REDIS0["host"], port=settings.REDIS0["port"],password=settings.REDIS0["password"])
			else:
				poolr0=redis.ConnectionPool(host=settings.REDIS0["host"], port=settings.REDIS0["port"])
			self.redis0 =redis.Redis(connection_pool=poolr0)
			if settings.REDIS1.has_key('password') is True:
				poolr1=redis.ConnectionPool(host=settings.REDIS1["host"], port=settings.REDIS1["port"],password=settings.REDIS1["password"])
			else:
				poolr1=redis.ConnectionPool(host=settings.REDIS1["host"], port=settings.REDIS1["port"])
			self.redis1=redis.Redis(connection_pool=poolr1)
			if settings.REDIS2.has_key('password') is True:
				poolr2=redis.ConnectionPool(host=settings.REDIS2["host"], port=settings.REDIS2["port"],password=settings.REDIS2["password"])
			else:
				poolr2=redis.ConnectionPool(host=settings.REDIS2["host"], port=settings.REDIS2["port"])
			self.redis2=redis.Redis(connection_pool=poolr2)
		except Exception, e:
			self.sendMail('下载数据中心的用户，链接redis失败', '下载文件失败')
			self.writeLog(":redis has gone away")
		
	def initLog(self, fileName):
	     logging.basicConfig(filename=os.path.join(os.getcwd(), settings.LOGPATH + fileName+'.log'),
							 format='%(asctime)s %(filename)s[line:%(lineno)d] %(levelname)s %(message)s',
							 datefmt='%Y-%m-%d %H:%M:%S', level=logging.INFO)
	
	def writeLog(self, msg):
		 print time.strftime("%Y-%m-%d %H:%M:%S",time.localtime())+ " " + str(msg)
		 logging.info(msg)
	
	def  getCsvFileData(self):	 
		fileName = self.getCsvFileName()
		self.writeLog('start read file'+fileName)	
		if os.path.isfile(fileName) is False:
			self.writeLog('文件不存在'+fileName)
		 	exit()
		data = []
		try :
			#读取文件
			file = open(fileName)  
			#忽略第一行
			for line in islice(file, 1, None):  
		 		content = line.strip()
		 		list = content.split(',')
	 			if list is None:
					continue
				data.append(list)
			file.close()
			self.writeLog('read file '+fileName+'finished')	
		except Exception, e:
				self.writeLog("read file error"+str(e))
		return data		
				
	def saveDataToCache(self, data):
		if len(data) < 1:
				self.writeLog('读取文件的长度小于1')
				return False
		try:	
			self.initRedis()
			for line in data:
				if line[0].strip() is None:
					continue
				cacheKey = line[0].strip()+'_'+line[1].strip()
				hashCacheKey = int(hashlib.md5(str(cacheKey)).hexdigest()[0:2], 16)%3
				#print 'hashCacheKey='+str(hashCacheKey)
				redisConn = self.getRedisObject(hashCacheKey)
				#保存付费用户
				if str(line[2]) == '1':
					#print line[2]
					payUserKey = cacheKey+'_payUser'
					redisConn.set(payUserKey, line[1].strip())
					redisConn.expire(payUserKey, self.ONE_MONTH)
				else:
					#保持活跃用户
					activeUserKey = cacheKey+'_activeUser'
					redisConn.set(activeUserKey, line[1].strip())
					redisConn.expire(activeUserKey, self.ONE_MONTH)
			self.writeLog("保持到redis 成功")	
		except Exception, e:
				self.writeLog("save to redis error"+str(e))	
	
	
	def getCsvFileName(self):
			now = time.strftime("%Y-%m-%d",time.localtime())
			fileName = os.getcwd()+settings.DATAPATH+ self.scriptName+ now+'.csv'
			return fileName
		
	def delCsvFile(self):
		filename = self.getCsvFileName()
		result = os.system("rm -rf '"+filename+"'")
		self.writeLog("del csv file success")
		
	
	def getTaskUrl(self):
		now = time.strftime("%Y-%m-%d",time.localtime())
		self.initRedis()
		key = 'task'+now
		try:
			value = self.redis0.get(key)
			url = None
			if value is not  None:
				url = json.loads(value)
			self.writeLog("get key="+str(key)+',taskurl = '+url)	
		except Exception, e:
			self.writeLog("解析保存的ｕｒｌ error"+str(e))
		return url
	
	def downCsvFile(self):
		try:
			loginUrl = settings.DATA_CENTER_INFO['loginUrt']+'?username='+settings.DATA_CENTER_INFO['username']+'&password='+settings.DATA_CENTER_INFO['password']
			self.writeLog("loginurl = "+loginUrl)
			cookies = urllib2.HTTPCookieProcessor()
			opener = urllib2.build_opener(cookies)
			loginResponseResult = opener.open(loginUrl)
			statusCode = loginResponseResult.getcode()
			self.writeLog("login statusCode="+str(statusCode))
			if statusCode != 200:
				self.sendMail('下载数据中心的用户，登录失败', '导入付费用户')
				self.writeLog("login fail")
				exit()
			
			taskUrl = self.getTaskUrl()
			#taskUrl = 'http://edata.idreamsky.com/user/task/csv?taskId=12231'
			if taskUrl is None:
				self.writeLog("任务id已经过期")
				exit()
			self.writeLog("下载任务的url = "+ taskUrl)
			request = urllib2.Request(taskUrl)
			responseResult = opener.open(request)
			downloadCode = responseResult.getcode()
			self.writeLog("下载任务的 downloadCode="+str(downloadCode))
			if downloadCode != 200:
				self.writeLog("下载失败")
				self.sendMail('下载文件失败', '下载文件失败')
				exit()
			#meta = responseResult.info() 
			
			now = time.strftime("%Y-%m-%d",time.localtime())
			fileName = os.getcwd()+settings.DATAPATH+ self.scriptName+ now+'.csv'
			self.writeLog("保存文件的路径：fileName="+fileName)
			data = responseResult.read()  
			with open(fileName, "wb") as code:
				code.write(data)
			self.writeLog("下载数据完成")
		except Exception, e:
			self.sendMail('数据中心的付费用户下载', '下载文件失败')
			self.writeLog("处理下载文件异常 "+str(e))
			
	def getRedisObject(self, type):
		key = 'redis'+str(type)
		mapList  = {'redis0' :self.redis0, 'redis1': self.redis1 ,'redis2': self.redis2}
		return mapList[key]
		 
	def run(self):
		self.initLog(self.scriptName)
		self.downCsvFile()
		data = self.getCsvFileData()
		self.saveDataToCache(data)
		self.delCsvFile()
	
		
		
		

	
	


