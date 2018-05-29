<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * Video message
 */
class Video extends \ProtobufMessage
{
    /* Field index constants */
    const TITLE = 1;
    const CONTENT_LENGTH = 2;
    const COPYRIGHT = 3;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::TITLE => array(
            'name' => 'title',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::CONTENT_LENGTH => array(
            'name' => 'content_length',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::COPYRIGHT => array(
            'name' => 'copyright',
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
        $this->values[self::TITLE] = null;
        $this->values[self::CONTENT_LENGTH] = null;
        $this->values[self::COPYRIGHT] = null;
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
     * Sets value of 'content_length' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setContentLength($value)
    {
        return $this->set(self::CONTENT_LENGTH, $value);
    }

    /**
     * Returns value of 'content_length' property
     *
     * @return integer
     */
    public function getContentLength()
    {
        $value = $this->get(self::CONTENT_LENGTH);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'copyright' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setCopyright($value)
    {
        return $this->set(self::COPYRIGHT, $value);
    }

    /**
     * Returns value of 'copyright' property
     *
     * @return integer
     */
    public function getCopyright()
    {
        $value = $this->get(self::COPYRIGHT);
        return $value === null ? (integer)$value : $value;
    }
}
}