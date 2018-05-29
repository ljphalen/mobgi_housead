#!/usr/bin/env python
# -*- coding:utf-8 -*-


ENV="DEV" #环境模式　DEV：开发环境模式　 PROD:生产环境模式
if ENV=="DEV":
    REDIS0={"host":"192.168.141.216","port":6378,"password":"123456"}
    REDIS1={"host":"192.168.141.216","port":6378,"password":"123456"}
    REDIS2={"host":"192.168.141.216","port":6378,"password":"123456"}
    #邮箱设置
    EMAIL_INFO = {"host":"smtp.idreamsky.com", 
                              "port": 25,
                            "user":"admonitor",
                             "pass":"#7XjFwSb6Rdx",
                            "FromAddr":"admonitor@idreamsky.com",
                            "toAddr":['rock.luo@idreamsky.com']
                            }
else:
    REDIS0={"host":"redis.ad.user1.ildyx.com","port":6379,"password":"ZxEXuArl0Viw"}
    REDIS1={"host":"redis.ad.user2.ildyx.com","port":6379,"password":"ZxEXuArl0Viw"}
    REDIS2={"host":"redis.ad.user3.ildyx.com","port":6379,"password":"ZxEXuArl0Viw"}
    #邮箱设置
    EMAIL_INFO = {"host":"smtp.idreamsky.com", 
                              "port": 25,
                            "user":"admonitor",
                             "pass":"#7XjFwSb6Rdx",
                            "FromAddr":"admonitor@idreamsky.com",
                            "toAddr":['rock.luo@idreamsky.com']
                            }

LOGPATH  ="./log/"
DATAPATH = '/data/'

#数据中心需要交互信息
DATA_CENTER_INFO = {"username":"soso.zhang","password" :"poppopp" ,"loginUrt":"http://edata.idreamsky.com/simpleLogin"}









