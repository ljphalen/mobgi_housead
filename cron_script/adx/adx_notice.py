#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import time
import config.adx as configAdx
import config.db as configDb
import config.redis as configRedis
import sys
import codecs
import commands
import fnmatch
import threading
import urllib2
from adx_base import AdxBase

default_encoding = 'utf-8'
if sys.getdefaultencoding() != default_encoding:
    reload(sys)
    sys.setdefaultencoding(default_encoding)

RQ_ADX_NOTICE = configAdx.RQ_ADX_NOTICE
RQ_ADX_LIMIT = configAdx.RQ_ADX_SERVER_LIMIT

class AdxNotice(AdxBase):
    rlen = 0
    threads = 1

    def init(self):
        self.rq = self.initRedis(configRedis.REDIS_QUEUE_ADX)

    def getRlen(self):
        try:
            if self.rq.ping():
                self.rlen = self.rq.llen(RQ_ADX_NOTICE)
                if self.rlen > 1000000:
                    self.errlog('houseAd rlen>1000000')
            else:
                self.rlen = 0
                self.errlog('redis queue has gone away')
            self.threads = self.getThreads(self.rlen)
        except Exception, e:
            self.rlen = 0
            self.errlog("redis error:" + str(e))
        finally:
            return self.rlen

    def getThreads(self, count):
        num = str(int(count / 1000) + 1)
        return len(num)

    def run(self):
        start_time = time.time()
        self.info("rlen:" + str(self.rlen))
        tasks = []
        if self.rlen > 0:
            for i in xrange(0, self.threads):
                t = threading.Thread(target=self.notice, args=(i,))
                tasks.append(t)
                t.start()
            for t in tasks:
                t.join()
        self.info("loop_time:" + str(time.time() - start_time))

    def notice(self, threadnum):
        try:
            start_time = time.time()
            for i in range(RQ_ADX_LIMIT):
                stream = self.rq.lpop(RQ_ADX_NOTICE)
                if stream is None:
                    break
                stream = eval(stream)
                if isinstance(stream, dict) is False:
                    break
                outBidId = str(stream['outBidId'])
                nurl = str(stream['nurl'])
                for i in xrange(3):#给你三次机会,过犹不及
                    if self.http(outBidId, nurl, threadnum) is True:
                        break
            self.info(str(threadnum) + '_thread_time=' + str(time.time() - start_time))
        except Exception, e:
            self.error(str(e))

    def http(self, outBidId, nurl, threadnum):
        # 替换空格为 %20，不替换会出现400 BAD_REQUEST 问题, 并把http:\/\/baidu.com替换成http://baidu.com
        noticeurl = nurl.replace(" ","%20").replace("\\", "")

        """修改这里"""
        if noticeurl=="":
            self.info('threadnum' + str(threadnum) + '_noticeurl is empty! return. ')
            return True
        if noticeurl=="unknow":
            self.info('threadnum' + str(threadnum) + '_noticeurl is empty! return. ')
            return True
        try:
            s_time = time.time()
            self.info('threadnum' + str(threadnum) + ' s_time='+str(s_time) + " outBidId="+outBidId+" noticeurl="+noticeurl)

            #使用urllib2,可以兼容tapjoy返回的是xml格式的数据
            response = urllib2.urlopen(noticeurl, timeout=5)
            status_code = response.getcode()
            headers=response.headers
            content=response.read()

            e_time = time.time()
            use_time = e_time - s_time
            if status_code!=200:#如果服务挂了
                self.info('threadnum' + str(threadnum) + 'e_time='+str(e_time)+' use_time='+str(use_time) + " response error! " + " outBidId="+outBidId+" noticeurl="+noticeurl + " :response info--> status="+str(status_code)+" headers="+str(headers)+" content="+content+"\n")
                return True
            self.info('threadnum' + str(threadnum) + ' e_time='+str(e_time)+' use_time='+str(use_time) + " response success! " + " outBidId="+outBidId+" noticeurl="+noticeurl + " :response info--> status="+str(status_code)+" headers="+str(headers)+" content="+content)
            if status_code==200:
                try:
                    return self.responseCallback()
                except Exception,e:
                    self.mylog('step12 exception:' + str(e)+"\n")
                    return False
            return False
        except Exception, e:
            e_time = time.time()
            use_time = e_time - s_time
            self.info('threadnum' + str(threadnum) + ' e_time='+str(e_time)+' use_time='+str(use_time) + " response exception! "+ " outBidId="+outBidId+" noticeurl="+noticeurl + " exception info: "+ str(e)+"\n")
            return False
        return True

    def responseCallback(self):#处理各个接口的错误返回
        #to do 发送通知成功的消息到redis队列.
        # mober=self.clickinfo['mober']
        # tid = str(self.clickinfo["id"])
        # #百度贴吧需要额外更新回调地址
        # if mober=='tieba' or mober=='toyblast_share':
        #     sql="update "+ self.curtable + " set iscallback=1 , callbacktime = %d"%(time.time())+", callback='"+self.clickinfo["callback"]+"' where id='"+tid+"'"#如果请求成功的话这将回调置为true
        #     # updateinfobrightsql = "update active set iscallback=1 , callbacktime = %d"%(time.time())+", callback='"+self.clickinfo["callback"]+"' where click_id='"+str(self.clickinfo["click_id"])+"'"+";\n"#生成通知更新infobright的SQL文件
        #     updateinfobrightsql = "update active set iscallback=1 , callbacktime = %d"%(time.time())+" where click_id='"+str(self.clickinfo["click_id"])+"'"+";\n"#生成通知更新infobright的SQL文件
        # else:
        #     sql="update "+ self.curtable +" set iscallback=1 , callbacktime = %d"%(time.time())+" where id='"+tid+"'"#如果请求成功的话这将回调置为true
        #     updateinfobrightsql = "update active set iscallback=1 , callbacktime = %d"%(time.time())+" where click_id='"+str(self.clickinfo["click_id"])+"'"+";\n"#生成通知更新infobright的SQL文件
        # self.mylog("step14 sql:" + sql)
        # self.db.query(sql)
        # self.db.commit()
        # self.sqlpath=settings.ACTIVEUPDATESQLFILEPATH+"/"
        # MIN=time.strftime("%Y%m%d%H%M",time.localtime())
        # #按年月日时分创建文件,一分钟一个文件
        # self.filename=self.sqlpath+""+MIN+"_"+self.mober+".sql"
        # file_object = codecs.open(self.filename, 'a','utf-8')
        # file_object.write(updateinfobrightsql)
        # file_object.close()
        # self.mylog("step15 update table --> activetable="+ self.curtable +" id="+str(self.clickinfo["id"])+"  iscallback"+"\n")
        # #print "loop finish!"
        return True


if __name__ == '__main__':
    sleepCount = 0
    while 1:
        adxNotice = AdxNotice('adx_server')
        rlen = adxNotice.getRlen()
        if rlen < RQ_ADX_LIMIT and sleepCount < 3:
            time.sleep(configAdx.SLEEP_SECOND)
            sleepCount = sleepCount + 1
            continue
        sleepCount = 0
        adxNotice.run()

