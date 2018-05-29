<?php
if (! defined ( 'BASE_PATH' )) exit ( 'Access Denied!' );

class Adx_Api_V1_BaseController extends Adx_Api_BaseController {
    
    
    public function init() {
        parent::init();
    
    }
    
    /**
     *
     * @param unknown $ip
     * @param unknown $type
     *            1世界区域 0中国区域
     */
    public function getParseAreaCacheDataByIp($ip) {
		if (! $ip) {
			return array ();
		}
		$ipLong = sprintf('%u',ip2long($ip));
		$resource = 'ip_info_'.(($ipLong % 2)+1);
		$cache = Cache_Factory::getCache (Cache_Factory::ID_REMOTE_REDIS,$resource);
		$key = 'ip_' . md5 ( $ip . '_ipinfo' );
		$ipInfo = $cache->get ( $key );
		if ($ipInfo === false) {
			$ipInfo = Util_IpToCityApi::getIpDetailInfo ( $ip );
			if ($ipInfo) {
				$cache->set ( $key, $ipInfo, Util_CacheKey::CACHE_KEY_EXPRIE_ONE_DAY );
			}
		}
		return $ipInfo;
    }
    
    /**
     * 功能:获取兼容新旧版本SDK的跳转类型
     * jumpType: 跳转类型，0表示静默下载(针对安卓)，1表示跳转市场应用(ios为Appstore,安卓为GooglePlay)，2表示跳转系统默认浏览器，3表示跳转自建浏览器，4表示打开列表广告，5表示自定义动作，6表示无动作，7表示通知栏下载(针对安卓），8表示商店内页打开（IOS）。目前仅0,1,2,3,7,8有价值
     * 当客户端版本≤0.1.0时，若配置的jumptype=7，实际下发0；若配置的jumptype=8，实际下发1
     * 当客户端版本＞0.1.0时，按实际配置下发
     *
     * @param type $clientVersion
     *            请求的SDK的版本号
     * @param type $jumpType
     */
    public function parseJumptype($clientVersion, $jumpType) {
        if ($clientVersion && version_compare ( $clientVersion, '0.1.0', '<=' )) {
            $compatible_jumptype_config = array (
                    7 => 0,
                    8 => 1
            ); // key为新版本SDK的下发值,value为旧版本SDK需要的下发值
            if (isset ( $compatible_jumptype_config [$jumpType] )) {
                $jumpType = $compatible_jumptype_config [$jumpType];
            }
        }
        return $jumpType;
    }
    
    public function __destruct() {
        $execTime = intval((microtime(true) - $this->sTime) * 1000);
        $action = $this->getRequest()->getActionName();
        if ($this->isReportToMonitor == 1) {
            $name = 'adx_v1_' . $action . '_' . $this->mAppKey . '_' . Util_ErrorCode::$mReportCodeDesc [$this->mReportCode];
            if ($this->mReportCode == Util_ErrorCode::FITER_CONFIG) {
                $name = 'adx_v1_' . $action . '_' . $this->mAppKey . '_' . $this->mReportData . '_' . Util_ErrorCode::$mReportCodeDesc [$this->mReportCode];
            }
            Common::sendLogAccess(0, 'ads', $name, $this->mReportMsg, $execTime);
        }
    }
}