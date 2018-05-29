#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import ConfigParser


class myini:
    path = None

    def __init__(self, path):
        self.path = path
        self.cf = ConfigParser.ConfigParser()
        self.cf.read(self.path)

    def has(self, field, key):
        try:
            result = self.cf.has_option(field, key)
        except Exception, e:
            return False
        return result

    def get(self, field, key):
        try:
            result = self.cf.get(field, key)
        except Exception, e:
            return False
        return result

    def set(self, field, key, value):
        try:
            if self.cf.has_section(field) is False:
                self.cf.add_section(field)
            self.cf.set(field, key, str(value))
            self.cf.write(open(self.path, 'w'))
        except Exception, e:
            return False
        return True
