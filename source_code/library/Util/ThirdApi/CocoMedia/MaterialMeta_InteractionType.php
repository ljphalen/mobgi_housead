<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * InteractionType enum embedded in MaterialMeta message
 */
final class MaterialMeta_InteractionType
{
    const NO_INTERACTION = 0;
    const SURFING = 1;
    const DOWNLOAD = 2;
    const OPTIONAL = 9;

    /**
     * Returns defined enum values
     *
     * @return int[]
     */
    public function getEnumValues()
    {
        return array(
            'NO_INTERACTION' => self::NO_INTERACTION,
            'SURFING' => self::SURFING,
            'DOWNLOAD' => self::DOWNLOAD,
            'OPTIONAL' => self::OPTIONAL,
        );
    }
}
}