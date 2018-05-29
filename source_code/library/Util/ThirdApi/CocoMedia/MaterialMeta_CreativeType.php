<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * CreativeType enum embedded in MaterialMeta message
 */
final class MaterialMeta_CreativeType
{
    const NO_TYPE = 0;
    const TEXT = 1;
    const IMAGE = 2;
    const TEXT_ICON = 3;
    const VIDEO = 4;
    const VIDEO_HTML = 9;

    /**
     * Returns defined enum values
     *
     * @return int[]
     */
    public function getEnumValues()
    {
        return array(
            'NO_TYPE' => self::NO_TYPE,
            'TEXT' => self::TEXT,
            'IMAGE' => self::IMAGE,
            'TEXT_ICON' => self::TEXT_ICON,
            'VIDEO' => self::VIDEO,
            'VIDEO_HTML' => self::VIDEO_HTML,
        );
    }
}
}