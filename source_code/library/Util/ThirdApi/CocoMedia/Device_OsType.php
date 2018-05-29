<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * OsType enum embedded in Device message
 */
final class Device_OsType
{
    const ANDROID = 1;
    const IOS = 2;

    /**
     * Returns defined enum values
     *
     * @return int[]
     */
    public function getEnumValues()
    {
        return array(
            'ANDROID' => self::ANDROID,
            'IOS' => self::IOS,
        );
    }
}
}