<?php
/**
 * 根据IP返回地区
 *
 * @author Intril
 *
 */
class Util_IpToCityApi {
    private static $_format = 'json';
    private static $_timeout = 100;
    const IP_URL = 'http://ip.lua.uu.cc/';

    /**
     * curl方式 返回IP对应该的国内省份
     *
     * @param $ip string
     * @return string
     */
    public static function getProvince($ip) {
       $ipUrl = Common::getConfig('siteConfig', 'IpUrl') ;
       if (!$ipUrl) {
           throw new Exception('Please defined IP_URL frist', 500);
           return;
       }
        $url = $ipUrl . '?format=' . self::$_format . '&level=2&ip=' . $ip;
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_TIMEOUT_MS, self::$_timeout);
        curl_setopt ( $ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $ret = curl_exec ( $ch );
        curl_close ( $ch );
        $result = json_decode ( $ret, true );
        if (empty($result['ret']) || $result['ret'] != 1) {
            return array ( 'province' => '', 'city' => '' );
        }
        if (isset($result['data']['province']) && $result['data']['province']) {
            $return['province'] = $result['data']['province'];
        } else if (isset($result['data']['region']) && $result['data']['region']) {
            $return['province'] = $result['data']['region'];
        }      
        if(empty($return["province"])){
            if($return["country_code"] == 'HK'){
                $return['province'] = '香港';
            }elseif($return["country_code"] == 'MO'){
                $return['province'] = '澳门';
            }elseif($return["country_code"] == 'TW'){
                $return['province'] = '台湾';
            }else{
                $return['province']='';
            }
        }
        if (isset($result['data']['city'])) {
            $return['city'] = $result['data']['city'];
        }else{
            $return['city'] = '';
        }
        return $return;
    }
    
    /**
     * 获取世界各个国家的编号
     * @param unknown $ip
     * @throws Exception
     */
    public static function getCountry($ip) {
        $ipUrl = Common::getConfig('siteConfig', 'IpUrl') ;
        if (!$ipUrl) {
           throw new Exception('Please defined IP_URL frist', 500);
           return;
        }
        $url = $ipUrl . '?format=' . self::$_format . '&level=2&ip=' . $ip;
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        //curl_setopt ( $ch, CURLOPT_TIMEOUT, self::$_timeout );
        curl_setopt ( $ch, CURLOPT_TIMEOUT_MS, self::$_timeout);
        curl_setopt ( $ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $ret = curl_exec ( $ch );
        curl_close ( $ch );
        $result = json_decode ( $ret, true );
        $countryCode = '60001';
        if (empty($result['ret']) || $result['ret'] != 1) {
            return $countryCode;
        }
        if ($result['data']['country_code']) {
               $countryCode = $result['data']['country_code'];
        }
        return $countryCode;
    }
    
    public static function getIpDetailInfo($ip) {
        $ipUrl = Common::getConfig('siteConfig', 'IpUrl') ;
        if (!$ipUrl) {
           throw new Exception('Please defined IP_URL frist', 500);
           return;
        }
        $url = $ipUrl . '?format=' . self::$_format . '&level=2&ip=' . $ip;
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        //curl_setopt ( $ch, CURLOPT_TIMEOUT, self::$_timeout );
        curl_setopt ( $ch, CURLOPT_TIMEOUT_MS, self::$_timeout);
        curl_setopt ( $ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $ret = curl_exec ( $ch );
        curl_close ( $ch );
        $result = json_decode ( $ret, true );
        if (empty($result['ret']) || $result['ret'] != 1) {
            return false;
        }
        return $result['data'];
    }
    
}