#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os
import sys
import time
from tool_base import ToolBase
import config.db as configDb


class toolRainBow(ToolBase):
    # 初始化条件
    def init(self):
        self.dbRainBowData = self.initDb(configDb.MYSQL_RAINBOW)  # 彩虹报表数据库
        self.dbNewData = self.initDb(configDb.MYSQL_MOBGI_DATA)
        self.RainBowTable = configDb.MYSQL_RAINBOW['table']
        self.GameMapTable = 'config_app'
        self.AppDauTable = 'report_dau'

    # 获取gameidMap
    def getGameIdMap(self):
        try:
            sql = "select dgc_game_id as game_id,app_key from %s where dgc_game_id != 0" % (self.GameMapTable)
            items = self.dbNewData.fetchall(sql)
        except Exception, e:
            self.error("getGameIdMap Err:" + str(e))
        result = {}
        for item in items[0]:
            result[item['app_key']] = item['game_id']
        return result

    # 获取game_dau的数据
    def getDauList(self):
        result = {}
        try:
            sql = "select id,app_key,game_dau from %s where ad_type=0 and channel_gid = 0 and days=\"%s\"" % (self.AppDauTable, str(self.startDatePosition))
            result = self.dbNewData.fetchall(sql)
        except Exception, e:
            self.error("getDauList Err:" + str(e))
        return result[0]

    # 获取游戏活跃
    def getGameDau(self):
        sql = """SELECT game_id,day_actv_usercnt as game_dau FROM `%s`where stat_date="%s" and  game_id in (%s);
        """ % (self.RainBowTable, str(self.startDatePosition), ','.join(map(str, self.gameMap.values())))
        items = self.dbRainBowData.fetchall(sql)
        result = {}
        for item in items[0]:
            result[item['game_id']] = item['game_dau']
        return result

    # 更新游戏活跃
    def updateGameDau(self, dauList, gameDau):
        try:
            for item in dauList:
                if self.gameMap.has_key(item['app_key']) and gameDau.has_key(str(self.gameMap[item['app_key']])):
                    gameId = str(self.gameMap[item['app_key']])
                    if item['game_dau'] != gameDau[gameId]:
                        sql = """update %s set game_dau=%s where id=%s;""" % (self.AppDauTable, str(gameDau[gameId]), str(item['id']))
                        if self.dbNewData.execute(sql) is False:
                            return False
                        self.info(sql)
                elif self.gameMap.has_key(item['app_key']):
                    self.info("gameDau can not found game_id=" + str(self.gameMap[item['app_key']]))
                else:
                    self.info("gameId_appkey can not found app_key=" + str(item['app_key']))
        except Exception, e:
            self.info("update game_dau fail :" + str(e))
            quit()

    # 获取脚本状态
    def checkCondition(self):
        sql = "select * from config_cron where script_name = '%s'" % (self.scriptName)
        condition = self.dbNewData.fetchone(sql)
        if time.time() - condition['position'] > 86400:
            self.startPosition = condition['position']
            self.startDatePosition = self.exchangeTimeStampDate(self.startPosition, '%Y-%m-%d')  # 用这个来取数据
            self.startDateTimePosition = self.exchangeTimeStampDate(self.startPosition, '%Y-%m-%d %H:%M:%S')  # 用这个来取数据
            self.period = condition['permid']
            return True
        else:
            return False

    # 更新脚本状态
    def updatePosition(self):
        nextPosition = self.startPosition + self.period
        sql = "update config_cron set position = %s where script_name = '%s'" % (int(nextPosition), self.scriptName)
        self.dbNewData.execute(sql)
        self.info('netx position is:' + str(nextPosition))

    def run(self):
        try:
            self.init()
            if self.checkCondition() is not True:
                self.info("No data")
                return False
            self.gameMap = self.getGameIdMap()
            gameDau = self.getGameDau()  # 获取实时dau
            if len(gameDau) > 0:
                dauList = self.getDauList()  # 获取原来表中的dau数据
                if self.updateGameDau(dauList, gameDau) is not False:
                    self.updatePosition()  # 更新位置
                    return True
            else:
                self.info('No DATA!')
        except Exception, e:
            self.error("run error:" + str(e))


if __name__ == '__main__':
    while 1:
        obj = toolRainBow('tool_rainbow')
        result=obj.run()
        if result is not True:
            break
