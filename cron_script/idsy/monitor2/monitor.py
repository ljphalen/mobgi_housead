#!/usr/bin/env python
# -*- coding:utf-8 -*-
import sys


sys.path.append("..")

from lib.monitor import Task

if __name__ == '__main__':
    task = Task("task")
    task.start()
