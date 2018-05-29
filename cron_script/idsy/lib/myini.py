#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import ConfigParser


class myini:
    path = None

    def __init__(self, file):
        self.file = file
        self.cf = ConfigParser.ConfigParser()
        self.cf.read(self.file)

    def has(self, field, key):
        return self.cf.has_option(field, key)

    def get(self, field, key):
        return self.cf.get(field, key)

    def set(self, field, key, value):
        if self.cf.has_section(field) is False:
            self.cf.add_section(field)
        self.cf.set(field, key, str(value))
        return self.cf.write(open(self.file, 'w'))
