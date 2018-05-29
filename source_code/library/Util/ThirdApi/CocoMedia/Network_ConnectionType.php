<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * ConnectionType enum embedded in Network message
 */
final class Network_ConnectionType
{
    const CONNECTION_UNKNOWN = 0;
    const CELL_UNKNOWN = 1;
    const CELL_2G = 2;
    const CELL_3G = 3;
    const CELL_4G = 4;
    const CELL_5G = 5;
    const WIFI = 100;
    const ETHERNET = 101;
    const NEW_TYPE = 999;

    /**
     * Returns defined enum values
     *
     * @return int[]
     */
    public function getEnumValues()
    {
        return array(
            'CONNECTION_UNKNOWN' => self::CONNECTION_UNKNOWN,
            'CELL_UNKNOWN' => self::CELL_UNKNOWN,
            'CELL_2G' => self::CELL_2G,
            'CELL_3G' => self::CELL_3G,
            'CELL_4G' => self::CELL_4G,
            'CELL_5G' => self::CELL_5G,
            'WIFI' => self::WIFI,
            'ETHERNET' => self::ETHERNET,
            'NEW_TYPE' => self::NEW_TYPE,
        );
    }
}
}