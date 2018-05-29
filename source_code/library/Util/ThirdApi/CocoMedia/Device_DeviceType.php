<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * DeviceType enum embedded in Device message
 */
final class Device_DeviceType
{
    const PHONE = 1;
    const TABLET = 2;

    /**
     * Returns defined enum values
     *
     * @return int[]
     */
    public function getEnumValues()
    {
        return array(
            'PHONE' => self::PHONE,
            'TABLET' => self::TABLET,
        );
    }
}
}