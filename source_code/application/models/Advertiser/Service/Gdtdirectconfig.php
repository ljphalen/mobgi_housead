<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-10-18 10:10:38
 * $Id: Gdtdirectconfig.php 62100 2016-10-18 10:10:38Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_GdtdirectconfigModel{
    
    //广点通token设置的时间
//    const tokenCacheTime = 1100;　//product 1200秒
    const tokenCacheTime = 600; //sandbox　300秒
    const retrytime = 3;
    /**
	 *
	 * 查询一条结果集
	 * @param array $search
	 */
	public static function getBy($search) {
	    return self::_getDao()->getBy($search);
	}
	
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
		$tmp = array();
        if(isset($data['uid'])) $tmp['uid'] = $data['uid'];
        if(isset($data['status'])) $tmp['status'] = $data['status'];
        if(isset($data['advertiser_id'])) $tmp['advertiser_id'] = $data['advertiser_id'];
        if(isset($data['app_id'])) $tmp['app_id'] = $data['app_id'];
        if(isset($data['app_key'])) $tmp['app_key'] = $data['app_key'];
        if(isset($data['plan_id'])) $tmp['plan_id'] = $data['plan_id'];
        if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
		return $tmp;
	}
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function addConfig($data) {
	    if (!is_array($data)) return false;
	    $data['create_time'] = Common::getTime();
	    $data['update_time'] = Common::getTime();
	    $data = self::_cookData($data);
	    return self::_getDao()->insert($data);
	}
    /**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $uid
	 */
	public static function updateConfigBy($data, $params) {
	    if (!is_array($data)) return false;
	    if (!is_array($params)) return false;
	    $data['update_time'] = Common::getTime();
	    $data = self::_cookData($data);
	    return self::_getDao()->updateBy($data, $params);
	}
	
	/**
	 * 清除广点通token的缓存
	 * @param type $advertiser_uid
	 * @return type
	 */
	public static function deleteGdtTokenCache($advertiser_uid){
	    $rediskey=  Util_CacheKey::GDT_DIRECT_TOKEN.'_'.$advertiser_uid;
	    $cache = self::getCache();
	    return $cache->delete($rediskey);
	}
	
	
    
    /**
     * 获取是否开启广点通直投状态
     * @return boolean
     */
    public static function isGdtDirect($advertiser_uid){
        $gdtconfig = self::getBy(array('uid'=>$advertiser_uid));
        if(empty($gdtconfig)){
            return false;
        }else if($gdtconfig['status'] == 'on'){
            return $gdtconfig;
        }
        return false;
    }
    

	
    /**
     * 获取广点通直投token
     * @return boolean
     */
    public static function getGdtDirectToken($advertiser_uid){
        $rediskey=  Util_CacheKey::GDT_DIRECT_TOKEN.'_'.$advertiser_uid;
        $cache = self::getCache();
        $redisvalue = $cache->get($rediskey);
        if($redisvalue === false){
            return self::getGdtDirectTokenDb($advertiser_uid);
        }else{
            return $redisvalue;
        }
    }
    
    /**
     * 从db获取最新的cache
     * @param type $advertiser_uid
     * @return boolean
     */
    public static function getGdtDirectTokenDb($advertiser_uid){
        $rediskey=  Util_CacheKey::GDT_DIRECT_TOKEN.'_'.$advertiser_uid;
        $cache = self::getCache();
        $gdtconfig = self::getBy(array('uid'=>$advertiser_uid));
        if(empty($gdtconfig)){
            return false;
        }else if($gdtconfig['status'] == 'off'){
            return false;
        }
        $advertiser_id = $gdtconfig['advertiser_id'];
        $app_id = $gdtconfig['app_id'];
        $app_key = $gdtconfig['app_key'];
        $time_stamp = time();
        $sign = sha1($app_id.$app_key.$time_stamp);
        $token = base64_encode($advertiser_id . ',' . $app_id . ',' . $time_stamp. ',' . $sign);
        $cache->set($rediskey, $token, self::tokenCacheTime);
        return $token;
    }
    
    /**
     * 远程调用广点通直投ＡＰＩ
     * @param type $advertiser_uid
     * @param type $resource_name
     * @param type $resource_action
     * @param type $data
     * @return boolean
     */
    public static function curl($advertiser_uid, $resource_name, $resource_action, $data, $method=''){
        $gdtDirectConfig =  Common::getConfig('gdtdirectconfig');
        if(!in_array($resource_name, $gdtDirectConfig['RESOURCE_NAME'])){
            return false;
        }
        if(!in_array($resource_action, $gdtDirectConfig['RESOURCE_ACTION'])){
            return false;
        }
        if(empty($method)){
            $method = $gdtDirectConfig['ACTION_METHOD'][$resource_action]?$gdtDirectConfig['ACTION_METHOD'][$resource_action]:'post';
        }
        $token = self::getGdtDirectToken($advertiser_uid);
        $url = $gdtDirectConfig['GDT_DIRECT_URL']. $gdtDirectConfig["API_VERSION"]."/". $resource_name. "/". $resource_action;
//        $url = 'http://test.com/housead/curl.php';
        $curl = new Util_Http_Curl($url);
//        var_dump($data);die;
        $curl->setHeader('Bearer '.$token, "Authorization");
        //上传流媒体请求头中需声明 Content-Type 为 multipart/form-data
        if($resource_name=='media' && $resource_action=='create'){
            $curl->setHeader("multipart/form-data", "Content-Type");
//            $boundary = '---------------------------7d4a6d158c9cdDdfa21er23dd3dsa2xa';
//            $curl->setHeader("multipart/form-data;boundary=".$boundary, "Content-Type");
//            $pre = '--';
//            $newdata = '';
//            foreach ($data as $key=>$item){
//                $tmp = '';
//                if($key =='media_file'){
//                    $tmp.=$pre.$boundary."\r\n".'Content-Disposition: form-data; filename="'.$key.'"'."\r\n\r\n".$item."\r\n";
//                    $tmp.='Content-Type: video/mpeg4'."\r\n\r\n";
//                    $tmp.=file_get_contents($item);
//                }else{
//                    $tmp.=$pre.$boundary."\r\n".'Content-Disposition: form-data; name="'.$key.'"'."\r\n\r\n".$item."\r\n";
//                }
//                $newdata.=$tmp;
//            }
//            $newdata.=$pre.$boundary.$pre;
//            $data = $newdata;
        }else{
//            $curl->setHeader("application/x-www-form-urlencoded", "Content-Type");
        }
//        var_dump($data);die;
        $curl->setData($data);
        $result = $curl->send($method);
//        var_dump($result);die;
        $result_arr = json_decode($result, TRUE);
        //30102  expired token  token 已过期，重新生成token.
        if($result_arr['code']== 30102){
            $token = self::getGdtDirectTokenDb($advertiser_uid);
            $curlretry = new Util_Http_Curl($url);
            $curlretry->setHeader('Bearer '.$token, "Authorization");
            $curlretry->setHeader("application/x-www-form-urlencoded", "Content-Type");
            $curlretry->setData($data);
            $result = $curlretry->send($method);
            $result_arr = json_decode($result, TRUE);
        }

        //系统错误，重试至多3次
        if(in_array($result_arr['code'], array(30000, 30001))){
            $i = 0;
            while($i < self::retrytime){
                $i++;
                $retry_result = $curl->send($method);
                $result_arr = json_decode($retry_result, TRUE);
                if(in_array($result_arr['code'], array(30000, 30001))){
                    continue;
                }else{
                    break;
                }
            }
        }
        return $result_arr;
    }
    
    public static function normal_curl($advertiser_uid, $resource_name, $resource_action, $data, $method=''){
        $gdtDirectConfig =  Common::getConfig('Gdtdirectconfig');
        if(!in_array($resource_name, $gdtDirectConfig['RESOURCE_NAME'])){
            return false;
        }
        if(!in_array($resource_action, $gdtDirectConfig['RESOURCE_ACTION'])){
            return false;
        }
        if(empty($method)){
            $method = $gdtDirectConfig['ACTION_METHOD'][$resource_action]?$gdtDirectConfig['ACTION_METHOD'][$resource_action]:'post';
        }
        $token = self::getGdtDirectToken($advertiser_uid);
        $url = $gdtDirectConfig['GDT_DIRECT_URL']. $gdtDirectConfig["API_VERSION"]."/". $resource_name. "/". $resource_action;
        
        $boundary = '---------------------------7d4a6d158c9cdDdfa21er23dd3dsa2xa';
        $pre = '--';
        $newdata = '';
        foreach ($data as $key=>$item){
            $tmp = '';
            if($key =='media_file'){
                $item= realpath($item);
                $tmp.=$pre.$boundary."\r\n".'Content-Disposition: form-data; name="'.$key.'"; filename="'.$item.'";'."\r\n";
                $tmp.='Content-Type: video/mp4'."\r\n\r\n";
                $tmp.=file_get_contents($item);
                $tmp.="\r\n". $pre.$boundary.$pre."\r\n\r\n";
            }else{
                $tmp.=$pre.$boundary."\r\n".'Content-Disposition: form-data; name="'.$key.'"'."\r\n\r\n".$item."\r\n";
            }
            $newdata.=$tmp;
        }
        $data = $newdata;
         $header = array(
            'Authorization: Bearer '.$token,
            "Content-Type: multipart/form-data;boundary=".$boundary,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        $result_arr = json_decode($response, TRUE);
        //30102  expired token  token 已过期，重新生成token.
        if($result_arr['code']== 30102){
            $token = self::getGdtDirectTokenDb($advertiser_uid);
            $header = array(
                'Authorization: Bearer '.$token,
                "Content-Type: multipart/form-data;boundary=".$boundary,
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            $result_arr = json_decode($result, TRUE);
        }
        
        //系统错误，重试至多3次
        if(in_array($result_arr['code'], array(30000, 30001))){
            $i = 0;
            while($i < self::retrytime){
                $i++;
                $retry_result = curl_exec($ch);
                $result_arr = json_decode($retry_result, TRUE);
                if(in_array($result_arr['code'], array(30000, 30001))){
                    continue;
                }else{
                    break;
                }
            }
        }
        return $result_arr;
    }
    
    /**
     * 自定义使用cache
     * @return type
     */
    public static function  getCache(){
       $cache = Cache_Factory::getCache(Cache_Factory::ID_REMOTE_REDIS);
       return $cache;  
    }
       
    /**
	 * 
	 * @return Admin_Dao_UserModel
	 */
	private static function _getDao() {
		return Common::getDao("Advertiser_Dao_GdtdirectconfigModel");
	}
    
}

