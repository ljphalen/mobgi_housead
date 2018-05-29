<?php
if (!defined('BASE_PATH')) exit('Access Denied!');


class Stat_Service_OriginalDataModel {
    const EVENT_TYPE_VIEW = 5;
    const EVENT_TYPE_CLICK = 6;


    public static function needCharge($type) {
    return in_array(intval($type), [self::EVENT_TYPE_VIEW, self::EVENT_TYPE_CLICK]);
}

}
