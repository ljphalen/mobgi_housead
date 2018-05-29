#!/usr/bin/env python
# -*- coding:utf-8 -*-

import os
import sys
import commands
import time
import config.api as configApi
import config.db as configDb
import lib.mysql as db
import lib.mylog as mylog

from lib.base import Base

reload(sys)
sys.setdefaultencoding("ISO-8859-1")



class ReportApiRun(Base):
    def init(self):
        self.currentDir = sys.path[0]
        self.logPath=configApi.LOG_PATH
        self.log = mylog.mylog(os.path.join(self.currentDir, self.logPath, self.scriptName + '.log'))
        self.apiPre = "report_api_"
        self.confTable = configDb.API_CONFIG_TABLE
        self.dbData = db.mysql(configDb.MYSQL_MOBGI_DATA)

    def getAdsList(self):
        sql = """select name from %s where status=1 and time<%s and name like '%s'""" % (self.confTable, time.time(), "report_api_%")
        list, count = self.dbData.fetchall(sql)
        return list

    def run(self):
        try:
            list = self.getAdsList()
            if list is not None:
                for script in list:
                    cmd = "/usr/bin/python " + os.path.join(self.currentDir, script['name'] + ".py")
                    status, result = commands.getstatusoutput(cmd)
                    if status == 0:
                        self.info('run ok:' + script['name'])
                    else:
                        self.error('run error:' + script['name'] + "," + result)
        except Exception, e:
            self.error("error:" + str(e))


if __name__ == '__main__':
    obj = ReportApiRun('run')
    obj.run()
