<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * CoordinateType enum embedded in Gps message
 */
final class Gps_CoordinateType
{
    const WGS84 = 1;
    const GCJ02 = 2;
    const BD09 = 3;

    /**
     * Returns defined enum values
     *
     * @return int[]
     */
    public function getEnumValues()
    {
        return array(
            'WGS84' => self::WGS84,
            'GCJ02' => self::GCJ02,
            'BD09' => self::BD09,
        );
    }
}
}