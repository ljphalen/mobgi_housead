#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
from monitor_base import MonitorBase
from lib.monitor import CheckTask
import config.db as configDb


#短信或者邮件发送脚本
class monitorsendmsg(MonitorBase):
    # 初始化条件
    def runInit(self):
        self.dbMonitor = self.initDb(configDb.MYSQL_MOBGI_MONITOR)  # 初始化Monitor库
        self.SendQueueTable = 'monitor_send_queue'  # 获取第三方数据的表
        self.MonitorTaskTable = 'monitor_task'

    # 获取所有需要发送的数据
    def sendInfos(self):
        sql = "select * from %s where is_send = 0"% (self.SendQueueTable)
        items, count = self.dbMonitor.fetchall(sql)
        ret = False
        if count !=0:
            for item in items:
                if self.checkMsg(item) is False:
                    return False
                if item['send_type'] == 'message':#短信
                    if self.sendSms(item['account'],item['content'])is not False:
                        ret = True
                elif item['send_type'] == 'email':#邮箱
                    if self.sendEmail(item['account'],item['title'],item['content'])is not False:
                        ret = True
                if ret is True:#修改发送状态
                    self.changeSendstatus(item)
        else:
            self.info('no sendTask!')
        return ret


    #检查是否可以发送
    def checkMsg(self,info):
        #如果发送时间小于当前时间就不发送，大于就发送
        if info['send_time'] > int(time.strftime('%H',time.localtime(time.time()))) and info['send_time'] != -1:
            self.info('not to send time!')
            return False
        else:
            return True


    #修改发送状态
    def changeSendstatus(self,info):
        sql = "update %s set is_send = 1 where id = %s"%(self.SendQueueTable,info['id'])
        return self.dbMonitor.execute(sql)




    def run(self):
        try:
            self.runInit()
            if self.sendInfos() is not False:
                return True
            else:
                self.info('send error!')
                return False
        except Exception, e:
            self.info("run error:" + str(e))


if __name__ == '__main__':
    startTimeStamp = time.time()
    obj = monitorsendmsg('monitor_sendmsg')
    obj.run()
