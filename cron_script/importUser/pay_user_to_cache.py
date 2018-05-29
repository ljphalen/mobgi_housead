#!/usr/bin/env python
#_*_ coding:utf-8 _*_
import sys
reload(sys)
sys.setdefaultencoding('utf-8')

import os
import time
import datetime
import logging
import settings
from base import Base


#此脚本用来把付费用户导入到缓存中去

class PayUserToCache(Base):
    
    def main(self):
        print 'main'
  

if '__main__' == __name__:
        eventTypeObject = PayUserToCache("payUser")
        eventTypeObject.run()