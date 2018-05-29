<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * MobadsResponse message
 */
class MobadsResponse extends \ProtobufMessage
{
    /* Field index constants */
    const REQUEST_ID = 1;
    const ERROR_CODE = 2;
    const ADS = 3;
    const EXPIRATION_TIME = 4;
    const REQUEST_TIME_S = 15;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::REQUEST_ID => array(
            'name' => 'request_id',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::ERROR_CODE => array(
            'name' => 'error_code',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::ADS => array(
            'name' => 'ads',
            'repeated' => true,
            'type' => '\CocoMedia\Ad'
        ),
        self::EXPIRATION_TIME => array(
            'name' => 'expiration_time',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::REQUEST_TIME_S => array(
            'name' => 'request_time_s',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_DOUBLE,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::REQUEST_ID] = null;
        $this->values[self::ERROR_CODE] = null;
        $this->values[self::ADS] = array();
        $this->values[self::EXPIRATION_TIME] = null;
        $this->values[self::REQUEST_TIME_S] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'request_id' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setRequestId($value)
    {
        return $this->set(self::REQUEST_ID, $value);
    }

    /**
     * Returns value of 'request_id' property
     *
     * @return string
     */
    public function getRequestId()
    {
        $value = $this->get(self::REQUEST_ID);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'error_code' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setErrorCode($value)
    {
        return $this->set(self::ERROR_CODE, $value);
    }

    /**
     * Returns value of 'error_code' property
     *
     * @return integer
     */
    public function getErrorCode()
    {
        $value = $this->get(self::ERROR_CODE);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Appends value to 'ads' list
     *
     * @param \CocoMedia\Ad $value Value to append
     *
     * @return null
     */
    public function appendAds(\CocoMedia\Ad $value)
    {
        return $this->append(self::ADS, $value);
    }

    /**
     * Clears 'ads' list
     *
     * @return null
     */
    public function clearAds()
    {
        return $this->clear(self::ADS);
    }

    /**
     * Returns 'ads' list
     *
     * @return \CocoMedia\Ad[]
     */
    public function getAds()
    {
        return $this->get(self::ADS);
    }

    /**
     * Returns 'ads' iterator
     *
     * @return \ArrayIterator
     */
    public function getAdsIterator()
    {
        return new \ArrayIterator($this->get(self::ADS));
    }

    /**
     * Returns element from 'ads' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return \CocoMedia\Ad
     */
    public function getAdsAt($offset)
    {
        return $this->get(self::ADS, $offset);
    }

    /**
     * Returns count of 'ads' list
     *
     * @return int
     */
    public function getAdsCount()
    {
        return $this->count(self::ADS);
    }

    /**
     * Sets value of 'expiration_time' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setExpirationTime($value)
    {
        return $this->set(self::EXPIRATION_TIME, $value);
    }

    /**
     * Returns value of 'expiration_time' property
     *
     * @return integer
     */
    public function getExpirationTime()
    {
        $value = $this->get(self::EXPIRATION_TIME);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'request_time_s' property
     *
     * @param double $value Property value
     *
     * @return null
     */
    public function setRequestTimeS($value)
    {
        return $this->set(self::REQUEST_TIME_S, $value);
    }

    /**
     * Returns value of 'request_time_s' property
     *
     * @return double
     */
    public function getRequestTimeS()
    {
        $value = $this->get(self::REQUEST_TIME_S);
        return $value === null ? (double)$value : $value;
    }

    public function parseMobadsString($string,$adUnitId,$mGlobalConfig)
    {
        $mobadsResponse = new MobadsResponse();
        if ($mobadsResponse->parseFromString($string)) {
            if ($mobadsResponse->getErrorCode()) {
                return array(
                    'success' => false,
                    'msg' => $mobadsResponse->getErrorCode(),
                    'data' => ''
                );
            }
            $Ads = $mobadsResponse->getAds();
            if (!$Ads) {
                return array(
                    'success' => false,
                    'msg' => 'no match ad',
                    'data' => ''
                );
            }
            $requsetId = $mobadsResponse->getRequestId(); //请求id
            if(count($Ads) >= 2){
                $list = array();
                $list[0] = $this->parseMobadsAd($Ads[0],$requsetId,$adUnitId,$mGlobalConfig);
                $list[1] = $this->parseMobadsAd($Ads[1],$requsetId,$adUnitId,$mGlobalConfig);
                if(empty($list[0]) || empty($list[1])){
                    return array(
                        'success' => false,
                        'msg' => 'can not get two interstitial ads',
                        'data' => ''
                    );
                }
                return array(
                    'success' => true,
                    'msg' => 'ok',
                    'data' => array(
                        'list' => $list
                    )
                );
            }else{
                return array(
                    'success' => false,
                    'msg' => 'can not get two ads',
                    'data' => ''
                );
            }
        } else {
            return array(
                'success' => false,
                'msg' => 'parse failed',
                'data' => ''
            );
        }
    }

    public function parseMobadsAd($Ad,$requestId,$adUnitId,$mGlobalConfig){

        $meta = $Ad->getMetaGroup()[0];
        $track = $Ad->getAdTracking();
        if($track) {
            $trackMeta = $track;
        }else{
            $trackMeta = null;
        }
        $data = array();
        $data['requestId'] = $requestId; //请求id
//            $data['request_times'] = $mobadsResponse->getRequestTimeS(); //请求时间
//            $data['expiration_time'] = $mobadsResponse->getExpirationTime(); //过期时间
        $data['adUnitId'] = $adUnitId; //广告单元ID（写死）
        $data['adName'] = $meta->getBrandName(); //广告名称（针对安卓）
        $data['originalityId'] = md5($Ad->getAdKey()); //创意ID
        $data['adId'] = $data['originalityId']; //广告ID（写死）
        $data['adType'] = $meta->getCreativeType(); //广告类型
        if($data['adType'] != '0' && $data['adType'] != '2'){
            return array();
        }
        $data['targetUrl'] = $meta->getClickUrl(); //广告目标
        $data['packageName'] = $meta->getAppPackage(); //包名（针对安卓）
//            $data['htmlUrl'] = ''; //html地址
//            $data['videoUrl'] = $meta->getVideoUrl(); //视频地址
        $data['imgUrl'] = $meta->getImageSrc()[0]; //插页图片地址
        $data['iconUrl'] = $meta->getIconSrc()[0]; //图标地址（针对安卓）
        foreach($data as $key => $val){
            if($val === null){
                $data[$key] = ''; // 过滤null为空字符串
            }
        }
        $jumpTypeMap = array(1 => 2, 2 => 7);
        $interactionType = $meta->getInteractionType(); //交互类型
        $data['jumpType'] = isset($jumpTypeMap[$interactionType])?$jumpTypeMap[$interactionType]:2; //跳转类型
//        $data['muteButton'] = 0; //是否显示静音按钮（后台自己设置）
//        $data['closeButton'] = 0; //是否显示关闭按钮（后台自己设置）
//        $data['downloadButton'] = 0; //是否显示下载按钮（后台自己设置）
//        $data['progressButton'] = 0; //是否显示进度按钮（后台自己设置）
        $data['border'] = isset($mGlobalConfig['border'])?$mGlobalConfig['border']:''; //边框
        $data['closeButtonDelayShow'] = isset($mGlobalConfig['closeButtonDelayShow'])?$mGlobalConfig['closeButtonDelayShow']:0; //关闭按钮延迟展示，0表示立即显示，1表示延迟显示
        $data['closeButtonDelayShowTimes'] = isset($mGlobalConfig['closeButtonDelayShowTimes'])?$mGlobalConfig['closeButtonDelayShowTimes']:0; //关闭按钮延迟展示的时间，以秒为单位；值为0，表示立即展示
        $data['clickmonurl'] = [];
        $data['showmonurl'] = [];
        if(empty($trackMeta)){
            $data['clickmonurl']= [];
        }else{
            foreach($trackMeta as $item){
                $event = $item->getTrackingEvent();
                if($event == 0){
                    $trackMetaUrlTemp = $item->getTrackingUrl();
                    foreach($trackMetaUrlTemp as $itemTrackUrl) {
                        array_push($data['clickmonurl'], $itemTrackUrl);
                    }
                }else if($event == 1){
                    $trackMetaUrlTemp = $item->getTrackingUrl();
                    foreach($trackMetaUrlTemp as $itemTrackUrl) {
                        array_push($data['showmonurl'], $itemTrackUrl);
                    }
                }
            }
        }
        $winNoticeUrl = $meta->getWinNoticeUrl();
        foreach($winNoticeUrl as $itemWinNoticeUrl) {
            array_push($data['showmonurl'], $itemWinNoticeUrl);
        }
        return $data;
    }
}
}