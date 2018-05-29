#!/usr/bin/env python
# -*- coding:utf-8 -*-
import os

SQL_PATH = "sql"
LOG_PATH = "../log"

# 循环一次休眠时间
SLEEP_SECOND = 1
# 最大错误循环次数
MAX_LOOP_TIMES = 3

if os.path.exists(LOG_PATH) is False:
    LOG_PATH = "../log"

# 汇率
EXCHANGE_RATE = 6.5

AD_TYPE_VIDEO = 1
AD_TYPE_PIC = 2
AD_TYPE_CUSTOM = 3
AD_TYPE_SPLASH = 4
AD_TYPE_NATIVE = 5

INTERGRATION_TYPE = [1, 2, 3, 4, 5]
# 1:视频
# 2:插页
# 3:自定义
# 4:开屏
# 5:原生信息流

PLATFORM = [0, 1]
# 0:安卓
# 1:IOS

# 配置API表
# API_CONFIG_TABLE = "config_import_time"

# 配置新的API表
ADS_TABLE = "config_ads"

# Loopme
LoopmeApiHost = "https://loopme.me/api/v1/reports/apps"
LoopmeApikey = "3ffced843ca4fa04"

# Uniplay 玩转互联SDK
UniplayApiHost = "http://dapi.wanzhuanhulian.com/data/app.php"
UniplayAccount = "ledouads@gmail.com"
UniplayPwd = "Idreamskyads@2017"

# Appnext
AppnextApiHost = "https://selfservice.appnext.com/revenue/api.asmx"
AppnextEmail = "ledouads@gmail.com"
AppnextPassword = "Idreamskyads@2017"
AppnextSecret = "32c457e7-1f2c-11e7-9022-1249e5ec4f79"

# CentrixLink
CentrixLinkApiHost = "https://report.centrixlink.com/stats/v1/apps/reports"
CentrixLinkApikey = "6122df8f41f147f2b0307b50f3fcf195"
CentrixLinkAdType = {
    0: AD_TYPE_VIDEO,
    1: AD_TYPE_SPLASH,
}

# Glispa
GlispaApiHost = "http://101.200.218.137:85/api/Publisher/Details"
GlispaEmail = "ledou@163.com"
GlispaPassword = "ledouads@2017"
GlispaAdType = AD_TYPE_PIC



# oneway
OnewayApiHost = "http://developer.oneway.mobi/report/list"
OnewayType = AD_TYPE_VIDEO  # video

# Dianview
DianviewApiHost = "http://www.dianview.com/stats/transfer-api/index"
DianviewApikey = "na1y_fmz_uvguvrmlqcfc8_lplp7xamaleyu0lpiheknf0l4dojesmyg8ryk8xtw"
DianviewType = {
    "video": AD_TYPE_VIDEO,
    "interstitial": AD_TYPE_PIC,
}  # video

# Yezi
YeziToken = "bbe233033459cc38bfc4d3658fa9e3b9"
YeziApiHost = "http://120.92.9.140:8082/httpDataSyncForJson"
YeziType = {
    # '1': 2, # banner
    '2': AD_TYPE_PIC,  # 插屏
    '4': AD_TYPE_SPLASH,  # 开屏
    '8': AD_TYPE_NATIVE,  # 原生
    '9': AD_TYPE_VIDEO,  # 奖励视频,

}

# Mobvista
MobvistaSkey = "f5686127cc6b71a1238d165a89f05834"
MobvistaSecret = "c8095e0bad66ba259dc29655159547ca"
MobvistaApiHost = "http://oauth2.mobvista.com/m/report/offline_api_report"
MobvistaApiVer = "1.0"
MobvistaType = AD_TYPE_VIDEO  # video

# Supersonic
SupersonicApiHost = "https://platform.ironsrc.com/partners/publisher/mediation/applications/v3/stats"
#SupersonicApiHost = "https://platform.supersonic.com/partners/publisher/mediation/applications/stats"(old)
SupersonicAccessKey = "ledouads@gmail.com"
SupersonicSecretKey = "827d3abc5df0251c5e9a0a97bf4c92e5"
SupersonicType = {
    "Rewarded Video": AD_TYPE_VIDEO,
    "Interstitial": AD_TYPE_PIC,
    "OfferWall": AD_TYPE_PIC
}

