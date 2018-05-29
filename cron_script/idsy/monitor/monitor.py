#!/usr/bin/env python
# -*- coding:utf-8 -*-
import sys
import commands

sys.path.append("..")

from lib.task import Task

if __name__ == '__main__':
    # wc = commands.getoutput('ps aux|grep "%s$"|wc -l' % (sys.argv[0]))
    # if int(wc) > 1:
    #     print "It's running..\n"
    #     exit()

    task = Task("task")
    task.start()
