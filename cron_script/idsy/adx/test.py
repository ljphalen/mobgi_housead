#!/usr/bin/env python
# -*- coding:utf-8 -*-
import threading  
import time
import os
   
def booth(tid):
    global i
    global lock
    while i > 0:
        lock.acquire()    
        if i != 0:
            i = i - 1 
            print '窗口：'+str(tid)+',余票：'+str(i)
            #time.sleep(1)                
        else:
            print("Thread_id",tid," No more tickets")
            exit()
        lock.release()               
        time.sleep(1) 
if __name__ == '__main__':      
    lock = threading.Lock()
    i= 100000
    tasks = []                         
    for k in range(1000):
        t = threading.Thread(target=booth,args=(k,))
        tasks.append(t)   
        t.start() 
    for task in tasks:
        t.join()      
    print('dddddddddddddd')