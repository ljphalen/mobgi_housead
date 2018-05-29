<?php

if (!defined('BASE_PATH'))
    exit('Access Denied!');

/**
 * CheckInfoStatus助手类
 * 负责验证关于信息上线的状态判断
 */
class Util_CheckInfoStatus {

    const TYPE_GAME = 'game';
    const TYPE_NEW = 'new';
    const TYPE_HD = 'hd';
    const TYPE_GIFT = 'gift';
    const TYPE_CATEGORY = 'category';
    const TYPE_SUBJECT = 'subject';

    /**
     * 提交信息检查
     * 
     * @param string $type 判断类型
     * @param int $id   判断的内容ID
     * @param array $inputTime  提交的开始与结束时间,为空不检查输入内容(eg. array('opentime' => '', 'endtime' => '') )
     * @param boolean $needInfo  是否需要返回信息内容
     * @return array/boolean
     */
    public static function check($type, $id, $inputTime = array(), $needInfo = FALSE) {
        switch ($type) {
            case self::TYPE_GAME:
                $checkInfo = Resource_Service_Games::getResourceByGames($id);
                break;
            case self::TYPE_NEW:
                $checkInfo = Client_Service_News::getNews($id);
                break;
            case self::TYPE_HD:
                $checkInfo = Client_Service_Hd::getHd($id);
                break;
            case self::TYPE_GIFT:
                $checkInfo = Client_Service_Gift::getGift($id);
                break;
            case self::TYPE_CATEGORY:
                $checkInfo = Resource_Service_Attribute::getBy(array('id' => $id, 'at_type' => 1));
                break;
            case self::TYPE_SUBJECT:
                $checkInfo = Client_Service_Subject::getSubject($id);
                break;
        }
        if (!$checkInfo) {
            return false;
        }
        $checkStatus = self::checkStatus($checkInfo, $type);
        if (!$checkStatus) {
            return false;
        }
        $checkTime = self::checkTime($checkInfo, $type, $inputTime);
        if (!$checkTime) {
            return false;
        }
        if ($needInfo) {
            return $checkInfo;
        } else {
            return true;
        }
    }

    private static function checkStatus($checkInfo, $type) {
        $checkFelid = self::felidConfig($type);
        if (!$checkFelid['status']) {
            return true;
        }
        if ($checkInfo[$checkFelid['status']] < 1) {
            return false;
        }
        return true;
    }

    private static function checkTime($checkInfo, $type, $inputTime = array()) {
        $checkFelid = self::felidConfig($type);
        if (!$checkFelid['endtime'] && !$checkFelid['opentime']) {
            return true;
        }
        if (!$inputTime && $checkFelid['endtime'] && $checkInfo[$checkFelid['endtime']] < Common::getTime()) {
            return false;
        }
        if ($inputTime['opentime'] && $checkFelid['opentime'] && !$checkFelid['endtime']) {
            if ($checkInfo[$checkFelid['opentime']] >= $inputTime['opentime']) {
                return false;
            }
        }
        if ($inputTime && $checkFelid['endtime'] && $checkFelid['opentime']) {
            if ($inputTime['endtime'] <= $inputTime['opentime']) {
                return false;
            }
            if ($inputTime['opentime'] <= $checkInfo[$checkFelid['opentime']] || $inputTime['endtime'] >= $checkInfo[$checkFelid['endtime']]) {
                return false;
            }
        }
        return true;
    }

    private static function felidConfig($type) {
        switch ($type) {
            case self::TYPE_GAME:
                $felid = array('status' => 'status', 'endtime' => '', 'opentime' => '');
                break;
            case self::TYPE_NEW:
                $felid = array('status' => 'status', 'endtime' => '', 'opentime' => 'create_time');
                break;
            case self::TYPE_HD:
                $felid = array('status' => 'status', 'endtime' => 'end_time', 'opentime' => 'start_time');
                break;
            case self::TYPE_GIFT:
                $felid = array('status' => 'status', 'endtime' => 'effect_end_time', 'opentime' => 'effect_start_time');
                break;
            case self::TYPE_CATEGORY:
                $felid = array('status' => '', 'endtime' => '', 'opentime' => '');
                break;
            case self::TYPE_SUBJECT:
                $felid = array('status' => 'status', 'endtime' => 'end_time', 'opentime' => 'start_time');
                break;
        }
        return $felid;
    }

}
