#!/usr/bin/env python
# -*- coding:utf-8 -*-
import datetime
import time
import calendar

def dayat():
	'''当天过期时间点'''
	timesnap=datetime.date.today() + datetime.timedelta(days=1)
	timeatout=int(timesnap.strftime('%s')) - 1
	return timeatout
def weekat():
	'''本周过期时间点'''
	weekdays=8-int(datetime.date.today().strftime("%w"))#获取本周还剩下多少天
	timesnap=datetime.date.today() + datetime.timedelta(days=weekdays)
	timesnap=timesnap.strftime('%s')
	return int(timesnap)-1#减一秒 23:59:59秒

def monthat():
	'''当月过期时间点'''
	year=int(datetime.date.today().strftime("%Y"))
	month=int(datetime.date.today().strftime("%m"))
	monthdays=calendar.monthrange(year,month)
	monthdays=int(monthdays[1])+1-int(datetime.date.today().strftime("%m"))#获取本月还剩下多少天
	timesnap=datetime.date.today() + datetime.timedelta(days=monthdays)
	timesnap=timesnap.strftime('%s')
	return int(timesnap)-1#减一秒 23:59:59秒
def hourat():
	return (int(time.time())/3600+1)*3600
def expiresat(timeat):
	if timeat=="hour":
		return hourat()
	elif timeat=="dayat":
		return dayat()
	elif timeat=="week":
		return weekat()
	elif timeat=="month":
		return monthat()
	else:
		return dayat()
def debug(msg):
	date=time.strftime("[%Y-%m-%d %H:%M:%S]",time.localtime())
	print date+str(msg)
	
def md5(str):
    import hashlib
    m = hashlib.md5()   
    m.update(str)
    return m.hexdigest()

def mycrc32(szString):
    import binascii
    crc32=binascii.crc32(szString) & 0xFFFFFFFF
    return crc32

def getActiveTable(idfamd5):
    return "active_" + str(mycrc32(idfamd5)%100)
