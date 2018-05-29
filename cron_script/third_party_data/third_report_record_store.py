#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
import codecs
import logging
import MySQLdb
from utils.mysql import MySQL
import third_party_setting
import ConfigParser
import redis
import commands
import urllib2

reload(sys)
sys.setdefaultencoding('utf-8')

"""将批量的redis实例的数据落地成sql文件导入"""

class ThirdReportRecordStore(object):
    def __init__(self):
        # 数据库参数
        self.host = third_party_setting.MYSQL_REQUST['host']
        self.user = third_party_setting.MYSQL_REQUST['user']
        self.passwd = third_party_setting.MYSQL_REQUST['passwd']
        self.port = third_party_setting.MYSQL_REQUST['port']
        self.db = third_party_setting.MYSQL_REQUST['db']
        self.table = third_party_setting.MYSQL_REQUST['table']
        # 文件存放路径
        self.sqlpath=os.path.join(os.getcwd(), third_party_setting.SQLFILEPATH)
        self.fieldlist = third_party_setting.FIELD_LIST

    # 不用logging日志是因为上个目录已经使用logging了，非继承关系被覆盖
    def mylog(self,msg):
        print time.strftime("%Y-%m-%d %H:%M:%S",time.localtime())+ " " + msg
        self.logfileHandle.write(time.strftime("%Y-%m-%d %H:%M:%S",time.localtime())+" " + msg + '\n')

    # 重连db
    def trysql(self,sql):
        try:
            self.mylog("select sql:"+sql)
            self.clickdb.queryNotCatch(sql)
        except MySQLdb.Error as m:
            self.mylog('masterdb reconnecting now')
            self.clickdb = MySQL(self.host, self.user, self.passwd, port=self.port, db=self.db)
            self.clickdb.query(sql)
        return self.clickdb.fetchAll()

    def saveDataToFile(self, streamlist):
        try:
            MIN=time.strftime("%Y%m%d%H%M",time.localtime())
            cf = ConfigParser.ConfigParser()
            confname='third_report_record.conf'
            cf.read(confname)
            curtable = 'record_id'
            reocrd_id = cf.getint("third_report_data", curtable)
            reocrd_id = int(reocrd_id)
            #按年_月_日_时_分创建文件,一分钟一个文件
            self.filename=self.sqlpath+"sql_content_"+MIN+".sql"
            file_object = codecs.open(self.filename, 'a','utf-8')
            for stream in streamlist:
                print stream
                sqlcontent=''
                valueStr=""
                # 增加id
                valueStr+=str(reocrd_id)+'\t'
                reocrd_id += 1
                for field_key in self.fieldlist:
                    if stream.has_key(field_key):
                        valueStr+=str(stream[field_key])+'\t'
                    else:
                        valueStr+=""+'\t'
                sqlcontent+=valueStr.strip('\t')+"\n"
                print sqlcontent
                if sqlcontent !="":
                    file_object.write(sqlcontent)
            file_object.close()
            cf.set("third_report_data", curtable, reocrd_id)
            cf.write(open(confname, "w"))
        except Exception, e:
            self.mylog("step1 save data to file failed! error:"+str(e))

    def insert(self):
        sqlfilepath=self.sqlpath
        file=os.listdir(sqlfilepath)
        # 循环读取文件夹中的sql文件进行入 agent_click表
        dbtable=self.table
        for f in file:
            #不导入当前分钟的数据
            if os.path.isfile(sqlfilepath+f) is False:#如果不是sql文件跳过
                continue
            if f=="errorSql.sql":#不导入错误日志文件
                continue
            try:
                loadSql="LOAD DATA LOCAL INFILE '"+sqlfilepath+f+"' INTO TABLE  "+dbtable+" character set utf8 FIELDS TERMINATED BY '\\t'  ENCLOSED BY '' escaped by '' LINES TERMINATED BY '\\n' STARTING BY '' "+third_party_setting.FIELDS
                delsql="&& rm -rf '"+sqlfilepath+f+"'"
                command = " mysql -u"+self.user+" -p"+self.passwd+" -h"+self.host+" -D"+self.db+" -P"+str(self.port)+" --local-infile=1 -e  \""+loadSql+"\"" +delsql+" 2>&1"#导入成功则删除sql文件
                print 'load inforbright:'+command
                output=commands.getstatusoutput(command)#导入成功则删除sql文件
                # loadSql="LOAD DATA  LOCAL INFILE \""+sqlfilepath+f+"\" INTO TABLE  "+dbtable+" character set utf8  "+third_party_setting.FIELD
                # #print loadSql
                # delsql="&& rm -rf '"+sqlfilepath+f+"'"
                # print " mysql -u"+self.user+" -p"+self.passwd+" -h"+self.host+" -P"+str(self.port)+" --local-infile=1 -e '"+loadSql
                # output=commands.getstatusoutput(" mysql -u"+self.user+" -p"+self.passwd+" -h"+self.host+" -P"+str(self.port)+" --local-infile=1 -e '"+loadSql+"'" +delsql+" 2>&1")#导入成功则删除sql文件
                if output[0]!=0:
                    #如果导入数据失败的则记录至异常sql文件中,以便以后恢复
                    self.mylog("step2 load failed! error:"+output[1]+"->loadsql:"+loadSql+"\n")
                    filesql_obj = codecs.open(sqlfilepath+"errorSql.sql", 'a','utf-8')
                    filesql_obj.write("error:"+output[1]+"->"+loadSql+"\r\n")
                    filesql_obj.close()
                else:
                    self.mylog("step3 load succeed! loadsql:"+loadSql+"\n")
            except Exception, e:
                self.mylog("step4 load data in to DETAILMYSQL error file is "+f+" error:"+str(e))
        return

    def run(self, streamlist):
        fileName = "third_report_record_store.log.txt"
        logfilename = os.path.join(os.getcwd(), third_party_setting.LOGPATH + fileName)
        self.logfileHandle = open(logfilename,'a')
        self.saveDataToFile(streamlist)
        self.insert()
        self.logfileHandle.close()
