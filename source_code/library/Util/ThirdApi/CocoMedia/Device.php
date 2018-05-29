<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * Device message
 */
class Device extends \ProtobufMessage
{
    /* Field index constants */
    const DEVICE_TYPE = 1;
    const OS_TYPE = 2;
    const OS_VERSION = 3;
    const VENDOR = 4;
    const MODEL = 5;
    const UDID = 6;
    const SCREEN_SIZE = 7;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::DEVICE_TYPE => array(
            'name' => 'device_type',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::OS_TYPE => array(
            'name' => 'os_type',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::OS_VERSION => array(
            'name' => 'os_version',
            'required' => false,
            'type' => '\CocoMedia\Version'
        ),
        self::VENDOR => array(
            'default' => '',
            'name' => 'vendor',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::MODEL => array(
            'default' => '',
            'name' => 'model',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::UDID => array(
            'name' => 'udid',
            'required' => false,
            'type' => '\CocoMedia\UdId'
        ),
        self::SCREEN_SIZE => array(
            'name' => 'screen_size',
            'required' => false,
            'type' => '\CocoMedia\Size'
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
        $this->values[self::DEVICE_TYPE] = null;
        $this->values[self::OS_TYPE] = null;
        $this->values[self::OS_VERSION] = null;
        $this->values[self::VENDOR] = self::$fields[self::VENDOR]['default'];
        $this->values[self::MODEL] = self::$fields[self::MODEL]['default'];
        $this->values[self::UDID] = null;
        $this->values[self::SCREEN_SIZE] = null;
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
     * Sets value of 'device_type' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setDeviceType($value)
    {
        return $this->set(self::DEVICE_TYPE, $value);
    }

    /**
     * Returns value of 'device_type' property
     *
     * @return integer
     */
    public function getDeviceType()
    {
        $value = $this->get(self::DEVICE_TYPE);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'os_type' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setOsType($value)
    {
        return $this->set(self::OS_TYPE, $value);
    }

    /**
     * Returns value of 'os_type' property
     *
     * @return integer
     */
    public function getOsType()
    {
        $value = $this->get(self::OS_TYPE);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'os_version' property
     *
     * @param \CocoMedia\Version $value Property value
     *
     * @return null
     */
    public function setOsVersion(\CocoMedia\Version $value=null)
    {
        return $this->set(self::OS_VERSION, $value);
    }

    /**
     * Returns value of 'os_version' property
     *
     * @return \CocoMedia\Version
     */
    public function getOsVersion()
    {
        return $this->get(self::OS_VERSION);
    }

    /**
     * Sets value of 'vendor' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setVendor($value)
    {
        return $this->set(self::VENDOR, $value);
    }

    /**
     * Returns value of 'vendor' property
     *
     * @return string
     */
    public function getVendor()
    {
        $value = $this->get(self::VENDOR);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'model' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setModel($value)
    {
        return $this->set(self::MODEL, $value);
    }

    /**
     * Returns value of 'model' property
     *
     * @return string
     */
    public function getModel()
    {
        $value = $this->get(self::MODEL);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'udid' property
     *
     * @param \CocoMedia\UdId $value Property value
     *
     * @return null
     */
    public function setUdid(\CocoMedia\UdId $value=null)
    {
        return $this->set(self::UDID, $value);
    }

    /**
     * Returns value of 'udid' property
     *
     * @return \CocoMedia\UdId
     */
    public function getUdid()
    {
        return $this->get(self::UDID);
    }

    /**
     * Sets value of 'screen_size' property
     *
     * @param \CocoMedia\Size $value Property value
     *
     * @return null
     */
    public function setScreenSize(\CocoMedia\Size $value=null)
    {
        return $this->set(self::SCREEN_SIZE, $value);
    }

    /**
     * Returns value of 'screen_size' property
     *
     * @return \CocoMedia\Size
     */
    public function getScreenSize()
    {
        return $this->get(self::SCREEN_SIZE);
    }
}
}