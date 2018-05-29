<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * CopyRight enum embedded in Video message
 */
final class Video_CopyRight
{
    const CR_NONE = 0;
    const CR_EXIST = 1;
    const CR_UGC = 2;
    const CR_OTHER = 3;

    /**
     * Returns defined enum values
     *
     * @return int[]
     */
    public function getEnumValues()
    {
        return array(
            'CR_NONE' => self::CR_NONE,
            'CR_EXIST' => self::CR_EXIST,
            'CR_UGC' => self::CR_UGC,
            'CR_OTHER' => self::CR_OTHER,
        );
    }
}
}