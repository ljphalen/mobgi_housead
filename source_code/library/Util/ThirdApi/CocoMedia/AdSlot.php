<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * AdSlot message
 */
class AdSlot extends \ProtobufMessage
{
    /* Field index constants */
    const ADSLOT_ID = 1;
    const ADSLOT_SIZE = 2;
    const VIDEO = 4;
    const ADSLOT_TYPE = 5;
    const ADS = 15;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::ADSLOT_ID => array(
            'name' => 'adslot_id',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::ADSLOT_SIZE => array(
            'name' => 'adslot_size',
            'required' => false,
            'type' => '\CocoMedia\Size'
        ),
        self::VIDEO => array(
            'name' => 'video',
            'required' => false,
            'type' => '\CocoMedia\Video'
        ),
        self::ADSLOT_TYPE => array(
            'name' => 'adslot_type',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::ADS => array(
            'name' => 'ads',
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
        $this->values[self::ADSLOT_ID] = null;
        $this->values[self::ADSLOT_SIZE] = null;
        $this->values[self::VIDEO] = null;
        $this->values[self::ADSLOT_TYPE] = null;
        $this->values[self::ADS] = null;
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
     * Sets value of 'adslot_id' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setAdslotId($value)
    {
        return $this->set(self::ADSLOT_ID, $value);
    }

    /**
     * Returns value of 'adslot_id' property
     *
     * @return string
     */
    public function getAdslotId()
    {
        $value = $this->get(self::ADSLOT_ID);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'adslot_size' property
     *
     * @param \CocoMedia\Size $value Property value
     *
     * @return null
     */
    public function setAdslotSize(\CocoMedia\Size $value=null)
    {
        return $this->set(self::ADSLOT_SIZE, $value);
    }

    /**
     * Returns value of 'adslot_size' property
     *
     * @return \CocoMedia\Size
     */
    public function getAdslotSize()
    {
        return $this->get(self::ADSLOT_SIZE);
    }

    /**
     * Sets value of 'video' property
     *
     * @param \CocoMedia\Video $value Property value
     *
     * @return null
     */
    public function setVideo(\CocoMedia\Video $value=null)
    {
        return $this->set(self::VIDEO, $value);
    }

    /**
     * Returns value of 'video' property
     *
     * @return \CocoMedia\Video
     */
    public function getVideo()
    {
        return $this->get(self::VIDEO);
    }

    /**
     * Sets value of 'adslot_type' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setAdslotType($value)
    {
        return $this->set(self::ADSLOT_TYPE, $value);
    }

    /**
     * Returns value of 'adslot_type' property
     *
     * @return integer
     */
    public function getAdslotType()
    {
        $value = $this->get(self::ADSLOT_TYPE);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'ads' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setAds($value)
    {
        return $this->set(self::ADS, $value);
    }

    /**
     * Returns value of 'ads' property
     *
     * @return integer
     */
    public function getAds()
    {
        $value = $this->get(self::ADS);
        return $value === null ? (integer)$value : $value;
    }
}
}