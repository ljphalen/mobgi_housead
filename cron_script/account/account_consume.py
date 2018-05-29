#!/usr/bin/env python
# -*- coding:utf-8 -*-
import ConfigParser
import MySQLdb
import datetime
import os
import redis
import time
from settings import config
from utils.mysql import MySQL


class AccountConsume(object):
    CHACHE_EPRIE = 900
    """回调广告商通知激活"""

    def __init__(self):
        self.initdb()
        self.logfileHandle = open(config.LOGPATH + "account_consume.txt", 'a')
        self.confname = config.CONSUME_CONF
        self.consume_log_fieldstr = '`uid`,  `account_type`, `balance_before`, `balance`,  `batchdeductionid`,  `price`,  `real_price`, `need_price`,  `create_time`'
        self.CONSUME_LOG_FIELD = "(" + self.consume_log_fieldstr + ")"

    def initdb(self):
        self.houseadDb = MySQL(config.MOBGI_HOUSEAD['host'], config.MOBGI_HOUSEAD['user'],
                               config.MOBGI_HOUSEAD['passwd'], port=config.MOBGI_HOUSEAD['port'],
                               db=config.MOBGI_HOUSEAD['db'])
        self.mobgiCharge = MySQL(config.MOBGI_CHARGE['host'], config.MOBGI_CHARGE['user'],
                                 config.MOBGI_CHARGE['passwd'], port=config.MOBGI_CHARGE['port'],
                                 db=config.MOBGI_CHARGE['db'])
        return self.houseadDb, self.mobgiCharge

    def mylog(self, msg):
        print time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()) + " " + msg
        self.logfileHandle.write(time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()) + " " + msg + '\n')

    def getRows(self, select_sql, db):
        try:
            db.queryNotCatch(select_sql)
        except MySQLdb.Error as m:
            self.mylog("db reconnect!")
            self.initdb()
            db.query(select_sql)
        result = db.fetchAll();
        return result

    def getOne(self, sql, db):
        try:
            db.fetchRow(sql)
        except MySQLdb.Error as m:
            self.mylog("db reconnect!")
            self.initdb()
            db.query(sql)
        result = db.fetchRow();
        return result

    # 新增记录
    def insertRows(self, insert_sql, db):
        self.cur_sql = insert_sql
        try:
            db.queryNotCatch(insert_sql)
            # return self.db.commit()
        except MySQLdb.Error as m:
            self.mylog("db reconnect!")
            self.initdb()
            db.queryNotCatch(insert_sql)
            return db.commit()

    # 更新记录
    def updateRows(self, update_sql, db):
        self.cur_sql = update_sql
        try:
            db.queryNotCatch(update_sql)
        # return self.db.commit()
        except MySQLdb.Error as m:
            self.mylog("db reconnect!")
            self.initdb()
            db.queryNotCatch(update_sql)
        return db.commit()

    def getUidByAdid(self, ad_id):
        ad_sql = "select account_id from delivery_ad_conf_list where id= '" + ad_id + "'"
        ad_result = self.getRows(ad_sql, self.houseadDb)
        if len(ad_result) == 0:
            self.mylog('cant get account_id. sql:' + ad_sql)
            return False
        else:
            return ad_result[0]['account_id']

    def getUidUnitidByOriginalityid(self, originality_id):
        key = config.REDIS_CACHEKEY['ORIGINALITY_INFO'] + originality_id
        if self.redisCache.exists(key) is False:
            sql = "select account_id,unit_id, ad_id from delivery_originality_relation where id= '" + originality_id + "'"
            result = self.getRows(sql, self.houseadDb)
            if len(result) == 0:
                self.mylog('cant get account_id. sql:' + sql)
                return False, False, False
            cacheData = result[0]
            self.redisCache.hmset(key, cacheData)
            self.redisCache.expire(key, self.CHACHE_EPRIE)
        else:
            cacheData = self.redisCache.hgetall(key)
        return cacheData['account_id'], cacheData['unit_id'], cacheData['ad_id']

    def getAccountDayconsumptionlimitByUid(self, uid):
        key = config.REDIS_CACHEKEY['ACCOUNT_CONSUME_LIMIT'] + uid
        if self.redisCache.exists(key) is False:
            sql = "select day_consumption_limit from advertiser_account_consumption_limit where uid= '" + uid + "'"
            result = self.getRows(sql, self.houseadDb)
            if len(result) == 0:
                self.mylog('cant get day_consumption_limit. sql:' + sql)
                return 0
            cacheData = result[0]
            self.redisCache.hmset(key, cacheData)
            self.redisCache.expire(key, self.CHACHE_EPRIE)
        else:
            cacheData = self.redisCache.hgetall(key)
        return cacheData['day_consumption_limit']

    def getUnitInfoByUnitid(self, unit_id):
        # limit_range
        key = config.REDIS_CACHEKEY['UNIT_INFO'] + unit_id
        if self.redisCache.exists(key) is False:
            sql = "select limit_type, limit_range,unit_type from delivery_unit_conf where id= '" + unit_id + "'"
            result = self.getRows(sql, self.houseadDb)
            if len(result) == 0:
                self.mylog('cant get unit_info. sql:' + sql)
                return False
            cacheData = result[0]
            self.redisCache.hmset(key, cacheData)
            self.redisCache.expire(key, self.CHACHE_EPRIE)
        else:
            cacheData = self.redisCache.hgetall(key)
        return cacheData

    def getAdInfoByid(self, adId):
        # limit_range
        key = config.REDIS_CACHEKEY['AD_INFO'] + adId
        if self.redisCache.exists(key) is False:
            sql = "select * from delivery_ad_conf_list where id= '" + adId + "'"
            result = self.getRows(sql, self.houseadDb)
            if len(result) == 0:
                self.mylog('cant get ad_info. sql:' + sql)
                return False
            cacheData = result[0]
            self.redisCache.hmset(key, cacheData)
            self.redisCache.expire(key, self.CHACHE_EPRIE)
        else:
            cacheData = self.redisCache.hgetall(key)
        return cacheData



    def getAccountdetailByUid(self, uid):
        sql = "select * from advertiser_account_detail where uid= '" + uid + "'"
        result = self.getRows(sql, self.houseadDb)
        if len(result) == 0:
            self.mylog('cant get account_detail. sql:' + sql)
            return False
        else:
            retrunData = {}
            for account in result:
                retrunData[account['account_type']] = account
            return retrunData

    def getVirtualAccountdetailByUidType(self, uid, account_type):
        virtual_account_sql = "select * from advertiser_account_virtual_detail where uid= '" + uid + "' and account_type='" + account_type + "' and status='normal' and balance !='0' order by expire_time asc"
        virtual_account_result = self.getRows(virtual_account_sql, self.houseadDb)
        if len(virtual_account_result) == 0:
            self.mylog('cant get virtual_account_detail. sql:' + virtual_account_sql)
            return False
        else:
            return virtual_account_result

    # 获取帐户所有子帐户的今日消费
    def getAccountDayConsumptions(self, uid, date):
        result = {}
        get_uid_date_consumption_sql = "select * from advertiser_account_day_consumption where uid='" + str(
            uid) + "' and date='" + str(date) + "'";
        day_consumption_result = self.getRows(get_uid_date_consumption_sql, self.houseadDb)
        if len(day_consumption_result) == 0:
            self.mylog('cant get day_consumption_result. sql:' + get_uid_date_consumption_sql)
            return result
        else:
            for consumption_item in day_consumption_result:
                result[consumption_item['account_type']] = consumption_item
            return result

    # 获取指定子帐户的日消耗额
    def getAccountUidAccounttypeDayConsumption(self, uid, account_type, date):
        get_uid_accounttype_date_consumption_sql = "select * from advertiser_account_day_consumption where uid='" + str(
            uid) + "' and account_type='" + str(account_type) + "' and date='" + str(date) + "'";
        day_consumption_result = self.getRows(get_uid_accounttype_date_consumption_sql, self.houseadDb)
        if len(day_consumption_result) == 0:
            self.mylog(
                'cant get uid_accounttype_day_consumption_result. sql:' + get_uid_accounttype_date_consumption_sql)
            return False
        else:
            return day_consumption_result[0]['consumption']

    # 获取指定帐号的今日消费总额
    def getUidConsumptiontotal(self, uid, date):
        day_consumption_results = self.getAccountDayConsumptions(uid, date)
        if len(day_consumption_results) == 0:
            consumption = 0
        else:
            consumption = 0
            for consumption_item in day_consumption_results:
                consumption += float(day_consumption_results[consumption_item]['consumption'])
        return consumption


    def getUnitOriginalityDayConsumption(self, unit_id, originality_id, date):
        sql = "select * from advertiser_unit_originality_day_consumption where unit_id='" + str(unit_id) + "' and originality_id='" + str(originality_id) + "' and date='" + str(date) + "'";
        results = self.getRows(sql, self.houseadDb)
        if len(results) == 0:
            self.mylog('cant get unit_originality_day_consumption_result. sql:' + sql)
            return False
        else:
            return results[0]['consumption']

    def getUnitConsumptiontotal(self, unit_id, date):
        results = {}
        sql = "select * from advertiser_unit_originality_day_consumption where unit_id='" + str(unit_id) + "' and date='" + str(date) + "'";
        results = self.getRows(sql, self.houseadDb)
        if len(results) == 0:
            consumption = 0
        else:
            consumption = 0
            for item in results:
                consumption += float(item['consumption'])
        return consumption

    def getAdInfoConsumptiontotal(self, adId, date):
        results = {}
        sql = "select * from advertiser_unit_originality_day_consumption where ad_id='" + str(adId) + "' and date='" + str(date) + "'";
        results = self.getRows(sql, self.houseadDb)
        if len(results) == 0:
            consumption = 0
        else:
            consumption = 0
            for item in results:
                consumption += float(item['consumption'])
        return consumption

    # 虚拟金详情扣费(同步扣除虚拟帐号详情的金额,优先扣除到期时间最近的虚拟金.)
    def updateVirtualAccountDetail(self, uid, account_type, real_fee):
        if account_type != 'redisCache':
            virtual_account_details = self.getVirtualAccountdetailByUidType(uid, account_type)
            if virtual_account_details != False:
                need_virtual_fee = float(real_fee)
                for virtual_item in virtual_account_details:
                    if float(virtual_item['balance']) > float(need_virtual_fee):
                        real_virtual_fee = need_virtual_fee
                        new_virtual_balance = float(virtual_item['balance']) - real_virtual_fee
                        need_virtual_fee = 0
                        update_virtual_account_sql = "update advertiser_account_virtual_detail set balance='" + str(
                            new_virtual_balance) + "' where  uid='" + str(
                            uid) + "' and account_type='" + account_type + "' and taskdetailid='" + virtual_item[
                                                         'taskdetailid'] + "'"
                    else:
                        real_virtual_fee = float(virtual_item['balance'])
                        new_virtual_balance = 0
                        need_virtual_fee = need_virtual_fee - real_virtual_fee
                        update_virtual_account_sql = "update advertiser_account_virtual_detail set balance='" + str(
                            new_virtual_balance) + "', status='runout' where  uid='" + str(
                            uid) + "' and account_type='" + account_type + "' and taskdetailid='" + virtual_item[
                                                         'taskdetailid'] + "'"
                    self.updateRows(update_virtual_account_sql, self.houseadDb)
                    self.mylog('uid:' + str(uid) + ", " + 'account_type:' + account_type + ", " + 'taskdetailid:' +
                               virtual_item['taskdetailid'] + ', balance: ' + str(
                        new_virtual_balance) + ',current real_virtual_fee:' + str(
                        real_virtual_fee) + ', need_virtual_fee:' + str(need_virtual_fee))
                    self.mylog(update_virtual_account_sql)
                    if need_virtual_fee == 0:
                        break

    # 更新具体子帐号的日消耗
    def updateAccountDayConsumption(self, uid, date, account_type, real_fee):
        old_accounttype_consumption = self.getAccountUidAccounttypeDayConsumption(uid, account_type, date)
        if old_accounttype_consumption != False:
            new_accounttype_consumption = float(old_accounttype_consumption) + real_fee
            update_uid_date_consumption_sql = "update advertiser_account_day_consumption set consumption='" + str(
                new_accounttype_consumption) + "', update_time='" + str(self.curtime) + "' where uid='" + str(
                uid) + "' and account_type='" + str(account_type) + "' and date='" + str(date) + "'"
            self.updateRows(update_uid_date_consumption_sql, self.houseadDb)
            self.mylog(update_uid_date_consumption_sql)
        else:
            new_accounttype_consumption = real_fee
            add_uid_date_consumption_sql = "insert into advertiser_account_day_consumption" \
                                           "(`uid`, `account_type`, `date`, `consumption`, `create_time`, `update_time`) " \
                                           "value" \
                                           "('" + str(uid) + "','" + account_type + "', '" + str(date) + "', '" + str(
                new_accounttype_consumption) + "','" + str(self.curtime) + "','" + str(self.curtime) + "')"
            self.insertRows(add_uid_date_consumption_sql, self.houseadDb)
            self.mylog(add_uid_date_consumption_sql)
        self.mylog('uid:' + str(uid) + ", date:" + str(date) + ", account_type:" + str(
            account_type) + ", account_consumption:" + str(new_accounttype_consumption))

    def updateAdDayConsumption(self, unit_id, ad_id, date, originality_id, real_fee):
        old_originality_consumption = self.getUnitOriginalityDayConsumption(unit_id, originality_id, date)
        if old_originality_consumption != False:
            new_originality_consumption = float(old_originality_consumption) + real_fee
            update_unit_date_consumption_sql = "update advertiser_unit_originality_day_consumption set consumption='" + str(
                new_originality_consumption) + "', update_time='" + str(self.curtime) + "' where unit_id='" + str(
                unit_id) + "' and originality_id='" + str(originality_id) + "' and date='" + str(date) + "'"
            self.updateRows(update_unit_date_consumption_sql, self.houseadDb)
            self.mylog(update_unit_date_consumption_sql)
        else:
            new_originality_consumption = real_fee
            add_unit_date_consumption_sql = "insert into advertiser_unit_originality_day_consumption" \
                                            "(`unit_id`, `ad_id`, `originality_id`, `date`, `consumption`, `create_time`, `update_time`) " \
                                            "value" \
                                            "('" + str(unit_id) + "','" + ad_id + "','" + originality_id + "', '" + str(
                date) + "', '" + str(new_originality_consumption) + "','" + str(self.curtime) + "','" + str(
                self.curtime) + "')"
            self.insertRows(add_unit_date_consumption_sql, self.houseadDb)
            self.mylog(add_unit_date_consumption_sql)
        self.mylog('unit_id:' + str(unit_id) + ", date:" + str(date) + ", originality_id:" + str(
            originality_id) + ", account_consumption:" + str(new_originality_consumption))

    def getLastConfigId(self):
        cf = self.getConfigObject()
        minId = cf.getint("account_consume", 'batchid')
        return minId

    def getConfigObject(self):
        # 读取配置中已经处理过了的processid
        cf = ConfigParser.ConfigParser()
        cf.read(config.CONSUME_CONF)
        return cf

    def getChargeDataCurrentId(self, lastId):
        sql = 'select * from adx_charge_minute where id > ' + str(lastId) + ' order by id desc limit 1';
        result = self.getRows(sql, self.mobgiCharge)
        if len(result) <= 0:
            return 0
        return result[0]['id']

    def initRedis(self):
        if config.REDIS.has_key('password') is True:
            poolr = redis.ConnectionPool(host=config.REDIS["host"], port=config.REDIS["port"],
                                         password=config.REDIS["password"])
        else:
            poolr = redis.ConnectionPool(host=config.REDIS["host"], port=config.REDIS["port"])
        self.redisCache = redis.Redis(connection_pool=poolr)

    def getChargeDataList(self, lastId, currentId):
        sql = 'select * from adx_charge_minute where id > ' + str(lastId) + ' and id<=' + str(
            currentId) + ' order by id asc limit ' + str(config.PROCESS_BATCH_NUM);
        result = self.getRows(sql, self.mobgiCharge)
        return result

    def updateMinuteCharge(self, is_mobgi, is_charged, id):
        sql = "update adx_charge_minute set is_charged= '" + str(is_charged) + "' ,is_mobgi ='" + str(
            is_mobgi) + "' ,update_time='" + str(self.curDatetime) + "' where id='" + str(id) + "'"
        self.updateRows(sql, self.mobgiCharge)
        self.mylog(sql)

    def run(self):
        self.mylog('start AccountConsume.')
        lastId = self.getLastConfigId()
        currentId = self.getChargeDataCurrentId(lastId)
        # print new_result
        if currentId <= 0:
            self.mylog("len(news_result) = 0")
            self.mylog('sleep ' + str(config.SLEEPSECONDS) + ' seconds.')
            self.mylog('end AccountConsume. \n')
            time.sleep(config.SLEEPSECONDS)
            return True

        while (int(lastId) < int(currentId)):
            self.initRedis()
            num = 0
            chargeDataList = self.getChargeDataList(lastId, currentId)
            originalityIdReluidList = {}
            originalityIdRelUnitIdList = {}
            originalityIdRelAdIdList = {}
            accountConsumptionLimitList = {}  # 今日消耗限额
            accountConsumptionList = {}  # 今日消耗
            unitInfo = {}
            adInfo = {}
            unitIdConsumptionList = {}
            adInfoConsumptionList = {}

            for item in chargeDataList:
                self.cur_sql = 'none'
                try:
                    # 预防多次扣除
                    if item['is_charged'] == '1':
                        self.mylog('id:' + str(item['id'] + ', is_charged:' + item['is_charged']) + ', continue!')
                        continue

                    # 定位上报数据的帐号id
                    if originalityIdReluidList.has_key(item['originality_id']):
                        uid = originalityIdReluidList[item['originality_id']]
                        unitId = originalityIdRelUnitIdList[item['originality_id']]
                        adId = originalityIdRelAdIdList[item['originality_id']]
                    else:
                        uid, unitId, adId = self.getUidUnitidByOriginalityid(item['originality_id'])
                        if uid != False:
                            originalityIdReluidList[item['originality_id']] = uid
                            originalityIdRelUnitIdList[item['originality_id']] = unitId
                            originalityIdRelAdIdList[item['originality_id']] = adId
                        else:
                            self.mylog('uid unit_id ad_id is False, continue!')
                            continue

                    # 获取账号消费限制
                    if accountConsumptionLimitList.has_key(uid):
                        accountConsumptionLimit = accountConsumptionLimitList[uid]
                    else:
                        accountConsumptionLimit = self.getAccountDayconsumptionlimitByUid(uid)
                        accountConsumptionLimitList[uid] = accountConsumptionLimit

                    # 获取单元信息
                    if unitInfo.has_key(unitId):
                        unitIdConsumptionLimit = unitInfo[unitId]['limit_range']
                    else:
                        unitInfoCacheData = self.getUnitInfoByUnitid(unitId)
                        if unitInfoCacheData != False:
                            unitInfo[unitId] = unitInfoCacheData
                            unitIdConsumptionLimit = unitInfoCacheData['limit_range']
                        else:
                            self.mylog('unitId:' + str(unitId) + ', unitInfo is False, continue!')
                            continue
                    # 获取广告信息todo
                            # 获取单元信息
                    if adInfo.has_key(adId) is False:
                        adInfoInfoCacheData = self.getAdInfoByid(adId)
                        if adInfoInfoCacheData != False:
                            adInfo[adId] = adInfoInfoCacheData
                        else:
                            self.mylog('adId:' + str(adId) + ', adInfo is False, continue!')
                            continue

                    # 获取帐号信息（需要锁表）
                    accountDetailInfo = self.getAccountdetailByUid(uid)
                    if accountDetailInfo == False:
                        self.mylog('accountDetailInfo is False, continue!')
                        continue

                    # 账号的总金额
                    accountTotalAmount = 0.0
                    # print accountDetailInfo
                    for accounType in config.DEDUCTION_ORDER:
                        if accountDetailInfo.has_key(accounType):
                            accountTotalAmount += float(accountDetailInfo[accounType]['balance'])
                    self.mylog('uid:' + str(uid) + "," + 'accountTotalAmount:' + str(accountTotalAmount) + ', deductibleAmount = ' + str(item['amount']))
                    if accountTotalAmount == 0:
                        self.mylog('关闭广告\n');
                        continue

                    # 定位日期
                    startTimeStamp = int(time.mktime(time.strptime(item['create_time'][0:10], "%Y-%m-%d")))
                    date = time.strftime("%Y%m%d", time.localtime(startTimeStamp))
                    self.curtime = str(time.time())
                    self.curDatetime = time.strftime("%Y-%m-%d %H:%M:%S")

                    # 内外部标志
                    isMobgi = int(unitInfo[unitId]['unit_type']) - 1
                    self.updateMinuteCharge(isMobgi, 0, item['id'])

                    # 账号每天的消耗
                    if accountConsumptionList.has_key(uid):
                        accountDayConsumption = accountConsumptionList[uid]
                    else:
                        accountDayConsumption = self.getUidConsumptiontotal(uid, date)
                        accountConsumptionList[uid] = accountDayConsumption

                    if float(accountConsumptionLimit) != 0.0 and float(accountDayConsumption) >= float(accountConsumptionLimit):
                        self.mylog('accountConsumptionLimit:' + str( accountConsumptionLimit) + ', accountDayConsumption:' + str(accountDayConsumption))
                        self.mylog('\n')
                        continue

                    # 广告计划每天消耗
                    if unitIdConsumptionList.has_key(unitId):
                        unitIdDayConsumption = unitIdConsumptionList[unitId];
                    else:
                        unitIdDayConsumption = self.getUnitConsumptiontotal(unitId, date)
                        unitIdConsumptionList[unitId] = unitIdDayConsumption
                    # 达到限制金额
                    if unitInfo[unitId]['limit_type'] == '1' and float(unitIdDayConsumption) >= float(unitIdConsumptionLimit):
                        self.mylog('unitIdDayConsumption:' + str(unitIdDayConsumption) + ', unitIdConsumptionLimit:' + str(unitIdConsumptionLimit))
                        self.mylog('\n')
                        continue

                    # 广告活动每天消耗
                    if adInfoConsumptionList.has_key(adId):
                        adInfoDayConsumption = adInfoConsumptionList[adId]
                    else:
                        adInfoDayConsumption = self.getAdInfoConsumptiontotal(adId, date)
                        adInfoConsumptionList[adId] = adInfoDayConsumption

                    # 达到限制金额
                    if adInfo[adId]['ad_limit_type'] == '1' and float(adInfoDayConsumption) >= float(adInfo[adId]['ad_limit_amount']):
                        self.mylog('adInfoDayConsumption:' + str(adInfoDayConsumption) + ', adInfoConsumptionLimit:' + str(adInfo[adId]['ad_limit_amount']))
                        self.mylog('\n')
                        continue

                    # 应扣的总金额，应扣金额
                    deductibleTotalAmount = 0
                    deductibleAmount = float(item['amount'])
                    consume_log_sql_valuelist = []
                    for accounType in config.DEDUCTION_ORDER:
                        if accountDetailInfo.has_key(accounType):
                            if float(accountDetailInfo[accounType]['balance']) == 0:
                                self.mylog('uid:' + str(uid) + ", " + 'account_type:' + accounType + ', balance = 0, continue')
                                continue
                            self.mylog('uid:' + str(uid) + ", " + 'account_type:' + accounType + ', balance: ' +accountDetailInfo[accounType]['balance'])
                            if float(accountDetailInfo[accounType]['balance']) > deductibleAmount:
                                # 当次扣费金额
                                curDeductibleAmount = deductibleAmount
                                deductibleTotalAmount += curDeductibleAmount
                                # 当前的余额
                                curBalance = float(accountDetailInfo[accounType]['balance']) - curDeductibleAmount
                                deductibleAmount = 0
                            else:
                                curDeductibleAmount = float(accountDetailInfo[accounType]['balance'])
                                deductibleTotalAmount += curDeductibleAmount
                                curBalance = 0
                                deductibleAmount = deductibleAmount - curDeductibleAmount
                            update_account_sql = "update advertiser_account_detail set balance='" + str(curBalance) + "' where  uid='" + str(uid) + "' and account_type='" + accounType + "'"
                            consume_log_sql_valueitem = "('" + str(uid) + "', '" + accounType + "', '" + accountDetailInfo[accounType]['balance'] + "', '" + str(curBalance) + "', '" + item['id'] + "', '" + item['amount'] + "', '" + str(curDeductibleAmount) + "', '" + str(deductibleAmount) + "', '" + str(self.curtime) + "')"
                            consume_log_sql_valuelist.append(consume_log_sql_valueitem)

                            self.updateRows(update_account_sql, self.houseadDb)
                            self.mylog('uid:' + str(uid) + ", " + 'account_type:' + accounType + ', curBalance: ' + str(curBalance) + ', deductibleTotalAmount: ' + str(deductibleTotalAmount) + ',curDeductibleAmount:' + str(curDeductibleAmount) + ', deductibleAmount:' + str(deductibleAmount))
                            self.mylog(update_account_sql)

                            # 同步扣除虚拟帐号详情的金额,优先扣除到期时间最近的虚拟金.
                            self.updateVirtualAccountDetail(uid, accounType, curDeductibleAmount)

                            # 更新日消耗数据
                            # (1.1)更新db日消耗数据
                            self.updateAccountDayConsumption(uid, date, accounType, curDeductibleAmount)
                            # (1.2)更新db下的单元创意的消耗数据
                            self.updateAdDayConsumption(unitId, adId, date, item['originality_id'], curDeductibleAmount)

                            if deductibleAmount == 0:
                                break
                        else:
                            continue;

                    # 更新redis缓存,帐号余额
                    accountTotalBalance = accountTotalAmount - deductibleTotalAmount
                    accountTotalBalanceRedisKey = config.REDIS_CACHEKEY['ACCOUNT_TOTAL_BALANCE'] + '_' + str(uid)
                    self.redisCache.set(accountTotalBalanceRedisKey, accountTotalBalance)
                    self.redisCache.expire(accountTotalBalanceRedisKey, 600)

                    # 更新批量数据的是否外部订单状态,是否扣费过
                    self.updateMinuteCharge(isMobgi, 1, item['id'])

                    # (2.1)更新redis缓存,日账号消耗数据
                    currentAccountDayConsumption = accountDayConsumption + float(deductibleTotalAmount)
                    accountConsumptionList[uid] = currentAccountDayConsumption
                    accountDayConsumptionRediskey = config.REDIS_CACHEKEY['ACCOUNT_DAY_CONSUMPTION'] + '_' + str(uid) + '_' + str(date)
                    self.redisCache.set(accountDayConsumptionRediskey, currentAccountDayConsumption)
                    self.redisCache.expire(accountDayConsumptionRediskey, 600)
                    self.mylog('uid:' + str(uid) + ", date:" + str(date) + ", consumption:" + str(currentAccountDayConsumption))

                    # (2.2)更新redis单元日消耗数据
                    curUnitIdConsumption = unitIdDayConsumption + float(deductibleTotalAmount)
                    unitIdConsumptionList[unitId] = curUnitIdConsumption
                    unitIdDayConsumptionRedisKey = config.REDIS_CACHEKEY['UNIT_DAY_CONSUMPTION'] + '_' + str(unitId) + '_' + str(date)
                    self.redisCache.set(unitIdDayConsumptionRedisKey, curUnitIdConsumption)
                    self.redisCache.expire(unitIdDayConsumptionRedisKey, 600)
                    self.mylog('unitId:' + str(unitId) + ", date:" + str(date) + ", consumption:" + str(curUnitIdConsumption))

                    curAdinfoConsumption = adInfoDayConsumption + float(deductibleTotalAmount)
                    adInfoConsumptionList[adId] = curAdinfoConsumption
                    adInfoDayConsumptionRedisKey = config.REDIS_CACHEKEY['ADINFO_DAY_CONSUMPTION'] + '_' + str(adId) + '_' + str(date)
                    self.redisCache.set(adInfoDayConsumptionRedisKey, curAdinfoConsumption)
                    self.redisCache.expire(adInfoDayConsumptionRedisKey, 600)
                    self.mylog('adId:' + str(adId) + ", date:" + str(date) + ", consumption:" + str(curAdinfoConsumption))

                    # 添加各个帐号消耗日志
                    if len(consume_log_sql_valuelist) != 0:
                        consume_log_sql_valuestr = ','.join(consume_log_sql_valuelist)
                        consume_log_sql = 'insert into advertiser_account_consume_log ' + self.CONSUME_LOG_FIELD + 'values' + consume_log_sql_valuestr + ';'
                        self.insertRows(consume_log_sql, self.houseadDb)

                    # 提交db操作的增,改
                    self.houseadDb.commit()
                    self.mobgiCharge.commit()
                except MySQLdb.Error as e:
                    self.mylog('exception: ' + "Mysql Error %d: %s" % (e.args[0], e.args[1]))
                    self.mylog('sql:' + self.cur_sql)
                    self.mylog('rollback now!')
                    self.houseadDb.rollback()
                    self.mobgiCharge.rollback()

                if accountTotalAmount - float(item['amount']) <= 0:
                    # need to do
                    self.mylog('关闭广告\n')

                self.mylog('finish\n')
                # print account_detail_info

            lastId = int(item['id'])
            cf = self.getConfigObject()
            cf.set("account_consume", 'batchid', lastId)
            cf.write(open(self.confname, "w"))
        self.mylog('end AccountConsume.')


if __name__ == '__main__':
    while 1:
        accountconsume = AccountConsume()
        accountconsume.run()
