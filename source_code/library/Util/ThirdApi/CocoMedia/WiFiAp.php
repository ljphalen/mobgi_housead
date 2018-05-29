<?php
/**
 * Auto generated from AYang_mobads_api_5.3.proto at 2016-12-09 11:10:13
 *
 * CocoMedia package
 */

namespace CocoMedia {
/**
 * WiFiAp message
 */
class WiFiAp extends \ProtobufMessage
{
    /* Field index constants */
    const AP_MAC = 1;
    const RSSI = 2;
    const AP_NAME = 3;
    const IS_CONNECTED = 4;

    /* @var array Field descriptors */
    protected static $fields = array(
        self::AP_MAC => array(
            'name' => 'ap_mac',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::RSSI => array(
            'name' => 'rssi',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_INT,
        ),
        self::AP_NAME => array(
            'name' => 'ap_name',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_STRING,
        ),
        self::IS_CONNECTED => array(
            'name' => 'is_connected',
            'required' => false,
            'type' => \ProtobufMessage::PB_TYPE_BOOL,
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
        $this->values[self::AP_MAC] = null;
        $this->values[self::RSSI] = null;
        $this->values[self::AP_NAME] = null;
        $this->values[self::IS_CONNECTED] = null;
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
     * Sets value of 'ap_mac' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setApMac($value)
    {
        return $this->set(self::AP_MAC, $value);
    }

    /**
     * Returns value of 'ap_mac' property
     *
     * @return string
     */
    public function getApMac()
    {
        $value = $this->get(self::AP_MAC);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'rssi' property
     *
     * @param integer $value Property value
     *
     * @return null
     */
    public function setRssi($value)
    {
        return $this->set(self::RSSI, $value);
    }

    /**
     * Returns value of 'rssi' property
     *
     * @return integer
     */
    public function getRssi()
    {
        $value = $this->get(self::RSSI);
        return $value === null ? (integer)$value : $value;
    }

    /**
     * Sets value of 'ap_name' property
     *
     * @param string $value Property value
     *
     * @return null
     */
    public function setApName($value)
    {
        return $this->set(self::AP_NAME, $value);
    }

    /**
     * Returns value of 'ap_name' property
     *
     * @return string
     */
    public function getApName()
    {
        $value = $this->get(self::AP_NAME);
        return $value === null ? (string)$value : $value;
    }

    /**
     * Sets value of 'is_connected' property
     *
     * @param boolean $value Property value
     *
     * @return null
     */
    public function setIsConnected($value)
    {
        return $this->set(self::IS_CONNECTED, $value);
    }

    /**
     * Returns value of 'is_connected' property
     *
     * @return boolean
     */
    public function getIsConnected()
    {
        $value = $this->get(self::IS_CONNECTED);
        return $value === null ? (boolean)$value : $value;
    }
}
}