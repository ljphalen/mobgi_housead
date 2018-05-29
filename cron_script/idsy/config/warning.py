#!/usr/bin/python
# -*- coding:utf-8 -*-

ENV = "DEV"  # 环境模式　DEV：开发环境模式　 PROD:生产环境模式
if ENV == "DEV":
    SMS_API = "http://sagent2.uu.cc/SMS/sa/sms/send"
    MAIL_SMTP = {
        "sender": "frankn72@126.com",
        "host": "smtp.126.com",
        "port": 25,
        "user": "frankn72@126.com",
        "pass": "123456"
    }
else:
    SMS_API = "http://sagent2.uu.cc/SMS/sa/sms/send"
    MAIL_SMTP = {
        "sender": "admonitor@idreamsky.com",
        "host": "smtp.idreamsky.com",
        "port": 25,
        "user": "admonitor",
        "pass": "#7XjFwSb6Rdx"
    }
