#!/usr/bin/env python
# -*- coding:utf-8 -*-
#from multiprocessing import Process, Lock  
import threading
def run(lock, num):  
    lock.acquire()
    print "Hello Num: %s" % (num)  
    lock.release()
if __name__ == '__main__':  
    #lock = Lock()  #这个一定要定义为全局
    lock = threading.Lock()
    tasks = []
    for num in range(100):  
        t= threading.Thread(target=run, args=(lock, num))
        tasks.append(t)
        t.start()
    for task in tasks:
       t.join()  
    print '主任务结束'
    #tasks = []
    #for num in range(20):  
         #t= threading.Thread(target=run, args=(lock, num))
         #tasks.append(t)
         #t.start()
         #for task in tasks:
            #t.join()  
    #lock = Lock()  #这个一定要定义为全局
    #for num in range(20):  
        #pro = Process(target=run, args=(lock, num))  #这个类似多线程中的threading，但是进程太多了，控制不了。
        #pro.start()
    
    #print '主进程结束'   
        
        
        
        
   