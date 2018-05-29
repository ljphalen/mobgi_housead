<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * OperatorType enum embedded in Network message
 */
final class Network_OperatorType
{
    const UNKNOWN_OPERATOR = 0;
    const CHINA_MOBILE = 1;
    const CHINA_TELECOM = 2;
    const CHINA_UNICOM = 3;
    const OTHER_OPERATOR = 99;

    /**
     * Returns defined enum values
     *
     * @return int[]
     */
    public function getEnumValues()
    {
        return array(
            'UNKNOWN_OPERATOR' => self::UNKNOWN_OPERATOR,
            'CHINA_MOBILE' => self::CHINA_MOBILE,
            'CHINA_TELECOM' => self::CHINA_TELECOM,
            'CHINA_UNICOM' => self::CHINA_UNICOM,
            'OTHER_OPERATOR' => self::OTHER_OPERATOR,
        );
    }
}
}