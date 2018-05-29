#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
from monitor_base import MonitorBase
import config.db as configDb
import json
from lib.task import CheckTask
# 人均次数监控检测脚本
# 该脚本用作预警
# 检测预警task任务
class MonitorCheck(MonitorBase):
    # 初始化条件
    def runInit(self):
        self.dbNewData = self.initDb(configDb.MYSQL_MOBGI_DATA)  # 初始化data库
        self.dbMonitor = self.initDb(configDb.MYSQL_MOBGI_MONITOR)  # 初始化Monitor库
        self.CapitalTable = 'ad_active'  # 人均数据表
        self.TaskTable= 'ad_task' #监控任务表
        self.TaskLogTable = 'ad_task_log'#监控报警表
        self.GlobalSetTable = 'ad_global_set'#报警全局设置表


    # 获取任务状态
    def checkCondition(self):
        sql = "select * from %s where id = '%s'" % (self.TaskTable,self.taskId)
        self.condition = self.dbMonitor.fetchone(sql)
        if self.condition is None:
            self.errorTask('TASK ID IS NOT EXISTS!',self.taskId)
            return False
        # 这个是跑数据的实际日期
        self.startPosition = self.exchangeTimeStamp(str(self.condition['next_time']))  # nextposition 为跑前一天的数据
        self.startDatePosition = self.exchangeTimeStampDate(self.startPosition, '%Y-%m-%d')
        self.startDateTimePosition = self.exchangeTimeStampDate(self.startPosition, '%Y-%m-%d %H:%M:%S')  # 用这个来取数据

        # 这个是能跑出来数据的实际日期，一般来说跑的数据是跑的时候的前一天的数据
        self.getPosition = int(self.startPosition) - 86400  # 这是获取数据的真实日期
        self.getDatePosition = self.exchangeTimeStampDate(self.getPosition, '%Y-%m-%d')  # 用这个来取数据
        self.getDateTimePosition = self.exchangeTimeStampDate(self.getPosition, '%Y-%m-%d %H:%M:%S')  # 用这个来取数据
        self.period = self.condition['period']

        self.info('start position data for Dyas is:' + str(self.startDateTimePosition))
        #self.info('get position data for Dyas is:' + str(self.getDateTimePosition))
        self.info('use Time start:' + time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time())))
        if self.condition['status'] == 2 :
            self.info('The script is runing!')
            return False
        if time.time()< self.startPosition:
            self.info('The script is not to StartTime!')
            return False
        return True

    #检查任务参数类型，进行处理
    def checkTask(self):
        self.params = json.loads(self.condition['params'])
        if self.params.has_key('monitor_type'):
            if self.params['monitor_type'] == 'capital':
                return self.analysisCapitalInfo()
            elif self.params['monitor_type'] == 'ecpm':
                return self.analysisEcpmInfo()
            elif self.params['monitor_type'] == 'act':
                return self.analysisActiveInfo()
            elif self.params['monitor_type'] == 'doublecheck':
                return self.analysisDoublecheckInfo()
            else:
                self.errorTask('no monitor_type!',self.taskId)
                return False
        return True

    #处理doubleCheck的数据, 热数据对比
    def analysisDoublecheckInfo(self):
        script_name = ('report_api_'+self.params['ads_id']+'.py').lower()
        api_path  = os.path.abspath(os.path.join(os.path.dirname(__file__),".."))+'/api/'
        cmd = "/usr/bin/python " + os.path.join(api_path, script_name) + " " + str(self.params['time_length'])
        self.info('apiPath:'+cmd)
        os.system(cmd)
        return True


    #处理capital数据,人均的数据
    def analysisCapitalInfo(self):
        sql = "select * from ad_capital where days = '%s' and app_key = '%s'"%(self.getDatePosition, self.params['app_key'])
        result = self.dbMonitor.fetchone(sql)
        if result is not None:
            #评价报警等级
            if float(result['plancapital']) != 0 and float(self.params['limit_val']) !=0:
                i = abs(((float(result['capital'])-float(result['plancapital']))/float(result['plancapital'])) / 100-float(self.params['limit_val']))
            else:
                self.info('/ IS NOT 0'+str(self.taskId))
                return False
            if i > self.globalSet['warning_max']:
                warning_level =0
            elif i < self.globalSet['warning_max'] and i >(0.5*(self.globalSet['warning_max']-self.globalSet['warning_min'])+self.globalSet['warning_min']):
                warning_level =1
            elif i >self.globalSet['warning_min'] and i < (0.5*(self.globalSet['warning_max']-self.globalSet['warning_min'])+self.globalSet['warning_min']):
                warning_level =2
            else:
                warning_level =3
            paramsTemp = {
                'app_key': self.params['app_key'],
                'now_data': float(result['capital']),
                'plan_data': float(result['plancapital']),
                'monitor_type': self.params['monitor_type'],
                'report_time':self.params['report_time']
            }
            temp = json.dumps(paramsTemp)
            data = {
                'task_id': self.taskId,
                'create_time': self.getDatePosition,
                'remark': '',
                'warming': warning_level,
                'is_deal': 1,  # 不报警默认为已经处理
                'info': temp,
            }
            sql = """insert into %s (task_id,days,remark,is_deal,warming_level,info) values(%s,'%s','%s',%s,%s,'%s')on duplicate key update info='%s'""" % (self.TaskLogTable, data['task_id'], data['create_time'], data['remark'], data['is_deal'],data['warming'], data['info'], data['info'])
            return self.dbMonitor.execute(sql)
        else:
            self.info('NO PLANDATA taskId ='+str(self.taskId))
            return False


    #处理ecpm数据,ecpm的数据
    def analysisEcpmInfo(self):
        sql = "select * from ad_ecpm where days = '%s' and app_key = '%s' and ad_type = '%s'"%(self.getDatePosition,self.params['app_key'],self.params['ad_type'])
        result = self.dbMonitor.fetchone(sql)
        if result is not None:
            paramsTemp = {
                'app_key': self.params['app_key'],
                'ad_type': self.params['ad_type'],
                'now_data': float(result['ecpm']),
                'plan_data': self.params['ecpm_min_val'],
                'monitor_type':self.params['monitor_type'],
                'report_time': self.params['report_time']
            }
            temp = json.dumps(paramsTemp)
            if result['ecpm'] >= self.params['ecpm_min_val']:
                #不报警#写入log
                data = {
                    'task_id':self.taskId,
                    'create_time':self.getDatePosition,
                    'remark':'',
                    'warming':3,
                    'is_deal':1,#不报警默认为已经处理
                    'info':temp,
                }
            else:
                data = {
                    'task_id': self.taskId,
                    'create_time': self.getDatePosition,
                    'remark': '',
                    'warming': 0,
                    'is_deal':0,
                    'info': temp,
                }
            sql = """insert into %s (task_id,days,remark,is_deal,warming_level,info) values(%s,'%s','%s',%s,%s,'%s')on duplicate key update info='%s'"""%(self.TaskLogTable,data['task_id'],data['create_time'],data['remark'],data['warming'],data['is_deal'],data['info'],data['info'])
            return self.dbMonitor.execute(sql)
        else:
            self.info('NO PLANDATA! taskId ='+str(self.taskId))
            return False

    # 处理active数据,活跃的数据
    def analysisActiveInfo(self):
        sql = "select * from ad_active where days = '%s' and app_key = '%s'" % (self.getDatePosition, self.params['app_key'])
        result = self.dbMonitor.fetchone(sql)
        if result is not None:
            # 评价报警等级
            if float(result['plandau']) != 0 and float(self.params['limit_val']) != 0:
               i = abs( ((float(result['dau'])-float(result['plandau']))/float(result['plandau'])) /  (100-float(self.params['limit_val'])) )
            else:
                self.errorTask('/ IS NOT 0 taskId =',self.taskId)
                return False
            if i > self.globalSet['warning_max']:
                warning_level = 0
            elif i < self.globalSet['warning_max'] and i > (0.5 * (self.globalSet['warning_max'] - self.globalSet['warning_min']) + self.globalSet['warning_min']):
                warning_level = 1
            elif i > self.globalSet['warning_min'] and i < (0.5 * (self.globalSet['warning_max'] - self.globalSet['warning_min']) + self.globalSet['warning_min']):
                warning_level = 2
            else:
                warning_level = 3
            paramsTemp = {
                'app_key': self.params['app_key'],
                'now_data': float(result['dau']),
                'plan_data': float(result['plandau']),
                'monitor_type': self.params['monitor_type'],
                'report_time': self.params['report_time']
            }
            temp = json.dumps(paramsTemp)
            if warning_level ==3:
                data = {
                    'task_id': self.taskId,
                    'create_time': self.getDatePosition,
                    'remark': '',
                    'warming': warning_level,
                    'is_deal': 1,  # 不报警默认为已经处理
                    'info': temp,
                }
            else:
                data = {
                    'task_id': self.taskId,
                    'create_time': self.getDatePosition,
                    'remark': '',
                    'warming': warning_level,
                    'is_deal': 0,  # 不报警默认为已经处理
                    'info': temp,
                }
            sql = """insert into %s (task_id,days,remark,is_deal,warming_level,info) values(%s,'%s','%s',%s,%s,'%s')on duplicate key update info='%s'""" % (self.TaskLogTable, data['task_id'], data['create_time'], data['remark'],data['is_deal'],data['warming'],data['info'], data['info'])

            return self.dbMonitor.execute(sql)
        else:
            self.info('NO PLANDATA! taskId ='+str(self.taskId))
            return False

    #获得全局设定
    def getGlobalset(self):
        sql = 'select * from %s where 1=1'%(self.GlobalSetTable)
        return self.dbMonitor.fetchone(sql)


    def run(self,taskId):#传回一个任务ID
        try:
            self.runInit()
            self.taskId = taskId
            if self.checkCondition() is not True:
                return False
            self.globalSet = self.getGlobalset()
            if self.checkTask() is not False:
                self.endTask(self.taskId)  # 更新位置
            else:
                self.info('Save data error!')
                return False
        except Exception, e:
            self.errorTask("run error:" + str(e),self.taskId)


if __name__ == '__main__':
    taskId = CheckTask(sys.argv)
    while 1:
        obj = MonitorCheck('monitor_check')
        if obj.run(taskId) is False:
            break
