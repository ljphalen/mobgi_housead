<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author huyuke
 *
 */
class Util_TimeConvert {

    const RADIX_DAY = 1;
    const RADIX_HOUR = 2;
    const RADIX_MINUTE = 3;

    const SECOND_OF_DAY = 86400;
    const SECOND_OF_HOUR = 3600;
    const SECOND_OF_MINUTE = 60;

    private static $sRadixTimeFormat = array(
                                        self::RADIX_DAY => 'Y-m-d 00:00:00',
                                        self::RADIX_HOUR => 'Y-m-d H:00:00',
                                        self::RADIX_MINUTE => 'Y-m-d H:i:00',
                                      );

    private static $sTimeRadix = array(
                                        self::RADIX_DAY => self::SECOND_OF_DAY,
                                        self::RADIX_HOUR => self::SECOND_OF_HOUR,
                                        self::RADIX_MINUTE => self::SECOND_OF_MINUTE,
                                      );

    public static function floor($originalTime, $radix) {
        if (!array_key_exists($radix, self::$sRadixTimeFormat)) {
            return -1;
        }

        $format = self::$sRadixTimeFormat[$radix];

        $timeFormat = date($format, $originalTime);

        return strtotime($timeFormat);
    }
    
    public static function ceil($originalTime, $radix) {
        if (!array_key_exists($radix, self::$sTimeRadix)) {
            return -1;
        }
        return self::floor($originalTime, $radix) + self::$sTimeRadix[$radix];
    }

    public static function addHours($hours,$currentTime='') {
        if(! $currentTime) {
            $currentTime=Common::getTime();
        }
        return strtotime("+{$hours} hours", $currentTime);
    }
    
    public static function addDays($days,$currentTime='') {
        if(! $currentTime) {
            $currentTime=Common::getTime();
        }
        return strtotime("+{$days} days", $currentTime);
    }
    
}
