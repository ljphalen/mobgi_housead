<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * MaterialMeta message
 */
class MaterialMeta extends \ProtobufMessage
{
    /* Field index constants */
    const CREATIVE_TYPE = 1;
    const INTERACTION_TYPE = 2;
    const WIN_NOTICE_URL = 3;
    const CLICK_URL = 4;
    const TITLE = 5;
    const BRAND_NAME = 16;
    const DESCRIPTION = 6;
    const ICON_SRC = 7;
    const IMAGE_SRC = 8;
    const APP_PACKAGE = 9;
    const APP_SIZE = 10;
    const VIDEO_URL = 11;
    const VIDEO_DURATION = 12;
    const META_INDEX = 13;
    const MATERIAL_WIDTH = 14;
    const MATERIAL_HEIGHT = 15;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::CREATIVE_TYPE => array(
            'name' => 'creative_type',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::INTERACTION_TYPE => array(
            'name' => 'interaction_type',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::WIN_NOTICE_URL => array(
            'name' => 'win_notice_url',
            'repeated' => true,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::CLICK_URL => array(
            'name' => 'click_url',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::TITLE => array(
            'name' => 'title',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::BRAND_NAME => array(
            'name' => 'brand_name',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::DESCRIPTION => array(
            'name' => 'description',
            'repeated' => true,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::ICON_SRC => array(
            'name' => 'icon_src',
            'repeated' => true,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::IMAGE_SRC => array(
            'name' => 'image_src',
            'repeated' => true,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::APP_PACKAGE => array(
            'name' => 'app_package',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::APP_SIZE => array(
            'name' => 'app_size',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::VIDEO_URL => array(
            'name' => 'video_url',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::VIDEO_DURATION => array(
            'name' => 'video_duration',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::META_INDEX => array(
            'name' => 'meta_index',
            'required' => false,
            'type' => '\CocoMedia\MetaIndex'
        ),
        self::MATERIAL_WIDTH => array(
            'name' => 'material_width',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::MATERIAL_HEIGHT => array(
            'name' => 'material_height',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
    );

    /**
     * Constructs new message container and clears its internal state
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::CREATIVE_TYPE] = null;
        $this->values[self::INTERACTION_TYPE] = null;
        $this->values[self::WIN_NOTICE_URL] = array();
        $this->values[self::CLICK_URL] = null;
        $this->values[self::TITLE] = null;
        $this->values[self::BRAND_NAME] = null;
        $this->values[self::DESCRIPTION] = array();
        $this->values[self::ICON_SRC] = array();
        $this->values[self::IMAGE_SRC] = array();
        $this->values[self::APP_PACKAGE] = null;
        $this->values[self::APP_SIZE] = null;
        $this->values[self::VIDEO_URL] = null;
        $this->values[self::VIDEO_DURATION] = null;
        $this->values[self::META_INDEX] = null;
        $this->values[self::MATERIAL_WIDTH] = null;
        $this->values[self::MATERIAL_HEIGHT] = null;
    }

    /**
     * Returns field descriptors
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    /**
     * Sets value of 'creative_type' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setCreativeType($value)
    {
        return $this->set(self::CREATIVE_TYPE, $value);
    }

    /**
     * Returns value of 'creative_type' property
     *
     * @return integer
     */
    public function getCreativeType()
    {
        $value = $this->get(self::CREATIVE_TYPE);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'interaction_type' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setInteractionType($value)
    {
        return $this->set(self::INTERACTION_TYPE, $value);
    }

    /**
     * Returns value of 'interaction_type' property
     *
     * @return integer
     */
    public function getInteractionType()
    {
        $value = $this->get(self::INTERACTION_TYPE);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Appends value to 'win_notice_url' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendWinNoticeUrl($value)
    {
        return $this->append(self::WIN_NOTICE_URL, $value);
    }

    /**
     * Clears 'win_notice_url' list
     *
     * @return null
     */
    public function clearWinNoticeUrl()
    {
        return $this->clear(self::WIN_NOTICE_URL);
    }

    /**
     * Returns 'win_notice_url' list
     *
     * @return string[]
     */
    public function getWinNoticeUrl()
    {
        return $this->get(self::WIN_NOTICE_URL);
    }

    /**
     * Returns 'win_notice_url' iterator
     *
     * @return \ArrayIterator
     */
    public function getWinNoticeUrlIterator()
    {
        return new \ArrayIterator($this->get(self::WIN_NOTICE_URL));
    }

    /**
     * Returns element from 'win_notice_url' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getWinNoticeUrlAt($offset)
    {
        return $this->get(self::WIN_NOTICE_URL, $offset);
    }

    /**
     * Returns count of 'win_notice_url' list
     *
     * @return int
     */
    public function getWinNoticeUrlCount()
    {
        return $this->count(self::WIN_NOTICE_URL);
    }

    /**
     * Sets value of 'click_url' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setClickUrl($value)
    {
        return $this->set(self::CLICK_URL, $value);
    }

    /**
     * Returns value of 'click_url' property
     *
     * @return string
     */
    public function getClickUrl()
    {
        $value = $this->get(self::CLICK_URL);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'title' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setTitle($value)
    {
        return $this->set(self::TITLE, $value);
    }

    /**
     * Returns value of 'title' property
     *
     * @return string
     */
    public function getTitle()
    {
        $value = $this->get(self::TITLE);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'brand_name' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setBrandName($value)
    {
        return $this->set(self::BRAND_NAME, $value);
    }

    /**
     * Returns value of 'brand_name' property
     *
     * @return string
     */
    public function getBrandName()
    {
        $value = $this->get(self::BRAND_NAME);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Appends value to 'description' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendDescription($value)
    {
        return $this->append(self::DESCRIPTION, $value);
    }

    /**
     * Clears 'description' list
     *
     * @return null
     */
    public function clearDescription()
    {
        return $this->clear(self::DESCRIPTION);
    }

    /**
     * Returns 'description' list
     *
     * @return string[]
     */
    public function getDescription()
    {
        return $this->get(self::DESCRIPTION);
    }

    /**
     * Returns 'description' iterator
     *
     * @return \ArrayIterator
     */
    public function getDescriptionIterator()
    {
        return new \ArrayIterator($this->get(self::DESCRIPTION));
    }

    /**
     * Returns element from 'description' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getDescriptionAt($offset)
    {
        return $this->get(self::DESCRIPTION, $offset);
    }

    /**
     * Returns count of 'description' list
     *
     * @return int
     */
    public function getDescriptionCount()
    {
        return $this->count(self::DESCRIPTION);
    }

    /**
     * Appends value to 'icon_src' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendIconSrc($value)
    {
        return $this->append(self::ICON_SRC, $value);
    }

    /**
     * Clears 'icon_src' list
     *
     * @return null
     */
    public function clearIconSrc()
    {
        return $this->clear(self::ICON_SRC);
    }

    /**
     * Returns 'icon_src' list
     *
     * @return string[]
     */
    public function getIconSrc()
    {
        return $this->get(self::ICON_SRC);
    }

    /**
     * Returns 'icon_src' iterator
     *
     * @return \ArrayIterator
     */
    public function getIconSrcIterator()
    {
        return new \ArrayIterator($this->get(self::ICON_SRC));
    }

    /**
     * Returns element from 'icon_src' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getIconSrcAt($offset)
    {
        return $this->get(self::ICON_SRC, $offset);
    }

    /**
     * Returns count of 'icon_src' list
     *
     * @return int
     */
    public function getIconSrcCount()
    {
        return $this->count(self::ICON_SRC);
    }

    /**
     * Appends value to 'image_src' list
     *
     * @param string $value Value to append
     *
     * @return null
     */
    public function appendImageSrc($value)
    {
        return $this->append(self::IMAGE_SRC, $value);
    }

    /**
     * Clears 'image_src' list
     *
     * @return null
     */
    public function clearImageSrc()
    {
        return $this->clear(self::IMAGE_SRC);
    }

    /**
     * Returns 'image_src' list
     *
     * @return string[]
     */
    public function getImageSrc()
    {
        return $this->get(self::IMAGE_SRC);
    }

    /**
     * Returns 'image_src' iterator
     *
     * @return \ArrayIterator
     */
    public function getImageSrcIterator()
    {
        return new \ArrayIterator($this->get(self::IMAGE_SRC));
    }

    /**
     * Returns element from 'image_src' list at given offset
     *
     * @param int $offset Position in list
     *
     * @return string
     */
    public function getImageSrcAt($offset)
    {
        return $this->get(self::IMAGE_SRC, $offset);
    }

    /**
     * Returns count of 'image_src' list
     *
     * @return int
     */
    public function getImageSrcCount()
    {
        return $this->count(self::IMAGE_SRC);
    }

    /**
     * Sets value of 'app_package' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setAppPackage($value)
    {
        return $this->set(self::APP_PACKAGE, $value);
    }

    /**
     * Returns value of 'app_package' property
     *
     * @return string
     */
    public function getAppPackage()
    {
        $value = $this->get(self::APP_PACKAGE);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'app_size' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setAppSize($value)
    {
        return $this->set(self::APP_SIZE, $value);
    }

    /**
     * Returns value of 'app_size' property
     *
     * @return integer
     */
    public function getAppSize()
    {
        $value = $this->get(self::APP_SIZE);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'video_url' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setVideoUrl($value)
    {
        return $this->set(self::VIDEO_URL, $value);
    }

    /**
     * Returns value of 'video_url' property
     *
     * @return string
     */
    public function getVideoUrl()
    {
        $value = $this->get(self::VIDEO_URL);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'video_duration' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setVideoDuration($value)
    {
        return $this->set(self::VIDEO_DURATION, $value);
    }

    /**
     * Returns value of 'video_duration' property
     *
     * @return integer
     */
    public function getVideoDuration()
    {
        $value = $this->get(self::VIDEO_DURATION);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'meta_index' property
     *
     * @param \CocoMedia\MetaIndex $value Property value
     *
     * @return null
     */
    public function setMetaIndex(\CocoMedia\MetaIndex $value=null)
    {
        return $this->set(self::META_INDEX, $value);
    }

    /**
     * Returns value of 'meta_index' property
     *
     * @return \CocoMedia\MetaIndex
     */
    public function getMetaIndex()
    {
        return $this->get(self::META_INDEX);
    }

    /**
     * Sets value of 'material_width' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setMaterialWidth($value)
    {
        return $this->set(self::MATERIAL_WIDTH, $value);
    }

    /**
     * Returns value of 'material_width' property
     *
     * @return integer
     */
    public function getMaterialWidth()
    {
        $value = $this->get(self::MATERIAL_WIDTH);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'material_height' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setMaterialHeight($value)
    {
        return $this->set(self::MATERIAL_HEIGHT, $value);
    }

    /**
     * Returns value of 'material_height' property
     *
     * @return integer
     */
    public function getMaterialHeight()
    {
        $value = $this->get(self::MATERIAL_HEIGHT);
        return $value === null ? (integer)$value : $value;
    }
}
}