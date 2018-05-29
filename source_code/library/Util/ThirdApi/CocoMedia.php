<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

class Util_ThirdApi_CocoMedia {

    private $mGlobalConfig = array();
    const COCOMEDIA_URL = 'http://api.ssp.ayang.com/api/def';
    const COCOMEDIA_TIMEOUT = 2;
    const COCOMEDIA_ADUNITID = 30;
    const COCOMEDIA_APP_MAP = array(
        'D76DCF49549589E9CE9B' => array(
            'appkey' => 'f49r6y3a',
            'blockid' => 'ckaqem2e'
        ),
        '3FBCDF359B5C9CC97B46' => array(
            'appkey' => 'xq76gjkc',
            'blockid' => 'kdh0jgsa'
        ),
        '8E69498B356D95CCB579' => array(
            'appkey' => 'ijqdeyeh',
            'blockid' => 'fjhv0bfy'
        ),
        'e19081b4527963d70c7a' => array(
            'appkey' => 'fg37ywf8',
            'blockid' => 'qqhbz2om'
        ),
        '0f881ba4e517c6c28d88' => array(
            'appkey' => 'j5pbvomq',
            'blockid' => 'z6oivsp4'
        )
    );
    
    

    public function __construct(){
        Yaf_loader::import("Util/ThirdApi/CocoMedia/Version.php");
        Yaf_loader::import("Util/ThirdApi/CocoMedia/App.php");
        Yaf_loader::import("Util/ThirdApi/CocoMedia/Tracking.php");
        Yaf_loader::import("Util/ThirdApi/CocoMedia/MaterialMeta.php");
        Yaf_loader::import("Util/ThirdApi/CocoMedia/MetaIndex.php");
        Yaf_loader::import("Util/ThirdApi/CocoMedia/AdSlot.php");
        Yaf_loader::import("Util/ThirdApi/CocoMedia/Ad.php");
        Yaf_loader::import("Util/ThirdApi/CocoMedia/Size.php");
        Yaf_loader::import("Util/ThirdApi/CocoMedia/Device.php");
        Yaf_loader::import("Util/ThirdApi/CocoMedia/UdId.php");
        Yaf_loader::import("Util/ThirdApi/CocoMedia/Network.php");
        Yaf_loader::import("Util/ThirdApi/CocoMedia/Gps.php");
        Yaf_loader::import("Util/ThirdApi/CocoMedia/MobadsRequest.php");
        Yaf_loader::import("Util/ThirdApi/CocoMedia/MobadsResponse.php");
    }

    public function getAdList($requestId,$sp,$appKey,$mGlobalConfig){
        $this->mGlobalConfig = $mGlobalConfig;
        // 客户端应用key映射椰子key
        $AppMap = self::COCOMEDIA_APP_MAP;
        if (!isset($AppMap[$appKey])){
            return array(
                'success' => false,
                'msg' => 'can not find this app',
                'data' => ''
            );
        }
        $appId = $AppMap[$appKey]['appkey'];
        $adslotId = $AppMap[$appKey]['blockid'];
        // 应用参数
        $app = $this->setApp($appId,array(3,4,0));
        // 广告位参数
//        if($sp['screenDirection'] == '1'){
//            $adslotId = 'fjhv0bfy'; // 广告位 id 横屏
//        }else{
//            $adslotId = 'fjhv0bfy'; // 广告位 id 竖屏
//        }
        $adslot = $this->setAdSlot($adslotId,8,array(0,0),2);
        // 设备唯一识别码
        if($sp['platform'] == '1'){
            $udid = $this->setUdId_Android($sp['uuid'],'','');
        }else{
            $udid = $this->setUdId_Ios($sp['uuid'],'');
        }
        // 设备参数
        $systemVersion = explode(".",$sp['systemVertion']);
        $device = $this->setDevice($sp['platform'],$systemVersion,$sp['brand'],$sp['model'],array(0,0),$udid);
        // 移动网络参数
        $network = $this->setNetwork($sp['netType'],$sp['operator']);

        $request = new \CocoMedia\MobadsRequest();
        $apiVersion = $this->setVersion(array(5,3,0)); // API 接口版本号
        $request->setRequestId((string)$requestId);
        $request->setApiVersion($apiVersion);
        $request->setApp($app);
        $request->setAdslot($adslot);
        $request->setDevice($device);
        $request->setNetwork($network);
        $request->getIsDebug(true);
        $data = $request->SerializeToString();
        return $this->getParseData($data);
    }