# Chartboost
ChartboostApiHost = "https://analytics.chartboost.com/v3/metrics/appcountry"
ChartboostUserId = "576a7a71f6cd4573038ef055"
ChartboostuserSignature = "4bbd2ffc9aa1b39febc24db65489503ab3856cf89f269dcd9f225c0e6d9e4a43"
ChartboostType = {
    "rewarded_video": AD_TYPE_VIDEO,
    "interstitial": AD_TYPE_PIC,
    "video_interstitial": AD_TYPE_PIC
}

# Adcolony
AdcolonyApiHost = "http://clients.adcolony.com/api/v2/publisher_summary"
AdcolonyUserCredentials = "0Ymaby9Tnfs5LFlGeUv"
AdcolonyType = AD_TYPE_VIDEO

# Domob
DomobApiHost = "http://dvx.domob.cn/api/applications"
DomobKey = "NzkyMjZsZWRvdWFkc0BnbWFpbC5jb204NWE3Yjc5YzJkNWMzOWVm"
DomobType = AD_TYPE_VIDEO

# Youmi
YoumiApiHost = "http://api.joyingmobi.com/data/app_traffic.json"
YoumiType = {
    "video": AD_TYPE_VIDEO,
    "spot": AD_TYPE_PIC
}


# Inmobi
InmobiTokenHost = "https://api.inmobi.com/v1.0/generatesession/generate"
InmobiApiHost = "https://api.inmobi.com/v1.1/reporting/publisher.json"
InmobiUserName = "ledouads@gmail.com"
InmobiPassword = "Idreamskyads@2017"
InmobiSecretKey = "534ccd9ea8f742fa9eb4542bb18ba168"

# Unity
UnityHost = "http://gameads-admin.applifier.com"
UnityPath = "/stats/monetization-api"
UnityApikey = "1c12d204f9cbe5feceb9b4ef99c980eed8a38fadbce869d854ad27333a955fcc"
UnityOtherParams = "&fields=revenue,started&splitBy=source&scale=hour"

# 畅思
ChanceHost = "http://dev.cocounion.com/webindex/appReportAPI"
ChanceUserName = 'ledouads@gmail.com'
ChanceKey = 'B01E4ADC4CBCEC33CFA4045C0D982F84'
ChanceMd5Key = 'c19be1508f7eb6632bbdc8b3d16386fa'

# applovin
ApplovinHost = 'https://r.applovin.com/report'
ApplovinApiKey = "gQtqlSIfVLEVKKLW1vUam0QPeXxOwpaaERPzvicO2svMAEuC8MnuNCI1lDPe0-EEQdBJKpiMfbkrrb2-zqsL09"
ApplovinTypeMap = {
    "INTER-MRAID": AD_TYPE_PIC,
    "INTER-PLAY": AD_TYPE_PIC,
    "INTER-VIDEO": AD_TYPE_PIC,
    "INTER-GRAPHIC": AD_TYPE_PIC,
    "INTER-REWARD": AD_TYPE_VIDEO,
    "NATIVE-GRAPHIC": AD_TYPE_NATIVE,
    "NATIVE-VIDEO": AD_TYPE_NATIVE
}

# fyber
FyberHost = "https://api.fyber.com"
FyberPublishId = "112857"
FyberPath = "/publishers/v1/" + FyberPublishId + "/statistics.json"
FyberApi = "e475dd5c668f5df3138ec633695597e2"


# Pingcoo
PingcooHost = "http://report.u.pingcoo.com"
PingcooPath = "/?c=Report"
PingcooEmail = "ledouads@gmail.com"

# Yumi
YumiSecret = "f45d8cd43ebd8546172aeb34b6df7430"
YumiDevId = "yumiabbdc1d0ba29965d"
YumiApiHost = "http://api.yumimobi.com/"
YumiType = {
    # 'banner':2,# 横幅,
    'interstitial': AD_TYPE_PIC,  # 插屏,
    'video': AD_TYPE_VIDEO,  # 视频,
    'startUp': AD_TYPE_SPLASH,  # 启动/开屏,
    'native': AD_TYPE_NATIVE,  # 原生
}

