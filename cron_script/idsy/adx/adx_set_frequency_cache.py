#!/usr/bin/env python
# -*- coding:utf-8 -*-


import time
from ad_qbase import AdQbase
import config.db as configDb
import config.redis as configRedis

# #debug
# CHAREG_TYPE_VIEW = configHouseAd.CHAREG_TYPE_VIEW
# #debug

# lpush adx_RQ:adx_charge '{"originality_id":233,"dsp_id":"Housead_DSP","imei":"50509907-7383-45B6-83FA-85603661D82D","charge_type":1,
# "event_type":5,"price":0.0005,"created_time":1500279972}'
# lpush adx_RQ:adx_charge '{"originality_id":231,"dsp_id":"Housead_DSP","imei":"50509907-7383-45B6-83FA-85603661D82D","charge_type":1,
# "event_type":5,"price":0.0005,"created_time":1500279972}'
# lpush adx_RQ:adx_charge '{"originality_id":231,"dsp_id":"Housead_DSP","imei":"867348026517826","charge_type":1,"event_type":5,"price":0.0005,
# "created_time":1500279972}'
# lpush adx_RQ:adx_charge '{"originality_id":198,"dsp_id":"Housead_DSP","imei":"ffffffff-8059-8a17-66db-c1063de1ea70","charge_type":1,
# "event_type":5,"price":0.0005,"created_time":1500279972}'


class FrequencyCache(AdQbase):
    """回调广告商通知激活"""

    def init(self):
        self.dbhousead = self.initDb(configDb.MYSQL_MOBGI_HOUSEAD)
        self.r = self.initRedis(configRedis.REDIS_MOBGI)
        # #debug
        # self.rq = self.initRedis(configRedis.REDIS_QUEUE_ADX)
        # #debug

    def run(self, streamlist):
        # #debug
        # rkey = configHouseAd.RQ_ADX_CLIENT_CHARGE
        # streamlist = []
        # for i in range(100):
        #     stream = self.rq.lpop(rkey)
        #     if stream is None:
        #         break
        #     stream = json.loads(stream)
        #     if stream['event_type'] == CHAREG_TYPE_VIEW and stream['originality_id']!=0:
        #             streamlist.append(stream)
        # print streamlist
        # #debug

        if len(streamlist) == 0:
            return False
        streamlist = self.checkStreamlist(streamlist)

        # #debug
        # print streamlist
        # #debug

        originality_frequencytype, originality_ad = self.getOriginalityFrequencyType(streamlist)
        frequency = self.getFrequency(streamlist, originality_frequencytype, originality_ad)

        # #debug
        # print originality_frequencytype, originality_ad
        # print frequency
        # #debug

        self.setFrequencyCache(frequency)

    # 校验参数
    def checkStreamlist(self, streamlist):
        streamlist_ = []
        for stream in streamlist:
            if stream['orig_id'] == 0:
                continue
            if stream['imei'] == '':
                continue
            streamlist_.append(stream)
        return streamlist_

    # 获取创意是否设置了创意定向或者广告定向
    def getOriginalityFrequencyType(self, streamlist):
        originality_frequencytype = {}
        originality_ad = {}
        for stream in streamlist:
            if originality_frequencytype.has_key(stream['orig_id']):
                continue
            else:
                frequencytype_rediskey = 'housead_fiterfrequencytype_originality_' + str(stream['orig_id'])
                frequencytype_originality_ad_rediskey = 'housead_fiterfrequency_originality_ad_' + str(stream['orig_id'])
                frequencytype_redisvalue = self.r.get(frequencytype_rediskey)
                if frequencytype_redisvalue is None:
                    sql = 'SELECT o.id, o.`ad_id` ,a.frequency_type, a.frequency FROM `delivery_originality_relation` AS o INNER JOIN ' \
                          '`delivery_ad_conf_list` AS a ON o.ad_id=a.`id` WHERE o.`id`=' + str(stream['orig_id'])
                    result = self.dbhousead.fetchone(sql)
                    if result is None or result["id"] is None:
                        originality_frequencytype[stream['orig_id']] = 'no'
                    else:
                        originality_frequencytype[stream['orig_id']] = result['frequency_type']
                        if result['frequency_type'] == 'ad':
                            originality_ad[stream['orig_id']] = result['ad_id']
                            self.r.set(frequencytype_originality_ad_rediskey, originality_ad[stream['orig_id']])
                            self.r.expire(frequencytype_originality_ad_rediskey, 86400)
                    self.r.set(frequencytype_rediskey, originality_frequencytype[stream['orig_id']])
                    self.r.expire(frequencytype_rediskey, 300)
                    # print 'from db'
                else:
                    # print 'from cache'
                    originality_frequencytype[stream['orig_id']] = frequencytype_redisvalue
                    if originality_frequencytype[stream['orig_id']] == 'ad':
                        originality_ad[stream['orig_id']] = self.r.get(frequencytype_originality_ad_rediskey)
                        # print originality_frequencytype
                        # print '--------------------'
                        # #debug
                        # print '=============='
                        # print frequencytype_redisvalue
                        # # quit()
                        # #debug
        return originality_frequencytype, originality_ad

    # 获取cache
    def getFrequency(self, streamlist, originality_frequencytype, originality_ad):

        today = time.strftime("%Y%m%d")
        frequency = {}
        for stream in streamlist:
            # self.info(str(stream))
            # 格式：housead_fiterfrequency_ad_日期_广告id_设备ID(imei/idfa)  ex. housead_fiterfrequency_ad_20170714_147_867348026517826
            if originality_frequencytype[stream['orig_id']] == 'ad':
                cachekey = 'housead_fiterfrequency_ad_' + str(today) + '_' + str(originality_ad[stream['orig_id']]) + '_' + stream['imei']
            # 格式：housead_fiterfrequency_originality_日期_创意id_设备ID(imei/idfa)  ex. housead_fiterfrequency_originality_20170714_231_867348026517826
            elif originality_frequencytype[stream['orig_id']] == 'originality':
                cachekey = 'housead_fiterfrequency_originality_' + str(today) + '_' + str(stream['orig_id']) + '_' + stream['imei']
            else:
                continue
            if frequency.has_key(cachekey):
                frequency[cachekey] = frequency[cachekey] + 1
            else:
                frequency[cachekey] = 1
        return frequency

    def setFrequencyCache(self, frequency):
        if len(frequency) == 0:
            return

        for key, value in frequency.items():
            try:
                self.r.incrby(key, value)
                self.r.expire(key, 86400)
            except Exception, e:
                if str(e) == 'value is not an integer or out of range':
                    self.r.set(key, value)
                    self.r.expire(key, 86400)
                else:
                    print str(e)


# if __name__ == '__main__':
#     frequency = [
#         {"orig_id":233,"dsp_id":"Housead_DSP","imei":"50509907-7383-45B6-83FA-85603661D82D"},
#         {"orig_id":231,"dsp_id":"Housead_DSP","imei":"50509907-7383-45B6-83FA-85603661D82D"},
#         {"orig_id":231,"dsp_id":"Housead_DSP","imei":"867348026517826"},
#         {"orig_id":198,"dsp_id":"Housead_DSP","imei":"ffffffff-8059-8a17-66db-c1063de1ea70"}
#         ]
#     frequencycache = FrequencyCache("frequency_cache")
#     frequencycache.run(frequency)

