<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * Gps message
 */
class Gps extends \ProtobufMessage
{
    /* Field index constants */
    const COORDINATE_TYPE = 1;
    const LONGITUDE = 2;
    const LATITUDE = 3;
    const TIMESTAMP = 4;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::COORDINATE_TYPE => array(
            'name' => 'coordinate_type',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::LONGITUDE => array(
            'name' => 'longitude',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_DOUBLE,
        ),
        self::LATITUDE => array(
            'name' => 'latitude',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_DOUBLE,
        ),
        self::TIMESTAMP => array(
            'name' => 'timestamp',
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
        $this->values[self::COORDINATE_TYPE] = null;
        $this->values[self::LONGITUDE] = null;
        $this->values[self::LATITUDE] = null;
        $this->values[self::TIMESTAMP] = null;
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
     * Sets value of 'coordinate_type' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setCoordinateType($value)
    {
        return $this->set(self::COORDINATE_TYPE, $value);
    }

    /**
     * Returns value of 'coordinate_type' property
     *
     * @return integer
     */
    public function getCoordinateType()
    {
        $value = $this->get(self::COORDINATE_TYPE);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'longitude' property
     *
     * @param double $value Property value
     *
     * @return null
     */
    public function setLongitude($value)
    {
        return $this->set(self::LONGITUDE, $value);
    }

    /**
     * Returns value of 'longitude' property
     *
     * @return double
     */
    public function getLongitude()
    {
        $value = $this->get(self::LONGITUDE);
        return $value === null ? (double)$value : $value;
    }

    /**
     * Sets value of 'latitude' property
     *
     * @param double $value Property value
     *
     * @return null
     */
    public function setLatitude($value)
    {
        return $this->set(self::LATITUDE, $value);
    }

    /**
     * Returns value of 'latitude' property
     *
     * @return double
     */
    public function getLatitude()
    {
        $value = $this->get(self::LATITUDE);
        return $value === null ? (double)$value : $value;
    }

    /**
     * Sets value of 'timestamp' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setTimestamp($value)
    {
        return $this->set(self::TIMESTAMP, $value);
    }

    /**
     * Returns value of 'timestamp' property
     *
     * @return integer
     */
    public function getTimestamp()
    {
        $value = $this->get(self::TIMESTAMP);
        return $value === null ? (integer)$value : $value;
    }
}
}