    public function getParseData($data){

        $url = self::COCOMEDIA_URL;
        $timeout = self::COCOMEDIA_TIMEOUT;
        $string = $this->getCurlData($url,$data,$timeout);
        $response = new \CocoMedia\MobadsResponse();
        $adUnitId = Util_Environment::isOnline()?self::COCOMEDIA_ADUNITID:41; //广告单元ID（写死）
        $res = $response->parseMobadsString($string,$adUnitId,$this->mGlobalConfig);
        if(!$res['success']){
            return $res;
        }
        foreach($res['data']['list'] as $key => $val){
            // 将椰子的上报链接入redis缓存
            $CocoArr = array();
            $CocoArr['clickmonurl'] = $val['clickmonurl']; //点击上报地址
            $CocoArr['showmonurl'] = $val['showmonurl']; //展示上报地址

            $rediskey = Util_CacheKey::THIRD_API_REQUEST_URL.$val['requestId'].'_'.$val['originalityId'];//请求id + 创意ID
            $cache = Cache_Factory::getCache();
            $cache->set($rediskey, $CocoArr, Common_Service_Const::ONE_DAY_FOR_SECONDS);
            unset($res['data']['list'][$key]['clickmonurl']);
            unset($res['data']['list'][$key]['showmonurl']);
        }
        return $res;
    }

    private function setApp($appId,$appVersion){
        $app = new \CocoMedia\App();
        $app->setAppId($appId); // 应用 ID
        $version = $this->setVersion($appVersion); // 应用版本
        $app->setAppVersion($version);
        return $app;
    }

    private function setAdSlot($adslotId,$adslotType,$adslotSize,$adsNum) {
        $adslot = new \CocoMedia\AdSlot();
        $adslot->setAdslotId($adslotId); // 广告位 id 竖屏
        $adslot->setAdslotType($adslotType); // 广告位类型
        $size = $this->setSize($adslotSize); // 广告位尺寸
        $adslot->setAdslotSize($size);
        $adslot->setAds($adsNum);
        return $adslot;
    }

    private function setUdId_Android($imei,$androidId,$mac) {
        $udid = new \CocoMedia\UdId();
        $udid->setImei($imei); // Android 设备唯一标识码
        $udid->setAndroidId($androidId); // Android 设备系统ID
        $udid->setMac($mac); // 设备 WiFi 网卡MAC 地址
        return  $udid;
    }

    private function setUdId_Ios($idfa,$mac) {
        $udid = new \CocoMedia\UdId();
        $udid->setIdfa($idfa); // Ios 设备唯一标识码
        $udid->setMac($mac); // 设备 WiFi 网卡MAC 地址
        return  $udid;
    }

    private function setDevice($osType,$systemVersion,$brand,$model,$deviceSize,$udid) {
        $device = new \CocoMedia\Device();
        $brand = intval($brand);
        $deviceTypeMap = array( 1 => 'Iphone',2 => 'Samsung', 3 => 'HTC',4 => 'NEXUS', 5 => 'xiaomi',6 => 'huawei',7 => 'other', 8 => 'ipad',9 => 'ipod');
        if($brand == 8){
            $deviceType = 2;
        }else{
            $deviceType = 1;
        }
        $device->setDeviceType(intval($deviceType)); // 设备类型 1:手机 2::平板
        $device->setOsType(intval($osType)); // 操作系统 1:Android 2:Ios
        $osVersion = $this->setVersion($systemVersion); // 操作系统版本
        $device->setOsVersion($osVersion);
        $vendor = isset($deviceTypeMap[$brand])?$deviceTypeMap[$brand]:'other';
        $device->setVendor($vendor); // 设备厂商
        $device->setModel($model);  // 设备型号
        $size = $this->setSize($deviceSize); // 设备屏幕尺寸
        $device->setScreenSize($size);
        $device->setUdid($udid);
        return  $device;
    }

    private function setNetwork($netType,$operator) {
        $network = new \CocoMedia\Network();
        $clientIp = Common::getClientIP();
        $network->setIpv4($clientIp); // IPv4 地址
        $ConnectionTypeMap = array(1=>100,2=>4,3=>3,4=>2);
        $netType = intval($netType);
        $connectionType = isset($ConnectionTypeMap[$netType])?$ConnectionTypeMap[$netType]:1;
        $network->setConnectionType($connectionType); // 网络类型 0：无法探测 1：未知 2:2G 3：3G 4:4G 5:5G 100：Wi-Fi
        $OperatorTypeMap = array(1=>3,2=>2,3=>1,4=>99);
        $operator = intval($operator);
        $operatorType = isset($OperatorTypeMap[$operator])?$OperatorTypeMap[$operator]:0;
        $network->setOperatorType($operatorType); // 运营商 ID 0：未知 1：移动 2：电信 3：联通 99：其他
        return  $network;
    }

    private function setVersion($versionArr){
        $major = isset($versionArr[0]) ? intval($versionArr[0]) : 0;
        $minor = isset($versionArr[1]) ? intval($versionArr[1]) : 0;
        $micro = isset($versionArr[2]) ? intval($versionArr[2]) : 0;
        $version = new \CocoMedia\Version();
        $version->setMajor($major);
        $version->setMinor($minor);
        $version->setMicro($micro);
        return $version;
    }

    private function setSize($sizeArr){
        $width = isset($sizeArr[0]) ? intval($sizeArr[0]) : 0;
        $height = isset($sizeArr[1]) ? intval($sizeArr[1]) : 0;
        $size = new \CocoMedia\Size();
        $size->setWidth($width);
        $size->setHeight($height);
        return $size;
    }

    private function getCurlData($url,$data,$timeout){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $result = curl_exec($ch);
        return $result;
    }
}