#mobgi
MobgiHost = "http://stat.mobgi.com/Stat/report/api"
MobgiType = {
    'pic': AD_TYPE_PIC,
    'native': AD_TYPE_NATIVE,
    'splash': AD_TYPE_SPLASH,
    'video':AD_TYPE_VIDEO,
    'custome':AD_TYPE_CUSTOM
}

#Housead_DSP
Housead_DSPHost = "http://stat.mobgi.com/Stat/report/api"
Housead_DSPType = {
    'pic': AD_TYPE_PIC,
    'native': AD_TYPE_NATIVE,
    'splash': AD_TYPE_SPLASH,
    'video':AD_TYPE_VIDEO,
    'custome':AD_TYPE_CUSTOM
}

# Vungle v2.0
VungleHost = "https://report.api.vungle.com/ext/pub/reports/performance"
VungleKey = "Bearerace296d6c40853ea17857939cbf85f68"

# Innotech
InnotechApiHost = "https://i.prebids.org/api/report/slot"
InnotechUsername = "ledouyouxi"
InnotechPassword = "Idreamskyads@2017"
InnotechAdType = AD_TYPE_PIC


# GDT
GDTAppid = "2019744"
GDTAppkey = "b05cb171f0566fbc8283b632d9ee204b"
GDTAgid = "2019744"
GDTMemberid = "185335956"
GDTApiHost = "https://api.e.qq.com/luna/v2/adnetwork_report/select?auth_type=TokenAuth&token="
GDTType = {
    u'插屏': AD_TYPE_PIC,
    u'原生': AD_TYPE_NATIVE,
    u'开屏': AD_TYPE_SPLASH
}
GDTYSType = {
    u'插屏': AD_TYPE_PIC,
    u'原生': AD_TYPE_VIDEO,
}
#tapjoy
TapJoyApiHost = "https://api.tapjoy.com/reporting_data.json"
TapJoyEmail = "ledouads@gmail.com"
TapJoyApiKey = "99d0471a13e4455593ef29b8cd478f0d"
TapJoyTimeZone = "+8"

#Admob
AdmobApiHost ="http://gapi.mobgi.com/report.php"
AdmobKey = 'admob_sync_data'



#Lmjoy
LmjoyHost ="http://ht.j-mede.com/v100/datasync.aspx"
LmjoyKey ="26ac523d-00e9-408f-822c-d64a4eec1135"
LmjoyAdType = 1


#youlan
YoulanHost = "http://media-report-api.youlanad.com/report/supplier/settlement"
YoulanToken = "0458401b2d6e4486b8ec890f72aee467"
YoulanId = "109"

#yeahmobi
YeahmobiHost = "http://www.cloudmobi.net/cloudmobi-reporting/get-data"
YeahmobiToken = "6d754be3ca6cfef503e6bb35847ac174"

#spider find Data
#OPPO
OppoLoginUrl = 'https://u.oppomobile.com/union/login'
OppoVerifyUrl = 'https://u.oppomobile.com/union/captcha'
OppoGetDataUrl = 'https://u.oppomobile.com/union/static/report/query'
OppoUsername = "1345982369@qq.com"
OppoPassWord = "Idsky2018"

#Adview
AdviewLoginUrl = 'http://www.adview.cn/login'
AdviewVerifyUrl = 'http://www.adview.cn/captcha'
AdviewGetDataUrl = 'http://www.adview.cn/user/income/appInfoReport'
AdviewUsername = "ledouads@gmail.com"
AdviewPassWord = "Idreamskyads@2017"


#Meizu
MeizuGetDataUrl = 'https://ssp.flyme.cn/c/outsspstat/getAppStat'

#Xiaomi
XiaomiGetDataUrl ='https://dev.mi.com/admob/cgi/stat/detail/get'

#UC_Aliyun
AliyunGetDataUrl = 'https://www.yousuode.cn/analysis/XgetData'

#Yumi_Aliyun
AliyunYumiGetDataUrl = 'https://developers.yumimobi.com/index.php/AppList/appDetails'